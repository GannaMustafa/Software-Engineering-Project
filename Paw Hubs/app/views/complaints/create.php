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

$data = $data ?? [];
$pageTitle = $pageTitle ?? 'Submit Complaint';
$error = $error ?? '';
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

        .complaint-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 2px solid var(--line);
        }

        .complaint-header h1 {
            color: var(--teal-dark);
            margin: 0 0 0.5rem;
        }

        .complaint-header p.muted {
            color: var(--muted);
            margin: 0;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
        }

        .complaint-form-wrapper {
            max-width: 700px;
            margin: 0 auto;
        }

        .complaint-form {
            background: var(--panel);
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            margin-bottom: 1rem;
            color: var(--text);
            font-size: 1.1rem;
        }

        .field {
            margin-bottom: 1.5rem;
        }

        .field label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--ink);
        }

        .field input,
        .field textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--line);
            border-radius: 4px;
            font-size: 1rem;
            font-family: inherit;
        }

        .field textarea {
            resize: vertical;
            min-height: 120px;
        }

        .field small.muted {
            display: block;
            margin-top: 0.25rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            padding-top: 1rem;
            border-top: 1px solid var(--line);
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 1rem;
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

        @media (max-width: 768px) {
            .complaint-form {
                padding: 1.5rem;
            }

            .form-row-3 {
                grid-template-columns: 1fr;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <?php require_once '../app/views/partials/navbar.php'; ?>

    <main class="panel">
        <section class="page-section">
            <div class="complaint-header">
                <h1>Submit a Complaint</h1>
                <p class="muted">We take your feedback seriously. Please describe your issue and we'll review it promptly.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>

            <div class="complaint-form-wrapper">
                <form method="post" action="<?= htmlspecialchars(app_url('complaints/store')) ?>" class="complaint-form">
                    <div class="form-section">
                        <h3 style="margin-bottom: 1rem; color: var(--text);">Issue Details</h3>

                        <div class="field">
                            <label for="subject">Subject *</label>
                            <input type="text" id="subject" name="subject" placeholder="Brief summary of your complaint" value="<?= e($data['subject'] ?? '') ?>" required>
                        </div>

                        <div class="field">
                            <label for="description">Description *</label>
                            <textarea id="description" name="description" rows="6" placeholder="Please provide detailed information about what happened and any relevant context..." required><?= e($data['description'] ?? '') ?></textarea>
                            <small class="muted">Be as specific as possible to help us understand and resolve the issue.</small>
                        </div>
                    </div>

                    <div class="form-section">
                        <h3 style="margin-bottom: 1rem; color: var(--text);">Related Information (Optional)</h3>

                        <div class="form-row-3">
                            <div class="field">
                                <label for="order_id">Order ID</label>
                                <input type="number" id="order_id" name="order_id" placeholder="e.g., 12345" value="<?= e($data['order_id'] ?? '') ?>">
                                <small class="muted">The booking/order ID if related</small>
                            </div>

                            <div class="field">
                                <label for="service_id">Service ID</label>
                                <input type="number" id="service_id" name="service_id" placeholder="e.g., 5" value="<?= e($data['service_id'] ?? '') ?>">
                                <small class="muted">The service ID if applicable</small>
                            </div>

                            <div class="field">
                                <label for="provider_id">Provider ID</label>
                                <input type="number" id="provider_id" name="provider_id" placeholder="e.g., 8" value="<?= e($data['provider_id'] ?? '') ?>">
                                <small class="muted">The provider ID if applicable</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?= htmlspecialchars(app_url('complaints/index')) ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit Complaint</button>
                    </div>
                </form>
            </div>
        </section>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>