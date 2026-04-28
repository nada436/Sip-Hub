/* ═══════════════════════════════════════════════════
   CART DRAWER ENGINE  —  assets/js/cart.js
   Depends on: cart_action.php (same views/user_pages/ folder)
═══════════════════════════════════════════════════ */
const CART_URL = "cart_action.php";

const overlay = document.getElementById("cartOverlay");
const drawer = document.getElementById("cartDrawer");
const drawerEmpty = document.getElementById("drawerEmpty");
const drawerItems = document.getElementById("drawerItems");
const drawerFooter = document.getElementById("drawerFooter");
const drawerTotal = document.getElementById("drawerTotal");
const drawerBadge = document.getElementById("drawerBadge");
const placeBtn = document.getElementById("placeOrderBtn");

/* ── Open / Close ── */
function openCart() {
  overlay.classList.add("open");
  drawer.classList.add("open");
}
function closeCart() {
  overlay.classList.remove("open");
  drawer.classList.remove("open");
}

overlay.addEventListener("click", closeCart);
document.getElementById("closeCartBtn").addEventListener("click", closeCart);
document
  .getElementById("navCartBtn")
  ?.addEventListener("click", () => fetchCart().then(openCart));

/* ── Swipe-down to close on mobile ── */
let _ty = 0;
drawer.addEventListener(
  "touchstart",
  (e) => {
    _ty = e.touches[0].clientY;
  },
  { passive: true },
);
drawer.addEventListener("touchend", (e) => {
  if (e.changedTouches[0].clientY - _ty > 70) closeCart();
});

/* ── XSS helper ── */
function esc(s) {
  return String(s ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

/* ── Render cart data into the drawer ── */
function renderCart(data) {
  const items = data.items || [];
  const qty = data.total_qty || 0;
  const price = data.total_price || 0;

  updateCartBadge(qty);

  if (items.length === 0) {
    drawerEmpty.style.display = "";
    drawerItems.innerHTML = "";
    drawerFooter.style.display = "none";
    drawerBadge.textContent = "";
    return;
  }

  drawerEmpty.style.display = "none";
  drawerFooter.style.display = "";
  drawerBadge.textContent = qty + (qty === 1 ? " item" : " items");
  drawerTotal.textContent = "EGP " + parseFloat(price).toFixed(2);

  drawerItems.innerHTML = items
    .map(
      (item) => `
    <div class="cart-item" id="ci-${item.product_id}">
      <img class="cart-item-img"
           src="../../${esc(item.image)}"
           alt="${esc(item.name)}"
           onerror="this.src='https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=200&q=70'"/>
      <div class="cart-item-info">
        <div class="cart-item-name">${esc(item.name)}</div>
        <div class="cart-item-price">EGP ${parseFloat(item.price).toFixed(2)} each</div>
      </div>
      <div class="cart-item-qty">
        <button class="cq-btn" onclick="cartUpdate(${item.product_id}, ${item.quantity - 1})">−</button>
        <span class="cq-num">${item.quantity}</span>
        <button class="cq-btn" onclick="cartUpdate(${item.product_id}, ${item.quantity + 1})">+</button>
      </div>
      <div class="cart-item-sub">EGP ${parseFloat(item.subtotal).toFixed(2)}</div>
      <button class="cart-item-remove" onclick="cartRemove(${item.product_id})" title="Remove">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
  `,
    )
    .join("");
}

/* ── Generic API call helper ── */
function api(pairs) {
  const fd = new FormData();
  Object.entries(pairs).forEach(([k, v]) => fd.append(k, v));
  return fetch(CART_URL, { method: "POST", body: fd }).then((r) => r.json());
}

function fetchCart() {
  return api({ action: "get" }).then((data) => {
    renderCart(data);
    return data;
  });
}

/* Exposed so Products.php can open the drawer after adding an item */
window.openCartDrawer = function (data) {
  renderCart(data);
  openCart();
};

function cartUpdate(pid, qty) {
  api({
    action: qty < 1 ? "remove" : "update",
    product_id: pid,
    quantity: Math.max(0, qty),
  }).then(renderCart);
}

function cartRemove(pid) {
  const el = document.getElementById("ci-" + pid);
  if (el) {
    el.style.opacity = ".35";
    el.style.pointerEvents = "none";
  }
  api({ action: "remove", product_id: pid }).then(renderCart);
}

/* ── Place order ── */
placeBtn.addEventListener("click", () => {
  placeBtn.disabled = true;
  placeBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Placing…';

  api({ action: "place_order" })
    .then((data) => {
      if (data.ok) {
        closeCart();
        document.getElementById("successOrderId").textContent =
          "Order #" + data.order_id;
        document.getElementById("orderSuccess").classList.add("show");
        updateCartBadge(0);
      } else {
        alert(data.msg || "Something went wrong. Please try again.");
      }
    })
    .catch(() => {
      alert("Network error. Please check your connection.");
    })
    .finally(() => {
      placeBtn.disabled = false;
      placeBtn.innerHTML = '<i class="bi bi-bag-check-fill"></i> Place Order';
    });
});

document.getElementById("successContinueBtn").addEventListener("click", () => {
  document.getElementById("orderSuccess").classList.remove("show");
});

/* ── Navbar badge ── */
window.updateCartBadge = function (count) {
  const badge = document.querySelector(".cart-badge");
  if (!badge) return;
  badge.textContent = count;
  badge.style.display = count > 0 ? "flex" : "none";
};

/* ── Toast notification ── */
window.showToast = function (msg) {
  const t = document.getElementById("cartToast");
  document.getElementById("cartToastMsg").textContent = msg;
  t.classList.add("show");
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove("show"), 2400);
};

/* Load cart count on page load */
fetchCart();
