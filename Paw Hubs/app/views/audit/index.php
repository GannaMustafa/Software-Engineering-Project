<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Audit Log | Paw Hubs</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --teal: #6BB5A8;
            --green: #9BC870;
            --olive: #CAD7A5;
            --mint: #C8E4D6;
            --sky: #94CDD3;
            --ink: #2f4f4f;
            --muted: #718096;
            --line: #d8ebe5;
            --soft: #f5faf8;
            --panel: #ffffff;
            --danger: #e53e3e;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            padding: 34px;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, var(--mint), #ffffff 45%, var(--sky));
        }

        .app-frame {
            max-width: 1480px;
            min-height: calc(100vh - 68px);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 270px 1fr;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--line);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(47, 79, 79, 0.14);
        }

        .sidebar {
            background: #ffffff;
            border-right: 1px solid var(--line);
            padding: 28px 22px;
            display: flex;
            flex-direction: column;
            gap: 28px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--teal);
            font-size: 22px;
            font-weight: 800;
        }

        .brand i,
        .stat-icon {
            display: grid;
            place-items: center;
            border-radius: 14px;
        }

        .brand i {
            width: 44px;
            height: 44px;
            background: var(--mint);
        }

        .menu-label {
            color: var(--muted);
            font-size: 12px;
            font-weight: 700;
            margin: 0 0 10px;
        }

        .menu {
            display: grid;
            gap: 8px;
        }

        .menu a {
            min-height: 44px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 14px;
            border-radius: 12px;
            color: var(--ink);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
        }

        .menu a.active,
        .menu a:hover {
            background: var(--mint);
            color: #4f9186;
        }

        .menu a i {
            width: 20px;
            text-align: center;
            color: var(--teal);
        }

        .sidebar-footer { margin-top: auto; }
        .content { padding: 26px; background: #f8fbfa; }

        .topbar,
        .panel,
        .stat-card {
            background: var(--panel);
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 18px 38px rgba(107, 181, 168, 0.08);
        }

        .topbar {
            min-height: 70px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 18px;
            margin-bottom: 22px;
        }

        .search {
            flex: 1;
            max-width: 560px;
            height: 46px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--soft);
            color: var(--muted);
        }

        .search input {
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font: inherit;
        }

        .action-btn {
            min-height: 44px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 13px;
            background: #ffffff;
            color: var(--ink);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
        }

        .page-head {
            display: flex;
            justify-content: space-between;
            gap: 18px;
            align-items: flex-end;
            margin: 4px 0 20px;
        }

        .page-head h1 {
            margin: 0;
            font-size: 32px;
            letter-spacing: 0;
        }

        .page-head p {
            margin: 7px 0 0;
            color: var(--muted);
            max-width: 720px;
            line-height: 1.55;
        }

        .role-pill,
        .badge {
            border-radius: 999px;
            padding: 8px 12px;
            background: var(--mint);
            color: #4f9186;
            font-size: 12px;
            font-weight: 800;
            white-space: nowrap;
            text-transform: capitalize;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, minmax(150px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .stat-card {
            min-height: 130px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            font-size: 18px;
        }

        .bg-teal { background: var(--mint); color: #4f9186; }
        .bg-green { background: var(--green); color: #ffffff; }
        .bg-olive { background: var(--olive); color: #4f6f35; }
        .bg-sky { background: var(--sky); color: #ffffff; }

        .stat-card span,
        .panel small {
            color: var(--muted);
            font-weight: 700;
        }

        .stat-card span { font-size: 13px; }
        .stat-card strong {
            display: block;
            margin-top: 5px;
            font-size: 29px;
        }

        .work-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.5fr) minmax(280px, 0.8fr);
            gap: 18px;
        }

        .panel {
            padding: 20px;
            min-width: 0;
        }

        .panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            margin-bottom: 16px;
        }

        .panel h2 {
            margin: 0;
            font-size: 20px;
        }

        .table-scroll { overflow-x: auto; }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        th,
        td {
            padding: 13px 10px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--muted);
            font-size: 12px;
            text-transform: uppercase;
        }

        td small,
        .muted {
            color: var(--muted);
            font-weight: 700;
        }

        .actor {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .avatar {
            width: 34px;
            height: 34px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            background: var(--mint);
            color: #4f9186;
            font-weight: 900;
            flex: 0 0 auto;
        }

        .badge.create,
        .badge.created,
        .badge.register,
        .badge.login { background: var(--green); color: #ffffff; }
        .badge.delete,
        .badge.deleted,
        .badge.error,
        .badge.failed { background: #fff5f5; color: var(--danger); }
        .badge.update,
        .badge.updated { background: var(--olive); color: #4f6f35; }

        .feed {
            display: grid;
            gap: 12px;
        }

        .feed-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px;
            display: grid;
            grid-template-columns: auto 1fr;
            gap: 10px;
            background: #ffffff;
        }

        .feed-item strong {
            display: block;
            margin-bottom: 4px;
        }

        .feed-item p {
            margin: 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .empty {
            min-height: 130px;
            display: grid;
            place-items: center;
            border: 1px dashed var(--line);
            border-radius: 14px;
            color: var(--muted);
            background: var(--soft);
            text-align: center;
            padding: 16px;
        }

        @media (max-width: 1150px) {
            body { padding: 16px; }
            .app-frame { grid-template-columns: 1fr; }
            .sidebar { display: none; }
            .stats,
            .work-grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .content { padding: 16px; }
            .topbar,
            .page-head { align-items: stretch; flex-direction: column; }
            .stats { grid-template-columns: 1fr; }
            th:nth-child(3),
            td:nth-child(3) { display: none; }
        }
    </style>
</head>
<body>
<?php
$logs = $logs ?? [];
$recentLogs = $recentLogs ?? array_slice($logs, 0, 6);
$stats = isset($stats) && is_array($stats) ? $stats : ['total' => 0, 'today' => 0, 'users' => 0, 'latest' => null];
?>
<div class="app-frame">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-shield-halved"></i><span>Paw Clinical</span></div>
        <div>
            <p class="menu-label">Admin Menu</p>
            <nav class="menu">
                <a href="index.php?url=clinical/index"><i class="fas fa-chart-pie"></i> Dashboard</a>
                <a href="index.php?url=clinical/resourceManager"><i class="fas fa-calendar-check"></i> Surgery Manager</a>
                <a class="active" href="index.php?url=audit/index"><i class="fas fa-shield-halved"></i> Audit Logs</a>
            </nav>
        </div>
        <div>
            <p class="menu-label">Main Site</p>
            <nav class="menu">
                <a href="index.php?url=home/index"><i class="fas fa-home"></i> Home</a>
                <a href="index.php?url=user/profile"><i class="far fa-user"></i> Profile</a>
            </nav>
        </div>
        <div class="sidebar-footer">
            <nav class="menu">
                <a href="index.php?url=auth/logout"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
            </nav>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <label class="search">
                <i class="fas fa-search"></i>
                <input id="auditSearch" type="search" placeholder="Search users, actions, details">
            </label>
            <a class="action-btn" href="index.php?url=clinical/index"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>

        <header class="page-head">
            <div>
                <h1>Audit Logs</h1>
                <p>Admin-only privacy audit trail for account activity, clinical records, and system changes pulled directly from the audit log database table.</p>
            </div>
            <span class="role-pill">Admin only</span>
        </header>

        <section class="stats">
            <article class="stat-card"><div class="stat-icon bg-teal"><i class="fas fa-list-check"></i></div><div><span>Total Events</span><strong><?= (int) $stats['total'] ?></strong></div></article>
            <article class="stat-card"><div class="stat-icon bg-green"><i class="fas fa-calendar-day"></i></div><div><span>Today</span><strong><?= (int) $stats['today'] ?></strong></div></article>
            <article class="stat-card"><div class="stat-icon bg-olive"><i class="fas fa-users"></i></div><div><span>Actors</span><strong><?= (int) $stats['users'] ?></strong></div></article>
            <article class="stat-card"><div class="stat-icon bg-sky"><i class="fas fa-clock"></i></div><div><span>Latest</span><strong><?= !empty($stats['latest']) ? htmlspecialchars(date('H:i', strtotime($stats['latest']))) : '-' ?></strong></div></article>
        </section>

        <section class="work-grid">
            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Audit Trail</h2>
                        <small>Latest 100 recorded privacy and system events.</small>
                    </div>
                    <span class="badge"><?= count($logs) ?> shown</span>
                </div>
                <div class="table-scroll">
                    <?php if (empty($logs)): ?>
                        <div class="empty">No audit log records yet.</div>
                    <?php else: ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Actor</th>
                                    <th>Action</th>
                                    <th>Entity</th>
                                    <th>Details</th>
                                    <th>Time</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($logs as $log): ?>
                                <?php $actionClass = strtolower($log['action'] ?? 'activity'); ?>
                                <tr class="audit-row">
                                    <td>
                                        <div class="actor">
                                            <span class="avatar"><?= htmlspecialchars(strtoupper(substr($log['actor_name'] ?? 'S', 0, 1))) ?></span>
                                            <div>
                                                <strong><?= htmlspecialchars($log['actor_name'] ?? 'System') ?></strong><br>
                                                <small><?= htmlspecialchars($log['actor_email'] ?? '') ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge <?= htmlspecialchars($actionClass) ?>"><?= htmlspecialchars($log['action'] ?? 'activity') ?></span></td>
                                    <td><?= htmlspecialchars($log['entity_type'] ?? 'record') ?> #<?= htmlspecialchars((string) ($log['entity_id'] ?? $log['id'] ?? '-')) ?></td>
                                    <td><?= htmlspecialchars($log['details'] ?? '') ?></td>
                                    <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($log['created_at'] ?? 'now'))) ?></td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>

            <div class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Recent Activity</h2>
                        <small>Newest audit events.</small>
                    </div>
                    <span class="badge"><?= count($recentLogs) ?> items</span>
                </div>
                <div class="feed">
                    <?php if (empty($recentLogs)): ?>
                        <div class="empty">No recent activities.</div>
                    <?php else: ?>
                        <?php foreach ($recentLogs as $log): ?>
                            <article class="feed-item">
                                <span class="avatar"><?= htmlspecialchars(strtoupper(substr($log['actor_name'] ?? 'S', 0, 1))) ?></span>
                                <div>
                                    <strong><?= htmlspecialchars($log['actor_name'] ?? 'System') ?> <span class="muted"><?= htmlspecialchars(date('H:i', strtotime($log['created_at'] ?? 'now'))) ?></span></strong>
                                    <p><?= htmlspecialchars($log['details'] ?? $log['action'] ?? 'Audit activity recorded.') ?></p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</div>

<script>
(function () {
    const search = document.getElementById('auditSearch');
    const rows = Array.from(document.querySelectorAll('.audit-row'));
    if (!search) return;
    search.addEventListener('input', function () {
        const value = search.value.trim().toLowerCase();
        rows.forEach(function (row) {
            row.style.display = row.textContent.toLowerCase().includes(value) ? '' : 'none';
        });
    });
})();
</script>
</body>
</html>
