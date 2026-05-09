<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            --danger: #e53e3e;
            --success: #6BB5A8;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            background: #f5faf8;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
        }

        .profile-shell {
            max-width: 1220px;
            margin: 28px auto 60px;
            padding: 0 28px;
        }

        .profile-layout {
            display: grid;
            grid-template-columns: 340px 1fr;
            gap: 24px;
            align-items: start;
        }

        .panel,
        .history-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 14px;
            box-shadow: 0 16px 38px rgba(107, 181, 168, 0.08);
        }

        .identity-panel {
            padding: 30px;
            text-align: center;
            position: sticky;
            top: 110px;
        }

        .profile-img {
            width: 142px;
            height: 142px;
            border-radius: 50%;
            object-fit: cover;
            border: 5px solid var(--mint);
            margin-bottom: 18px;
        }

        .identity-panel h1 {
            margin: 0 0 6px;
            font-size: 28px;
            color: var(--teal-dark);
            letter-spacing: 0;
        }

        .identity-panel p {
            margin: 0;
            color: var(--muted);
        }

        .mini-info {
            margin-top: 24px;
            display: grid;
            gap: 12px;
            text-align: left;
        }

        .mini-info div {
            border-top: 1px solid var(--line);
            padding-top: 12px;
        }

        .mini-info span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            margin-bottom: 4px;
        }

        .mini-info strong {
            font-size: 15px;
            word-break: break-word;
        }

        .edit-panel {
            padding: 28px;
        }

        .section-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 20px;
        }

        .section-title h2 {
            margin: 0;
            color: var(--teal);
            font-size: 24px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .field.full {
            grid-column: 1 / -1;
        }

        .field label {
            display: block;
            margin-bottom: 7px;
            font-weight: 700;
            color: var(--ink);
            font-size: 14px;
        }

        .field input {
            width: 100%;
            min-height: 48px;
            border: 2px solid var(--line);
            border-radius: 10px;
            padding: 0 14px;
            background: #f5faf8;
            outline: none;
            font: inherit;
        }

        .field input:focus {
            border-color: var(--teal);
            background: #fff;
        }

        .upload-field {
            border: 2px dashed var(--teal);
            border-radius: 12px;
            background: #f5faf8;
            padding: 16px;
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .upload-field i {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: var(--mint);
            color: var(--teal);
            display: grid;
            place-items: center;
            font-size: 20px;
        }

        .actions {
            margin-top: 20px;
            display: flex;
            justify-content: flex-end;
        }

        .save-btn,
        .modal-btn {
            border: 0;
            border-radius: 10px;
            padding: 13px 20px;
            background: var(--teal);
            color: #fff;
            font-weight: 700;
            cursor: pointer;
            font: inherit;
        }

        .alert {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 14px;
        }

        .alert.success {
            background: #C8E4D6;
            color: var(--success);
        }

        .alert.error {
            background: #fff5f5;
            color: var(--danger);
        }

        .history-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 18px;
            margin-top: 24px;
        }

        .history-card {
            padding: 22px;
            text-align: left;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .history-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 20px 44px rgba(107, 181, 168, 0.12);
        }

        .history-icon {
            width: 58px;
            height: 58px;
            border-radius: 14px;
            background: var(--mint);
            color: var(--teal-dark);
            display: grid;
            place-items: center;
            font-size: 24px;
            margin-bottom: 16px;
        }

        .history-card h3 {
            margin: 0 0 8px;
            font-size: 20px;
        }

        .history-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.45;
        }

        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(47, 79, 79, 0.48);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 22px;
            z-index: 2000;
        }

        .modal-backdrop.show {
            display: flex;
        }

        .history-modal {
            width: min(760px, 100%);
            max-height: 84vh;
            overflow-y: auto;
            background: #fff;
            border-radius: 18px;
            padding: 26px;
            box-shadow: 0 26px 64px rgba(0, 0, 0, 0.22);
        }

        .modal-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
        }

        .modal-head h3 {
            margin: 0;
            color: var(--teal-dark);
            font-size: 24px;
        }

        .close-modal {
            width: 42px;
            height: 42px;
            border: 0;
            border-radius: 50%;
            background: var(--mint);
            cursor: pointer;
            color: var(--ink);
        }

        .detail-list {
            display: grid;
            gap: 12px;
        }

        .detail-item {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 14px;
            background: #f5faf8;
        }

        .detail-item strong {
            display: block;
            color: var(--ink);
            margin-bottom: 5px;
        }

        .detail-item span {
            display: block;
            color: var(--muted);
            font-size: 14px;
            line-height: 1.55;
        }

        .empty {
            color: var(--muted);
            padding: 18px;
            background: #f5faf8;
            border: 1px dashed var(--line);
            border-radius: 12px;
        }

        @media (max-width: 980px) {
            .profile-layout,
            .history-grid {
                grid-template-columns: 1fr;
            }

            .identity-panel {
                position: static;
            }
        }

        @media (max-width: 640px) {
            .profile-shell {
                padding: 0 16px;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<?php require_once '../app/views/partials/navbar.php'; ?>

<?php
if (!function_exists('asset')) {
    function asset($path) {
        $base = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        if ($base === '/' || $base === '.') {
            $base = '';
        }
        return $base . '/' . ltrim($path, '/');
    }
}

$user = isset($user) && is_array($user) ? $user : [];
$history = isset($history) && is_array($history) ? $history : [
    'appointments' => [],
    'orders' => [],
    'services' => []
];
$success = $success ?? null;
$errors = isset($errors) && is_array($errors) ? $errors : [];

$profileImage = $user['image'] ?? '';
$profileSrc = $profileImage && $profileImage !== 'default.png'
    ? asset('uploads/' . $profileImage)
    : asset('images/guest.png');
$appointmentCount = count($history['appointments']);
$orderCount = count($history['orders']);
$serviceCount = count($history['services']);
?>

<main class="profile-shell">
    <div class="profile-layout">
        <aside class="panel identity-panel">
            <img src="<?= htmlspecialchars($profileSrc) ?>" class="profile-img" alt="Profile picture">
            <h1><?= htmlspecialchars($user['username']) ?></h1>
            <p><?= htmlspecialchars(ucfirst($user['role'] ?? 'pet_owner')) ?></p>

            <div class="mini-info">
                <div>
                    <span>Email</span>
                    <strong><?= htmlspecialchars($user['email']) ?></strong>
                </div>
                <div>
                    <span>Phone</span>
                    <strong><?= htmlspecialchars($user['phone'] ?? 'Not set') ?></strong>
                </div>
                <div>
                    <span>Member Since</span>
                    <strong><?= htmlspecialchars(date('F j, Y', strtotime($user['created_at'] ?? 'now'))) ?></strong>
                </div>
            </div>
        </aside>

        <section>
            <div class="panel edit-panel">
                <div class="section-title">
                    <h2>Edit Profile</h2>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="alert error">
                        <?php foreach ($errors as $error): ?>
                            <div><?= htmlspecialchars($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">
                    <div class="form-grid">
                        <div class="field">
                            <label for="username">Username</label>
                            <input id="username" type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="email">Email</label>
                            <input id="email" type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        <div class="field">
                            <label for="phone">Phone</label>
                            <input id="phone" type="text" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                        </div>
                        <div class="field">
                            <label for="profile_image">Profile Picture</label>
                            <div class="upload-field">
                                <i class="far fa-image"></i>
                                <input id="profile_image" type="file" name="profile_image" accept="image/jpeg,image/png,image/webp">
                            </div>
                        </div>
                        <div class="field">
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="new_password" placeholder="Leave blank to keep current">
                        </div>
                        <div class="field">
                            <label for="confirm_password">Confirm New Password</label>
                            <input id="confirm_password" type="password" name="confirm_password" placeholder="Repeat new password">
                        </div>
                    </div>
                    <div class="actions">
                        <button class="save-btn" type="submit"><i class="fas fa-floppy-disk"></i> Save Changes</button>
                    </div>
                </form>
            </div>

            <div class="history-grid">
                <article class="history-card" data-modal-target="appointmentsModal">
                    <div class="history-icon"><i class="far fa-calendar-check"></i></div>
                    <h3>Vet Appointments</h3>
                    <p><?= $appointmentCount ?> appointment<?= $appointmentCount === 1 ? '' : 's' ?> with doctors and your pets.</p>
                </article>
                <article class="history-card" data-modal-target="ordersModal">
                    <div class="history-icon"><i class="fas fa-bag-shopping"></i></div>
                    <h3>Marketplace Orders</h3>
                    <p><?= $orderCount ?> order<?= $orderCount === 1 ? '' : 's' ?> with product status and vendor details.</p>
                </article>
                <article class="history-card" data-modal-target="servicesModal">
                    <div class="history-icon"><i class="fas fa-handshake-angle"></i></div>
                    <h3>Service Providers</h3>
                    <p><?= $serviceCount ?> service interaction<?= $serviceCount === 1 ? '' : 's' ?> with providers.</p>
                </article>
            </div>
        </section>
    </div>
</main>

<div class="modal-backdrop" id="appointmentsModal">
    <div class="history-modal">
        <div class="modal-head">
            <h3>Vet Appointments</h3>
            <button class="close-modal" type="button" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="detail-list">
            <?php if (empty($history['appointments'])): ?>
                <div class="empty">No appointments yet.</div>
            <?php else: ?>
                <?php foreach ($history['appointments'] as $appointment): ?>
                    <div class="detail-item">
                        <strong><?= htmlspecialchars($appointment['appointment_type'] ?? 'Vet appointment') ?></strong>
                        <span>Pet: <?= htmlspecialchars($appointment['pet_name'] ?? 'Unknown pet') ?><?= !empty($appointment['species']) ? ' - ' . htmlspecialchars($appointment['species']) : '' ?></span>
                        <span>Date: <?= htmlspecialchars($appointment['appointment_date'] ?? 'Not set') ?></span>
                        <span>Status: <?= htmlspecialchars($appointment['status'] ?? 'Pending') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="ordersModal">
    <div class="history-modal">
        <div class="modal-head">
            <h3>Marketplace Orders</h3>
            <button class="close-modal" type="button" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="detail-list">
            <?php if (empty($history['orders'])): ?>
                <div class="empty">No marketplace orders yet.</div>
            <?php else: ?>
                <?php foreach ($history['orders'] as $order): ?>
                    <div class="detail-item">
                        <strong>Order #<?= (int) $order['id'] ?> - <?= htmlspecialchars($order['vendor_name'] ?? 'Marketplace vendor') ?></strong>
                        <span>Total: EGP <?= htmlspecialchars((string) ($order['total_price'] ?? 0)) ?></span>
                        <span>Status: <?= htmlspecialchars($order['availability_status'] ?? 'Pending') ?></span>
                        <span>Recurring: <?= !empty($order['is_recurring']) ? 'Yes' : 'No' ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal-backdrop" id="servicesModal">
    <div class="history-modal">
        <div class="modal-head">
            <h3>Service Provider Interactions</h3>
            <button class="close-modal" type="button" aria-label="Close"><i class="fas fa-times"></i></button>
        </div>
        <div class="detail-list">
            <?php if (empty($history['services'])): ?>
                <div class="empty">No service provider interactions yet.</div>
            <?php else: ?>
                <?php foreach ($history['services'] as $service): ?>
                    <div class="detail-item">
                        <strong><?= htmlspecialchars($service['business_name'] ?? 'Service provider') ?></strong>
                        <span>Service: <?= htmlspecialchars($service['service_type'] ?? 'General service') ?></span>
                        <span>Rating: <?= htmlspecialchars((string) ($service['rating'] ?? 'Not rated')) ?></span>
                        <span>Comment: <?= htmlspecialchars($service['comment'] ?? 'No comment') ?></span>
                        <span>Date: <?= htmlspecialchars($service['created_at'] ?? 'Not set') ?></span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-modal-target]').forEach(function (card) {
        card.addEventListener('click', function () {
            const modal = document.getElementById(card.dataset.modalTarget);
            modal.classList.add('show');
        });
    });

    document.querySelectorAll('.modal-backdrop').forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal || event.target.closest('.close-modal')) {
                modal.classList.remove('show');
            }
        });
    });
</script>

<?php require_once '../app/views/partials/footer.php'; ?>
<?php require_once '../app/views/partials/theme_toggle.php'; ?>

</body>
</html>
