<?php
if (!function_exists('asset')) {
    function asset($path)
    {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base === '/' || $base === '.') {
            $base = '';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

function getServicePrice($serviceName) {
    $prices = [
        'Pet Sitting - Basic' => 25.00,
        'Pet Sitting - Premium' => 40.00,
        'Overnight Pet Sitting' => 60.00,
        'Dog Walking' => 15.00,
    ];
    return $prices[$serviceName] ?? 20.00;
}

$service = $service ?? [];
$pets = $pets ?? [];
$booking = $booking ?? null;
$pageTitle = $pageTitle ?? 'Reserve Service';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?> | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/Style.css">
    <style>
        :root {
            --teal: #6BB5A8;
            --teal-dark: #4f9186;
            --green: #9BC870;
            --olive: #CAD7A5;
            --mint: #C8E4D6;
            --sky: #94CDD3;
            --ink: #2f4f4f;
            --muted: #718096;
            --line: #d8ebe5;
            --panel: #ffffff;
            --soft: #f5faf8;
        }

        .reserve-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .service-summary {
            background: var(--soft);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 1rem;
        }

        .btn-submit {
            background: var(--teal);
            color: white;
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1rem;
        }

        .btn-submit:hover {
            background: var(--teal-dark);
        }
    </style>
</head>

<body>
    <?php require_once '../app/views/partials/navbar.php'; ?>

    <main class="panel">
        <section class="page-section">
            <header class="page-header">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="muted"><?= $booking ? 'Your reservation has been confirmed!' : 'Fill in the details to reserve this service.' ?></p>
            </header>

            <div class="reserve-form">
                <?php if ($booking): ?>
                    <div class="booking-confirmation card">
                        <h2>Reservation Confirmed</h2>
                        <p><strong>Booking ID:</strong> #<?= htmlspecialchars($booking['id']) ?></p>
                        <p><strong>Service:</strong> <?= htmlspecialchars($booking['service_name']) ?></p>
                        <p><strong>Provider:</strong> <?= htmlspecialchars($booking['provider_name']) ?></p>
                        <p><strong>Pet:</strong> <?= htmlspecialchars($booking['pet_name']) ?></p>
                        <p><strong>Start Date:</strong> <?= htmlspecialchars($booking['start_date']) ?></p>
                        <p><strong>End Date:</strong> <?= htmlspecialchars($booking['end_date']) ?></p>
                        <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($booking['status'])) ?></p>
                        <?php if (!empty($booking['special_instructions'])): ?>
                            <p><strong>Special Instructions:</strong> <?= nl2br(htmlspecialchars($booking['special_instructions'])) ?></p>
                        <?php endif; ?>
                        <p><strong>Created At:</strong> <?= htmlspecialchars($booking['created_at']) ?></p>
                        <a href="<?= htmlspecialchars(app_url('service/index')) ?>" class="btn-submit">Back to Services</a>
                    </div>
                <?php else: ?>
                    <div class="service-summary card">
                        <h2>Service Details</h2>
                        <p><strong>Service:</strong> <?= htmlspecialchars($service['name']) ?></p>
                        <p><strong>Provider:</strong> <?= htmlspecialchars($service['business_name']) ?></p>
                        <?php $basePrice = getServicePrice($service['name']); ?>
                        <?php $discountPercent = $service['discount_percentage'] ?? 0; ?>
                        <?php $multiPetDiscount = count($pets) > 1 ? 5 : 0; ?>
                        <?php $totalDiscount = min(100, $discountPercent + $multiPetDiscount); ?>
                        <?php $finalPrice = $basePrice * (1 - $totalDiscount / 100); ?>
                        <p><strong>Base Price:</strong> $<?= number_format($basePrice, 2) ?></p>
                        <p><strong>Discounts:</strong> <?= $discountPercent ?>% (service)<?php if ($multiPetDiscount > 0): ?> + <?= $multiPetDiscount ?>% (multi-pet)<?php endif; ?> = <?= $totalDiscount ?>%</p>
                        <p><strong>Final Price:</strong> $<?= number_format($finalPrice, 2) ?></p>
                        <?php $availableSlots = $availableSlots ?? 0; ?>
                        <?php $capacity = $capacity ?? 0; ?>
                        
                    </div>

                    <form action="<?= htmlspecialchars(app_url('service/processReservation')) ?>" method="POST">
                        <input type="hidden" name="service_id" value="<?= htmlspecialchars($service['id']) ?>">

                        <div class="form-group">
                            <label for="pet_id">Select Pet:</label>
                            <select name="pet_id" id="pet_id" required>
                                <option value="">Choose a pet</option>
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?= htmlspecialchars($pet['id']) ?>"><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['species']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date:</label>
                            <input type="date" name="start_date" id="start_date" required>
                        </div>

                        <div class="form-group">
                            <label for="end_date">End Date:</label>
                            <input type="date" name="end_date" id="end_date" required>
                        </div>

                        <div class="form-group">
                            <label for="special_instructions">Special Instructions:</label>
                            <textarea name="special_instructions" id="special_instructions" rows="4"></textarea>
                        </div>

                        <button type="submit" class="btn-submit">Reserve Service</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>