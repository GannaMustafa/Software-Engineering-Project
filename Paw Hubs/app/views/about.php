<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --teal: #6BB5A8;
            --teal-dark: #2f5950;
            --sky: #94CDD3;
            --mint: #C8E4D6;
            --ink: #2f4f4f;
            --muted: #647474;
            --panel: #ffffff;
            --line: #d8ebe5;
        }

        * {
            box-sizing: border-box;
        }

        body {
            cursor: url('../public/images/icons8-dog-paw-34.png'), auto;
            margin: 0;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background: #f5faf8;
        }

        .page-shell {
            max-width: 1280px;
            margin: 0 auto;
            padding: 28px 32px 48px;
        }

        .hero-card {
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            border-radius: 28px;
            padding: 60px 42px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            position: relative;
            min-height: 480px;
            box-shadow: 0 26px 70px rgba(44, 106, 94, 0.08);
            overflow: hidden;
        }

        .hero-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(248, 251, 250, 0.95) 0%, rgba(239, 247, 242, 0.85) 50%, rgba(107, 181, 168, 0.15) 100%);
            z-index: 1;
            pointer-events: none;
        }

        .hero-card>* {
            position: relative;
            z-index: 2;
        }

        .hero-copy {
            max-width: 620px;
        }

        .hero-copy h1 {
            margin: 0 0 16px;
            font-size: 42px;
            line-height: 1.03;
            color: var(--teal-dark);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .hero-copy p {
            margin: 0 0 22px;
            color: var(--muted);
            font-size: 18px;
            line-height: 1.8;
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .hero-copy ul {
            margin: 0;
            padding-left: 20px;
            color: var(--muted);
            line-height: 1.8;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .hero-copy ul li {
            margin-bottom: 12px;
        }

        .hero-image {
            display: none;
        }

        .hero-image img {
            display: none;
        }

        .stats-grid {
            margin-top: 34px;
            display: grid;
            grid-template-columns: repeat(4, minmax(180px, 1fr));
            gap: 22px;
        }

        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 14px 36px rgba(107, 181, 168, 0.08);
        }

        .stat-card h3 {
            margin: 0 0 14px;
            font-size: 18px;
            color: var(--teal-dark);
        }

        .stat-card strong {
            display: block;
            font-size: 38px;
            color: var(--teal);
            margin-bottom: 8px;
        }

        .stat-card span {
            color: var(--muted);
            font-size: 15px;
        }

        .section-title {
            margin: 44px 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 28px;
            color: var(--teal-dark);
        }

        .section-title p {
            margin: 0;
            color: var(--muted);
            max-width: 620px;
        }

        .reviews-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 44px;
        }

        .review-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 14px 36px rgba(107, 181, 168, 0.08);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 12px;
        }

        .review-user {
            flex: 1;
        }

        .review-user h4 {
            margin: 0 0 4px;
            font-size: 16px;
            color: var(--teal-dark);
        }

        .review-service {
            margin: 0;
            font-size: 13px;
            color: var(--muted);
        }

        .review-rating {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .stars {
            color: #FFB800;
            font-size: 16px;
            letter-spacing: 2px;
        }

        .rating-number {
            font-weight: 600;
            color: var(--teal);
        }

        .review-text {
            margin: 0;
            color: var(--ink);
            font-size: 15px;
            line-height: 1.6;
        }

        .feature-list {
            display: grid;
            grid-template-columns: repeat(2, minmax(260px, 1fr));
            gap: 18px;
        }

        .feature-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 24px;
        }

        .feature-card h3 {
            margin: 0 0 10px;
            font-size: 20px;
            color: var(--teal-dark);
        }

        .feature-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.75;
        }

        @media (max-width: 980px) {

            .hero-card,
            .stats-grid,
            .feature-list {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .page-shell {
                padding: 20px 18px 30px;
            }

            .hero-card {
                padding: 28px 24px;
            }

            .hero-copy h1 {
                font-size: 34px;
            }
        }
    </style>
</head>

<body>

    <?php require_once '../app/views/partials/navbar.php'; ?>

    <?php
    $stats = isset($stats) && is_array($stats) ? $stats : [];
    $ordersTotal = $stats['orders_total'] ?? 0;
    $ordersSuccess = $stats['orders_success'] ?? 0;
    $doctorRating = $stats['doctor_rating'] ?? null;
    $serviceRating = $stats['service_rating'] ?? null;
    $activeUsers = $stats['active_users'] ?? 0;
    ?>

    <main class="page-shell">
        <section class="hero-card" style="background-image: url('images/AboutUs.png');">
            <div class="hero-copy">
                <h1>About Paw Hubs</h1>
                <p>Paw Hubs brings pet owners, veterinarians, and trusted service providers together in one friendly platform. We help you manage pet health, book appointments, buy products, and discover local services all from one dashboard.</p>

                <ul>
                    <li>Easy appointment booking with vets and clinics.</li>
                    <li>Marketplace shopping for premium pet products.</li>
                    <li>Service provider reviews and service booking for grooming, walking, and care.</li>
                    <li>Secure user accounts with personalized pet health tracking.</li>
                </ul>
            </div>
        </section>

        <div class="stats-grid" aria-label="Site statistics">
            <article class="stat-card">
                <h3>Total active users</h3>
                <strong><?= htmlspecialchars((string) $activeUsers) ?></strong>
                <span>Registered pet owners and service partners.</span>
            </article>
            <article class="stat-card">
                <h3>Marketplace orders</h3>
                <strong><?= htmlspecialchars((string) $ordersTotal) ?></strong>
                <span>Orders processed through the platform.</span>
            </article>
            <article class="stat-card">
                <h3>Successful marketplace purchases</h3>
                <strong><?= htmlspecialchars((string) $ordersSuccess) ?></strong>
                <span>Confirmed completed orders.</span>
            </article>
            <article class="stat-card">
                <h3>Doctor rating</h3>
                <strong><?= htmlspecialchars($doctorRating !== null ? $doctorRating : 'N/A') ?></strong>
                <span>Average score from vet reviews.</span>
            </article>
            <article class="stat-card">
                <h3>Service provider rating</h3>
                <strong><?= htmlspecialchars($serviceRating !== null ? $serviceRating : 'N/A') ?></strong>
                <span>Average score from service reviews.</span>
            </article>
        </div>

        <div class="section-title">
            <h2>What our customers say</h2>
            <p>Real feedback from pet owners and service providers who trust Paw Hubs.</p>
        </div>

        <div class="reviews-grid" aria-label="Customer testimonials">
            <?php
            $reviews = $stats['reviews'] ?? [];
            if (empty($reviews)):
            ?>
                <article class="review-card" style="grid-column: 1 / -1; text-align: center; padding: 40px;">
                    <p style="color: var(--muted); margin: 0;">No testimonials available yet. Start using Paw Hubs and share your experience!</p>
                </article>
                <?php
            else:
                foreach ($reviews as $review):
                    $rating = (int)($review['rating'] ?? 0);
                    $stars = str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
                ?>
                    <article class="review-card">
                        <div class="review-header">
                            <div class="review-user">
                                <h4><?= htmlspecialchars($review['owner_name'] ?? 'Anonymous') ?></h4>
                                <p class="review-service"><?= htmlspecialchars($review['service_name'] ?? 'Paw Hubs Service') ?></p>
                            </div>
                            <div class="review-rating">
                                <span class="stars"><?= $stars ?></span>
                                <span class="rating-number"><?= $rating ?></span>
                            </div>
                        </div>
                        <?php if (!empty($review['comment'])): ?>
                            <p class="review-text"><?= htmlspecialchars($review['comment']) ?></p>
                        <?php endif; ?>
                    </article>
            <?php
                endforeach;
            endif;
            ?>
        </div>

        <div class="section-title">
            <h2>What we do best</h2>
            <p>Paw Hubs is built to help every pet-loving family feel confident about care, health, and trusted service. Our platform grows as your pets grow.</p>
        </div>

        <div class="feature-list">
            <article class="feature-card">
                <h3>Trusted marketplace</h3>
                <p>From food to toys, every marketplace order is tracked and presented with clear product details and status information.</p>
            </article>
            <article class="feature-card">
                <h3>Veterinarian support</h3>
                <p>Book appointments with vets, keep medical records updated, and get expert care guidance for dogs and cats.</p>
            </article>
            <article class="feature-card">
                <h3>Service providers</h3>
                <p>Find and rate local pet sitters, groomers, and trainers so others can choose trusted providers.</p>
            </article>
            <article class="feature-card">
                <h3>Pet-friendly community</h3>
                <p>A clean interface and clear statistics make Paw Hubs easy to use for both new pet owners and long-time caretakers.</p>
            </article>
        </div>
    </main>

    <?php require_once '../app/views/partials/footer.php'; ?>
</body>

</html>