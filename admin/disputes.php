<?php
$active_page = 'disputes';

require_once __DIR__ . '/controllers/DisputeController.php';

$controller = new DisputeController();
$message = $controller->handlePost();
$data = $controller->index();

$disputes = $data['disputes'];
$status_f = $data['status'];
$search = $data['search'];

$view_id = (int)($_GET['view'] ?? 0);
$resolve_id = (int)($_GET['resolve'] ?? 0);

$detail = $controller->getDetails($view_id);
$resolve_dispute = $controller->getDetails($resolve_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Disputes — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;">
    <div>
      <h1>Disputes</h1>
      <p>Review and resolve user-provider conflicts.</p>
    </div>

    <div style="display:flex;gap:10px;align-items:center;">
      <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <div class="search-wrap" style="min-width:200px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" placeholder="Search disputes…" value="<?= $search ?>">
        </div>

        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all"     <?= $status_f==='all'      ?'selected':'' ?>>All Status</option>
          <option value="pending" <?= $status_f==='pending'  ?'selected':'' ?>>Pending</option>
          <option value="resolved"<?= $status_f==='resolved' ?'selected':'' ?>>Resolved</option>
        </select>
      </form>
    </div>
  </div>

  <?php if ($message): ?>
  <div style="background:var(--success-light);color:#388e3c;padding:10px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:500;"><?= htmlspecialchars($message) ?></div>
  <?php endif; ?>

  <div class="disputes-grid">
    <?php foreach ($disputes as $d): ?>
    <div class="dispute-card">
      <div class="dispute-card-header">
        <div>
          <div class="dispute-parties">
            <?= htmlspecialchars($d['user_name'] ?? 'Unknown User') ?> 
            <span>vs</span> 
            <?= htmlspecialchars($d['provider_name'] ?? 'Unknown Provider') ?>
          </div>
          <div class="dispute-date" style="margin-top:3px;">
            <?= $d['date'] ?? 'N/A' ?> &bull; $<?= number_format($d['amount'] ?? 0, 2) ?>
          </div>
        </div>

        <span class="badge badge-<?= $d['status'] ?? 'pending' ?>">
          <?= ucfirst($d['status'] ?? 'pending') ?>
        </span>
      </div>

      <div class="dispute-summary"><?= htmlspecialchars($d['issue'] ?? 'No issue description') ?></div>

      <div class="dispute-footer">
        <div class="dispute-date">1 evidence file(s)</div> 

        <div class="dispute-actions">
          <a href="?view=<?= $d['id'] ?>&status=<?= $status_f ?>" class="icon-btn" title="View Details">
            <i class="ri-eye-line"></i>
          </a>

          <?php if (($d['status'] ?? 'pending') === 'pending'): ?>
          <a href="?resolve=<?= $d['id'] ?>&status=<?= $status_f ?>" class="icon-btn success" title="Resolve">
            <i class="ri-check-line"></i>
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

</main>

<?php if ($detail): ?>
<div class="modal-overlay open">
  <div class="modal modal-lg">
    <div class="modal-header">
      <h3>Dispute #<?= $detail['id'] ?> Details</h3>
      <a href="disputes.php?status=<?= $status_f ?>" class="modal-close">
        <i class="ri-close-line"></i>
      </a>  
    </div>

    <div class="info-grid">
      <div class="info-item"><div class="info-label">User</div><div class="info-value"><?= htmlspecialchars($detail['user_name'] ?? 'N/A') ?></div></div>
      <div class="info-item"><div class="info-label">Provider</div><div class="info-value"><?= htmlspecialchars($detail['provider_name'] ?? 'N/A') ?></div></div>
      <div class="info-item"><div class="info-label">Date Filed</div><div class="info-value"><?= $detail['date'] ?? 'N/A' ?></div></div>
      <div class="info-item"><div class="info-label">Amount</div><div class="info-value">$<?= number_format($detail['amount'] ?? 0, 2) ?></div></div>
    </div>

    <div class="section-divider">Issue Description</div>
    <p style="font-size:13px;line-height:1.6;color:var(--text-dark);margin-bottom:12px;">
      <?= htmlspecialchars($detail['issue'] ?? 'No description') ?>
    </p>

    <div class="section-divider">Messages</div>

    <div class="message-bubble msg-user">
      <div class="msg-label">User Statement</div>
      <?= htmlspecialchars($detail['user_msg'] ?? 'No message') ?>
    </div>

    <div class="message-bubble msg-provider">
      <div class="msg-label">Provider Response</div>
      <?= htmlspecialchars($detail['provider_resp'] ?? 'No response yet') ?>
    </div>

    <div class="section-divider">Evidence Files</div>
    <p style="color:var(--text-muted);">No evidence files uploaded yet.</p>

    <div class="form-actions">
      <a href="disputes.php?status=<?= $status_f ?>" class="btn-secondary">Close</a>
      <?php if (($detail['status'] ?? '') === 'pending'): ?>
      <a href="?resolve=<?= $detail['id'] ?>" class="btn-primary">Resolve Dispute</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<?php if ($resolve_dispute): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header">
      <h3>Resolve Dispute #<?= $resolve_dispute['id'] ?></h3>
      <a href="disputes.php?status=<?= $status_f ?>" class="modal-close">
        <i class="ri-close-line"></i>
      </a>
    </div>

    <div style="background:var(--bg-light);border-radius:var(--radius-sm);padding:12px;margin-bottom:16px;font-size:13px;">
      <strong><?= htmlspecialchars($resolve_dispute['user_name'] ?? '') ?></strong> vs 
      <strong><?= htmlspecialchars($resolve_dispute['provider_name'] ?? '') ?></strong><br>
      <span class="text-muted"><?= htmlspecialchars($resolve_dispute['issue'] ?? '') ?></span>
    </div>

    <form method="POST">
      <input type="hidden" name="dispute_id" value="<?= $resolve_dispute['id'] ?>">

      <div class="form-group">
        <label class="form-label">Decision</label>
        <select class="form-control" name="decision" required>
          <option value="">— Select decision —</option>
          <option value="Full Refund to User">Full Refund to User</option>
          <option value="Reject Claim">Reject Claim (Provider wins)</option>
          <option value="Partial Refund">Partial Refund</option>
        </select>
      </div>

      <div class="form-group">
        <label class="form-label">Admin Note (optional)</label>
        <textarea class="form-control" name="note" rows="3"></textarea>
      </div>

      <div class="form-actions">
        <a href="disputes.php?status=<?= $status_f ?>" class="btn-secondary">Cancel</a>
        <button type="submit" name="action" value="resolve" class="btn-primary">
          Submit Resolution
        </button>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

</body>
</html>