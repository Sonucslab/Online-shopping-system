<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Orders — Nexus Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
</head>
<body>

  <aside class="admin-sidebar">
    <a href="dashboard.php" class="logo" style="color:#fff;font-size:1.2rem;font-weight:700;display:block;padding:1.2rem;text-decoration:none;">
      <i class="fa-solid fa-bolt" style="margin-right:6px;"></i> Nexus Admin
    </a>
    <nav class="admin-nav">
      <a href="dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a>
      <a href="orders.php" class="active"><i class="fa-solid fa-box"></i> Orders</a>
      <a href="products.php"><i class="fa-solid fa-tag"></i> Products</a>
      <a href="categories.php"><i class="fa-solid fa-list"></i> Categories</a>
      <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <h2>Manage Orders</h2>
    </header>

    <div class="card">
      <table class="table">
        <thead>
          <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th>Action</th></tr>
        </thead>
        <tbody id="orders-tbody">
          <tr><td colspan="7" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const statusColors = {pending:'#f59e0b',processing:'#3b82f6',shipped:'#8b5cf6',delivered:'#22c55e',cancelled:'#ef4444'};

    function loadOrders() {
      fetch('../php/admin/orders_manage.php')
        .then(r => r.json())
        .then(data => {
          const tbody = document.getElementById('orders-tbody');
          if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No orders yet</td></tr>';
            return;
          }
          tbody.innerHTML = data.map(o => `<tr>
            <td>#ORD-${String(o.order_id).padStart(3,'0')}</td>
            <td>${o.customer_name}<br><small style="color:#6b7280;">${o.customer_email}</small></td>
            <td>${new Date(o.order_date).toLocaleDateString()}</td>
            <td style="text-transform:capitalize;">${(o.payment_method||'—').replace('_',' ')}</td>
            <td>$${parseFloat(o.total_amount).toFixed(2)}</td>
            <td><span style="color:${statusColors[o.status]};font-weight:600;text-transform:capitalize;">${o.status}</span></td>
            <td>
              <select onchange="updateStatus(${o.order_id}, this.value)" style="padding:4px 8px;border:1px solid #ddd;border-radius:4px;font-size:13px;">
                ${['pending','processing','shipped','delivered','cancelled'].map(s =>
                  `<option value="${s}" ${s===o.status?'selected':''}>${s.charAt(0).toUpperCase()+s.slice(1)}</option>`
                ).join('')}
              </select>
            </td>
          </tr>`).join('');
        });
    }
    loadOrders();

    function updateStatus(orderId, status) {
      const fd = new FormData();
      fd.append('action', 'update_status');
      fd.append('order_id', orderId);
      fd.append('status', status);
      fetch('../php/admin/orders_manage.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.success) loadOrders();
          else alert(data.error || 'Update failed');
        });
    }
  </script>

</body>
</html>
