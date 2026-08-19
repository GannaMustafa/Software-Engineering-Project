  <?php
  require_once 'controllers/ProviderController.php';

  $active_page = 'providers';
  $controller = new ProviderController();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $controller->postAction(); // this redirects
  }

  $message = $_GET['message'] ?? '';
  unset($_GET['message']); 

  $data       = $controller->index();

  $providers     = $data['list'];
  $search        = $data['search'];
  $status_f      = $data['status'];
  $role_f        = $data['role'];
  $profile       = $data['profile'];
  $show_add      = $data['showAdd'];
  ?>

  <!DOCTYPE html>
  <html lang="en">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>Provider Management — PawAdmin</title>
    <link rel="stylesheet" href="assets/css/style.css">
  </head>
  <body>

  <?php include 'components/sidebar.php'; ?>

  <main class="main-content">

    <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
      <div>
        <h1>Provider / Vet / Vendor Management</h1>
        <p>Manage veterinarians, service providers and marketplace vendors.</p>
      </div>
      <a href="?add=1" class="btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Provider
      </a>
    </div>

    <?php if ($message): ?>
    <div style="background:var(--success-light);color:#388e3c;padding:12px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:14px;">
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>


    <div class="table-card">
      <div class="table-toolbar">
        <form method="GET" style="display:contents;">
          <div class="search-wrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" name="search" placeholder="Search provider…" value="<?= $search ?>">
          </div>
          <button type="submit" class="btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Search
          </button>

          <select name="role" class="filter-select" onchange="this.form.submit()">
            <option value="all"     <?= $role_f==='all'      ? 'selected' : '' ?>>All Roles</option>
            <option value="vet"     <?= $role_f==='vet'      ? 'selected' : '' ?>>Vet</option>
            <option value="provider"<?= $role_f==='provider' ? 'selected' : '' ?>>Service Provider</option>
            <option value="vendor"  <?= $role_f==='vendor'   ? 'selected' : '' ?>>Vendor</option>
          </select>

          <select name="status" class="filter-select" onchange="this.form.submit()">
            <option value="all"      <?= $status_f==='all'       ? 'selected' : '' ?>>All Status</option>
            <option value="active"   <?= $status_f==='active'    ? 'selected' : '' ?>>Active</option>
            <option value="suspended"<?= $status_f==='suspended' ? 'selected' : '' ?>>Suspended</option>
          </select>
        </form>
      </div>

      <table class="data-table">
        <thead>
          <tr>
            <th>Name</th>
            <th>Role</th>
            <th>Status</th>
            <th>KYC</th>
            <th>Rating</th>
            <th>Earnings</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($providers as $p): ?>
          <tr>
            <td>
              <div class="user-cell">
                <div class="user-avatar" style="background:var(--info-light);color:var(--info);">
                  <?= strtoupper(substr($p['name'] ?? 'P', 0, 1)) ?>
                </div>
                <div>
                  <div class="user-name"><?= htmlspecialchars($p['name'] ?? 'N/A') ?></div>
                  <div class="user-sub"><?= htmlspecialchars($p['email'] ?? '') ?></div>
                </div>
              </div>
            </td>
            <td>
              <span class="badge badge-<?= $p['role'] ?? 'default' ?>">
                <?= $p['role'] === 'vet' ? 'Vet' : ($p['role'] === 'provider' ? 'Provider' : 'Vendor') ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?= $p['status'] ?? 'active' ?>">
                <?= ucfirst($p['status'] ?? 'active') ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?= $p['kyc'] ?? 'approved' ?>">
                <?= ucfirst($p['kyc'] ?? 'approved') ?>
              </span>
            </td>
            <td>
              <?php if (!empty($p['rating']) && $p['rating'] > 0): ?>
                ★ <?= number_format($p['rating'], 1) ?>
              <?php else: ?>
                <span class="text-muted" style="font-size:12px;">N/A</span>
              <?php endif; ?>
            </td>
            <td>
              <span class="earnings">$<?= number_format($p['earnings'] ?? 0) ?></span>
            </td>
            <td>
              <div class="action-icons">
                <a href="?profile=<?= $p['id'] ?>" class="icon-btn" title="View Profile">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                </a>

                <form method="POST" style="display:inline;">
                  <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                  <?php if (($p['status'] ?? 'active') === 'active'): ?>
                  <button type="submit" name="action" value="suspend" class="icon-btn danger" title="Suspend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
                  </button>
                  <?php else: ?>
                  <button type="submit" name="action" value="unsuspend" class="icon-btn success" title="Unsuspend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                  <?php endif; ?>
                </form>

                <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this <?= htmlspecialchars($p['role'] ?? 'provider') ?> permanently?');">
                  <input type="hidden" name="provider_id" value="<?= $p['id'] ?>">
                  <button type="submit" name="action" value="delete" class="icon-btn danger" title="Delete">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2 2 2 0 0 1 2-2 2 2 0 0 1 2 2 2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
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


  <?php if ($show_add): ?>
  <div class="modal-overlay open">
    <div class="modal">
      <div class="modal-header">
        <h3>Add New Provider / Vet / Vendor</h3>
        <a href="provider-management.php" class="modal-close">&times;</a>
      </div>
      <form method="POST">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Full Name / Business Name <span style="color:red;">*</span></label>
            <input type="text" class="form-control" name="name" placeholder="Enter name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Email <span style="color:red;">*</span></label>
            <input type="email" class="form-control" name="email" placeholder="user@example.com" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password <span style="color:red;">*</span></label>
            <input type="password" class="form-control" name="password" placeholder="Enter password" required>
          </div>
          <div class="form-group">
            <label class="form-label">Phone</label>
            <input type="text" class="form-control" name="phone" placeholder="+966 5xxxxxxxx">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Role <span style="color:red;">*</span></label>
          <select class="form-control" name="role" required>
            <option value="vet">Veterinarian</option>
            <option value="provider">Service Provider</option>
            <option value="vendor">Vendor (Marketplace)</option>
          </select>
        </div>

        <div class="form-actions">
          <a href="provider-management.php" class="btn-secondary">Cancel</a>
          <button type="submit" name="action" value="add" class="btn-primary">Create Account</button>
        </div>
      </form>
    </div>
  </div>
  <?php endif; ?>

  <?php if ($profile): ?>
  <div class="modal-overlay open">
    <div class="modal modal-lg">
      <div class="modal-header">
        <h3><?= htmlspecialchars($profile['name'] ?? 'Profile') ?></h3>
        <a href="provider-management.php" class="modal-close">&times;</a>
      </div>

      <div class="profile-header">
        <div class="profile-avatar-lg" style="background:var(--info-light);color:var(--info);">
          <?= strtoupper(substr($profile['name'] ?? 'P', 0, 1)) ?>
        </div>
        <div class="profile-meta">
          <div class="name"><?= htmlspecialchars($profile['name'] ?? '') ?></div>
          <div class="email"><?= htmlspecialchars($profile['email'] ?? '') ?></div>
        </div>
      </div>

      <div class="form-actions">
        <a href="provider-management.php" class="btn-secondary">Close</a>
        
        <?php if (($profile['status'] ?? 'active') === 'active'): ?>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="provider_id" value="<?= $profile['id'] ?>">
          <button type="submit" name="action" value="suspend" class="btn-danger">Suspend Account</button>
        </form>
        <?php else: ?>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="provider_id" value="<?= $profile['id'] ?>">
          <button type="submit" name="action" value="unsuspend" class="btn-success">Unsuspend Account</button>
        </form>
        <?php endif; ?>

        <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this account permanently?');">
          <input type="hidden" name="provider_id" value="<?= $profile['id'] ?>">
          <button type="submit" name="action" value="delete" class="btn-danger">Delete Account</button>
        </form>
      </div>
    </div>
  </div>
  <?php endif; ?>

  </body>
  </html>