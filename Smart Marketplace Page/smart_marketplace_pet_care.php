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

function createMarketplaceNotification(PDO $db, int $userId, string $title, string $message, string $type): void
{
  if (!$userId) {
    return;
  }

  $check = $db->prepare("
    SELECT id
    FROM notifications
    WHERE user_id = ?
      AND title = ?
      AND message = ?
      AND type = ?
      AND DATE(created_at) = CURDATE()
    LIMIT 1
  ");
  $check->execute([$userId, $title, $message, $type]);

  if ($check->fetchColumn()) {
    return;
  }

  $stmt = $db->prepare("
    INSERT INTO notifications (user_id, title, message, type, is_read)
    VALUES (?, ?, ?, ?, 0)
  ");
  $stmt->execute([$userId, $title, $message, $type]);
}


function getVetRecommendation(PDO $db, ?array $selectedPet, ?int $ownerUserId): array
{
  $fallback = [
    'has_doctor' => false,
    'doctor' => '',
    'title' => '',
    'meta' => ''
  ];

  if (!$selectedPet || !$ownerUserId) {
    return $fallback;
  }

  $stmt = $db->prepare("
    SELECT vr.*, u.username AS vet_name
    FROM vet_requests vr
    LEFT JOIN users u ON u.id = vr.reviewed_by
    WHERE vr.pet_id = ?
      AND vr.owner_user_id = ?
      AND vr.reviewed_by IS NOT NULL
    ORDER BY COALESCE(vr.reviewed_at, vr.created_at) DESC, vr.id DESC
    LIMIT 1
  ");
  $stmt->execute([(int)$selectedPet['id'], $ownerUserId]);
  $request = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$request) {
    return $fallback;
  }

  $vetName = trim((string)($request['vet_name'] ?? ''));
  $doctor = $vetName !== '' && stripos($vetName, 'Dr.') !== 0 ? 'Dr. ' . $vetName : $vetName;

  return [
    'has_doctor' => true,
    'doctor' => $doctor ?: 'your veterinarian',
    'title' => 'Recommended from: ' . ($request['title'] ?? 'last vet request'),
    'meta' => 'Linked to request #' . (int)$request['id'] . ' - status: ' . ucfirst($request['status'] ?? 'reviewed')
  ];
}

function getRenalCareDietRequest(PDO $db, ?array $selectedPet, ?int $ownerUserId): array
{
  $fallback = [
    'status' => 'none',
    'is_approved' => false,
    'is_pending' => false
  ];

  if (!$selectedPet || !$ownerUserId) {
    return $fallback;
  }

  $stmt = $db->prepare("
    SELECT *
    FROM vet_requests
    WHERE pet_id = ?
      AND owner_user_id = ?
      AND request_type = 'renal_care_diet_approval'
      AND related_type = 'marketplace_product'
    ORDER BY
      CASE
        WHEN status IN ('approved', 'completed') THEN 0
        WHEN status = 'pending' THEN 1
        ELSE 2
      END,
      COALESCE(reviewed_at, created_at) DESC,
      id DESC
    LIMIT 1
  ");
  $stmt->execute([(int)$selectedPet['id'], $ownerUserId]);
  $request = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$request) {
    return $fallback;
  }

  $status = strtolower($request['status'] ?? 'none');

  return [
    'status' => $status,
    'is_approved' => in_array($status, ['approved', 'completed'], true),
    'is_pending' => $status === 'pending'
  ];
}

function petHasVetInMedicalProcedures(PDO $db, ?array $selectedPet): bool
{
  if (!$selectedPet) {
    return false;
  }

  $stmt = $db->prepare("
    SELECT id
    FROM medical_procedures
    WHERE pet_id = ?
      AND vet_id IS NOT NULL
    LIMIT 1
  ");
  $stmt->execute([(int)$selectedPet['id']]);

  return (bool)$stmt->fetchColumn();
}

$pets = [];
$selectedPet = null;
$vetRecommendation = [
  'has_doctor' => false,
  'doctor' => '',
  'title' => '',
  'meta' => ''
];
$renalCareDietRequest = [
  'status' => 'none',
  'is_approved' => false,
  'is_pending' => false
];


if (!empty($_SESSION['user_id'])) {
  try {
    $db = Database::getInstance()->getConnection();

    $ownerStmt = $db->prepare("SELECT id FROM pet_owners WHERE user_id = ? LIMIT 1");
    $ownerStmt->execute([$_SESSION['user_id']]);
    $owner = $ownerStmt->fetch(PDO::FETCH_ASSOC);

    if ($owner) {
      $petStmt = $db->prepare("SELECT * FROM pets WHERE owner_id = ? ORDER BY id DESC");
      $petStmt->execute([$owner['id']]);
      $pets = $petStmt->fetchAll(PDO::FETCH_ASSOC);

      $selectedPetId = (int)($_GET['pet_id'] ?? $_SESSION['marketplace_pet_id'] ?? 0);

      foreach ($pets as $pet) {
        if ((int)$pet['id'] === $selectedPetId) {
          $selectedPet = $pet;
          break;
        }
      }

      if (!$selectedPet && !empty($pets)) {
        $selectedPet = $pets[0];
      }

      if ($selectedPet) {
        $_SESSION['marketplace_pet_id'] = $selectedPet['id'];
        $vetRecommendation = getVetRecommendation($db, $selectedPet, (int)$_SESSION['user_id']);
        $renalCareDietRequest = getRenalCareDietRequest($db, $selectedPet, (int)$_SESSION['user_id']);
        $petHasVet = petHasVetInMedicalProcedures($db, $selectedPet);
      }
    }
  } catch (Exception $e) {
    $pets = [];
    $selectedPet = null;
  }
}

$petName = $selectedPet['name'] ?? 'Your pet';
$petAge = isset($selectedPet['age']) ? $selectedPet['age'] . ' yrs' : 'Age not added';
$petSpecies = $selectedPet['species'] ?? 'Pet';
$petImageName = basename(trim((string)($selectedPet['image'] ?? '')));
$petImage = $petImageName !== '' && $petImageName !== 'default.png' && $petImageName !== 'default-pet.png'
  ? asset('uploads/pets/' . $petImageName)
  : asset('uploads/pets/default-pet.png');

$petWeight = isset($selectedPet['weight']) && (float)$selectedPet['weight'] > 0
  ? rtrim(rtrim(number_format((float)$selectedPet['weight'], 2), '0'), '.') . ' kg'
  : '';

$petAllergiesText = strtolower((string)($selectedPet['allergies'] ?? ''));
$petAllergies = array_filter(array_map('trim', explode(',', $petAllergiesText)));

function productAllergyAlert(array $petAllergies, array $ingredients, string $petName)
{
  $matches = [];

  foreach ($petAllergies as $allergy) {
    foreach ($ingredients as $ingredient) {
      if ($allergy !== '' && stripos($ingredient, $allergy) !== false) {
        $matches[] = $allergy;
      }
    }
  }

  $matches = array_unique($matches);

  if (!empty($matches)) {
    return '<div class="allergy-alert"><i class="bi bi-exclamation-triangle-fill" style="color:#c97a1a;"></i><span><b>Allergy alert:</b> contains <u>' .
      htmlspecialchars(implode(', ', $matches)) .
      '</u> — listed in ' . htmlspecialchars($petName) . '\'s allergies.</span></div>';
  }

  return '<div class="allergy-safe"><i class="bi bi-check-circle-fill"></i> Safe for ' .
    htmlspecialchars($petName) .
    ' — no allergens detected</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_renal_care_diet') {
  if (empty($_SESSION['user_id']) || !$selectedPet) {
    header('Location: ' . $_SERVER['REQUEST_URI']);
    exit;
  }

  try {
    $db = Database::getInstance()->getConnection();

    if (!petHasVetInMedicalProcedures($db, $selectedPet)) {
      header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?pet_id=' . (int)$selectedPet['id']);
      exit;
    }

    $check = $db->prepare("
      SELECT id
      FROM vet_requests
      WHERE pet_id = ?
        AND owner_user_id = ?
        AND request_type = 'renal_care_diet_approval'
        AND related_type = 'marketplace_product'
        AND status IN ('pending', 'approved', 'completed')
      LIMIT 1
    ");
    $check->execute([(int)$selectedPet['id'], (int)$_SESSION['user_id']]);

    if (!$check->fetchColumn()) {
      $stmt = $db->prepare("
        INSERT INTO vet_requests
          (pet_id, owner_user_id, request_type, title, description, priority, related_type)
        VALUES (?, ?, 'renal_care_diet_approval', 'Renal Care Diet approval', ?, 'normal', 'marketplace_product')
      ");

      $description = 'Owner requested vet approval to buy Renal Care Diet from Smart Marketplace for ' . ($selectedPet['name'] ?? 'this pet') . '.';
      $stmt->execute([(int)$selectedPet['id'], (int)$_SESSION['user_id'], $description]);
    }
  } catch (Exception $e) {
  }

  header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?') . '?pet_id=' . (int)$selectedPet['id']);
  exit;
}

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
    $autoShipProductNames = [
      'Adult Dry Food 1kg',
      'Oatmeal Gentle Shampoo',
      'Training Pads (50pk)'
    ];

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

    $hasAutoShip = false;

    foreach ($items as $item) {
      $name = trim($item['name'] ?? '');

      if (in_array($name, $autoShipProductNames, true)) {
        $hasAutoShip = true;
        break;
      }
    }


    $deliveryDate = $hasAutoShip
      ? date('Y-m-d', strtotime('+30 days'))
      : date('Y-m-d', strtotime('+3 days'));

    $finalTotalPrice = max(0, $totalPrice - $pointsCreditUsed);

    $orderStmt = $db->prepare("
      INSERT INTO orders (owner_id, vendor_id, total_price, is_recurring, delivery_date)
      VALUES (?, ?, ?, ?, ?)
    ");

    $orderStmt->execute([
      $ownerId,
      $vendorId,
      $finalTotalPrice,
      $hasAutoShip ? 1 : 0,
      $deliveryDate
    ]);

    $orderId = (int) $db->lastInsertId();

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

      if ($name === '') {
        continue;
      }

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

    createMarketplaceNotification(
      $db,
      (int)$_SESSION['user_id'],
      'Order placed successfully',
      'Your marketplace order #' . $orderId . ' has been placed. Delivery date: ' . date('M j, Y', strtotime($deliveryDate)) . '.',
      'marketplace_order'
    );

    $db->commit();

    echo json_encode([
      'ok' => true,
      'order_id' => $orderId,
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
          AND oi.product_name IN ('Adult Dry Food 1kg', 'Oatmeal Gentle Shampoo', 'Training Pads (50pk)')
        GROUP BY oi.product_name
        ORDER BY MIN(o.delivery_date) ASC
        LIMIT 20
      ");

      $autoShipStmt->execute([$ownerId]);
      $autoShipOrders = $autoShipStmt->fetchAll(PDO::FETCH_ASSOC);
      foreach ($autoShipOrders as $autoOrder) {
        if (!empty($autoOrder['delivery_date']) && date('Y-m-d', strtotime($autoOrder['delivery_date'])) === date('Y-m-d', strtotime('+1 day'))) {
          createMarketplaceNotification(
            $db,
            (int)$_SESSION['user_id'],
            'Auto-ship delivery tomorrow',
            'Your auto-ship item "' . ($autoOrder['items'] ?? 'pet product') . '" is scheduled for delivery tomorrow.',
            'auto_ship_delivery'
          );
        }
      }
    }
  } catch (Exception $e) {
    $autoShipOrders = [];
  }
}

$marketplaceProducts = [];

try {
  $db = Database::getInstance()->getConnection();
  $stmt = $db->query("
    SELECT mi.*, v.name AS vendor_name
    FROM marketplace_items mi
    LEFT JOIN vendors v ON v.id = mi.vendor_id
    WHERE mi.stock > 0
    ORDER BY mi.created_at DESC, mi.id DESC
  ");
  $marketplaceProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
  $marketplaceProducts = [];
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
          <details class="pet-picker">
            <summary class="pet-pill">
              <div class="pet-photo">
                <img src="<?= htmlspecialchars($petImage) ?>" alt="<?= htmlspecialchars($petName) ?>"
                  onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset('uploads/pets/default-pet.png')) ?>';">
              </div>

              <div class="pet-pill-info">
                <div class="pet-pill-label">Shopping for:</div>
                <div class="pet-pill-name"><?= htmlspecialchars($petName) ?></div>
                <small>
                  <?= htmlspecialchars($petAge) ?> • <?= htmlspecialchars($petSpecies) ?><?= $petWeight ? ' • ' . htmlspecialchars($petWeight) : '' ?>
                </small>
              </div>
              <i class="fas fa-chevron-down"></i>
            </summary>

            <div class="pet-picker-menu">
              <div class="pet-picker-title">My Pets</div>

              <?php if (!empty($pets)): ?>
                <?php foreach ($pets as $pet): ?>
                  <?php
                  $optionImageName = basename(trim((string)($pet['image'] ?? '')));
                  $optionImage = $optionImageName !== '' && $optionImageName !== 'default.png'
                    ? asset('uploads/pets/' . $optionImageName)
                    : asset('uploads/pets/default-pet.png');

                  $optionAge = isset($pet['age']) ? $pet['age'] . ' yrs' : 'Age not added';
                  $optionSpecies = $pet['species'] ?? 'Pet';
                  $isActivePet = $selectedPet && (int)$selectedPet['id'] === (int)$pet['id'];
                  ?>

                  <a class="pet-picker-option <?= $isActivePet ? 'active' : '' ?>" href="?pet_id=<?= (int)$pet['id'] ?>">
                    <img src="<?= htmlspecialchars($optionImage) ?>" alt="<?= htmlspecialchars($pet['name']) ?>"
                      onerror="this.onerror=null;this.src='<?= htmlspecialchars(asset('uploads/pets/default-pet.png')) ?>';">

                    <span>
                      <strong><?= htmlspecialchars($pet['name']) ?></strong>
                      <small><?= htmlspecialchars($optionSpecies) ?> • <?= htmlspecialchars($optionAge) ?></small>
                    </span>

                    <?php if ($isActivePet): ?>
                      <em>Active</em>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="pet-picker-empty">No pets added yet</div>
              <?php endif; ?>

              <a class="pet-picker-add" href="<?= htmlspecialchars(app_url('home/index', 'my-pets')) ?>">
                + Add New Pet
              </a>
            </div>
          </details>
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
    <?php if (!empty($vetRecommendation['has_doctor'])): ?>
      <!-- VET PANEL -->
      <h3 class="section-title"><span class="dot"></span> Recommended by <?= htmlspecialchars($vetRecommendation['doctor']) ?> (last consultation)</h3>
      <div class="vet-panel mb-2">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <span class="vet-tag"><i class="bi bi-shield-check"></i> VET LINK ACTIVE</span>
            <h6 class="mt-2"><?= htmlspecialchars($vetRecommendation['title']) ?> unlocks a <b style="color:var(--teal);">15% discount</b></h6>
            <small style="color:var(--muted);"><?= htmlspecialchars($vetRecommendation['meta']) ?></small>
          </div>
          <button class="btn btn-primary-soft" id="view-recommendations-btn" style="width:auto;padding:10px 22px;">
            View Recommendations
          </button>
        </div>
      </div>
    <?php endif; ?>

    <!-- BROWSE PRODUCTS -->
    <h3 class="section-title"><span class="dot" style="background:var(--sky);"></span> Browse Products</h3>
    <div class="filter-row mb-4">
      <div class="chip active" data-filter="all">All</div>
      <div class="chip" data-filter="therapeutic-diets">Therapeutic Diets</div>
      <div class="chip" data-filter="treats">Treats</div>
      <div class="chip" data-filter="supplements">Supplements</div>
      <div class="chip" data-filter="hygiene">Hygiene</div>
      <div class="chip" data-filter="toys">Toys</div>
      <?php if (!empty($vetRecommendation['has_doctor'])): ?>
        <div class="chip" data-filter="vet-recommended"><i class="bi bi-shield-plus"></i> Vet-Recommended</div>
      <?php endif; ?>
      <div class="chip" data-filter="auto-ship"><i class="bi bi-arrow-repeat"></i> Auto-Ship Eligible</div>
    </div>

    <div class="row g-4 row-cols-2 row-cols-md-3 row-cols-lg-4">
      <?php foreach ($marketplaceProducts as $product): ?>
        <?php
        $category = strtolower(trim((string)($product['category'] ?? '')));
        $categorySlug = preg_replace('/[^a-z0-9]+/', '-', $category);
        $categorySlug = trim($categorySlug, '-') ?: 'all';
        $image = trim((string)($product['image'] ?? ''));
        $imageSrc = $image !== '' ? 'images/' . htmlspecialchars($image) : 'images/Dry-Food.jpg';
        ?>
        <div class="col" data-category="<?= htmlspecialchars($categorySlug) ?>">
          <div class="product">
            <div class="product-img">
              <img src="<?= $imageSrc ?>" alt="<?= htmlspecialchars($product['name']) ?>" loading="lazy">
            </div>
            <div class="product-body">
              <h5><?= htmlspecialchars($product['name']) ?></h5>
              <div class="brand"><?= htmlspecialchars($product['vendor_name'] ?? 'Marketplace Vendor') ?></div>
              <div class="price">EGP <?= number_format((float)$product['price'], 0) ?></div>
              <div class="allergy-safe">
                <i class="bi bi-box-seam"></i>
                <?= htmlspecialchars($product['short_description'] ?? 'Available now') ?>
              </div>
              <div class="mt-auto pt-3">
                <button class="btn-primary-soft add-to-cart-btn"
                  data-name="<?= htmlspecialchars($product['name']) ?>"
                  data-brand="<?= htmlspecialchars($product['vendor_name'] ?? 'Marketplace Vendor') ?>"
                  data-price="<?= htmlspecialchars((string)$product['price']) ?>"
                  data-bg="bg-green"
                  data-badge=""
                  data-badge-label=""
                  data-pts="<?= max(1, (int)((float)$product['price'] / 50)) ?>">
                  <i class="bi bi-cart-plus"></i> Add to Cart
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
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
    <h3 class="section-title" id="my-cart" style="margin-top:44px;">
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

  <?php require_once $pawHubsPath . '/app/views/partials/footer.php'; ?> <div id="toast" class="toast-message"></div>
  <script>
    window.POINTS_VALUE = <?= json_encode($pointsCredit) ?>;
  </script>
  <script src="smart_marketplace_pet_care.js"></script>
</body>

</html>