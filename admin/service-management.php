<?php
require_once 'controllers/ServiceController.php';

$active_page = 'services';

$controller = new ServiceController();
$message    = $controller->postAction();
$data       = $controller->index();

$services      = $data['list'];
$search        = $data['search'];
$performed_by_f = $data['performed_by'];
$currentService = $data['service'];
$editService   = $data['editService'];
$show_add      = $data['showAdd'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Service Management — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;">
    <div>
      <h1>Service Management</h1>
      <p>Manage all available services and pricing.</p>
    </div>
    <a href="?add=1" class="btn-primary">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
      Add Service
    </a>
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
          <input type="text" name="search" placeholder="Search service..." value="<?= $search ?>">
        </div>
        <button type="submit" class="btn-primary">Search</button>

        <select name="performed_by" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= $performed_by_f==='all' ? 'selected' : '' ?>>All</option>
          <option value="Vet" <?= $performed_by_f==='Vet' ? 'selected' : '' ?>>Vet</option>
          <option value="Provider" <?= $performed_by_f==='Provider' ? 'selected' : '' ?>>Provider</option>
        </select>
      </form>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Service</th>
          <th>Category</th>
          <th>Price</th>
          <th>Duration</th>
          <th>Performed by</th>
          <th>Description</th>
          <th>Discount</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s['name']) ?></strong></td>
          <td><span class="badge badge-approved"><?= htmlspecialchars($s['category']) ?></span></td>
          <td><span class="earnings">$<?= number_format($s['price']) ?></span></td>
          <td><?= htmlspecialchars($s['duration']) ?></td>
          <td><?= htmlspecialchars($s['performed_by']) ?></td>
          <td style="max-width:280px;"><?= htmlspecialchars($s['description']) ?></td>
          <td>
            <?php if ($s['discount_percentage'] > 0): ?>
              <span class="badge badge-success">-<?= $s['discount_percentage'] ?>%</span>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div class="action-icons">
              <a href="?view=<?= $s['id'] ?>" class="icon-btn" title="View Details">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              </a>
              <a href="?edit=<?= $s['id'] ?>" class="icon-btn success" title="Edit Service">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2v-2a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v-2"/><path d="M18 2l4 4-10 10H8v-4L18 2z"/></svg>
              </a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this service?')">
                <input type="hidden" name="service_id" value="<?= $s['id'] ?>">
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

<?php 
$modalTitle = '';
$modalMode = '';
$serviceData = null;

if ($show_add) {
    $modalTitle = 'Add New Service';
    $modalMode = 'add';
} elseif (isset($_GET['edit']) && $editService) {
    $modalTitle = 'Edit Service';
    $modalMode = 'edit';
    $serviceData = $editService;
} elseif (isset($_GET['view']) && $currentService) {
    $modalTitle = 'Service Details';
    $modalMode = 'view';
    $serviceData = $currentService;
}
?>

<?php if ($modalMode): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header">
      <h3><?= $modalTitle ?></h3>
      <a href="service-management.php" class="modal-close">&times;</a>
    </div>

    <?php if ($modalMode === 'view'): ?>
      <div class="profile-header">
        <div class="profile-avatar-lg" style="background:var(--primary-light);color:var(--primary-dark);font-size:28px;">📋</div>
        <div class="profile-meta">
          <div class="name"><?= htmlspecialchars($serviceData['name']) ?></div>
          <div class="email"><?= htmlspecialchars($serviceData['category']) ?></div>
        </div>
      </div>

      <p><strong>Price:</strong> <span class="earnings">$<?= number_format($serviceData['price']) ?></span></p>
      <p><strong>Duration:</strong> <?= htmlspecialchars($serviceData['duration']) ?></p>
      <p><strong>Performed by:</strong> <?= htmlspecialchars($serviceData['performed_by']) ?></p>
      <p><strong>Discount:</strong> <?= $serviceData['discount_percentage'] > 0 ? $serviceData['discount_percentage'].'%' : 'None' ?></p>
      <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($serviceData['description'])) ?></p>

      <div class="form-actions">
        <a href="service-management.php" class="btn-secondary">Close</a>
        <a href="?edit=<?= $serviceData['id'] ?>" class="btn-primary">Edit This Service</a>
      </div>

    <?php else: ?>
      <form method="POST">
        <input type="hidden" name="service_id" value="<?= $serviceData['id'] ?? '' ?>">
        <input type="hidden" name="action" value="<?= $modalMode ?>">

        <div class="form-group">
          <label class="form-label">Service Name</label>
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($serviceData['name'] ?? '') ?>" required>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Category</label>
            <select name="category" class="form-control" required>
              <option value="Consultation" <?= ($serviceData['category'] ?? '') === 'Consultation' ? 'selected' : '' ?>>Consultation</option>
              <option value="Vaccination" <?= ($serviceData['category'] ?? '') === 'Vaccination' ? 'selected' : '' ?>>Vaccination</option>
              <option value="Grooming" <?= ($serviceData['category'] ?? '') === 'Grooming' ? 'selected' : '' ?>>Grooming</option>
              <option value="Dental" <?= ($serviceData['category'] ?? '') === 'Dental' ? 'selected' : '' ?>>Dental</option>
              <option value="Sitting" <?= ($serviceData['category'] ?? '') === 'Sitting' ? 'selected' : '' ?>>Sitting</option>
              <option value="Surgery" <?= ($serviceData['category'] ?? '') === 'Surgery' ? 'selected' : '' ?>>Surgery</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Price (USD)</label>
            <input type="number" name="price" class="form-control" value="<?= $serviceData['price'] ?? '' ?>" step="0.01" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Duration</label>
            <input type="text" name="duration" class="form-control" value="<?= htmlspecialchars($serviceData['duration'] ?? '') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Performed By</label>
            <select name="performed_by" class="form-control" required>
              <option value="Vet" <?= ($serviceData['performed_by'] ?? '') === 'Vet' ? 'selected' : '' ?>>Vet</option>
              <option value="Provider" <?= ($serviceData['performed_by'] ?? '') === 'Provider' ? 'selected' : '' ?>>Provider</option>
            </select>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Discount (%)</label>
          <input type="number" name="discount_percentage" class="form-control" value="<?= $serviceData['discount_percentage'] ?? 0 ?>" min="0" max="100">
        </div>

        <div class="form-group">
          <label class="form-label">Description</label>
          <textarea name="description" class="form-control" rows="4"><?= htmlspecialchars($serviceData['description'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
          <a href="service-management.php" class="btn-secondary">Cancel</a>
          <button type="submit" class="btn-primary"><?= $modalMode === 'edit' ? 'Update Service' : 'Add Service' ?></button>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

</body>
</html>