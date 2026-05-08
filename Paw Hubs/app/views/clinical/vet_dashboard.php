<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vet Dashboard | Paw Hubs</title>
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

        * {
            box-sizing: border-box;
        }

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
        }

        .brand i {
            width: 44px;
            height: 44px;
            border-radius: 14px;
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

        .sidebar-footer {
            margin-top: auto;
        }

        .content {
            padding: 26px;
            background: #f8fbfa;
        }

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
            cursor: pointer;
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
            max-width: 760px;
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
            grid-template-columns: repeat(5, minmax(140px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .stat-card {
            min-height: 128px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            font-size: 18px;
        }

        .bg-teal {
            background: var(--mint);
            color: #4f9186;
        }

        .bg-green {
            background: var(--green);
            color: #fff;
        }

        .bg-olive {
            background: var(--olive);
            color: #4f6f35;
        }

        .bg-sky {
            background: var(--sky);
            color: #fff;
        }

        .stat-card span,
        .panel small,
        .meta {
            color: var(--muted);
            font-weight: 700;
        }

        .stat-card span {
            font-size: 13px;
        }

        .stat-card strong {
            display: block;
            margin-top: 5px;
            font-size: 28px;
        }

        .hero,
        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
        }

        .hero,
        .stats,
        .grid {
            margin-bottom: 18px;
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

        .quick-links {
            display: grid;
            gap: 12px;
        }

        .quick-link {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            padding: 14px 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            background: #fff;
            color: var(--ink);
            text-decoration: none;
            font-weight: 800;
        }

        .quick-link span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .notice {
            border-radius: 16px;
            padding: 15px 17px;
            margin-bottom: 16px;
            line-height: 1.6;
            font-weight: 700;
        }

        .notice.success {
            background: #e6fffa;
            color: #155e75;
            border: 1px solid #b2f5ea;
        }

        .notice.error {
            background: #fff5f5;
            color: #742a2a;
            border: 1px solid #fed7d7;
        }

        .feed {
            display: grid;
            gap: 12px;
        }

        .feed-item {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
        }

        .feed-item strong {
            display: block;
            margin-bottom: 4px;
        }

        .feed-item p {
            margin: 0;
            color: var(--muted);
            line-height: 1.5;
        }

        .request-item {
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 16px;
            background: #fff;
            display: grid;
            gap: 12px;
        }

        .request-top {
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .request-type {
            color: var(--muted);
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0;
        }

        .request-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .mini-form {
            display: grid;
            gap: 8px;
            padding-top: 10px;
            border-top: 1px dashed var(--line);
        }

        .mini-form input,
        .mini-form textarea {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 12px;
            font: inherit;
            background: var(--soft);
            color: var(--ink);
        }

        .empty {
            min-height: 120px;
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
            body {
                padding: 16px;
            }

            .app-frame {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .stats,
            .hero,
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .content {
                padding: 16px;
            }

            .topbar,
            .page-head {
                align-items: stretch;
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php
    $workflowRequests = $workflowRequests ?? [];
    $vetRequests = $vetRequests ?? [];
    $incomingLabStats = $incomingLabStats ?? ['new' => 0, 'critical' => 0, 'uninterpreted' => 0];
    ?>
    <div class="app-frame">
        <aside class="sidebar">
            <div class="brand"><i class="fas fa-stethoscope"></i><span>Paw Vet</span></div>
            <div>
                <p class="menu-label">Clinical</p>
                <nav class="menu">
                    <a class="active" href="index.php?url=clinical/index"><i class="fas fa-chart-pie"></i> Dashboard</a>
                    <a href="index.php?url=clinical/surgeryManager"><i class="fas fa-briefcase-medical"></i> Surgery Manager</a>
                    <a href="index.php?url=clinical/labHub"><i class="fas fa-vial-circle-check"></i> Lab Hub</a>
                    <a href="index.php?url=clinical/referralsWorkflow"><i class="fas fa-share-nodes"></i> Referrals Workflow</a>
                </nav>
            </div>
            <div class="sidebar-footer">
                <nav class="menu">
                    <a href="index.php?url=home/index"><i class="fas fa-home"></i> Home</a>
                    <a href="index.php?url=auth/logout"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
                </nav>
            </div>
        </aside>

        <main class="content">
            <div class="topbar">
                <label class="search"><i class="fas fa-search"></i><input type="search" placeholder="Search dashboard summaries"></label>
                <a class="action-btn" href="#vet-requests"><i class="fas fa-user-check"></i> Approval Queue</a>
            </div>

            <header class="page-head">
                <div>
                    <h1>Vet Clinical Dashboard</h1>
                    <p>This is the main summary page. Open each clinical workspace from the sidebar for full details and actions.</p>
                </div>
                <span class="role-pill">Vet access</span>
            </header>

            <?php if (!empty($message)): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <?php if (!empty($errors)): ?><div class="notice error"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>

            <section class="hero">
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Clinical Overview</h2><small>Main summary only</small>
                        </div>
                    </div>
                    <p class="meta">Detailed procedure management, lab interpretation, and referral transfers were moved out of this dashboard into separate pages from the left menu.</p>
                </article>
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Open Workspaces</h2><small>Go directly to the section you need</small>
                        </div>
                    </div>
                    <div class="quick-links">
                        <a class="quick-link" href="index.php?url=clinical/surgeryManager">
                            <div>Surgery Manager<br><span>Procedure cases, permissions, and surgery requests</span></div><i class="fas fa-arrow-right"></i>
                        </a>
                        <a class="quick-link" href="index.php?url=clinical/labHub">
                            <div>Lab Hub<br><span>Incoming lab results and interpretation tools</span></div><i class="fas fa-arrow-right"></i>
                        </a>
                        <a class="quick-link" href="index.php?url=clinical/referralsWorkflow">
                            <div>Referrals Workflow<br><span>Transfers and specialist directory</span></div><i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </article>
            </section>

            <section class="stats">
                <article class="stat-card">
                    <div class="stat-icon bg-teal"><i class="fas fa-briefcase-medical"></i></div>
                    <div><span>Procedures</span><strong><?= (int) ($stats['procedures'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-green"><i class="fas fa-vial"></i></div>
                    <div><span>Lab Reports</span><strong><?= (int) ($stats['lab_reports'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-olive"><i class="fas fa-share-nodes"></i></div>
                    <div><span>Referrals</span><strong><?= (int) ($stats['referrals'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-sky"><i class="fas fa-bell"></i></div>
                    <div><span>Pet Health Alerts</span><strong><?= (int) ($stats['vet_requests'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-olive"><i class="fas fa-user-clock"></i></div>
                    <div><span>Pending Owner</span><strong><?= (int) ($stats['pending_owner'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-sky"><i class="fas fa-user-shield"></i></div>
                    <div><span>Pending Admin</span><strong><?= (int) ($stats['pending_admin'] ?? 0) ?></strong></div>
                </article>
            </section>

            <section class="grid" id="vet-requests">
                <article class="panel" style="grid-column: 1 / -1;">
                    <div class="panel-head">
                        <div>
                            <h2>Pet Health Approval Queue</h2>
                            <small>Chronic alerts, passport requests, vaccination completion, microchip surgery, and diet approvals</small>
                        </div>
                        <span class="badge"><?= count($vetRequests) ?> pending</span>
                    </div>

                    <?php if (empty($vetRequests)): ?>
                        <div class="empty">No pet health requests need vet attention.</div>
                    <?php else: ?>
                        <div class="feed">
                            <?php foreach ($vetRequests as $request): ?>
                                <div class="request-item">
                                    <div class="request-top">
                                        <div>
                                            <div class="request-type"><?= htmlspecialchars(str_replace('_', ' ', $request['request_type'] ?? 'request')) ?></div>
                                            <strong><?= htmlspecialchars($request['title'] ?? 'Vet request') ?></strong>
                                            <p class="meta">
                                                <?= htmlspecialchars($request['pet_name'] ?? 'Unknown pet') ?>
                                                <?php if (!empty($request['species'])): ?> · <?= htmlspecialchars($request['species']) ?><?php endif; ?>
                                                    <?php if (!empty($request['owner_name'])): ?> · owner: <?= htmlspecialchars($request['owner_name']) ?><?php endif; ?>
                                                        <?php if (!empty($request['destination_country'])): ?> · destination: <?= htmlspecialchars($request['destination_country']) ?><?php endif; ?>
                                            </p>
                                        </div>
                                        <span class="badge"><?= htmlspecialchars($request['priority'] ?? 'normal') ?></span>
                                    </div>
                                    <p><?= htmlspecialchars($request['description'] ?? '') ?></p>

                                    <div class="request-actions">
                                        <form method="POST">
                                            <input type="hidden" name="action" value="resolve_vet_request">
                                            <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                            <input type="hidden" name="resolution" value="<?= ($request['request_type'] ?? '') === 'vaccination_completion' ? 'completed' : 'approved' ?>">
                                            <button class="action-btn" type="submit">
                                                <i class="fas fa-check"></i>
                                                <?= ($request['request_type'] ?? '') === 'vaccination_completion' ? 'Complete Vaccination' : 'Approve' ?>
                                            </button>
                                        </form>
                                        <form method="POST">
                                            <input type="hidden" name="action" value="resolve_vet_request">
                                            <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                            <input type="hidden" name="resolution" value="rejected">
                                            <button class="action-btn" type="submit"><i class="fas fa-xmark"></i> Reject</button>
                                        </form>
                                        <?php if (($request['request_type'] ?? '') === 'microchip_surgery'): ?>
                                            <a class="action-btn" href="index.php?url=clinical/surgeryManager"><i class="fas fa-briefcase-medical"></i> Surgery Manager</a>
                                        <?php endif; ?>
                                    </div>

                                    <form method="POST" class="mini-form">
                                        <input type="hidden" name="action" value="add_request_health_record">
                                        <input type="hidden" name="request_id" value="<?= (int) $request['id'] ?>">
                                        <input type="hidden" name="pet_id" value="<?= (int) $request['pet_id'] ?>">
                                        <input type="text" name="title" placeholder="Health record title">
                                        <textarea name="description" rows="2" placeholder="Vet notes, findings, passport document review, or follow-up plan"></textarea>
                                        <button class="action-btn" type="submit"><i class="fas fa-notes-medical"></i> Add Health Record</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>

            <section class="grid">
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Lab Snapshot</h2><small>Dashboard summary</small>
                        </div>
                    </div>
                    <div class="feed">
                        <div class="feed-item"><strong>Incoming Results</strong>
                            <p><?= (int) ($incomingLabStats['new'] ?? 0) ?> results are waiting review.</p>
                        </div>
                        <div class="feed-item"><strong>Critical Labs</strong>
                            <p><?= (int) ($incomingLabStats['critical'] ?? 0) ?> results need urgent attention.</p>
                        </div>
                        <div class="feed-item"><strong>Uninterpreted</strong>
                            <p><?= (int) ($incomingLabStats['uninterpreted'] ?? 0) ?> reports still need interpretation.</p>
                        </div>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Recent Workflow Requests</h2><small>Latest approvals state</small>
                        </div><span class="badge"><?= count($workflowRequests) ?> requests</span>
                    </div>
                    <?php if (empty($workflowRequests)): ?>
                        <div class="empty">No workflow requests yet.</div>
                    <?php else: ?>
                        <div class="feed">
                            <?php foreach (array_slice($workflowRequests, 0, 4) as $request): ?>
                                <div class="feed-item">
                                    <strong><?= htmlspecialchars($request['action_title'] ?? 'Workflow request') ?></strong>
                                    <p><?= htmlspecialchars($request['pet_name'] ?? 'Unknown pet') ?> - owner: <?= htmlspecialchars($request['owner_status'] ?? 'not_needed') ?> - admin: <?= htmlspecialchars($request['admin_status'] ?? 'not_needed') ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </article>
            </section>
        </main>
    </div>
    <?php require_once '../app/views/partials/theme_toggle.php'; ?>
</body>

</html>