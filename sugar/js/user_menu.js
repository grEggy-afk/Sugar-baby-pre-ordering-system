// ============================================
// 1. GLOBAL VARIABLES & STATE
// ============================================
let menuItems = [];
let addOnsList = [];
let notifications = [];
let cart = [];
let activeCategory = "All";
let currentSearch = "";
let currentSort = "";
let currentEditId = null;

const DEFAULT_PRICES = {
  Regular: 25,
  Large: 35,
  XLarge: 45,
  Jumbo: 60
};

const CART_STORAGE_KEY = "sugar_baby_cart";

// ============================================
// 2. DOM ELEMENT REFERENCES
// ============================================
const productsGrid = document.getElementById("productsGrid");
const categoryTabsContainer = document.getElementById("categoryTabs");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");
const totalFlavorsCount = document.getElementById("totalFlavorsCount");
const cartBadge = document.getElementById("cartBadge"); 

const editModalOverlay = document.getElementById("editModalOverlay");
const closeModalBtn = document.getElementById("closeModalBtn");

const userTrigger = document.getElementById("userTrigger");
const userPopup = document.getElementById("userPopup");
const notifBtn = document.getElementById("notifBtn");
const notifDropdown = document.getElementById("notifDropdown");
const darkModeToggles = document.querySelectorAll(".darkModeToggle");

// ============================================
// 3. INITIALIZATION
// ============================================
document.addEventListener("DOMContentLoaded", () => {
  initEventListeners();
  fetchProducts();
  renderNotifications();
  loadCart(); 
  updateCartBadge();
});

// ============================================
// 4. FETCH & PARSE DATA
// ============================================
async function fetchProducts() {
  try {
    const response = await fetch("/sugar/admin_page/products.json");
    if (!response.ok) throw new Error(`HTTP ${response.status}: Unable to locate products.json`);
    
    const rawData = await response.json();
    parseProductsData(rawData);
    initCategoryTabs();
    renderProducts();
    updateDashboardCount();
  } catch (error) {
    console.error("Error loading products.json:", error);
    showErrorState(error.message);
  }
}

function parseProductsData(data) {
  menuItems = [];
  addOnsList = [];

  if (data && Array.isArray(data.add_ons)) {
    addOnsList = data.add_ons.map(addon => ({
      name: addon.name,
      price: parseFloat(addon.price) || 10
    }));
  }

  let idCounter = 1;
  if (data && typeof data === "object" && Array.isArray(data.categories)) {
    data.categories.forEach(cat => {
      const catName = cat.name || "General";

      if (Array.isArray(cat.items)) {
        cat.items.forEach(itemName => {
          let itemPrices = DEFAULT_PRICES;
          let basePrice = 25;

          // Special price for HOT DRINKS (Standard size only)
          if (catName === "HOT DRINKS") {
            itemPrices = { "Standard": 25 };
            basePrice = 25;
          } else {
            itemPrices = DEFAULT_PRICES;
            basePrice = DEFAULT_PRICES.Regular;
          }

          menuItems.push({
            id: idCounter++,
            name: itemName,
            category: catName,
            prices: itemPrices,
            basePrice: basePrice,
            icon: getCategoryIcon(catName)
          });
        });
      }
    });
  }
}

function getCategoryIcon(category) {
  if (!category) return "fa-glass-water";
  const cat = category.toUpperCase();
  if (cat.includes("COFFEE")) return "fa-mug-saucer";
  if (cat.includes("TEA")) return "fa-glass-water";
  if (cat.includes("HOT")) return "fa-mug-hot";
  if (cat.includes("SNACK")) return "fa-cookie-bite";
  if (cat.includes("MANGO") || cat.includes("AVO")) return "fa-ice-cream";
  if (cat.includes("CHEESECAKE")) return "fa-blender";
  return "fa-glass-water";
}

function initCategoryTabs() {
  if (!categoryTabsContainer) return;
  if (menuItems.length === 0) {
    categoryTabsContainer.innerHTML = `<p style="color: var(--text-muted);">No categories available</p>`;
    return;
  }

  const categories = ["All", ...new Set(menuItems.map(item => item.category))];
  categoryTabsContainer.innerHTML = categories.map(cat => `
    <button class="btn-toggle category-btn ${cat === activeCategory ? 'active-cat' : ''}" data-category="${cat}" style="${cat === activeCategory ? 'background: var(--pastel-pink-dark); color: white;' : 'background: var(--bg-card); color: var(--text-main); border: 2px solid var(--border);'}">
      ${cat}
    </button>
  `).join('');

  categoryTabsContainer.querySelectorAll(".category-btn").forEach(btn => {
    btn.addEventListener("click", () => {
      activeCategory = btn.getAttribute("data-category");
      initCategoryTabs();
      renderProducts();
    });
  });
}

function renderProducts() {
  if (!productsGrid) return;

  let filtered = menuItems.filter(item => {
    const matchesCategory = activeCategory === "All" || item.category === activeCategory;
    const matchesSearch = item.name.toLowerCase().includes(currentSearch.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  if (currentSort === "price-low") filtered.sort((a, b) => a.basePrice - b.basePrice);
  else if (currentSort === "price-high") filtered.sort((a, b) => b.basePrice - a.basePrice);
  else if (currentSort === "name-az") filtered.sort((a, b) => a.name.localeCompare(b.name));
  else if (currentSort === "name-za") filtered.sort((a, b) => b.name.localeCompare(a.name));

  if (filtered.length === 0) {
    productsGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 2rem;">No menu items found.</p>`;
    return;
  }

  productsGrid.innerHTML = filtered.map(item => `
    <div class="product-card">
      <div class="badge">${item.category}</div>
      <div class="product-image-container">
        <i class="fa-solid ${item.icon}"></i>
      </div>
      <div>
        <div class="product-title">${item.name}</div>
        <div class="product-price" style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-top: 4px;">
          ₱${item.basePrice}
        </div>
      </div>
      
      <div style="display: flex; gap: 8px; margin-top: 1rem;">
        <button class="btn-toggle add-to-cart-btn" data-id="${item.id}" type="button" style="flex: 1; justify-content: center; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border); cursor: pointer; pointer-events: auto; position: relative; z-index: 10;">
          <i class="fa-solid fa-cart-plus"></i> Cart
        </button>
        <button class="btn-toggle order-btn" data-id="${item.id}" type="button" style="flex: 1; justify-content: center; cursor: pointer; pointer-events: auto; position: relative; z-index: 10;">
          <i class="fa-solid fa-mug-saucer"></i> Check Out
        </button>
      </div>
    </div>
  `).join('');
}

// ============================================
// 5. CART LOGIC (LOAD, SAVE, UPDATE, REMOVE)
// ============================================
function loadCart() {
  try {
    const savedCart = localStorage.getItem(CART_STORAGE_KEY);
    if (savedCart) {
      const parsed = JSON.parse(savedCart);
      cart = Array.isArray(parsed) ? parsed : [];
      return;
    }
  } catch (err) { console.error('Load error:', err); }
  cart = [];
}

function saveCart() {
  try { localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cart)); } 
  catch (err) { console.error('Save error:', err); }
}

function getCartItemUnitPrice(item) {
  const basePrice = Number(item.basePrice) || 0;
  const addOnPrice = Array.isArray(item.addOns)
    ? item.addOns.reduce((s, a) => s + (Number(a.price) || 0), 0) : 0;
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

function updateCartBadge() {
  if (cartBadge) {
    const totalItems = getCartQuantity();
    cartBadge.textContent = totalItems;
    cartBadge.classList.toggle("hidden", totalItems === 0);
  }
}

function handleQuickAddToCart(productId) {
  const product = menuItems.find(p => String(p.id) === String(productId));
  if (!product) return;

  const selectedSize = "Large";
  const basePrice = product.prices[selectedSize] || product.basePrice;

  const newItem = {
    id: `cart-${Date.now()}`,
    productId: product.id,
    name: product.name,
    category: product.category,
    size: selectedSize,
    basePrice: basePrice,
    addOns: [],
    quantity: 1
  };

  cart.push(newItem);
  saveCart();
  updateCartBadge();

  // Show ADDED TO CART MODAL
  let successModal = document.getElementById("cartSuccessModalOverlay");
  if (!successModal) {
    successModal = document.createElement("div");
    successModal.id = "cartSuccessModalOverlay";
    successModal.style.cssText = `
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);
      display: flex; align-items: center; justify-content: center; z-index: 1100;
    `;
    document.body.appendChild(successModal);
  }

  successModal.innerHTML = `
    <div style="background: var(--bg-card); padding: 2.5rem 2rem; border-radius: 20px; width: 90%; max-width: 340px; text-align: center; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
      <h3 style="color: var(--text-main); font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; letter-spacing: 0.5px;">ADDED TO CART</h3>
      <button type="button" id="cartSuccessOkBtn" class="btn-toggle" style="width: 100%; padding: 12px; justify-content: center; background: var(--pastel-pink-dark); color: white; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; font-size: 1rem;">OK</button>
    </div>
  `;

  successModal.classList.remove("hidden");

  document.getElementById("cartSuccessOkBtn").onclick = () => {
    successModal.classList.add("hidden");
  };
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

// --- RENDER CART PAGE ---
function renderCart() {
  const container = document.getElementById("cartItemsContainer");
  const quantityLabel = document.getElementById("summaryQuantity");
  const itemsPrice = document.getElementById("summaryItemsPrice");
  const subtotal = document.getElementById("summarySubtotal");
  const totalEl = document.getElementById("summaryTotal");
  const checkoutBtn = document.getElementById("checkoutBtn");
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
        <button type="button" id="emptyCartShopBtn" onclick="window.location.href='?page=menu'">Browse Menu</button>
      </div>
    `;
    return;
  }

  container.innerHTML = cart.map(item => {
    const unitPrice = getCartItemUnitPrice(item);
    const totalPrice = getCartItemTotal(item);
    const addons = Array.isArray(item.addOns) ? item.addOns.map(a => 
      `<span class="cart-addon">${escapeHtml(a.name)} (+₱${Number(a.price).toFixed(2)})</span>`
    ).join('') : '';

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

function escapeHtml(value) {
  return String(value ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// --- EDIT MODAL LOGIC ---
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
  if (modal) modal.classList.remove('hidden');
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
  const qty = Number(elQty?.textContent || 1); item.quantity = Math.max(1, qty);

  const addonPrices = { 'Boba': 15, 'Extra Pearl': 15, 'Cream Cheese': 20, 'Extra Caramel': 20, 'Popping Boba': 20 };
  const selected = [];
  if (elBoba && elBoba.checked) selected.push('Boba');
  if (elPearl && elPearl.checked) selected.push('Extra Pearl');
  if (elCheese && elCheese.checked) selected.push('Cream Cheese');
  if (elCaramel && elCaramel.checked) selected.push('Extra Caramel');
  if (elPopping && elPopping.checked) selected.push('Popping Boba');
  item.addOns = selected.map(n => ({ name: n, price: addonPrices[n] || 0 }));

  saveCart(); renderCart(); updateCartBadge(); closeEditModal();
  notify('Item Updated', `${item.name} has been modified.`);
}

function closeEditModal() {
  const modal = document.getElementById('editModal');
  if (modal) modal.classList.add('hidden');
  currentEditId = null;
}

// ============================================
// 6. NOTIFICATIONS & TOASTS
// ============================================
function notify(title, message) {
  notifications.unshift({ title, message });
  renderNotifications();
  showToast(title, message);
}

function showToast(title, message) {
  let toastContainer = document.getElementById("toastContainer");
  if (!toastContainer) {
    toastContainer = document.createElement("div");
    toastContainer.id = "toastContainer";
    toastContainer.style.cssText = `
      position: fixed; bottom: 20px; right: 20px; z-index: 9999;
      display: flex; flex-direction: column; gap: 10px; pointer-events: none;
    `;
    document.body.appendChild(toastContainer);
  }

  const toast = document.createElement("div");
  toast.style.cssText = `
    background: var(--bg-card); color: var(--text-main); border: 1px solid var(--border);
    padding: 12px 16px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    font-size: 0.85rem; max-width: 300px; pointer-events: auto; animation: slideIn 0.3s ease;
  `;
  toast.innerHTML = `<strong>${title}</strong><p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.75rem;">${message}</p>`;
  
  toastContainer.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = "0";
    toast.style.transition = "opacity 0.3s ease";
    setTimeout(() => toast.remove(), 300);
  }, 3500);
}

function renderNotifications() {
  const notifListContainer = document.getElementById("notifListContainer");
  const bellBadge = document.getElementById("bellBadge");

  if (notifications.length > 0 && bellBadge) {
    bellBadge.textContent = notifications.length;
    bellBadge.classList.remove("hidden");
  } else if (bellBadge) {
    bellBadge.classList.add("hidden");
  }

  if (!notifListContainer) return;

  if (notifications.length === 0) {
    notifListContainer.innerHTML = `<div class="notif-item" style="padding: 0.5rem 0; font-size: 0.8rem; color: var(--text-muted);">No notifications to show</div>`;
    return;
  }

  notifListContainer.innerHTML = notifications.map(n => `
    <div style="padding: 0.5rem 0; border-bottom: 1px solid var(--border); font-size: 0.85rem;">
      <strong>${n.title}</strong>
      <p style="color: var(--text-muted); font-size: 0.75rem;">${n.message}</p>
    </div>
  `).join('');
}

// ============================================
// 7. ORDER MODAL (Check Out) - REWRITTEN WITHOUT ORDER FORM
// ============================================
function generatePickupTimeOptions() {
  let options = '';
  let startHour = 8;
  let startMinute = 0;
  let endHour = 19;
  let endMinute = 30;

  let currentTotalMinutes = startHour * 60 + startMinute;
  let endTotalMinutes = endHour * 60 + endMinute;

  while (currentTotalMinutes <= endTotalMinutes) {
    let h = Math.floor(currentTotalMinutes / 60);
    let m = currentTotalMinutes % 60;
    let period = h >= 12 ? 'PM' : 'AM';
    let displayHour = h % 12;
    displayHour = displayHour ? displayHour : 12;
    let displayMinute = m < 10 ? '0' + m : m;
    
    let timeString = `${displayHour}:${displayMinute} ${period}`;
    options += `<option value="${timeString}">${timeString}</option>`;

    currentTotalMinutes += 30;
  }
  return options;
}

function openOrderModal(productId) {
  const product = menuItems.find(p => String(p.id) === String(productId));
  if (!product) return;

  // Create the modal HTML directly (no need for orderForm)
  let modal = document.getElementById("editModalOverlay");
  if (!modal) {
    modal = document.createElement("div");
    modal.id = "editModalOverlay";
    modal.style.cssText = `
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);
      display: flex; align-items: center; justify-content: center; z-index: 1000;
    `;
    document.body.appendChild(modal);
  }

  // Store product ID in the modal itself
  modal.dataset.productId = product.id;

  // Generate Add-Ons HTML
  const addOnsHtml = addOnsList.length > 0 ? addOnsList.map(a => `
    <label style="font-size: 0.85rem; display: flex; align-items: center; gap: 6px; background: var(--bg-main); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; color: var(--text-main);">
      <input type="checkbox" class="modalAddOn" value="${a.name}" data-price="${a.price}">
      <span>${a.name} (+₱${a.price})</span>
    </label>
  `).join('') : '<p style="color: var(--text-muted); font-size: 0.8rem;">No add-ons available</p>';

  // Build the modal HTML
  modal.innerHTML = `
    <div style="background: var(--bg-card); padding: 2rem; border-radius: 24px; width: 90%; max-width: 440px; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto; text-align: center;">
      <!-- Header Icon -->
      <div style="width: 60px; height: 60px; background: var(--pastel-pink); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 0.5rem;">
        <i class="fa-solid ${product.icon}" style="font-size: 1.8rem; color: #2c3e50;"></i>
      </div>
      <span class="badge" style="display: inline-block; margin-bottom: 0.5rem;">${product.category}</span>
      <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.25rem;">${product.name}</h2>

      <!-- Size -->
      <div style="text-align: left; margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Select Size</label>
        <select id="modalSize" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600; outline: none;">
          ${Object.keys(product.prices).map(size => `
            <option value="${size}" data-price="${product.prices[size]}">${size} - ₱${product.prices[size]}.00</option>
          `).join('')}
        </select>
      </div>

      <!-- Quantity -->
      <div style="text-align: left; margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Quantity</label>
        <input type="number" id="modalQuantity" value="1" min="1" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600; outline: none;">
      </div>

      <!-- Sweetness Level -->
      <div style="text-align: left; margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Sweetness Level</label>
        <select id="modalSugar" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600; outline: none;">
          <option value="100%">100%</option>
          <option value="75%">75%</option>
          <option value="50%">50%</option>
          <option value="20%">20%</option>
        </select>
      </div>

      <!-- Add-Ons -->
      <div style="text-align: left; margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Add-Ons</label>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
          ${addOnsHtml}
        </div>
      </div>

      <!-- Pick-Up Time -->
      <div style="text-align: left; margin-bottom: 1.5rem;">
        <label style="display: block; font-size: 0.85rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.5rem;">Pick-Up Time</label>
        <select id="modalPickupTime" style="width: 100%; padding: 0.6rem 0.8rem; border-radius: 10px; border: 1px solid var(--border); background: var(--bg-main); color: var(--text-main); font-weight: 600; outline: none;">
          ${generatePickupTimeOptions()}
        </select>
      </div>

      <!-- Total Price & Buttons -->
      <div style="border-top: 1px solid var(--border); padding-top: 1rem; margin-top: 0.5rem;">
        <div style="font-weight: 700; font-size: 1.1rem; color: var(--pastel-pink-dark); margin-bottom: 1rem; text-align: right;" id="modalTotalPriceDisplay">Total: ₱${product.basePrice}.00</div>
        <div style="display: flex; gap: 0.75rem; justify-content: flex-end;">
          <button onclick="closeOrderModal()" style="padding: 0.6rem 1.2rem; border-radius: 10px; border: 1px solid var(--border); background: transparent; color: var(--text-main); font-weight: 600; cursor: pointer;">Cancel</button>
          <button onclick="confirmOrderFromModal()" style="padding: 0.6rem 1.4rem; border-radius: 10px; border: none; background: var(--pastel-pink-dark); color: white; font-weight: 700; cursor: pointer;">Check Out</button>
        </div>
      </div>
    </div>
  `;

  modal.classList.remove("hidden");
  updateModalTotalPrice(product);
}

function closeOrderModal() {
  const modal = document.getElementById("editModalOverlay");
  if (modal) modal.classList.add("hidden");
}

function updateModalTotalPrice(product) {
  const sizeSelect = document.getElementById("modalSize");
  const qtyInput = document.getElementById("modalQuantity");
  const priceDisplay = document.getElementById("modalTotalPriceDisplay");

  if (!sizeSelect || !qtyInput || !priceDisplay) return;

  const selectedSize = sizeSelect.value;
  const basePrice = parseFloat(sizeSelect.options[sizeSelect.selectedIndex].dataset.price) || 25;
  const quantity = parseInt(qtyInput.value) || 1;

  let totalAddonCost = 0;
  document.querySelectorAll('.modalAddOn:checked').forEach(cb => {
    totalAddonCost += parseFloat(cb.dataset.price) || 10;
  });

  const grandTotal = (basePrice + totalAddonCost) * quantity;
  priceDisplay.textContent = `Total: ₱${grandTotal.toFixed(2)}`;
}

function confirmOrderFromModal() {
  // Get the current product ID from the modal's data attribute
  const productId = document.getElementById("editModalOverlay").dataset.productId;
  const product = menuItems.find(p => String(p.id) === String(productId));
  if (!product) return;

  const sizeSelect = document.getElementById("modalSize");
  const qtyInput = document.getElementById("modalQuantity");
  const sugarSelect = document.getElementById("modalSugar");
  const pickupSelect = document.getElementById("modalPickupTime");

  const selectedSize = sizeSelect ? sizeSelect.value : 'Large';
  const basePrice = parseFloat(sizeSelect.options[sizeSelect.selectedIndex].dataset.price) || 25;
  const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;
  const selectedSugar = sugarSelect ? sugarSelect.value : '100%';
  const selectedPickupTime = pickupSelect ? pickupSelect.value : '8:00 AM';

  const selectedAddOns = Array.from(document.querySelectorAll('.modalAddOn:checked'))
    .map(cb => ({ name: cb.value, price: parseFloat(cb.dataset.price) || 10 }));
  const totalAddonCost = selectedAddOns.reduce((sum, item) => sum + item.price, 0);
  const finalTotalPrice = (basePrice + totalAddonCost) * qty;

  const singleItem = {
    id: Date.now(),
    productId: product.id,
    name: product.name,
    category: product.category,
    size: selectedSize,
    sugarLevel: selectedSugar,
    basePrice: basePrice,
    addOns: selectedAddOns,
    quantity: qty,
    pickupTime: selectedPickupTime,
    totalPrice: finalTotalPrice
  };

  closeOrderModal();

  // Show QR Payment Modal
  const refNo = `#SB-${Math.floor(1000 + Math.random() * 9000)}`;
  showQRPaymentModal(refNo, finalTotalPrice, singleItem);
}

function showQRPaymentModal(refNo, totalAmount, singleItem) {
  let qrModal = document.getElementById("qrPaymentModalOverlay");
  
  if (!qrModal) {
    qrModal = document.createElement("div");
    qrModal.id = "qrPaymentModalOverlay";
    qrModal.style.cssText = `
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);
      display: flex; align-items: center; justify-content: center; z-index: 1000;
    `;
    document.body.appendChild(qrModal);
  }

  qrModal.innerHTML = `
    <div style="background: var(--bg-card); padding: 2rem; border-radius: 20px; width: 90%; max-width: 440px; text-align: center; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.15); max-height: 90vh; overflow-y: auto;">
      <h3 style="color: var(--text-main); font-size: 1.25rem; font-weight: 700; margin-bottom: 0.2rem;">Complete Payment</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">Reference Code: <strong style="color: var(--pastel-pink-dark);">${refNo}</strong></p>
      
      <div style="background: var(--bg-main); padding: 1.25rem; display: inline-block; border-radius: 16px; border: 1px solid var(--border); margin-bottom: 1rem;">
        <div style="width: 130px; height: 130px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: white; color: #2d3748; font-weight: bold; font-size: 0.8rem; border-radius: 12px;">
          <i class="fa-solid fa-qrcode" style="font-size: 3rem; margin-bottom: 6px; color: var(--pastel-pink-dark);"></i>
          <span style="font-size: 0.7rem; color: #718096;">GCASH / QR</span>
        </div>
      </div>

      <p style="font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-bottom: 1rem;">Account Name: Marvin Bayan</p>

      <div style="text-align: left; margin-bottom: 1rem;">
        <label style="display: block; font-size: 0.8rem; font-weight: 700; text-transform: uppercase; margin-bottom: 0.4rem; color: var(--text-muted);">Upload Payment Proof</label>
        <label style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 16px; border: 2px dashed var(--border); border-radius: 12px; background: var(--bg-main); cursor: pointer;">
          <i class="fa-solid fa-cloud-arrow-up" style="font-size: 1.5rem; color: var(--pastel-pink-dark); margin-bottom: 6px;"></i>
          <span id="uploadFileName" style="font-size: 0.85rem; color: var(--text-main); font-weight: 600;">Click to upload receipt image</span>
          <input type="file" id="paymentProofInput" accept="image/*" style="display: none;">
        </label>
      </div>

      <div style="background: var(--bg-main); padding: 12px 16px; border-radius: 12px; border: 1px solid var(--border); margin-bottom: 1.25rem; text-align: left; font-size: 0.85rem;">
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: var(--text-muted);">
          <span>Sweetness:</span> <strong style="color: var(--text-main);">${singleItem.sugarLevel || '100%'}</strong>
        </div>
        <div style="display: flex; justify-content: space-between; margin-bottom: 4px; color: var(--text-muted);">
          <span>Pick-up Time:</span> <strong style="color: var(--text-main);">${singleItem.pickupTime}</strong>
        </div>
        <div style="display: flex; justify-content: space-between; border-top: 1px solid var(--border); padding-top: 6px; margin-top: 6px;">
          <span style="font-weight: 700; color: var(--text-main);">Total Due:</span> 
          <span style="font-weight: 700; color: var(--pastel-pink-dark); font-size: 1.05rem;">₱${totalAmount.toFixed(2)}</span>
        </div>
      </div>
      
      <div style="display: flex; gap: 12px;">
        <button type="button" id="closeQrModalBtn" class="btn-toggle" style="flex: 1; padding: 12px; justify-content: center; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border); border-radius: 10px; font-weight: 600; cursor: pointer;">Cancel</button>
        <button type="button" id="simulatePaymentBtn" class="btn-toggle" style="flex: 1; padding: 12px; justify-content: center; background: var(--pastel-pink-dark); color: white; border-radius: 10px; font-weight: 600; border: none; cursor: pointer;">Confirm Order</button>
      </div>
    </div>
  `;

  qrModal.classList.remove("hidden");

  const fileInput = document.getElementById("paymentProofInput");
  const fileNameSpan = document.getElementById("uploadFileName");
  if (fileInput && fileNameSpan) {
    fileInput.onchange = (e) => {
      if (e.target.files.length > 0) {
        fileNameSpan.textContent = e.target.files[0].name;
        fileNameSpan.style.color = "var(--pastel-pink-dark)";
      }
    };
  }

  document.getElementById("closeQrModalBtn").onclick = () => {
    qrModal.classList.add("hidden");
  };

  // ============================================
  // ✅ FINAL FIX: SEND ORDER TO DATABASE VIA API
  // ============================================
  document.getElementById("simulatePaymentBtn").onclick = () => {
    // Prepare the data to send
    const formData = new FormData();
    formData.append('product_name', singleItem.name);
    formData.append('price', singleItem.totalPrice);
    formData.append('quantity', singleItem.quantity);
    formData.append('payment_method', 'GCash');

    // Send to PHP backend
    fetch('/sugar/api/place_order_api.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        qrModal.classList.add("hidden");

        if (data.ok) {
            // Show success modal after saving to DB
            let successModal = document.getElementById("orderSuccessModalOverlay");
            if (!successModal) {
                successModal = document.createElement("div");
                successModal.id = "orderSuccessModalOverlay";
                successModal.style.cssText = `
                    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                    background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px);
                    display: flex; align-items: center; justify-content: center; z-index: 1100;
                `;
                document.body.appendChild(successModal);
            }

            successModal.innerHTML = `
                <div style="background: var(--bg-card); padding: 2.5rem 2rem; border-radius: 20px; width: 90%; max-width: 340px; text-align: center; border: 1px solid var(--border); box-shadow: 0 20px 40px rgba(0,0,0,0.15);">
                    <h3 style="color: var(--text-main); font-size: 1.4rem; font-weight: 800; margin-bottom: 1.5rem; letter-spacing: 0.5px;">ORDER PLACED!</h3>
                    <button type="button" id="successOkBtn" class="btn-toggle" style="width: 100%; padding: 12px; justify-content: center; background: var(--pastel-pink-dark); color: white; border-radius: 10px; font-weight: 700; border: none; cursor: pointer; font-size: 1rem;">OK</button>
                </div>
            `;

            successModal.classList.remove("hidden");

            notify("Order Placed Successfully! 🎉", `${singleItem.name} (${singleItem.size}) is set for pick-up at ${singleItem.pickupTime}.`);

            document.getElementById("successOkBtn").onclick = () => {
                successModal.classList.add("hidden");
                // Refresh the page to show the new order on the Orders page
                location.reload();
            };
        } else {
            alert('Failed to place order: ' + data.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Network error while placing order.');
    });
  };
}

// ============================================
// 8. EVENT LISTENERS
// ============================================
function initEventListeners() {
  if (productsGrid) {
    productsGrid.addEventListener("click", (e) => {
      const orderBtn = e.target.closest(".order-btn");
      const addToCartBtn = e.target.closest(".add-to-cart-btn");

      if (orderBtn) {
        e.preventDefault();
        e.stopPropagation();
        const productId = orderBtn.getAttribute("data-id");
        openOrderModal(productId);
      }

      if (addToCartBtn) {
        e.preventDefault();
        e.stopPropagation();
        const productId = addToCartBtn.getAttribute("data-id");
        handleQuickAddToCart(productId);
      }
    });
  }

  if (searchInput) searchInput.addEventListener("input", (e) => { currentSearch = e.target.value; renderProducts(); });
  if (sortSelect) sortSelect.addEventListener("change", (e) => { currentSort = e.target.value; renderProducts(); });
  if (closeModalBtn) closeModalBtn.addEventListener("click", closeOrderModal);
  
  if (editModalOverlay) {
    editModalOverlay.addEventListener("click", (e) => {
      if (e.target === editModalOverlay) closeOrderModal();
    });
  }

  if (userTrigger && userPopup) {
    userTrigger.style.pointerEvents = "auto";
    userTrigger.style.opacity = "1";
    userTrigger.style.cursor = "pointer";
    userTrigger.addEventListener("click", (e) => {
      e.stopPropagation();
      userPopup.classList.toggle("hidden");
      if (notifDropdown) notifDropdown.classList.add("hidden");
    });
  }

  if (notifBtn && notifDropdown) {
    notifBtn.style.pointerEvents = "auto";
    notifBtn.style.opacity = "1";
    notifBtn.style.cursor = "pointer";
    notifBtn.addEventListener("click", (e) => {
      e.stopPropagation();
      notifDropdown.classList.toggle("hidden");
      if (userPopup) userPopup.classList.add("hidden");
    });
  }

  document.addEventListener("click", () => {
    if (userPopup) userPopup.classList.add("hidden");
    if (notifDropdown) notifDropdown.classList.add("hidden");
  });

  darkModeToggles.forEach(toggle => {
    toggle.disabled = false;
    if (toggle.parentElement) toggle.parentElement.style.opacity = "1";
    toggle.addEventListener("change", (e) => {
      const isChecked = e.target.checked;
      document.body.classList.toggle("dark-mode", isChecked);
      darkModeToggles.forEach(t => t.checked = isChecked);
    });
  });

  // Setup Cart Events
  setupCartEvents();
}

// ============================================
// 9. CART EVENTS SETUP
// ============================================
function setupCartEvents() {
  const container = document.getElementById('cartItemsContainer');
  if (container) {
    container.addEventListener('click', function(event) {
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
    checkoutBtn.addEventListener('click', function() {
      if (cart.length === 0) return notify('Cart Empty', 'Add an item before checking out.');
      const modalTotal = document.getElementById('modalTotal');
      if (modalTotal) modalTotal.textContent = `₱${getCartSubtotal().toFixed(2)}`;
      if (checkoutModal) { checkoutModal.classList.remove('hidden'); }
    });
  }

  function closeCheckoutModal() {
    if (checkoutModal) checkoutModal.classList.add('hidden');
  }
  if (closeCheckoutBtn) closeCheckoutBtn.addEventListener('click', closeCheckoutModal);
  if (cancelCheckoutBtn) cancelCheckoutBtn.addEventListener('click', closeCheckoutModal);
  if (confirmCheckoutBtn) confirmCheckoutBtn.addEventListener('click', function() {
    notify('Order Successful', 'Thank you! Your order has been placed.');
    cart = []; saveCart(); renderCart(); updateCartBadge(); closeCheckoutModal();
  });

  if (closeEditBtn) closeEditBtn.addEventListener('click', closeEditModal);
  if (cancelEditBtn) cancelEditBtn.addEventListener('click', closeEditModal);
  if (saveEditBtn) saveEditBtn.addEventListener('click', saveEditChanges);

  if (editMinus) editMinus.addEventListener('click', function() {
    const q = document.getElementById('editQuantity');
    if (q) { const current = Number(q.textContent || 1); q.textContent = Math.max(1, current - 1); }
  });
  if (editPlus) editPlus.addEventListener('click', function() {
    const q = document.getElementById('editQuantity');
    if (q) { const current = Number(q.textContent || 1); q.textContent = current + 1; }
  });
}

// ============================================
// 10. UTILITY FUNCTIONS
// ============================================
function updateDashboardCount() {
  if (totalFlavorsCount) totalFlavorsCount.textContent = menuItems.length;
}

function showErrorState(message) {
  if (!productsGrid) return;
  productsGrid.innerHTML = `
    <div style="grid-column: 1 / -1; padding: 24px; background: var(--bg-card); border: 2px dashed #e53e3e; border-radius: 12px; text-align: center;">
      <h3 style="color: #e53e3e; margin-bottom: 8px;">Failed to Load products.json</h3>
      <p style="color: var(--text-main); font-size: 14px; margin-bottom: 12px;">${message}</p>
    </div>
  `;
}