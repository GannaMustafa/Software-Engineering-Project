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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lab Result Interpretation Hub | Paw Hubs</title>
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
            cursor: url('../public/images/icons8-dog-paw-34.png'), auto;
            font-family: 'Outfit', sans-serif;

        }

        main {
            margin: 0;
            min-height: 100vh;
            padding: 34px;
            font-family: 'Outfit', sans-serif;
            color: var(--ink);
            background: linear-gradient(135deg, var(--mint), #ffffff 44%, var(--sky));
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
            display: grid;
            gap: 10px;
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
            width: 100%;
            border: 0;
            outline: 0;
            background: transparent;
            font: inherit;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 12px;
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

        .action-btn.primary {
            background: var(--teal);
            color: #fff;
            border-color: transparent;
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

        .hero-band {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) 280px;
            gap: 18px;
            margin-bottom: 18px;
        }

        .hero-copy {
            min-height: 180px;
            padding: 24px;
            border-radius: 18px;
            border: 1px solid var(--line);
            background:
                linear-gradient(120deg, rgba(200, 228, 214, 0.95), rgba(255, 255, 255, 0.95)),
                url("<?= htmlspecialchars(asset('images/hero-dog-cat.png')) ?>") right bottom / auto 100% no-repeat;
            box-shadow: 0 18px 38px rgba(107, 181, 168, 0.08);
        }

        .hero-copy strong {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            border-radius: 999px;
            background: #ffffff;
            color: #4f9186;
            font-size: 13px;
        }

        .hero-copy h2 {
            margin: 18px 0 10px;
            font-size: 29px;
        }

        .hero-copy p {
            margin: 0;
            color: var(--muted);
            line-height: 1.65;
            font-weight: 700;
            max-width: 620px;
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
            color: #4f9186;
        }

        .bg-green {
            background: var(--green);
            color: #ffffff;
        }

        .bg-olive {
            background: var(--olive);
            color: #4f6f35;
        }

        .bg-sky {
            background: var(--sky);
            color: #ffffff;
        }

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

        .grid {
            display: grid;
            grid-template-columns: minmax(320px, 0.95fr) minmax(0, 1.05fr);
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

        .panel small,
        .meta {
            color: var(--muted);
            font-weight: 700;
        }

        .notice {
            border-radius: 16px;
            padding: 16px 18px;
            margin-bottom: 16px;
            line-height: 1.6;
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

        .insight-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            background: var(--soft);
            margin-bottom: 14px;
        }

        .insight-card strong {
            display: block;
            margin-bottom: 10px;
        }

        .insight-card ul {
            margin: 0;
            padding-left: 18px;
            color: var(--muted);
            line-height: 1.7;
        }

        .form-grid {
            display: grid;
            gap: 14px;
        }

        .split {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
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
            background: #ffffff;
            font: inherit;
            color: var(--ink);
        }

        textarea.form-control {
            min-height: 96px;
            resize: vertical;
        }

        .report-list {
            display: grid;
            gap: 12px;
        }

        .report-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 13px;
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: start;
            background: #ffffff;
        }

        .report-card strong {
            display: block;
            margin-bottom: 4px;
        }

        .report-card p {
            margin: 8px 0 0;
            line-height: 1.55;
        }

        .meta {
            display: block;
            font-size: 13px;
            line-height: 1.45;
        }

        .badge.critical {
            background: #fff5f5;
            color: var(--danger);
        }

        .badge.completed,
        .badge.normal {
            background: var(--green);
            color: #fff;
        }

        .badge.pending {
            background: var(--olive);
            color: #4f6f35;
        }

        .badge.reviewed {
            background: var(--sky);
            color: #fff;
        }

        .status-strip {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 8px;
            margin: 12px 0;
        }

        .indicator {
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px;
            background: var(--soft);
            font-size: 12px;
            font-weight: 800;
            color: var(--muted);
        }

        .indicator strong {
            display: block;
            margin: 2px 0 0;
            color: var(--ink);
            font-size: 13px;
        }

        .indicator.abnormal strong {
            color: var(--danger);
        }

        .indicator.normal strong {
            color: #4f9186;
        }

        .technical-box,
        .review-form {
            border: 1px solid var(--line);
            border-radius: 14px;
            background: var(--soft);
            padding: 16px;
            margin: 14px 0 16px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--ink);
        }

        .technical-box h4,
        .review-form h4 {
            margin: 0 0 8px;
            font-size: 14px;
        }

        .technical-box pre {
            margin: 0;
            white-space: pre-wrap;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.55;
        }

        .review-form {
            display: grid;
            gap: 10px;
        }

        .review-form .action-btn {
            width: max-content;
            margin-top: 4px;
        }

        .file-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
            color: #4f9186;
            text-decoration: none;
            font-weight: 700;
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
            body {
                padding: 16px;
            }

            .app-frame {
                grid-template-columns: 1fr;
            }

            .sidebar {
                display: none;
            }

            .hero-band,
            .grid {
                grid-template-columns: 1fr;
            }

            .stats {
                grid-template-columns: repeat(2, minmax(150px, 1fr));
            }
        }

        @media (max-width: 640px) {
            .content {
                padding: 16px;
            }

            .topbar {
                grid-template-columns: 1fr;
                height: auto;
                padding: 14px;
            }

            .top-actions {
                flex-wrap: wrap;
            }

            .page-head {
                flex-direction: column;
                align-items: flex-start;
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
    $role = $role ?? 'pet_owner';
    $pets = $pets ?? [];
    $vets = $vets ?? [];
    $reports = $reports ?? [];
    $stats = $stats ?? ['total' => 0, 'critical' => 0, 'normal' => 0, 'pending' => 0];
    $isVet = $role === 'vet';
    $splitLines = function ($text) {
        $lines = preg_split('/\R+/', trim((string) $text));
        return array_values(array_filter($lines ?: [], fn($line) => trim($line) !== ''));
    };
    ?>
    <?php if ($role === 'pet_owner') {
        require_once  '../app/views/partials/navbar.php';
    }; ?>
    <main>
        <div class="app-frame">
            <aside class="sidebar">

                <div class="brand"><i class="fas fa-vial-circle-check"></i><span>Paw Clinical</span></div>
                <div>
                    <p class="menu-label">Clinical</p>
                    <nav class="menu">
                        <a href="index.php?url=<?= $role === 'vet' ? 'clinical/vetDashboard' : 'clinical/index' ?>"><i class="fas fa-chart-pie"></i> Dashboard</a>
                        <a class="active" href="index.php?url=clinical/labHub"><i class="fas fa-vial"></i> Lab Hub</a>
                        <?php if ($role === 'vet'): ?>
                            <a href="index.php?url=clinical/surgeryManager"><i class="fas fa-calendar-check"></i> Surgery Manager</a>
                            <a href="index.php?url=clinical/referralsWorkflow"><i class="fas fa-share-nodes"></i> Referrals Workflow</a>
                        <?php endif; ?>
                        <?php if ($role === 'pet_owner'): ?>
                            <a href="/Pet Health page/pet-health.php"><i class="fas fa-paw"></i> Pet Medical Record</a>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php if ($role === 'vet'): ?>
                    <div class="sidebar-footer">
                        <nav class="menu">
                            <a href="index.php?url=auth/logout"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
                        </nav>
                    </div><?php endif; ?>
            </aside>

            <main class="content">
                <div class="topbar">
                    <label class="search">
                        <i class="fas fa-search"></i>
                        <input type="search" placeholder="Search lab tests, pets, reports">
                    </label>
                    <div class="top-actions">
                        <button class="action-btn primary" type="submit" form="labUploadForm"><i class="fas fa-wand-magic-sparkles"></i> <?= $isVet ? 'Save Technical Review' : 'Upload Result' ?></button>
                    </div>
                </div>

                <header class="page-head">
                    <div>
                        <h1>Lab Result Interpretation Hub</h1>
                        <p><?= $isVet ? 'Review uploaded diagnostics, keep reference ranges and detailed values intact, add interpretation notes, and send simplified findings back to the owner.' : 'View plain-language lab insights, visual normal/elevated/low indicators, key findings, and recommended follow-up actions saved to your pet record.' ?></p>
                    </div>
                    <span class="role-pill"><?= htmlspecialchars($role) ?> access</span>
                </header>

                <section class="hero-band">
                    <div class="hero-copy">
                        <strong><i class="fas fa-flask"></i> Lab Result Interpretation Hub</strong>
                        <h2>Diagnostic upload and interpretation in one place</h2>
                        <p>Keep lab files, result summaries, report status, and easy-to-read owner insights together so the clinical team and the pet owner see one organized story.</p>
                    </div>
                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Quick Snapshot</h2>
                                <small><?= $isVet ? 'Cases needing review' : 'Your pet results' ?></small>
                            </div>
                        </div>
                        <div class="report-list">
                            <div class="report-card">
                                <div><strong>Total Reports</strong><span class="meta">All visible results</span></div>
                                <span class="badge"><?= (int) $stats['total'] ?></span>
                            </div>
                            <div class="report-card">
                                <div><strong>Abnormal Flags</strong><span class="meta">Need attention</span></div>
                                <span class="badge critical"><?= (int) ($stats['abnormal'] ?? $stats['critical']) ?></span>
                            </div>
                            <div class="report-card">
                                <div><strong>Vet Reviewed</strong><span class="meta">Completed notes</span></div>
                                <span class="badge reviewed"><?= (int) ($stats['reviewed'] ?? 0) ?></span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="stats">
                    <article class="stat-card">
                        <div class="stat-icon"><i class="fas fa-file-medical"></i></div>
                        <div><span>Total Reports</span><strong><?= (int) $stats['total'] ?></strong></div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-icon bg-green"><i class="fas fa-circle-check"></i></div>
                        <div><span>Normal</span><strong><?= (int) $stats['normal'] ?></strong></div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-icon bg-olive"><i class="fas fa-clock"></i></div>
                        <div><span>Pending Review</span><strong><?= (int) $stats['pending'] ?></strong></div>
                    </article>
                    <article class="stat-card">
                        <div class="stat-icon bg-sky"><i class="fas fa-triangle-exclamation"></i></div>
                        <div><span>Abnormal / Critical</span><strong><?= (int) (($stats['abnormal'] ?? 0) + $stats['critical']) ?></strong></div>
                    </article>
                </section>

                <section class="grid">
                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2><?= $isVet ? 'Upload And Interpret Result' : 'Upload Diagnostic Result' ?></h2>
                                <small><?= $isVet ? 'Attach file, preserve technical data, and publish notes' : 'Attach a PDF/image or paste values for vet review' ?></small>
                            </div>
                        </div>

                        <?php if (!empty($message)): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
                        <?php if (!empty($errors)): ?><div class="notice error"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>

                        <?php if (!empty($preview)): ?>
                            <div class="insight-card">
                                <strong><i class="fas fa-lightbulb"></i> Generated simplified insight</strong>
                                <ul>
                                    <?php foreach (explode("\n", $preview) as $line): ?>
                                        <li><?= htmlspecialchars($line) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <form id="labUploadForm" class="form-grid" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="upload_lab_report">
                            <div class="split">
                                <div class="input-group">
                                    <label>Pet</label>
                                    <select class="form-control" name="pet_id" required>
                                        <option value="">Choose pet</option>
                                        <?php foreach ($pets as $pet): ?>
                                            <option value="<?= (int) $pet['id'] ?>"><?= htmlspecialchars($pet['name'] . ' - ' . ($pet['species'] ?? 'Pet')) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <?php if ($role !== 'vet'): ?>
                                    <div class="input-group">
                                        <label>Assigned Vet</label>
                                        <select class="form-control" name="vet_id">
                                            <option value="">Unassigned</option>
                                            <?php foreach ($vets as $vet): ?>
                                                <option value="<?= (int) $vet['id'] ?>"><?= htmlspecialchars(($vet['username'] ?? 'Vet') . ' - ' . ($vet['specialization'] ?? 'General')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="split">
                                <div class="input-group">
                                    <label>Test Name</label>
                                    <input class="form-control" name="test_name" placeholder="CBC, glucose, liver profile" required>
                                </div>
                                <div class="input-group">
                                    <label>Test Type</label>
                                    <select class="form-control" name="test_type">
                                        <option value="">Auto categorize</option>
                                        <option value="Blood count">Blood count</option>
                                        <option value="Kidney and urinary">Kidney and urinary</option>
                                        <option value="Liver profile">Liver profile</option>
                                        <option value="Glucose">Glucose</option>
                                        <option value="Diagnostic imaging">Diagnostic imaging</option>
                                        <option value="General lab">General lab</option>
                                    </select>
                                </div>
                            </div>

                            <div class="split">
                                <div class="input-group">
                                    <label>Report Date</label>
                                    <input class="form-control" type="date" name="report_date" value="<?= htmlspecialchars(date('Y-m-d')) ?>">
                                </div>
                                <div class="input-group">
                                    <label>Status</label>
                                    <select class="form-control" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="normal">Normal</option>
                                        <option value="critical">Critical</option>
                                        <option value="completed">Completed</option>
                                    </select>
                                </div>
                                <div class="input-group">
                                    <label>Report File</label>
                                    <input class="form-control" type="file" name="lab_file" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                </div>
                            </div>

                            <div class="input-group">
                                <label>Short Result Summary</label>
                                <textarea class="form-control" name="result_summary" placeholder="Example: WBC mildly high, glucose normal, ALT elevated."></textarea>
                            </div>

                            <div class="input-group">
                                <label>Raw Values</label>
                                <textarea class="form-control" name="raw_values" placeholder="Example: WBC: 18 high&#10;ALT: 140 elevated&#10;Glucose: 95 normal"></textarea>
                            </div>

                            <div class="input-group">
                                <label>Reference Ranges</label>
                                <textarea class="form-control" name="reference_ranges" placeholder="Example: WBC 6-17, ALT 10-125, Glucose 70-120"></textarea>
                            </div>

                            <div class="input-group">
                                <label>Symptoms Or Notes</label>
                                <textarea class="form-control" name="notes" placeholder="Appetite, vomiting, medication, hydration, behavior changes."></textarea>
                            </div>
                        </form>
                    </div>

                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Recent Lab Insights</h2>
                                <small><?= $isVet ? 'Technical reports plus owner-facing summaries' : 'Simplified summaries saved with each report' ?></small>
                            </div>
                            <span class="badge"><?= count($reports) ?> shown</span>
                        </div>

                        <div class="report-list">
                            <?php if (empty($reports)): ?>
                                <div class="empty">No lab reports yet. Upload the first result to generate a simplified insight.</div>
                            <?php else: ?>
                                <?php foreach ($reports as $report): ?>
                                    <?php
                                    $flags = $splitLines($report['abnormal_flags'] ?? '');
                                    $followUps = $splitLines($report['follow_up_actions'] ?? '');
                                    $statusText = strtolower($report['status'] ?? 'pending');
                                    $indicatorClass = !empty($flags) || $statusText === 'critical' ? 'abnormal' : ($statusText === 'normal' ? 'normal' : '');
                                    ?>
                                    <article class="report-card">
                                        <div>
                                            <strong><?= htmlspecialchars($report['test_name'] ?? 'Lab test') ?></strong>
                                            <span class="meta"><?= htmlspecialchars($report['pet_name'] ?? 'Unknown pet') ?> | <?= htmlspecialchars($report['owner_name'] ?? 'Owner') ?> | <?= htmlspecialchars($report['test_type'] ?? 'General lab') ?> | <?= htmlspecialchars($report['report_date'] ?? date('Y-m-d', strtotime($report['created_at'] ?? 'now'))) ?></span>
                                            <div class="status-strip">
                                                <div class="indicator <?= htmlspecialchars($indicatorClass) ?>">Result status<strong><?= htmlspecialchars($report['status'] ?? 'pending') ?></strong></div>
                                                <div class="indicator <?= !empty($flags) ? 'abnormal' : 'normal' ?>">Key findings<strong><?= !empty($flags) ? count($flags) . ' flagged' : 'No flags' ?></strong></div>
                                                <div class="indicator">Vet review<strong><?= !empty($report['reviewed_at']) ? 'Completed' : 'Pending' ?></strong></div>
                                            </div>
                                            <p><?= nl2br(htmlspecialchars($report['interpretation'] ?? $report['result_summary'] ?? 'Waiting for interpretation.')) ?></p>
                                            <?php if (!empty($flags)): ?>
                                                <div class="technical-box">
                                                    <h4><i class="fas fa-triangle-exclamation"></i> Key Findings</h4>
                                                    <pre><?= htmlspecialchars(implode("\n", $flags)) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                            <?php if (!empty($followUps)): ?>
                                                <div class="technical-box">
                                                    <h4><i class="fas fa-list-check"></i> Follow-Up Actions</h4>
                                                    <pre><?= htmlspecialchars(implode("\n", $followUps)) ?></pre>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($isVet): ?>
                                                <div class="technical-box">
                                                    <h4><i class="fas fa-microscope"></i> Technical Data</h4>
                                                    <pre><?= htmlspecialchars(trim("Values:\n" . ($report['raw_values'] ?? 'Not provided') . "\n\nReference ranges:\n" . ($report['reference_ranges'] ?? 'Not provided') . "\n\nHistorical comparison:\nCompare with earlier reports for " . ($report['pet_name'] ?? 'this pet') . '.')) ?></pre>
                                                </div>
                                                <form class="review-form" method="post">
                                                    <input type="hidden" name="action" value="interpret_lab_result">
                                                    <input type="hidden" name="report_id" value="<?= (int) $report['id'] ?>">
                                                    <h4><i class="fas fa-user-doctor"></i> Vet Interpretation Notes</h4>
                                                    <input class="form-control" name="diagnosis" placeholder="Clinical impression or diagnosis" value="<?= htmlspecialchars($report['status'] === 'completed' ? 'Reviewed lab result' : '') ?>" required>
                                                    <textarea class="form-control" name="interpretation_notes" placeholder="Reference-range interpretation, trend comparison, and clinical notes"><?= htmlspecialchars($report['vet_notes'] ?? '') ?></textarea>
                                                    <div class="split">
                                                        <input class="form-control" name="linked_disease" placeholder="Linked disease, if any">
                                                        <input class="form-control" name="extra_tests" placeholder="Additional tests needed">
                                                    </div>
                                                    <button class="action-btn primary" type="submit"><i class="fas fa-check"></i> Save Review</button>
                                                </form>
                                            <?php endif; ?>
                                            <?php if (!empty($report['file_path'])): ?>
                                                <a class="file-link" href="<?= htmlspecialchars(asset('uploads/' . $report['file_path'])) ?>" target="_blank"><i class="fas fa-paperclip"></i> View uploaded file</a>
                                            <?php endif; ?>
                                        </div>
                                        <span class="badge <?= htmlspecialchars(strtolower($report['status'] ?? 'pending')) ?>"><?= htmlspecialchars($report['status'] ?? 'pending') ?></span>
                                    </article>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </main>
</body>

</html>