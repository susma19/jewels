<?php
require_once __DIR__ . '/config.php';
$conn = db();
$result = $conn->query('SELECT id, name, price, material, image_url FROM products ORDER BY name LIMIT 12');
$featuredProducts = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
include __DIR__ . '/header.php';
?>
<main>
  <section class="hero section-tan">
    <div class="container hero-grid">
      <div class="hero-copy">
        <h1>Timeless Elegance in Every Piece</h1>
        <p>Discover our curated collection of handcrafted jewelry, where artistry meets sophistication.</p>
        <div class="hero-actions">
          <a class="btn btn-solid" href="shop.php">Shop Collection</a>
          <a class="btn btn-outline" href="about.php">Learn More</a>
        </div>
      </div>
      <div class="hero-image card-shadow">
<img src="https://i.pinimg.com/736x/d4/e6/e0/d4e6e0ccc2ea522658f77b606289f88d.jpg" alt="Pearl Jewels" />      </div>
    </div>
  </section>

  <section class="section section-light">
    <div class="container">
      <div class="section-head">
        <h2>Featured Collection</h2>
        <p>Handpicked pieces that embody elegance and craftsmanship.</p>
      </div>
      <div class="carousel-wrap">
        <button class="carousel-btn carousel-prev" type="button" aria-label="Previous">‹</button>
        <div class="carousel-viewport">
          <div class="carousel-track" id="featuredCarouselTrack">
            <?php foreach ($featuredProducts as $p): ?>
              <article class="product-card carousel-slide">
                <img src="<?= htmlspecialchars($p['image_url'] ?? '') ?>" alt="<?= htmlspecialchars($p['name']) ?>" />
                <div class="product-info">
                  <h3><?= htmlspecialchars($p['name']) ?></h3>
                  <p><?= htmlspecialchars($p['material'] ?? 'Fine jewelry') ?></p>
                  <div class="product-row">
                    <strong>Rs <?= number_format((int) $p['price'], 2) ?></strong>
                    <button class="add-cart-btn" data-id="db-<?= (int) $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= (int) $p['price'] ?>" type="button">Add to Cart</button>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="carousel-btn carousel-next" type="button" aria-label="Next">›</button>
      </div>
      <div class="carousel-dots" id="featuredCarouselDots" aria-hidden="true"></div>

      <section class="search-results" id="searchResultsSection">
        <div class="section-head left-head"><h2>Search Results</h2><p id="searchMeta">Type and search for products.</p></div>
        <div class="products-grid" id="searchResults"></div>
      </section>
    </div>
  </section>
</main>
<?php include __DIR__ . '/footer.php'; ?>
