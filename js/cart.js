// js/cart.js

// Initialize cart from localStorage or empty array
let cart = JSON.parse(localStorage.getItem('nexus_cart')) || [];

function saveCart() {
    localStorage.setItem('nexus_cart', JSON.stringify(cart));
    updateCartCount();
}

function addToCart(id, title, price) {
    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ id, title, price, quantity: 1 });
    }
    saveCart();
    
    // Simple visual feedback
    const btn = event.target;
    const originalText = btn.innerText;
    btn.innerText = 'Added!';
    btn.style.backgroundColor = '#10b981'; // Green
    setTimeout(() => {
        btn.innerText = originalText;
        btn.style.backgroundColor = '';
    }, 1000);
}

function removeFromCart(id) {
    cart = cart.filter(item => item.id !== id);
    saveCart();
    renderCart(); // If on cart page
    renderCheckout(); // If on checkout page
}

function updateQuantity(id, newQuantity) {
    if (newQuantity < 1) return;
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity = newQuantity;
        saveCart();
        renderCart();
        renderCheckout();
    }
}

function updateCartCount() {
    const counts = document.querySelectorAll('#cart-count');
    const totalCount = cart.reduce((sum, item) => sum + item.quantity, 0);
    counts.forEach(el => el.innerText = totalCount);
}

// Render logic for cart.html
function renderCart() {
    const container = document.getElementById('cart-items-container');
    if (!container) return; // Not on cart page

    if (cart.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; color: var(--text-muted); padding: var(--spacing-lg);">
              <i class="fa-solid fa-basket-shopping" style="font-size: 3rem; margin-bottom: 1rem;"></i>
              <p>Your cart is empty.</p>
              <a href="index.html#products" class="btn btn-primary" style="margin-top: 1rem;">Start Shopping</a>
            </div>
        `;
        updateTotals('cart');
        document.getElementById('checkout-btn').style.pointerEvents = 'none';
        document.getElementById('checkout-btn').style.opacity = '0.5';
        return;
    }

    document.getElementById('checkout-btn').style.pointerEvents = 'auto';
    document.getElementById('checkout-btn').style.opacity = '1';

    let html = '';
    cart.forEach(item => {
        html += `
            <div class="flex items-center justify-between" style="border-bottom: 1px solid var(--surface-border); padding-bottom: var(--spacing-md);">
                <div class="flex items-center gap-md">
                    <div style="width: 80px; height: 80px; background: rgba(0,0,0,0.2); border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                        <i class="fa-solid fa-box" style="font-size: 2rem; color: var(--text-muted);"></i>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.25rem;">${item.title}</h4>
                        <div style="color: var(--primary-color); font-weight: 600;">$${item.price.toFixed(2)}</div>
                    </div>
                </div>
                <div class="flex items-center gap-md">
                    <div class="flex items-center gap-sm" style="background: rgba(0,0,0,0.2); border-radius: 4px; padding: 0.25rem;">
                        <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" style="background:none; border:none; color:var(--text-main); cursor:pointer; padding: 0 0.5rem;"><i class="fa-solid fa-minus"></i></button>
                        <span style="width: 20px; text-align: center;">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" style="background:none; border:none; color:var(--text-main); cursor:pointer; padding: 0 0.5rem;"><i class="fa-solid fa-plus"></i></button>
                    </div>
                    <button onclick="removeFromCart(${item.id})" style="background:none; border:none; color: var(--accent-color); cursor:pointer; padding: 0.5rem;"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
    });
    container.innerHTML = html;
    updateTotals('cart');
}

// Render logic for checkout.html summary
function renderCheckout() {
    const list = document.getElementById('checkout-items-list');
    if (!list) return; // Not on checkout page
    
    if (cart.length === 0) {
        list.innerHTML = '<p style="color: var(--text-muted);">No items in order.</p>';
    } else {
        let html = '';
        cart.forEach(item => {
            html += `
                <div class="flex justify-between items-center text-sm">
                    <span>${item.quantity}x ${item.title}</span>
                    <span>$${(item.price * item.quantity).toFixed(2)}</span>
                </div>
            `;
        });
        list.innerHTML = html;
    }
    updateTotals('checkout');
}

function updateTotals(prefix) {
    const subtotalEl = document.getElementById(`${prefix}-subtotal`);
    const taxEl = document.getElementById(`${prefix}-tax`);
    const totalEl = document.getElementById(`${prefix}-total`);
    
    if (!subtotalEl || !taxEl || !totalEl) return;

    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.quantity), 0);
    const tax = subtotal * 0.08;
    const total = subtotal + tax;

    subtotalEl.innerText = `$${subtotal.toFixed(2)}`;
    taxEl.innerText = `$${tax.toFixed(2)}`;
    totalEl.innerText = `$${total.toFixed(2)}`;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();
    renderCart();
    renderCheckout();
});
