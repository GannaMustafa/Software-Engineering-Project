<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clinical Operations | Paw Hubs</title>
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
        }

        * { box-sizing: border-box; }

        html { scroll-behavior: smooth; }

        body {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            background: linear-gradient(135deg, var(--mint), #ffffff 44%, var(--sky));
            color: var(--ink);
            min-height: 100vh;
            padding: 34px;
        }

        .app-frame {
            max-width: 1480px;
            min-height: calc(100vh - 68px);
            margin: 0 auto;
            display: grid;
            grid-template-columns: 290px 1fr;
            background: rgba(255, 255, 255, 0.9);
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
            font-size: 23px;
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
            min-height: 50px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 14px;
            color: var(--ink);
            text-decoration: none;
            font-weight: 700;
            font-size: 14px;
            line-height: 1.45;
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

        .content {
            padding: 26px;
            background: #f8fbfa;
        }

        .topbar,
        .panel,
        .stat-card {
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 18px 38px rgba(107, 181, 168, 0.08);
        }

        .topbar {
            height: 70px;
            display: grid;
            grid-template-columns: minmax(260px, 1fr) auto;
            align-items: center;
            gap: 18px;
            padding: 0 18px;
            margin-bottom: 22px;
        }

        .search {
            display: flex;
            align-items: center;
            gap: 12px;
            height: 46px;
            max-width: 560px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 14px;
            color: var(--muted);
            background: var(--soft);
        }

        .search input {
            border: 0;
            outline: 0;
            width: 100%;
            background: transparent;
            font: inherit;
        }

        .action-btn {
            height: 44px;
            padding: 0 16px;
            border: 1px solid var(--line);
            border-radius: 13px;
            background: #ffffff;
            color: var(--ink);
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 10px;
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
        }

        .page-head p {
            margin: 7px 0 0;
            color: var(--muted);
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
            grid-template-columns: repeat(3, minmax(160px, 1fr));
            gap: 16px;
            margin-bottom: 18px;
        }

        .stat-card {
            padding: 18px;
            min-height: 130px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 13px;
            display: grid;
            place-items: center;
            font-size: 18px;
        }

        .bg-teal { background: var(--mint); color: #4f9186; }
        .bg-green { background: var(--green); color: #ffffff; }
        .bg-olive { background: var(--olive); color: #4f6f35; }

        .stat-card span {
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .stat-card strong {
            display: block;
            margin-top: 5px;
            font-size: 29px;
        }

        .sections {
            display: grid;
            gap: 18px;
            margin-top: 18px;
        }

        .section-block {
            scroll-margin-top: 24px;
        }

        .section-intro {
            margin-bottom: 16px;
        }

        .section-intro h2 {
            margin: 0 0 6px;
            font-size: 26px;
        }

        .section-intro p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
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

        .panel small {
            color: var(--muted);
            font-weight: 700;
        }

        .list {
            display: grid;
            gap: 11px;
        }

        .item {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
            background: #ffffff;
        }

        .item strong {
            display: block;
            margin-bottom: 4px;
        }

        .item span {
            display: block;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .badge.critical,
        .badge.cancelled,
        .badge.rejected { background: #fff5f5; color: #e53e3e; }
        .badge.completed,
        .badge.normal,
        .badge.accepted { background: var(--green); color: #fff; }
        .badge.pending,
        .badge.scheduled { background: var(--olive); color: #4f6f35; }

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
            .stats { grid-template-columns: 1fr; }
        }

        @media (max-width: 640px) {
            .content { padding: 16px; }
            .topbar { grid-template-columns: 1fr; height: auto; padding: 14px; }
            .page-head { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
<?php
$role = $role ?? 'pet_owner';
$stats = $stats ?? [];
$procedures = $procedures ?? [];
$labReports = $labReports ?? [];
$referrals = $referrals ?? [];
?>
<div class="app-frame">
    <aside class="sidebar">
        <div class="brand"><i class="fas fa-stethoscope"></i><span>Paw Clinical</span></div>
        <div>
            <p class="menu-label">Clinical</p>
            <nav class="menu">
                <a class="active" href="#surgery-resource-manager"><i class="fas fa-briefcase-medical"></i> Surgery &amp; Procedure Resource Manager</a>
                <a href="#lab-result-hub"><i class="fas fa-vial-circle-check"></i> Lab Result Interpretation Hub</a>
                <a href="#referrals-workflow"><i class="fas fa-share-nodes"></i> Veterinary Referrals Workflow</a>
            </nav>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <label class="search">
                <i class="fas fa-search"></i>
                <input type="search" placeholder="Search procedures, lab reports, referrals">
            </label>
            <button class="action-btn" type="button"><?= htmlspecialchars($role) ?> workspace</button>
        </div>

        <header class="page-head">
            <div>
                <h1>Clinical Operations</h1>
                <p>Clinical content is organized into three focused workspaces for procedures, lab interpretation, and referrals.</p>
            </div>
            <span class="role-pill"><?= htmlspecialchars($role) ?> access</span>
        </header>

        <section class="stats">
            <article class="stat-card"><div class="stat-icon bg-teal"><i class="fas fa-briefcase-medical"></i></div><div><span>Medical Procedures</span><strong><?= (int) ($stats['procedures'] ?? 0) ?></strong></div></article>
            <article class="stat-card"><div class="stat-icon bg-green"><i class="fas fa-vial-circle-check"></i></div><div><span>Lab Reports</span><strong><?= (int) ($stats['lab_reports'] ?? 0) ?></strong></div></article>
            <article class="stat-card"><div class="stat-icon bg-olive"><i class="fas fa-share-nodes"></i></div><div><span>Referral Requests</span><strong><?= (int) ($stats['referrals'] ?? 0) ?></strong></div></article>
        </section>

        <section class="sections">
            <section class="section-block" id="surgery-resource-manager">
                <div class="section-intro">
                    <h2>Surgery &amp; Procedure Resource Manager</h2>
                    <p>Track procedure history, review case status, and keep surgery-related activity grouped in one place.</p>
                </div>
                <div class="panel">
                    <div class="panel-head"><div><h2>Medical Procedures History</h2><small>Surgery &amp; procedure resource manager</small></div><span class="badge"><?= count($procedures) ?> shown</span></div>
                    <div class="list">
                        <?php if (empty($procedures)): ?>
                            <div class="empty">No procedures recorded yet.</div>
                        <?php else: ?>
                            <?php foreach ($procedures as $procedure): ?>
                                <div class="item">
                                    <div>
                                        <strong><?= htmlspecialchars($procedure['procedure_name']) ?></strong>
                                        <span><?= htmlspecialchars($procedure['pet_name'] ?? 'Unknown pet') ?> - <?= htmlspecialchars($procedure['procedure_type'] ?? 'Procedure') ?> - Dr. <?= htmlspecialchars($procedure['vet_name'] ?? 'Unassigned') ?></span>
                                        <span><?= htmlspecialchars($procedure['procedure_date'] ?? date('Y-m-d', strtotime($procedure['created_at']))) ?></span>
                                    </div>
                                    <span class="badge <?= htmlspecialchars(strtolower($procedure['status'] ?? 'scheduled')) ?>"><?= htmlspecialchars($procedure['status'] ?? 'scheduled') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="section-block" id="lab-result-hub">
                <div class="section-intro">
                    <h2>Lab Result Interpretation Hub</h2>
                    <p>Browse submitted lab reports, review summaries, and see interpretation status in one dedicated section.</p>
                </div>
                <div class="panel">
                    <div class="panel-head"><div><h2>Lab Reports Section</h2><small>Lab result interpretation hub</small></div><span class="badge"><?= count($labReports) ?> shown</span></div>
                    <div class="list">
                        <?php if (empty($labReports)): ?>
                            <div class="empty">No lab reports recorded yet.</div>
                        <?php else: ?>
                            <?php foreach ($labReports as $report): ?>
                                <div class="item">
                                    <div>
                                        <strong><?= htmlspecialchars($report['test_name']) ?></strong>
                                        <span><?= htmlspecialchars($report['pet_name'] ?? 'Unknown pet') ?> - <?= htmlspecialchars($report['result_summary'] ?? 'No summary') ?></span>
                                        <span><?= htmlspecialchars($report['interpretation'] ?? 'Waiting for interpretation') ?></span>
                                    </div>
                                    <span class="badge <?= htmlspecialchars(strtolower($report['status'] ?? 'pending')) ?>"><?= htmlspecialchars($report['status'] ?? 'pending') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="section-block" id="referrals-workflow">
                <div class="section-intro">
                    <h2>Veterinary Referrals Workflow</h2>
                    <p>Follow referral requests between veterinarians and keep specialty transfers organized in one workflow.</p>
                </div>
                <div class="panel">
                    <div class="panel-head"><div><h2>Referral Requests</h2><small>Veterinary referrals workflow</small></div><span class="badge"><?= count($referrals) ?> shown</span></div>
                    <div class="list">
                        <?php if (empty($referrals)): ?>
                            <div class="empty">No referral requests yet.</div>
                        <?php else: ?>
                            <?php foreach ($referrals as $referral): ?>
                                <div class="item">
                                    <div>
                                        <strong><?= htmlspecialchars($referral['specialty'] ?? 'Referral request') ?></strong>
                                        <span><?= htmlspecialchars($referral['pet_name'] ?? 'Unknown pet') ?> - From <?= htmlspecialchars($referral['from_vet'] ?? 'Unassigned') ?> to <?= htmlspecialchars($referral['to_vet'] ?? 'Unassigned') ?></span>
                                        <span><?= htmlspecialchars($referral['reason'] ?? 'No reason provided') ?></span>
                                    </div>
                                    <span class="badge <?= htmlspecialchars(strtolower($referral['status'] ?? 'pending')) ?>"><?= htmlspecialchars($referral['priority'] ?? 'normal') ?> / <?= htmlspecialchars($referral['status'] ?? 'pending') ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
        </section>
    </main>
</div>
</body>
</html>
