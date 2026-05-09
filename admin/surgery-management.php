<?php
require_once 'controllers/SurgeryController.php';

$active_page = 'surgery';

$controller = new SurgeryController();
$message    = $controller->postAction();
$data       = $controller->index();

$surgeries      = $data['list'];
$search         = $data['search'];
$status_f       = $data['status'];
$currentSurgery = $data['surgery'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Surgery Management — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
    <div>
      <h1>Surgery Management</h1>
      <p>Review requests, assign rooms & schedule surgeries.</p>
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
          <input type="text" name="search" placeholder="Search pet, owner..." value="<?= $search ?>">
        </div>
        <button type="submit" class="btn-primary">Search</button>

        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= $status_f==='all' ? 'selected' : '' ?>>All Requests</option>
          <option value="pending" <?= $status_f==='pending' ? 'selected' : '' ?>>Pending</option>
          <option value="approved" <?= $status_f==='approved' ? 'selected' : '' ?>>Approved</option>
          <option value="completed" <?= $status_f==='completed' ? 'selected' : '' ?>>Completed</option>
        </select>
      </form>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Pet</th>
          <th>Owner</th>
          <th>Provider</th>
          <th>Procedure</th>
          <th>Requested Date</th>
          <th>Scheduled</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($surgeries as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['pet_name'] ?? 'Unknown') ?></strong></td>
          <td><?= htmlspecialchars($s['owner'] ?? 'Unknown') ?></td>
          <td><?= htmlspecialchars($s['provider'] ?? 'General Vet') ?></td>
          <td><?= htmlspecialchars($s['procedure_name'] ?? $s['procedure_type'] ?? 'Surgery') ?></td>
          <td><?= $s['procedure_date'] ? date('M j, Y', strtotime($s['procedure_date'])) : 'Not set' ?></td>
          <td>
            <?= $s['procedure_date'] ? date('M j, Y', strtotime($s['procedure_date'])) : '<span class="text-muted">Not scheduled</span>' ?>
          </td>
          <td>
            <span class="badge <?= $s['status']==='pending' ? 'badge-pending' : ($s['status']==='approved' ? 'badge-approved' : 'badge-active') ?>">
              <?= ucfirst($s['status'] ?? 'pending') ?>
            </span>
          </td>
          <td>
            <a href="?view=<?= $s['id'] ?>" class="icon-btn" title="Review / Schedule">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

<?php if ($currentSurgery): ?>
<div class="modal-overlay open">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3><?= $currentSurgery['status'] === 'pending' ? 'Review & Schedule Surgery' : 'Surgery Details' ?></h3>
      <a href="surgery-management.php" class="modal-close">&times;</a>
    </div>

    <div class="profile-header">
      <div class="profile-avatar-lg" style="background:var(--primary-light);color:var(--primary-dark);font-size:28px;">🐾</div>
      <div class="profile-meta">
        <div class="name"><?= htmlspecialchars($currentSurgery['pet_name'] ?? 'Pet') ?></div>
        <div>Owner: <?= htmlspecialchars($currentSurgery['owner'] ?? 'Unknown') ?></div>
      </div>
    </div>

    <p><strong>Procedure:</strong> <?= htmlspecialchars($currentSurgery['procedure_name'] ?? $currentSurgery['procedure_type'] ?? 'Surgery') ?></p>

    <?php if ($currentSurgery['status'] === 'pending'): ?>
    <form method="POST">
      <input type="hidden" name="surgery_id" value="<?= $currentSurgery['id'] ?>">
      
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Select Room</label>
          <select name="room" class="form-control" required>
            <option value="OR-1">OR-1 (Main Operating Room)</option>
            <option value="OR-2">OR-2 (Minor Procedures)</option>
            <option value="OR-3">OR-3 (Recovery)</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Schedule Date</label>
          <input type="date" name="scheduled_date" class="form-control" required>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Schedule Time</label>
        <input type="time" name="scheduled_time" class="form-control" required>
      </div>

      <div class="form-actions">
        <a href="surgery-management.php" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="approve" class="btn-primary">Approve & Schedule</button>
        <button type="submit" name="action" value="reject" class="btn-danger">Reject Request</button>
      </div>
    </form>
    <?php else: ?>
    <div class="form-actions">
      <a href="surgery-management.php" class="btn-secondary">Close</a>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

</body>
</html>