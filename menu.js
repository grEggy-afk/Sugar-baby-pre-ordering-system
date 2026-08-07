let menuItems = [];
let addOnsList = [];
let notifications = [];
let activeCategory = "All";
let currentSearch = "";
let currentSort = "";

const DEFAULT_PRICES = {
  Regular: 25,
  Large: 35,
  XLarge: 45,
  Jumbo: 60
};

const productsGrid = document.getElementById("productsGrid");
const categoryTabsContainer = document.getElementById("categoryTabs");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");
const totalFlavorsCount = document.getElementById("totalFlavorsCount");

const editModalOverlay = document.getElementById("editModalOverlay");
const orderForm = document.getElementById("orderForm");
const closeModalBtn = document.getElementById("closeModalBtn");
const orderItemName = document.getElementById("orderItemName");
const orderItemCategory = document.getElementById("orderItemCategory");
const orderItemSize = document.getElementById("orderItemSize");
const orderQuantity = document.getElementById("orderQuantity");

const userTrigger = document.getElementById("userTrigger");
const userPopup = document.getElementById("userPopup");
const notifBtn = document.getElementById("notifBtn");
const notifDropdown = document.getElementById("notifDropdown");
const darkModeToggles = document.querySelectorAll(".darkModeToggle");

document.addEventListener("DOMContentLoaded", () => {
  initNavigation();
  initEventListeners();
  fetchProducts();
  renderNotifications();
});

function initNavigation() {
  document.querySelectorAll(".nav-item").forEach(item => {
    item.style.pointerEvents = "auto";
    item.style.opacity = "1";
    item.style.cursor = "pointer";
    item.addEventListener("click", (e) => {
      e.preventDefault();
      const tabTarget = item.getAttribute("data-tab");
      if (tabTarget) switchTab(tabTarget);
    });
  });
}

function switchTab(tabId) {
  document.querySelectorAll(".nav-item").forEach(nav => nav.classList.remove("active"));
  document.querySelectorAll(".tab-content").forEach(content => {
    content.classList.remove("active");
    content.style.display = "none";
  });

  const targetNav = document.querySelector(`.nav-item[data-tab="${tabId}"]`);
  const targetContent = document.getElementById(tabId);

  if (targetNav) targetNav.classList.add("active");
  if (targetContent) {
    targetContent.classList.add("active");
    targetContent.style.display = "block";
  }
}

async function fetchProducts() {
  try {
    const response = await fetch("products.json");
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
        cat.items.forEach(item => {
          let itemName = "";
          let itemPrices = DEFAULT_PRICES;
          let basePrice = 25;

          if (typeof item === "string") {
            itemName = item;
          } else if (typeof item === "object" && item !== null) {
            itemName = item.name || "Unnamed Item";
            if (item.prices) {
              itemPrices = item.prices;
              basePrice = item.prices.Regular || item.prices.Large || 25;
            } else if (item.price) {
              basePrice = item.price;
              itemPrices = { Regular: item.price, Large: item.price, XLarge: item.price, Jumbo: item.price };
            }
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

      if (Array.isArray(cat.cream_base)) {
        cat.cream_base.forEach(item => {
          const itemPrices = {
            Regular: item.L ? item.L - 10 : 39,
            Large: item.L || 49,
            XLarge: item.XL || 59,
            Jumbo: item.XL ? item.XL + 10 : 69
          };
          menuItems.push({
            id: idCounter++,
            name: `${item.name} (Cream Base)`,
            category: catName,
            prices: itemPrices,
            basePrice: itemPrices.Large,
            icon: getCategoryIcon(catName)
          });
        });
      }

      if (Array.isArray(cat.coffee_base)) {
        cat.coffee_base.forEach(item => {
          const itemPrices = {
            Regular: item.L ? item.L - 10 : 39,
            Large: item.L || 49,
            XLarge: item.XL || 59,
            Jumbo: item.XL ? item.XL + 10 : 69
          };
          menuItems.push({
            id: idCounter++,
            name: `${item.name} (Coffee Base)`,
            category: catName,
            prices: itemPrices,
            basePrice: itemPrices.Large,
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
      <button class="btn-toggle order-btn" data-id="${item.id}" type="button" style="width: 100%; justify-content: center; margin-top: 1rem; cursor: pointer; pointer-events: auto; position: relative; z-index: 10;">
        <i class="fa-solid fa-cart-plus"></i> Order
      </button>
    </div>
  `).join('');
}

function updateModalTotalPrice() {
  const selectedSize = orderItemSize ? orderItemSize.value : 'Large';
  const currentProductId = orderForm ? orderForm.dataset.currentProductId : null;
  const product = menuItems.find(p => String(p.id) === String(currentProductId));
  
  const pricesObj = product ? product.prices : DEFAULT_PRICES;
  const basePrice = pricesObj[selectedSize] || pricesObj['Large'] || 35;
  const quantity = parseInt(orderQuantity ? orderQuantity.value : 1) || 1;

  let totalAddonCostPerItem = 0;
  document.querySelectorAll('input[name="modalAddOns"]:checked').forEach(cb => {
    totalAddonCostPerItem += parseFloat(cb.dataset.price) || 10;
  });

  const grandTotal = (basePrice + totalAddonCostPerItem) * quantity;
  let priceDisplay = document.getElementById("modalTotalPriceDisplay");
  
  if (!priceDisplay) {
    priceDisplay = document.createElement("div");
    priceDisplay.id = "modalTotalPriceDisplay";
    priceDisplay.style.cssText = "font-weight: 700; font-size: 1.1rem; color: var(--pastel-pink-dark); margin: 1rem 0; text-align: right;";
    if (orderForm) {
      const submitBtn = orderForm.querySelector('button[type="submit"]');
      if (submitBtn && submitBtn.parentNode === orderForm) {
        orderForm.insertBefore(priceDisplay, submitBtn);
      } else {
        orderForm.appendChild(priceDisplay);
      }
    }
  }
  priceDisplay.textContent = `Total: ₱${grandTotal.toFixed(2)}`;
}

function openOrderModal(productId) {
  const product = menuItems.find(p => String(p.id) === String(productId));
  if (!product) return;

  if (orderForm) orderForm.dataset.currentProductId = product.id;
  if (orderItemName) orderItemName.textContent = product.name;
  if (orderItemCategory) orderItemCategory.textContent = product.category;
  
  if (orderItemSize) {
    const prices = product.prices || DEFAULT_PRICES;
    orderItemSize.innerHTML = `
      <option value="Regular">Regular - ₱${prices.Regular || 25}.00</option>
      <option value="Large" selected>Large - ₱${prices.Large || 35}.00</option>
      <option value="XLarge">XLarge - ₱${prices.XLarge || 45}.00</option>
      <option value="Jumbo">Jumbo - ₱${prices.Jumbo || 60}.00</option>
    `;
    orderItemSize.onchange = updateModalTotalPrice;
  }

  if (orderQuantity) {
    orderQuantity.value = 1;
    orderQuantity.oninput = updateModalTotalPrice;
  }

  if (orderForm) {
    let addOnContainer = document.getElementById("modalAddOnsContainer");
    if (!addOnContainer) {
      addOnContainer = document.createElement("div");
      addOnContainer.id = "modalAddOnsContainer";
      addOnContainer.style.margin = "1rem 0";
    }

    if (addOnsList.length > 0) {
      addOnContainer.innerHTML = `
        <label style="display: block; font-size: 0.875rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main);">Add-Ons:</label>
        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 8px;">
          ${addOnsList.map(a => `
            <label style="font-size: 0.85rem; display: flex; align-items: center; gap: 6px; background: var(--bg-main); padding: 8px 10px; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; color: var(--text-main);">
              <input type="checkbox" name="modalAddOns" value="${a.name}" data-price="${a.price}">
              <span>${a.name} (+₱${a.price})</span>
            </label>
          `).join('')}
        </div>
      `;

      addOnContainer.querySelectorAll('input[name="modalAddOns"]').forEach(cb => {
        cb.onchange = updateModalTotalPrice;
      });
    }

    const quantityEl = orderQuantity ? (orderQuantity.closest('.form-group') || orderQuantity.parentElement) : null;
    
    if (quantityEl && quantityEl.parentNode) {
      quantityEl.parentNode.insertBefore(addOnContainer, quantityEl.nextSibling);
    } else {
      orderForm.appendChild(addOnContainer);
    }
  }

  const modalSubmitBtn = orderForm.querySelector('button[type="submit"]');
  if (modalSubmitBtn) {
    modalSubmitBtn.innerHTML = `<i class="fa-solid fa-cart-plus"></i> Add to Cart`;
  }

  updateModalTotalPrice();
  if (editModalOverlay) editModalOverlay.classList.remove("hidden");
}

function closeOrderModal() {
  if (editModalOverlay) editModalOverlay.classList.add("hidden");
}

function showQRPaymentModal(refNo, totalAmount, singleItem) {
  let qrModal = document.getElementById("qrPaymentModalOverlay");
  
  if (!qrModal) {
    qrModal = document.createElement("div");
    qrModal.id = "qrPaymentModalOverlay";
    qrModal.style.cssText = `
      position: fixed; top: 0; left: 0; width: 100%; height: 100%;
      background: rgba(0, 0, 0, 0.6); display: flex; align-items: center;
      justify-content: center; z-index: 1000;
    `;
    document.body.appendChild(qrModal);
  }

  qrModal.innerHTML = `
    <div style="background: var(--bg-card); padding: 2rem; border-radius: 16px; width: 90%; max-width: 400px; text-align: center; border: 1px solid var(--border); box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
      <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">Scan QR to Pay</h3>
      <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem;">Reference: <strong>${refNo}</strong></p>
      
      <div style="background: white; padding: 1.5rem; display: inline-block; border-radius: 12px; border: 2px dashed var(--border); margin-bottom: 1rem;">
        <div style="width: 150px; height: 150px; display: flex; flex-direction: column; align-items: center; justify-content: center; background: #f7fafc; color: #2d3748; font-weight: bold; font-size: 0.9rem; border-radius: 8px;">
          <i class="fa-solid fa-qrcode" style="font-size: 3rem; margin-bottom: 8px; color: var(--pastel-pink-dark);"></i>
          <span>QR PLACEHOLDER</span>
        </div>
      </div>

      <p style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 1.5rem;">Total Due: ₱${totalAmount.toFixed(2)}</p>
      
      <div style="display: flex; gap: 10px;">
        <button type="button" id="closeQrModalBtn" class="btn-toggle" style="flex: 1; justify-content: center; background: var(--bg-main); color: var(--text-main); border: 1px solid var(--border);">Cancel</button>
        <button type="button" id="simulatePaymentBtn" class="btn-toggle" style="flex: 1; justify-content: center; background: var(--pastel-pink-dark); color: white;">I've Paid</button>
      </div>
    </div>
  `;

  qrModal.classList.remove("hidden");

  document.getElementById("closeQrModalBtn").onclick = () => {
    qrModal.classList.add("hidden");
  };

  document.getElementById("simulatePaymentBtn").onclick = () => {
    notifications.unshift({
      title: `Payment Confirmed (${refNo})`,
      message: `Your payment of ₱${totalAmount.toFixed(2)} for ${singleItem.name} (${singleItem.size}) was successfully processed.`
    });
    renderNotifications();

    qrModal.classList.add("hidden");
    alert("Payment successful! Order placed.");
  };
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

function initEventListeners() {
  if (productsGrid) {
    productsGrid.addEventListener("click", (e) => {
      const orderBtn = e.target.closest(".order-btn");
      if (orderBtn) {
        e.preventDefault();
        e.stopPropagation();
        const productId = orderBtn.getAttribute("data-id");
        openOrderModal(productId);
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

  if (orderForm) {
    orderForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const currentProductId = orderForm.dataset.currentProductId;
      const product = menuItems.find(p => String(p.id) === String(currentProductId));
      const pricesObj = product ? product.prices : DEFAULT_PRICES;

      const selectedSize = orderItemSize ? orderItemSize.value : 'Large';
      const basePrice = pricesObj[selectedSize] || 35;
      const qty = parseInt(orderQuantity ? orderQuantity.value : 1) || 1;
      
      const selectedAddOns = Array.from(document.querySelectorAll('input[name="modalAddOns"]:checked'))
        .map(cb => ({ name: cb.value, price: parseFloat(cb.dataset.price) || 10 }));

      const totalAddonCost = selectedAddOns.reduce((sum, item) => sum + item.price, 0);
      const finalTotalPrice = (basePrice + totalAddonCost) * qty;

      const singleItem = {
        id: Date.now(),
        productId: product ? product.id : null,
        name: product ? product.name : 'Unknown Item',
        category: product ? product.category : 'General',
        size: selectedSize,
        basePrice: basePrice,
        addOns: selectedAddOns,
        quantity: qty,
        totalPrice: finalTotalPrice
      };

      closeOrderModal();
      
      const refNo = `#SB-${Math.floor(1000 + Math.random() * 9000)}`;
      showQRPaymentModal(refNo, finalTotalPrice, singleItem);
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
}

function updateDashboardCount() {
  if (totalFlavorsCount) totalFlavorsCount.textContent = menuItems.length;
}

function showErrorState(message) {
  if (!productsGrid) return;
  const isFileProtocol = window.location.protocol === "file:";
  productsGrid.innerHTML = `
    <div style="grid-column: 1 / -1; padding: 24px; background: var(--bg-card); border: 2px dashed #e53e3e; border-radius: 12px; text-align: center;">
      <h3 style="color: #e53e3e; margin-bottom: 8px;">Failed to Load products.json</h3>
      <p style="color: var(--text-main); font-size: 14px; margin-bottom: 12px;">${message}</p>
      ${isFileProtocol ? `<p style="font-size: 13px; color: var(--text-muted);">Browsers block direct file fetching over <code>file:///</code>. Please run via local server (VS Code Live Server or <code>npx serve</code>).</p>` : ''}
    </div>
  `;
}