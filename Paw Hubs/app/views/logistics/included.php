<?php
$dashboard = $dashboard ?? [];
$role = $role ?? ($dashboard['role'] ?? 'pet_owner');
$capabilities = $dashboard['capabilities'] ?? [];
$stats = $dashboard['stats'] ?? [];
$backUrl = $role === 'admin'
    ? 'index.php?url=admin/index'
    : ($role === 'service_provider' ? 'index.php?url=logistics/index' : 'index.php?url=home/index');

if (!function_exists('e')) {
    function e($value)
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Included Logistics Logic | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/logistics.css">
</head>
<body>
<div class="app-frame">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-list-check"></i><span>Included Logic</span></div>

        <div>
            <p class="menu-label">Included Section</p>
            <nav class="menu" aria-label="Included logistics logic">
                <?php foreach ($capabilities as $capability): ?>
                    <a href="#feature-<?= (int) $capability['number'] ?>">
                        <i class="fas <?= e($capability['icon']) ?>"></i>
                        <?= e($capability['number']) ?>. <?= e($capability['title']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        </div>

        <div class="sidebar-footer">
            <nav class="menu">
                <a href="index.php?url=logistics/index"><i class="fas fa-route"></i> Page sections</a>
                <a href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Payment report</a>
                <a href="<?= e($backUrl) ?>"><i class="fas fa-arrow-left"></i> Back to workspace</a>
            </nav>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <h1>Included Logic</h1>
                <p>The required provider payment, reporting, and income analytics features are separated here so the implementation checklist stays easy to review.</p>
            </div>
            <div class="action-row">
                <span class="role-pill"><i class="fas fa-user-shield"></i> <?= e(str_replace('_', ' ', $role)) ?> access</span>
                <a class="action-btn" href="index.php?url=logistics/index"><i class="fas fa-route"></i> Page Sections</a>
                <a class="action-btn primary" href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Payment Report</a>
            </div>
        </header>

        <section class="logic-note">
            <h2>Logistics + Analytics Page</h2>
            <p>These cards match the included section from the requirement image and point to the database-backed implementation handled by the service layer.</p>
        </section>

        <section class="stats" aria-label="Implementation summary">
            <?php foreach ($stats as $stat): ?>
                <article class="stat-card">
                    <header>
                        <div>
                            <span><?= e($stat['label']) ?></span>
                            <strong><?= e($stat['value']) ?></strong>
                        </div>
                        <div class="stat-icon tone-<?= e($stat['tone']) ?>"><i class="fas <?= e($stat['icon']) ?>"></i></div>
                    </header>
                    <small><?= e($stat['hint']) ?></small>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="features-grid" aria-label="Required capabilities">
            <?php foreach ($capabilities as $capability): ?>
                <article class="feature-card" id="feature-<?= (int) $capability['number'] ?>">
                    <div class="feature-head">
                        <div class="feature-icon"><i class="fas <?= e($capability['icon']) ?>"></i></div>
                        <span class="feature-number">#<?= (int) $capability['number'] ?></span>
                    </div>
                    <h2><?= e($capability['title']) ?></h2>
                    <p><?= e($capability['summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="panel">
            <header class="page-head">
                <div>
                    <h2>Where each part lives</h2>
                    <p>The structure stays readable: database rows in the model/service boundary, calculations in the service, routing in the controller, and UI in the views.</p>
                </div>
                <span class="badge success"><i class="fas fa-code-branch"></i> MVC split</span>
            </header>

            <div class="report-grid">
                <article class="report-card">
                    <h3>Model</h3>
                    <p>Defines provider payment, service booking, completion report, and income history data access.</p>
                </article>
                <article class="report-card">
                    <h3>Service</h3>
                    <p>Calculates commission, tax, provider earnings, platform due amounts, analytics totals, and PDF report data.</p>
                </article>
                <article class="report-card">
                    <h3>Views</h3>
                    <p>Separates page sections, included logic, and payment report review into clear screens.</p>
                </article>
            </div>

            <div class="section-footer">
                <a class="action-btn" href="index.php?url=logistics/index"><i class="fas fa-route"></i> Open Page Sections</a>
                <a class="action-btn primary" href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Review Payment Data</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
