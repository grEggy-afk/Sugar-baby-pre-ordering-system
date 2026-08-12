<!-- customer_page/menu.php -->
<div class="inventory-header" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
    <div>
        <h2 style="color: var(--text-main); margin: 0;">Sugar Baby Menu</h2>
        <p style="color: var(--text-muted); font-size: 0.875rem; margin-top: 0.25rem;">Explore our Milk Tea & Coffee offerings</p>
    </div>
    <div class="action-controls">
        <select id="sortSelect" class="sort-select" style="background-color: var(--bg-card); color: var(--text-main); border: 2px solid var(--pastel-yellow-dark); padding: 0.6rem 1rem; font-size: 0.875rem; font-weight: 600; border-radius: 10px; cursor: pointer; outline: none;">
            <option value="" disabled selected>Sort By</option>
            <option value="price-low">▼ Price Low to High</option>
            <option value="price-high">▼ Price High to Low</option>
            <option value="name-az">▼ Name A-Z</option>
            <option value="name-za">▼ Name Z-A</option>
        </select>
    </div>
</div>

<!-- Category Tabs -->
<div id="categoryTabs" style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem; overflow-x: auto; padding-bottom: 0.5rem;"></div>

<!-- Products Grid -->
<div id="productsGrid" class="products-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 1.5rem; padding: 1rem 0;"></div>

<!-- Import ng JS -->
<script src="../js/user_menu.js"></script>