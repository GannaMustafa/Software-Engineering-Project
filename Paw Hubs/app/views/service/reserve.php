<?php
if (!function_exists('asset')) {
    function asset($path) {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base === '/' || $base === '.') $base = '';
        return $base . '/' . ltrim($path, '/');
    }
}

if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
}

$service = $service ?? [];
$pets = $pets ?? [];
$pageTitle = $pageTitle ?? 'Book Service';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?> | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/Style.css">

    <style>
        .reserve-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: var(--soft, #f5faf8);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .service-summary {
            background: white;
            padding: 28px;
            border-radius: 24px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .form-card {
            background: white;
            padding: 32px;
            border-radius: 24px;
            box-shadow: 0 14px 40px rgba(0,0,0,0.05);
        }
        .field {
            margin-bottom: 24px;
        }
        .field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--ink, #2f4f4f);
        }
        .field input, .field select, .field textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #dfe7e4;
            border-radius: 16px;
            font-size: 1rem;
        }
        .field textarea {
            min-height: 130px;
            resize: vertical;
        }
        .btn-book {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #6BB5A8, #94CDD3);
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        .btn-book:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(107,181,168,0.3);
        }
    </style>
</head>
<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <div class="reserve-container">
        <div style="text-align:center; margin-bottom:30px;">
            <h1>Book Service</h1>
            <p class="muted">Complete the form below to request this service</p>
        </div>

        <?php if (isset($_SESSION['booking_confirmed'])): ?>
            <div style="background:#d4edda; color:#155724; padding:1.5rem; border-radius:16px; margin-bottom:25px; text-align:center;">
                <strong>Your booking has been submitted successfully!</strong><br>
                You will be notified once the provider confirms it.
            </div>
            <?php unset($_SESSION['booking_confirmed']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['booking_error'])): ?>
            <div style="background:#fdecea; color:#721c24; padding:1rem; border-radius:16px; margin-bottom:25px;">
                <?= e($_SESSION['booking_error']) ?>
            </div>
            <?php unset($_SESSION['booking_error']); ?>
        <?php endif; ?>

        <!-- Service Summary -->
        <div class="service-summary">
            <h2><?= e($service['name']) ?></h2>
            <p><strong>Category:</strong> <?= e($service['category'] ?? 'General') ?></p>
            <p><strong>Provider:</strong> <?= e($service['business_name'] ?? 'Available Provider') ?></p>
            <p><strong>Price:</strong> <span style="font-size:1.4rem; font-weight:700; color:var(--teal-dark);">
                $<?= number_format($service['price'], 2) ?>
            </span></p>
            <?php if (!empty($service['duration'])): ?>
                <p><strong>Duration:</strong> <?= e($service['duration']) ?></p>
            <?php endif; ?>
            <?php if ($service['discount_percentage'] > 0): ?>
                <p style="color:#e74c3c;"><strong>Discount:</strong> <?= $service['discount_percentage'] ?>% OFF</p>
            <?php endif; ?>
            <?php if (!empty($service['description'])): ?>
                <p style="margin-top:15px;"><?= nl2br(e($service['description'])) ?></p>
            <?php endif; ?>
        </div>

        <!-- Booking Form -->
        <div class="form-card">
            <form method="POST" action="<?= htmlspecialchars(app_url('service/confirmReservation')) ?>">
                <input type="hidden" name="service_id" value="<?= (int)$service['id'] ?>">

                <div class="field">
                    <label for="pet_id">Select Your Pet *</label>
                    <select id="pet_id" name="pet_id" required>
                        <option value="">-- Choose a Pet --</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?= (int)$pet['id'] ?>">
                                <?= e($pet['name']) ?> (<?= e($pet['species']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="field">
                    <label for="notes">Special Instructions / Notes</label>
                    <textarea id="notes" name="notes" rows="5" 
                        placeholder="Any special requirements, allergies, behavior notes, etc..."></textarea>
                </div>

                <button type="submit" class="btn-book">
                    <i class="fas fa-calendar-check"></i> Confirm Booking Request
                </button>
            </form>
        </div>

        <div style="text-align:center; margin-top:25px;">
            <a href="<?= app_url('service/index') ?>" style="color:var(--muted); text-decoration:underline;">
                ← Back to All Services
            </a>
        </div>
    </div>

    <?php require_once '../app/views/partials/footer.php'; ?>

</body>
</html>