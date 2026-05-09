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
    function e($value)
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$reviews = $reviews ?? [];
$services = $services ?? [];
$providers = $providers ?? [];
$pageTitle = $pageTitle ?? 'Reviews';
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
            --ink: #2f4f4f;
            --muted: #718096;
            --line: #d8ebe5;
            --panel: #ffffff;
            --soft: #f5faf8;
            --bg: #f9fbfb;
        }

        body {
            background: var(--bg);
        }

        .reviews-page {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            align-items: start;
        }

        .reviews-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .reviews-header h1 {
            margin: 0;
            color: var(--teal-dark);
        }

        .reviews-grid {
            display: grid;
            gap: 1rem;
        }

        .review-card,
        .review-form-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 1.25rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            color: var(--muted);
            font-size: 0.9rem;
        }

        .review-stars {
            color: #f6b93b;
            font-size: 1rem;
            margin-bottom: 0.75rem;
        }

        .review-text {
            color: var(--ink);
            line-height: 1.6;
        }

        .review-form-card h3,
        .review-card h2 {
            margin-top: 0;
            font-size: 1.05rem;
            color: var(--teal-dark);
        }

        .review-form {
            display: grid;
            gap: 1rem;
        }

        .field {
            display: grid;
            gap: 0.5rem;
        }

        .field label {
            font-weight: 600;
            color: var(--ink);
        }

        .field select,
        .field textarea {
            width: 100%;
            padding: 0.85rem;
            border: 1px solid var(--line);
            border-radius: 8px;
            font-size: 1rem;
            background: white;
        }

        .field textarea {
            min-height: 110px;
            resize: vertical;
        }

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.85rem 1.25rem;
            border: none;
            border-radius: 8px;
            background: var(--teal);
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .btn-submit:hover {
            background: var(--teal-dark);
        }

        .muted {
            color: var(--muted);
        }

        @media (max-width: 960px) {
            .reviews-page {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php require_once '../app/views/partials/navbar.php'; ?>

    <main class="panel">
        <section class="page-section">
            <div class="reviews-header">
                <div>
                    <h1><?= htmlspecialchars($pageTitle) ?></h1>
                    <p class="muted">Read and submit service reviews for your pet care providers.</p>
                </div>
            </div>

            <?php if (isset($_SESSION['review_submitted'])): ?>
                <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <strong>Review submitted successfully!</strong>
                </div>
                <?php unset($_SESSION['review_submitted']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['review_error'])): ?>
                <div style="background: #fdecea; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem;">
                    <strong><?= htmlspecialchars($_SESSION['review_error']) ?></strong>
                </div>
                <?php unset($_SESSION['review_error']); ?>
            <?php endif; ?>

            <div class="reviews-page">
                <div class="reviews-grid">
                    <?php foreach ($reviews as $review): ?>
                        <article class="review-card">
                            <div class="review-meta">
                                <span>Owner #<?= (int)$review['owner_id'] ?></span>
                                <?php if (!empty($review['service_name'])): ?>
                                    <span>Service: <?= htmlspecialchars($review['service_name']) ?></span>
                                <?php elseif (!empty($review['direct_provider_name'])): ?>
                                    <span>Provider: <?= htmlspecialchars($review['direct_provider_name']) ?></span>
                                <?php else: ?>
                                    <span>Target: Unknown</span>
                                <?php endif; ?>
                                <span><?= htmlspecialchars($review['created_at']) ?></span>
                            </div>
                            <div class="review-stars">
                                <?= str_repeat('★', min(5, max(0, (int)$review['rating']))) ?><?= str_repeat('☆', 5 - min(5, max(0, (int)$review['rating']))) ?>
                            </div>
                            <div class="review-text">
                                <?= nl2br(htmlspecialchars($review['comment'])) ?>
                            </div>
                        </article>
                    <?php endforeach; ?>

                    <?php if (empty($reviews)): ?>
                        <p class="muted">No reviews yet. Be the first to add one.</p>
                    <?php endif; ?>
                </div>

                <aside>
                    <div class="review-form-card">
                        <h3>Review a Service</h3>
                        <form class="review-form" method="post" action="<?= htmlspecialchars(app_url('reviews/index')) ?>">
                            <div class="field">
                                <label for="service_id">Service</label>
                                <select id="service_id" name="service_id" required>
                                    <option value="">Choose a service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?= (int)$service['id'] ?>"><?= htmlspecialchars($service['name'] ?: 'Service #' . (int)$service['id']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="rating">Rating</label>
                                <select id="rating" name="rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> ★</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="comment">Comment</label>
                                <textarea id="comment" name="comment" rows="4" required></textarea>
                            </div>

                            <button type="submit" class="btn-submit"><i class="fas fa-star"></i> Post review</button>
                        </form>
                    </div>

                    <div class="review-form-card">
                        <h3>Review a Provider</h3>
                        <p class="muted">Provider reviews are linked via services in this system.</p>
                        <form class="review-form" method="post" action="<?= htmlspecialchars(app_url('reviews/index')) ?>">
                            <div class="field">
                                <label for="service_id_provider">Provider</label>
                                <select id="service_id_provider" name="service_id" required>
                                    <option value="">Choose a provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?= (int)$provider['id'] ?>"><?= htmlspecialchars($provider['business_name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="rating_provider">Rating</label>
                                <select id="rating_provider" name="rating">
                                    <?php for ($i = 5; $i >= 1; $i--): ?>
                                        <option value="<?= $i ?>"><?= $i ?> ★</option>
                                    <?php endfor; ?>
                                </select>
                            </div>

                            <div class="field">
                                <label for="comment_provider">Comment</label>
                                <textarea id="comment_provider" name="comment" rows="4" required></textarea>
                            </div>

                            <button type="submit" class="btn-submit"><i class="fas fa-comment-dots"></i> Post review</button>
                        </form>
                    </div>
                </aside>
            </div>
        </section>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>
