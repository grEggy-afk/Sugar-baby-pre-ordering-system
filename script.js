let rawMenuData = {};
let productsList = [];
let shopSizes = [];
let currentActiveCategory = 'All Items';
let currentSortOrder = 'default';

document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM fully loaded. Fetching products.json...");

    // 1. Fetch JSON data
    fetch('products.json')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log("JSON loaded successfully:", data);
            rawMenuData = data;
            shopSizes = data.sizes || [];
            productsList = flattenMenu(data, shopSizes);
            initMenu(productsList);
        })
        .catch(error => {
            console.error('Failed to load products.json:', error);
            const grid = document.getElementById('productsGrid');
            if (grid) {
                grid.innerHTML = `<div style="padding: 20px; color: #e53e3e; font-size: 13px; grid-column: 1 / -1;">
                    <strong>Failed to load products.json</strong><br>
                    Reason: ${error.message}<br><br>
                    <em>Tip: Ensure you are running your project using a local server (like Live Server in VS Code) and that products.json is in the exact same directory as your HTML file.</em>
                </div>`;
            }
        });

    // 2. Tab Navigation Logic
    const navItems = document.querySelectorAll('.nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = item.getAttribute('data-tab');
            if (!targetTab) return;

            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');

            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === targetTab) {
                    content.classList.add('active');
                }
            });
        });
    });

    // 3. Popup User Menu & Notification Dropdowns
    const userTrigger = document.getElementById('userTrigger');
    const userPopup = document.getElementById('userPopup');
    if (userTrigger && userPopup) {
        userTrigger.addEventListener('click', (e) => {
            e.stopPropagation();
            userPopup.classList.toggle('hidden');
        });
    }

    const notifBtn = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    if (notifBtn && notifDropdown) {
        notifBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            notifDropdown.classList.toggle('hidden');
        });
    }

    window.addEventListener('click', () => {
        if (userPopup) userPopup.classList.add('hidden');
        if (notifDropdown) notifDropdown.classList.add('hidden');
    });

    // 4. Dark Mode Toggles
    const darkModeToggles = document.querySelectorAll('.darkModeToggle');
    darkModeToggles.forEach(toggle => {
        toggle.addEventListener('change', (e) => {
            if (e.target.checked) {
                document.body.classList.add('dark-mode');
                darkModeToggles.forEach(t => t.checked = true);
            } else {
                document.body.classList.remove('dark-mode');
                darkModeToggles.forEach(t => t.checked = false);
            }
        });
    });

    // 5. Modal & Search Bindings
    document.getElementById('closeModalBtn')?.addEventListener('click', closeModal);
    document.getElementById('editForm')?.addEventListener('submit', handleFormSubmit);
    document.getElementById('addNewItemBtn')?.addEventListener('click', openAddModal);

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const term = e.target.value.toLowerCase();
            applyFiltersAndSort(term);
        });
    }

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            currentSortOrder = e.target.value;
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            applyFiltersAndSort(searchTerm);
        });
    }
});

function switchTab(tabId) {
    const targetItem = document.querySelector(`.nav-item[data-tab="${tabId}"]`);
    if (targetItem) targetItem.click();
}

// Flattens categories and add-ons into a single product list containing raw pricing structures
function flattenMenu(data, sizes) {
    let list = [];
    
    if (data.categories) {
        data.categories.forEach(cat => {
            const catName = cat.name;

            if (cat.items) {
                cat.items.forEach(item => {
                    if (typeof item === 'string') {
                        let priceMap = {};
                        if (catName === "HOT DRINKS") {
                            priceMap = { "Standard": 25 };
                        } else {
                            sizes.forEach(s => {
                                priceMap[s.name] = s.promo_price !== undefined ? s.promo_price : s.price;
                            });
                        }
                        list.push({ 
                            name: item, 
                            category: catName, 
                            prices: priceMap, 
                            available: true 
                        });
                    } else if (item.name && item.prices) {
                        list.push({ 
                            name: item.name, 
                            category: catName, 
                            prices: item.prices, 
                            available: true 
                        });
                    } else if (item.name && item.price !== undefined) {
                        list.push({ 
                            name: item.name, 
                            category: catName, 
                            prices: { "Standard": item.price }, 
                            available: true 
                        });
                    }
                });
            }

            if (cat.cream_base) {
                cat.cream_base.forEach(item => {
                    list.push({ 
                        name: `${item.name} (Cream Base)`, 
                        category: catName, 
                        prices: { "LARGE": item.L, "XLARGE": item.XL }, 
                        available: true 
                    });
                });
            }

            if (cat.coffee_base) {
                cat.coffee_base.forEach(item => {
                    list.push({ 
                        name: `${item.name} (Coffee Base)`, 
                        category: catName, 
                        prices: { "LARGE": item.L, "XLARGE": item.XL }, 
                        available: true 
                    });
                });
            }
        });
    }

    if (data.add_ons) {
        data.add_ons.forEach(addon => {
            list.push({
                name: addon.name,
                category: 'ADD-ONS',
                prices: { "Standard": addon.price },
                available: true
            });
        });
    }

    return list;
}

function initMenu(data) {
    renderCategories(data);
    applyFiltersAndSort();
    updateTotalFlavorsCount(data);
}

function renderCategories(data) {
    const tabsContainer = document.getElementById('categoryTabs');
    if (!tabsContainer) return;
    
    const categories = ['All Items', ...new Set(data.map(item => item.category))];
    
    tabsContainer.innerHTML = '';
    categories.forEach((cat, index) => {
        const btn = document.createElement('button');
        btn.className = index === 0 ? 'btn-toggle' : 'sort-select';
        if (index === 0) {
            btn.style.backgroundColor = 'var(--pastel-yellow)';
            btn.style.borderColor = 'var(--pastel-yellow-dark)';
        }
        btn.textContent = cat;
        btn.addEventListener('click', () => {
            document.querySelectorAll('#categoryTabs button').forEach(b => {
                b.style.backgroundColor = 'var(--bg-card)';
            });
            btn.style.backgroundColor = 'var(--pastel-yellow)';
            
            currentActiveCategory = cat;
            const searchInput = document.getElementById('searchInput');
            const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
            applyFiltersAndSort(searchTerm);
        });
        tabsContainer.appendChild(btn);
    });
}

// Master filter and sorting function
function applyFiltersAndSort(searchTerm = '') {
    let result = currentActiveCategory === 'All Items' 
        ? [...productsList] 
        : productsList.filter(item => item.category === currentActiveCategory);

    if (searchTerm) {
        result = result.filter(item => 
            item.name.toLowerCase().includes(searchTerm) || item.category.toLowerCase().includes(searchTerm)
        );
    }

    if (currentSortOrder === 'name-az') {
        result.sort((a, b) => a.name.localeCompare(b.name));
    } else if (currentSortOrder === 'name-za') {
        result.sort((a, b) => b.name.localeCompare(a.name));
    }

    renderProducts(result);
}

function renderProducts(data) {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;

    grid.innerHTML = '';
    data.forEach((item) => {
        const absoluteIndex = productsList.findIndex(p => p.name === item.name && p.category === item.category);

        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.style.position = 'relative';
        
        const isAvailable = item.available !== false;
        
        // Build interactive size option buttons
        let sizeButtonsHtml = '';
        if (item.prices) {
            sizeButtonsHtml = `<div style="display: flex; flex-wrap: wrap; gap: 4px; margin-bottom: 8px;">` +
                Object.keys(item.prices).map(sizeName => `
                    <button type="button" onclick="togglePrice(event, ${absoluteIndex}, '${sizeName}')" style="font-size:11px; background:var(--bg-main); padding:4px 8px; border-radius:6px; border:1px solid var(--border); cursor:pointer; font-weight:600;">
                        ${sizeName}
                    </button>
                `).join('') + `</div>`;
        }
        
        productCard.innerHTML = `
            <div style="position: absolute; top: 12px; right: 12px; display: flex; gap: 4px; z-index: 10;">
                <button onclick="openEditModal(${absoluteIndex})" style="background:var(--bg-main); border:1px solid var(--border); border-radius:6px; width:26px; height:26px; cursor:pointer;"><i class="fa-solid fa-pen" style="font-size:10px; color:var(--text-muted);"></i></button>
                <button onclick="deleteProduct(${absoluteIndex})" style="background:var(--bg-main); border:1px solid var(--border); border-radius:6px; width:26px; height:26px; cursor:pointer;"><i class="fa-solid fa-trash" style="font-size:10px; color:#e53e3e;"></i></button>
            </div>
            
            <!-- Picture Frame Style Container -->
            <div style="border: 4px solid var(--border); border-radius: 8px; padding: 10px; background: var(--bg-main); margin-bottom: 12px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.03);">
                <div style="width: 100%; height: 90px; background: var(--bg-card); border-radius: 4px; display: flex; align-items: center; justify-content: center; border: 1px dashed var(--border); margin-bottom: 8px; overflow: hidden;">
                    <i class="fa-solid fa-mug-hot" style="font-size: 24px; color: var(--text-muted); opacity: 0.5;"></i>
                </div>
                <div class="badge" style="display: inline-block; margin-bottom: 4px;">${item.category}</div>
                <h3 class="product-title" style="margin-top: 0; margin-bottom: 0; font-size: 14px;">${item.name}</h3>
            </div>
            
            ${sizeButtonsHtml}
            <div class="price-display" style="font-size: 12px; font-weight: 700; color: var(--primary-color, #ff6b81); min-height: 18px; margin-bottom: 8px;"></div>

            <div style="display: flex; justify-content: flex-end; align-items: center; margin-top: auto; border-top: 1px solid var(--border); padding-top: 8px;">
                <div style="font-size: 0.75rem; font-weight: 700; color: ${isAvailable ? '#319795' : '#e53e3e'};">
                    ${isAvailable ? '● Available' : '● Unavailable'}
                </div>
            </div>
        `;
        grid.appendChild(productCard);
    });
}

// Toggle price reveal and hide when clicking the same size button again
window.togglePrice = function(event, itemIndex, sizeName) {
    const card = event.target.closest('.product-card');
    const priceDisplay = card.querySelector('.price-display');
    const targetItem = productsList[itemIndex];
    const clickedButton = event.target;
    
    const isAlreadyActive = clickedButton.style.background && clickedButton.style.background !== 'var(--bg-main)';

    // Reset all buttons and price display first
    const buttons = card.querySelectorAll('button[type="button"]');
    buttons.forEach(btn => btn.style.background = 'var(--bg-main)');

    if (isAlreadyActive) {
        // If it was already active, hide the price
        priceDisplay.textContent = '';
    } else {
        // Otherwise, reveal the price and highlight the button
        const price = targetItem.prices[sizeName];
        priceDisplay.textContent = `Selected (${sizeName}): ₱${price}`;
        clickedButton.style.background = 'var(--active-nav-bg, #ffe77a)';
    }
};

function updateTotalFlavorsCount(data) {
    const countEl = document.getElementById('totalFlavorsCount');
    if (countEl) countEl.textContent = data.length;
}

function openAddModal() {
    document.getElementById('editItemIndex').value = '';
    document.getElementById('editItemName').value = '';
    document.getElementById('editItemCategory').value = 'MILK TEA';
    document.getElementById('editItemAvailability').value = 'true';
    document.getElementById('editModalOverlay')?.classList.remove('hidden');
}

function openEditModal(index) {
    const item = productsList[index];
    document.getElementById('editItemIndex').value = index;
    document.getElementById('editItemName').value = item.name;
    document.getElementById('editItemCategory').value = item.category;
    document.getElementById('editItemAvailability').value = item.available !== false ? "true" : "false";
    document.getElementById('editModalOverlay')?.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('editModalOverlay')?.classList.add('hidden');
}

function handleFormSubmit(e) {
    e.preventDefault();
    const index = document.getElementById('editItemIndex').value;
    const name = document.getElementById('editItemName').value;
    const category = document.getElementById('editItemCategory').value;
    const available = document.getElementById('editItemAvailability').value === 'true';

    if (index === '') {
        productsList.push({ name, category, prices: { "Standard": 35 }, available });
    } else {
        productsList[index].name = name;
        productsList[index].category = category;
        productsList[index].available = available;
    }

    closeModal();
    initMenu(productsList);
}

function deleteProduct(index) {
    if (confirm("Are you sure you want to delete this product?")) {
        productsList.splice(index, 1);
        initMenu(productsList);
    }
}