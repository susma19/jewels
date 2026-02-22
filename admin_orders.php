<?php
require_once __DIR__ . '/config.php';
session_start();

$isAdmin = isset($_SESSION['admin_id'])
    || (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
if (!$isAdmin) {
    header('Location: admin_login.php');
    exit;
}

$conn = db();

// Handle status update
if (isset($_POST['update_order'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status']);
    $ordered_by = trim($_POST['ordered_by'] ?? '');
    $sent_by = trim($_POST['sent_by'] ?? '');
    
    $stmt = $conn->prepare('UPDATE orders SET status = ?, ordered_by = ?, sent_by = ? WHERE id = ?');
    $stmt->bind_param('sssi', $status, $ordered_by, $sent_by, $order_id);
    $stmt->execute();
    
    header('Location: admin_orders.php?message=updated');
    exit;
}

// Get all orders
$orders = [];
$query = 'SELECT o.*, u.name, u.email FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.created_at DESC';
$result = $conn->query($query);
if ($result) {
    $orders = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-header {
            background-color: #333;
            color: white;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .btn-back {
            background-color: #666;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            margin-bottom: 20px;
        }
        .btn-back:hover {
            background-color: #555;
        }
        .orders-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(500px, 1fr));
            gap: 20px;
        }
        .order-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        .order-number {
            font-weight: bold;
            font-size: 16px;
            color: #333;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }
        .status-paid {
            background-color: #d1ecf1;
            color: #0c5460;
        }
        .status-processing {
            background-color: #cce5ff;
            color: #004085;
        }
        .status-shipped {
            background-color: #d4edda;
            color: #155724;
        }
        .status-delivered {
            background-color: #6c757d;
            color: white;
        }
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        .order-info {
            margin-bottom: 15px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .info-label {
            color: #666;
            font-weight: 600;
        }
        .info-value {
            color: #333;
            text-align: right;
        }
        .order-items {
            background-color: #f9f9f9;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
        .item-line {
            font-size: 13px;
            padding: 5px 0;
            display: flex;
            justify-content: space-between;
        }
        .form-group {
            margin: 15px 0;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-weight: 600;
            font-size: 13px;
        }
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            box-sizing: border-box;
        }
        .btn-update {
            background-color: #d4af37;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            margin-top: 10px;
        }
        .btn-update:hover {
            background-color: #b8941e;
            color: white;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .total-price {
            font-size: 18px;
            font-weight: bold;
            color: #d4af37;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>📋 Manage Orders & Billing</h1>
        <div>
            <a href="admin_logout.php" style="color: white;">Logout</a>
        </div>
    </div>

    <div class="admin-container">
        <a href="admin_dashboard.php" class="btn-back">← Back to Dashboard</a>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message">
                <?php 
                if (htmlspecialchars($_GET['message']) === 'updated') echo 'Order updated successfully!';
                ?>
            </div>
        <?php endif; ?>

        <h2>All Orders (<?= count($orders) ?>)</h2>

        <?php if (!empty($orders)): ?>
        <div class="orders-grid">
            <?php foreach ($orders as $order): 
                // Get order items
                $order_id = (int)$order['id'];
                $items_result = $conn->query("SELECT oi.*, p.name FROM order_items oi 
                                             JOIN products p ON oi.product_id = p.id 
                                             WHERE oi.order_id = $order_id");
                $items = $items_result ? $items_result->fetch_all(MYSQLI_ASSOC) : [];
            ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <div class="order-number">Order #<?= htmlspecialchars($order['order_number'] ?? 'N/A') ?></div>
                        <small style="color: #999;">ID: <?= (int)$order['id'] ?></small>
                    </div>
                    <span class="status-badge status-<?= htmlspecialchars($order['status']) ?>">
                        <?= ucfirst(htmlspecialchars($order['status'])) ?>
                    </span>
                </div>

                <div class="order-info">
                    <div class="info-row">
                        <span class="info-label">Customer:</span>
                        <span class="info-value"><?= htmlspecialchars($order['name']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value"><?= htmlspecialchars($order['email']) ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Date:</span>
                        <span class="info-value"><?= date('M d, Y H:i', strtotime($order['created_at'])) ?></span>
                    </div>
                </div>

                <div style="margin: 15px 0; padding: 10px 0; border-top: 1px solid #eee; border-bottom: 1px solid #eee;">
                    <p style="margin: 0 0 10px 0; color: #666; font-weight: 600; font-size: 13px;">Items Ordered:</p>
                    <div class="order-items">
                        <?php foreach ($items as $item): ?>
                        <div class="item-line">
                            <span><?= htmlspecialchars($item['name']) ?></span>
                            <span>x<?= (int)$item['quantity'] ?> = Rs <?= number_format((float)$item['price'] * (int)$item['quantity'], 2) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="total-price">
                        Total: Rs <?= number_format((float)$order['total_amount'], 2) ?>
                    </div>
                </div>

                <form method="POST" action="admin_orders.php">
                    <input type="hidden" name="update_order" value="1">
                    <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
                    
                    <div class="form-group">
                        <label for="status_<?= (int)$order['id'] ?>">Status</label>
                        <select name="status" id="status_<?= (int)$order['id'] ?>" required>
                            <option value="pending" <?= $order['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="paid" <?= $order['status'] === 'paid' ? 'selected' : '' ?>>Paid</option>
                            <option value="processing" <?= $order['status'] === 'processing' ? 'selected' : '' ?>>Processing</option>
                            <option value="shipped" <?= $order['status'] === 'shipped' ? 'selected' : '' ?>>Shipped</option>
                            <option value="delivered" <?= $order['status'] === 'delivered' ? 'selected' : '' ?>>Delivered</option>
                            <option value="cancelled" <?= $order['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ordered_by_<?= (int)$order['id'] ?>">Ordered By (Name/Person)</label>
                        <input type="text" name="ordered_by" id="ordered_by_<?= (int)$order['id'] ?>" value="<?= htmlspecialchars($order['ordered_by'] ?? '') ?>" placeholder="e.g., John Doe">
                    </div>

                    <div class="form-group">
                        <label for="sent_by_<?= (int)$order['id'] ?>">Sent By (Courier/Handler)</label>
                        <input type="text" name="sent_by" id="sent_by_<?= (int)$order['id'] ?>" value="<?= htmlspecialchars($order['sent_by'] ?? '') ?>" placeholder="e.g., DHL, Fedex">
                    </div>

                    <button type="submit" class="btn-update">Update Order</button>
                </form>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <p style="background: white; padding: 20px; border-radius: 4px;">No orders found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
