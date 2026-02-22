# Project Changes Summary – Your Choice Jewelry (test-2)

Use this as a prompt or checklist so others can replicate or extend the same features.

---

## 1. Fix login server error (500)

- **config.php**: On database connection failure, return JSON `{ "success": false, "message": "Server error. Please try again." }` instead of plain-text `die()`. Remove the closing `?>` to avoid accidental output and "headers already sent" errors.
- **login.php**: After `db()`, check `$stmt` and `$result`; if `prepare()` or `get_result()` fails, return the same JSON error and exit. Remove closing `?>`.
- **register.php**: Remove closing `?>`.
- **Database setup**: Ensure MySQL is running and the database exists. Create `ecommerce_db` and run `schema.sql` (or use a one-time `setup.php` that creates the DB and runs the schema). Without the database, login/register will still 500.

---

## 2. Show username and logout after login

- **header.php**: Call `session_start()` (with `PHP_SESSION_NONE` check to avoid double start). When logged in, show: “Hi, [username]” and a “Logout” link; when not, show the “Login” button.
- **logout.php**: New file. Start session, clear `$_SESSION`, destroy session cookie, `session_destroy()`, then `header('Location: index.php')` and exit.
- **script.js**: On successful login (when `targetUrl === 'login.php'` and `data.success`), call `location.reload()` so the header updates.
- **styles.css**: Add `.nav-user` for the username text in the header.

---

## 3. User dropdown instead of plain Logout button

- **header.php**: Replace the single “Logout” link with a dropdown: a button “Hi, [username] ▾” that toggles a menu containing “Update Profile” (link to `profile.php`) and “Logout” (link to `logout.php`). Optionally add a “Profile” link in the main nav for logged-in users.
- **styles.css**: Add `.user-dropdown`, `.user-dropdown-trigger`, `.user-dropdown-menu`, `.user-dropdown-menu.open` (position absolute, below trigger; show/hide with opacity/visibility/transform).
- **script.js**: On click of `#userDropdownTrigger`, toggle class `open` on `#userDropdownMenu` and set `aria-expanded`. On click anywhere on `document`, remove `open` and set `aria-expanded="false"`. On Escape key, also close the dropdown.

---

## 4. Profile page and update profile

- **profile.php**: New file. Require login (redirect to index if not). Load current user’s name and email from DB; output a form with: Full Name, Email, Current Password (required), New Password (optional). Form submits via JS to `update_profile.php`.
- **update_profile.php**: New file. Session required; accept POST. Validate current password, then allow updating name and email (ensure new email is unique if changed). If new password is provided and length ≥ 6, update `password_hash`; otherwise leave password unchanged. On success, set `$_SESSION['user_name']` to new name and return JSON `{ "success": true, "message": "Profile updated successfully" }`.
- **script.js**: On profile form submit, `fetch('update_profile.php', { method: 'POST', body: new FormData(form) })`. On success, update the dropdown trigger text to “Hi, [new name] ▾” if the element exists.
- **styles.css**: Add `.profile-form-wrap`, `.profile-form` for the profile page layout.

---

## 5. Products on Shop and Collection from database

- **shop.php** and **collection.php**: At the top, `require_once config.php`, `$conn = db()`, run `SELECT id, name, price, material, image_url FROM products ORDER BY name`, fetch all rows. Include header, then in the main content replace placeholder with a products grid: for each product output a product card (image, name, material, price, “Add to Cart” button with `data-id="db-{id}"`, `data-name`, `data-price`). If no products, show “No products available yet.”

---

## 6. About Us page content

- **about.php**: Replace the single info card with multiple sections: hero with tagline; “Our Story” with text and image; “Our Values” (e.g. Craftsmanship, Sustainability, Authenticity) in a 3-column card layout; “Our Materials” with text and image; “Why Choose Us” with short CTA and link to shop.
- **styles.css**: Add `.about-grid`, `.about-copy`, `.about-image`, `.about-grid-reverse`, `.values-grid`, `.value-card`, `.about-cta`; keep responsive behavior for smaller screens.

---

## 7. Featured Collection carousel (manual + auto scroll)

- **index.php**: Load featured products from DB (same query as shop, e.g. `LIMIT 12`). Replace the static product grid with a carousel: a wrapper, prev/next buttons, a viewport with overflow hidden, and a track containing product cards with class `carousel-slide`. Add a dots container (e.g. `#featuredCarouselDots`) below.
- **styles.css**: Carousel wrapper (flex), viewport (overflow hidden), track (flex, gap, transition). Slide width: e.g. 33.33% desktop, 50% tablet, 100% mobile. Style prev/next buttons and dots.
- **script.js**: Carousel logic: `getItemsPerView()` (1/2/3 by width), `getTotalSlides()`, `updateLayout()` (set slide width from viewport), `goToSlide(index)` (translate track, update dots). Build dots on init and on resize. Prev/next click and `setInterval` for auto-advance (e.g. 4.5s). Reset auto timer on manual navigation. Use `data-id="db-{id}"` etc. so “Add to Cart” still works.

---

## 8. Payment method in cart

- **footer.php**: Inside the cart drawer, between cart items and the total footer, add a “Payment method” block. Radio options: “Credit / Debit card” (value `card`), “eSewa” (value `esewa`), “Cash on delivery” (value `cod`). Add a “Proceed to Checkout” button (e.g. `id="cartCheckoutBtn"`). Show this block only when the cart has items (e.g. parent has class `has-items`).
- **styles.css**: `.cart-payment` (border-top, padding). `.cart-drawer.has-items .cart-payment { display: block }`, else `display: none`. Style payment options (e.g. bordered rows, selected state) and full-width checkout button. Ensure cart items area can flex so payment and total sit at bottom.
- **script.js**: In `renderCart()`, add/remove class `has-items` on the cart drawer based on `cart.length > 0`.

---

## 9. Fake payment popup on Proceed to Checkout

- **footer.php**: Add a modal (e.g. `id="paymentModal"`) with: title “Complete Payment”, total line, “Paying with [method]” line, a container for dynamic fields (`id="paymentModalFields"`), message area, “Pay Now” button (`id="paymentModalConfirm"`), and close button.
- **styles.css**: Style the payment modal like the login modal (same overlay/modal class); add styles for total, method label, and field container.
- **script.js**: On click of “Proceed to Checkout” (use event delegation on `document` for `#cartCheckoutBtn`): get cart and selected payment method; if cart empty return. Set modal total and method text. Build fields in `paymentModalFields`: for `card` show Card number, Expiry, CVV; for `esewa` show eSewa ID/Mobile and PIN; for `cod` show a note only. Set button text to “Confirm Order” for COD, “Pay Now” otherwise. Open the payment modal. Close modal on close button, click overlay, or Escape. On “Pay Now”/“Confirm Order” click: show “Payment successful! Thank you for your order.”, disable button, then after a short delay (e.g. 1.8s) close modal, close cart, clear cart (`saveCart([])`, `renderCart()`), and re-enable button. Replace PayPal with eSewa in options and in any method-label logic.

---

## 10. Proceed to Checkout button not working – fix

- **script.js**: Do not rely on a single `getElementById("cartCheckoutBtn").addEventListener("click", ...)` at load time. Use event delegation: `document.addEventListener("click", (e) => { const btn = e.target.closest("#cartCheckoutBtn"); if (!btn) return; e.preventDefault(); ... })` and run the same “open payment modal” logic inside. Ensure `closePaymentModal()` only touches `paymentModalMessage` if it exists (e.g. `if (paymentModalMessage) paymentModalMessage.textContent = ""`).

---

## File list (new or heavily modified)

- **config.php** – JSON on DB failure, no closing `?>`
- **login.php** – Error handling, no `?>`
- **register.php** – No `?>`
- **logout.php** – New
- **setup.php** – New (optional, one-time DB setup)
- **header.php** – Session, login state, user dropdown
- **profile.php** – New
- **update_profile.php** – New
- **shop.php**, **collection.php** – Products from DB
- **index.php** – Featured products from DB, carousel markup
- **about.php** – Full content and sections
- **footer.php** – Cart payment block, payment modal HTML
- **styles.css** – All new classes for nav-user, dropdown, profile, about, carousel, cart payment, payment modal
- **script.js** – Login reload, dropdown, profile form submit, carousel, cart has-items, payment modal open/close/confirm, event delegation for checkout button

Use this as a prompt: “Apply these changes to a PHP/MySQL jewelry site with header, footer, cart drawer, and login so that [feature list] work as described above.”
