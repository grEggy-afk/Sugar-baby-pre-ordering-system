let rawMenuData = {};
let productsList = [];
let shopSizes = [];
let currentActiveCategory = 'All Items';
let currentSortOrder = 'default';

document.addEventListener("DOMContentLoaded", () => {
    fetch('products.json')
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
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
                    <strong>Failed to load products.json</strong><br>Reason: ${error.message}
                </div>`;
            }
        });

    // Unified Tab Navigation supporting both data-tab and data-target
    const navItems = document.querySelectorAll('.nav-link, .nav-item[data-tab]');
    const tabContents = document.querySelectorAll('.tab-content, .view-section');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const targetTab = item.getAttribute('data-tab') || item.getAttribute('data-target');
            if (!targetTab) return;

            navItems.forEach(nav => {
                nav.classList.remove('active');
                if(nav.parentElement) nav.parentElement.classList.remove('active');
            });
            item.classList.add('active');
            if(item.parentElement) item.parentElement.classList.add('active');

            tabContents.forEach(content => {
                content.classList.remove('active');
                if (content.id === targetTab) {
                    content.classList.add('active');
                }
            });
        });
    });

    // Modal & Search Bindings
    document.getElementById('closeModalBtn')?.addEventListener('click', closeModal);
    document.getElementById('editForm')?.addEventListener('submit', handleFormSubmit);
    document.getElementById('addNewItemBtn')?.addEventListener('click', openAddModal);

    const searchInput = document.getElementById('searchInput');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            applyFiltersAndSort(e.target.value.toLowerCase());
        });
    }

    const sortSelect = document.getElementById('sortSelect');
    if (sortSelect) {
        sortSelect.addEventListener('change', (e) => {
            currentSortOrder = e.target.value;
            applyFiltersAndSort(searchInput ? searchInput.value.toLowerCase() : '');
        });
    }
});

function flattenMenu(data, sizes) {
    let list = [];
    if (data.categories) {
        data.categories.forEach(cat => {
            const catName = cat.name;
            if (cat.items) {
                cat.items.forEach(item => {
                    if (typeof item === 'string') {
                        let priceMap = {};
                        sizes.forEach(s => { priceMap[s.name] = s.promo_price ?? s.price; });
                        list.push({ name: item, category: catName, prices: priceMap, available: true });
                    } else if (item.name && item.prices) {
                        list.push({ name: item.name, category: catName, prices: item.prices, available: true });
                    }
                });
            }
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
        btn.className = index === 0 ? 'btn-primary' : 'btn-secondary';
        btn.textContent = cat;
        btn.addEventListener('click', () => {
            currentActiveCategory = cat;
            applyFiltersAndSort(document.getElementById('searchInput')?.value.toLowerCase() || '');
        });
        tabsContainer.appendChild(btn);
    });
}

function applyFiltersAndSort(searchTerm = '') {
    let result = currentActiveCategory === 'All Items' 
        ? [...productsList] 
        : productsList.filter(item => item.category === currentActiveCategory);

    if (searchTerm) {
        result = result.filter(item => item.name.toLowerCase().includes(searchTerm));
    }

    if (currentSortOrder === 'name-az') result.sort((a, b) => a.name.localeCompare(b.name));
    if (currentSortOrder === 'name-za') result.sort((a, b) => b.name.localeCompare(a.name));

    renderProducts(result);
}

function renderProducts(data) {
    const grid = document.getElementById('productsGrid');
    if (!grid) return;
    grid.innerHTML = '';
    
    data.forEach((item) => {
        const absoluteIndex = productsList.findIndex(p => p.name === item.name);
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.innerHTML = `
            <h3>${item.name}</h3>
            <span class="badge">${item.category}</span>
            <div style="font-size: 12px; font-weight: 700;">${item.available ? 'Available' : 'Unavailable'}</div>
        `;
        grid.appendChild(productCard);
    });
}

function updateTotalFlavorsCount(data) {
    const countEl = document.getElementById('totalFlavorsCount');
    if (countEl) countEl.textContent = data.length;
}

function openAddModal() {
    document.getElementById('editModalOverlay')?.classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModalOverlay')?.classList.add('hidden');
}
function handleFormSubmit(e) {
    e.preventDefault();
    closeModal();
}