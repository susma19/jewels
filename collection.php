<?php
require_once __DIR__ . '/config.php';
$conn = db();
$result = $conn->query('SELECT id, name, price, material, image_url FROM products ORDER BY name');
$products = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
include __DIR__ . '/header.php';
?>
<main class="section section-light">
  <div class="container">
    <div class="section-head left-head"><h1>Collection</h1><p>Explore seasonal and signature jewelry collections.</p></div>
    <div class="products-grid" id="collectionProducts">
      <?php foreach ($products as $p): ?>
        <article class="product-card">
          <img src="<?= htmlspecialchars($p['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($p['name']) ?>" />
          <div class="product-info">
            <h3><?= htmlspecialchars($p['name']) ?></h3>
            <p><?= htmlspecialchars($p['material'] ?? 'Fine jewelry') ?></p>
            <div class="product-row">
              <strong>$<?= number_format((float) $p['price'], 0) ?></strong>
              <button class="add-cart-btn" data-id="db-<?= (int) $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= (float) $p['price'] ?>" type="button">Add to Cart</button>
            </div>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if (empty($products)): ?>
      <p class="info-card">No products available yet.</p>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
