<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to checkout']);
    exit;
}

$conn = db();
$user_id = (int)$_SESSION['user_id'];
$cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
$payment_method = $_POST['payment_method'] ?? '';
$total_amount = (float)($_POST['total_amount'] ?? 0);
$shipping_address = trim($_POST['shipping_address'] ?? '');
$ordered_by = trim($_POST['ordered_by'] ?? '');
$sent_by = trim($_POST['sent_by'] ?? '');

// Validate cart
if (empty($cart_items) || $total_amount <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit;
}

// Begin transaction
$conn->begin_transaction();

try {
    // Create order number
    $order_number = 'ORD-' . date('YmdHis') . '-' . substr(uniqid(), -4);
    
    // Insert order
    $stmt = $conn->prepare(
        'INSERT INTO orders (user_id, order_number, total_amount, status, shipping_address, ordered_by, sent_by) 
         VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    
    $status = 'pending';
    if ($payment_method === 'cod') {
        $status = 'pending';
    } else {
        $status = 'paid';
    }
    
    if (!$stmt) {
        throw new Exception('Failed to prepare order statement: ' . $conn->error);
    }
    
    $stmt->bind_param('isdsss', $user_id, $order_number, $total_amount, $status, $shipping_address, $ordered_by, $sent_by);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create order: ' . $stmt->error);
    }
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    $item_stmt = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
    );
    if (!$item_stmt) {
        throw new Exception('Failed to prepare order items statement: ' . $conn->error);
    }
    
    $items_inserted = 0;
    foreach ($cart_items as $item) {
        $item_id = $item['id'] ?? '';
        $product_id = 0;
        
        // Handle product ID extraction
        if (is_string($item_id)) {
            if (strpos($item_id, 'db-') === 0) {
                $product_id = (int)str_replace('db-', '', $item_id);
            } else {
                $product_id = (int)$item_id;
            }
        } else {
            $product_id = (int)$item_id;
        }
        
        if ($product_id <= 0) {
            continue; // Skip invalid product IDs
        }
        
        $quantity = (int)($item['qty'] ?? 1);
        $price = (float)($item['price'] ?? 0);
        
        if ($quantity <= 0 || $price <= 0) {
            continue; // Skip invalid items
        }
        
        $item_stmt->bind_param('iiid', $order_id, $product_id, $quantity, $price);
        
        if (!$item_stmt->execute()) {
            throw new Exception('Failed to add order item: ' . $item_stmt->error);
        }
        $items_inserted++;
    }
    
    if ($items_inserted === 0) {
        throw new Exception('No valid items to add to order');
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'payment_method' => $payment_method
    ]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
