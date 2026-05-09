<?php
require_once 'controllers/UserController.php';

$active_page = 'users';

$controller = new UserController();
$message    = $controller->postAction();
$data       = $controller->index();

$users     = $data['list'];
$search    = $data['search'];
$status_f  = $data['status'];
$sort_f    = $data['sort'];
$profile   = $data['profile'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Pet Owners Management — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
    <div>
      <h1>Pet Owners Management</h1>
      <p>Manage registered pet owners, monitor activity and account status.</p>
    </div>
  </div>

  <?php if ($message): ?>
  <div style="background:var(--success-light);color:#388e3c;padding:10px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:500;">
    <?= htmlspecialchars($message) ?>
  </div>
  <?php endif; ?>


  <div class="table-card">
    <div class="table-toolbar">
      <form method="GET" style="display:contents;">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" placeholder="Search by name or email…" value="<?= $search ?>">
        </div>
        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= $status_f==='all' ? 'selected' : '' ?>>All Status</option>
          <option value="active" <?= $status_f==='active' ? 'selected' : '' ?>>Active</option>
          <option value="suspended" <?= $status_f==='suspended' ? 'selected' : '' ?>>Suspended</option>
        </select>
        <select name="sort" class="filter-select" onchange="this.form.submit()">
          <option value="newest" <?= $sort_f==='newest' ? 'selected' : '' ?>>Newest First</option>
          <option value="name" <?= $sort_f==='name' ? 'selected' : '' ?>>Name A–Z</option>
        </select>
        <button type="submit" class="btn-primary">Search</button>
      </form>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email / Phone</th>
          <th>Status</th>
          <th>Joined</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($users as $u): ?>
        <tr>
          <td>
            <div class="user-cell">
              <div class="user-avatar"><?= strtoupper(substr($u['username'],0,1)) ?></div>
              <div>
                <div class="user-name"><?= htmlspecialchars($u['username']) ?></div>
              </div>
            </div>
          </td>
          <td>
            <div><?= htmlspecialchars($u['email']) ?></div>
            <div class="user-sub"><?= htmlspecialchars($u['phone'] ?? 'No phone') ?></div>
          </td>
          <td>
            <span class="badge badge-<?= $u['status'] ?>"><?= ucfirst($u['status'] ?? 'active') ?></span>
          </td>
          <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
          <td>
            <div class="action-icons">
              <a href="?profile=<?= $u['id'] ?>" class="icon-btn" title="View Profile">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <?php if (($u['status'] ?? 'active') === 'active'): ?>
                <button type="submit" name="action" value="suspend" class="icon-btn danger" title="Suspend">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                </button>
                <?php else: ?>
                <button type="submit" name="action" value="unsuspend" class="icon-btn success" title="Unsuspend">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
                <?php endif; ?>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user permanently?');">
                <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                <button type="submit" name="action" value="delete" class="icon-btn danger" title="Delete">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2 2 2 0 0 1 2-2 2 2 0 0 1 2 2 2 2 0 0 1 2 2v2"/></svg>
                </button>
              </form>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

</main>

<?php if ($profile): ?>
<div class="modal-overlay open">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>User Profile #<?= $profile['id'] ?></h3>
      <a href="user-management.php" class="modal-close">&times;</a>
    </div>

    <div class="profile-header">
      <div class="profile-avatar-lg"><?= strtoupper(substr($profile['username'],0,1)) ?></div>
      <div class="profile-meta">
        <div class="name"><?= htmlspecialchars($profile['username']) ?></div>
        <div class="email"><?= htmlspecialchars($profile['email']) ?></div>
      </div>
    </div>

    <!-- Bookings Section -->
    <div style="margin:20px 0; padding:15px; background:#f8f9fa; border-radius:12px;">
      <h4 style="margin:0 0 12px 0;">Service Bookings</h4>
      <?php if (empty($profile['bookings'])): ?>
        <p style="color:#888; font-style:italic;">No bookings yet.</p>
      <?php else: ?>
        <?php foreach ($profile['bookings'] as $b): ?>
          <div style="background:white; padding:12px; margin-bottom:8px; border-radius:8px; border:1px solid #eee;">
            <strong><?= htmlspecialchars($b['service_name']) ?></strong><br>
            <small>Pet: <?= htmlspecialchars($b['pet_name'] ?? 'N/A') ?> | 
                   Status: <strong><?= ucfirst(htmlspecialchars($b['status'])) ?></strong></small>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <div class="form-actions">
      <a href="user-management.php" class="btn-secondary">Close</a>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="user_id" value="<?= $profile['id'] ?>">
        <button type="submit" name="action" value="<?= ($profile['status'] ?? 'active') === 'active' ? 'suspend' : 'unsuspend' ?>" 
          class="btn-primary">
          <?= ($profile['status'] ?? 'active') === 'active' ? 'Suspend User' : 'Unsuspend User' ?>
        </button>
      </form>
    </div>
  </div>
</div>
<?php endif; ?>

</body>
</html>