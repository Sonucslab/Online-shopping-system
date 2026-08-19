<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Categories — Nexus Admin</title>
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
      <a href="categories.php" class="active"><i class="fa-solid fa-list"></i> Categories</a>
      <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <h2>Manage Categories</h2>
      <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Add Category</button>
    </header>

    <div class="card">
      <table class="table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Description</th><th>Products</th><th>Actions</th></tr>
        </thead>
        <tbody id="cat-tbody">
          <tr><td colspan="5" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal" style="display:none;">
      <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
          <h3 id="modal-title">Add Category</h3>
          <button onclick="closeModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <form id="cat-form" onsubmit="saveCategory(event)">
          <input type="hidden" id="f-id" value="">
          <div class="form-group">
            <label class="form-label">Category Name</label>
            <input type="text" class="form-control" id="f-name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="f-desc" rows="2"></textarea>
          </div>
          <button type="submit" class="btn btn-primary w-100" style="margin-top:1rem;">Save Category</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    function loadCategories() {
      fetch('../php/admin/categories_crud.php')
        .then(r => r.json())
        .then(data => {
          const tbody = document.getElementById('cat-tbody');
          tbody.innerHTML = data.map(c => `<tr>
            <td>${c.category_id}</td>
            <td>${c.name}</td>
            <td>${c.description || '—'}</td>
            <td>${c.product_count}</td>
            <td>
              <button class="btn btn-secondary" onclick='editCat(${JSON.stringify(c)})' style="padding:4px 10px;font-size:13px;"><i class="fa-solid fa-pen"></i></button>
              <button class="btn" onclick="deleteCat(${c.category_id})" style="padding:4px 10px;font-size:13px;color:#ef4444;"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>`).join('');
        });
    }
    loadCategories();

    function openModal(t = 'Add Category') {
      document.getElementById('modal').style.display = 'flex';
      document.getElementById('modal-title').textContent = t;
      if (t === 'Add Category') document.getElementById('cat-form').reset();
      document.getElementById('f-id').value = '';
    }
    function closeModal() { document.getElementById('modal').style.display = 'none'; }

    function editCat(c) {
      openModal('Edit Category');
      document.getElementById('f-id').value = c.category_id;
      document.getElementById('f-name').value = c.name;
      document.getElementById('f-desc').value = c.description || '';
    }

    function saveCategory(e) {
      e.preventDefault();
      const id = document.getElementById('f-id').value;
      const fd = new FormData();
      fd.append('action', id ? 'edit' : 'add');
      if (id) fd.append('category_id', id);
      fd.append('name', document.getElementById('f-name').value);
      fd.append('description', document.getElementById('f-desc').value);
      fetch('../php/admin/categories_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) { closeModal(); loadCategories(); } else alert(data.error); });
    }

    function deleteCat(id) {
      if (!confirm('Delete this category?')) return;
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('category_id', id);
      fetch('../php/admin/categories_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => { if (data.success) loadCategories(); else alert(data.error); });
    }
  </script>

</body>
</html>
