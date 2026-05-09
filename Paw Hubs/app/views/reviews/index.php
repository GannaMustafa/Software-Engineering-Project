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
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

$reviews  = $reviews ?? [];
$services = $services ?? [];
$pageTitle = $pageTitle ?? 'Reviews';
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
        .reviews-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 30px;
            background: var(--soft, #f5faf8);
            border-radius: 28px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.08);
        }

        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .page-header h1 {
            font-size: 34px;
            color: var(--teal-dark, #4f9186);
            margin-bottom: 8px;
        }

        .reviews-page {
            display: grid;
            grid-template-columns: 1fr 420px;
            gap: 40px;
            align-items: start;
        }

        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .review-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease;
        }

        .review-card:hover {
            transform: translateY(-4px);
        }

        .review-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: var(--muted, #718096);
        }

        .review-stars {
            color: #f6b93b;
            font-size: 1.35rem;
            margin: 8px 0;
        }

        .review-text {
            line-height: 1.7;
            color: var(--ink, #2f4f4f);
        }

        .review-form-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 30px;
        }

        .review-form-card h3 {
            margin-top: 0;
            color: var(--teal-dark, #4f9186);
            font-size: 1.5rem;
        }

        .field {
            margin-bottom: 22px;
        }

        .field label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--ink, #2f4f4f);
        }

        .field select,
        .field textarea {
            width: 100%;
            padding: 14px 18px;
            border: 1px solid #dfe7e4;
            border-radius: 16px;
            font-size: 1rem;
            background: #ffffff;
        }

        .field textarea {
            min-height: 140px;
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
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(107, 181, 168, 0.3);
        }

        @media (max-width: 992px) {
            .reviews-page {
                grid-template-columns: 1fr;
            }
            .review-form-card {
                position: static;
            }
        }
    </style>
</head>
<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <div class="reviews-container">
        <div class="page-header">
            <h1><?= e($pageTitle) ?></h1>
            <p class="muted">Read and submit service reviews for your pet care providers.</p>
        </div>

        <?php if (isset($_SESSION['review_submitted'])): ?>
            <div style="background:#d4edda; color:#155724; padding:1rem; border-radius:16px; margin-bottom:25px; text-align:center;">
                <strong>Review submitted successfully!</strong>
            </div>
            <?php unset($_SESSION['review_submitted']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['review_error'])): ?>
            <div style="background:#fdecea; color:#721c24; padding:1rem; border-radius:16px; margin-bottom:25px; text-align:center;">
                <strong><?= e($_SESSION['review_error']) ?></strong>
            </div>
            <?php unset($_SESSION['review_error']); ?>
        <?php endif; ?>

        <div class="reviews-page">

            <!-- Reviews List -->
            <div class="reviews-list">
                <?php if (empty($reviews)): ?>
                    <div class="review-card" style="text-align:center; padding:60px 30px;">
                        <p class="muted" style="font-size:1.1rem;">No reviews yet. Be the first to add one.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-meta">
                                <strong><?= e($review['owner_username'] ?? 'Pet Owner') ?></strong>
                                <span><?= date('Y-m-d H:i', strtotime($review['created_at'])) ?></span>
                            </div>

                            <?php if (!empty($review['service_name'])): ?>
                                <strong>Service: <?= e($review['service_name']) ?></strong><br>
                            <?php endif; ?>

                            <?php if (!empty($review['provider_name'])): ?>
                                <small>by <?= e($review['provider_name']) ?></small>
                            <?php endif; ?>

                            <div class="review-stars">
                                <?= str_repeat('★', (int)$review['rating']) ?>
                                <?= str_repeat('☆', 5 - (int)$review['rating']) ?>
                            </div>

                            <div class="review-text">
                                <?= nl2br(e($review['comment'])) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- Review Form -->
            <div class="review-form-card">
                <h3>Review a Service</h3>
                <form method="post" action="<?= htmlspecialchars(app_url('reviews/index')) ?>">
                    <div class="field">
                        <label for="service_id">Service</label>
                        <select id="service_id" name="service_id" required>
                            <option value="">Choose a service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= (int)$service['id'] ?>">
                                    <?= e($service['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating" required>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <option value="<?= $i ?>"><?= $i ?> ★</option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="field">
                        <label for="comment">Comment</label>
                        <textarea id="comment" name="comment" rows="6" required placeholder="Write your review here..."></textarea>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fas fa-star"></i> Post Review
                    </button>
                </form>
            </div>

        </div>
    </div>

    <?php require_once '../app/views/partials/footer.php'; ?>

</body>
</html>