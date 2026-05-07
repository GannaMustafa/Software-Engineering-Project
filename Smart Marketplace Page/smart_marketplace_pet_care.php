<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$pawHubsPath = dirname(__DIR__) . '/Paw Hubs';
require_once $pawHubsPath . '/app/core/Database.php';

if (!function_exists('asset')) {
  function asset($path)
  {
    return '../Paw Hubs/public/' . ltrim($path, '/');
  }
}

$selectedPet = null;

if (!empty($_SESSION['user_id'])) {
  try {
    $db = Database::getInstance()->getConnection();

    $ownerStmt = $db->prepare("SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1");
    $ownerStmt->execute([$_SESSION['user_id']]);
    $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

    if ($owner) {
      $petStmt = $db->prepare("SELECT * FROM pets WHERE owner_id = ? ORDER BY id DESC LIMIT 1");
      $petStmt->execute([$owner['id']]);
      $selectedPet = $petStmt->fetch(PDO::FETCH_ASSOC);
    }
  } catch (Exception $e) {
    $selectedPet = null;
  }
}

$petName = $selectedPet['name'] ?? 'Your pet';
$petAge = isset($selectedPet['age']) ? $selectedPet['age'] . ' yrs' : 'Age not added';
$petSpecies = $selectedPet['species'] ?? 'Pet';
$petImage = !empty($selectedPet['image']) && $selectedPet['image'] !== 'default.png'
  ? asset('uploads/' . $selectedPet['image'])
  : asset('images/guest.png');
$petWeight = isset($selectedPet['weight_kg']) && (int)$selectedPet['weight_kg'] > 0
  ? $selectedPet['weight_kg'] . ' kg'
  : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'checkout') {
  header('Content-Type: application/json');

  if (empty($_SESSION['user_id'])) {
    echo json_encode(['ok' => false, 'message' => 'Please login first.']);
    exit;
  }

  $items = json_decode($_POST['items'] ?? '[]', true);
  $pointsApplied = ($_POST['points_applied'] ?? '0') === '1';

  if (empty($items) || !is_array($items)) {
    echo json_encode(['ok' => false, 'message' => 'Cart is empty.']);
    exit;
  }

  try {
    $db = Database::getInstance()->getConnection();
    $db->beginTransaction();

    $ownerStmt = $db->prepare("SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1");
    $ownerStmt->execute([$_SESSION['user_id']]);
    $ownerId = (int) $ownerStmt->fetchColumn();

    if (!$ownerId) {
      throw new Exception('Pet owner profile not found.');
    }

    $vendorStmt = $db->query("SELECT id FROM vendors LIMIT 1");
    $vendorId = (int) $vendorStmt->fetchColumn();

    if (!$vendorId) {
      $db->exec("INSERT INTO vendors (name, balance, is_active) VALUES ('Smart Marketplace', 0, 1)");
      $vendorId = (int) $db->lastInsertId();
    }

    $totalPrice = 0;
    $earnedPoints = 0;
    $orderIds = [];

    foreach ($items as $item) {
      $qty = max(1, (int) ($item['qty'] ?? 1));
      $price = (float) ($item['price'] ?? 0);
      $pts = (int) ($item['pts'] ?? 0);
      $taskPts = (int) ($item['taskPoints'] ?? 0);

      $totalPrice += $price * $qty;
      $earnedPoints += ($pts * $qty) + $taskPts;
    }

    $currentPoints = 0;
    $pointsCreditUsed = 0;

    if ($pointsApplied) {
      $pointsStmt = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM loyalty_points WHERE user_id = ?");
      $pointsStmt->execute([$_SESSION['user_id']]);
      $currentPoints = (int) $pointsStmt->fetchColumn();

      $pointsCreditUsed = $currentPoints / 2;
      $pointsCreditUsed = min($pointsCreditUsed, $totalPrice);
    }

    $remainingPointsCredit = $pointsCreditUsed;

    $orderStmt = $db->prepare("
  INSERT INTO orders (owner_id, vendor_id, total_price, is_recurring, delivery_date)
  VALUES (?, ?, ?, ?, ?)
");

    $itemStmt = $db->prepare("
  INSERT INTO order_items (order_id, product_name, price, quantity, availability_status, points)
  VALUES (?, ?, ?, ?, 'pending', ?)
");

    foreach ($items as $item) {
      $name = trim($item['name'] ?? '');
      $qty = max(1, (int) ($item['qty'] ?? 1));
      $price = (float) ($item['price'] ?? 0);
      $pts = (int) ($item['pts'] ?? 0);
      $taskPts = (int) ($item['taskPoints'] ?? 0);
      $badge = strtolower($item['badge'] ?? '');
      $isAutoShip = !empty($item['isAutoShip']) || $badge === 'sub';

      if ($name === '') {
        continue;
      }

      $itemTotal = $price * $qty;

      if ($remainingPointsCredit > 0) {
        $creditForItem = min($remainingPointsCredit, $itemTotal);
        $itemTotal = max(0, $itemTotal - $creditForItem);
        $remainingPointsCredit -= $creditForItem;
      }

      $deliveryDate = $isAutoShip
        ? date('Y-m-d', strtotime('+30 days'))
        : date('Y-m-d', strtotime('+3 days'));

      $orderStmt->execute([
        $ownerId,
        $vendorId,
        $itemTotal,
        $isAutoShip ? 1 : 0,
        $deliveryDate
      ]);

      $orderId = (int) $db->lastInsertId();
      $orderIds[] = $orderId;

      $itemStmt->execute([
        $orderId,
        $name,
        $price,
        $qty,
        ($pts * $qty) + $taskPts
      ]);
    }

    if ($earnedPoints > 0) {
      $pointsStmt = $db->prepare("INSERT INTO loyalty_points (user_id, points) VALUES (?, ?)");
      $pointsStmt->execute([$_SESSION['user_id'], $earnedPoints]);
    }

    if ($pointsApplied && $pointsCreditUsed > 0) {
      $pointsToDeduct = (int) round($pointsCreditUsed * 2);
      $deductStmt = $db->prepare("INSERT INTO loyalty_points (user_id, points) VALUES (?, ?)");
      $deductStmt->execute([$_SESSION['user_id'], -$pointsToDeduct]);
    }

    $db->commit();

    echo json_encode([
      'ok' => true,
      'order_id' => implode(', ', $orderIds),
      'earned_points' => $earnedPoints
    ]);
    exit;
  } catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) {
      $db->rollBack();
    }

    echo json_encode(['ok' => false, 'message' => $e->getMessage()]);
    exit;
  }
}

$loyaltyPoints = 0;

if (!empty($_SESSION['user_id'])) {
  try {
    $db = Database::getInstance()->getConnection();

    $tableStmt = $db->prepare("
      SELECT COUNT(*)
      FROM INFORMATION_SCHEMA.TABLES
      WHERE TABLE_SCHEMA = DATABASE()
        AND TABLE_NAME = 'loyalty_points'
    ");
    $tableStmt->execute();

    if ((int) $tableStmt->fetchColumn() > 0) {
      $pointsStmt = $db->prepare("SELECT COALESCE(SUM(points), 0) FROM loyalty_points WHERE user_id = ?");
      $pointsStmt->execute([$_SESSION['user_id']]);
      $loyaltyPoints = (int) $pointsStmt->fetchColumn();
    }
  } catch (Exception $e) {
    $loyaltyPoints = 0;
  }
}

$pointsCredit = $loyaltyPoints / 2;
$nextReward = 1500;
$rewardProgress = min(100, (int) round(($loyaltyPoints / $nextReward) * 100));

$userOrders = [];

if (!empty($_SESSION['user_id'])) {
  try {
    $db = Database::getInstance()->getConnection();

    $ownerStmt = $db->prepare("SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1");
    $ownerStmt->execute([$_SESSION['user_id']]);
    $ownerId = (int) $ownerStmt->fetchColumn();

    if ($ownerId) {
      $ordersStmt = $db->prepare("
        SELECT o.id, o.total_price, o.is_recurring, o.delivery_date,
               GROUP_CONCAT(CONCAT(oi.product_name, ' x', oi.quantity) SEPARATOR ', ') AS items,
               COALESCE(SUM(oi.points), 0) AS earned_points
        FROM orders o
        LEFT JOIN order_items oi ON oi.order_id = o.id
        WHERE o.owner_id = ?
        GROUP BY o.id, o.total_price, o.is_recurring
        ORDER BY o.id DESC
        LIMIT 5
      ");
      $ordersStmt->execute([$ownerId]);
      $userOrders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (Exception $e) {
    $userOrders = [];
  }
}

$autoShipOrders = [];

if (!empty($_SESSION['user_id'])) {
  try {
    $db = Database::getInstance()->getConnection();

    $ownerStmt = $db->prepare("SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1");
    $ownerStmt->execute([$_SESSION['user_id']]);
    $ownerId = (int) $ownerStmt->fetchColumn();

    if ($ownerId) {
      $autoShipStmt = $db->prepare("
        SELECT MIN(o.id) AS id,
               MIN(o.delivery_date) AS delivery_date,
               oi.product_name AS items
        FROM orders o
        INNER JOIN order_items oi ON oi.order_id = o.id
        WHERE o.owner_id = ?
          AND o.is_recurring = 1
        GROUP BY oi.product_name
        ORDER BY MIN(o.delivery_date) ASC
        LIMIT 20
      ");

      $autoShipStmt->execute([$ownerId]);
      $autoShipOrders = $autoShipStmt->fetchAll(PDO::FETCH_ASSOC);
    }
  } catch (Exception $e) {
    $autoShipOrders = [];
  }
}


?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="smart_marketplace_pet_care.css">


</head>

<body>
  <?php require_once $pawHubsPath . '/app/views/partials/navbar.php'; ?>


  <div class="page">


    <!-- HERO -->
    <div class="hero">
      <div class="row align-items-center g-4">
        <div class="col-md-7">
          <h1>Smart Marketplace</h1>
          <p>Curated, vet-aware shopping for your pet — with allergy checks, prescription verification, and rewards
            built in.</p>
        </div>
        <div class="col-md-5">
          <div class="pet-pill">
            <div class="pet-photo">
              <img src="<?= htmlspecialchars($petImage) ?>" alt="<?= htmlspecialchars($petName) ?>"
                style="width:55px; height:55px; border-radius:50%; object-fit:cover;">
            </div>
            <div>
              <div style="font-weight:700;">
                Shopping for: <?= htmlspecialchars($petName) ?>
              </div>
              <small style="opacity:.85;">
                <?= htmlspecialchars($petAge) ?> • <?= htmlspecialchars($petSpecies) ?> • <?= htmlspecialchars($petWeight) ?>
              </small>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- LOYALTY CARD -->
    <div class="loyalty-card">
      <div class="row align-items-center g-3">
        <div class="col-md-3 text-center text-md-start">
          <div class="text-uppercase small text-muted" style="letter-spacing:1px;">Loyalty Points</div>

          <div class="points-num">
            <?= number_format($loyaltyPoints) ?>
            <span style="font-size:1rem;color:var(--muted);font-weight:500;">pts</span>
          </div>

          <small style="color:var(--muted);">
            ≈ EGP <?= number_format($pointsCredit, 2) ?> marketplace credit
          </small>
        </div>

        <div class="col-md-9">
          <div class="d-flex justify-content-between small mb-1">
            <span style="font-weight:600;">
              Progress to next reward (<?= number_format($nextReward) ?> pts)
            </span>
            <span style="color:var(--teal);font-weight:700;">
              <?= $rewardProgress ?>%
            </span>
          </div>

          <div class="progress mb-3">
            <div class="progress-bar" style="width:<?= $rewardProgress ?>%"></div>
          </div>

          <div class="d-flex flex-wrap gap-2">
            <div class="task-chip">
              Flea treatment <span class="pts">+150</span>
            </div>

            <div class="task-chip">
              Annual checkup <span class="pts">+200</span>
            </div>

            <div class="task-chip">
              Dental cleaning <span class="pts">+120</span>
            </div>

            <div class="task-chip">
              Vaccine booster <span class="pts">+180</span>
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- VET PANEL -->
    <h3 class="section-title"><span class="dot"></span> Recommended by Dr. Hassan (last consultation)</h3>
    <div class="vet-panel mb-2">
      <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
          <span class="vet-tag"><i class="bi bi-shield-check"></i> VET LINK ACTIVE</span>
          <h6 class="mt-2">3 products from your last visit unlock a <b style="color:var(--teal);">15% discount</b></h6>
          <small style="color:var(--muted);">Linked to consultation #CN-2041 • valid until May 28, 2026</small>
        </div>
        <button class="btn btn-primary-soft" id="view-recommendations-btn" style="width:auto;padding:10px 22px;">
          View Recommendations
        </button>
      </div>
    </div>

    <!-- BROWSE PRODUCTS -->
    <h3 class="section-title"><span class="dot" style="background:var(--sky);"></span> Browse Products</h3>
    <div class="filter-row mb-4">
      <div class="chip active" data-filter="all">All</div>
      <div class="chip" data-filter="therapeutic-diets">Therapeutic Diets</div>
      <div class="chip" data-filter="treats">Treats</div>
      <div class="chip" data-filter="supplements">Supplements</div>
      <div class="chip" data-filter="hygiene">Hygiene</div>
      <div class="chip" data-filter="toys">Toys</div>
      <div class="chip" data-filter="vet-recommended"><i class="bi bi-shield-plus"></i> Vet-Recommended</div>
      <div class="chip" data-filter="auto-ship"><i class="bi bi-arrow-repeat"></i> Auto-Ship Eligible</div>
    </div>

    <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">

      <div class="col" data-category="therapeutic-diets">
        <div class="product">
          <span class="badge-tag badge-rx"><i class="bi bi-prescription2"></i> RX REQUIRED</span>
          <div class="product-img"><img src="images/Renal-Care-Diet.jpg" alt="Renal Care Diet" loading="lazy"></div>
          <div class="product-body">
            <h5>Renal Care Diet</h5>
            <div class="brand">Hill's Prescription</div>
            <div class="price">EGP 2,400</div>
            <div class="rx-info"><i class="bi bi-lock-fill"></i><span>Vet prescription required. Awaiting approval from
                Dr. Hassan.</span></div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft btn-locked" disabled><i class="bi bi-lock"></i>
                Locked — Request Approval</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="supplements vet-recommended">
        <div class="product">
          <span class="badge-tag badge-vet"><i class="bi bi-shield-check"></i> VET PICK -15%</span>
          <div class="product-img"><img src="images/joint-support-chews.jpg" alt="Joint Support Chews" loading="lazy">
          </div>
          <div class="product-body">
            <h5>Joint Support Chews</h5>
            <div class="brand">VetPlus Mobility</div>
            <div class="price">EGP 1,275 <small>EGP 1,500</small></div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Safe for Milo — no allergens detected
            </div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Joint Support Chews"
                data-brand="VetPlus Mobility" data-price="1275" data-old="1500" data-bg="bg-sand"
                data-badge="vet" data-badge-label="VET -15%" data-pts="26"><i class="bi bi-cart-plus"></i> Add to
                Cart</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="treats">
        <div class="product">
          <div class="product-img"><img src="images/Chicken-Crunchy-Treats.jpg" alt="Chicken Crunchy Treats"
              loading="lazy"></div>
          <div class="product-body">
            <h5>Chicken Crunchy Treats</h5>
            <div class="brand">PawSnacks Co.</div>
            <div class="price">EGP 450</div>
            <div class="allergy-alert"><i class="bi bi-exclamation-triangle-fill"
                style="color:#c97a1a;"></i><span><b>Allergy alert:</b> contains <u>chicken</u> — listed in Milo's
                allergies.</span></div>
            <div class="mt-auto pt-3"><button class="btn-outline-soft add-to-cart-btn"
                data-name="Chicken Crunchy Treats" data-brand="PawSnacks Co." data-price="450"
                data-bg="bg-sky" data-badge="warn" data-badge-label="⚠ Allergen" data-pts="9">Acknowledge &
                Continue</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="therapeutic-diets auto-ship">
        <div class="product">
          <span class="badge-tag badge-sub"><i class="bi bi-arrow-repeat"></i> AUTO-SHIP</span>
          <div class="product-img"><img src="images/Dry-Food.jpg" alt="Adult Dry Food 1kg" loading="lazy"></div>
          <div class="product-body">
            <h5>Adult Dry Food 1kg</h5>
            <div class="brand">Royal Canin</div>
            <div class="price">EGP 260</div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Allergen-free for Milo</div>
            <div class="mt-auto pt-3">
              <button class="btn-primary-soft add-to-cart-btn" data-name="Adult Dry Food 1kg" data-brand="Royal Canin"
                data-price="260" data-bg="bg-green" data-badge="sub" data-badge-label="AUTO-SHIP"
                data-auto-ship="1" data-pts="5">

                <i class="bi bi-cart-plus"></i> Subscribe & Save 10%
              </button>
            </div>
          </div>
        </div>
      </div>

      <div class="col" data-category="supplements vet-recommended">
        <div class="product">
          <span class="badge-tag badge-vet"><i class="bi bi-shield-check"></i> VET PICK</span>
          <div class="product-img"><img src="images/Omega-3-Fish-Oil.jpg" alt="Omega-3 Fish Oil" loading="lazy"></div>
          <div class="product-body">
            <h5>Omega-3 Fish Oil</h5>
            <div class="brand">PetWell Naturals</div>
            <div class="price">EGP 1,000 <small>EGP 1,200</small></div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Safe for Milo</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Omega-3 Fish Oil"
                data-brand="PetWell Naturals" data-price="1000" data-old="1200" data-bg="bg-mint"
                data-badge="vet" data-badge-label="VET PICK" data-pts="20" data-task-points="180"><i class="bi bi-cart-plus"></i> Add to
                Cart</button></div>

          </div>
        </div>
      </div>

      <div class="col" data-category="toys">
        <div class="product">
          <div class="product-img"><img src="images/Squeaky-Fetch-Ball.webp" alt="Squeaky Fetch Ball" loading="lazy">
          </div>
          <div class="product-body">
            <h5>Squeaky Fetch Ball</h5>
            <div class="brand">PlayPaws</div>
            <div class="price">EGP 325</div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Non-toxic materials</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Squeaky Fetch Ball"
                data-brand="PlayPaws" data-price="325" data-bg="bg-sky" data-badge=""
                data-badge-label="" data-pts="7"><i class="bi bi-cart-plus"></i> Add to Cart</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="hygiene auto-ship">
        <div class="product">
          <span class="badge-tag badge-sub"><i class="bi bi-arrow-repeat"></i> AUTO-SHIP</span>
          <div class="product-img"><img src="images/Oatmeal-Gentle-Shampoo.webp" alt="Oatmeal Gentle Shampoo"
              loading="lazy"></div>
          <div class="product-body">
            <h5>Oatmeal Gentle Shampoo</h5>
            <div class="brand">CleanCoat</div>
            <div class="price">EGP 710</div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Hypoallergenic</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn"
                data-name="Oatmeal Gentle Shampoo" data-brand="CleanCoat" data-price="639" data-old="710"
                data-bg="bg-sand" data-badge="sub" data-badge-label="AUTO-SHIP"
                data-auto-ship="1" data-pts="13" data-task-points="150">
                <i class="bi bi-cart-plus"></i> Subscribe & Save 10%</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col" data-category="supplements">
        <div class="product">
          <span class="badge-tag badge-rx"><i class="bi bi-prescription2"></i> RX REQUIRED</span>
          <div class="product-img"><img src="images/Anti-Anxiety-Tablets.webp" loading="lazy"></div>
          <div class="product-body">
            <h5>Anti-Anxiety Tablets</h5>
            <div class="brand">CalmVet Rx</div>
            <div class="price">EGP 1,600</div>
            <div class="rx-info"><i class="bi bi-lock-fill"></i><span>Vet prescription required.</span></div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft btn-locked" disabled><i class="bi bi-lock"></i>
                Locked — Request Approval</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="treats vet-recommended">
        <div class="product">
          <span class="badge-tag badge-vet"><i class="bi bi-shield-check"></i> VET PICK -10%</span>
          <div class="product-img"><img src="images/Dental-Chew-Sticks.jpg" alt="Dental Chew Sticks" loading="lazy">
          </div>
          <div class="product-body">
            <h5>Dental Chew Sticks</h5>
            <div class="brand">FreshBite</div>
            <div class="price">EGP 540 <small>EGP 600</small></div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Wheat-free, safe for Milo</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Dental Chew Sticks"
                data-brand="FreshBite" data-price="540" data-old="600" data-bg="bg-mint"
                data-badge="sub" data-badge-label="AUTO-SHIP"
                data-auto-ship="1" data-pts="120">
                <i class="bi bi-cart-plus"></i> Add to
                Cart</button>
            </div>
          </div>
        </div>
      </div>

      <div class="col" data-category="hygiene">
        <div class="product">
          <div class="product-img"><img src="images/Orthopedic-Memory-Bed.jpg" alt="Orthopedic Memory Bed"
              loading="lazy"></div>
          <div class="product-body">
            <h5>Orthopedic Memory Bed</h5>
            <div class="brand">CozyPaw</div>
            <div class="price">EGP 2,700</div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Removable washable cover</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Orthopedic Memory Bed"
                data-brand="CozyPaw" data-price="2700" data-bg="bg-sky" data-badge=""
                data-badge-label="" data-pts="54"><i class="bi bi-cart-plus"></i> Add to Cart</button></div>
          </div>
        </div>
      </div>

      <div class="col" data-category="hygiene auto-ship">
        <div class="product">
          <span class="badge-tag badge-sub"><i class="bi bi-arrow-repeat"></i> AUTO-SHIP</span>
          <div class="product-img"><img src="images/Training-Pads.jpg" alt="Training Pads" loading="lazy"></div>
          <div class="product-body">
            <h5>Training Pads (50pk)</h5>
            <div class="brand">PupClean</div>
            <div class="price">EGP 900</div>
            <div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Fragrance-free</div>
            <div class="mt-auto pt-3"><button class="btn-primary-soft add-to-cart-btn" data-name="Training Pads (50pk)"
                data-brand="PupClean" data-price="810" data-old="900" data-bg="bg-green"
                data-badge="sub" data-badge-label="AUTO-SHIP" data-auto-ship="1" data-pts="120"><i class="bi bi-cart-plus"></i> Subscribe &
                Save 10%</button></div>
          </div>
        </div>
      </div>

    </div>

    <!-- AUTO-SHIP -->
    <h3 class="section-title"><span class="dot" style="background:var(--teal);"></span> Your Smart Auto-Ship</h3>

    <div class="row g-3">
      <?php if (empty($autoShipOrders)): ?>
        <div class="col-12">
          <div class="ship-card">
            <div class="ship-icon"><i class="bi bi-box-seam"></i></div>
            <div>
              <div style="font-weight:700;">No auto-ship orders yet</div>
              <div class="ship-meta">Subscribe to auto-ship products and checkout to see predicted deliveries here.</div>
            </div>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($autoShipOrders as $autoOrder): ?>
          <?php
          $deliveryValue = !empty($autoOrder['delivery_date'])
            ? date('Y-m-d', strtotime($autoOrder['delivery_date']))
            : date('Y-m-d', strtotime('+30 days'));
          ?>

          <div class="col-12">
            <div class="ship-card auto-ship-card">
              <div class="ship-icon"><i class="bi bi-box-seam"></i></div>

              <div class="ship-content">
                <div class="ship-top">
                  <div>
                    <div class="ship-title">
                      <?= htmlspecialchars($autoOrder['items'] ?? 'Auto-ship order') ?>
                    </div>

                    <div class="ship-meta delivery-row">
                      Next delivery:
                      <b class="delivery-text"><?= date('D M j Y', strtotime($deliveryValue)) ?></b>
                      <small class="adjust-label">(Predicted)</small>
                    </div>
                  </div>

                  <span class="vet-tag auto-ship-tag">AUTO-SHIP</span>
                </div>

                <div class="ship-actions">
                  <div class="ship-meta">Predicted recurring delivery cycle: 30 days</div>

                  <div class="delivery-controls">
                    <input type="date" class="delivery-date" value="<?= $deliveryValue ?>">
                    <button type="button" class="change-date-btn">Change Delivery</button>
                  </div>
                </div>

                <div class="consumption-bar">
                  <div class="consumption-fill" style="width:68%"></div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <!-- ===== MY CART SECTION ===== -->
    <h3 class="section-title" style="margin-top:44px;">
      <span class="dot" style="background:var(--green);"></span>
      My Cart
      <span id="cart-count-badge"
        style="background:var(--green);color:#fff;font-size:.7rem;padding:3px 10px;border-radius:99px;font-weight:700;margin-left:4px;">0
        items</span>
    </h3>

    <div class="cart-section">
      <div class="cart-header">
        <div class="cart-header-left">
          <div class="cart-icon-wrap">
            <i class="bi bi-cart3"></i>
            <span class="cart-badge" id="cart-header-badge">0</span>
          </div>
          <div>
            <p class="cart-title">Milo's Cart</p>
            <p class="cart-subtitle">Items are allergy-checked & vet-verified</p>
          </div>
        </div>
        <button onclick="clearCart()"
          style="background:#fdf0f0;color:#b54848;border:1px solid #f1c9c9;border-radius:10px;padding:8px 16px;font-size:.82rem;font-weight:600;cursor:pointer;">
          <i class="bi bi-trash3"></i> Clear Cart
        </button>
      </div>

      <div class="cart-body">
        <div id="cart-items-list">
          <!-- items injected here -->
        </div>
        <div class="cart-empty" id="cart-empty">
          <div class="empty-emoji">🛒</div>
          <p>Your cart is empty!<br><span style="font-size:.85rem;color:#aac2b8;">Browse products above and click "Add
              to Cart"</span></p>
        </div>
      </div>

      <div class="cart-summary" id="cart-summary" style="display:none;">
        <!-- Points banner -->
        <div class="cart-points-banner">
          <i class="bi bi-star-fill" style="color:var(--teal);font-size:1rem;flex-shrink:0;"></i>
          <span>You'll earn <strong id="pts-to-earn">0 pts</strong> from this order &nbsp;·&nbsp; Use your <strong><?= number_format($loyaltyPoints) ?> pts (EGP <?= number_format($pointsCredit, 2) ?>)</strong> as credit?</span>
          <button class="use-pts-btn" id="use-pts-btn" onclick="togglePoints()">Use Points</button>
        </div>

        <!-- Coupon -->
        <div class="coupon-row">
          <input type="text" class="coupon-input" id="coupon-input" placeholder="Enter coupon code (try: MILO10)">
          <button class="coupon-btn" onclick="applyCoupon()">Apply</button>
        </div>
        <div class="coupon-ok" id="coupon-ok"><i class="bi bi-check-circle-fill"></i> Coupon MILO10 applied — 10% off!
        </div>

        <!-- Lines -->
        <div class="summary-line">
          <span>Subtotal (<span id="sum-items">0</span> items)</span>
          <span id="sum-subtotal">EGP 0.00</span>
        </div>
        <div class="summary-line" id="sum-discount-row" style="display:none;">
          <span>Vet &amp; auto-ship discounts</span>
          <span class="discount" id="sum-discount">-EGP 0.00</span>
        </div>
        <div class="summary-line" id="sum-coupon-row" style="display:none;">
          <span>Coupon (MILO10)</span>
          <span class="discount" id="sum-coupon">-EGP 0.00</span>
        </div>
        <div class="summary-line" id="sum-pts-row" style="display:none;">
          <span>Health Points credit</span>
          <span class="pts-credit" id="sum-pts-val">-EGP <?= number_format($pointsCredit, 2) ?></span>
        </div>
        <div class="summary-line">
          <span>Shipping</span>
          <span style="color:var(--green);font-weight:600;" id="shipping-line">Free 🎉</span>
        </div>
        <div class="summary-line total">
          <span>Total</span>
          <span id="sum-total">EGP 0.00</span>
        </div>

        <button class="checkout-btn mt-3" onclick="checkout()">
          <i class="bi bi-bag-check-fill"></i> Proceed to Checkout
          <span style="opacity:.7;font-size:.85rem;font-weight:500;" id="checkout-pts-note">(+0 pts)</span>
        </button>
      </div>
    </div>

    <!-- MY ORDERS -->
    <h3 class="section-title" style="margin-top:44px;">
      <span class="dot" style="background:var(--sky);"></span>
      My Orders
    </h3>

    <div class="orders-section">
      <?php if (empty($userOrders)): ?>
        <div class="orders-empty">
          No orders yet.
        </div>
      <?php else: ?>
        <?php foreach ($userOrders as $order): ?>
          <div class="order-card">
            <div>
              <div class="order-title">
                Order #<?= (int) $order['id'] ?>
              </div>
              <div class="order-meta">
                For: <?= htmlspecialchars($petName) ?> • <?= htmlspecialchars($order['items'] ?? 'No items') ?>
              </div>
            </div>
            <div class="order-meta">
              Delivery: <?= !empty($order['delivery_date']) ? date('M j, Y', strtotime($order['delivery_date'])) : 'Not scheduled' ?>
            </div>
            <div class="order-side">
              <strong>EGP <?= number_format((float) $order['total_price'], 2) ?></strong>
              <span>+<?= (int) $order['earned_points'] ?> pts</span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>


  </div>

  <?php require_once $pawHubsPath . '/app/views/partials/footer.php'; ?>
  <?php require_once $pawHubsPath . '/app/views/partials/theme_toggle.php'; ?>
  <div id="toast" class="toast-message"></div>
  <script>
    window.POINTS_VALUE = <?= json_encode($pointsCredit) ?>;
  </script>
  <script src="smart_marketplace_pet_care.js"></script>
</body>

</html>