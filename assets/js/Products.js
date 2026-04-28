/* ═══════════════════════════════════════════════════
   PRODUCTS PAGE  —  assets/js/products.js
   Depends on: cart.js being loaded first (openCartDrawer, updateCartBadge, showToast)
═══════════════════════════════════════════════════ */

/* ── Qty steppers ── */
document.querySelectorAll(".product-card").forEach((card) => {
  const minus = card.querySelector(".qty-minus");
  const plus = card.querySelector(".qty-plus");
  const val = card.querySelector(".qty-value");

  plus.addEventListener("click", () => {
    val.textContent = +val.textContent + 1;
  });
  minus.addEventListener("click", () => {
    if (+val.textContent > 1) val.textContent = +val.textContent - 1;
  });
});

/* ── Add-to-cart ── */
document.querySelectorAll(".add-to-cart-btn").forEach((btn) => {
  btn.addEventListener("click", () => {
    const card = btn.closest(".product-card");
    const qty = +card.querySelector(".qty-value").textContent;
    const name = btn.dataset.name;

    btn.disabled = true;
    btn.innerHTML = '<i class="bi bi-hourglass-split"></i>';

    const fd = new FormData();
    fd.append("action", "add");
    fd.append("product_id", btn.dataset.id);
    fd.append("quantity", qty);

    fetch("cart_action.php", { method: "POST", body: fd })
      .then((r) => r.json())
      .then((data) => {
        if (data.ok) {
          updateCartBadge(data.total_qty);
          openCartDrawer(data);
          showToast('Added "' + name + '" to cart ✓');
          card.querySelector(".qty-value").textContent = 1;
        } else {
          alert(data.msg || "Could not add to cart.");
        }
      })
      .catch(() => alert("Network error. Please try again."))
      .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-cart-plus"></i> Add';
      });
  });
});
