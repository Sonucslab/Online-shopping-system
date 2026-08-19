<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sales Reports — Nexus Admin</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="stylesheet" href="../css/admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
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
      <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php" class="active"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <h2>Sales Reports</h2>
    </header>

    <!-- Charts Row -->
    <div class="grid grid-cols-2 mb-4">
      <div class="card">
        <h3 style="margin-bottom:1rem;">Revenue by Category</h3>
        <canvas id="chart-category" height="260"></canvas>
      </div>
      <div class="card">
        <h3 style="margin-bottom:1rem;">Payment Methods</h3>
        <canvas id="chart-payment" height="260"></canvas>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="card mb-4">
      <h3 style="margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border-color);">Top 5 Products by Revenue</h3>
      <table class="table">
        <thead>
          <tr><th>#</th><th>Product</th><th>Units Sold</th><th>Revenue</th></tr>
        </thead>
        <tbody id="top-products-tbody">
          <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Monthly Sales Table -->
    <div class="card">
      <h3 style="margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border-color);">Monthly Sales Summary</h3>
      <table class="table">
        <thead>
          <tr><th>Month</th><th>Orders</th><th>Revenue</th><th>Avg. Order Value</th></tr>
        </thead>
        <tbody id="monthly-tbody">
          <tr><td colspan="4" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>
  </main>

  <script>
    const chartColors = ['#2563eb','#22c55e','#f97316','#a855f7','#ef4444','#14b8a6','#f59e0b'];

    fetch('../php/admin/reports.php')
      .then(r => r.json())
      .then(data => {
        // Revenue by Category Chart (Bar)
        const catCtx = document.getElementById('chart-category').getContext('2d');
        new Chart(catCtx, {
          type: 'bar',
          data: {
            labels: data.revenue_by_category.map(c => c.category_name),
            datasets: [{
              label: 'Revenue ($)',
              data: data.revenue_by_category.map(c => parseFloat(c.revenue)),
              backgroundColor: chartColors.slice(0, data.revenue_by_category.length),
              borderRadius: 6,
              barThickness: 40
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => '$' + v } } }
          }
        });

        // Payment Methods Chart (Doughnut)
        const payCtx = document.getElementById('chart-payment').getContext('2d');
        new Chart(payCtx, {
          type: 'doughnut',
          data: {
            labels: data.payment_methods.map(p => p.method.replace('_',' ').replace(/\b\w/g,l=>l.toUpperCase())),
            datasets: [{
              data: data.payment_methods.map(p => parseFloat(p.total)),
              backgroundColor: chartColors.slice(0, data.payment_methods.length),
              borderWidth: 2,
              borderColor: '#fff'
            }]
          },
          options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
        });

        // Top Products Table
        const tpTbody = document.getElementById('top-products-tbody');
        tpTbody.innerHTML = data.top_products.map((p, i) => `<tr>
          <td>${i+1}</td>
          <td>${p.product_name}</td>
          <td>${p.units_sold}</td>
          <td>$${parseFloat(p.revenue).toFixed(2)}</td>
        </tr>`).join('');

        // Monthly Sales Table
        const mTbody = document.getElementById('monthly-tbody');
        if (data.monthly_sales.length === 0) {
          mTbody.innerHTML = '<tr><td colspan="4" style="text-align:center;">No data</td></tr>';
        } else {
          mTbody.innerHTML = data.monthly_sales.map(m => `<tr>
            <td>${m.month_name}</td>
            <td>${m.orders}</td>
            <td>$${parseFloat(m.revenue).toFixed(2)}</td>
            <td>$${(parseFloat(m.revenue) / parseInt(m.orders)).toFixed(2)}</td>
          </tr>`).join('');
        }
      })
      .catch(err => console.error('Reports error:', err));
  </script>

</body>
</html>
