// ---- CART STATE ----
let cart = [];
let couponApplied = false;
let pointsApplied = false;
const POINTS_VALUE = Number(window.POINTS_VALUE || 0);

// ---- FILTER CHIPS ----
document.querySelectorAll('.chip').forEach(chip => {
  chip.addEventListener('click', function () {
    document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
    this.classList.add('active');
    const key = this.dataset.filter;
    document.querySelectorAll('[data-category]').forEach(col => {
      if (key === 'all') { col.style.display = ''; }
      else {
        const cats = col.dataset.category.split(' ');
        col.style.display = cats.includes(key) ? '' : 'none';
      }
    });
  });
});

// ---- VIEW RECOMMENDATIONS ----
const recommendationsBtn = document.getElementById('view-recommendations-btn');

if (recommendationsBtn) {
  recommendationsBtn.addEventListener('click', function () {
    const vetChip = document.querySelector('.chip[data-filter="vet-recommended"]');

    if (vetChip) {
      vetChip.click();
    }

    const firstVetProduct = document.querySelector('[data-category~="vet-recommended"]');

    if (firstVetProduct) {
      firstVetProduct.scrollIntoView({
        behavior: 'smooth',
        block: 'center'
      });
    }
  });
}

// ---- CHANGE DATE ----
document.querySelectorAll('.change-date-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const card = this.closest('.ship-card');
    const input = card.querySelector('.delivery-date');
    const text = card.querySelector('.delivery-text');
    const label = card.querySelector('.adjust-label');
    const originalDate = new Date(input.defaultValue);

    if (input.style.display === 'inline-block') {
      input.style.display = 'none';
      this.textContent = 'Change Delivery';

      const newDate = new Date(input.value);
      text.textContent = newDate.toLocaleDateString('en-US', {
        weekday: 'short',
        month: 'short',
        day: 'numeric',
        year: 'numeric'
      });

      const diffDays = Math.round((newDate - originalDate) / (1000 * 60 * 60 * 24));
      label.textContent = diffDays === 0 ? '(Predicted)' : `(You Adjusted ${diffDays > 0 ? '+' : ''}${diffDays} days)`;
    } else {
      input.style.display = 'inline-block';
      input.focus();
      this.textContent = 'Save';
    }
  });
});

// ---- ADD TO CART ----
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
  btn.addEventListener('click', function () {
    const d = this.dataset;
    // Find the product image src
    const productCard = this.closest('.product');
    const imgEl = productCard ? productCard.querySelector('.product-img img') : null;
    const imgSrc = imgEl ? imgEl.src : null;

    const existing = cart.find(i => i.name === d.name);
    if (existing) {
      existing.qty++;
    } else {
      cart.push({
        name: d.name, brand: d.brand,
        price: parseFloat(d.price),
        old: d.old ? parseFloat(d.old) : null,
        emoji: d.emoji, bg: d.bg,
        img: imgSrc,
        badge: d.badge, badgeLabel: d.badgeLabel,
        pts: parseInt(d.pts),
        taskPoints: parseInt(d.taskPoints || '0'),
        isAutoShip: d.autoShip === '1',
        qty: 1
      });
    }
    renderCart();

    // Button feedback
    const orig = this.innerHTML;
    this.innerHTML = '<i class="bi bi-check-lg"></i> Added!';
    this.style.background = 'var(--green)';
    setTimeout(() => { this.innerHTML = orig; this.style.background = ''; }, 1200);
  });
});

// ---- RENDER CART ----
function renderCart() {

  const list = document.getElementById('cart-items-list');
  const empty = document.getElementById('cart-empty');
  const summary = document.getElementById('cart-summary');
  const badge = document.getElementById('cart-header-badge');
  const countBadge = document.getElementById('cart-count-badge');

  const totalItems = cart.reduce((s, i) => s + i.qty, 0);
  badge.textContent = totalItems;
  countBadge.textContent = totalItems + (totalItems === 1 ? ' item' : ' items');

  if (cart.length === 0) {
    list.innerHTML = '';
    empty.style.display = 'block';
    summary.style.display = 'none';
    return;
  }
  empty.style.display = 'none';
  summary.style.display = 'block';

  list.innerHTML = cart.map((item, idx) => {
    const badgeHTML = item.badge ? `<span class="mini-badge ${item.badge}">${item.badgeLabel}</span>` : '';
    const oldHTML = item.old ? `<div class="old-price">was EGP ${item.old.toLocaleString('en-EG')}</div>` : '';
    const lineTotal = (item.price * item.qty).toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const imgContent = item.img
      ? `<img src="${item.img}" alt="${item.name}" style="width:100%;height:100%;object-fit:cover;border-radius:12px;">`
      : item.emoji;
    return `
      <div class="cart-item" id="cart-item-${idx}">
        <div class="cart-item-emoji ${item.img ? '' : item.bg}" style="overflow:hidden;border-radius:12px;">${imgContent}</div>
        <div class="cart-item-info">
          <p class="cart-item-name">${item.name}</p>
          <p class="cart-item-brand">${item.brand}</p>
          <div class="cart-item-badges">${badgeHTML}</div>
        </div>
        <div class="qty-control">
          <button class="qty-btn" onclick="changeQty(${idx}, -1)">−</button>
          <span class="qty-num">${item.qty}</span>
          <button class="qty-btn" onclick="changeQty(${idx}, 1)">+</button>
        </div>
        <div class="cart-item-price">
          <div class="main-price">EGP ${lineTotal}</div>
          ${oldHTML}
          <div class="points-earn">+${item.pts * item.qty} pts</div>
        </div>
        <button class="remove-btn" onclick="removeItem(${idx})" title="Remove"><i class="bi bi-x-lg"></i></button>
      </div>`;
  }).join('');

  updateSummary();

}

function changeQty(idx, delta) {
  cart[idx].qty += delta;
  if (cart[idx].qty <= 0) cart.splice(idx, 1);
  renderCart();
}

function removeItem(idx) {
  const el = document.getElementById(`cart-item-${idx}`);
  if (el) {
    el.style.opacity = '0';
    el.style.transform = 'translateX(30px)';
    el.style.transition = 'all .25s';
    setTimeout(() => { cart.splice(idx, 1); renderCart(); }, 230);
  } else {
    cart.splice(idx, 1); renderCart();
  }
}

function clearCart() {
  cart = [];
  couponApplied = false;
  pointsApplied = false;
  document.getElementById('coupon-ok').style.display = 'none';
  document.getElementById('coupon-input').value = '';
  const pb = document.getElementById('use-pts-btn');
  pb.textContent = 'Use Points'; pb.classList.remove('applied');
  renderCart();
}

function formatEGP(amount) {
  return 'EGP ' + amount.toLocaleString('en-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function updateSummary() {
  const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
  const savedDiscount = cart.reduce((s, i) => {
    return s + (i.old ? (i.old - i.price) * i.qty : 0);
  }, 0);
  const totalPts = cart.reduce((s, i) => s + i.pts * i.qty, 0);
  const totalItems = cart.reduce((s, i) => s + i.qty, 0);

  document.getElementById('sum-items').textContent = totalItems;
  document.getElementById('sum-subtotal').textContent = formatEGP(subtotal);
  document.getElementById('pts-to-earn').textContent = totalPts + ' pts';
  document.getElementById('checkout-pts-note').textContent = `(+${totalPts} pts)`;

  const discountRow = document.getElementById('sum-discount-row');
  if (savedDiscount > 0) {
    discountRow.style.display = 'flex';
    document.getElementById('sum-discount').textContent = '-' + formatEGP(savedDiscount);
  } else {
    discountRow.style.display = 'none';
  }

  let total = subtotal;

  const couponRow = document.getElementById('sum-coupon-row');
  let couponAmt = 0;
  if (couponApplied) {
    couponAmt = subtotal * 0.10;
    couponRow.style.display = 'flex';
    document.getElementById('sum-coupon').textContent = '-' + formatEGP(couponAmt);
    total -= couponAmt;
  } else {
    couponRow.style.display = 'none';
  }

  const ptsRow = document.getElementById('sum-pts-row');
  if (pointsApplied) {
    ptsRow.style.display = 'flex';
    total = Math.max(0, total - POINTS_VALUE);
  } else {
    ptsRow.style.display = 'none';
  }

  document.getElementById('sum-total').textContent = formatEGP(total);
}

function applyCoupon() {
  const val = document.getElementById('coupon-input').value.trim().toUpperCase();
  const ok = document.getElementById('coupon-ok');
  if (val === 'MILO10') {
    couponApplied = true;
    ok.style.display = 'flex';
    updateSummary();
  } else {
    ok.style.display = 'none';
    couponApplied = false;
    document.getElementById('coupon-input').style.borderColor = '#f1c9c9';
    setTimeout(() => document.getElementById('coupon-input').style.borderColor = '', 1500);
    updateSummary();
  }
}

function togglePoints() {
  pointsApplied = !pointsApplied;
  const btn = document.getElementById('use-pts-btn');
  btn.textContent = pointsApplied ? '✓ Applied' : 'Use Points';
  btn.classList.toggle('applied', pointsApplied);
  updateSummary();
}

function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  if (!toast) return;

  toast.textContent = message;
  toast.classList.add('show');

  if (type === 'error') {
    toast.style.background = '#b54848';
  } else {
    toast.style.background = 'var(--ink)';
  }

  setTimeout(() => {
    toast.classList.remove('show');
  }, 3500);
}


function checkout() {
  if (cart.length === 0) {
    showToast('Your cart is empty.', 'error');
    return;
  }

  const formData = new FormData();
  formData.append('action', 'checkout');
  formData.append('items', JSON.stringify(cart));
  formData.append('points_applied', pointsApplied ? '1' : '0');

  fetch(window.location.href, {
    method: 'POST',
    body: formData
  })
    .then(res => res.json())
    .then(data => {
      if (!data.ok) {
        showToast(data.message || 'Order failed.', 'error');
        return;
      }

      showToast(`Order #${data.order_id} placed. You earned ${data.earned_points} points.`);
      clearCart();

      setTimeout(() => {
        window.location.reload();
      }, 1200);
    })
    .catch(() => {
      showToast('Order failed. Please try again.', 'error');
    });
}



// Initial render
renderCart();

