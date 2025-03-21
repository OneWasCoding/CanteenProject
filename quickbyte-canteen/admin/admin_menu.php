<!-- admin_menu.php -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark flex-column align-items-start" id="sidebar">
  <a class="navbar-brand ms-3 mb-4" href="admin_dashboard.php">
    <i class="bi bi-speedometer2"></i> Admin Panel
  </a>
  <div class="collapse navbar-collapse show">
    <ul class="nav flex-column w-100">
      <li class="nav-item">
        <a class="nav-link text-white" href="admin_dashboard.php">
          <i class="bi bi-bar-chart-line"></i> Dashboard
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manage_users.php">
          <i class="bi bi-people"></i> Manage Users
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manage_stalls.php">
          <i class="bi bi-shop"></i> Manage Stalls
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manage_products.php">
          <i class="bi bi-collection"></i> Manage Products
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manage_orders.php">
          <i class="bi bi-receipt"></i> Manage Orders
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link text-white" href="manage_reviews.php">
          <i class="bi bi-chat-left-text"></i> Manage Reviews
        </a>
      </li>
      <li class="nav-item mt-auto">
        <a class="nav-link text-white" href="../auth/logout.php">
          <i class="bi bi-box-arrow-right"></i> Logout
        </a>
      </li>
    </ul>
  </div>
</nav>

<style>
  /* Sidebar specific styles */
  #sidebar {
    width: 250px;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    z-index: 1000;
  }
  /* Adjust main content to leave space for sidebar */
  .admin-content {
    margin-left: 260px;
    padding: 20px;
  }
  /* Hover effects */
  #sidebar .nav-link:hover {
    background-color: #495057;
    border-radius: 4px;
  }
</style>
