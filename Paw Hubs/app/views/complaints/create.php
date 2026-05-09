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

$pageTitle = $pageTitle ?? 'Submit Complaint';
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
            max-width: 800px;
            margin: 40px auto;
            padding: 40px;
            background: var(--soft, #f5faf8);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.08);
        }
        .form-card {
            background: white;
            padding: 35px;
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
        .field input, .field textarea, .field select {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #dfe7e4;
            border-radius: 16px;
            font-size: 1rem;
        }
        .field textarea {
            min-height: 160px;
            resize: vertical;
        }
        .btn-submit {
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
        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(107,181,168,0.3);
        }
    </style>
</head>
<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <div class="complaints-container">
        <div style="text-align:center; margin-bottom:30px;">
            <h1 style="color:var(--teal-dark);">Submit a Complaint</h1>
            <p style="color:#718096;">We take your feedback seriously and will review it promptly.</p>
        </div>

        <?php if (isset($_SESSION['complaint_error'])): ?>
            <div style="background:#fdecea; color:#721c24; padding:1rem; border-radius:16px; margin-bottom:25px;">
                <?= e($_SESSION['complaint_error']) ?>
            </div>
            <?php unset($_SESSION['complaint_error']); ?>
        <?php endif; ?>

        <div class="form-card">
            <form method="POST" action="<?= htmlspecialchars(app_url('complaints/store')) ?>">
                <div class="field">
                    <label for="provider_name">Provider Name *</label>
                    <input type="text" id="provider_name" name="provider_name" 
                           placeholder="e.g. Happy Paws Grooming" required>
                </div>

                <div class="field">
                    <label for="issue">Issue / Subject *</label>
                    <input type="text" id="issue" name="issue" 
                           placeholder="e.g. Poor service, Overcharging, Late delivery..." required>
                </div>

                <div class="field">
                    <label for="description">Detailed Description *</label>
                    <textarea id="description" name="description" rows="7" 
                              placeholder="Please explain what happened in detail..." required></textarea>
                </div>

                <div class="field">
                    <label for="amount">Amount Involved (Optional)</label>
                    <input type="number" id="amount" name="amount" step="0.01" 
                           placeholder="0.00" value="0">
                </div>

                <button type="submit" class="btn-submit">
                    <i class="fas fa-paper-plane"></i> Submit Complaint
                </button>
            </form>
        </div>
    </div>

    <?php require_once '../app/views/partials/footer.php'; ?>

</body>
</html>