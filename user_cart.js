/* =========================================================
   SUGAR BABY - USER CART
   COMPLETE JAVASCRIPT
   VERSION WITH EDIT SIZE + SUGAR + ADD-ONS
========================================================= */


/* =========================================================
   STORAGE
========================================================= */

const CART_STORAGE_KEY = "sugar_baby_cart";
const ORDER_STORAGE_KEY = "sugar_baby_orders";


/* =========================================================
   SAMPLE CART
========================================================= */

const SAMPLE_CART = [

    {
        id: "sample-1",
        productId: "milk-tea-001",
        category: "Milk Tea",
        name: "Classic Milk Tea",
        size: "Large",
        basePrice: 185,

        addOns: [
            {
                name: "Boba",
                price: 15
            },
            {
                name: "Extra Caramel",
                price: 20
            }
        ],

        sugarLevel: "50%",
        quantity: 2
    },

    {
        id: "sample-2",
        productId: "coffee-001",
        category: "Coffee",
        name: "Iced Caramel Macchiato",
        size: "Medium",
        basePrice: 185,

        addOns: [
            {
                name: "Extra Caramel",
                price: 20
            }
        ],

        sugarLevel: "75%",
        quantity: 2
    },

    {
        id: "sample-3",
        productId: "shake-001",
        category: "Shake",
        name: "Strawberry Cream Shake",
        size: "Large",
        basePrice: 220,

        addOns: [
            {
                name: "Whipped Cream",
                price: 25
            }
        ],

        sugarLevel: "100%",
        quantity: 1
    }

];


/* =========================================================
   AVAILABLE ADD-ONS
========================================================= */

const AVAILABLE_ADDONS = [

    {
        name: "Boba",
        price: 15
    },

    {
        name: "Extra Pearl",
        price: 15
    },

    {
        name: "Cream Cheese",
        price: 20
    },

    {
        name: "Extra Caramel",
        price: 20
    },

    {
        name: "Popping Boba",
        price: 20
    },

    {
        name: "Whipped Cream",
        price: 25
    },

    {
        name: "Cotton Candy",
        price: 10
    },

    {
        name: "Nata",
        price: 10
    },

    {
        name: "Pearl",
        price: 10
    },

    {
        name: "Coffee Jelly",
        price: 10
    },

    {
        name: "Oreo",
        price: 10
    }

];


/* =========================================================
   CART
========================================================= */

let cart = [];


/* =========================================================
   ONE PICKUP TIME FOR WHOLE ORDER
========================================================= */

let pickupTime = "";


/* =========================================================
   CURRENT EDIT ITEM
========================================================= */

let editingItemId = null;


/* =========================================================
   LOAD CART
========================================================= */

function loadCart() {

    try {

        const saved =
            localStorage.getItem(
                CART_STORAGE_KEY
            );

        if (saved === null) {

            cart = SAMPLE_CART.map(item => ({

                ...item,

                addOns:
                    Array.isArray(item.addOns)
                        ? item.addOns.map(addon => ({
                            ...addon
                        }))
                        : []

            }));

            saveCart();

            return;
        }


        const parsed =
            JSON.parse(saved);


        cart =
            Array.isArray(parsed)
                ? parsed
                : [];


    } catch (error) {

        console.error(
            "Unable to load cart:",
            error
        );

        cart = [];

    }

}


/* =========================================================
   SAVE CART
========================================================= */

function saveCart() {

    try {

        localStorage.setItem(
            CART_STORAGE_KEY,
            JSON.stringify(cart)
        );

    } catch (error) {

        console.error(
            "Unable to save cart:",
            error
        );

    }

}


/* =========================================================
   GET UNIT PRICE
========================================================= */

function getCartItemUnitPrice(item) {

    const basePrice =
        Number(item.basePrice) || 0;


    const addOnPrice =
        Array.isArray(item.addOns)

            ? item.addOns.reduce(
                (total, addon) => {

                    return total +
                        (Number(addon.price) || 0);

                },
                0
            )

            : 0;


    return basePrice + addOnPrice;

}


/* =========================================================
   GET ITEM TOTAL
========================================================= */

function getCartItemTotal(item) {

    const quantity =
        Number(item.quantity) || 1;


    return (
        getCartItemUnitPrice(item) *
        quantity
    );

}


/* =========================================================
   CART SUBTOTAL
========================================================= */

function getCartSubtotal() {

    return cart.reduce(
        (total, item) => {

            return total +
                getCartItemTotal(item);

        },
        0
    );

}


/* =========================================================
   CART QUANTITY
========================================================= */

function getCartQuantity() {

    return cart.reduce(
        (total, item) => {

            return total +
                (Number(item.quantity) || 0);

        },
        0
    );

}


/* =========================================================
   CATEGORY ICON
========================================================= */

function getCategoryIcon(category) {

    const value =
        String(category || "")
            .toLowerCase();


    if (value.includes("tea")) {

        return "fa-mug-saucer";

    }


    if (value.includes("coffee")) {

        return "fa-mug-hot";

    }


    if (
        value.includes("shake") ||
        value.includes("smoothie")
    ) {

        return "fa-cup-togo";

    }


    if (
        value.includes("dessert") ||
        value.includes("snack")
    ) {

        return "fa-cookie-bite";

    }


    return "fa-mug-hot";

}


/* =========================================================
   PICKUP TIME DISPLAY
========================================================= */

function updatePickupTimeDisplay() {

    const pickupTimeDisplay =
        document.getElementById(
            "pickupTimeDisplay"
        );


    if (!pickupTimeDisplay) {
        return;
    }


    let savedPickupTime =
        localStorage.getItem(
            "pickupTime"
        );


    if (!savedPickupTime) {

        savedPickupTime =
            "10:00 AM";

    }


    pickupTimeDisplay.innerHTML = `

        <i class="fa-regular fa-clock"></i>

        <span>
            Pick-Up Time:
            <strong>
                ${escapeHtml(savedPickupTime)}
            </strong>
        </span>

    `;

}


/* =========================================================
   ESCAPE HTML
========================================================= */

function escapeHtml(value) {

    return String(value ?? "")

        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}


/* =========================================================
   RENDER CART
========================================================= */

function renderCart() {

    
    const summaryPickupTime =
        document.getElementById("summaryPickupTime");

    if (summaryPickupTime) {
        let savedPickupTime =
            localStorage.getItem("pickupTime") ||  "10:00 AM";

        summaryPickupTime.textContent = savedPickupTime;
    }

    const container =
        document.getElementById(
            "cartItemsContainer"
        );


    if (!container) {
        return;
    }


    const quantity =
        getCartQuantity();


    const subtotal =
        getCartSubtotal();


    /* =====================================================
       SUMMARY
    ===================================================== */

    const summaryQuantity =
        document.getElementById(
            "summaryQuantity"
        );


    const summaryItemsPrice =
        document.getElementById(
            "summaryItemsPrice"
        );


    

    const summaryTotal =
        document.getElementById(
            "summaryTotal"
        );


    if (summaryQuantity) {

        summaryQuantity.textContent =
            quantity;

    }


    if (summaryItemsPrice) {

        summaryItemsPrice.textContent =
            `₱${subtotal.toFixed(2)}`;

    }


    if (summarySubtotal) {

        summarySubtotal.textContent =
            `₱${subtotal.toFixed(2)}`;

    }


    if (summaryTotal) {

        summaryTotal.textContent =
            `₱${subtotal.toFixed(2)}`;

    }


    /* =====================================================
       CHECKOUT BUTTON
    ===================================================== */

    const checkoutBtn =
        document.getElementById(
            "checkoutBtn"
        );


    if (checkoutBtn) {

        checkoutBtn.disabled =
            cart.length === 0;

    }


    /* =====================================================
       EMPTY CART
    ===================================================== */

    if (cart.length === 0) {

        container.innerHTML = `

            <div class="empty-cart">

                <div class="empty-cart-icon">

                    <i class="fa-solid fa-cart-shopping"></i>

                </div>

                <h2>
                    Your cart is empty
                </h2>

                <p>
                    Add something delicious from
                    the menu to get started.
                </p>

                <button
                    type="button"
                    id="emptyCartShopBtn"
                >
                    Browse Menu
                </button>

            </div>

        `;


        const shopButton =
            document.getElementById(
                "emptyCartShopBtn"
            );


        if (shopButton) {

            shopButton.addEventListener(
                "click",
                () => {

                    window.location.href =
                        "index.html";

                }
            );

        }


        return;

    }


    /* =====================================================
       CART ITEMS
    ===================================================== */

    container.innerHTML =

        cart.map(item => {

            const unitPrice =
                getCartItemUnitPrice(item);


            const totalPrice =
                getCartItemTotal(item);


            /* ---------------------------------------------
               ADD-ONS
            --------------------------------------------- */

            const addons =

                Array.isArray(item.addOns)

                    ? item.addOns
                        .map(addon => {

                            return `

                                <span class="cart-addon">

                                    ${escapeHtml(
                                        addon.name
                                    )}

                                    (+₱${Number(
                                        addon.price
                                    ).toFixed(2)})

                                </span>

                            `;

                        })
                        .join("")

                    : "";


            return `

                <article
                    class="cart-item"
                    data-id="${escapeHtml(item.id)}"
                >

                    <!-- IMAGE -->

                    <div class="item-image">

                        <i
                            class="fa-solid
                            ${getCategoryIcon(
                                item.category
                            )}"
                        ></i>

                    </div>


                    <!-- DETAILS -->

                    <div class="item-information">

                        <span class="item-category">

                            ${escapeHtml(
                                item.category ||
                                "General"
                            )}

                        </span>


                        <h3 class="item-name">

                            ${escapeHtml(
                                item.name
                            )}

                        </h3>


                        <div class="item-details">

                            <div>

                                Size:

                                <strong>

                                    ${escapeHtml(
                                        item.size ||
                                        "Large"
                                    )}

                                </strong>

                                ·

                                ₱${unitPrice.toFixed(2)}

                                each

                            </div>


                            <!-- SUGAR -->

                            <div class="sugar-level">

                                <i class="fa-solid fa-cube"></i>

                                Sugar:

                                ${escapeHtml(
                                    item.sugarLevel ||
                                    "50%"
                                )}

                            </div>


                            ${
                                addons
                                    ? `

                                    <div class="addons">

                                        ${addons}

                                    </div>

                                    `
                                    : ""
                            }

                        </div>

                    </div>


                    <!-- ACTIONS -->

                    <div class="item-actions">


                        <!-- PRICE -->

                        <strong class="item-price">

                            ₱${totalPrice.toFixed(2)}

                        </strong>


                        <!-- QUANTITY -->

                        <div class="quantity-control">

                            <button
                                type="button"
                                class="cart-quantity-btn"
                                data-action="decrease"
                                data-id="${escapeHtml(
                                    item.id
                                )}"
                                aria-label="Decrease quantity"
                            >
                                −
                            </button>


                            <span>

                                ${Number(
                                    item.quantity
                                ) || 1}

                            </span>


                            <button
                                type="button"
                                class="cart-quantity-btn"
                                data-action="increase"
                                data-id="${escapeHtml(
                                    item.id
                                )}"
                                aria-label="Increase quantity"
                            >
                                +
                            </button>

                        </div>


                        <!-- EDIT / REMOVE -->

                        <div class="item-buttons">

                            <button
                                type="button"
                                class="edit-btn"
                                data-edit-id="${escapeHtml(
                                    item.id
                                )}"
                            >

                                <i class="fa-solid fa-pen"></i>

                                Edit

                            </button>


                            <button
                                type="button"
                                class="remove-btn"
                                data-remove-id="${escapeHtml(
                                    item.id
                                )}"
                            >

                                <i class="fa-solid fa-trash"></i>

                                Remove

                            </button>

                        </div>


                    </div>

                </article>

            `;

        }).join("");

}


/* =========================================================
   UPDATE CART BADGE
========================================================= */

function updateCartBadge() {

    const badges =
        document.querySelectorAll(
            ".cart-count, #cartBadge"
        );


    const quantity =
        getCartQuantity();


    badges.forEach(badge => {

        badge.textContent =
            quantity;


        if (quantity > 0) {

            badge.classList.remove(
                "hidden"
            );

        } else {

            badge.classList.add(
                "hidden"
            );

        }

    });

}


/* =========================================================
   UPDATE QUANTITY
========================================================= */

function updateCartQuantity(
    itemId,
    change
) {

    const item =
        cart.find(
            product =>
                String(product.id) ===
                String(itemId)
        );


    if (!item) {
        return;
    }


    const currentQuantity =
        Number(item.quantity) || 1;


    item.quantity =
        Math.max(
            1,
            currentQuantity + change
        );


    saveCart();

    renderCart();

    updateCartBadge();

}


/* =========================================================
   REMOVE ITEM
========================================================= */

function removeCartItem(itemId) {

    const index =
        cart.findIndex(
            item =>
                String(item.id) ===
                String(itemId)
        );


    if (index === -1) {
        return;
    }


    const removedItem =
        cart[index];


    cart.splice(
        index,
        1
    );


    saveCart();

    renderCart();

    updateCartBadge();


    notify(
        "Removed from Cart",
        `${removedItem.name} was removed.`
    );

}


/* =========================================================
   CLEAR CART
========================================================= */

function clearCart() {

    if (cart.length === 0) {
        return;
    }


    const confirmed =
        confirm(
            "Are you sure you want to clear your cart?"
        );


    if (!confirmed) {
        return;
    }


    cart = [];


    saveCart();

    renderCart();

    updateCartBadge();


    notify(
        "Cart Cleared",
        "All items have been removed."
    );

}


/* =========================================================
   CREATE ADD-ON EDITOR
========================================================= */

function createEditAddonEditor() {

    const modal =
        document.getElementById(
            "editModal"
        );


    if (!modal) {
        return null;
    }


    /*
        If your HTML already contains:
        #editAddonsContainer

        we will use it.
    */

    let container =
        document.getElementById(
            "editAddonsContainer"
        );


    if (container) {
        return container;
    }


    /*
        Otherwise create it automatically.
    */

    const modalCard =
        modal.querySelector(
            ".modal-card"
        );


    if (!modalCard) {
        return null;
    }


    container =
        document.createElement(
            "div"
        );


    container.id =
        "editAddonsContainer";


    container.className =
        "edit-addons-container";


    container.innerHTML = `

        <div class="edit-addons-title">

            Add-ons

        </div>

        <div
            id="editAddonsOptions"
            class="edit-addons-options"
        ></div>

    `;


    /*
        Put add-ons before the buttons.
    */

    const saveButton =
        document.getElementById(
            "saveEditBtn"
        );


    if (saveButton) {

        const buttonParent =
            saveButton.parentElement;


        if (buttonParent) {

            modalCard.insertBefore(
                container,
                buttonParent
            );

        } else {

            modalCard.appendChild(
                container
            );

        }

    } else {

        modalCard.appendChild(
            container
        );

    }


    return container;

}


/* =========================================================
   RENDER EDIT ADD-ONS
========================================================= */

function renderEditAddons(item) {

    const container =
        createEditAddonEditor();


    if (!container) {
        return;
    }


    const options =
        document.getElementById(
            "editAddonsOptions"
        );


    if (!options) {
        return;
    }


    const selectedAddons =
        Array.isArray(item.addOns)
            ? item.addOns
            : [];


    options.innerHTML =

        AVAILABLE_ADDONS
            .map((addon, index) => {

                const isSelected =
                    selectedAddons.some(
                        selected =>

                            String(
                                selected.name
                            ).toLowerCase() ===

                            String(
                                addon.name
                            ).toLowerCase()
                    );


                return `

                    <label
                        class="edit-addon-option"
                    >

                        <input
                            type="checkbox"
                            class="edit-addon-checkbox"
                            data-addon-index="${index}"
                            ${isSelected
                                ? "checked"
                                : ""}
                        >

                        <span class="edit-addon-name">

                            ${escapeHtml(
                                addon.name
                            )}

                        </span>

                        <span class="edit-addon-price">

                            +₱${Number(
                                addon.price
                            ).toFixed(2)}

                        </span>

                    </label>

                `;

            })
            .join("");

}


/* =========================================================
   GET SELECTED EDIT ADD-ONS
========================================================= */

function getSelectedEditAddons() {

    const checkboxes =
        document.querySelectorAll(
            ".edit-addon-checkbox"
        );


    const selected = [];


    checkboxes.forEach(
        checkbox => {

            if (!checkbox.checked) {
                return;
            }


            const index =
                Number(
                    checkbox.dataset
                        .addonIndex
                );


            const addon =
                AVAILABLE_ADDONS[index];


            if (!addon) {
                return;
            }


            selected.push({
                name: addon.name,
                price: Number(addon.price)
            });

        }
    );


    return selected;

}


/* =========================================================
   OPEN EDIT MODAL
========================================================= */

function openEditModal(itemId) {

    const item =
        cart.find(
            product =>
                String(product.id) ===
                String(itemId)
        );


    if (!item) {
        return;
    }


    editingItemId =
        itemId;


    const editItemId =
        document.getElementById(
            "editItemId"
        );


    const editSize =
        document.getElementById(
            "editSize"
        );


    const editSugar =
        document.getElementById(
            "editSugarLevel"
        );


    if (editItemId) {

        editItemId.value =
            item.id;

    }


    if (editSize) {

        editSize.value =
            item.size ||
            "Large";

    }


    if (editSugar) {

        editSugar.value =
            item.sugarLevel ||
            "50%";

    }


    /*
        IMPORTANT:
        Load the existing add-ons
        into the edit modal.
    */

    renderEditAddons(item);


    const modal =
        document.getElementById(
            "editModal"
        );


    if (modal) {

        modal.classList.remove(
            "hidden"
        );


        modal.setAttribute(
            "aria-hidden",
            "false"
        );

    }

}


/* =========================================================
   CLOSE EDIT MODAL
========================================================= */

function closeEditModal() {

    const modal =
        document.getElementById(
            "editModal"
        );


    if (modal) {

        modal.classList.add(
            "hidden"
        );


        modal.setAttribute(
            "aria-hidden",
            "true"
        );

    }


    editingItemId =
        null;

}


/* =========================================================
   SAVE EDITED ITEM
========================================================= */

function saveEditedItem() {

    if (!editingItemId) {
        return;
    }


    const item =
        cart.find(
            product =>
                String(product.id) ===
                String(editingItemId)
        );


    if (!item) {
        return;
    }


    const editSize =
        document.getElementById(
            "editSize"
        );


    const editSugar =
        document.getElementById(
            "editSugarLevel"
        );


    /* =====================================================
       SAVE SIZE
    ===================================================== */

    if (editSize) {

        item.size =
            editSize.value;

    }


    /* =====================================================
       SAVE SUGAR
    ===================================================== */

    if (editSugar) {

        item.sugarLevel =
            editSugar.value;

    }


    /* =====================================================
       SAVE ADD-ONS
    ===================================================== */

    item.addOns =
        getSelectedEditAddons();


    /* =====================================================
       SAVE EVERYTHING
    ===================================================== */

    saveCart();

    renderCart();

    updateCartBadge();

    closeEditModal();


    notify(
        "Item Updated",
        `${item.name} was updated successfully.`
    );

}


/* =========================================================
   OPEN CHECKOUT
========================================================= */

function openCheckout() {

    if (cart.length === 0) {

        notify(
            "Empty Cart",
            "Please add an item first."
        );

        return;
    }


    const modal =
        document.getElementById(
            "checkoutModal"
        );


    const modalTotal =
        document.getElementById(
            "modalTotal"
        );


    const pickupSelect =
        document.getElementById(
            "pickupTime"
        );


    if (modalTotal) {

        modalTotal.textContent =
            `₱${getCartSubtotal().toFixed(2)}`;

    }


    if (pickupSelect) {

        pickupSelect.value =
            pickupTime ||
            localStorage.getItem(
                "pickupTime"
            ) ||
            "";

    }


    if (modal) {

        modal.classList.remove(
            "hidden"
        );


        modal.setAttribute(
            "aria-hidden",
            "false"
        );

    }

}


/* =========================================================
   CLOSE CHECKOUT
========================================================= */

function closeCheckout() {

    const modal =
        document.getElementById(
            "checkoutModal"
        );


    if (modal) {

        modal.classList.add(
            "hidden"
        );


        modal.setAttribute(
            "aria-hidden",
            "true"
        );

    }

}


/* =========================================================
   CONFIRM CHECKOUT
========================================================= */

function confirmCheckout() {

    const pickupSelect =
        document.getElementById(
            "pickupTime"
        );


    pickupTime =
        pickupSelect
            ? pickupSelect.value
            : "";


    /* =====================================================
       REQUIRE PICKUP TIME
    ===================================================== */

    if (!pickupTime) {

        notify(
            "Pickup Time Required",
            "Please select a pickup time."
        );


        if (pickupSelect) {

            pickupSelect.focus();

        }


        return;

    }


    /*
        Save ONE pickup time
        for the whole checkout.
    */

    localStorage.setItem(
        "pickupTime",
        pickupTime
    );


    /* =====================================================
       CREATE ORDER
    ===================================================== */

    const order = {

        id:
            "ORDER-" +
            Date.now(),

        items:

            cart.map(item => ({

                ...item,

                addOns:

                    Array.isArray(
                        item.addOns
                    )

                        ? item.addOns.map(
                            addon => ({
                                ...addon
                            })
                        )

                        : []

            })),

        /*
            Pickup time belongs
            to the entire order.
        */

        pickupTime:
            pickupTime,

        total:
            getCartSubtotal(),

        createdAt:
            new Date().toISOString()

    };


    /* =====================================================
       SAVE ORDER HISTORY
    ===================================================== */

    try {

        const savedOrders =
            JSON.parse(
                localStorage.getItem(
                    ORDER_STORAGE_KEY
                ) || "[]"
            );


        const orders =
            Array.isArray(savedOrders)
                ? savedOrders
                : [];


        orders.push(order);


        localStorage.setItem(
            ORDER_STORAGE_KEY,
            JSON.stringify(orders)
        );


    } catch (error) {

        console.error(
            "Unable to save order:",
            error
        );

    }


    /* =====================================================
       SUCCESS
    ===================================================== */

    notify(
        "Order Successful",
        `Pickup time: ${pickupTime}`
    );


    /* =====================================================
       CLEAR CART
    ===================================================== */

    cart = [];


    saveCart();

    renderCart();

    updateCartBadge();

    updatePickupTimeDisplay();


    /* =====================================================
       CLOSE MODAL
    ===================================================== */

    closeCheckout();


    /*
        Do not immediately erase the
        saved pickup time because the
        summary can still display it.
    */

}


/* =========================================================
   TOAST
========================================================= */

function notify(
    title,
    message
) {

    const container =
        document.getElementById(
            "toastContainer"
        );


    if (!container) {
        return;
    }


    const toast =
        document.createElement(
            "div"
        );


    toast.className =
        "toast";


    toast.innerHTML = `

        <strong>

            ${escapeHtml(title)}

        </strong>

        <span>

            ${escapeHtml(message)}

        </span>

    `;


    container.appendChild(
        toast
    );


    setTimeout(
        () => {

            toast.remove();

        },
        3500
    );

}


/* =========================================================
   EVENT LISTENERS
========================================================= */

function setupEventListeners() {


    /* =====================================================
       CLEAR CART
    ===================================================== */

    const clearCartBtn =
        document.getElementById(
            "clearCartBtn"
        );


    if (clearCartBtn) {

        clearCartBtn.addEventListener(
            "click",
            clearCart
        );

    }


    /* =====================================================
       CHECKOUT
    ===================================================== */

    const checkoutBtn =
        document.getElementById(
            "checkoutBtn"
        );


    if (checkoutBtn) {

        checkoutBtn.addEventListener(
            "click",
            openCheckout
        );

    }


    /* =====================================================
       CLOSE CHECKOUT
    ===================================================== */

    const closeCheckoutBtn =
        document.getElementById(
            "closeCheckoutBtn"
        );


    if (closeCheckoutBtn) {

        closeCheckoutBtn.addEventListener(
            "click",
            closeCheckout
        );

    }


    /* =====================================================
       CANCEL CHECKOUT
    ===================================================== */

    const cancelCheckoutBtn =
        document.getElementById(
            "cancelCheckoutBtn"
        );


    if (cancelCheckoutBtn) {

        cancelCheckoutBtn.addEventListener(
            "click",
            closeCheckout
        );

    }


    /* =====================================================
       CONFIRM CHECKOUT
    ===================================================== */

    const confirmCheckoutBtn =
        document.getElementById(
            "confirmCheckoutBtn"
        );


    if (confirmCheckoutBtn) {

        confirmCheckoutBtn.addEventListener(
            "click",
            confirmCheckout
        );

    }


    /* =====================================================
       PICKUP TIME
    ===================================================== */

    const pickupSelect =
        document.getElementById(
            "pickupTime"
        );


    if (pickupSelect) {

        pickupSelect.addEventListener(
            "change",
            () => {

                pickupTime =
                    pickupSelect.value;

            }
        );

    }


    /* =====================================================
       EDIT MODAL
    ===================================================== */

    const closeEditBtn =
        document.getElementById(
            "closeEditBtn"
        );


    if (closeEditBtn) {

        closeEditBtn.addEventListener(
            "click",
            closeEditModal
        );

    }


    const cancelEditBtn =
        document.getElementById(
            "cancelEditBtn"
        );


    if (cancelEditBtn) {

        cancelEditBtn.addEventListener(
            "click",
            closeEditModal
        );

    }


    const saveEditBtn =
        document.getElementById(
            "saveEditBtn"
        );


    if (saveEditBtn) {

        saveEditBtn.addEventListener(
            "click",
            saveEditedItem
        );

    }


    /* =====================================================
       CART ITEM BUTTONS
    ===================================================== */

    const cartContainer =
        document.getElementById(
            "cartItemsContainer"
        );


    if (cartContainer) {

        cartContainer.addEventListener(
            "click",
            event => {


                /* -----------------------------------------
                   QUANTITY
                ----------------------------------------- */

                const quantityButton =
                    event.target.closest(
                        ".cart-quantity-btn"
                    );


                if (quantityButton) {

                    const id =
                        quantityButton.dataset.id;


                    const action =
                        quantityButton.dataset.action;


                    updateCartQuantity(

                        id,

                        action === "increase"
                            ? 1
                            : -1

                    );


                    return;

                }


                /* -----------------------------------------
                   REMOVE
                ----------------------------------------- */

                const removeButton =
                    event.target.closest(
                        ".remove-btn"
                    );


                if (removeButton) {

                    removeCartItem(
                        removeButton.dataset.removeId
                    );


                    return;

                }


                /* -----------------------------------------
                   EDIT
                ----------------------------------------- */

                const editButton =
                    event.target.closest(
                        ".edit-btn"
                    );


                if (editButton) {

                    openEditModal(
                        editButton.dataset.editId
                    );

                }

            }
        );

    }


    /* =====================================================
       CHECKOUT BACKDROP
    ===================================================== */

    const checkoutModal =
        document.getElementById(
            "checkoutModal"
        );


    if (checkoutModal) {

        checkoutModal.addEventListener(
            "click",
            event => {

                if (
                    event.target ===
                    checkoutModal
                ) {

                    closeCheckout();

                }

            }
        );

    }


    /* =====================================================
       EDIT BACKDROP
    ===================================================== */

    const editModal =
        document.getElementById(
            "editModal"
        );


    if (editModal) {

        editModal.addEventListener(
            "click",
            event => {

                if (
                    event.target ===
                    editModal
                ) {

                    closeEditModal();

                }

            }
        );

    }


    /* =====================================================
       ESC KEY
    ===================================================== */

    document.addEventListener(
        "keydown",
        event => {

            if (
                event.key !==
                "Escape"
            ) {

                return;

            }


            closeCheckout();

            closeEditModal();

        }
    );

}


/* =========================================================
   INITIALIZE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    () => {

        loadCart();

        renderCart();

        updateCartBadge();

        updatePickupTimeDisplay();

        setupEventListeners();

    }
);