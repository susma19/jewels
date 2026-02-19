  <aside class="cart-drawer" id="cartDrawer" aria-label="Shopping cart">
    <div class="cart-header">
      <h3>Your Cart</h3>
      <button class="icon-btn light-border" id="cartClose" type="button" aria-label="Close cart">✕</button>
    </div>
    <div class="cart-items" id="cartItems"></div>
    <div class="cart-payment" id="cartPayment">
      <h4 class="cart-payment-title">Payment method</h4>
      <div class="cart-payment-options">
        <label class="cart-payment-option">
          <input type="radio" name="cartPayment" value="card" checked />
          <span class="payment-option-label">Credit / Debit card</span>
        </label>
        <label class="cart-payment-option">
          <input type="radio" name="cartPayment" value="esewa" />
          <span class="payment-option-label">eSewa</span>
        </label>
        <label class="cart-payment-option">
          <input type="radio" name="cartPayment" value="cod" />
          <span class="payment-option-label">Cash on delivery</span>
        </label>
      </div>
      <button class="btn btn-solid cart-checkout-btn" id="cartCheckoutBtn" type="button">Proceed to Checkout</button>
    </div>
    <div class="cart-footer">
      <p>Total: <strong id="cartTotal">$0</strong></p>
    </div>
  </aside>
  <div class="overlay" id="cartOverlay"></div>

  <div class="modal-overlay" id="loginModal" role="dialog" aria-modal="true" aria-labelledby="loginTitle">
    <div class="login-modal">
      <button class="icon-btn modal-close light-border" id="loginClose" type="button" aria-label="Close login modal">✕</button>
      <h3 id="loginTitle">Welcome Back</h3>
      <p>Login to continue shopping your favorite jewelry pieces.</p>
      <form class="auth-form" id="loginForm" action="login.php" method="post">
        <label for="loginEmail">Email</label>
        <input id="loginEmail" name="email" type="email" placeholder="you@example.com" required />
        <label for="loginPassword">Password</label>
        <input id="loginPassword" name="password" type="password" placeholder="••••••••" required />
        <button class="btn btn-solid" type="submit">Login</button>
      </form>
      <p class="switch-auth">Don't have an account? <button class="link-btn" id="openSignup" type="button">Sign Up</button></p>
      <p id="loginMessage" class="form-message"></p>
    </div>
  </div>

  <div class="modal-overlay" id="signupModal" role="dialog" aria-modal="true" aria-labelledby="signupTitle">
    <div class="login-modal">
      <button class="icon-btn modal-close light-border" id="signupClose" type="button" aria-label="Close sign up modal">✕</button>
      <h3 id="signupTitle">Create Account</h3>
      <p>Join to save favorites and checkout faster.</p>
      <form class="auth-form" id="signupForm" action="register.php" method="post">
        <label for="signupName">Full Name</label>
        <input id="signupName" name="name" type="text" placeholder="John Doe" required />
        <label for="signupEmail">Email</label>
        <input id="signupEmail" name="email" type="email" placeholder="you@example.com" required />
        <label for="signupPassword">Password</label>
        <input id="signupPassword" name="password" type="password" placeholder="Minimum 6 characters" required minlength="6" />
        <button class="btn btn-solid" type="submit">Sign Up</button>
      </form>
      <p id="signupMessage" class="form-message"></p>
    </div>
  </div>

  <div class="modal-overlay" id="paymentModal" role="dialog" aria-modal="true" aria-labelledby="paymentModalTitle">
    <div class="login-modal payment-modal">
      <button class="icon-btn modal-close light-border" id="paymentModalClose" type="button" aria-label="Close payment">✕</button>
      <h3 id="paymentModalTitle">Complete Payment</h3>
      <p class="payment-modal-total" id="paymentModalTotal">Total: $0</p>
      <p class="payment-modal-method" id="paymentModalMethod"></p>
      <div class="payment-modal-fields" id="paymentModalFields"></div>
      <p id="paymentModalMessage" class="form-message"></p>
      <button class="btn btn-solid" id="paymentModalConfirm" type="button">Pay Now</button>
    </div>
  </div>

  <footer class="footer">
    <div class="container footer-grid">
      <div>
        <h3>Your Choice</h3>
        <p>Crafting timeless jewelry pieces that celebrate life's precious moments.</p>
      </div>
      <div>
        <h4>Shop</h4>
        <ul><li><a href="shop.php">Necklaces</a></li><li><a href="shop.php">Earrings</a></li><li><a href="shop.php">Bracelets</a></li></ul>
      </div>
      <div>
        <h4>Customer Care</h4>
        <ul><li><a href="contact.php">Contact Us</a></li><li><a href="#">Shipping & Returns</a></li><li><a href="#">Care Instructions</a></li></ul>
      </div>
      <div>
        <h4>Connect</h4>
        <ul><li><a href="#">Instagram</a></li><li><a href="mailto:hello@yourchoicejewelry.com">hello@yourchoicejewelry.com</a></li><li><a href="tel:1234567">123-4567</a></li></ul>
      </div>
    </div>
    <div class="container footer-bottom">
      <small>©2025 Your Choice Jewelry. All rights reserved.</small>
      <div><a href="#">Privacy Policy</a> · <a href="#">Terms of Service</a></div>
    </div>
  </footer>

  <script src="script.js"></script>
</body>
</html>
