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

if (!function_exists('e')) {
    function e($string)
    {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
}

$complaints = $complaints ?? [];
$pageTitle = $pageTitle ?? 'My Complaints';
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
            --text: var(--ink);
        }

        .complaints-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--line);
        }

        .complaints-header h1 {
            color: var(--teal-dark);
            margin: 0 0 0.5rem;
        }

        .complaints-header p.muted {
            color: var(--muted);
            margin: 0;
        }

        .header-actions {
            display: flex;
            gap: 1rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 0.9rem;
            transition: background 0.3s;
        }

        .btn-primary {
            background: var(--teal);
            color: white;
        }

        .btn-primary:hover {
            background: var(--teal-dark);
        }

        .btn-secondary {
            background: var(--line);
            color: var(--ink);
        }

        .btn-secondary:hover {
            background: #c8dbd3;
        }

        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .alert-info-empty {
            background: var(--soft);
            border: 1px solid var(--line);
            color: var(--ink);
        }

        .complaints-list {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .complaint-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .complaint-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .complaint-title h3 {
            margin: 0 0 0.25rem;
            color: var(--ink);
            font-size: 1.1rem;
        }

        .complaint-date {
            color: var(--muted);
            font-size: 0.9rem;
            margin: 0;
        }

        .badge-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            text-transform: uppercase;
        }

        .badge-status-resolved {
            background: var(--green);
            color: white;
        }

        .badge-status-pending {
            background: var(--sky);
            color: white;
        }

        .complaint-content {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .subject-section,
        .description-section {
            border-bottom: 1px solid var(--line);
            padding-bottom: 1rem;
        }

        .subject-section:last-child,
        .description-section:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .subject-label,
        .description-label {
            margin: 0 0 0.5rem;
            color: var(--ink);
            font-weight: 600;
        }

        .subject-text,
        .description-text {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .related-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            background: var(--soft);
            padding: 1rem;
            border-radius: 4px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .info-label {
            font-weight: 600;
            color: var(--ink);
            font-size: 0.9rem;
        }

        .info-value {
            color: var(--muted);
            font-family: monospace;
        }

        @media (max-width: 768px) {
            .complaints-header {
                flex-direction: column;
                gap: 1rem;
            }

            .header-actions {
                align-self: stretch;
            }

            .complaint-header-bar {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <?php require_once '../app/views/partials/navbar.php'; ?>

    <main class="panel">
        <section class="page-section">
            <div class="complaints-header">
                <div>
                    <h1>My Complaints</h1>
                    <p class="muted">Track and manage your service complaints. We're here to help resolve any issues.</p>
                </div>
                <div class="header-actions">
                    <a class="btn btn-primary" href="<?= htmlspecialchars(app_url('complaints/create')) ?>">+ New Complaint</a>
                    <?php if (!empty($complaints)): ?>
                        <a class="btn btn-secondary" href="<?= htmlspecialchars(app_url('complaints/download')) ?>" target="_blank">⬇ Download</a>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (isset($_SESSION['complaint_submitted'])): ?>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 1rem;">
                    <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                    <div>
                        <strong>Complaint Submitted Successfully!</strong>
                        <p style="margin: 0.25rem 0 0; font-size: 0.9rem;">We'll review your complaint and get back to you soon.</p>
                    </div>
                </div>
                <?php unset($_SESSION['complaint_submitted']); ?>
            <?php endif; ?>

            <?php if (empty($complaints)): ?>
                <div class="alert alert-info-empty">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem;">📋</span>
                        <div>
                            <strong>No complaints yet</strong>
                            <p style="margin: 0.25rem 0 0; font-size: 0.9rem; color: var(--muted);">You haven't submitted any complaints. If you experience any issues, we're here to help.</p>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($complaints as $complaint): ?>
                        <div class="complaint-card">
                            <div class="complaint-header-bar">
                                <div class="complaint-title">
                                    <h3 class="complaint-id">Complaint #<?= (int)$complaint['id'] ?></h3>
                                    <p class="complaint-date">Submitted on <?= date('M d, Y \a\t H:i', strtotime($complaint['created_at'])) ?></p>
                                </div>
                                <div class="complaint-status">
                                    <span class="badge-status badge-status-<?= strtolower($complaint['status']) === 'resolved' ? 'resolved' : 'pending' ?>">
                                        <?= e($complaint['status']) ?>
                                    </span>
                                </div>
                            </div>

                            <div class="complaint-content">
                                <div class="subject-section">
                                    <h4 class="subject-label">Issue</h4>
                                    <p class="subject-text"><?= e($complaint['subject']) ?></p>
                                </div>

                                <div class="description-section">
                                    <h4 class="description-label">Details</h4>
                                    <p class="description-text"><?= nl2br(e($complaint['description'])) ?></p>
                                </div>

                                <?php if (!empty($complaint['order_id']) || !empty($complaint['service_id']) || !empty($complaint['provider_id'])): ?>
                                    <div class="related-info">
                                        <?php if (!empty($complaint['order_id'])): ?>
                                            <div class="info-item">
                                                <span class="info-label">Order ID:</span>
                                                <span class="info-value">#<?= (int)$complaint['order_id'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($complaint['service_id'])): ?>
                                            <div class="info-item">
                                                <span class="info-label">Service ID:</span>
                                                <span class="info-value">#<?= (int)$complaint['service_id'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($complaint['provider_id'])): ?>
                                            <div class="info-item">
                                                <span class="info-label">Provider ID:</span>
                                                <span class="info-value">#<?= (int)$complaint['provider_id'] ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>