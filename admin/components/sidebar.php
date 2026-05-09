<link href="https://cdn.jsdelivr.net/npm/remixicon@4.3.0/fonts/remixicon.css" rel="stylesheet">

<aside class="sidebar">

  <nav class="sidebar-nav">
    <span class="nav-label">Main Menu</span>

    <a href="admin-dashboard.php" class="nav-item <?= $active_page === 'dashboard' ? 'active' : '' ?>">
      <i class="ri-dashboard-line"></i>
      Dashboard
    </a>

    <a href="user-management.php" class="nav-item <?= $active_page === 'users' ? 'active' : '' ?>">
      <i class="ri-user-line"></i>
      Pet Owners
    </a>

    <a href="provider-management.php" class="nav-item <?= $active_page === 'providers' ? 'active' : '' ?>">
      <i class="ri-profile-line"></i>
      Providers
    </a>

    <a href="disputes.php" class="nav-item <?= $active_page  === 'disputes' ? 'active' : '' ?>">
      <i class="ri-error-warning-line"></i>
      Disputes
    </a>
    <a href="kyc-verification.php"
       class="nav-item <?= $active_page === 'kyc' ? 'active' : '' ?>">
      <i class="ri-notification-3-line"></i>
      KYC Verification
    </a>
    <a href="system-control.php" class="nav-item <?= $active_page === 'system' ? 'active' : '' ?>">
      <i class="ri-settings-3-line"></i>
      System Control
    </a>

    <a href="service-management.php"
       class="nav-item <?= $active_page === 'services' ? 'active' : '' ?>">
      <i class="ri-service-line"></i>
      Services Management
    </a>

    <a href="surgery-management.php"
       class="nav-item <?= $active_page === 'surgeries' ? 'active' : '' ?>">
      <i class="ri-hospital-line"></i>
      Surgeries Management
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="sidebar-admin">
      <div class="admin-avatar">HE</div>
      <div class="admin-info">
        <div class="admin-name">Habiba Elnady</div>
        <div class="admin-role">Admin</div>
      </div>
    </div>
  </div>

</aside>