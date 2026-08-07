document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('homeFeatureContent');

  if (!container) return;

  async function loadHomeFeatures() {
    try {
      const response = await fetch('products.json');
      if (!response.ok) {
        throw new Error(`HTTP error! Status: ${response.status}`);
      }

      const products = await response.json();
      const featured = (products || []).slice(0, 10);

      container.innerHTML = `
        <div style="background: linear-gradient(135deg, #fff2a8, #ffd6e7); border: 1px solid #f0e6db; border-radius: 20px; padding: 1.5rem 1.6rem; margin-bottom: 1rem; position: relative; overflow: hidden;">
          <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.2em; text-transform: uppercase; color: #ff4d7a; margin-bottom: 0.45rem;">Great Deals</p>
          <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
            <div style="flex: 1 1 260px; min-width: 220px;">
              <h2 style="color: var(--text-main); margin-bottom: 0.35rem;">Hot now & new</h2>
              <p style="color: var(--text-muted); line-height: 1.6; margin-bottom: 1rem;">Discover fresh favorites, limited offers, and the easiest way to order your next drink.</p>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; justify-content: flex-end;">
              <button onclick="switchTab('menu')" style="border: none; border-radius: 999px; background: transparent; color: var(--text-main); font-weight: 700; padding: 0.9rem 1.3rem; cursor: pointer;">Browse menu</button>
              <button onclick="openOrderPanel()" style="border: none; border-radius: 999px; background: #ff5a8f; color: #fff; padding: 0.9rem 1.3rem; cursor: pointer; font-weight: 700; box-shadow: 0 6px 18px rgba(255,90,143,0.18);">Order now</button>
            </div>
          </div>
        </div>

        <div id="orderPanel" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.55); backdrop-filter: blur(3px); z-index: 999; padding: 1.5rem;">
          <div style="width: min(560px, 100%); margin: auto; background: #ffffff; border-radius: 28px; padding: 1.5rem; box-shadow: 0 32px 90px rgba(15, 23, 42, 0.25); position: relative;">
            <button onclick="closeOrderPanel()" style="position: absolute; top: 1rem; right: 1rem; border: none; background: transparent; font-size: 1.2rem; color: #4b5563; cursor: pointer;">✕</button>
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.25rem;">
              <div>
                <p style="font-size: 0.75rem; color: #4f46e5; font-weight: 700; text-transform: uppercase; letter-spacing: 0.12em; margin-bottom: 0.35rem;">Order preview</p>
                <h3 style="color: var(--text-main); margin: 0;">Quick order details</h3>
              </div>
              <span style="background: #fef3c7; color: #92400e; font-weight: 700; padding: 0.65rem 0.95rem; border-radius: 999px;">Ready to checkout</span>
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: stretch;">
              <div style="width: 140px; min-width: 140px; height: 140px; border-radius: 22px; background: #f8f7ff; display: flex; align-items: center; justify-content: center; color: #5c6ac4; font-weight: 700; font-size: 0.95rem;">Image placeholder</div>
              <div style="flex: 1; min-width: 220px; display: grid; gap: 1rem;">
                <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                  <span>Product</span><strong>Hot now item</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                  <span>Size</span><strong>Regular</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                  <span>Amount</span><strong>1</strong>
                </div>
                <div style="display: flex; justify-content: space-between; color: var(--text-muted);">
                  <span>Price</span><strong>₱50.00</strong>
                </div>
                <button style="width: fit-content; border: none; border-radius: 999px; padding: 0.95rem 1.3rem; background: #e2e8f0; color: #2d3748; font-weight: 700; cursor: pointer;">Choose product</button>
                <button onclick="closeOrderPanel()" style="width: fit-content; border: none; border-radius: 999px; padding: 0.95rem 1.3rem; background: #4f46e5; color: #fff; font-weight: 700; cursor: pointer;">Proceed to order</button>
              </div>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; align-items: stretch;">
          <div style="flex: 1 1 48%; min-width: 280px; background: #ffffff; border: 1px solid #f0e6db; border-radius: 18px; padding: 1.3rem; box-shadow: 0 8px 24px rgba(255, 171, 193, 0.14);">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem;">
              <div>
                <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #ff4d7a; margin-bottom: 0.4rem;">Loyalty rewards</p>
                <h3 style="color: var(--text-main); margin: 0; font-size: 1.2rem;">Loyalty Card</h3>
              </div>
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.2rem;">Show the physical loyalty card in-store to claim free toppings, upsizes, and XL upgrades.</p>
            <div style="display: grid; gap: 0.75rem; margin-bottom: 1.2rem;">
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="display: flex; gap: 0.4rem;">
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #ffd6e7;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #ffd6e7;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #ffd6e7;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #ffd6e7;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #ffd6e7; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #8d3a63;">5</div>
                </div>
                <span style="color: #a33c5e; font-weight: 700;">Extra toppings</span>
              </div>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="display: flex; gap: 0.4rem;">
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #cbebff;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #cbebff;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #cbebff;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #cbebff;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #cbebff; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #1f4c70;">10</div>
                </div>
                <span style="color: #3f6f91; font-weight: 700;">Free upsize</span>
              </div>
              <div style="display: flex; align-items: center; gap: 0.75rem;">
                <div style="display: flex; gap: 0.4rem;">
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #fff2a8;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #fff2a8;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #fff2a8;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #fff2a8;"></div>
                  <div style="width: 48px; height: 48px; border-radius: 50%; background: #fff2a8; display: flex; align-items: center; justify-content: center; font-weight: 700; color: #8d6b00;">15</div>
                </div>
                <span style="color: #8d6b00; font-weight: 700;">Free XL</span>
              </div>
            </div>
          </div>

          <div style="flex: 1 1 48%; min-width: 280px; background: #f8f7ff; border: 1px solid #e5e3ff; border-radius: 18px; padding: 1.3rem; box-shadow: 0 8px 24px rgba(203, 235, 255, 0.18);">
            <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.35rem; color: #5c6ac4;">New arrival</p>
            <h3 style="color: var(--text-main); margin-bottom: 1rem;">Peach & Honey Breeze</h3>
            <div style="height: 220px; border-radius: 20px; background: linear-gradient(180deg, #dde7ff 0%, #f8f7ff 100%); display: flex; align-items: center; justify-content: center; color: #3f587e; font-weight: 700; text-align: center; padding: 1rem; margin-bottom: 1.2rem;">A creamy seasonal pick with peach, honey, and chill vibes.</div>
            <p style="color: var(--text-muted); font-size: 0.93rem; line-height: 1.6; margin-bottom: 1.25rem;">Try the new flavor and collect extra loyalty stamps when you order today.</p>
            <button onclick="switchTab('menu')" style="border: none; border-radius: 999px; padding: 0.9rem 1.2rem; background: #5c6ac4; color: #fff; font-weight: 700; cursor: pointer; width: fit-content;">See the menu</button>
          </div>
        </div>

        <div>
          <h3 style="color: var(--text-main); margin-bottom: 0.35rem;">Hot now products</h3>
          <p style="color: var(--text-muted); margin-bottom: 0.85rem; line-height: 1.5;">Only items featured here are available through the quick order panel.</p>
          <div id="homeProductList" style="display: flex; gap: 1rem; overflow-x: auto; padding: 0.3rem 0.2rem 0.8rem; scroll-snap-type: x proximity; min-width: 100%; scrollbar-width: thin; -webkit-overflow-scrolling: touch;"></div>
        </div>

        <div style="margin-top: 1.5rem; background: #ffffff; border: 1px solid #f0e6db; border-radius: 20px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(203, 235, 255, 0.18);">
          <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
            <div>
              <p style="font-size: 0.75rem; font-weight: 700; letter-spacing: 0.18em; text-transform: uppercase; color: #5e7cff; margin-bottom: 0.35rem;">Shop highlight</p>
              <h3 style="color: var(--text-main); margin: 0;">Why customers keep coming back</h3>
            </div>
            <button onclick="switchTab('menu')" style="border: none; border-radius: 999px; padding: 0.85rem 1.2rem; background: #ff5a8f; color: #fff; font-weight: 700; cursor: pointer;">Order your favorite</button>
          </div>
          <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem;">
            <div style="background: #f8f7ff; border-radius: 16px; padding: 1rem;">
              <h4 style="margin-bottom: 0.65rem; color: var(--text-main);">Fast pickup</h4>
              <p style="color: var(--text-muted); font-size: 0.92rem; line-height: 1.6;">Grab your drink quickly with our ready-to-go service for busy hours.</p>
            </div>
            <div style="background: #fff2a8; border-radius: 16px; padding: 1rem;">
              <h4 style="margin-bottom: 0.65rem; color: #2c3e50;">Daily rewards</h4>
              <p style="color: #2c3e50; font-size: 0.92rem; line-height: 1.6;">Earn stamps and redeem special perks each time you visit.</p>
            </div>
            <div style="background: #ffd6e7; border-radius: 16px; padding: 1rem;">
              <h4 style="margin-bottom: 0.65rem; color: #2c3e50;">Staff favorites</h4>
              <p style="color: #2c3e50; font-size: 0.92rem; line-height: 1.6;">Our top picks right now, curated by the shop team.</p>
            </div>
          </div>
        </div>`;

      const list = document.getElementById('homeProductList');
      if (list) {
        featured.forEach((product, index) => {
          const card = document.createElement('article');
          card.className = 'product-card';
          card.style.minWidth = '240px';
          card.style.flex = '0 0 240px';
          card.style.scrollSnapAlign = 'start';
          const badge = index === 0 ? 'Hot Now' : index === 1 ? 'Best Seller' : index === 2 ? 'New' : 'Fresh';

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

          list.appendChild(card);
        });
      }
    } catch (error) {
      console.error('Failed to load home features:', error);
      container.innerHTML = '<p style="color: #dc2626;">Could not load featured items.</p>';
    }
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;');
  }

  function toggleOrderPanel(show) {
    const panel = document.getElementById('orderPanel');
    if (panel) panel.style.display = show ? 'block' : 'none';
  }

  window.openOrderPanel = () => {
    const panel = document.getElementById('orderPanel');
    if (panel) panel.style.display = 'flex';
  };

  window.closeOrderPanel = () => toggleOrderPanel(false);

  loadHomeFeatures();
});
