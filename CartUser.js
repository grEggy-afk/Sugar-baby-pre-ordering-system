const CART_STORAGE_KEY = "sweetbrew_cart";

const SAMPLE_CART = [
  {
    id: "sample-1",
    productId: "milk-tea-1",
    category: "Milk Tea",
    name: "Classic Milk Tea",
    size: "Large",
    basePrice: 145,
    addOns: [
      { name: "Boba", price: 15 },
      { name: "Extra Pearl", price: 15 }
    ],
    quantity: 1
  },
  {
    id: "sample-2",
    productId: "coffee-1",
    category: "Coffee",
    name: "Iced Caramel Macchiato",
    size: "Medium",
    basePrice: 165,
    addOns: [
      { name: "Extra Caramel", price: 20 }
    ],
    quantity: 2
  },
  {
    id: "sample-3",
    productId: "fruit-tea-1",
    category: "Fruit Tea",
    name: "Strawberry Fruit Tea",
    size: "Large",
    basePrice: 155,
    addOns: [
      { name: "Popping Boba", price: 20 }
    ],
    quantity: 1
  }
];

let cart = [];
let currentEditId = null;

function loadCart() {
  try {
    const savedCart = localStorage.getItem(CART_STORAGE_KEY);
    if (!savedCart) {
      cart = JSON.parse(JSON.stringify(SAMPLE_CART));
      saveCart();
      return;
    }
    const parsed = JSON.parse(savedCart);
    cart = Array.isArray(parsed) ? parsed : [];
  } catch (err) {
    console.error('Unable to load cart:', err);
    cart = JSON.parse(JSON.stringify(SAMPLE_CART));
    saveCart();
  }
}

function saveCart() {
  try {
    localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart));
  } catch (err) {
    console.error('Unable to save cart:', err);
  }
}

function getCartItemUnitPrice(item) {
  const basePrice = Number(item.basePrice) || 0;
  const addOnPrice = Array.isArray(item.addOns)
    ? item.addOns.reduce((s, a) => s + (Number(a.price) || 0), 0)
    : 0;
  return basePrice + addOnPrice;
}

function getCartItemTotal(item) {
  return getCartItemUnitPrice(item) * (Number(item.quantity) || 1);
}

function getCartSubtotal() {
  return cart.reduce((t, i) => t + getCartItemTotal(i), 0);
}

function getCartQuantity() {
  return cart.reduce((t, i) => t + (Number(i.quantity) || 0), 0);
}

function getCategoryIcon(category) {
  const normalized = String(category || '').toLowerCase();
  if (normalized.includes('tea')) return 'fa-mug-saucer';
  if (normalized.includes('coffee')) return 'fa-mug-hot';
  if (normalized.includes('shake') || normalized.includes('smoothie')) return 'fa-cup-togo';
  if (normalized.includes('dessert') || normalized.includes('snack')) return 'fa-cookie-bite';
  return 'fa-mug-hot';
}

function createCartItemKey(item) {
  const addOns = Array.isArray(item.addOns) ? item.addOns.map(a => a.name).sort().join('|') : '';
  return [item.productId, item.size || 'Large', addOns].join('::');
}

function addItemToCart(item) {
  const newKey = createCartItemKey(item);
  const existing = cart.find(c => createCartItemKey(c) === newKey);
  if (existing) {
    existing.quantity = (Number(existing.quantity) || 0) + (Number(item.quantity) || 1);
  } else {
    cart.push({ ...item, id: item.id || `cart-${Date.now()}`, quantity: Number(item.quantity) || 1 });
  }
  saveCart();
  renderCart();
  updateCartBadge();
  notify('Added to Cart', `${item.name} has been added to your cart.`);
}

function updateCartQuantity(cartId, change) {
  const item = cart.find(i => String(i.id) === String(cartId));
  if (!item) return;
  item.quantity = Math.max(1, (Number(item.quantity) || 1) + change);
  saveCart();
  renderCart();
  updateCartBadge();
}

function removeCartItem(cartId) {
  const idx = cart.findIndex(i => String(i.id) === String(cartId));
  if (idx === -1) return;
  const removed = cart.splice(idx, 1)[0];
  saveCart();
  renderCart();
  updateCartBadge();
  notify('Removed from Cart', `${removed.name} was removed from your cart.`);
}

function clearCart() {
  if (cart.length === 0) return notify('Cart Empty', 'There are no items to remove.');
  if (!confirm('Are you sure you want to clear your cart?')) return;
  cart = [];
  saveCart();
  renderCart();
  updateCartBadge();
  notify('Cart Cleared', 'All items have been removed from your cart.');
}

// Modal-based edit
function editCartItem(cartId) {
  const item = cart.find(i => String(i.id) === String(cartId));
  if (!item) return;
  currentEditId = String(cartId);

  const elName = document.getElementById('editItemName');
  const elSize = document.getElementById('editSize');
  const elQty = document.getElementById('editQuantity');
  const elBoba = document.getElementById('editBoba');
  const elPearl = document.getElementById('editPearl');
  const elCheese = document.getElementById('editCheese');
  const elCaramel = document.getElementById('editCaramel');
  const elPopping = document.getElementById('editPopping');

  if (elName) elName.textContent = `Modify ${item.name}`;
  if (elSize) elSize.value = item.size || 'Large';
  if (elQty) elQty.textContent = item.quantity || 1;

  const selected = Array.isArray(item.addOns) ? item.addOns.map(a => a.name) : [];
  if (elBoba) elBoba.checked = selected.includes('Boba');
  if (elPearl) elPearl.checked = selected.includes('Extra Pearl');
  if (elCheese) elCheese.checked = selected.includes('Cream Cheese');
  if (elCaramel) elCaramel.checked = selected.includes('Extra Caramel');
  if (elPopping) elPopping.checked = selected.includes('Popping Boba');

  const modal = document.getElementById('editModal');
  if (modal) { modal.classList.remove('hidden'); modal.setAttribute('aria-hidden', 'false'); }
}

function saveEditChanges() {
  if (!currentEditId) return;
  const item = cart.find(i => String(i.id) === currentEditId);
  if (!item) return;

  const elSize = document.getElementById('editSize');
  const elQty = document.getElementById('editQuantity');
  const elBoba = document.getElementById('editBoba');
  const elPearl = document.getElementById('editPearl');
  const elCheese = document.getElementById('editCheese');
  const elCaramel = document.getElementById('editCaramel');
  const elPopping = document.getElementById('editPopping');

  if (elSize) item.size = elSize.value;
  const qty = Number(elQty?.textContent || 1);
  item.quantity = Math.max(1, qty);

  const addonPrices = { 'Boba': 15, 'Extra Pearl': 15, 'Cream Cheese': 20, 'Extra Caramel': 20, 'Popping Boba': 20 };
  const selected = [];
  if (elBoba && elBoba.checked) selected.push('Boba');
  if (elPearl && elPearl.checked) selected.push('Extra Pearl');
  if (elCheese && elCheese.checked) selected.push('Cream Cheese');
  if (elCaramel && elCaramel.checked) selected.push('Extra Caramel');
  if (elPopping && elPopping.checked) selected.push('Popping Boba');

  item.addOns = selected.map(n => ({ name: n, price: addonPrices[n] || 0 }));

  saveCart();
  renderCart();
  updateCartBadge();
  closeEditModal();
  notify('Item Updated', `${item.name} has been modified.`);
}

function closeEditModal() {
  const modal = document.getElementById('editModal');
  if (modal) { modal.classList.add('hidden'); modal.setAttribute('aria-hidden', 'true'); }
  currentEditId = null;
}

function renderCart() {
  const container = document.getElementById('cartItemsContainer');
  const quantityLabel = document.getElementById('summaryQuantity');
  const itemsPrice = document.getElementById('summaryItemsPrice');
  const subtotal = document.getElementById('summarySubtotal');
  const totalEl = document.getElementById('summaryTotal');
  const checkoutBtn = document.getElementById('checkoutBtn');
  if (!container) return;

  const totalQuantity = getCartQuantity();
  const total = getCartSubtotal();

  if (quantityLabel) quantityLabel.textContent = totalQuantity;
  if (itemsPrice) itemsPrice.textContent = `₱${total.toFixed(2)}`;
  if (subtotal) subtotal.textContent = `₱${total.toFixed(2)}`;
  if (totalEl) totalEl.textContent = `₱${total.toFixed(2)}`;
  if (checkoutBtn) checkoutBtn.disabled = cart.length === 0;

  if (cart.length === 0) {
    container.innerHTML = `
      <div class="empty-cart">
        <div class="empty-cart-icon"><i class="fa-solid fa-cart-shopping"></i></div>
        <h2>Your cart is empty</h2>
        <p>Add something delicious from the menu to get started.</p>
        <button type="button" id="emptyCartShopBtn">Browse Menu</button>
      </div>
    `;
    const shopBtn = document.getElementById('emptyCartShopBtn');
    if (shopBtn) shopBtn.addEventListener('click', () => window.location.href = 'index.html');
    return;
  }

  container.innerHTML = cart.map(item => {
    const unitPrice = getCartItemUnitPrice(item);
    const totalPrice = getCartItemTotal(item);
    const addons = Array.isArray(item.addOns) ? item.addOns.map(a => `<span class="cart-addon">${escapeHtml(a.name)} (+₱${Number(a.price).toFixed(2)})</span>`).join('') : '';

    return `
      <article class="cart-item">
        <div class="item-image"><i class="fa-solid ${getCategoryIcon(item.category)}"></i></div>
        <div class="item-info">
          <span class="item-category">${escapeHtml(item.category || 'General')}</span>
          <h3 class="item-name">${escapeHtml(item.name)}</h3>
          <div class="item-details">
            <div>Size: <strong>${escapeHtml(item.size || 'Large')}</strong></div>
            <div class="unit-price">₱${unitPrice.toFixed(2)} each</div>
            ${addons ? `<div class="addons">${addons}</div>` : ''}
          </div>
        </div>
        <div class="item-actions">
          <strong class="item-price">₱${totalPrice.toFixed(2)}</strong>
          <div class="quantity-control">
            <button type="button" class="cart-quantity-btn" data-action="decrease" data-id="${item.id}">−</button>
            <span>${item.quantity}</span>
            <button type="button" class="cart-quantity-btn" data-action="increase" data-id="${item.id}">+</button>
          </div>
          <div class="item-buttons">
            <button type="button" class="edit-btn" data-action="edit" data-id="${item.id}"><i class="fa-solid fa-pen"></i> Edit</button>
            <button type="button" class="remove-btn" data-action="remove" data-id="${item.id}"><i class="fa-solid fa-trash"></i> Remove</button>
          </div>
        </div>
      </article>
    `;
  }).join('');
}

function setupCartEvents() {
  const container = document.getElementById('cartItemsContainer');
  if (container) {
    container.addEventListener('click', function (event) {
      const button = event.target.closest('button');
      if (!button) return;
      const action = button.dataset.action;
      const id = button.dataset.id;
      if (action === 'increase') updateCartQuantity(id, 1);
      if (action === 'decrease') updateCartQuantity(id, -1);
      if (action === 'remove') removeCartItem(id);
      if (action === 'edit') editCartItem(id);
    });
  }

  const clearBtn = document.getElementById('clearCartBtn');
  if (clearBtn) clearBtn.addEventListener('click', clearCart);

  const checkoutBtn = document.getElementById('checkoutBtn');
  const checkoutModal = document.getElementById('checkoutModal');
  const closeCheckoutBtn = document.getElementById('closeCheckoutBtn');
  const cancelCheckoutBtn = document.getElementById('cancelCheckoutBtn');
  const confirmCheckoutBtn = document.getElementById('confirmCheckoutBtn');

  const editModal = document.getElementById('editModal');
  const closeEditBtn = document.getElementById('closeEditBtn');
  const saveEditBtn = document.getElementById('saveEditBtn');
  const cancelEditBtn = document.getElementById('cancelEditBtn');
  const editMinus = document.getElementById('editMinus');
  const editPlus = document.getElementById('editPlus');

  if (checkoutBtn) {
    checkoutBtn.addEventListener('click', function () {
      if (cart.length === 0) return notify('Cart Empty', 'Add an item before checking out.');
      const modalTotal = document.getElementById('modalTotal');
      if (modalTotal) modalTotal.textContent = `₱${getCartSubtotal().toFixed(2)}`;
      if (checkoutModal) { checkoutModal.classList.remove('hidden'); checkoutModal.setAttribute('aria-hidden', 'false'); }
    });
  }

  function closeModal() {
    if (!checkoutModal) return;
    checkoutModal.classList.add('hidden');
    checkoutModal.setAttribute('aria-hidden', 'true');
  }

  if (closeCheckoutBtn) closeCheckoutBtn.addEventListener('click', closeModal);
  if (cancelCheckoutBtn) cancelCheckoutBtn.addEventListener('click', closeModal);
  if (confirmCheckoutBtn) confirmCheckoutBtn.addEventListener('click', function () {
    notify('Order Successful', 'Thank you! Your order has been placed.');
    cart = [];
    saveCart();
    renderCart();
    updateCartBadge();
    closeModal();
  });
  if (checkoutModal) checkoutModal.addEventListener('click', function (ev) { if (ev.target === checkoutModal) closeModal(); });

  if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
  if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);
  if (saveEditBtn) saveEditBtn.addEventListener('click', saveEditChanges);

  if (editMinus) editMinus.addEventListener('click', function () {
    const q = document.getElementById('editQuantity'); if (!q) return; const current = Number(q.textContent || 1); q.textContent = Math.max(1, current - 1);
  });
  if (editPlus) editPlus.addEventListener('click', function () {
    const q = document.getElementById('editQuantity'); if (!q) return; const current = Number(q.textContent || 1); q.textContent = current + 1;
  });
  if (editModal) editModal.addEventListener('click', function (ev) { if (ev.target === editModal) closeEditModal(); });
}

function updateCartBadge() {
  const badges = document.querySelectorAll('.cart-count, #cartBadge');
  const quantity = getCartQuantity();
  badges.forEach(b => { b.textContent = quantity; if (quantity > 0) b.classList.remove('hidden'); else b.classList.add('hidden'); });
}

function notify(title, message) {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `<strong>${escapeHtml(title)}</strong><span>${escapeHtml(message)}</span>`;
  container.appendChild(toast);
  setTimeout(() => { toast.classList.add('toast-hide'); setTimeout(() => toast.remove(), 300); }, 2500);
}

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', function () {
  loadCart();
  renderCart();
  updateCartBadge();
  setupCartEvents();
});
