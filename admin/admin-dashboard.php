<?php
$active_page = 'dashboard';

require_once __DIR__ . '/controllers/DashboardController.php';

$controller = new DashboardController();
$data = $controller->index();

$stats = $data['stats'];
$recent_disputes = $data['recent_disputes'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Dashboard — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header">
    <h1>Dashboard</h1>
    <p>Welcome back, Admin. Here's what's happening today.</p>
  </div>

  <div class="stats-grid">
    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Total Users</div>
        <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
      </div>
      <div class="stat-icon teal">
        <i class="ri-user-line"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Total Providers / Vets</div>
        <div class="stat-value"><?= number_format($stats['total_providers']) ?></div>
      </div>
      <div class="stat-icon blue">
        <i class="ri-hospital-line"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Active Pets</div>
        <div class="stat-value"><?= number_format($stats['total_pets']) ?></div>
      </div>
      <div class="stat-icon yellow">
        <i class="ri-heart-3-line"></i>
      </div>
    </div>

    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Pending Disputes</div>
        <div class="stat-value"><?= $stats['pending_disputes'] ?></div>
      </div>
      <div class="stat-icon red">
        <i class="ri-error-warning-line"></i>
      </div>
    </div>
  </div>

    <div class="charts-grid">

    <div class="chart-card">
      <h3>User Growth (Last 6 Months)</h3>
      <canvas id="userChart" height="100"></canvas>
    </div>

    <div class="chart-card">
      <h3>Service Orders (Last 6 Months)</h3>
      <canvas id="ordersChart"></canvas>
    </div>

  </div>

  <div class="table-card">
    <div class="table-toolbar" style="justify-content:space-between;">
      <span style="font-size:14px;font-weight:600;">Recent Disputes</span>
      <a href="disputes.php" class="btn-primary">
        <i class="ri-arrow-right-line"></i> View All
      </a>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>User</th>
          <th>Provider</th>
          <th>Issue</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recent_disputes as $d): ?>
        <tr>
          <td><?= htmlspecialchars($d['user_name'] ?? 'User') ?></td>
          <td><?= htmlspecialchars($d['provider_name'] ?? 'Provider') ?></td>
          <td><?= htmlspecialchars(substr($d['issue'] ?? '', 0, 60)) ?>...</td>
          <td>
            <span class="badge badge-<?= $d['status'] ?>"><?= ucfirst($d['status']) ?></span>
          </td>
          <td>
            <a href="disputes.php?view=<?= $d['id'] ?>" class="icon-btn">
              <i class="ri-eye-line"></i>
            </a>
          </td>
        </tr>
        <?php endforeach; ?>

        <?php if (empty($recent_disputes)): ?>
        <tr>
          <td colspan="5" style="text-align:center; color:var(--text-muted); padding:20px;">
            No disputes found.
          </td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

</main>

<script>
const months = ['Nov', 'Dec', 'Jan', 'Feb', 'Mar', 'Apr'];
const userData = [820, 940, 1020, 1100, 1190, 1280];
const ordersData = [130, 210, 170, 260, 200, 310];

new Chart(document.getElementById('userChart'), {
  type: 'line',
  data: {
    labels: months,
    datasets: [{
      label: 'Users',
      data: userData,
      borderColor: '#3AAFA9',
      backgroundColor: 'rgba(58,175,169,0.15)',
      fill: true,
      tension: 0.4
    }]
  }
});

new Chart(document.getElementById('ordersChart'), {
  type: 'bar',
  data: {
    labels: months,
    datasets: [{
      label: 'Orders',
      data: ordersData,
      backgroundColor: '#3AAFA9'
    }]
  }
});
</script>

</body>
</html>