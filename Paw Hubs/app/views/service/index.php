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

$services = $services ?? [];
$pageTitle = $pageTitle ?? 'Services';
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
        .services-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: var(--soft, #f5faf8);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
        }
        .service-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 14px 40px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .service-card:hover {
            transform: translateY(-8px);
        }
        .service-category {
            display: inline-block;
            padding: 4px 12px;
            background: #e6f4f1;
            color: var(--teal-dark);
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .price {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--teal-dark);
        }
        .btn-book {
            margin-top: auto;
            background: linear-gradient(135deg, #6BB5A8, #94CDD3);
            color: white;
            padding: 14px;
            text-align: center;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <div class="services-container">
        <div style="text-align:center; margin-bottom:40px;">
            <h1>Our Services</h1>
            <p class="muted">Professional pet care services tailored for your furry friends</p>
        </div>

        <?php if (isset($_SESSION['booking_confirmed'])): ?>
            <div style="background:#d4edda; color:#155724; padding:1rem; border-radius:16px; margin-bottom:25px; text-align:center;">
                <strong>✅ <?= e($_SESSION['booking_message'] ?? 'Booking submitted successfully!') ?></strong>
            </div>
            <?php unset($_SESSION['booking_confirmed'], $_SESSION['booking_message']); ?>
        <?php endif; ?>

        <div class="service-grid">
            <?php foreach ($services as $s): ?>
                <div class="service-card">
                    <div class="service-category"><?= e($s['category'] ?? 'General') ?></div>
                    <h2 style="margin:15px 0 8px;"><?= e($s['name']) ?></h2>
                    <p style="color:#666; flex-grow:1;"><?= nl2br(e($s['description'])) ?></p>

                    <div style="margin:15px 0;">
                        <strong class="price">$<?= number_format($s['price'], 2) ?></strong>
                        <?php if (!empty($s['duration'])): ?>
                            <small style="color:#888;"> / <?= e($s['duration']) ?></small>
                        <?php endif; ?>
                    </div>

                    <?php if ($s['discount_percentage'] > 0): ?>
                        <div style="color:#e74c3c; font-weight:600;">
                            <?= $s['discount_percentage'] ?>% OFF
                        </div>
                    <?php endif; ?>

                    <a href="<?= app_url('service/reserve/' . $s['id']) ?>" class="btn-book">
                        <i class="fas fa-calendar-plus"></i> Book Now
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php require_once '../app/views/partials/footer.php'; ?>

</body>
</html>