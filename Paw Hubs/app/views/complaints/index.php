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

$complaints = $complaints ?? [];
$pageTitle = $pageTitle ?? 'My Complaints';
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
        .complaints-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: var(--soft, #f5faf8);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 15px;
        }
        .btn-new {
            background: linear-gradient(135deg, #6BB5A8, #94CDD3);
            color: white;
            padding: 12px 24px;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .complaints-grid {
            display: grid;
            gap: 20px;
        }
        .complaint-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 14px 40px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        .complaint-card:hover {
            transform: translateY(-5px);
        }
        .complaint-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
        }
        .status-badge {
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-pending { background: #fff3cd; color: #856404; }
        .status-resolved { background: #d4edda; color: #155724; }
        .complaint-issue {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--ink, #2f4f4f);
            margin: 8px 0;
        }
        .no-complaints {
            text-align: center;
            padding: 80px 20px;
            color: var(--muted);
        }
    </style>
</head>
<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <div class="complaints-container">
        <div class="page-header">
            <div>
                <h1>My Complaints</h1>
                <p>Track all your submitted complaints</p>
            </div>
            <a href="<?= app_url('complaints/create') ?>" class="btn-new">
                <i class="fas fa-plus"></i> New Complaint
            </a>
        </div>

        <?php if (isset($_SESSION['complaint_submitted'])): ?>
            <div style="background:#d4edda; color:#155724; padding:16px; border-radius:16px; margin-bottom:25px; text-align:center;">
                <strong>✅ Complaint submitted successfully!</strong>
            </div>
            <?php unset($_SESSION['complaint_submitted']); ?>
        <?php endif; ?>

        <?php if (empty($complaints)): ?>
            <div class="no-complaints">
                <i class="fas fa-folder-open" style="font-size:3.5rem; margin-bottom:20px; opacity:0.4;"></i>
                <p>You haven't submitted any complaints yet.</p>
                <a href="<?= app_url('complaints/create') ?>" class="btn-new" style="margin-top:20px;">
                    Submit Your First Complaint
                </a>
            </div>
        <?php else: ?>
            <div class="complaints-grid">
                <?php foreach ($complaints as $c): ?>
                    <div class="complaint-card">
                        <div class="complaint-header">
                            <div>
                                <strong><?= e($c['user_name'] ?? 'You') ?></strong> 
                                • <?= date('M j, Y', strtotime($c['created_at'] ?? $c['date'])) ?>
                            </div>
                            <span class="status-badge status-<?= strtolower($c['status'] ?? 'pending') ?>">
                                <?= ucfirst(e($c['status'] ?? 'pending')) ?>
                            </span>
                        </div>

                        <div class="complaint-issue">
                            <?= e($c['issue'] ?? 'No title') ?>
                        </div>

                        <div style="line-height:1.7; color:#555; margin-bottom:15px;">
                            <?= nl2br(e($c['user_msg'] ?? $c['description'] ?? '')) ?>
                        </div>

                        <?php if (!empty($c['provider_name'])): ?>
                            <p><strong>Provider:</strong> <?= e($c['provider_name']) ?></p>
                        <?php endif; ?>

                        <?php if (!empty($c['amount']) && $c['amount'] > 0): ?>
                            <p><strong>Amount:</strong> $<strong><?= number_format($c['amount'], 2) ?></strong></p>
                        <?php endif; ?>

                        <?php if (!empty($c['resolution'])): ?>
                            <div style="margin-top:15px; padding:12px; background:#f0f9f4; border-radius:12px;">
                                <strong>Resolution:</strong> <?= e($c['resolution']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php require_once '../app/views/partials/footer.php'; ?>

</body>
</html>