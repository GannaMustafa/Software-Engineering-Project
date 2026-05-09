<?php
$dashboard = $dashboard ?? [];
$role = $role ?? ($dashboard['role'] ?? 'service_provider');
$provider = $dashboard['provider'] ?? null;
$sections = $dashboard['sections'] ?? [];
$stats = $dashboard['stats'] ?? [];
$payments = $dashboard['vendors'] ?? [];
$bookings = $dashboard['batches'] ?? [];
$reports = $dashboard['incidents'] ?? [];
$behaviorProfiles = $dashboard['behaviorProfiles'] ?? [];
$income = $dashboard['income'] ?? ['monthly' => [], 'sources' => [], 'providers' => []];
$backUrl = $role === 'admin' ? 'index.php?url=admin/index' : 'index.php?url=home/index';

if (!function_exists('e')) {
    function e($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('money')) {
    function money($amount) { return 'EGP ' . number_format((float) $amount, 0); }
}
if (!function_exists('rate_percent')) {
    function rate_percent($rate) { return number_format((float) $rate * 100, 1) . '%'; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Provider Payments | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/logistics.css">
</head>
<body>
<div class="app-frame">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-money-bill-transfer"></i><span>Provider Income</span></div>
        <div>
            <p class="menu-label">Page Sections</p>
            <nav class="menu" aria-label="Provider payment sections">
                <?php foreach ($sections as $section): ?>
                    <a href="#<?= e($section['id']) ?>"><i class="fas <?= e($section['icon']) ?>"></i> <?= e($section['label']) ?></a>
                <?php endforeach; ?>
            </nav>
        </div>
        <div class="sidebar-footer">
            <nav class="menu">
                <a href="index.php?url=logistics/included"><i class="fas fa-list-check"></i> Included logic</a>
                <a href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Income report PDF</a>
                <a href="<?= e($backUrl) ?>"><i class="fas fa-arrow-left"></i> Back to workspace</a>
            </nav>
        </div>
    </aside>

    <main class="content">
        <header class="topbar">
            <div>
                <h1>Service Provider Payments</h1>
                <p>Completion reports, owner cash payments, commission, tax, and provider income history are loaded from the database.</p>
            </div>
            <div class="action-row">
                <span class="role-pill"><i class="fas fa-user-tie"></i> <?= e($provider['business_name'] ?? str_replace('_', ' ', $role)) ?></span>
                <a class="action-btn primary" href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Downloadable Report</a>
            </div>
        </header>

        <?php if (!empty($message)): ?><div class="logic-note"><p><?= e($message) ?></p></div><?php endif; ?>
        <?php if (!empty($errors)): ?>
            <div class="logic-note"><?php foreach ($errors as $error): ?><p><?= e($error) ?></p><?php endforeach; ?></div>
        <?php endif; ?>

        <section class="stats" aria-label="Provider payment summary">
            <?php foreach ($stats as $stat): ?>
                <article class="stat-card">
                    <header>
                        <div><span><?= e($stat['label']) ?></span><strong><?= e($stat['value']) ?></strong></div>
                        <div class="stat-icon tone-<?= e($stat['tone']) ?>"><i class="fas <?= e($stat['icon']) ?>"></i></div>
                    </header>
                    <small><?= e($stat['hint']) ?></small>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="panel" id="provider-payments">
            <header class="page-head">
                <div>
                    <h2>Provider payments</h2>
                    <p>Each row shows what the owner paid in cash, what the provider keeps, and what must be transferred to the system.</p>
                </div>
                <span class="badge info"><i class="fas fa-scale-balanced"></i> Commission + tax</span>
            </header>
            <div class="table-scroll">
                <table>
                    <thead>
                    <tr>
                        <th>Service</th><th>Owner</th><th>Cash total</th><th>Provider keeps</th><th>Commission</th><th>Tax</th><th>Due to system</th><th>Status</th><th>Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><strong><?= e($payment['service_name']) ?></strong><span class="muted">Payment #<?= (int) $payment['id'] ?></span></td>
                            <td><?= e($payment['owner_name']) ?><br><span class="muted"><?= e($payment['pet_name'] ?? '') ?></span></td>
                            <td><?= money($payment['gross_amount']) ?></td>
                            <td><strong><?= money($payment['provider_earning']) ?></strong></td>
                            <td><?= money($payment['commission_amount']) ?><br><span class="muted"><?= rate_percent($payment['commission_rate']) ?></span></td>
                            <td><?= money($payment['tax_amount']) ?><br><span class="muted"><?= rate_percent($payment['tax_rate']) ?></span></td>
                            <td><?= money($payment['platform_total_due']) ?></td>
                            <td><span class="badge <?= e($payment['status_class']) ?>"><?= e($payment['payment_status']) ?> / <?= e($payment['transfer_status']) ?></span></td>
                            <td>
                                <?php if ($role === 'service_provider' && $payment['payment_status'] !== 'cash_collected'): ?>
                                    <form method="post"><input type="hidden" name="action" value="confirm_cash_payment"><input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>"><button class="confirm-btn" type="submit">Cash received</button></form>
                                <?php elseif ($role === 'service_provider' && $payment['transfer_status'] !== 'transferred'): ?>
                                    <form method="post"><input type="hidden" name="action" value="mark_transferred"><input type="hidden" name="payment_id" value="<?= (int) $payment['id'] ?>"><button class="confirm-btn" type="submit">Transferred</button></form>
                                <?php else: ?>
                                    <span class="muted">Done</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($payments)): ?><tr><td colspan="9">No provider payment rows yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="service-bookings">
            <header class="page-head">
                <div>
                    <h2>Service bookings</h2>
                    <p>When a service is completed, the provider submits the completion report here. The payment row is created from the service price.</p>
                </div>
                <span class="badge success"><i class="fas fa-clipboard-check"></i> Report upload</span>
            </header>
            <div class="report-grid">
                <?php foreach ($bookings as $booking): ?>
                    <article class="report-card">
                        <div class="card-head"><div><h3><?= e($booking['service_name']) ?></h3><p>Owner: <?= e($booking['owner_name']) ?></p></div><span class="badge <?= e($booking['status'] === 'completed' ? 'success' : 'warning') ?>"><?= e($booking['status']) ?></span></div>
                        <div class="detail-list">
                            <div><span>Booked at</span><strong><?= e($booking['booked_at']) ?></strong></div>
                            <div><span>Price</span><strong><?= money($booking['price']) ?></strong></div>
                            <div><span>Pet</span><strong><?= e($booking['pet_name'] ?? 'N/A') ?></strong></div>
                        </div>
                        <?php if ($role === 'service_provider' && empty($booking['report_id'])): ?>
                            <form class="report-actions" method="post">
                                <input type="hidden" name="action" value="create_completion_report">
                                <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                                <textarea name="report_details" rows="3" placeholder="Service details, notes, and owner handoff summary"></textarea>
                                <button class="confirm-btn" type="submit"><i class="fas fa-file-circle-check"></i> Submit Report</button>
                            </form>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($bookings)): ?><div class="empty">No service bookings found for this provider.</div><?php endif; ?>
            </div>
        </section>

        <section class="panel" id="completion-reporting">
            <header class="page-head"><div><h2>Completion reports</h2><p>Reports submitted after a finished service, with admin confirmation status and payment context.</p></div><span class="badge warning"><i class="fas fa-bell"></i> Admin notified</span></header>
            <div class="table-scroll">
                <table>
                    <thead><tr><th>Report</th><th>Owner</th><th>Provider</th><th>Status</th><th>Details</th></tr></thead>
                    <tbody>
                    <?php foreach ($reports as $reportRow): ?>
                        <tr>
                            <td><strong><?= e($reportRow['incident_id']) ?></strong><span class="muted"><?= e($reportRow['reported_at']) ?></span></td>
                            <td><?= e($reportRow['owner']) ?></td>
                            <td><?= e($reportRow['sitter']) ?></td>
                            <td><span class="badge <?= e($reportRow['status_class']) ?>"><?= e($reportRow['status']) ?></span></td>
                            <td><?= e($reportRow['next_action']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($reports)): ?><tr><td colspan="5">No completion reports yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="behavior-tracking">
            <header class="page-head"><div><h2>Behavior tracking</h2><p>Pet notes shared through completed or scheduled provider bookings.</p></div><span class="badge success"><i class="fas fa-share-nodes"></i> Database profiles</span></header>
            <div class="profile-grid">
                <?php foreach ($behaviorProfiles as $profile): ?>
                    <article class="profile-card">
                        <div class="card-head"><div><h3><?= e($profile['pet']) ?></h3><p><?= e($profile['species']) ?> - Owner: <?= e($profile['owner']) ?></p></div><span class="badge <?= e($profile['status_class']) ?>"><?= e($profile['share_status']) ?></span></div>
                        <p><?= e($profile['provider_note']) ?></p>
                        <div class="chips"><?php foreach ($profile['signals'] as $signal): ?><span class="chip"><?= e($signal) ?></span><?php endforeach; ?></div>
                    </article>
                <?php endforeach; ?>
                <?php if (empty($behaviorProfiles)): ?><div class="empty">No pet behavior profiles are linked to this provider yet.</div><?php endif; ?>
            </div>
        </section>

        <section class="panel" id="income-analytics">
            <header class="page-head"><div><h2>Income analytics</h2><p>Lifetime transaction history for the service provider, ready to print or download as PDF.</p></div><span class="badge success"><i class="fas fa-chart-line"></i> Income analysis</span></header>
            <div class="income-grid">
                <div><h3>Monthly income trend</h3><div class="bars" style="margin-top:16px;"><?php foreach ($income['monthly'] as $month): ?><div class="bar-row"><strong><?= e($month['month']) ?></strong><div class="bar-track"><div class="bar-fill" style="width: <?= (int) $month['income_percent'] ?>%;"></div></div><span class="muted"><?= money($month['income']) ?></span></div><?php endforeach; ?></div></div>
                <div><h3>Income source mix</h3><div style="margin-top:10px;"><?php foreach ($income['sources'] as $source): ?><div class="source-row"><div><strong><?= e($source['label']) ?></strong><span><?= (int) $source['percent'] ?>% of tracked movement</span></div><strong><?= money($source['amount']) ?></strong></div><?php endforeach; ?></div></div>
            </div>
            <div class="section-footer">
                <a class="action-btn primary" href="index.php?url=logistics/paymentReport"><i class="fas fa-file-invoice-dollar"></i> Print / Download PDF</a>
            </div>
        </section>
    </main>
</div>
</body>
</html>
