<?php
require_once 'controllers/SurgeryController.php';

$active_page = 'surgeries';

$controller = new SurgeryController();
$message    = $controller->postAction();
$data       = $controller->index();

$surgeries      = $data['list'];
$search         = $data['search'];
$status_f       = $data['status'];
$currentSurgery = $data['surgery'];

$modalAction = $_GET['action_modal'] ?? '';
$modalId     = (int)($_GET['modal_id'] ?? 0);
$modalSurgery = null;
if ($modalId && in_array($modalAction, ['accept', 'reschedule'])) {
    foreach ($surgeries as $s) {
        if ((int)$s['id'] === $modalId) { $modalSurgery = $s; break; }
    }
    if (!$modalSurgery) $modalSurgery = $currentSurgery;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Surgery Management — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .action-group { display:flex; gap:6px; align-items:center; flex-wrap:nowrap; }
    .btn-accept   { background:var(--success,#388e3c); color:#fff; border:none; padding:4px 10px; border-radius:var(--radius-sm,4px); cursor:pointer; font-size:12px; white-space:nowrap; }
    .btn-accept:hover { filter:brightness(1.12); }
    .btn-reschedule { background:var(--warning-light,#fff3e0); color:#e65100; border:1px solid #e65100; padding:4px 10px; border-radius:var(--radius-sm,4px); cursor:pointer; font-size:12px; white-space:nowrap; }
    .btn-reschedule:hover { background:#ffe0b2; }

    .detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px 20px; margin:14px 0; }
    .detail-item label { font-size:11px; color:var(--text-muted,#888); display:block; margin-bottom:2px; text-transform:uppercase; letter-spacing:.04em; }
    .detail-item span  { font-size:14px; font-weight:500; }
    .detail-section-title { font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:var(--text-muted,#888); margin:16px 0 6px; border-bottom:1px solid var(--border,#eee); padding-bottom:4px; }
    .notes-box { background:var(--bg-soft,#f8f9fa); border-radius:var(--radius-sm,4px); padding:10px 14px; font-size:13px; white-space:pre-wrap; max-height:120px; overflow-y:auto; }
    .urgency-high   { color:#c62828; font-weight:700; }
    .urgency-normal { color:#388e3c; }
  </style>
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
    <div>
      <h1>Surgery Management</h1>
      <p>Review requests, assign rooms &amp; schedule surgeries.</p>
    </div>
  </div>

  <?php if ($message): ?>
  <div style="background:var(--success-light);color:#388e3c;padding:10px 16px;border-radius:var(--radius-sm);margin-bottom:16px;">
    <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>

  <div class="table-card">
    <div class="table-toolbar">
      <form method="GET" style="display:contents;">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" placeholder="Search pet, owner, procedure..." value="<?= $search ?>">
        </div>
        <button type="submit" class="btn-primary">Search</button>

        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all"         <?= $status_f==='all'         ? 'selected':'' ?>>All Requests</option>
          <option value="pending"     <?= $status_f==='pending'     ? 'selected':'' ?>>Pending</option>
          <option value="approved"    <?= $status_f==='approved'    ? 'selected':'' ?>>Approved</option>
          <option value="rescheduled" <?= $status_f==='rescheduled' ? 'selected':'' ?>>Rescheduled</option>
          <option value="completed"   <?= $status_f==='completed'   ? 'selected':'' ?>>Completed</option>
          <option value="rejected"    <?= $status_f==='rejected'    ? 'selected':'' ?>>Rejected</option>
        </select>
      </form>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Pet</th>
          <th>Owner</th>
          <th>Procedure</th>
          <th>Urgency</th>
          <th>Requested On</th>
          <th>Scheduled Date</th>
          <th>Room / Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($surgeries as $s): ?>
        <?php
          $isPending = in_array($s['status'], ['pending', 'rescheduled']);
          $urgency   = $s['urgency'] ?? 'normal';
        ?>
        <tr>
          <td>
            <strong><?= htmlspecialchars($s['pet_name'] ?? 'Unknown') ?></strong>
            <?php if (!empty($s['pet_species'])): ?>
              <br><small style="color:var(--text-muted)"><?= htmlspecialchars($s['pet_species']) ?></small>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($s['owner'] ?? 'Unknown') ?></td>
          <td><?= htmlspecialchars($s['procedure_name'] ?? $s['procedure_type'] ?? 'Surgery') ?></td>
          <td>
            <span class="<?= $urgency==='high' ? 'urgency-high' : 'urgency-normal' ?>">
              <?= ucfirst($urgency) ?>
            </span>
          </td>
          <td><?= $s['created_at'] ? date('M j, Y', strtotime($s['created_at'])) : '—' ?></td>
          <td><?= $s['procedure_date'] ? date('M j, Y', strtotime($s['procedure_date'])) : '<span class="text-muted">Not set</span>' ?></td>
          <td>
            <?php if ($s['room'] || $s['scheduled_time']): ?>
              <?= htmlspecialchars($s['room'] ?? '') ?>
              <?php if ($s['scheduled_time']): ?>
                <br><small><?= date('g:i A', strtotime($s['scheduled_time'])) ?></small>
              <?php endif; ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <?php
              $badgeClass = match($s['status'] ?? 'pending') {
                'approved'    => 'badge-approved',
                'completed'   => 'badge-active',
                'rejected'    => 'badge-danger',
                'rescheduled' => 'badge-warning',
                default       => 'badge-pending',
              };
            ?>
            <span class="badge <?= $badgeClass ?>">
              <?= ucfirst($s['status'] ?? 'pending') ?>
            </span>
          </td>
          <td>
            <div class="action-group">
              <a href="?view=<?= $s['id'] ?>" class="icon-btn" title="View Details">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>

              <?php if ($isPending): ?>
              <a href="?action_modal=accept&modal_id=<?= $s['id'] ?>" class="btn-accept" title="Accept Request">Accept</a>
              <a href="?action_modal=reschedule&modal_id=<?= $s['id'] ?>" class="btn-reschedule" title="Reschedule">Reschedule</a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($surgeries)): ?>
        <tr><td colspan="9" style="text-align:center;color:var(--text-muted);padding:24px;">No surgery requests found.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

<?php if ($currentSurgery && !$modalSurgery): ?>
<div class="modal-overlay open">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Surgery Request — Details</h3>
      <a href="surgery-management.php" class="modal-close">&times;</a>
    </div>

    <div class="profile-header">
      <div class="profile-avatar-lg" style="background:var(--primary-light);color:var(--primary-dark);font-size:28px;">🐾</div>
      <div class="profile-meta">
        <div class="name"><?= htmlspecialchars($currentSurgery['pet_name'] ?? 'Pet') ?></div>
        <div>
          <?= htmlspecialchars($currentSurgery['pet_species'] ?? '') ?>
          <?= !empty($currentSurgery['pet_breed']) ? '· ' . htmlspecialchars($currentSurgery['pet_breed']) : '' ?>
        </div>
      </div>
    </div>

    <!-- Procedure info -->
    <div class="detail-section-title">Procedure</div>
    <div class="detail-grid">
      <div class="detail-item">
        <label>Procedure Name</label>
        <span><?= htmlspecialchars($currentSurgery['procedure_name'] ?? $currentSurgery['procedure_type'] ?? 'Surgery') ?></span>
      </div>
      <div class="detail-item">
        <label>Type</label>
        <span><?= htmlspecialchars($currentSurgery['procedure_type'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Urgency</label>
        <span class="<?= ($currentSurgery['urgency'] ?? 'normal')==='high' ? 'urgency-high':'urgency-normal' ?>">
          <?= ucfirst($currentSurgery['urgency'] ?? 'Normal') ?>
        </span>
      </div>
      <div class="detail-item">
        <label>Status</label>
        <span><?= ucfirst($currentSurgery['status'] ?? 'pending') ?></span>
      </div>
      <div class="detail-item">
        <label>Requested On</label>
        <span><?= $currentSurgery['created_at'] ? date('M j, Y g:i A', strtotime($currentSurgery['created_at'])) : '—' ?></span>
      </div>
      <div class="detail-item">
        <label>Scheduled Date</label>
        <span><?= $currentSurgery['procedure_date'] ? date('M j, Y', strtotime($currentSurgery['procedure_date'])) : 'Not set' ?></span>
      </div>
      <div class="detail-item">
        <label>Room</label>
        <span><?= htmlspecialchars($currentSurgery['room'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Scheduled Time</label>
        <span><?= $currentSurgery['scheduled_time'] ? date('g:i A', strtotime($currentSurgery['scheduled_time'])) : '—' ?></span>
      </div>
    </div>

    <!-- Pet info -->
    <div class="detail-section-title">Pet Information</div>
    <div class="detail-grid">
      <div class="detail-item">
        <label>Age</label>
        <span><?= htmlspecialchars($currentSurgery['pet_age'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Weight</label>
        <span><?= $currentSurgery['pet_weight'] ? htmlspecialchars($currentSurgery['pet_weight']) . ' kg' : '—' ?></span>
      </div>
      <div class="detail-item">
        <label>Gender</label>
        <span><?= htmlspecialchars($currentSurgery['pet_gender'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Vaccination</label>
        <span><?= htmlspecialchars($currentSurgery['pet_vaccination_status'] ?? '—') ?></span>
      </div>
      <?php if (!empty($currentSurgery['pet_allergies'])): ?>
      <div class="detail-item" style="grid-column:span 2;">
        <label>Known Allergies</label>
        <span><?= htmlspecialchars($currentSurgery['pet_allergies']) ?></span>
      </div>
      <?php endif; ?>
    </div>

    <!-- Owner & Vet -->
    <div class="detail-section-title">Owner &amp; Veterinarian</div>
    <div class="detail-grid">
      <div class="detail-item">
        <label>Owner</label>
        <span><?= htmlspecialchars($currentSurgery['owner'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Owner Email</label>
        <span><?= htmlspecialchars($currentSurgery['owner_email'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Owner Phone</label>
        <span><?= htmlspecialchars($currentSurgery['owner_phone'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>Assigned Vet</label>
        <span><?= htmlspecialchars($currentSurgery['provider'] ?? 'General Vet') ?></span>
      </div>
      <div class="detail-item">
        <label>Specialization</label>
        <span><?= htmlspecialchars($currentSurgery['vet_specialization'] ?? '—') ?></span>
      </div>
      <div class="detail-item">
        <label>License #</label>
        <span><?= htmlspecialchars($currentSurgery['vet_license'] ?? '—') ?></span>
      </div>
    </div>

    <!-- Notes -->
    <?php if (!empty($currentSurgery['notes'])): ?>
    <div class="detail-section-title">Notes / History</div>
    <div class="notes-box"><?= htmlspecialchars($currentSurgery['notes']) ?></div>
    <?php endif; ?>

    <?php if (!empty($currentSurgery['pet_medical_notes'])): ?>
    <div class="detail-section-title">Pet Medical Notes</div>
    <div class="notes-box"><?= htmlspecialchars($currentSurgery['pet_medical_notes']) ?></div>
    <?php endif; ?>

    <div class="form-actions" style="margin-top:18px;">
      <a href="surgery-management.php" class="btn-secondary">Close</a>
      <?php if (in_array($currentSurgery['status'], ['pending','rescheduled'])): ?>
        <a href="?action_modal=accept&modal_id=<?= $currentSurgery['id'] ?>" class="btn-accept" style="padding:8px 18px;font-size:14px;">Accept</a>
        <a href="?action_modal=reschedule&modal_id=<?= $currentSurgery['id'] ?>" class="btn-reschedule" style="padding:8px 18px;font-size:14px;">Reschedule</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>


<?php if ($modalSurgery && $modalAction === 'accept'): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header">
      <h3>Accept &amp; Schedule Surgery</h3>
      <a href="surgery-management.php" class="modal-close">&times;</a>
    </div>

    <div class="profile-header">
      <div class="profile-avatar-lg" style="background:var(--primary-light);color:var(--primary-dark);font-size:28px;">🐾</div>
      <div class="profile-meta">
        <div class="name"><?= htmlspecialchars($modalSurgery['pet_name'] ?? 'Pet') ?></div>
        <div>Owner: <?= htmlspecialchars($modalSurgery['owner'] ?? 'Unknown') ?></div>
      </div>
    </div>

    <p><strong>Procedure:</strong> <?= htmlspecialchars($modalSurgery['procedure_name'] ?? $modalSurgery['procedure_type'] ?? 'Surgery') ?></p>

    <form method="POST">
      <input type="hidden" name="surgery_id" value="<?= $modalSurgery['id'] ?>">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Select Room</label>
          <select name="room" class="form-control" required>
            <option value="OR-1">OR-1 (Main Operating Room)</option>
            <option value="OR-2">OR-2 (Minor Procedures)</option>
            <option value="OR-3">OR-3 (Recovery &amp; Post-op)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Schedule Date</label>
          <input type="date" name="scheduled_date" class="form-control" required min="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Schedule Time</label>
        <input type="time" name="scheduled_time" class="form-control" required>
      </div>

      <div class="form-actions">
        <a href="surgery-management.php" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="approve" class="btn-primary">Confirm Acceptance</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>


<?php if ($modalSurgery && $modalAction === 'reschedule'): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header">
      <h3>Reschedule Surgery</h3>
      <a href="surgery-management.php" class="modal-close">&times;</a>
    </div>

    <div class="profile-header">
      <div class="profile-avatar-lg" style="background:var(--primary-light);color:var(--primary-dark);font-size:28px;">🐾</div>
      <div class="profile-meta">
        <div class="name"><?= htmlspecialchars($modalSurgery['pet_name'] ?? 'Pet') ?></div>
        <div>Owner: <?= htmlspecialchars($modalSurgery['owner'] ?? 'Unknown') ?></div>
      </div>
    </div>

    <p><strong>Procedure:</strong> <?= htmlspecialchars($modalSurgery['procedure_name'] ?? $modalSurgery['procedure_type'] ?? 'Surgery') ?></p>
    <p style="color:#e65100;font-size:13px;">The current room or date is unavailable. Please pick a new slot and provide a reason.</p>

    <form method="POST">
      <input type="hidden" name="surgery_id" value="<?= $modalSurgery['id'] ?>">

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">New Room</label>
          <select name="room" class="form-control" required>
            <option value="OR-1">OR-1 (Main Operating Room)</option>
            <option value="OR-2">OR-2 (Minor Procedures)</option>
            <option value="OR-3">OR-3 (Recovery &amp; Post-op)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">New Date</label>
          <input type="date" name="scheduled_date" class="form-control" required min="<?= date('Y-m-d') ?>">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">New Time</label>
        <input type="time" name="scheduled_time" class="form-control" required>
      </div>

      <div class="form-group">
        <label class="form-label">Reason for Rescheduling</label>
        <textarea name="reschedule_reason" class="form-control" rows="3" placeholder="e.g. OR-1 is booked on the requested date. Proposing alternative slot." required></textarea>
      </div>

      <div class="form-actions">
        <a href="surgery-management.php" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="reschedule" class="btn-reschedule" style="padding:8px 18px;font-size:14px;">Confirm Reschedule</button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>
