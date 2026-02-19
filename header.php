<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$current = basename($_SERVER['PHP_SELF']);
$loggedIn = isset($_SESSION['user_id']) && isset($_SESSION['user_name']);
function isActive(string $file, string $current): string {
    return $file === $current ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Your Choice Jewelry</title>
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
<header class="topbar">
  <div class="container nav-wrap">
    <a class="brand" href="index.php">Your Choice</a>

    <nav class="nav-links" aria-label="Primary">
      <a class="<?= isActive('shop.php', $current) ?>" href="shop.php">Shop</a>
      <a class="<?= isActive('collection.php', $current) ?>" href="collection.php">Collection</a>
      <a class="<?= isActive('about.php', $current) ?>" href="about.php">About</a>
      <a class="<?= isActive('contact.php', $current) ?>" href="contact.php">Contact</a>
      <?php if ($loggedIn): ?><a class="<?= isActive('profile.php', $current) ?>" href="profile.php">Profile</a><?php endif; ?>
    </nav>

    <div class="nav-actions">
      <button class="icon-btn" id="searchToggle" type="button" aria-label="Toggle search">⌕</button>
      <button class="icon-btn cart-btn" id="cartToggle" type="button" aria-label="Open cart">
        🛍 <span class="cart-count" id="cartCount">0</span>
      </button>
      <?php if ($loggedIn): ?>
        <div class="user-dropdown">
          <button class="user-dropdown-trigger" id="userDropdownTrigger" type="button" aria-haspopup="true" aria-expanded="false">
            Hi, <?= htmlspecialchars($_SESSION['user_name']) ?> ▾
          </button>
          <div class="user-dropdown-menu" id="userDropdownMenu" aria-hidden="true">
            <a href="profile.php">Update Profile</a>
            <a href="logout.php">Logout</a>
          </div>
        </div>
      <?php else: ?>
        <button class="btn btn-login" id="loginOpen" type="button">Login</button>
      <?php endif; ?>
    </div>
  </div>

  <div class="search-bar-wrap" id="searchBarWrap" aria-hidden="true">
    <div class="container search-row">
      <input id="searchInput" type="search" placeholder="Search products by name..." aria-label="Search products" />
      <button id="searchBtn" class="btn btn-solid" type="button">Search</button>
    </div>
  </div>
</header>
