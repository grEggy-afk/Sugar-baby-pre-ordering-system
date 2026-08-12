let menuItems = [];
let activeCategory = "All";
let currentSearch = "";
let currentSort = "";

const productsGrid = document.getElementById("productsGrid");
const categoryTabsContainer = document.getElementById("categoryTabs");
const searchInput = document.getElementById("searchInput");
const sortSelect = document.getElementById("sortSelect");
const totalFlavorsCount = document.getElementById("totalFlavorsCount");

const editModalOverlay = document.getElementById("editModalOverlay");
const closeModalBtn = document.getElementById("closeModalBtn");
const editForm = document.getElementById("editForm");
const editItemIndex = document.getElementById("editItemIndex");
const editItemName = document.getElementById("editItemName");
const editItemCategory = document.getElementById("editItemCategory");
const editItemAvailability = document.getElementById("editItemAvailability");

document.addEventListener("DOMContentLoaded", () => {
  initNavigation();
  initEventListeners();
  fetchProducts();
});

// =======================
// 1. NAVIGATION & TABS
// =======================
function initNavigation() {
  document.querySelectorAll(".nav-item").forEach(item => {
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

// =======================
// 2. FETCH & PARSE DATA
// =======================
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

  // Handle array format (simple list)
  if (Array.isArray(data)) {
    let idCounter = 1;
    data.forEach(item => {
      const itemName = item.name || "Unnamed Item";
      const itemPrice = item.price || 25;
      const itemCategory = item.category || "General";
      
      // Generate default prices based on category
      let priceMap = {};
      if (itemCategory === "HOT DRINKS") {
        priceMap = { "Standard": itemPrice };
      } else {
        priceMap = {
          "Regular": 25,
          "Large": 35,
          "XLarge": 45,
          "Jumbo": 60
        };
        // Set default base price to Regular
        priceMap["Regular"] = itemPrice;
      }

      menuItems.push({
        id: idCounter++,
        name: itemName,
        category: itemCategory,
        prices: priceMap,
        available: true
      });
    });
  } 
  // Handle complex format (categories & sizes)
  else if (data.categories) {
    let idCounter = 1;
    data.categories.forEach(cat => {
      if (cat.items) {
        cat.items.forEach(item => {
          let priceMap = {};
          if (cat.name === "HOT DRINKS") {
            priceMap = { "Standard": 25 };
          } else {
            // Use sizes from the JSON if available, otherwise use defaults
            if (data.sizes) {
              data.sizes.forEach(size => {
                priceMap[size.name] = size.price;
              });
            } else {
              priceMap = { "Regular": 25, "Large": 35, "XLarge": 45, "Jumbo": 60 };
            }
          }
          menuItems.push({
            id: idCounter++,
            name: typeof item === 'string' ? item : item.name,
            category: cat.name,
            prices: priceMap,
            available: true
          });
        });
      }
    });
  }
}

// =======================
// 3. CATEGORY TABS
// =======================
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

// =======================
// 4. RENDER PRODUCTS (WITH SIZES & EDIT/DELETE)
// =======================
function renderProducts() {
  if (!productsGrid) return;

  let filtered = menuItems.filter(item => {
    const matchesCategory = activeCategory === "All" || item.category === activeCategory;
    const matchesSearch = item.name.toLowerCase().includes(currentSearch.toLowerCase());
    return matchesCategory && matchesSearch;
  });

  // Sorting
  if (currentSort === "price-low") filtered.sort((a, b) => {
    const p1 = Object.values(a.prices)[0] || 0;
    const p2 = Object.values(b.prices)[0] || 0;
    return p1 - p2;
  });
  else if (currentSort === "price-high") filtered.sort((a, b) => {
    const p1 = Object.values(a.prices)[0] || 0;
    const p2 = Object.values(b.prices)[0] || 0;
    return p2 - p1;
  });
  else if (currentSort === "name-az") filtered.sort((a, b) => a.name.localeCompare(b.name));
  else if (currentSort === "name-za") filtered.sort((a, b) => b.name.localeCompare(a.name));

  if (filtered.length === 0) {
    productsGrid.innerHTML = `<p style="grid-column: 1/-1; text-align: center; color: var(--text-muted); padding: 2rem;">No menu items found.</p>`;
    return;
  }

  productsGrid.innerHTML = filtered.map((item, index) => {
    const absoluteIndex = menuItems.findIndex(p => p.id === item.id);
    const isAvailable = item.available !== false;
    
    // Generate size buttons HTML
    let sizeButtonsHtml = '';
    if (item.prices) {
      sizeButtonsHtml = `<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 8px;">` +
        Object.keys(item.prices).map(sizeName => `
          <span style="font-size:11px; background:var(--bg-main); padding:4px 8px; border-radius:6px; border:1px solid var(--border); font-weight:600; color:var(--text-muted);">
            ${sizeName}
          </span>
        `).join('') + `</div>`;
    }

    return `
      <div class="product-card" style="position: relative;">
        <!-- Admin Actions (Edit & Delete) -->
        <div style="position: absolute; top: 12px; right: 12px; display: flex; gap: 4px; z-index: 10;">
          <button onclick="openEditModal(${absoluteIndex})" style="background:var(--bg-main); border:1px solid var(--border); border-radius:6px; width:26px; height:26px; cursor:pointer;">
            <i class="fa-solid fa-pen" style="font-size:10px; color:var(--text-muted);"></i>
          </button>
          <button onclick="deleteProduct(${absoluteIndex})" style="background:var(--bg-main); border:1px solid var(--border); border-radius:6px; width:26px; height:26px; cursor:pointer;">
            <i class="fa-solid fa-trash" style="font-size:10px; color:#e53e3e;"></i>
          </button>
        </div>
        
        <div class="product-image-container">
          <i class="fa-solid fa-mug-hot" style="font-size:2.8rem; color:var(--text-muted); opacity:0.5;"></i>
        </div>
        <div class="badge">${item.category}</div>
        <div class="product-title">${item.name}</div>
        
        ${sizeButtonsHtml}
        
        <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: 12px; border-top: 1px solid var(--border); padding-top: 10px;">
          <div style="font-size: 0.75rem; font-weight: 700; color: ${isAvailable ? '#319795' : '#e53e3e'};">
            ${isAvailable ? '● Available' : '● Unavailable'}
          </div>
        </div>
      </div>
    `;
  }).join('');
}

// =======================
// 5. ADD / EDIT / DELETE MODALS
// =======================
function openAddModal() {
  document.getElementById('editItemIndex').value = '';
  document.getElementById('editItemName').value = '';
  document.getElementById('editItemCategory').value = 'MILK TEA';
  document.getElementById('editItemAvailability').value = 'true';
  editModalOverlay.classList.remove('hidden');
}

function openEditModal(index) {
  const item = menuItems[index];
  document.getElementById('editItemIndex').value = index;
  document.getElementById('editItemName').value = item.name;
  document.getElementById('editItemCategory').value = item.category;
  document.getElementById('editItemAvailability').value = item.available !== false ? "true" : "false";
  editModalOverlay.classList.remove('hidden');
}

function closeModal() {
  editModalOverlay.classList.add('hidden');
}

function deleteProduct(index) {
  if (confirm("Are you sure you want to delete this product?")) {
    menuItems.splice(index, 1);
    renderProducts();
    updateDashboardCount();
  }
}

function handleFormSubmit(e) {
  e.preventDefault();
  const index = document.getElementById('editItemIndex').value;
  const name = document.getElementById('editItemName').value;
  const category = document.getElementById('editItemCategory').value;
  const available = document.getElementById('editItemAvailability').value === 'true';

  let defaultPrices = { "Standard": 35 };
  if (category === "MILK TEA" || category === "COFFEE" || category === "FRUIT TEA") {
    defaultPrices = { "Regular": 25, "Large": 35, "XLarge": 45, "Jumbo": 60 };
  } else if (category === "HOT DRINKS") {
    defaultPrices = { "Standard": 25 };
  }

  if (index === '') {
    // Add new item
    menuItems.push({ 
      id: Date.now(),
      name, 
      category, 
      prices: defaultPrices, 
      available 
    });
  } else {
    // Update existing item
    menuItems[index].name = name;
    menuItems[index].category = category;
    menuItems[index].available = available;
  }

  closeModal();
  renderProducts();
  updateDashboardCount();
}

// =======================
// 6. EVENT LISTENERS
// =======================
function initEventListeners() {
  if (searchInput) searchInput.addEventListener("input", (e) => { currentSearch = e.target.value; renderProducts(); });
  if (sortSelect) sortSelect.addEventListener("change", (e) => { currentSort = e.target.value; renderProducts(); });
  if (closeModalBtn) closeModalBtn.addEventListener("click", closeModal);
  if (editForm) editForm.addEventListener("submit", handleFormSubmit);
  
  // Open Add Modal
  const addNewItemBtn = document.getElementById("addNewItemBtn");
  if (addNewItemBtn) addNewItemBtn.addEventListener("click", openAddModal);

  if (editModalOverlay) {
    editModalOverlay.addEventListener("click", (e) => {
      if (e.target === editModalOverlay) closeModal();
    });
  }

  // Dark Mode Toggles
  document.querySelectorAll(".darkModeToggle").forEach(toggle => {
    toggle.addEventListener("change", (e) => {
      const isChecked = e.target.checked;
      document.body.classList.toggle("dark-mode", isChecked);
      document.querySelectorAll(".darkModeToggle").forEach(t => t.checked = isChecked);
    });
  });
}

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