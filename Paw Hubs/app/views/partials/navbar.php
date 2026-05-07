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

if (!function_exists('project_url')) {
    function project_url($path = '')
    {
        $scriptName = urldecode(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? ''));
        $base = '';

        $knownFolders = [
            '/Paw Hubs/public',
            '/Pet Health page',
            '/Smart Marketplace Page'
        ];

        foreach ($knownFolders as $folder) {
            $pos = strpos($scriptName, $folder);
            if ($pos !== false) {
                $base = substr($scriptName, 0, $pos);
                break;
            }
        }

        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('app_url')) {
    function app_url($route = 'home/index', $fragment = '')
    {
        $url = project_url('Paw Hubs/public/index.php?url=' . ltrim($route, '/'));
        return $fragment ? $url . '#' . ltrim($fragment, '#') : $url;
    }
}

if (!function_exists('nav_active')) {
    function nav_active($routes = [], $scripts = [])
    {
        $currentRoute = strtolower(trim($_GET['url'] ?? '', '/'));
        $currentScript = strtolower(urldecode(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '')));

        foreach ((array) $scripts as $script) {
            if (strpos($currentScript, strtolower($script)) !== false) {
                return 'active';
            }
        }

        foreach ((array) $routes as $route) {
            $route = strtolower(trim($route, '/'));

            if ($currentRoute === $route || strpos($currentRoute, $route . '/') === 0) {
                return 'active';
            }
        }

        if (
            empty($currentRoute)
            && strpos($currentScript, '/paw hubs/public/index.php') !== false
            && in_array('home/index', (array) $routes, true)
        ) {
            return 'active';
        }

        return '';
    }
}

$role = $_SESSION['role'] ?? 'pet_owner';
$db = null;
$displayName = $_SESSION['username'] ?? 'Guest';
$profileImage = $_SESSION['profile_pic'] ?? '';
if (isset($_SESSION['user_id'])) {
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT username, image FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $navUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($navUser) {
            $displayName = $navUser['username'] ?: $displayName;
            $profileImage = $navUser['image'] ?? $profileImage;
            $_SESSION['username'] = $displayName;
            $_SESSION['profile_pic'] = $profileImage;
        }
    } catch (Throwable $e) {
        $profileImage = '';
    }
}
$profileSrc = $profileImage && $profileImage !== 'default.png'
    ? asset('uploads/' . $profileImage)
    : asset('images/guest.png');

$notifications = [];
$notificationCount = 0;
if (isset($_SESSION['user_id']) && $db) {
    try {
        $stmt = $db->prepare(
            "SELECT id, title, message, type, is_read, created_at
             FROM notifications
             WHERE user_id = ?
             ORDER BY created_at DESC
             LIMIT 10"
        );
        $stmt->execute([$_SESSION['user_id']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $notificationCount = count(array_filter($notifications, fn($row) => (int) $row['is_read'] === 0));
    } catch (Throwable $e) {
        $notifications = [];
        $notificationCount = 0;
    }
}
?>
<nav class="navbar">
    <div class="nav-container">
        <div class="logo">
            <i class="fas fa-paw"></i>
            <div>
                <span>Paw Hubs</span>
                <small>Care • Love • Health</small>
            </div>
        </div>

        <ul class="nav-links">
            <li><a href="<?= htmlspecialchars(app_url('home/index')) ?>" class="<?= nav_active(['home/index']) ?>">Home</a></li>
            <?php if ($role == 'pet_owner'): ?>
                <li><a href="<?= htmlspecialchars(app_url('home/index', 'my-pets')) ?>">My Pets</a></li>
                <li><a href="<?= htmlspecialchars(project_url('Pet Health page/pet-health.php')) ?>" class="<?= nav_active([], ['/Pet Health page/pet-health.php']) ?>">Health</a></li>
                <li><a href="<?= htmlspecialchars(app_url('clinical/labHub')) ?>" class="<?= nav_active(['clinical/labHub']) ?>">Lab Hub</a></li>
            <?php endif; ?>
            <li><a href="<?= htmlspecialchars(app_url('home/index', 'appointments')) ?>">Appointments</a></li>
            <li><a href="<?= htmlspecialchars(project_url('Smart Marketplace Page/smart_marketplace_pet_care.php')) ?>" class="<?= nav_active([], ['/Smart Marketplace Page/smart_marketplace_pet_care.php']) ?>">Marketplace</a></li>
            <li><a href="<?= htmlspecialchars(app_url('home/index', 'services-showcase-title')) ?>">Services</a></li>
            <?php if (in_array($role, ['admin', 'vet'], true)): ?>
                <li><a href="<?= htmlspecialchars(app_url('clinical/index')) ?>" class="<?= nav_active(['clinical/index', 'clinical/surgeryManager', 'clinical/referralsWorkflow', 'clinical/resourceManager']) ?>">Clinical</a></li>
                <li><a href="<?= htmlspecialchars(app_url('clinical/labHub')) ?>" class="<?= nav_active(['clinical/labHub']) ?>">Lab Hub</a></li>
            <?php endif; ?>
            <?php if ($role === 'admin'): ?>
                <li><a href="<?= htmlspecialchars(app_url('admin/index')) ?>" class="<?= nav_active(['admin']) ?>">Admin</a></li>
                <li><a href="<?= htmlspecialchars(app_url('clinical/resourceManager')) ?>" class="<?= nav_active(['clinical/resourceManager']) ?>">Surgery Manager</a></li>
                <li><a href="<?= htmlspecialchars(app_url('audit/index')) ?>" class="<?= nav_active(['audit/index']) ?>">Audit Log</a></li>
            <?php endif; ?>
            <li><a href="<?= htmlspecialchars(app_url('about/index')) ?>" class="<?= nav_active(['about/index']) ?>">About</a></li>
        </ul>

        <div class="nav-right">
            <div class="notifications-wrapper">
                <button id="notificationToggle" class="icon-btn" type="button" aria-label="Notifications" aria-expanded="false">
                    <i class="far fa-bell"></i>
                    <?php if (!empty($notificationCount)): ?>
                        <span class="badge"><?= (int) $notificationCount ?></span>
                    <?php endif; ?>
                </button>
                <div class="notifications-dropdown" id="notificationsDropdown" aria-hidden="true">
                    <div class="notification-card-header">
                        <strong>Notifications</strong>
                        <span><?= htmlspecialchars($notificationCount) ?> unread</span>
                    </div>
                    <div class="notification-list">
                        <?php if (empty($notifications)): ?>
                            <div class="notification-empty">No new notifications yet.</div>
                        <?php else: ?>
                            <?php foreach ($notifications as $notification): ?>
                                <article class="notification-item <?= $notification['is_read'] ? '' : 'unread' ?>">
                                    <div class="notification-body">
                                        <div class="notification-title"><?= htmlspecialchars($notification['title']) ?></div>
                                        <div class="notification-message"><?= htmlspecialchars($notification['message']) ?></div>
                                    </div>
                                    <small class="notification-time"><?= htmlspecialchars(date('M j, g:i A', strtotime($notification['created_at']))) ?></small>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="profile" id="profileToggle">
                <img src="<?= htmlspecialchars($profileSrc) ?>" alt="profile">
                <div class="profile-info">
                    <span class="name"><?= htmlspecialchars(explode(" ", $displayName)[0]) ?></span>
                    <i class="fas fa-chevron-down"></i>
                </div>

                <div class="dropdown" id="dropdownMenu">
                    <div class="dropdown-header">
                        <strong><?= htmlspecialchars($displayName) ?></strong>
                        <span><?= ucfirst($role) ?></span>
                    </div>
                    <hr>
                    <a href="<?= htmlspecialchars(app_url('user/profile')) ?>"><i class="far fa-user"></i> My Profile</a>
                    <a href="#"><i class="fas fa-cog"></i> Settings</a>
                    <hr>
                    <a href="<?= htmlspecialchars(app_url('auth/logout')) ?>" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        background: #ffffff;
        padding: 24px 0 0;
        position: sticky;
        top: 0;
        z-index: 1000;
    }

    .nav-container {
        max-width: 1440px;
        margin: 0 auto;
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 0 34px;
        gap: 24px;
    }

    .logo {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 28px;
        font-weight: 700;
        color: #6BB5A8;
        white-space: nowrap;
    }

    .logo i {
        font-size: 42px;
    }

    .logo small {
        display: block;
        color: #718096;
        font-size: 12px;
        font-weight: 600;
        margin-top: 2px;
    }

    .nav-links {
        list-style: none;
        display: flex;
        gap: 12px;
        margin: 0;
        padding: 0;
        align-items: center;
    }

    .nav-links a {
        text-decoration: none;
        color: #2f4f4f;
        font-weight: 700;
        font-size: 15px;
        padding: 12px 18px;
        border-radius: 10px;
        display: block;
    }

    .nav-links a:hover,
    .nav-links a.active {
        color: #4f9186;
        background: #C8E4D6;
    }

    .nav-right {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .icon-btn {
        width: 48px;
        height: 48px;
        border: 0;
        border-radius: 50%;
        background: #f5faf8;
        font-size: 18px;
        color: #2f4f4f;
        cursor: pointer;
        display: grid;
        place-items: center;
    }

    .profile {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        position: relative;
        padding: 0;
    }

    .profile:hover {
        background: transparent;
    }

    .profile img {
        width: 54px;
        height: 54px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #C8E4D6;
    }

    .profile-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-info .name {
        font-size: 14px;
        font-weight: 600;
        color: #2f4f4f;
    }

    .profile-info i {
        font-size: 12px;
        color: #94CDD3;
    }

    .notifications-wrapper {
        position: relative;
    }

    .notifications-wrapper .badge {
        position: absolute;
        top: 8px;
        right: 8px;
        min-width: 18px;
        height: 18px;
        padding: 0 6px;
        border-radius: 999px;
        background: #ef476f;
        color: #fff;
        font-size: 11px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .notifications-dropdown {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 360px;
        max-height: 420px;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(16px);
        box-shadow: 0 28px 80px rgba(45, 68, 98, 0.16);
        border: 1px solid rgba(255, 255, 255, 0.75);
        border-radius: 22px;
        transform: translateY(-10px);
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.25s ease, transform 0.25s ease, visibility 0.25s;
        z-index: 1001;
    }

    .notifications-dropdown.open {
        transform: translateY(0);
        opacity: 1;
        visibility: visible;
    }

    .notification-card-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 20px;
        border-bottom: 1px solid rgba(160, 214, 189, 0.45);
    }

    .notification-card-header strong {
        font-size: 15px;
        color: #194d3b;
    }

    .notification-card-header span {
        font-size: 13px;
        color: #4f6f64;
    }

    .notification-list {
        max-height: 320px;
        overflow-y: auto;
    }

    .notification-item {
        padding: 16px 20px;
        display: grid;
        gap: 8px;
        border-bottom: 1px solid rgba(115, 168, 146, 0.16);
        transition: background 0.2s ease;
    }

    .notification-item:hover {
        background: rgba(174, 233, 208, 0.25);
    }

    .notification-item.unread {
        background: rgba(249, 238, 255, 0.7);
    }

    .notification-title {
        font-size: 14px;
        font-weight: 700;
        color: #1f4f3a;
    }

    .notification-message {
        font-size: 13px;
        color: #576863;
        line-height: 1.5;
    }

    .notification-time {
        display: block;
        font-size: 12px;
        color: #7c8d82;
    }

    .notification-empty {
        padding: 24px 20px;
        text-align: center;
        color: #58756c;
        font-size: 14px;
    }

    /* Dropdown */
    .dropdown {
        position: absolute;
        top: calc(100% + 10px);
        right: 0;
        background: white;
        width: 220px;
        border-radius: 12px;
        box-shadow: 0 10px 25px rgba(107, 181, 168, 0.16);
        display: none;
        flex-direction: column;
        padding: 10px;
        border: 1px solid #d8ebe5;
    }

    .dropdown.show {
        display: flex;
        animation: fadeIn 0.2s ease;
    }

    .dropdown-header {
        padding: 10px;
        display: flex;
        flex-direction: column;
    }

    .dropdown-header strong {
        font-size: 14px;
        color: #2f4f4f;
    }

    .dropdown-header span {
        font-size: 12px;
        color: #718096;
    }

    .dropdown hr {
        border: 0;
        border-top: 1px solid #d8ebe5;
        margin: 5px 0;
    }

    .dropdown a {
        padding: 10px;
        text-decoration: none;
        color: #2f4f4f;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 10px;
        border-radius: 8px;
        transition: 0.2s;
    }

    .dropdown a:hover {
        background: #C8E4D6;
        color: #4f9186;
    }

    .dropdown a.logout {
        color: #e53e3e;
    }

    .dropdown a.logout:hover {
        background: #fff5f5;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @media (max-width: 1060px) {
        .nav-container {
            flex-wrap: wrap;
        }

        .nav-links {
            order: 3;
            width: 100%;
            overflow-x: auto;
            padding-bottom: 8px;
        }
    }

    @media (max-width: 680px) {
        .nav-container {
            padding: 0 16px;
        }

        .logo {
            font-size: 22px;
        }

        .logo i {
            font-size: 34px;
        }

        .profile-info .name {
            display: none;
        }
    }
</style>

<script>
    const profileToggle = document.getElementById('profileToggle');
    const dropdownMenu = document.getElementById('dropdownMenu');
    const notificationToggle = document.getElementById('notificationToggle');
    const notificationsDropdown = document.getElementById('notificationsDropdown');

    profileToggle.onclick = function(e) {
        e.stopPropagation();
        dropdownMenu.classList.toggle('show');
        notificationsDropdown.classList.remove('open');
        if (notificationToggle) {
            notificationToggle.setAttribute('aria-expanded', 'false');
            notificationsDropdown.setAttribute('aria-hidden', 'true');
        }
    }

    if (notificationToggle && notificationsDropdown) {
        notificationToggle.onclick = function(e) {
            e.stopPropagation();
            const isOpen = notificationsDropdown.classList.toggle('open');
            notificationToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            notificationsDropdown.setAttribute('aria-hidden', isOpen ? 'false' : 'true');

            if (isOpen) {
                dropdownMenu.classList.remove('show');
                const badge = notificationToggle.querySelector('.badge');
                if (badge) {
                    fetch('<?= htmlspecialchars(app_url('notifications/markRead')) ?>', {
                            method: 'POST'
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                badge.remove();
                                const statusLabel = notificationsDropdown.querySelector('.notification-card-header span');
                                if (statusLabel) {
                                    statusLabel.textContent = '0 unread';
                                }
                                document.querySelectorAll('.notification-item.unread').forEach(item => item.classList.remove('unread'));
                            }
                        })
                        .catch(() => {});
                }
            }
        }
    }

    document.onclick = function() {
        dropdownMenu.classList.remove('show');
        if (notificationsDropdown) {
            notificationsDropdown.classList.remove('open');
            notificationToggle && notificationToggle.setAttribute('aria-expanded', 'false');
            notificationsDropdown.setAttribute('aria-hidden', 'true');
        }
    }
</script>