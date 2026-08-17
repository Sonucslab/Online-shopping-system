// online shoping/js/cart.js

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
    alert(title + ' added to cart!'); // Simple alert instead of animation
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
            <div class="text-center" style="padding: 2rem;">
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
            <div class="flex items-center justify-between" style="border-bottom: 1px solid var(--border-color); padding-bottom: 1rem; margin-bottom: 1rem;">
                <div class="flex items-center gap-md">
                    <div>
                        <h4 style="margin-bottom: 0.25rem;">${item.title}</h4>
                        <div style="font-weight: 600;">$${item.price.toFixed(2)}</div>
                    </div>
                </div>
                <div class="flex items-center gap-md">
                    <div class="flex items-center gap-sm" style="border: 1px solid var(--border-color); padding: 0.25rem;">
                        <button onclick="updateQuantity(${item.id}, ${item.quantity - 1})" class="btn btn-secondary" style="padding: 0 0.5rem; border:none;">-</button>
                        <span style="width: 20px; text-align: center;">${item.quantity}</span>
                        <button onclick="updateQuantity(${item.id}, ${item.quantity + 1})" class="btn btn-secondary" style="padding: 0 0.5rem; border:none;">+</button>
                    </div>
                    <button onclick="removeFromCart(${item.id})" class="btn" style="color: var(--error-color);">Remove</button>
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
        list.innerHTML = '<p class="text-muted">No items in order.</p>';
    } else {
        let html = '';
        cart.forEach(item => {
            html += `
                <div class="flex justify-between items-center text-sm" style="margin-bottom: 0.5rem;">
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
