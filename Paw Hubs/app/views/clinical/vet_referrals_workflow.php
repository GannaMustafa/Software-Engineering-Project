<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Referrals Workflow | Paw Hubs</title>
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
            background: #fff;
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

        .brand i {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            display: grid;
            place-items: center;
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
            background: #fff;
            color: var(--ink);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            text-decoration: none;
            cursor: pointer;
        }

        .action-btn.primary {
            background: var(--teal);
            border-color: transparent;
            color: #fff;
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

        .badge.urgent,
        .badge.critical {
            background: #fff5f5;
            color: var(--danger);
        }

        .badge.completed,
        .badge.approved {
            background: var(--green);
            color: #fff;
        }

        .badge.pending {
            background: var(--olive);
            color: #4f6f35;
        }

        .stats,
        .grid {
            display: grid;
            gap: 16px;
            margin-bottom: 18px;
        }

        .stats {
            grid-template-columns: repeat(3, minmax(150px, 1fr));
        }

        .grid {
            grid-template-columns: minmax(320px, 0.9fr) minmax(0, 1.1fr);
        }

        .stat-card {
            min-height: 126px;
            padding: 18px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            font-size: 18px;
            background: var(--mint);
            color: #4f9186;
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

        .form-grid,
        .card-list {
            display: grid;
            gap: 12px;
        }

        .split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 12px;
        }

        .input-group {
            display: grid;
            gap: 8px;
        }

        .input-group label {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .form-control {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 12px 14px;
            background: #fff;
            font: inherit;
            color: var(--ink);
        }

        textarea.form-control {
            min-height: 110px;
            resize: vertical;
        }

        .item-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 14px;
            background: #fff;
            display: grid;
            gap: 10px;
        }

        .item-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .item-card strong {
            display: block;
            margin-bottom: 4px;
        }

        .item-card p {
            margin: 0;
            color: var(--muted);
            line-height: 1.55;
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

            .app-frame,
            .grid {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
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

            .stats,
            .split {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php
    $stats = $stats ?? [];
    $referrals = $referrals ?? [];
    $transferCases = $transferCases ?? [];
    $specialists = $specialists ?? [];
    ?>
    <div class="app-frame">
        <aside class="sidebar">
            <div class="brand"><i class="fas fa-share-nodes"></i><span>Paw Referrals</span></div>
            <div>
                <p class="menu-label">Clinical</p>
                <nav class="menu">
                    <a href="index.php?url=clinical/vetDashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
                    <a href="index.php?url=clinical/surgeryManager"><i class="fas fa-briefcase-medical"></i> Surgery Manager</a>
                    <a href="index.php?url=clinical/labHub"><i class="fas fa-vial-circle-check"></i> Lab Hub</a>
                    <a class="active" href="index.php?url=clinical/referralsWorkflow"><i class="fas fa-share-nodes"></i> Referrals Workflow</a>
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
                <label class="search"><i class="fas fa-search"></i><input type="search" placeholder="Search referrals, specialists, pets"></label>
                <a class="action-btn" href="index.php?url=clinical/vetDashboard"><i class="fas fa-arrow-left"></i> Dashboard</a>
            </div>

            <header class="page-head">
                <div>
                    <h1>Referrals Workflow</h1>
                    <p>Transfer cases to specialists, review incoming and outgoing referral activity, and keep specialty handoffs organized.</p>
                </div>
                <span class="role-pill">Vet access</span>
            </header>

            <?php if (!empty($message)): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <?php if (!empty($errors)): ?><div class="notice error"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>

            <section class="stats">
                <article class="stat-card">
                    <div class="stat-icon"><i class="fas fa-share-nodes"></i></div>
                    <div><span>Referrals</span><strong><?= (int) ($stats['referrals'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-sky"><i class="fas fa-user-doctor"></i></div>
                    <div><span>Specialists</span><strong><?= (int) ($stats['specialists'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-olive"><i class="fas fa-triangle-exclamation"></i></div>
                    <div><span>Urgent</span><strong><?= (int) ($stats['urgent'] ?? 0) ?></strong></div>
                </article>
            </section>

            <section class="grid">
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Transfer Case</h2>
                            <small>Create a specialist referral</small>
                        </div>
                    </div>
                    <form method="POST" class="form-grid">
                        <input type="hidden" name="action" value="transfer_referral_case">
                        <div class="input-group">
                            <label>Case</label>
                            <select class="form-control" name="pet_id" required>
                                <option value="">Choose a case</option>
                                <?php foreach ($transferCases as $case): ?>
                                    <option value="<?= (int) ($case['pet_id'] ?? 0) ?>"><?= htmlspecialchars(($case['pet_name'] ?? 'Unknown pet') . ' - ' . ($case['summary'] ?? 'Clinical case')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="input-group">
                            <label>Specialist</label>
                            <select class="form-control" name="to_vet_id" required>
                                <option value="">Choose specialist</option>
                                <?php foreach ($specialists as $specialist): ?>
                                    <option value="<?= (int) ($specialist['id'] ?? 0) ?>"><?= htmlspecialchars(($specialist['username'] ?? 'Vet') . ' - ' . ($specialist['specialization'] ?? 'General')) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="split">
                            <div class="input-group">
                                <label>Specialty</label>
                                <input class="form-control" name="specialty" placeholder="Dermatology, surgery, cardiology" required>
                            </div>
                            <div class="input-group">
                                <label>Priority</label>
                                <select class="form-control" name="priority">
                                    <option value="normal">Normal</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <label>Reason</label>
                            <textarea class="form-control" name="reason" placeholder="Clinical reason, findings, and requested specialist support" required></textarea>
                        </div>
                        <button class="action-btn primary" type="submit"><i class="fas fa-paper-plane"></i> Transfer Case</button>
                    </form>
                </article>

                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Referral Requests</h2>
                            <small>Incoming and outgoing transfers</small>
                        </div>
                        <span class="badge"><?= count($referrals) ?> shown</span>
                    </div>
                    <div class="card-list">
                        <?php if (empty($referrals)): ?>
                            <div class="empty">No referral requests have been created yet.</div>
                        <?php else: ?>
                            <?php foreach ($referrals as $referral): ?>
                                <article class="item-card">
                                    <div class="item-top">
                                        <div>
                                            <strong><?= htmlspecialchars($referral['specialty'] ?? 'Referral request') ?></strong>
                                            <p><?= htmlspecialchars($referral['pet_name'] ?? 'Unknown pet') ?> - from <?= htmlspecialchars($referral['from_vet'] ?? 'Unassigned') ?> to <?= htmlspecialchars($referral['to_vet'] ?? 'Unassigned') ?></p>
                                        </div>
                                        <span class="badge <?= htmlspecialchars(strtolower($referral['priority'] ?? 'normal')) ?>"><?= htmlspecialchars($referral['priority'] ?? 'normal') ?> / <?= htmlspecialchars($referral['status'] ?? 'pending') ?></span>
                                    </div>
                                    <p><?= htmlspecialchars($referral['reason'] ?? 'No reason provided.') ?></p>
                                    <?php if (!empty($referral['notes'])): ?>
                                        <p><?= htmlspecialchars($referral['notes']) ?></p>
                                    <?php endif; ?>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Specialist Directory</h2>
                        <small>Available referral destinations</small>
                    </div>
                    <span class="badge"><?= count($specialists) ?> vets</span>
                </div>
                <div class="card-list">
                    <?php if (empty($specialists)): ?>
                        <div class="empty">No specialists are available in the directory yet.</div>
                    <?php else: ?>
                        <?php foreach ($specialists as $specialist): ?>
                            <article class="item-card">
                                <div class="item-top">
                                    <div>
                                        <strong><?= htmlspecialchars($specialist['username'] ?? 'Veterinarian') ?></strong>
                                        <p><?= htmlspecialchars($specialist['specialization'] ?? 'General practice') ?> - <?= htmlspecialchars($specialist['license_number'] ?? 'License not recorded') ?></p>
                                    </div>
                                    <span class="badge"><?= (int) ($specialist['referrals_count'] ?? 0) ?> referrals</span>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
