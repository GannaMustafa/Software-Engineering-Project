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

$db = Database::getInstance();
$connect = $db->getConnection();
$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function createVetRequest(PDO $connect, int $petId, ?int $ownerUserId, string $requestType, string $title, string $description, string $priority = 'normal', ?string $relatedType = null, ?int $relatedId = null, ?string $destinationCountry = null): void
{
  $check = $connect->prepare("
    SELECT id
    FROM vet_requests
    WHERE pet_id = ?
      AND request_type = ?
      AND status = 'pending'
      AND COALESCE(related_type, '') = COALESCE(?, '')
      AND COALESCE(related_id, 0) = COALESCE(?, 0)
    LIMIT 1
  ");
  $check->execute([$petId, $requestType, $relatedType, $relatedId]);

  if ($check->fetchColumn()) {
    return;
  }

  $stmt = $connect->prepare("
    INSERT INTO vet_requests
      (pet_id, owner_user_id, request_type, title, description, priority, related_type, related_id, destination_country)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
  ");
  $stmt->execute([$petId, $ownerUserId, $requestType, $title, $description, $priority, $relatedType, $relatedId, $destinationCountry]);
}

function createUserNotification(PDO $connect, ?int $userId, string $title, string $message, string $type): void
{
  if (!$userId) {
    return;
  }

  $check = $connect->prepare("
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

  $stmt = $connect->prepare("
    INSERT INTO notifications (user_id, title, message, type, is_read)
    VALUES (?, ?, ?, ?, 0)
  ");
  $stmt->execute([$userId, $title, $message, $type]);
}


if (!isset($_SESSION['user_id'])) {
  header("Location: ../Paw Hubs/public/index.php?url=auth/login");
  exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'] ?? 'pet_owner';



$sql = "SELECT p.id, p.name, p.species, p.weight, p.age ,p.gender,p.image
        FROM pets p
        INNER JOIN pet_owners po ON p.owner_id = po.id
        WHERE po.user_id = ?";
$stmt = $connect->prepare($sql);
$stmt->execute([$user_id]);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);
$stmt = $connect->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$username = $stmt->fetchColumn() ?? 'Guest';
$stmt = $connect->prepare("SELECT address FROM pet_owners WHERE user_id = ?");
$stmt->execute([$user_id]);
$user_address = $stmt->fetchColumn() ?? 'Unknown';



$selected_pet_id = $_GET['pet_id'] ?? $_SESSION['selected_pet_id'] ?? ($pets[0]['id'] ?? null);
$selected_pet = null;
foreach ($pets as $p) {
  if ($p['id'] == $selected_pet_id) {
    $selected_pet = $p;
    $_SESSION['selected_pet_id'] = $p['id'];
    break;
  }
}

if (!$selected_pet && !empty($pets)) {
  $selected_pet = $pets[0];
  $_SESSION['selected_pet_id'] = $selected_pet['id'];
}

$selectedPetImage = '../Paw Hubs/public/uploads/pets/default-pet.png';
if (!empty($selected_pet['image']) && $selected_pet['image'] !== 'default.png' && $selected_pet['image'] !== 'default-pet.png') {
  $selectedPetImage = '../Paw Hubs/public/uploads/pets/' . rawurlencode($selected_pet['image']);
}

$passportRequest = null;
$passportStatus = 'not_requested';
$passportVetName = 'Veterinarian';
$passportDestination = 'Not selected';
$passportNumber = null;
$passportIssuedDate = null;
$passportDocUrl = '';
$microchipStatus = 'Not requested';
$microchipReference = 'Pending vet confirmation';
$rabiesStatus = 'Not scheduled';
$rabiesValidUntil = 'Not available';
$healthCertificateStatus = 'Pending approval';
$tapewormStatus = 'Based on destination';

function formatDisplayDate(?string $dateValue, string $fallback = 'Not available'): string
{
  if (!$dateValue) {
    return $fallback;
  }

  $timestamp = strtotime($dateValue);
  return $timestamp ? date('M d, Y', $timestamp) : $fallback;
}

function passportRequirementText(PDO $connect, ?array $selectedPet, string $destination, string $field, string $requiredText, string $notRequiredText): string
{
  if (!$selectedPet || $destination === '' || $destination === 'Not selected') {
    return 'Destination needed';
  }

  $stmt = $connect->prepare("
    SELECT {$field}
    FROM country_pet_requirements
    WHERE LOWER(country_name) = LOWER(?)
      AND LOWER(species) = LOWER(?)
    LIMIT 1
  ");
  $stmt->execute([$destination, $selectedPet['species'] ?? 'dog']);
  $required = $stmt->fetchColumn();

  if ($required === false) {
    return 'Check destination rules';
  }

  return (int) $required === 1 ? $requiredText : $notRequiredText;
}

if ($selected_pet) {
  $stmt = $connect->prepare("
    SELECT vr.*, u.username AS vet_name
    FROM vet_requests vr
    LEFT JOIN users u ON u.id = vr.reviewed_by
    WHERE vr.pet_id = ?
      AND vr.owner_user_id = ?
      AND vr.request_type = 'passport_request'
    ORDER BY
      CASE
        WHEN vr.status IN ('approved', 'completed') THEN 0
        WHEN vr.status = 'pending' THEN 1
        ELSE 2
      END,
      COALESCE(vr.reviewed_at, vr.created_at) DESC,
      vr.id DESC
    LIMIT 1
  ");
  $stmt->execute([$selected_pet['id'], $user_id]);
  $passportRequest = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  if ($passportRequest) {
    $passportStatus = strtolower($passportRequest['status'] ?? 'pending');
    $passportVetName = $passportRequest['vet_name'] ?: 'Veterinarian';
    $passportDestination = $passportRequest['destination_country'] ?: 'Not selected';
    $passportIssuedDate = $passportRequest['reviewed_at'] ?: $passportRequest['created_at'];
    $passportNumber = 'PET-' . date('Y', strtotime($passportIssuedDate ?: 'now')) . '-' . str_pad((string) $selected_pet['id'], 4, '0', STR_PAD_LEFT);
  }

  $stmt = $connect->prepare("
    SELECT status, reviewed_at, id
    FROM vet_requests
    WHERE pet_id = ?
      AND owner_user_id = ?
      AND request_type = 'microchip_surgery'
    ORDER BY
      CASE WHEN status IN ('approved', 'completed') THEN 0 WHEN status = 'pending' THEN 1 ELSE 2 END,
      COALESCE(reviewed_at, created_at) DESC,
      id DESC
    LIMIT 1
  ");
  $stmt->execute([$selected_pet['id'], $user_id]);
  $microchipRequest = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($microchipRequest) {
    $microchipStatusValue = strtolower($microchipRequest['status'] ?? 'pending');
    $microchipStatus = in_array($microchipStatusValue, ['approved', 'completed'], true) ? 'Approved' : ucfirst($microchipStatusValue);
    $microchipReference = 'MC-' . str_pad((string) $selected_pet['id'], 4, '0', STR_PAD_LEFT) . '-' . str_pad((string) $microchipRequest['id'], 4, '0', STR_PAD_LEFT);
  }
}


$healthRecords = [];
if ($selected_pet) {
  $stmt = $connect->prepare("
        SELECT id, title, description, record_date, created_at
        FROM health_records
        WHERE pet_id = ?
        ORDER BY record_date DESC, created_at DESC
    ");
  $stmt->execute([$selected_pet['id']]);
  $healthRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$chronicConditionLabel = 'Not set';
if ($selected_pet) {
  $stmt = $connect->prepare("
        SELECT title, description
        FROM health_records
        WHERE pet_id = ?
          AND LOWER(title) LIKE '%chronic%'
        ORDER BY record_date DESC, created_at DESC
        LIMIT 1
    ");
  $stmt->execute([$selected_pet['id']]);
  $chronicRecord = $stmt->fetch(PDO::FETCH_ASSOC);
  if ($chronicRecord) {
    $chronicConditionLabel = trim($chronicRecord['description'] ?: $chronicRecord['title']);
  }
}

$vetCanAdd = in_array($role, ['vet', 'admin'], true);
$vaccines = [];
if ($selected_pet) {
  $stmt = $connect->prepare("
        SELECT id, vaccine_name, due_date, status, created_at
        FROM vaccines
        WHERE pet_id = ?
        ORDER BY due_date ASC
    ");
  $stmt->execute([$selected_pet['id']]);
  $vaccines = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($vaccines as &$vaccine) {
    $dueDate = new DateTime($vaccine['due_date']);
    $today = new DateTime();
    $interval = $today->diff($dueDate);
    $daysUntilDue = $interval->invert ? -$interval->days : $interval->days;

    $vaccine['days_until_due'] = $daysUntilDue;

    if ($daysUntilDue < 0) {
      $vaccine['status_badge'] = 'over';
      $vaccine['status_text'] = 'Overdue - ' . abs($daysUntilDue) . ' d';
    } elseif ($daysUntilDue <= 14) {
      $vaccine['status_badge'] = 'due';
      $vaccine['status_text'] = 'Due soon';
    } elseif ($daysUntilDue <= 45) {
      $vaccine['status_badge'] = 'soon';
      $vaccine['status_text'] = 'Due in ' . $daysUntilDue . ' days';
    } else {
      $vaccine['status_badge'] = 'ok';
      $vaccine['status_text'] = 'On track';
    }

    $vaccine['formatted_date'] = $dueDate->format('M d, Y');
  }

  foreach ($vaccines as $vaccine) {
    if (stripos($vaccine['vaccine_name'] ?? '', 'rabies') !== false) {
      $rabiesStatus = strtolower($vaccine['status'] ?? '') === 'completed' ? 'Completed' : ucfirst($vaccine['status'] ?? 'pending');
      $rabiesValidUntil = $vaccine['formatted_date'] ?? formatDisplayDate($vaccine['due_date'] ?? null);
      break;
    }
  }

  foreach ($vaccines as $vaccine) {
    $badge = $vaccine['status_badge'] ?? '';
    if (in_array($badge, ['due', 'over'], true)) {
      $isOverdue = $badge === 'over';
      createUserNotification(
        $connect,
        $user_id,
        $isOverdue ? 'Vaccination overdue' : 'Vaccination due soon',
        ($selected_pet['name'] ?? 'Your pet') . ' has ' . ($vaccine['vaccine_name'] ?? 'a vaccine') . ' ' . strtolower($vaccine['status_text'] ?? 'due soon') . '.',
        $isOverdue ? 'vaccine_overdue' : 'vaccine_due'
      );
    }
  }
}

$vaccineTypes = [
  'dog' => [
    'Rabies Booster' => ['frequency' => 'Annual', 'months' => 12],
    'DHPP (Distemper combo)' => ['frequency' => '3-yr', 'months' => 36],
    'Leptospirosis' => ['frequency' => 'Annual', 'months' => 12],
    'Bordetella (Kennel cough)' => ['frequency' => '6-mo', 'months' => 6],
    'Lyme Disease' => ['frequency' => 'Annual', 'months' => 12],
    'Canine Influenza' => ['frequency' => 'Annual', 'months' => 12]
  ],
  'cat' => [
    'Rabies' => ['frequency' => 'Annual', 'months' => 12],
    'FVRCP (Feline Distemper)' => ['frequency' => '3-yr', 'months' => 36],
    'FeLV (Feline Leukemia)' => ['frequency' => 'Annual', 'months' => 12],
    'FIV (Feline Immunodeficiency)' => ['frequency' => 'Annual', 'months' => 12]
  ]
];
$speciesKey = strtolower($selected_pet['species'] ?? 'dog');
$vaccineOptions = $vaccineTypes[$speciesKey] ?? $vaccineTypes['dog'];
$totalVaccinesCount = count($vaccineOptions);
$completedVaccinesCount = count(array_filter($vaccines, function ($vaccine) {
  return strtolower($vaccine['status'] ?? '') === 'completed';
}));
$upcomingVaccinesCount = count(array_filter($vaccines, function ($vaccine) {
  return ($vaccine['days_until_due'] ?? -1) >= 0 && strtolower($vaccine['status'] ?? 'pending') !== 'completed';
}));
$stmt = $connect->prepare("SELECT COALESCE(SUM(points), 0) FROM loyalty_points WHERE user_id = ?");
$stmt->execute([$user_id]);
$databaseLoyaltyPoints = (int) $stmt->fetchColumn();
$loyalityPoints = $databaseLoyaltyPoints;
$nextRewardPoints = 1500;
$healthScore = min(100, (int) round(($loyalityPoints / $nextRewardPoints) * 100));

$passportApproved = in_array($passportStatus, ['approved', 'completed'], true);
$passportStatusText = $passportRequest ? ucfirst($passportStatus) : 'Not requested';
if ($passportApproved) {
  $healthCertificateStatus = 'Issued ' . formatDisplayDate($passportIssuedDate, date('M d, Y'));
}
$tapewormStatus = passportRequirementText($connect, $selected_pet, $passportDestination, 'tapeworm_treatment_required', 'Required for destination', 'Not required');
$passportDocUrl = $passportApproved
  ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . strtok($_SERVER['REQUEST_URI'] ?? '', '?') . '?pet_id=' . urlencode((string) ($selected_pet['id'] ?? '')) . '&passport_doc=' . urlencode((string) $passportNumber) . '&print=1')
  : '';



if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_vaccine') {
  if (isset($_POST['pet_id'], $_POST['vaccine_name'], $_POST['due_date'])) {
    $petId = intval($_POST['pet_id']);
    $vaccineName = trim($_POST['vaccine_name']);
    $dueDate = $_POST['due_date'];
    $status = $_POST['status'] ?? 'pending';


    if ($petId > 0 && $vaccineName !== '' && $dueDate !== '') {
      $checkStmt = $connect->prepare("
        SELECT id
        FROM vaccines
        WHERE pet_id = ?
          AND vaccine_name = ?
          AND due_date >= CURDATE()
        LIMIT 1
      ");
      $checkStmt->execute([$petId, $vaccineName]);
      $existingVaccine = $checkStmt->fetch(PDO::FETCH_ASSOC);

      if ($existingVaccine) {
        header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&vaccine_exists=1");
        exit;
      }

      $stmt = $connect->prepare("
                INSERT INTO vaccines (pet_id, vaccine_name, due_date, status)
                VALUES (?, ?, ?, ?)
            ");
      $stmt->execute([$petId, $vaccineName, $dueDate, $status]);
      $vaccineId = (int) $connect->lastInsertId();

      createVetRequest(
        $connect,
        $petId,
        $user_id,
        'vaccination_completion',
        'Vaccination completion needed',
        $vaccineName . ' is scheduled for ' . date('M d, Y', strtotime($dueDate)) . '. Vet should verify/administer it and complete the vaccination record.',
        'normal',
        'vaccine',
        $vaccineId
      );

      header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&vaccine_added=1");
      exit;
    }
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_passport') {
  $petId = intval($_POST['pet_id'] ?? 0);
  $destinationCountry = trim($_POST['destination_country'] ?? '');

  if ($petId > 0) {
    createVetRequest(
      $connect,
      $petId,
      $user_id,
      'passport_request',
      'Pet passport approval requested',
      'Owner requested vet review for passport documents, medical records, rabies vaccination, and travel eligibility.',
      'normal',
      null,
      null,
      $destinationCountry !== '' ? $destinationCountry : null
    );

    header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&passport_requested=1");
    exit;
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'request_microchip_surgery') {
  $petId = intval($_POST['pet_id'] ?? 0);

  if ($petId > 0) {
    createVetRequest(
      $connect,
      $petId,
      $user_id,
      'microchip_surgery',
      'Microchip insertion surgery requested',
      'Owner requested a microchip insertion procedure. Vet should review the pet and schedule the operation through Surgery Manager.',
      'normal'
    );

    header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&microchip_requested=1");
    exit;
  }
}
function calculateBCS(float $currentWeight, float $idealWeight): int
{
  if ($idealWeight <= 0) return 5;

  $percentageDiff = (($currentWeight - $idealWeight) / $idealWeight) * 100;

  if ($percentageDiff >= 40) return 9;
  if ($percentageDiff >= 30) return 8;
  if ($percentageDiff >= 20) return 7;
  if ($percentageDiff >= 10) return 6;
  if ($percentageDiff >= -10) return 5;
  if ($percentageDiff >= -20) return 4;
  if ($percentageDiff >= -30) return 3;
  if ($percentageDiff >= -40) return 2;
  return 1;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'log_entry') {
  $petId = intval($_POST['pet_id'] ?? 0);
  $currentWeight = floatval($_POST['weight'] ?? 0);

  if ($petId > 0 && $currentWeight > 0) {
    $stmt = $connect->prepare("
      SELECT p.weight
      FROM pets p
      INNER JOIN pet_owners po ON po.id = p.owner_id
      WHERE p.id = ?
        AND po.user_id = ?
    ");
    $stmt->execute([$petId, $user_id]);
    $idealWeightResult = $stmt->fetchColumn();

    if ($idealWeightResult === false) {
      header("Location: " . $_SERVER['PHP_SELF']);
      exit;
    }

    $idealWeight = floatval($idealWeightResult);

    $postedBcs = intval($_POST['bcs_score'] ?? 0);
    $bcs = ($postedBcs >= 1 && $postedBcs <= 9) ? $postedBcs : calculateBCS($currentWeight, $idealWeight);
    $today = date('Y-m-d');
    $loggedAt = date('Y-m-d H:i:s');

    try {
      $sql = "INSERT INTO pet_weight_logs (pet_id, weight, bcs_score, logged_at) 
                    VALUES (?, ?, ?, ?)";
      $stmt = $connect->prepare($sql);
      $stmt->execute([$petId, $currentWeight, $bcs, $loggedAt]);

      if (!empty($_POST['water_ml'])) {
        $stmt = $connect->prepare("
        INSERT INTO daily_logs (pet_id, metric_type, metric_value, logged_date)
        VALUES (?, 'water_intake', ?, ?)
    ");
        $stmt->execute([$petId, $_POST['water_ml'], $today]);
      }

      if (!empty($_POST['insulin_units'])) {
        $stmt = $connect->prepare("
        INSERT INTO daily_logs (pet_id, metric_type, metric_value, logged_date)
        VALUES (?, 'insulin', ?, ?)
    ");
        $stmt->execute([$petId, $_POST['insulin_units'], $today]);
      }
      if (!empty($_POST['mobility_score'])) {
        $stmt = $connect->prepare("
        INSERT INTO daily_logs (pet_id, metric_type, metric_value, logged_date)
        VALUES (?, 'mobility_score', ?, ?)
    ");
        $stmt->execute([$petId, $_POST['mobility_score'], $today]);
      }
      if (($_POST['entry_type'] ?? '') === 'chronic') {
        $metricSummary = 'Weight: ' . $currentWeight . ' kg, BCS: ' . $bcs;
        if ($_POST['water_ml'] !== '') {
          $metricSummary .= ', water intake: ' . $_POST['water_ml'] . ' ml';
        }
        if ($_POST['insulin_units'] !== '') {
          $metricSummary .= ', insulin: ' . $_POST['insulin_units'] . ' units';
        }
        if ($_POST['mobility_score'] !== '') {
          $metricSummary .= ', mobility score: ' . $_POST['mobility_score'] . '/10';
        }

        createVetRequest(
          $connect,
          $petId,
          $user_id,
          'chronic_alert',
          'Chronic condition review needed',
          'Owner logged chronic condition metrics: ' . $metricSummary . '. Vet should review the daily logs and add/update the chronic condition health record.',
          'urgent'
        );
      }
      header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&logged=1");
      exit;
    } catch (PDOException $e) {
      echo "Error: " . $e->getMessage();
    }
  }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_health_record') {
  if ($vetCanAdd && isset($_POST['pet_id'], $_POST['title'], $_POST['description'])) {
    $petId = intval($_POST['pet_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $recordDate = $_POST['record_date'] ?: date('Y-m-d');

    if ($petId > 0 && $title !== '') {
      $stmt = $connect->prepare("
                INSERT INTO health_records (pet_id, title, description, record_date)
                VALUES (?, ?, ?, ?)
            ");
      $stmt->execute([$petId, $title, $description, $recordDate]);

      header("Location: " . $_SERVER['PHP_SELF'] . "?pet_id=" . $petId . "&record_added=1");
      exit;
    }
  }
}



$showToast = false;
$toastPetName = '';

if (isset($_GET['pet_id']) && $selected_pet) {
  $showToast = true;
  $toastPetName = htmlspecialchars($selected_pet['name']);
}
$chronicLogs = [];
if ($selected_pet) {
  try {
    $stmt = $connect->prepare("
            SELECT metric_type, metric_value, logged_date 
            FROM daily_logs 
            WHERE pet_id = ? 
            AND metric_type IN ('water_intake', 'insulin')
            AND logged_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
            ORDER BY logged_date ASC
        ");
    $stmt->execute([$selected_pet['id']]);
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $chronicData = [
      'labels' => [],
      'water_intake' => [],
      'insulin' => []
    ];

    foreach ($logs as $log) {
      $date = date('D', strtotime($log['logged_date']));

      if (!in_array($date, $chronicData['labels'])) {
        $chronicData['labels'][] = $date;
      }

      $index = array_search($date, $chronicData['labels']);

      if ($log['metric_type'] === 'water_intake') {
        $chronicData['water_intake'][$index] = floatval($log['metric_value']);
      } elseif ($log['metric_type'] === 'insulin') {
        $chronicData['insulin'][$index] = floatval($log['metric_value']);
      }
    }

    $count = count($chronicData['labels']);
    for ($i = 0; $i < $count; $i++) {
      $chronicData['water_intake'][$i] = $chronicData['water_intake'][$i] ?? 0;
      $chronicData['insulin'][$i] = $chronicData['insulin'][$i] ?? 0;
    }

    $chronicLogs = $chronicData;
  } catch (Exception $e) {
    $chronicLogs = ['labels' => [], 'water_intake' => [], 'insulin' => []];
  }
}
$todayMetrics = [
  'insulin' => null,
  'water_intake' => null,
  'mobility_score' => null
];
$trends = [];

if ($selected_pet) {
  $today = date('Y-m-d');

  $stmt = $connect->prepare("
        SELECT metric_type, metric_value 
        FROM daily_logs 
        WHERE pet_id = ? 
        AND logged_date = ?
        AND metric_type IN ('insulin', 'water_intake', 'mobility_score')
    ");
  $stmt->execute([$selected_pet['id'], $today]);
  $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($logs as $log) {
    $todayMetrics[$log['metric_type']] = floatval($log['metric_value']);
  }

  if ($todayMetrics['water_intake'] === null) {
    $stmt = $connect->prepare("
            SELECT metric_value 
            FROM daily_logs 
            WHERE pet_id = ? AND metric_type = 'water_intake' 
            ORDER BY logged_date DESC LIMIT 1
        ");
    $stmt->execute([$selected_pet['id']]);
    $lastWater = $stmt->fetchColumn();
    if ($lastWater !== false) {
      $todayMetrics['water_intake'] = floatval($lastWater);
    }
  }

  $stmt = $connect->prepare("
        SELECT metric_type, AVG(metric_value) as avg_value
        FROM daily_logs 
        WHERE pet_id = ? 
        AND logged_date >= DATE_SUB(?, INTERVAL 7 DAY)
        AND metric_type IN ('insulin', 'water_intake', 'mobility_score')
        GROUP BY metric_type
    ");
  $stmt->execute([$selected_pet['id'], $today]);
  $averages = $stmt->fetchAll(PDO::FETCH_ASSOC);

  foreach ($averages as $avg) {
    $current = $todayMetrics[$avg['metric_type']];
    $avgVal = floatval($avg['avg_value']);

    if ($current !== null && $avgVal > 0) {
      $percentChange = (($current - $avgVal) / $avgVal) * 100;
      $trends[$avg['metric_type']] = round($percentChange, 1);

      if ($percentChange >= 25) {
        $metricLabels = [
          'water_intake' => 'water intake',
          'insulin' => 'insulin',
          'mobility_score' => 'mobility score'
        ];
        createUserNotification(
          $connect,
          $user_id,
          'Daily log higher than usual',
          ($selected_pet['name'] ?? 'Your pet') . "'s " . ($metricLabels[$avg['metric_type']] ?? $avg['metric_type']) . ' is ' . round($percentChange, 1) . '% higher than the 7-day average.',
          'daily_log_high'
        );
      }
    }
  }
}

$latestBCS = null;
if ($selected_pet) {
  $stmt = $connect->prepare("
        SELECT bcs_score 
        FROM pet_weight_logs 
        WHERE pet_id = ? AND bcs_score IS NOT NULL 
        ORDER BY logged_at DESC 
        LIMIT 1
    ");
  $stmt->execute([$selected_pet['id']]);
  $bcsResult = $stmt->fetch(PDO::FETCH_ASSOC);
  $latestBCS = $bcsResult['bcs_score'] ?? null;
}
$weightData = [];
$alert = null;

if ($selected_pet) {
  $sql = "SELECT weight, bcs_score, logged_at 
            FROM pet_weight_logs 
            WHERE pet_id = ? 
            ORDER BY logged_at ASC 
            LIMIT 180";
  $stmt = $connect->prepare($sql);
  $stmt->execute([$selected_pet['id']]);
  $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

  if (!empty($logs)) {
    foreach ($logs as $log) {
      $weightData[] = [
        'date' => $log['logged_at'],
        'weight' => floatval($log['weight']),
        'bcs' => $log['bcs_score'] ?? null
      ];
    }

    $recent = array_slice($logs, -2);
    if (count($recent) === 2) {
      $old = floatval($recent[0]['weight']);
      $new = floatval($recent[1]['weight']);
      $days = strtotime($recent[1]['logged_at']) - strtotime($recent[0]['logged_at']);
      $days = round($days / 86400);

      if ($days <= 14 && $old > 0) {
        $change = (($new - $old) / $old) * 100;
        if (abs($change) > 5) {
          $alert = [
            'type' => $change > 0 ? 'gain' : 'loss',
            'value' => round(abs($change), 1),
            'days' => $days,
            'old' => $old,
            'new' => $new
          ];
        }
      }
    }
  }
}

if ($selected_pet && $alert) {
  $weightAlertMessage = 'Sudden weight ' . ($alert['type'] === 'gain' ? 'gain' : 'loss') . ' detected for ' . ($selected_pet['name'] ?? 'your pet') . ': ' . $alert['value'] . '% in ' . $alert['days'] . ' days (' . $alert['old'] . ' to ' . $alert['new'] . ' kg).';

  createUserNotification(
    $connect,
    $user_id,
    'Weight change alert',
    $weightAlertMessage,
    'weight_alert'
  );

  createVetRequest(
    $connect,
    (int) $selected_pet['id'],
    $user_id,
    'weight_alert',
    'Weight change review needed',
    $weightAlertMessage . ' Vet should review the pet health logs and recommend follow-up if needed.',
    'urgent'
  );
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PawCare - Pet Health Management</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <link href="pet-health.css" rel="stylesheet">

</head>

<body>
  <?php require_once $pawHubsPath . '/app/views/partials/navbar.php'; ?>
  <main>

    <!--PET SELECTOR DROPDOWN-->
    <div class="dropdownBox">
      <button class="btn btn-ghost dropdown-toggle d-flex align-items-center px-3" type="button" id="petSelector"
        data-bs-toggle="dropdown" aria-expanded="false"
        style="border-radius: 12px; min-width: 280px; width: 100%; justify-content: space-between;">

        <!-- LEFT SIDE: Avatar + Text -->
        <div class="d-flex align-items-center gap-2">
          <img src="<?= htmlspecialchars($selectedPetImage) ?>" class="rounded-circle" id="selectedPetAvatar" alt="Pet"
            width="32" height="32">
          <div class="text-start">
            <div class="fw-semibold small lh-1" id="selectedPetName"><?= htmlspecialchars($selected_pet['name'] ?? 'No pet selected') ?></div>
            <div class="text-muted small lh-1" style="font-size: 0.7rem;" id="selectedPetBreed"><?= htmlspecialchars($selected_pet['species'] ?? 'Add a pet to track health') ?>
            </div>
          </div>
        </div>

        <!-- RIGHT SIDE: Arrow -->
        <i class="bi bi-chevron-down text-muted" style="font-size: 0.7rem;"></i>
      </button>
      <ul class="dropdown-menu shadow border-0 p-2 dropdown-menu-end" style=" border-radius: 16px; margin-top: 8px;">
        <li class="px-3 py-2">
          <div class="text-muted small fw-semibold">My Pets</div>
        </li>
        <li>
          <hr class="dropdown-divider my-1">
        </li>
        <!-- Pet -->
        <?php foreach ($pets as $pet) { ?>
          <li>
            <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-2 rounded-3 <?= $pet['id'] == ($selected_pet['id'] ?? null) ? 'active' : '' ?>"
              href="?pet_id=<?= $pet['id'] ?>">
              <img src="../Paw Hubs/public/uploads/pets/<?= htmlspecialchars($pet['image'] ?? 'default-pet.png') ?>" class="rounded-circle" width="40" alt="Buddy">
              <div class="flex-grow-1">
                <div class="fw-semibold small"><?php echo $pet["name"]; ?></div>
                <div class="text-muted small" style="font-size: 0.75rem;"><?php echo $pet["species"]; ?> &middot; <?php echo $pet["age"]; ?> yrs</div>
              </div>
              <?php if ($pet['id'] == ($selected_pet['id'] ?? null)): ?>
                <span class="badge-soft b-ok">Active</span>
              <?php endif; ?>
            </a>
          </li>

          <li>
            <hr class="dropdown-divider my-2">
          </li>
        <?php } ?>
        <li>
          <a class="dropdown-item text-center text-brand fw-semibold py-2" href="<?= htmlspecialchars(app_url('home/index', 'my-pets')) ?>" id="addPetBtn">
            <i class="bi bi-plus-circle me-1"></i> Add New Pet
          </a>
        </li>
      </ul>
    </div>
    </div>
    </div>



    <!-- PET HERO -->
    <div class="pet-hero mb-4">
      <?php if (!empty($pets)) { ?>
        <div class="pet-avatar"><?php if (!empty($selected_pet['image']) && $selected_pet['image'] !== 'default.png' && $selected_pet['image'] !== 'default-pet.png'): ?>
            <img src="<?= htmlspecialchars($selectedPetImage) ?>"
              alt="<?= htmlspecialchars($selected_pet['name']) ?>"
              style="width: 100%; height: 100%; object-fit: cover; border-radius: 24px;">
          <?php else: ?>

            <?php
                                  $species = strtolower($selected_pet['species'] ?? '');
                                  $emoji = '🐶';
                                  if (strpos($species, 'cat') !== false || strpos($species, 'feline') !== false) {
                                    $emoji = '😺';
                                  }
            ?>
            <span style="font-size: 3.4rem;"><?= $emoji ?></span>
          <?php endif; ?>
        </div>
        <div class="flex-grow-1">
          <div class="pet-hero-title">
            <div>
              <div class="d-flex align-items-center gap-2 mb-1">
                <h4 class="mb-0"><?= htmlspecialchars($selected_pet["name"]) ?></h4>
                <span class="chip"><span class="dot"></span> Healthy</span>
              </div>
              <div class="text-muted"><?= htmlspecialchars("{$selected_pet["species"]} . {$selected_pet["gender"]} . {$selected_pet["age"]} yrs . {$selected_pet["weight"]} kg") ?></div>
            </div>

            <div class="dropdown pet-switcher">
              <button class="pet-switcher-btn dropdown-toggle" type="button" id="petSelectorHero"
                data-bs-toggle="dropdown" aria-expanded="false">
                <span class="pet-switcher-copy">
                  <span class="pet-switcher-label">Choose pet to check</span>
                  <span class="pet-switcher-name"><?= htmlspecialchars($selected_pet['name'] ?? 'No pet selected') ?></span>
                </span>
                <i class="bi bi-chevron-down"></i>
              </button>
              <ul class="dropdown-menu pet-switcher-menu shadow border-0 p-2" aria-labelledby="petSelectorHero">
                <li class="px-3 py-2">
                  <div class="text-muted small fw-semibold">Switch pet</div>
                </li>
                <?php foreach ($pets as $pet) { ?>
                  <?php
                  $petImage = '../Paw Hubs/public/uploads/pets/default-pet.png';
                  if (!empty($pet['image']) && $pet['image'] !== 'default.png' && $pet['image'] !== 'default-pet.png') {
                    $petImage = '../Paw Hubs/public/uploads/pets/' . rawurlencode($pet['image']);
                  }
                  ?>
                  <li>
                    <a class="dropdown-item d-flex align-items-center gap-3 py-2 px-2 rounded-3 <?= $pet['id'] == ($selected_pet['id'] ?? null) ? 'active' : '' ?>"
                      href="?pet_id=<?= urlencode((string) $pet['id']) ?>">
                      <img src="<?= htmlspecialchars($petImage) ?>" class="rounded-circle pet-switcher-thumb" alt="<?= htmlspecialchars($pet['name']) ?>">
                      <div class="flex-grow-1">
                        <div class="fw-semibold small"><?= htmlspecialchars($pet["name"]); ?></div>
                        <div class="text-muted small" style="font-size: 0.75rem;"><?= htmlspecialchars($pet["species"]); ?> &middot; <?= htmlspecialchars((string) $pet["age"]); ?> yrs</div>
                      </div>
                      <?php if ($pet['id'] == ($selected_pet['id'] ?? null)): ?>
                        <span class="badge-soft b-ok">Active</span>
                      <?php endif; ?>
                    </a>
                  </li>
                <?php } ?>
                <li>
                  <hr class="dropdown-divider my-2">
                </li>
                <li>
                  <a class="dropdown-item text-center text-brand fw-semibold py-2" href="../Paw Hubs/public/index.php?url=user/profile#pets" id="addPetBtnHero">
                    <i class="bi bi-plus-circle me-1"></i> Add New Pet
                  </a>
                </li>
              </ul>
            </div>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-3">
            <span class="chip"><i class="bi bi-shield-check" style="color:var(--brand-deep)"></i> Vaccines: <?= (int) $completedVaccinesCount ?>/<?= (int) $totalVaccinesCount ?></span>
            <span class="chip"><i class="bi bi-cpu" style="color:#3d8b95"></i> Microchip: <?= htmlspecialchars($microchipStatus) ?></span>
            <span class="chip"><i class="bi bi-bandaid" style="color:#7f8f55"></i> Chronic: <?= htmlspecialchars($chronicConditionLabel) ?></span>
            <span class="chip"><i class="bi bi-stars" style="color:#7f8f55"></i> Loyalty: <?= (int) $loyalityPoints ?> pts</span>
            <span class="chip"><i class="bi bi-geo-alt text-danger"></i><?php echo $user_address; ?></span>
          </div>
        </div>
        <div class="text-center">
          <div class="health-score" style="--score: <?= (int) $healthScore ?>%;"><span><?= (int) $healthScore ?>%</span></div>
          <div class="small text-muted mt-2">Health Points</div>
        </div>
        <div class="d-none d-md-flex flex-column gap-2">
          <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#logEntryModal">
            <i class="bi bi-plus-lg"></i> Log Entry
        </div>
    </div>

    <!-- STATS -->
    <div class="row g-3 mb-4">
      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico teal"><svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"
              fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
              style="vertical-align:-0.15em">
              <path d="m18 2 4 4" />
              <path d="m17 7 3-3" />
              <path d="M19 9 8.7 19.3c-1 1-2.5 1-3.4 0l-.6-.6c-1-1-1-2.5 0-3.4L15 5" />
              <path d="m9 11 4 4" />
              <path d="m5 19-3 3" />
              <path d="m14 4 6 6" />
            </svg>
          </div>
          <div>
            <div class="v"><?= (int) $upcomingVaccinesCount ?></div>
            <div class="l">Upcoming Vaccines</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico green"><i class="bi bi-activity"></i></div>
          <div>
            <div class="v"><?php echo $selected_pet["weight"]; ?> kg</div>
            <div class="l">Current Weight</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico sky"><i class="bi bi-droplet"></i></div>
          <div>
            <div class="v">
              <?= $todayMetrics['water_intake'] !== null ? $todayMetrics['water_intake'] : '-' ?>
              <small class="text-muted fs-6">ml</small>
            </div>
            <div class="l">Water Intake</div>
          </div>
        </div>
      </div>
      <div class="col-6 col-lg-3">
        <div class="card-soft p-3 stat">
          <div class="ico mint"><i class="bi bi-heart-pulse"></i></div>
          <div>
            <div class="v"><?php
                            $latestBcsStmt = $connect->prepare("SELECT bcs_score FROM pet_weight_logs WHERE pet_id = ? ORDER BY logged_at DESC LIMIT 1");
                            $latestBcsStmt->execute([$selected_pet['id']]);
                            $latestBcs = $latestBcsStmt->fetchColumn();
                            ?>
              <?= $latestBcs ? "BCS {$latestBcs}/9" : "No Data" ?></div>
            <div class="l">Body Condition Score</div>
          </div>
        </div>
      </div>

    </div>

    <div class="row g-4">
      <!-- LEFT COLUMN -->
      <div class="col-lg-8">
        <!-- WEIGHT & NUTRITION -->
        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Weight & Nutrition Trend</h5>
              <div class="text-muted small">BCS-aware analytics with sudden-change alerts</div>
            </div>

            <div class="d-flex gap-1">
              <button class="tab-pill">1M</button>
              <button class="tab-pill active">6M</button>
              <button class="tab-pill">1Y</button>
              <button class="tab-pill">All</button>
            </div>
          </div>
          <canvas id="weightChart" height="110"></canvas>

          <?php if ($alert): ?>
            <div class="alert mt-3 mb-0 d-flex align-items-center gap-2 rounded-3 border-0"
              style="background:#fff5e6;color:#9a5b15">
              <i class="bi bi-exclamation-triangle-fill"></i>
              Sudden weight <?= $alert['type'] === 'gain' ? 'gain' : 'loss' ?> detected:
              <strong><?= $alert['value'] ?>% in <?= $alert['days'] ?> days</strong>
              (<?= $alert['old'] ?> to <?= $alert['new'] ?> kg). Consider reviewing diet & activity.
            </div>
          <?php endif; ?>
        </div>


        <!-- VACCINE SCHEDULER -->
        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Automated Vaccination Scheduler</h5>
              <div class="text-muted small">Auto-calculated by species, age & history</div>
            </div>

            <button class="btn-brand" data-bs-toggle="modal" data-bs-target="#scheduleVaccineModal">
              <i class="bi bi-calendar-plus"></i> Schedule
            </button>

          </div>
          <!-- VACCINE SCHEDULER -->
          <div class="vacc-list">
            <?php if (!empty($vaccines)): ?>
              <?php foreach ($vaccines as $index => $vaccine): ?>
                <?php
                $iconColors = ['teal', 'warn', 'danger', 'green', 'sky'];
                $iconColor = $iconColors[$index % count($iconColors)];

                $frequency = $vaccineOptions[$vaccine['vaccine_name']]['frequency'] ?? 'Scheduled';
                ?>

                <div class="vacc-row">
                  <div class="d-flex gap-3 align-items-center">
                    <div class="ico <?= $iconColor ?>" style="width:42px;height:42px;border-radius:12px">
                      <i class="bi bi-eyedropper"></i>
                    </div>

                    <div>
                      <div class="fw-bold"><?= htmlspecialchars($vaccine['vaccine_name']) ?></div>
                      <div class="text-muted small">
                        Due date &middot; <?= htmlspecialchars($frequency) ?>
                      </div>
                    </div>
                  </div>

                  <div class="text-end">
                    <div class="fw-bold"><?= htmlspecialchars($vaccine['formatted_date']) ?></div>
                    <span class="badge-soft b-<?= htmlspecialchars($vaccine['status_badge']) ?>">
                      <?= htmlspecialchars($vaccine['status_text']) ?>
                    </span>
                  </div>
                </div>
              <?php endforeach; ?>

            <?php else: ?>
              <div class="vacc-empty">
                No vaccines scheduled yet.
              </div>
            <?php endif; ?>
          </div>
        </div>



        <!-- CHRONIC -->
        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <div>
              <h5>Chronic Condition Tracker</h5>
              <div class="text-muted small">Daily metrics for ongoing care plans</div>
            </div>
          </div>
          <div class="row g-3">
            <div class="col-6 col-md-3">
              <div class="metric">
                <h6>Insulin (today)</h6>
                <div class="num">
                  <?= $todayMetrics['insulin'] ?? '-' ?>
                  <small class="text-muted fs-6">units</small>
                </div>
                <div class="small <?= ($trends['insulin'] ?? 0) > 0 ? 'trend-up' : 'trend-dn' ?>">
                  <i class="bi bi-<?= ($trends['insulin'] ?? 0) > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                  <?= abs($trends['insulin'] ?? 0) ?>% vs avg
                </div>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="metric">
                <h6>Water Intake</h6>
                <div class="num">
                  <?= $todayMetrics['water_intake'] ?? '-' ?>
                  <small class="text-muted fs-6">ml</small>
                </div>
                <div class="small <?= ($trends['water_intake'] ?? 0) > 0 ? 'trend-up' : 'trend-dn' ?>">
                  <i class="bi bi-<?= ($trends['water_intake'] ?? 0) > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                  <?= abs($trends['water_intake'] ?? 0) ?>% vs avg
                </div>
              </div>
            </div>

            <div class="col-6 col-md-3">
              <div class="metric">
                <h6>Mobility Score</h6>
                <div class="num">
                  <?= $todayMetrics['mobility_score'] ?? '-' ?>
                  <small class="text-muted fs-6">/10</small>
                </div>
                <div class="small <?= ($trends['mobility_score'] ?? 0) > 0 ? 'trend-up' : 'trend-dn' ?>">
                  <i class="bi bi-<?= ($trends['mobility_score'] ?? 0) > 0 ? 'arrow-up' : 'arrow-down' ?>"></i>
                  <?= abs($trends['mobility_score'] ?? 0) ?>% vs avg
                </div>
              </div>
            </div>
          </div>
          <div class="mt-3"><canvas id="chronicChart" height="80"></canvas></div>

        </div>
      </div>

      <!-- RIGHT COLUMN -->
      <div class="col-lg-4">
        <!-- TRAVEL PASSPORT -->
        <div class="passport mb-4">

          <div class="d-flex justify-content-between align-items-start">

            <div>
              <div class="text-uppercase small" style="letter-spacing:.18em;color:#dff1ec">Pet Travel Passport</div>
              <h4 class="mt-1 mb-0">
                <?= htmlspecialchars($selected_pet['name'] ?? 'Selected pet') ?>
                <span class="passport-number"><?= $passportNumber ? htmlspecialchars(' - ' . $passportNumber) : '' ?></span>
              </h4>
              <div class="passport-status mt-2 <?= $passportApproved ? 'is-approved' : 'is-pending' ?>">
                <i class="bi bi-<?= $passportApproved ? 'patch-check-fill' : 'hourglass-split' ?>"></i>
                <?= htmlspecialchars($passportStatusText) ?>
              </div>
            </div>
            <div class="seal"><i class="bi bi-patch-check"></i></div>
          </div>
          <div class="mt-3">
            <div class="row-line"><span>Destination</span><span><?= htmlspecialchars($passportDestination) ?></span></div>
            <div class="row-line"><span>Microchip</span><span><?= htmlspecialchars($microchipStatus . ' - ' . $microchipReference) ?></span></div>
            <div class="row-line"><span>Rabies Cert.</span><span><?= htmlspecialchars($rabiesStatus . ' - ' . $rabiesValidUntil) ?></span></div>
            <div class="row-line"><span>Health Cert.</span><span><?= htmlspecialchars($healthCertificateStatus) ?></span></div>
            <div class="row-line"><span>Tapeworm Tx.</span><span><?= htmlspecialchars($tapewormStatus) ?></span></div>
            <div class="row-line"><span>Vet Signature</span><span><?= htmlspecialchars($passportApproved ? $passportVetName : 'Waiting for approval') ?></span></div>
          </div>
          <div class="mt-3">
            <?php if ($passportApproved): ?>
              <button class="btn-brand w-100 passport-doc-btn" type="button" data-bs-toggle="modal" data-bs-target="#passportDocsModal">
                <i class="bi bi-file-earmark-richtext"></i> Generate Passport Docs & QR
              </button>
            <?php else: ?>
              <form method="POST" class="passport-request-form">
                <input type="hidden" name="action" value="request_passport">
                <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?? '' ?>">
                <input type="text" name="destination_country" class="form-control" placeholder="Destination country" style="border-radius:12px">
                <button class="btn-brand flex-grow-1" style="background:#fff;color:var(--brand-deep)">
                  <i class="bi bi-file-earmark-medical"></i> Request Passport
                </button>
              </form>
            <?php endif; ?>
            <form method="POST" class="mt-2">
              <input type="hidden" name="action" value="request_microchip_surgery">
              <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?? '' ?>">
              <?php if (!$passportApproved) { ?>
                <button class="btn-ghost w-100" type="submit">
                  <i class="bi bi-cpu"></i> Request Microchip Surgery
                </button>
              <?php } ?>
            </form>
          </div>

        </div>

        <!-- REMINDERS -->
        <div class="card-soft p-4 mb-4">
          <div class="section-title">
            <h5 class="mb-0">Reminders</h5>
            <a href="#" class="small text-decoration-none" style="color:var(--brand-deep)">View all &rarr;</a>
          </div>

          <?php if (!empty($vaccines)): ?>
            <?php foreach (array_slice($vaccines, 0, 3) as $vaccine): ?>
              <div class="d-flex align-items-center gap-3 py-3 border-bottom">
                <div class="ico warn" style="width:38px;height:38px;border-radius:12px;">
                  <i class="bi bi-eyedropper"></i>
                </div>

                <div class="flex-grow-1">
                  <div class="fw-semibold"><?= htmlspecialchars($vaccine['vaccine_name']) ?></div>
                  <div class="text-muted small">
                    Due <?= htmlspecialchars($vaccine['formatted_date']) ?>
                  </div>
                </div>

                <span class="badge-soft b-<?= htmlspecialchars($vaccine['status_badge']) ?>">
                  <?= htmlspecialchars($vaccine['status_text']) ?>
                </span>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted py-4">
              No vaccine reminders yet.
            </div>
          <?php endif; ?>
        </div>


        <!-- HEALTH RECORDS -->
        <div class="card-soft p-4">
          <div class="section-title">
            <h5>Health Records</h5>
            <?php if ($vetCanAdd): ?>
              <button class="btn-ghost btn-sm" data-bs-toggle="modal" data-bs-target="#addHealthRecordModal">
                <i class="bi bi-plus-lg"></i> Add
              </button>
            <?php endif; ?>
          </div>

          <div class="timeline">
            <?php if (!empty($healthRecords)): ?>
              <?php foreach ($healthRecords as $record): ?>
                <div class="tl-item">
                  <div class="fw-semibold"><?= htmlspecialchars($record['title']) ?></div>
                  <div class="text-muted small">
                    <?= date('M d, Y', strtotime($record['record_date'])) ?>
                    <?php
                    $stmt = $connect->prepare("SELECT username FROM users WHERE id = ?");
                    $stmt->execute([$_SESSION['user_id']]);
                    $vetName = $stmt->fetchColumn() ?: 'Veterinarian';
                    ?>
                    &middot; <?= htmlspecialchars($vetName) ?>
                  </div>
                  <div class="small mt-1"><?= nl2br(htmlspecialchars($record['description'])) ?></div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center text-muted py-4">
                <i class="bi bi-journal-text fs-3 mb-2 d-block"></i>
                No health records yet.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Add Health Record Modal (Vet Only) -->
        <?php if ($vetCanAdd): ?>
          <div class="modal fade" id="addHealthRecordModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
                <div class="modal-header border-0 pb-0 pt-3 px-4">
                  <h5 class="modal-title fw-bold">Add Health Record</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                  <form method="POST" action="">
                    <input type="hidden" name="action" value="add_health_record">
                    <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?? '' ?>">

                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Record Title</label>
                      <input type="text" name="title" class="form-control" placeholder="e.g., Annual Checkup" required>
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Date</label>
                      <input type="date" name="record_date" class="form-control" value="<?= date('Y-m-d') ?>">
                    </div>

                    <div class="mb-3">
                      <label class="form-label fw-semibold small">Description</label>
                      <textarea name="description" class="form-control" rows="3" placeholder="Clinical notes, findings, recommendations..." required></textarea>
                    </div>

                    <div class="d-flex gap-2">
                      <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn-brand flex-fill">Save Record</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  <?php } else { ?>
    <h5 style="padding-left: 40%;">No pets added , Add pet to track!</h5>
  <?php } ?>
  <?php if ($selected_pet && $passportApproved): ?>
    <div class="modal fade" id="passportDocsModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content passport-doc-modal">
          <div class="modal-header border-0 pb-0">
            <div>
              <div class="text-uppercase small text-muted fw-semibold">Approved passport documents</div>
              <h5 class="modal-title fw-bold"><?= htmlspecialchars($selected_pet['name']) ?> travel passport</h5>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <div class="passport-doc-layout">
              <section class="passport-document" id="passportDocument">
                <div class="passport-doc-header">
                  <div>
                    <div class="text-uppercase small text-muted fw-semibold">PawCare Pet Passport</div>
                    <h3><?= htmlspecialchars($passportNumber ?? 'Approved passport') ?></h3>
                  </div>
                  <div class="passport-doc-seal"><i class="bi bi-patch-check-fill"></i></div>
                </div>

                <div class="passport-doc-grid">
                  <div><span>Pet name</span><strong><?= htmlspecialchars($selected_pet['name']) ?></strong></div>
                  <div><span>Species</span><strong><?= htmlspecialchars($selected_pet['species']) ?></strong></div>
                  <div><span>Gender</span><strong><?= htmlspecialchars($selected_pet['gender'] ?? 'Unknown') ?></strong></div>
                  <div><span>Age</span><strong><?= htmlspecialchars((string) $selected_pet['age']) ?> yrs</strong></div>
                  <div><span>Weight</span><strong><?= htmlspecialchars((string) $selected_pet['weight']) ?> kg</strong></div>
                  <div><span>Owner</span><strong><?= htmlspecialchars($username) ?></strong></div>
                  <div><span>Destination</span><strong><?= htmlspecialchars($passportDestination) ?></strong></div>
                  <div><span>Issued</span><strong><?= htmlspecialchars(formatDisplayDate($passportIssuedDate)) ?></strong></div>
                  <div><span>Microchip</span><strong><?= htmlspecialchars($microchipStatus . ' - ' . $microchipReference) ?></strong></div>
                  <div><span>Rabies certificate</span><strong><?= htmlspecialchars($rabiesStatus . ' - ' . $rabiesValidUntil) ?></strong></div>
                  <div><span>Health certificate</span><strong><?= htmlspecialchars($healthCertificateStatus) ?></strong></div>
                  <div><span>Vet signature</span><strong><?= htmlspecialchars($passportVetName) ?></strong></div>
                </div>
              </section>

              <aside class="passport-qr-panel">
                <div id="passportQrCode" data-doc-url="<?= htmlspecialchars($passportDocUrl) ?>"></div>

                <button class="btn-brand w-100 mt-2" type="button" onclick="setTimeout(() => window.print(), 300)">
                  <i class="bi bi-printer"></i> Print / Save PDF
                </button>
              </aside>
            </div>
          </div>
        </div>
      </div>
    </div>
  <?php endif; ?>
  <div class="modal fade" id="scheduleVaccineModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
        <div class="modal-header border-0 pb-0 pt-3 px-4">
          <h5 class="modal-title fw-bold">Schedule Vaccine</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body px-4 py-3">
          <form method="POST">
            <input type="hidden" name="action" value="add_vaccine">
            <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?? '' ?>">

            <div class="mb-3">
              <label class="form-label fw-semibold small">Vaccine</label>
              <select name="vaccine_name" class="form-control" required>
                <option value="">Choose vaccine</option>
                <?php
                $scheduledNames = array_column($vaccines, 'vaccine_name');
                ?>

                <?php foreach ($vaccineOptions as $name => $info): ?>
                  <?php if (!in_array($name, $scheduledNames)): ?>
                    <option value="<?= htmlspecialchars($name) ?>">
                      <?= htmlspecialchars($name) ?> - <?= htmlspecialchars($info['frequency']) ?>
                    </option>
                  <?php endif; ?>
                <?php endforeach; ?>

              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-semibold small">Due Date</label>
              <input type="date" name="due_date" class="form-control" required>
            </div>

            <div class="d-flex gap-2">
              <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn-brand flex-fill">Save Schedule</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <!-- Log Entry Modal -->
  <div class="modal fade" id="logEntryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content card-soft p-0" style="border-radius: 22px; overflow: hidden;">
        <div class="modal-header border-0 pb-0 pt-3 px-4">
          <h5 class="modal-title fw-bold">Log Entry</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body px-4 py-3">
          <form id="logEntryForm" method="POST">
            <input type="hidden" name="action" value="log_entry">
            <input type="hidden" name="pet_id" value="<?= $selected_pet['id'] ?? '' ?>">

            <!-- Entry Type Toggle -->
            <div class="mb-3">
              <label class="form-label fw-semibold small">Entry Type</label>
              <div class="d-flex gap-2">
                <input type="radio" class="btn-check" name="entry_type" id="typeWeight" value="weight" checked>
                <label class="btn btn-outline-secondary flex-fill" for="typeWeight">Weight Only</label>

                <input type="radio" class="btn-check" name="entry_type" id="typeChronic" value="chronic">
                <label class="btn btn-outline-secondary flex-fill" for="typeChronic">Weight + Chronic</label>
              </div>
            </div>

            <!-- Weight (Always Visible) -->
            <div class="mb-3">
              <label class="form-label fw-semibold small">Weight (kg)</label>
              <input type="number" step="0.01" name="weight" class="form-control" placeholder="e.g., 28.4" required>
            </div>

            <!-- Chronic Fields (Hidden by default) -->
            <div id="chronicFields" class="d-none border-top pt-3 mt-3">
              <div class="row g-3">
                <div class="col-6">
                  <label class="form-label fw-semibold small">Insulin (units)</label>
                  <input type="number" step="0.1" name="insulin_units" class="form-control" placeholder="0">
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold small">Water Intake (ml)</label>
                  <input type="number" name="water_ml" class="form-control" placeholder="0">
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold small">Mobility Score (1-10)</label>
                  <input type="number" name="mobility_score" class="form-control" min="1" max="10" placeholder="0">
                </div>
                <div class="col-6">
                  <label class="form-label fw-semibold small">BCS (1-9)</label>
                  <input type="number" name="bcs_score" class="form-control" min="1" max="9" placeholder="0">
                </div>
              </div>
            </div>

            <div class="mt-4 d-flex gap-2">
              <button type="button" class="btn btn-ghost flex-fill" data-bs-dismiss="modal">Cancel</button>
              <button type="submit" class="btn-brand flex-fill">Save Log</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  </div>
  <?php if (isset($_GET['pet_id']) && $selected_pet): ?>
    <div id="phpToast" class="position-fixed bottom-0 end-0 m-3 card-soft px-4 py-3 shadow"
      style="z-index: 1100; animation: slideIn 0.3s ease;">
      <div class="fw-semibold text-brand-deep">
        Switched to <?= htmlspecialchars($selected_pet['name']) ?> 🐾
      </div>
    </div>
    <script>
      setTimeout(() => {
        const toast = document.getElementById('phpToast');
        if (toast) {
          toast.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
          toast.style.opacity = '0';
          toast.style.transform = 'translateX(100%)';
          setTimeout(() => toast.remove(), 300);
        }
      }, 1000);
    </script>
  <?php endif; ?>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
  <script>
    window.PET_DATA = {
      weightLogs: <?= json_encode($weightData ?? [], JSON_NUMERIC_CHECK) ?>,
      alert: <?= json_encode($alert ?? null) ?>
    };

    document.addEventListener('DOMContentLoaded', () => {
      if (typeof initRealWeightChart === 'function' && window.PET_DATA.weightLogs?.length > 0) {
        initRealWeightChart(window.PET_DATA.weightLogs, window.PET_DATA.alert);
      }
    });


    document.getElementById('logEntryModal')?.addEventListener('show.bs.modal', function() {
      const petIdInput = document.querySelector('#logEntryForm input[name="pet_id"]');
      if (petIdInput) petIdInput.value = <?= json_encode($selected_pet['id'] ?? '') ?>;
      document.getElementById('chronicFields')?.classList.add('d-none');
      const weightType = document.getElementById('typeWeight');
      if (weightType) weightType.checked = true;
    });


    document.querySelectorAll('input[name="entry_type"]').forEach(radio => {
      radio.addEventListener('change', function() {
        const chronicDiv = document.getElementById('chronicFields');
        if (this.value === 'chronic') {
          chronicDiv.classList.remove('d-none');
        } else {
          chronicDiv.classList.add('d-none');
        }
      });
    });
    window.PET_DATA = {
      weightLogs: <?= json_encode($weightData ?? [], JSON_NUMERIC_CHECK) ?>,
      alert: <?= json_encode($alert ?? null) ?>,
      chronicLogs: <?= json_encode($chronicLogs, JSON_NUMERIC_CHECK) ?>
    };
  </script>

  <script src="pet-health.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
  <script>
    document.getElementById('passportDocsModal')?.addEventListener('shown.bs.modal', () => {
      const qrTarget = document.getElementById('passportQrCode');
      if (!qrTarget || qrTarget.dataset.rendered === '1') return;

      const docUrl = qrTarget.dataset.docUrl || window.location.href;
      if (typeof QRCode === 'function') {
        new QRCode(qrTarget, {
          text: docUrl,
          width: 160,
          height: 160,
          colorDark: '#1f2a36',
          colorLight: '#ffffff',
          correctLevel: QRCode.CorrectLevel.H
        });
        qrTarget.dataset.rendered = '1';
      } else {
        const fallback = document.createElement('a');
        fallback.href = docUrl;
        fallback.textContent = 'Open passport documents';
        fallback.target = '_blank';
        qrTarget.appendChild(fallback);
      }
    });
  </script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const params = new URLSearchParams(window.location.search);
      if (params.get('print') === '1') {
        const modal = document.getElementById('passportDocsModal');
        if (modal) {
          modal.classList.remove('fade');
          modal.classList.add('show');
          modal.style.display = 'block';
          modal.style.opacity = '1';
          modal.style.visibility = 'visible';
          modal.style.position = 'static';
          modal.style.transform = 'none';
          modal.setAttribute('aria-hidden', 'false');
        }
        setTimeout(() => window.print(), 200);
      }
    });
  </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <?php require_once $pawHubsPath . '/app/views/partials/footer.php'; ?>

</body>

</html>