const CART_KEY = "yourChoiceCart";

const cartToggle = document.getElementById("cartToggle");
const cartClose = document.getElementById("cartClose");
const cartDrawer = document.getElementById("cartDrawer");
const cartOverlay = document.getElementById("cartOverlay");
const cartItems = document.getElementById("cartItems");
const cartCount = document.getElementById("cartCount");
const cartTotal = document.getElementById("cartTotal");
const addCartButtons = document.querySelectorAll(".add-cart-btn");

const loginOpen = document.getElementById("loginOpen");
const loginClose = document.getElementById("loginClose");
const loginModal = document.getElementById("loginModal");
const openSignup = document.getElementById("openSignup");
const signupModal = document.getElementById("signupModal");
const signupClose = document.getElementById("signupClose");

const loginForm = document.getElementById("loginForm");
const signupForm = document.getElementById("signupForm");
const loginMessage = document.getElementById("loginMessage");
const signupMessage = document.getElementById("signupMessage");

const searchToggle = document.getElementById("searchToggle");
const searchBarWrap = document.getElementById("searchBarWrap");
const searchInput = document.getElementById("searchInput");
const searchBtn = document.getElementById("searchBtn");
const searchResults = document.getElementById("searchResults");
const searchMeta = document.getElementById("searchMeta");
const searchResultsSection = document.getElementById("searchResultsSection");

function getCart() {
  try {
    const saved = localStorage.getItem(CART_KEY);
    return saved ? JSON.parse(saved) : [];
  } catch (_) {
    return [];
  }
}

function saveCart(cart) {
  localStorage.setItem(CART_KEY, JSON.stringify(cart));
}

const getTotalCount = (cart) => cart.reduce((sum, item) => sum + item.qty, 0);
const getTotalPrice = (cart) => cart.reduce((sum, item) => sum + item.price * item.qty, 0);

function renderCart() {
  const cart = getCart();
  if (cartCount) cartCount.textContent = getTotalCount(cart);
  if (cartTotal) cartTotal.textContent = `$${getTotalPrice(cart).toLocaleString()}`;

  if (cartDrawer) cartDrawer.classList.toggle("has-items", cart.length > 0);

  if (!cartItems) return;

  if (!cart.length) {
    cartItems.innerHTML = "<p>Your cart is empty.</p>";
    return;
  }

  cartItems.innerHTML = cart
    .map(
      (item) => `<article class="cart-item"><div><strong>${item.name}</strong><small>Qty: ${item.qty}</small><small>$${item.price.toLocaleString()} each</small></div><button class="remove-item" type="button" data-id="${item.id}">Remove</button></article>`
    )
    .join("");

  cartItems.querySelectorAll(".remove-item").forEach((button) => {
    button.addEventListener("click", () => removeItem(button.dataset.id));
  });
}

function addItem(product) {
  const cart = getCart();
  const existing = cart.find((item) => item.id === product.id);
  if (existing) existing.qty += 1;
  else cart.push({ ...product, qty: 1 });
  saveCart(cart);
  renderCart();
  openCart();
}

function removeItem(id) {
  const updated = getCart()
    .map((item) => (item.id === id ? { ...item, qty: item.qty - 1 } : item))
    .filter((item) => item.qty > 0);
  saveCart(updated);
  renderCart();
}

const openCart = () => {
  cartDrawer?.classList.add("open");
  cartOverlay?.classList.add("show");
};
const closeCart = () => {
  cartDrawer?.classList.remove("open");
  cartOverlay?.classList.remove("show");
};

const openModal = (modal) => modal?.classList.add("show");
const closeModal = (modal) => modal?.classList.remove("show");

async function handleAuthSubmit(event, targetUrl, messageEl) {
  event.preventDefault();
  const form = event.currentTarget;
  const body = new FormData(form);

  try {
    const res = await fetch(targetUrl, { method: "POST", body });
    const data = await res.json();
    messageEl.textContent = data.message;
    if (data.success) {
      form.reset();
      setTimeout(() => {
        closeModal(loginModal);
        closeModal(signupModal);
      }, 400);
      if (targetUrl === 'login.php') location.reload();
    }
  } catch (_) {
    messageEl.textContent = "Server error. Please try again.";
  }
}

async function runSearch() {
  const query = searchInput?.value.trim() || "";
  if (!searchResults || !searchMeta || !searchResultsSection) return;
  if (!query) {
    searchResults.innerHTML = "";
    searchMeta.textContent = "Type and search for products.";
    return;
  }

  searchResultsSection.classList.remove("hidden");
  searchMeta.textContent = `Searching for "${query}"...`;

  try {
    const res = await fetch(`search.php?q=${encodeURIComponent(query)}`);
    const data = await res.json();
    if (!data.success) throw new Error("Search failed");

    if (!data.products.length) {
      searchResults.innerHTML = "<p>No products found.</p>";
      searchMeta.textContent = `No results for "${query}".`;
      return;
    }

    searchMeta.textContent = `${data.products.length} result(s) for "${query}".`;
    searchResults.innerHTML = data.products
      .map(
        (p) => `<article class="product-card"><img src="${p.image_url}" alt="${p.name}" /><div class="product-info"><h3>${p.name}</h3><p>${p.material || "Fine jewelry"}</p><div class="product-row"><strong>$${Number(p.price).toLocaleString()}</strong><button class="add-cart-btn" data-id="db-${p.id}" data-name="${p.name}" data-price="${Number(p.price)}" type="button">Add to Cart</button></div></div></article>`
      )
      .join("");

    searchResults.querySelectorAll(".add-cart-btn").forEach((button) => {
      button.addEventListener("click", () => {
        addItem({
          id: button.dataset.id,
          name: button.dataset.name,
          price: Number(button.dataset.price),
        });
      });
    });
  } catch (_) {
    searchMeta.textContent = "Unable to search right now.";
  }
}

addCartButtons.forEach((button) => {
  button.addEventListener("click", () => {
    addItem({ id: button.dataset.id, name: button.dataset.name, price: Number(button.dataset.price) });
  });
});

cartToggle?.addEventListener("click", openCart);
cartClose?.addEventListener("click", closeCart);
cartOverlay?.addEventListener("click", closeCart);

const paymentModal = document.getElementById("paymentModal");
const paymentModalClose = document.getElementById("paymentModalClose");
const paymentModalTotal = document.getElementById("paymentModalTotal");
const paymentModalMethod = document.getElementById("paymentModalMethod");
const paymentModalFields = document.getElementById("paymentModalFields");
const paymentModalMessage = document.getElementById("paymentModalMessage");
const paymentModalConfirm = document.getElementById("paymentModalConfirm");

function openPaymentModal() {
  if (paymentModal) paymentModal.classList.add("show");
}
function closePaymentModal() {
  if (paymentModal) paymentModal.classList.remove("show");
  if (paymentModalMessage) paymentModalMessage.textContent = "";
}

function buildPaymentFields(method) {
  if (!paymentModalFields) return;
  if (method === "card") {
    paymentModalFields.innerHTML = `
      <label for="payCardNumber">Card number</label>
      <input id="payCardNumber" type="text" placeholder="1234 5678 9012 3456" maxlength="19" />
      <label for="payCardExp">Expiry</label>
      <input id="payCardExp" type="text" placeholder="MM/YY" maxlength="5" />
      <label for="payCardCvv">CVV</label>
      <input id="payCardCvv" type="text" placeholder="123" maxlength="4" />
    `;
  } else if (method === "esewa") {
    paymentModalFields.innerHTML = `
      <label for="payEsewaId">eSewa ID / Mobile number</label>
      <input id="payEsewaId" type="text" placeholder="98XXXXXXXX" />
      <label for="payEsewaPin">eSewa PIN</label>
      <input id="payEsewaPin" type="password" placeholder="••••••" />
    `;
  } else {
    paymentModalFields.innerHTML = `<p class="payment-cod-note">Pay when your order is delivered. No details needed.</p>`;
  }
}

document.addEventListener("click", (e) => {
  const btn = e.target.closest("#cartCheckoutBtn");
  if (!btn) return;
  e.preventDefault();
  const cart = getCart();
  if (!cart.length) return;
  const method = document.querySelector('input[name="cartPayment"]:checked')?.value || "card";
  const total = getTotalPrice(cart);
  const methodLabel = method === "card" ? "Credit / Debit card" : method === "esewa" ? "eSewa" : "Cash on delivery";
  if (paymentModalTotal) paymentModalTotal.textContent = "Total: $" + total.toLocaleString();
  if (paymentModalMethod) paymentModalMethod.textContent = "Paying with " + methodLabel;
  const confirmBtn = paymentModalConfirm;
  if (confirmBtn) confirmBtn.textContent = method === "cod" ? "Confirm Order" : "Pay Now";
  buildPaymentFields(method);
  openPaymentModal();
});

paymentModalClose?.addEventListener("click", closePaymentModal);
paymentModal?.addEventListener("click", (e) => { if (e.target === paymentModal) closePaymentModal(); });

paymentModalConfirm?.addEventListener("click", () => {
  if (!paymentModalMessage) return;
  paymentModalMessage.style.color = "#2d7a3e";
  paymentModalMessage.textContent = "Payment successful! Thank you for your order.";
  paymentModalConfirm.textContent = "Done";
  paymentModalConfirm.disabled = true;
  setTimeout(() => {
    closePaymentModal();
    closeCart();
    saveCart([]);
    renderCart();
    paymentModalConfirm.textContent = "Pay Now";
    paymentModalConfirm.disabled = false;
  }, 1800);
});

loginOpen?.addEventListener("click", () => openModal(loginModal));
loginClose?.addEventListener("click", () => closeModal(loginModal));
openSignup?.addEventListener("click", () => {
  closeModal(loginModal);
  openModal(signupModal);
});
signupClose?.addEventListener("click", () => closeModal(signupModal));

[loginModal, signupModal].forEach((modal) => {
  modal?.addEventListener("click", (event) => {
    if (event.target === modal) closeModal(modal);
  });
});

document.addEventListener("keydown", (event) => {
  if (event.key === "Escape") {
    closeCart();
    closeModal(loginModal);
    closeModal(signupModal);
    closePaymentModal();
    document.getElementById("userDropdownMenu")?.classList.remove("open");
  }
});

const userDropdownTrigger = document.getElementById("userDropdownTrigger");
const userDropdownMenu = document.getElementById("userDropdownMenu");
if (userDropdownTrigger && userDropdownMenu) {
  userDropdownTrigger.addEventListener("click", (e) => {
    e.stopPropagation();
    userDropdownMenu.classList.toggle("open");
    userDropdownTrigger.setAttribute("aria-expanded", userDropdownMenu.classList.contains("open"));
  });
  document.addEventListener("click", () => {
    userDropdownMenu.classList.remove("open");
    userDropdownTrigger.setAttribute("aria-expanded", "false");
  });
}

loginForm?.addEventListener("submit", (e) => handleAuthSubmit(e, "login.php", loginMessage));
signupForm?.addEventListener("submit", (e) => handleAuthSubmit(e, "register.php", signupMessage));

searchToggle?.addEventListener("click", () => {
  searchBarWrap?.classList.toggle("open");
  if (searchBarWrap?.classList.contains("open")) searchInput?.focus();
});
searchBtn?.addEventListener("click", runSearch);
searchInput?.addEventListener("keydown", (e) => {
  if (e.key === "Enter") runSearch();
});

renderCart();

// Featured Collection Carousel
(function initFeaturedCarousel() {
  const track = document.getElementById("featuredCarouselTrack");
  const dotsContainer = document.getElementById("featuredCarouselDots");
  const prevBtn = document.querySelector(".carousel-prev");
  const nextBtn = document.querySelector(".carousel-next");
  if (!track) return;

  const slides = track.querySelectorAll(".carousel-slide");
  let currentIndex = 0;
  let autoTimer = null;
  const AUTO_INTERVAL = 4500;

  function getItemsPerView() {
    return window.innerWidth <= 700 ? 1 : window.innerWidth <= 980 ? 2 : 3;
  }

  function getTotalSlides() {
    return Math.max(1, Math.ceil(slides.length / getItemsPerView()));
  }

  function updateLayout() {
    const viewport = track.parentElement;
    const perView = getItemsPerView();
    const gap = 16;
    const slideWidth = (viewport.offsetWidth - gap * (perView - 1)) / perView;
    track.querySelectorAll(".carousel-slide").forEach((el) => {
      el.style.flex = `0 0 ${slideWidth}px`;
      el.style.minWidth = `${slideWidth}px`;
    });
  }

  function goToSlide(index) {
    const total = getTotalSlides();
    currentIndex = ((index % total) + total) % total;
    const viewport = track.parentElement;
    const slideWidth = viewport.offsetWidth / getItemsPerView();
    track.style.transform = `translateX(-${currentIndex * viewport.offsetWidth}px)`;
    dotsContainer.querySelectorAll("button").forEach((btn, i) => btn.classList.toggle("active", i === currentIndex));
  }

  function buildDots() {
    dotsContainer.innerHTML = "";
    const total = getTotalSlides();
    for (let i = 0; i < total; i++) {
      const btn = document.createElement("button");
      btn.type = "button";
      btn.setAttribute("aria-label", `Go to slide ${i + 1}`);
      btn.addEventListener("click", () => {
        goToSlide(i);
        resetAuto();
      });
      dotsContainer.appendChild(btn);
    }
    goToSlide(0);
  }

  function next() {
    goToSlide(currentIndex + 1);
    resetAuto();
  }

  function prev() {
    goToSlide(currentIndex - 1);
    resetAuto();
  }

  function resetAuto() {
    clearInterval(autoTimer);
    autoTimer = setInterval(next, AUTO_INTERVAL);
  }

  if (prevBtn) prevBtn.addEventListener("click", prev);
  if (nextBtn) nextBtn.addEventListener("click", next);

  updateLayout();
  buildDots();
  autoTimer = setInterval(next, AUTO_INTERVAL);

  let resizeDebounce;
  window.addEventListener("resize", () => {
    clearTimeout(resizeDebounce);
    resizeDebounce = setTimeout(() => {
      updateLayout();
      buildDots();
      goToSlide(Math.min(currentIndex, getTotalSlides() - 1));
    }, 150);
  });
})();
