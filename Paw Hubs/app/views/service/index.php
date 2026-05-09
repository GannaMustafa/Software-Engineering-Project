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

$services = $services ?? [];
$pageTitle = $pageTitle ?? 'Find a Service Provider';
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

        .service-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        .service-card {
            display: flex;
            flex-direction: column;
        }

        .btn-reserve {
            margin-top: auto;
            display: inline-block;
            background: var(--teal);
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            text-align: center;
            transition: background 0.3s;
        }

        .btn-reserve:hover {
            background: var(--teal-dark);
        }
    </style>
</head>

<body>
    <?php require_once '../app/views/partials/navbar.php'; ?>

    <main class="panel service-page">
        <section class="page-section">
            <header class="page-header">
                <h1><?= htmlspecialchars($pageTitle) ?></h1>
                <p class="muted">Browse trusted pet sitters and care options for your pet.</p>
            </header>

            <?php if (isset($_SESSION['booking_confirmed'])): ?>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong><?= htmlspecialchars($_SESSION['booking_message'] ?? 'Booking submitted!') ?></strong>
                        <p style="margin: 0.25rem 0 0; font-size: 0.9rem;">Your booking status will be "Confirmed" if the sitter is available, otherwise it will be "Pending" until a sitter accepts.</p>
                    </div>
                </div>
                <?php unset($_SESSION['booking_confirmed']); unset($_SESSION['booking_message']); ?>
            <?php endif; ?>

            <?php if (empty($services)): ?>
                <div class="card no-services-card">
                    <p class="muted">No pet sitters are available right now. Please check back soon.</p>
                </div>
            <?php else: ?>
                <div class="service-grid">
                    <?php foreach ($services as $service): ?>
                        <article class="card service-card">
                            <div class="service-card-header">
                                <div>
                                    <h2><?= htmlspecialchars($service['business_name'] ?: 'Pet Sitter') ?></h2>
                                    <p class="muted"><?= htmlspecialchars($service['name']) ?></p>
                                </div>
                                <span class="service-badge"><i class="fas fa-award"></i> Verified</span>
                            </div>

                            <?php if (!empty($service['description'])): ?>
                                <p><?= nl2br(htmlspecialchars($service['description'])) ?></p>
                            <?php endif; ?>

                            <div class="service-card-meta">
                                <span><strong>Rating:</strong> <?= htmlspecialchars($service['rating'] !== null ? number_format((float)$service['rating'], 1) : 'N/A') ?></span>
                                <?php $basePrice = getServicePrice($service['name']); ?>
                                <?php $discountedPrice = $basePrice * (1 - ($service['discount_percentage'] ?? 0) / 100); ?>
                                <span><strong>Price:</strong> $<?= number_format($discountedPrice, 2) ?>/session</span>
                                <?php if (($service['discount_percentage'] ?? 0) > 0): ?>
                                    <span><strong>Discount:</strong> <?= htmlspecialchars(number_format((float)($service['discount_percentage'] ?? 0), 1)) ?>%</span>
                                <?php endif; ?>
                            </div>

                            <a href="<?= htmlspecialchars(app_url('service/reserve/' . $service['id'])) ?>" class="btn-reserve">Reserve Sitter</a>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>
