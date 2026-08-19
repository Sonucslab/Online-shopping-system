<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Customers — Nexus Admin</title>
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
      <a href="orders.php"><i class="fa-solid fa-box"></i> Orders</a>
      <a href="products.php"><i class="fa-solid fa-tag"></i> Products</a>
      <a href="categories.php"><i class="fa-solid fa-list"></i> Categories</a>
      <a href="customers.php" class="active"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <h2>Customer List</h2>
    </header>

    <div class="card">
      <table class="table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>City</th><th>Orders</th><th>Total Spent</th><th>Actions</th></tr>
        </thead>
        <tbody id="cust-tbody">
          <tr><td colspan="8" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    function loadCustomers() {
      fetch('../php/admin/customers_list.php')
        .then(r => r.json())
        .then(data => {
          const tbody = document.getElementById('cust-tbody');
          if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;">No customers yet</td></tr>';
            return;
          }
          tbody.innerHTML = data.map(c => `<tr>
            <td>${c.customer_id}</td>
            <td>${c.first_name} ${c.last_name}</td>
            <td>${c.email}</td>
            <td>${c.phone || '—'}</td>
            <td>${c.city || '—'}</td>
            <td>${c.order_count}</td>
            <td>$${parseFloat(c.total_spent).toFixed(2)}</td>
            <td>
              <button class="btn" onclick="deleteCustomer(${c.customer_id})" style="padding:4px 10px;font-size:13px;color:#ef4444;"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>`).join('');
        });
    }
    loadCustomers();

    function deleteCustomer(id) {
      if (!confirm('Delete this customer?')) return;
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('customer_id', id);
      fetch('../php/admin/customers_list.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) loadCustomers(); else alert(data.error); });
    }
  </script>

</body>
</html>
