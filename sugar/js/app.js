document.addEventListener('DOMContentLoaded', () => {
  const toggleBtn = document.getElementById('toggleBtn');
  const productGrid = document.getElementById('productGrid');
  const statusMsg = document.getElementById('statusMsg');
  const sortSelect = document.getElementById('sortSelect');

  let products = [];
  let isFetched = false;
  let isVisible = false;

  /**
   * Fetch inventory items from local products.json
   */
  async function fetchProducts() {
    try {
      statusMsg.textContent = 'Loading milk tea products...';
      const response = await fetch('products.json');

      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      products = await response.json();
      isFetched = true;
      
      // Sort and render as soon as fetched
      applySortAndRender();
      statusMsg.classList.add('hidden');
    } catch (error) {
      console.error('Fetch error:', error);
      statusMsg.textContent = 'Failed to load product data.';
      statusMsg.style.color = '#dc2626';
    }
  }

  /**
   * Render Milk Tea Cards into the grid (Format: Name, Category, Price)
   */
  function renderProducts(items) {
    productGrid.innerHTML = '';

    items.forEach(product => {
      const card = document.createElement('article');
      card.className = 'product-card';

      card.innerHTML = `
        <div class="product-image-container">
          <i class="fa-solid fa-glass-water"></i>
        </div>
        <div>
          <span class="badge">${escapeHtml(product.category)}</span>
          <h3 class="product-title">${escapeHtml(product.name)}</h3>
          <div class="product-price">₱${Number(product.price).toFixed(2)}</div>
        </div>
      `;

      productGrid.appendChild(card);
    });
  }

  /**
   * Escape HTML string helper
   */
  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  /**
   * FUNCTIONAL SORT LOGIC
   */
  function applySortAndRender() {
    if (!products || products.length === 0) return;

    const sortValue = sortSelect.value;
    let sortedProducts = [...products];

    switch (sortValue) {
      case 'price-low':
        sortedProducts.sort((a, b) => parseFloat(a.price) - parseFloat(b.price));
        break;
      case 'price-high':
        sortedProducts.sort((a, b) => parseFloat(b.price) - parseFloat(a.price));
        break;
      case 'name-az':
        sortedProducts.sort((a, b) => a.name.localeCompare(b.name));
        break;
      case 'name-za':
        sortedProducts.sort((a, b) => b.name.localeCompare(a.name));
        break;
      default:
        break;
    }

    renderProducts(sortedProducts);
  }

  /**
   * Show/Hide Button Toggle Event
   */
  toggleBtn.addEventListener('click', async () => {
    if (!isFetched) {
      await fetchProducts();
      if (!isFetched) return;
    }

    isVisible = !isVisible;

    if (isVisible) {
      productGrid.classList.remove('hidden');
      statusMsg.classList.add('hidden');
      toggleBtn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> <span>Hide Products</span>';
    } else {
      productGrid.classList.add('hidden');
      statusMsg.textContent = 'Products hidden. Click button to view again.';
      statusMsg.classList.remove('hidden');
      toggleBtn.innerHTML = '<i class="fa-solid fa-eye"></i> <span>Show Products</span>';
    }
  });

  /**
   * Sort Select Event Listener
   */
  sortSelect.addEventListener('change', async () => {
    if (!isFetched) {
      await fetchProducts();
      isVisible = true;
      productGrid.classList.remove('hidden');
      statusMsg.classList.add('hidden');
      toggleBtn.innerHTML = '<i class="fa-solid fa-eye-slash"></i> <span>Hide Products</span>';
    } else {
      applySortAndRender();
    }
  });
});