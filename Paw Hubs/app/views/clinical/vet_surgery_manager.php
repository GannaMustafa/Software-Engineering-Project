<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surgery Manager | Paw Hubs</title>
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

        .badge.pending,
        .badge.pending_admin,
        .badge.pending_vet_review {
            background: var(--olive);
            color: #4f6f35;
        }

        .badge.approved,
        .badge.completed {
            background: var(--green);
            color: #fff;
        }

        .badge.rejected,
        .badge.critical,
        .badge.emergency {
            background: #fff5f5;
            color: var(--danger);
        }

        .stats,
        .grid {
            display: grid;
            gap: 16px;
            margin-bottom: 18px;
        }

        .stats {
            grid-template-columns: repeat(4, minmax(150px, 1fr));
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
            font-size: 18px;
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
            min-height: 94px;
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

            .stats {
                grid-template-columns: repeat(2, minmax(150px, 1fr));
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
    $procedures = $procedures ?? [];
    $ownerSurgeryRequests = $ownerSurgeryRequests ?? [];
    $workflowRequests = $workflowRequests ?? [];
    $operatingRooms = $operatingRooms ?? [];
    $equipment = $equipment ?? [];
    $specialists = $specialists ?? [];
    ?>
    <div class="app-frame">
        <aside class="sidebar">
            <div class="brand"><i class="fas fa-briefcase-medical"></i><span>Paw Surgery</span></div>
            <div>
                <p class="menu-label">Clinical</p>
                <nav class="menu">
                    <a href="index.php?url=clinical/vetDashboard"><i class="fas fa-chart-pie"></i> Dashboard</a>
                    <a class="active" href="index.php?url=clinical/surgeryManager"><i class="fas fa-briefcase-medical"></i> Surgery Manager</a>
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
                <label class="search"><i class="fas fa-search"></i><input type="search" placeholder="Search surgeries, pets, approvals"></label>
                <a class="action-btn" href="index.php?url=clinical/vetDashboard"><i class="fas fa-arrow-left"></i> Dashboard</a>
            </div>

            <header class="page-head">
                <div>
                    <h1>Surgery Manager</h1>
                    <p>Review owner surgery requests, forward approved procedures for admin scheduling, and monitor surgery workflow approvals.</p>
                </div>
                <span class="role-pill">Vet access</span>
            </header>

            <?php if (!empty($message)): ?><div class="notice success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
            <?php if (!empty($errors)): ?><div class="notice error"><?= htmlspecialchars(implode(' ', $errors)) ?></div><?php endif; ?>

            <section class="stats">
                <article class="stat-card">
                    <div class="stat-icon"><i class="fas fa-notes-medical"></i></div>
                    <div><span>Procedures</span><strong><?= (int) ($stats['procedures'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-olive"><i class="fas fa-user-clock"></i></div>
                    <div><span>Owner Requests</span><strong><?= (int) ($stats['owner_requests'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-sky"><i class="fas fa-user-shield"></i></div>
                    <div><span>Pending Admin</span><strong><?= (int) ($stats['pending_admin'] ?? 0) ?></strong></div>
                </article>
                <article class="stat-card">
                    <div class="stat-icon bg-green"><i class="fas fa-circle-check"></i></div>
                    <div><span>Approved</span><strong><?= (int) ($stats['approved'] ?? 0) ?></strong></div>
                </article>
            </section>

            <section class="grid">
                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Owner Surgery Requests</h2>
                            <small>Ready for vet review</small>
                        </div>
                        <span class="badge"><?= count($ownerSurgeryRequests) ?> shown</span>
                    </div>
                    <div class="card-list">
                        <?php if (empty($ownerSurgeryRequests)): ?>
                            <div class="empty">No owner surgery requests are waiting for review.</div>
                        <?php else: ?>
                            <?php foreach ($ownerSurgeryRequests as $request): ?>
                                <article class="item-card">
                                    <div class="item-top">
                                        <div>
                                            <strong><?= htmlspecialchars(ucwords(str_replace('_', ' ', $request['procedure_type'] ?? 'Surgery request'))) ?></strong>
                                            <p><?= htmlspecialchars($request['pet_name'] ?? 'Unknown pet') ?> - owner: <?= htmlspecialchars($request['owner_name'] ?? 'Unknown') ?></p>
                                        </div>
                                        <span class="badge <?= htmlspecialchars(strtolower($request['urgency'] ?? 'normal')) ?>"><?= htmlspecialchars($request['urgency'] ?? 'normal') ?></span>
                                    </div>
                                    <p><?= htmlspecialchars($request['reason'] ?? 'No reason provided.') ?></p>
                                    <form method="POST" class="form-grid">
                                        <input type="hidden" name="action" value="send_owner_surgery_to_admin">
                                        <input type="hidden" name="surgery_request_id" value="<?= (int) ($request['id'] ?? 0) ?>">
                                        <div class="split">
                                            <input class="form-control" type="date" name="procedure_date" required>
                                            <input class="form-control" type="time" name="start_time" required>
                                        </div>
                                        <div class="split">
                                            <input class="form-control" type="time" name="end_time" required>
                                            <input class="form-control" name="summary" placeholder="Admin scheduling note">
                                        </div>
                                        <div class="split">
                                            <select class="form-control" name="room_id" required>
                                                <option value="">Operating room</option>
                                                <?php foreach ($operatingRooms as $room): ?>
                                                    <option value="<?= (int) ($room['id'] ?? 0) ?>"><?= htmlspecialchars($room['name'] ?? 'Operating room') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="form-control" name="equipment_id" required>
                                                <option value="">Surgical equipment</option>
                                                <?php foreach ($equipment as $item): ?>
                                                    <option value="<?= (int) ($item['id'] ?? 0) ?>"><?= htmlspecialchars($item['name'] ?? 'Equipment') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <select class="form-control" name="specialist_id" required>
                                            <option value="">Specialist staff</option>
                                            <?php foreach ($specialists as $specialist): ?>
                                                <option value="<?= (int) ($specialist['id'] ?? 0) ?>"><?= htmlspecialchars(($specialist['username'] ?? 'Specialist') . ' - ' . ($specialist['specialization'] ?? 'General')) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <textarea class="form-control" name="notes" placeholder="Vet notes for admin review"></textarea>
                                        <button class="action-btn primary" type="submit"><i class="fas fa-paper-plane"></i> Send To Admin</button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>

                <article class="panel">
                    <div class="panel-head">
                        <div>
                            <h2>Procedure Cases</h2>
                            <small>Recent surgery-related cases</small>
                        </div>
                        <span class="badge"><?= count($procedures) ?> shown</span>
                    </div>
                    <div class="card-list">
                        <?php if (empty($procedures)): ?>
                            <div class="empty">No procedure cases are linked to your account yet.</div>
                        <?php else: ?>
                            <?php foreach ($procedures as $procedure): ?>
                                <article class="item-card">
                                    <div class="item-top">
                                        <div>
                                            <strong><?= htmlspecialchars($procedure['procedure_name'] ?? 'Procedure') ?></strong>
                                            <p><?= htmlspecialchars($procedure['pet_name'] ?? 'Unknown pet') ?> - <?= htmlspecialchars($procedure['species'] ?? 'Pet') ?></p>
                                        </div>
                                        <span class="badge <?= htmlspecialchars(strtolower($procedure['status'] ?? 'pending')) ?>"><?= htmlspecialchars($procedure['status'] ?? 'pending') ?></span>
                                    </div>
                                    <p><?= htmlspecialchars($procedure['notes'] ?? 'No notes recorded.') ?></p>
                                    <form method="POST" class="form-grid">
                                        <input type="hidden" name="action" value="submit_clinical_workflow">
                                        <input type="hidden" name="action_key" value="surgery_booking">
                                        <input type="hidden" name="procedure_id" value="<?= (int) ($procedure['id'] ?? 0) ?>">
                                        <input type="hidden" name="pet_id" value="<?= (int) ($procedure['pet_id'] ?? 0) ?>">
                                        <input type="hidden" name="summary" value="<?= htmlspecialchars('Surgery booking request for ' . ($procedure['pet_name'] ?? 'selected pet')) ?>">
                                        <div class="split">
                                            <input class="form-control" type="date" name="procedure_date" value="<?= htmlspecialchars($procedure['procedure_date'] ?? '') ?>" required>
                                            <input class="form-control" type="time" name="start_time" required>
                                        </div>
                                        <div class="split">
                                            <input class="form-control" type="time" name="end_time" required>
                                            <select class="form-control" name="room_id" required>
                                                <option value="">Operating room</option>
                                                <?php foreach ($operatingRooms as $room): ?>
                                                    <option value="<?= (int) ($room['id'] ?? 0) ?>"><?= htmlspecialchars($room['name'] ?? 'Operating room') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="split">
                                            <select class="form-control" name="equipment_id" required>
                                                <option value="">Surgical equipment</option>
                                                <?php foreach ($equipment as $item): ?>
                                                    <option value="<?= (int) ($item['id'] ?? 0) ?>"><?= htmlspecialchars($item['name'] ?? 'Equipment') ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select class="form-control" name="specialist_id" required>
                                                <option value="">Specialist staff</option>
                                                <?php foreach ($specialists as $specialist): ?>
                                                    <option value="<?= (int) ($specialist['id'] ?? 0) ?>"><?= htmlspecialchars(($specialist['username'] ?? 'Specialist') . ' - ' . ($specialist['specialization'] ?? 'General')) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <textarea class="form-control" name="notes" placeholder="Notes for owner/admin approval"></textarea>
                                        <button class="action-btn primary" type="submit"><i class="fas fa-clipboard-check"></i> Request Surgery Approval</button>
                                    </form>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </article>
            </section>

            <section class="panel">
                <div class="panel-head">
                    <div>
                        <h2>Workflow Requests</h2>
                        <small>Surgery approval status</small>
                    </div>
                    <span class="badge"><?= count($workflowRequests) ?> requests</span>
                </div>
                <div class="card-list">
                    <?php
                    $surgeryRequests = array_values(array_filter($workflowRequests, fn($request) => strtolower($request['action_key'] ?? '') === 'surgery_booking'));
                    ?>
                    <?php if (empty($surgeryRequests)): ?>
                        <div class="empty">No surgery workflow requests have been submitted yet.</div>
                    <?php else: ?>
                        <?php foreach ($surgeryRequests as $request): ?>
                            <article class="item-card">
                                <div class="item-top">
                                    <div>
                                        <strong><?= htmlspecialchars($request['action_title'] ?? 'Surgery Booking') ?></strong>
                                        <p><?= htmlspecialchars($request['pet_name'] ?? 'Unknown pet') ?> - owner: <?= htmlspecialchars($request['owner_status'] ?? 'not_needed') ?> - admin: <?= htmlspecialchars($request['admin_status'] ?? 'not_needed') ?></p>
                                    </div>
                                    <span class="badge <?= htmlspecialchars(strtolower($request['request_status'] ?? 'pending')) ?>"><?= htmlspecialchars($request['request_status'] ?? 'pending') ?></span>
                                </div>
                                <p><?= htmlspecialchars($request['notes'] ?? 'No notes provided.') ?></p>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
</body>

</html>
