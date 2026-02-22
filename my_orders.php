<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/config.php';

if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_name'])) {
    header('Location: index.php');
    exit;
}

$conn = db();
$user_id = (int) $_SESSION['user_id'];

$stmt = $conn->prepare('SELECT id, order_number, total_amount, status, shipping_address, ordered_by, sent_by, created_at FROM orders WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $user_id);
$stmt->execute();
$orders_result = $stmt->get_result();
$orders = $orders_result ? $orders_result->fetch_all(MYSQLI_ASSOC) : [];

$order_ids = array_column($orders, 'id');
$items_by_order = [];
if (!empty($order_ids)) {
    $placeholders = implode(',', array_fill(0, count($order_ids), '?'));
    $types = str_repeat('i', count($order_ids));
    $item_stmt = $conn->prepare("SELECT oi.order_id, oi.quantity, oi.price, p.name AS product_name FROM order_items oi JOIN products p ON p.id = oi.product_id WHERE oi.order_id IN ($placeholders)");
    $item_stmt->bind_param($types, ...$order_ids);
    $item_stmt->execute();
    $items_result = $item_stmt->get_result();
    while ($row = $items_result->fetch_assoc()) {
        $oid = $row['order_id'];
        if (!isset($items_by_order[$oid])) $items_by_order[$oid] = [];
        $items_by_order[$oid][] = $row;
    }
}

$status_labels = [
    'pending'   => 'Pending',
    'paid'      => 'Paid',
    'processing'=> 'Processing',
    'shipped'   => 'Shipped',
    'delivered' => 'Delivered',
    'cancelled' => 'Cancelled',
];

include __DIR__ . '/header.php';
?>
<main class="section section-light">
  <div class="container">
    <div class="section-head left-head">
      <h1>My Orders</h1>
      <p>View your order history and current status.</p>
    </div>

    <?php if (empty($orders)): ?>
      <div class="orders-empty info-card">
        <p>You haven't placed any orders yet.</p>
        <a class="btn btn-solid" href="shop.php">Browse Shop</a>
      </div>
    <?php else: ?>
      <div class="orders-list">
        <?php foreach ($orders as $order): ?>
          <?php
          $items = $items_by_order[$order['id']] ?? [];
          $status = $order['status'] ?? 'pending';
          $status_class = 'status-' . $status;
          $status_text = $status_labels[$status] ?? ucfirst($status);
          ?>
          <article class="order-card info-card">
            <div class="order-card-header">
              <div class="order-meta">
                <strong class="order-number"><?= htmlspecialchars($order['order_number']) ?></strong>
                <span class="order-date"><?= date('M j, Y g:i A', strtotime($order['created_at'])) ?></span>
              </div>
              <span class="order-status status-badge <?= $status_class ?>"><?= htmlspecialchars($status_text) ?></span>
            </div>
            <?php if (!empty($items)): ?>
              <ul class="order-items-list">
                <?php foreach ($items as $item): ?>
                  <li>
                    <span class="order-item-name"><?= htmlspecialchars($item['product_name']) ?></span>
                    <span class="order-item-qty">× <?= (int) $item['quantity'] ?></span>
                    <span class="order-item-price">Rs <?= number_format((float) $item['price'] * (int) $item['quantity']) ?></span>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
            <div class="order-card-footer">
              <div class="order-shipping">
                <?php if (!empty($order['shipping_address'])): ?>
                  <small>Ship to: <?= htmlspecialchars($order['shipping_address']) ?></small>
                <?php endif; ?>
              </div>
              <strong class="order-total">Total: Rs <?= number_format((float) $order['total_amount']) ?></strong>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</main>
<?php include __DIR__ . '/footer.php'; ?>
