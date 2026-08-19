<?php require_once '../php/db.php'; requireAdmin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Manage Products — Nexus Admin</title>
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
      <a href="products.php" class="active"><i class="fa-solid fa-tag"></i> Products</a>
      <a href="categories.php"><i class="fa-solid fa-list"></i> Categories</a>
      <a href="customers.php"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="reports.php"><i class="fa-solid fa-chart-bar"></i> Reports</a>
      <a href="../php/logout.php" style="margin-top:2rem;color:#ef4444;"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </nav>
  </aside>

  <main class="admin-main">
    <header class="admin-header">
      <h2>Manage Products</h2>
      <button class="btn btn-primary" onclick="openModal()"><i class="fa-solid fa-plus"></i> Add Product</button>
    </header>

    <div class="card">
      <table class="table">
        <thead>
          <tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
        </thead>
        <tbody id="products-tbody">
          <tr><td colspan="6" style="text-align:center;">Loading...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div class="modal-overlay" id="modal" style="display:none;">
      <div class="modal-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
          <h3 id="modal-title">Add Product</h3>
          <button onclick="closeModal()" style="background:none;border:none;font-size:1.2rem;cursor:pointer;">&times;</button>
        </div>
        <form id="product-form" onsubmit="saveProduct(event)">
          <input type="hidden" id="f-id" value="">
          <div class="form-group">
            <label class="form-label">Product Name</label>
            <input type="text" class="form-control" id="f-name" required>
          </div>
          <div class="form-group">
            <label class="form-label">Category</label>
            <select class="form-control" id="f-category" required></select>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea class="form-control" id="f-desc" rows="2"></textarea>
          </div>
          <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
            <div class="form-group">
              <label class="form-label">Price ($)</label>
              <input type="number" class="form-control" id="f-price" step="0.01" min="0.01" required>
            </div>
            <div class="form-group">
              <label class="form-label">Stock Qty</label>
              <input type="number" class="form-control" id="f-stock" min="0" required>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Image URL</label>
            <input type="text" class="form-control" id="f-img" placeholder="images/products/...">
          </div>
          <button type="submit" class="btn btn-primary w-100" style="margin-top:1rem;" id="f-submit">Save Product</button>
        </form>
      </div>
    </div>
  </main>

  <script>
    let categories = [];

    // Load categories for dropdown
    fetch('../php/admin/categories_crud.php')
      .then(r => r.json())
      .then(data => {
        categories = data;
        const sel = document.getElementById('f-category');
        sel.innerHTML = data.map(c => `<option value="${c.category_id}">${c.name}</option>`).join('');
      });

    // Load products
    function loadProducts() {
      fetch('../php/admin/products_crud.php')
        .then(r => r.json())
        .then(data => {
          const tbody = document.getElementById('products-tbody');
          if (data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;">No products found</td></tr>';
            return;
          }
          tbody.innerHTML = data.map(p => `<tr>
            <td>${p.product_id}</td>
            <td>${p.name}</td>
            <td>${p.category_name}</td>
            <td>$${parseFloat(p.price).toFixed(2)}</td>
            <td>${p.stock_qty}</td>
            <td>
              <button class="btn btn-secondary" onclick='editProduct(${JSON.stringify(p)})' style="padding:4px 10px;font-size:13px;"><i class="fa-solid fa-pen"></i></button>
              <button class="btn" onclick="deleteProduct(${p.product_id})" style="padding:4px 10px;font-size:13px;color:#ef4444;"><i class="fa-solid fa-trash"></i></button>
            </td>
          </tr>`).join('');
        });
    }
    loadProducts();

    function openModal(title = 'Add Product') {
      document.getElementById('modal').style.display = 'flex';
      document.getElementById('modal-title').textContent = title;
      if (title === 'Add Product') document.getElementById('product-form').reset();
      document.getElementById('f-id').value = '';
    }

    function closeModal() {
      document.getElementById('modal').style.display = 'none';
    }

    function editProduct(p) {
      openModal('Edit Product');
      document.getElementById('f-id').value = p.product_id;
      document.getElementById('f-name').value = p.name;
      document.getElementById('f-category').value = p.category_id;
      document.getElementById('f-desc').value = p.description || '';
      document.getElementById('f-price').value = p.price;
      document.getElementById('f-stock').value = p.stock_qty;
      document.getElementById('f-img').value = p.image_url || '';
    }

    function saveProduct(e) {
      e.preventDefault();
      const id = document.getElementById('f-id').value;
      const formData = new FormData();
      formData.append('action', id ? 'edit' : 'add');
      if (id) formData.append('product_id', id);
      formData.append('name', document.getElementById('f-name').value);
      formData.append('category_id', document.getElementById('f-category').value);
      formData.append('description', document.getElementById('f-desc').value);
      formData.append('price', document.getElementById('f-price').value);
      formData.append('stock_qty', document.getElementById('f-stock').value);
      formData.append('image_url', document.getElementById('f-img').value);

      fetch('../php/admin/products_crud.php', { method: 'POST', body: formData })
        .then(r => r.json())
        .then(data => {
          if (data.success) {
            closeModal();
            loadProducts();
          } else {
            alert(data.error || 'Failed to save');
          }
        });
    }

    function deleteProduct(id) {
      if (!confirm('Delete this product?')) return;
      const fd = new FormData();
      fd.append('action', 'delete');
      fd.append('product_id', id);
      fetch('../php/admin/products_crud.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
          if (data.success) loadProducts();
          else alert(data.error || 'Delete failed');
        });
    }
  </script>

</body>
</html>
