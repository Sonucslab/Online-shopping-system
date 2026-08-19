<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Dashboard — Nexus Electronics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

  <!-- Sidebar -->
  <aside class="admin-sidebar">
    <a href="dashboard.php" class="logo" style="color:#fff;font-size:1.2rem;font-weight:700;display:block;padding:1.2rem;text-decoration:none;">
      <i class="fa-solid fa-bolt" style="margin-right:6px;"></i> Nexus Admin
    </a>
    <nav class="admin-nav">
      <a href="dashboard.php" class="active"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="orders.php"><i class="fa-solid fa-box"></i> Orders</a>
      <a href="products.php"><i class="fa-solid fa-tag"></i> Products</a>
      <a href="categories.php"><i class="fa-solid fa-list"></i> Categories</a>
      <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="admin-main">
    <header class="admin-header">
      <h2>Dashboard Overview</h2>
      <div><strong><?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?></strong> (Admin)</div>
    </header>

    <!-- Stats Cards -->
    <div class="grid grid-cols-4 mb-4" id="stats-grid">
      <div class="card text-center" style="border-left:4px solid #2563eb;">
        <h4 style="color:var(--text-muted);margin-bottom:.5rem;">Total Revenue</h4>
        <div style="font-size:1.5rem;font-weight:700;" id="stat-revenue">Loading...</div>
      </div>
      <div class="card text-center" style="border-left:4px solid #22c55e;">
        <h4 style="color:var(--text-muted);margin-bottom:.5rem;">Orders</h4>
        <div style="font-size:1.5rem;font-weight:700;" id="stat-orders">-</div>
      </div>
      <div class="card text-center" style="border-left:4px solid #f97316;">
        <h4 style="color:var(--text-muted);margin-bottom:.5rem;">Products</h4>
        <div style="font-size:1.5rem;font-weight:700;" id="stat-products">-</div>
      </div>
      <div class="card text-center" style="border-left:4px solid #a855f7;">
        <h4 style="color:var(--text-muted);margin-bottom:.5rem;">Customers</h4>
        <div style="font-size:1.5rem;font-weight:700;" id="stat-customers">-</div>
      </div>
    </div>

    <!-- Recent Orders -->
    <div class="card">
      <h3 style="margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border-color);">Recent Orders</h3>
      <table class="table" id="recent-orders-table">
        <thead>
          <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Status</th><th>Total</th></tr>
        </thead>
        <tbody id="recent-orders-body">
          <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    // Fetch report data for dashboard
    fetch('../php/admin/reports.php')
      .then(r => r.json())
      .then(data => {
        document.getElementById('stat-revenue').textContent = '$' + parseFloat(data.stats.total_revenue).toLocaleString('en-US', {minimumFractionDigits:2});
        document.getElementById('stat-orders').textContent = data.stats.total_orders;
        document.getElementById('stat-products').textContent = data.stats.total_products;
        document.getElementById('stat-customers').textContent = data.stats.total_customers;

        // Recent orders
        const tbody = document.getElementById('recent-orders-body');
        if (data.recent_orders.length === 0) {
          tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">No orders yet</td></tr>';
          return;
        }
        tbody.innerHTML = data.recent_orders.map(o => {
          const statusColors = {pending:'#f59e0b',processing:'#3b82f6',shipped:'#8b5cf6',delivered:'#22c55e',cancelled:'#ef4444'};
          return `<tr>
            <td>#ORD-${String(o.order_id).padStart(3,'0')}</td>
            <td>${o.customer_name}</td>
            <td>${new Date(o.order_date).toLocaleDateString()}</td>
            <td><span style="color:${statusColors[o.status] || '#666'};font-weight:600;text-transform:capitalize;">${o.status}</span></td>
            <td>$${parseFloat(o.total_amount).toFixed(2)}</td>
          </tr>`;
        }).join('');
      })
      .catch(err => console.error('Dashboard fetch error:', err));
  </script>

</body>
</html>
