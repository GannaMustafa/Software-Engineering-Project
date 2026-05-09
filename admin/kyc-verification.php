<?php

require_once 'controllers/KYCController.php';

$active_page = 'kyc';

$controller = new KYCController();

$message = $controller->postAction();
$data     = $controller->index();

$kyc_list = $data['list'];
$status_f = $data['status'];
$search   = $data['search'];

$stats    = $data['stats'];

$total    = $stats['total'];
$pending  = $stats['pending'];
$approved = $stats['approved'];
$rejected = $stats['rejected'];

$doc_view = $data['view'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>KYC Verification — PawAdmin</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php include 'components/sidebar.php'; ?>

<main class="main-content">

  <div class="page-header">
    <h1>KYC Verification</h1>
    <p>Review identity documents and approve service providers.</p>
  </div>

  <?php if ($message): ?>
  <div style="background:var(--success-light);color:#388e3c;padding:10px 16px;border-radius:var(--radius-sm);margin-bottom:16px;font-size:13px;font-weight:500;"><?= $message ?></div>
  <?php endif; ?>

  <div class="stats-grid" style="grid-template-columns:repeat(4,1fr);margin-bottom:24px;">
    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Total Applications</div>
        <div class="stat-value"><?= $total ?></div>
      </div>
      <div class="stat-icon teal">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Pending Review</div>
        <div class="stat-value"><?= $pending ?></div>
      </div>
      <div class="stat-icon yellow">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Approved</div>
        <div class="stat-value"><?= $approved ?></div>
      </div>
      <div class="stat-icon teal">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
      </div>
    </div>
    <div class="stat-card">
      <div class="stat-info">
        <div class="stat-label">Rejected</div>
        <div class="stat-value"><?= $rejected ?></div>
      </div>
      <div class="stat-icon red">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
      </div>
    </div>
  </div>

  <div class="table-card">
    <div class="table-toolbar">
      <form method="GET" style="display:contents;">
        <div class="search-wrap">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          <input type="text" name="search" placeholder="Search applicants…" value="<?= $search ?>">
        </div>
        <button type="submit" class="btn-primary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Search
        </button>
        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all"     <?= $status_f==='all'      ?'selected':'' ?>>All Status</option>
          <option value="pending" <?= $status_f==='pending'  ?'selected':'' ?>>Pending</option>
          <option value="approved"<?= $status_f==='approved' ?'selected':'' ?>>Approved</option>
          <option value="rejected"<?= $status_f==='rejected' ?'selected':'' ?>>Rejected</option>
        </select>
        
      </form>
    </div>

    <table class="data-table">
      <thead>
        <tr>
          <th>Name</th><th>Role</th><th>Documents</th><th>Submitted</th><th>Status</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($kyc_list as $k): ?>
        <tr>
          <td>
            <div class="user-cell">
              <div class="user-avatar" style="background:var(--accent-blue);color:#2B2B2B;"><?= strtoupper(substr($k['name'],0,1)) ?></div>
              <div>
                <div class="user-name"><?= htmlspecialchars($k['name']) ?></div>
                <div class="user-sub"><?= htmlspecialchars($k['email']) ?></div>
              </div>
            </div>
          </td>
          <td><span class="badge badge-<?= $k['role'] ?>"><?= ucfirst($k['role']) ?></span></td>
          <td>
            <div class="doc-preview">
              <div class="doc-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
              </div>
              <?= count($k['docs']) ?> document(s)
            </div>
          </td>
<td>
    <?= isset($k['submitted_at']) 
        ? date('M j, Y', strtotime($k['submitted_at'])) 
        : (isset($k['created_at']) ? date('M j, Y', strtotime($k['created_at'])) : 'N/A') 
    ?>
</td>
<td>
    <span class="badge badge-<?= $k['status'] ?? 'pending' ?>">
        <?= ucfirst($k['status'] ?? 'pending') ?>
    </span>
</td>
          <td>
            <div class="action-icons">
              <a href="?view=<?= $k['id'] ?>&status=<?= $status_f ?>" class="icon-btn" title="View Documents">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/></svg>
              </a>
              <?php if ($k['status'] === 'pending'): ?>
              <form method="POST" style="display:inline;">
                <input type="hidden" name="kyc_id" value="<?= $k['id'] ?>">
                <button type="submit" name="action" value="approve" class="icon-btn success" title="Approve">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
              </form>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Reject this KYC application?');">
                <input type="hidden" name="kyc_id" value="<?= $k['id'] ?>">
                <button type="submit" name="action" value="reject" class="icon-btn danger" title="Reject">
                  <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>

    <div class="pagination">
      <span>Showing <?= count($kyc_list) ?> of <?= $total ?> applications</span>
      <div class="pagination-btns">
        <button class="page-btn active">1</button>
      </div>
    </div>
  </div>

</main>

<?php if ($doc_view): ?>
<div class="modal-overlay open">
  <div class="modal">
    <div class="modal-header">
      <h3>KYC Documents — <?= htmlspecialchars($doc_view['name'] ?? $doc_view['username'] ?? 'Applicant') ?></h3>
      <a href="kyc-verification.php?status=<?= $status_f ?>" class="modal-close">&times;</a>
    </div>

    <div class="info-grid">
      <div class="info-item"><div class="info-label">Applicant</div><div class="info-value"><?= htmlspecialchars($doc_view['name'] ?? $doc_view['username'] ?? 'N/A') ?></div></div>
      <div class="info-item"><div class="info-label">Role</div><div class="info-value"><span class="badge badge-<?= $doc_view['role'] ?? 'default' ?>"><?= ucfirst($doc_view['role'] ?? 'user') ?></span></div></div>
      <div class="info-item"><div class="info-label">Submitted</div><div class="info-value">
        <?= isset($doc_view['submitted_at']) ? date('M j, Y', strtotime($doc_view['submitted_at'])) : 'N/A' ?>
      </div></div>
      <div class="info-item"><div class="info-label">Current Status</div><div class="info-value"><span class="badge badge-<?= $doc_view['status'] ?? 'pending' ?>"><?= ucfirst($doc_view['status'] ?? 'pending') ?></span></div></div>
    </div>

    <div class="section-divider">Submitted Documents</div>
    <div style="padding:10px 0;">
      <?php 
        // Fake documents for display (since we don't store actual files yet)
        $fake_docs = ($doc_view['role'] === 'vet' || ($doc_view['user_role'] ?? '') === 'vet') 
          ? ['National ID', 'Medical License', 'University Certificate']
          : ['Business License', 'Owner ID', 'Tax Certificate'];
      ?>
      <?php foreach ($fake_docs as $doc): ?>
      <div style="display:flex;align-items:center;gap:12px;padding:12px 14px;background:var(--bg-light);border-radius:var(--radius-sm);margin-bottom:8px;">
        <div class="doc-icon" style="width:36px;height:36px;">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        </div>
        <div style="flex:1;">
          <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($doc) ?></div>
          <div style="font-size:11px;color:var(--text-muted);">Uploaded <?= isset($doc_view['submitted_at']) ? date('M j, Y', strtotime($doc_view['submitted_at'])) : 'Recently' ?></div>
        </div>
        <span style="font-size:12px;color:var(--primary);cursor:pointer;font-weight:500;">View File</span>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if (!empty($doc_view['note'])): ?>
    <div class="section-divider">Admin Note</div>
    <p style="font-size:13px;color:var(--text-muted);"><?= htmlspecialchars($doc_view['note']) ?></p>
    <?php endif; ?>

    <div class="form-actions">
      <a href="kyc-verification.php?status=<?= $status_f ?>" class="btn-secondary">Close</a>
      
      <?php if (($doc_view['status'] ?? '') === 'pending'): ?>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="kyc_id" value="<?= $doc_view['id'] ?>">
        <button type="submit" name="action" value="reject" class="btn-danger">Reject</button>
      </form>
      <form method="POST" style="display:inline;">
        <input type="hidden" name="kyc_id" value="<?= $doc_view['id'] ?>">
        <button type="submit" name="action" value="approve" class="btn-primary">Approve</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

</body>
</html>
