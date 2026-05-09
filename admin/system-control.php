<?php
$active_page = 'system';

require_once __DIR__ . '/controllers/SystemController.php';

$controller = new SystemController();
$message = $controller->handleRequest();
$data = $controller->index();

$suspended = $data['suspended'];
$logs = $data['logs'];
$archive_stats = $data['archive_stats'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>System Control — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header">
    <h1>System Control</h1>
    <p>Manage suspended accounts, archive tools, and review admin logs.</p>
  </div>

  <?php if ($message): ?>
  <div style="background:var(--success-light);color:#388e3c;padding:10px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:500;"><?= $message ?></div>
  <?php endif; ?>

  <div class="system-grid">

    <div class="system-section">
      <div class="system-section-header">
        <h3>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
          Suspended Accounts
        </h3>
        <span class="badge badge-suspended"><?= count($suspended) ?></span>
      </div>
      <div class="system-section-body" style="padding:0;">
        <table class="data-table">
          <thead>
            <tr><th>Name</th><th>Type</th><th>Since</th><th>Action</th></tr>
          </thead>
          <tbody>
            <?php foreach ($suspended as $s): ?>
            <tr>
              <td>
                <div class="user-cell">
                  <div class="user-avatar" style="background:var(--danger-light);color:var(--danger);"><?= strtoupper(substr($s['name'],0,1)) ?></div>
                  <span class="fw-600"><?= htmlspecialchars($s['name']) ?></span>
                </div>
              </td>
              <td><span class="badge badge-<?= $s['type']==='vet'?'vet':'provider' ?>"><?= ucfirst($s['type']) ?></span></td>
              <td style="font-size:12px;"><?= $s['since'] ?></td>
              <td>
                <form method="POST">
                  <input type="hidden" name="user_id" value="<?= $s['id'] ?>">
                  <button type="submit" name="action" value="unsuspend" class="icon-btn success" title="Unsuspend">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </button>
                </form>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="system-section">
      <div class="system-section-header">
        <h3>
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
          Archive &amp; Cleanup Tools
        </h3>
      </div>
      <div class="system-section-body">
        <div style="background:var(--bg-light);border-radius:var(--radius-sm);padding:14px;margin-bottom:12px;">
          <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
            <div>
              <div style="font-size:13px;font-weight:600;">Archive Old Orders</div>
              <div style="font-size:12px;color:var(--text-muted);margin-top:2px;"><?= $archive_stats['old_orders'] ?> orders older than 6 months are eligible</div>
            </div>
            <form method="POST">
              <button type="submit" name="action" value="archive_orders" class="btn-primary" style="font-size:12px;padding:6px 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
                Archive Now
              </button>
            </form>
          </div>
          <div style="height:6px;background:var(--border);border-radius:10px;overflow:hidden;">
            <div style="height:100%;width:<?= min(100, ($archive_stats['old_orders']/500)*100) ?>%;background:var(--primary);border-radius:10px;"></div>
          </div>
        </div>

        <div style="background:var(--danger-light);border-radius:var(--radius-sm);padding:14px;">
          <div style="display:flex;align-items:center;justify-content:space-between;">
            <div>
              <div style="font-size:13px;font-weight:600;color:var(--danger);">Delete Inactive Users</div>
              <div style="font-size:12px;color:#c62828;margin-top:2px;"><?= $archive_stats['inactive_users'] ?> users with no activity in 12+ months</div>
            </div>
            <form method="POST" onsubmit="return confirm('This will permanently delete <?= $archive_stats['inactive_users'] ?> inactive users. Are you sure?');">
              <button type="submit" name="action" value="delete_inactive" class="btn-danger" style="font-size:12px;padding:6px 12px;">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
                Delete
              </button>
            </form>
          </div>
        </div>

        <div class="section-divider" style="margin-top:16px;">System Health</div>
        <?php
        $metrics = [
          ['label'=>'Storage Used',    'val'=>'68%', 'pct'=>68, 'color'=>'var(--primary)'],
          ['label'=>'DB Connections',  'val'=>'42%', 'pct'=>42, 'color'=>'var(--info)'],
          ['label'=>'Cache Hit Rate',  'val'=>'91%', 'pct'=>91, 'color'=>'var(--success)'],
        ];
        ?>
        <?php foreach ($metrics as $m): ?>
        <div style="margin-bottom:12px;">
          <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px;">
            <span><?= $m['label'] ?></span>
            <span style="font-weight:600;color:<?= $m['color'] ?>"><?= $m['val'] ?></span>
          </div>
          <div style="height:6px;background:var(--border);border-radius:10px;overflow:hidden;">
            <div style="height:100%;width:<?= $m['pct'] ?>%;background:<?= $m['color'] ?>;border-radius:10px;"></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

  </div>

  <div class="system-section" style="margin-top:20px;">
    <div class="system-section-header">
      <h3>
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        Admin Action Logs
      </h3>
      <span style="font-size:12px;color:var(--text-muted);"><?= count($logs) ?> recent entries</span>
    </div>
    <div class="system-section-body">
      <?php foreach ($logs as $log): ?>
      <div class="log-item">
        <div class="log-dot info"></div>
        <span><?= htmlspecialchars($log['msg'] ?? $log['details'] ?? 'System action performed') ?></span>
        <span class="log-time">
          <?= isset($log['time']) ? date('M j, Y H:i', strtotime($log['time'])) : 'Just now' ?>
        </span>
      </div>
      <?php endforeach; ?>

      <?php if (empty($logs)): ?>
      <p style="text-align:center; color:var(--text-muted); padding:20px;">No recent logs available.</p>
      <?php endif; ?>
    </div>
  </div>

</main>
</body>
</html>
