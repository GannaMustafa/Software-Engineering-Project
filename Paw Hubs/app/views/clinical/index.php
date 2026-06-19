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

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            cursor: url('../public/images/icons8-dog-paw-34.png'), auto;
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
            background: #ffffff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 18px 38px rgba(107, 181, 168, 0.08);
        }

        .topbar {
            height: 70px;
            display: flex;
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
            margin-right: 470px;
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
            text-decoration: none;
        }

        .action-btn.primary {
            background: var(--teal);
            color: #ffffff;
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

        .bg-teal {
            background: var(--mint);
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
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 16px;
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

        .item-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .inline-resource-form {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            padding: 14px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: var(--soft);
            margin-top: 8px;
        }

        .inline-resource-form .full {
            grid-column: 1 / -1;
        }

        .inline-resource-form label {
            display: grid;
            gap: 7px;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
        }

        .inline-resource-form .form-control {
            width: 100%;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 11px 12px;
            background: #ffffff;
            font: inherit;
            color: var(--ink);
        }

        .resource-note {
            grid-column: 1 / -1;
            color: var(--muted);
            font-size: 13px;
            font-weight: 700;
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
        .badge.rejected {
            background: #fff5f5;
            color: #e53e3e;
        }

        .badge.completed,
        .badge.normal,
        .badge.accepted {
            background: var(--green);
            color: #fff;
        }

        .badge.pending,
        .badge.scheduled {
            background: var(--olive);
            color: #4f6f35;
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

            .stats {
                grid-template-columns: 1fr;
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

            .page-head,
            .section-intro {
                flex-direction: column;
                align-items: stretch;
            }

            .item {
                grid-template-columns: 1fr;
            }

            .item-actions {
                justify-content: flex-start;
            }

            .inline-resource-form {
                grid-template-columns: 1fr;
            }
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
    $pets = $pets ?? [];
    $specialists = $specialists ?? [];
    $operatingRooms = $operatingRooms ?? [];
    $equipment = $equipment ?? [];
    $message = $message ?? null;
    $errors = $errors ?? [];
    $ownerSurgeryRequests = $ownerSurgeryRequests ?? [];
    $transferCases = $transferCases ?? [];
    $permissions = $permissions ?? [];
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
            <?php if ($role === 'vet'): ?>
                <div class="sidebar-footer">
                    <nav class="menu">
                        <a href="index.php?url=auth/logout"><i class="fas fa-arrow-right-from-bracket"></i> Logout</a>
                    </nav>
                </div>
            <?php endif; ?>
        </aside>

        <main class="content">
            <div class="topbar">
                <label class="search">
                    <i class="fas fa-search"></i>
                    <input type="search" placeholder="Search procedures, lab reports, referrals">
                </label>
                <?php if ($role === 'pet_owner'): ?>
                    <a class="action-btn" href="<?= 'index.php?url=clinical/labHub' ?>"><i class="fas fa-arrow-left"></i> Back</a>
                <?php endif; ?>
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
                <article class="stat-card">
                    <div class="stat-icon bg-teal"><i class="fas fa-briefcase-medical"></i></div>
                    <div><span>Medical Procedures</span><strong><?= (int) ($stats['procedures'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-green"><i class="fas fa-vial-circle-check"></i></div>
                    <div><span>Lab Reports</span><strong><?= (int) ($stats['lab_reports'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-olive"><i class="fas fa-share-nodes"></i></div>
                    <div><span>Referral Requests</span><strong><?= (int) ($stats['referrals'] ?? 0) ?></strong></div>
                </article>
            </section>

            <section class="sections">
                <section class="section-block" id="surgery-resource-manager">
                    <div class="section-intro">
                        <div>
                            <h2>Surgery &amp; Procedure Resource Manager</h2>
                            <p>Track procedure history, review case status, and keep surgery-related activity grouped in one place.</p>
                        </div>
                    </div>
                    <?php if ($role === 'pet_owner'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Request Surgery</h2><small>Schedule a procedure for your pet</small>
                                </div>
                            </div>
                            <form method="POST" style="padding: 20px;">
                                <input type="hidden" name="action" value="request_surgery">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                    <select name="pet_id" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="">Select Pet</option>
                                        <?php foreach ($pets as $pet): ?>
                                            <option value="<?= $pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['species']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="procedure_type" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="">Procedure Type</option>
                                        <option value="spay">Spay</option>
                                        <option value="neuter">Neuter</option>
                                        <option value="dental">Dental Surgery</option>
                                        <option value="orthopedic">Orthopedic</option>
                                    </select>
                                    <select name="urgency" style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="normal">Normal</option>
                                        <option value="urgent">Urgent</option>
                                        <option value="emergency">Emergency</option>
                                    </select>
                                </div>
                                <textarea name="reason" placeholder="Reason for surgery" required style="width: 100%; padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px; margin-bottom: 16px;"></textarea>
                                <button type="submit" style="padding: 12px 24px; background: var(--teal); color: white; border: none; border-radius: 8px;">Request Surgery</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Medical Procedures History</h2><small>Surgery &amp; procedure resource manager</small>
                            </div><span class="badge"><?= count($procedures) ?> shown</span>
                        </div>
                        <div class="list">
                            <?php if (empty($procedures)): ?>
                                <div class="empty">No procedures recorded yet.</div>
                            <?php else: ?>
                                <?php foreach ($procedures as $procedure): ?>
                                    <?php $procedureStatus = strtolower($procedure['status'] ?? 'scheduled'); ?>
                                    <div class="item">
                                        <div>
                                            <strong><?= htmlspecialchars($procedure['procedure_name'] ?? $procedure['procedure_type'] ?? 'Procedure') ?></strong>
                                            <span><?= htmlspecialchars($procedure['pet_name'] ?? 'Unknown pet') ?> - <?= htmlspecialchars($procedure['procedure_type'] ?? 'Procedure') ?> - Dr. <?= htmlspecialchars($procedure['vet_name'] ?? 'Unassigned') ?></span>
                                            <span><?= htmlspecialchars($procedure['procedure_date'] ?? date('Y-m-d', strtotime($procedure['created_at'] ?? 'now'))) ?></span>
                                        </div>
                                        <div class="item-actions">
                                            <span class="badge <?= htmlspecialchars($procedureStatus) ?>"><?= htmlspecialchars($procedure['status'] ?? 'scheduled') ?></span>
                                        </div>
                                        <?php if ($role === 'vet' && in_array($procedureStatus, ['owner_requested', 'pending_vet_review'], true)): ?>
                                            <form class="inline-resource-form" method="post">
                                                <input type="hidden" name="action" value="submit_clinical_workflow">
                                                <input type="hidden" name="action_key" value="surgery_booking">
                                                <input type="hidden" name="procedure_id" value="<?= (int) $procedure['id'] ?>">
                                                <input type="hidden" name="pet_id" value="<?= (int) $procedure['pet_id'] ?>">
                                                <input type="hidden" name="summary" value="<?= htmlspecialchars('Owner requested ' . ($procedure['procedure_type'] ?? 'surgery') . ' for ' . ($procedure['pet_name'] ?? 'this pet')) ?>">
                                                <?php if (empty($operatingRooms) || empty($equipment) || empty($specialists)): ?>
                                                    <div class="resource-note">Admin must add available rooms, equipment, and specialist staff before this request can be sent.</div>
                                                <?php endif; ?>
                                                <label>Operating Room
                                                    <select class="form-control" name="room_id" required>
                                                        <option value="">Choose room</option>
                                                        <?php foreach ($operatingRooms as $room): ?>
                                                            <option value="<?= (int) $room['id'] ?>"><?= htmlspecialchars(($room['name'] ?? 'Room') . (!empty($room['location']) ? ' - ' . $room['location'] : '')) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </label>
                                                <label>Equipment
                                                    <select class="form-control" name="equipment_id" required>
                                                        <option value="">Choose equipment</option>
                                                        <?php foreach ($equipment as $item): ?>
                                                            <option value="<?= (int) $item['id'] ?>"><?= htmlspecialchars(($item['name'] ?? 'Equipment') . (!empty($item['type']) ? ' - ' . $item['type'] : '')) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </label>
                                                <label>Specialist Staff
                                                    <select class="form-control" name="specialist_id" required>
                                                        <option value="">Choose staff</option>
                                                        <?php foreach ($specialists as $specialist): ?>
                                                            <option value="<?= (int) $specialist['id'] ?>"><?= htmlspecialchars(($specialist['username'] ?? 'Specialist') . ' - ' . ($specialist['specialization'] ?? 'General')) ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </label>
                                                <label>Date
                                                    <input class="form-control" type="date" name="procedure_date" value="<?= htmlspecialchars(date('Y-m-d')) ?>" required>
                                                </label>
                                                <label>Start
                                                    <input class="form-control" type="time" name="start_time" required>
                                                </label>
                                                <label>End
                                                    <input class="form-control" type="time" name="end_time" required>
                                                </label>
                                                <label class="full">Vet Notes
                                                    <textarea class="form-control" name="notes" placeholder="Add triage notes, risks, equipment details, or timing notes"></textarea>
                                                </label>
                                                <button class="action-btn primary" type="submit"><i class="fas fa-user-shield"></i> Check Availability And Send To Admin</button>
                                            </form>
                                        <?php endif; ?>
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
                    <?php if ($role === 'vet'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Upload Lab Report</h2><small>Upload diagnostic results for interpretation</small>
                                </div>
                            </div>
                            <form method="POST" enctype="multipart/form-data" style="padding: 20px;">
                                <input type="hidden" name="action" value="upload_lab_report">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                    <select name="pet_id" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="">Select Pet</option>
                                        <?php foreach ($pets as $pet): ?>
                                            <option value="<?= $pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['species']) ?><?php if (isset($pet['owner_name'])): ?> - <?= htmlspecialchars($pet['owner_name']) ?><?php endif; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="test_name" placeholder="Test Name (e.g., CBC, Glucose)" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                </div>
                                <input type="file" name="lab_file" accept=".pdf,.jpg,.png" required style="margin-bottom: 16px;">
                                <textarea name="result_summary" placeholder="Result summary" style="width: 100%; padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px; margin-bottom: 16px;"></textarea>
                                <button type="submit" style="padding: 12px 24px; background: var(--teal); color: white; border: none; border-radius: 8px;">Upload Report</button>
                            </form>
                        </div>
                    <?php endif; ?>
                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Lab Reports Section</h2><small>Lab result interpretation hub</small>
                            </div><span class="badge"><?= count($labReports) ?> shown</span>
                        </div>
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
                                        <?php if ($role === 'vet' && ($report['status'] ?? 'pending') === 'pending'): ?>
                                            <button style="margin-left: 10px; padding: 6px 12px; background: var(--green); color: white; border: none; border-radius: 6px;">Interpret</button>
                                        <?php endif; ?>
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
                    <?php if ($role === 'vet'): ?>
                        <div class="panel">
                            <div class="panel-head">
                                <div>
                                    <h2>Initiate Referral</h2><small>Refer a case to a specialist</small>
                                </div>
                            </div>
                            <form method="POST" style="padding: 20px;">
                                <input type="hidden" name="action" value="initiate_referral">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                                    <select name="pet_id" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="">Select Pet</option>
                                        <?php foreach ($pets as $pet): ?>
                                            <option value="<?= $pet['id'] ?>"><?= htmlspecialchars($pet['name']) ?> (<?= htmlspecialchars($pet['species']) ?><?php if (isset($pet['owner_name'])): ?> - <?= htmlspecialchars($pet['owner_name']) ?><?php endif; ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <select name="specialist_id" required style="padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px;">
                                        <option value="">Select Specialist</option>
                                        <?php foreach ($specialists as $spec): ?>
                                            <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['username']) ?> (<?= htmlspecialchars($spec['specialization']) ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <input type="text" name="specialty" placeholder="Specialty (e.g., Cardiology)" required style="width: 100%; padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px; margin-bottom: 16px;">
                                <textarea name="reason" placeholder="Reason for referral" required style="width: 100%; padding: 12px; border: 1px solid #d8ebe5; border-radius: 8px; margin-bottom: 16px;"></textarea>
                                <button type="submit" style="padding: 12px 24px; background: var(--teal); color: white; border: none; border-radius: 8px;">Initiate Referral</button>
                            </form>
                        </div>
                    <?php endif; ?>

                    <div class="panel">
                        <div class="panel-head">
                            <div>
                                <h2>Referral Requests</h2><small>Veterinary referrals workflow</small>
                            </div><span class="badge"><?= count($referrals) ?> shown</span>
                        </div>
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

    <!-- ✅ Error Popup -->
    <style>
        .error-popup-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.45);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
        }

        .error-popup-box {
            background: #ffffff;
            padding: 24px 28px;
            border-radius: 10px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.25);
            max-width: 420px;
            width: 90%;
            position: relative;
            border-left: 5px solid #e53e3e;
        }

        .error-popup-close {
            position: absolute;
            top: 12px;
            right: 16px;
            background: none;
            border: none;
            font-size: 22px;
            color: #666;
            cursor: pointer;
            line-height: 1;
            transition: color 0.2s;
        }

        .error-popup-close:hover {
            color: #e53e3e;
        }

        .error-popup-title {
            margin: 0 0 12px;
            font-size: 17px;
            font-weight: 600;
            color: #2d3748;
        }

        .error-popup-list {
            margin: 0;
            padding-left: 20px;
            color: #c53030;
        }

        .error-popup-list li {
            margin-bottom: 6px;
            font-size: 14px;
            line-height: 1.4;
        }
    </style>

    <div id="error-popup" class="error-popup-overlay">
        <div class="error-popup-box">
            <button class="error-popup-close" onclick="closeErrorPopup()">&times;</button>
            <h4 class="error-popup-title">⚠️ Action Failed</h4>
            <ul id="error-list" class="error-popup-list"></ul>
        </div>
    </div>

    <script>
        function closeErrorPopup() {
            document.getElementById('error-popup').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.getElementById('error-popup');
            overlay.addEventListener('click', function(e) {
                if (e.target === this) closeErrorPopup();
            });

            <?php if (!empty($errors)): ?>
                const errors = <?php echo json_encode(array_values($errors)); ?>;
                const list = document.getElementById('error-list');
                errors.forEach(err => {
                    const li = document.createElement('li');
                    li.textContent = err;
                    list.appendChild(li);
                });
                overlay.style.display = 'flex';
                setTimeout(() => closeErrorPopup(), 7000);
            <?php endif; ?>
        });
    </script>
</body>

</html>