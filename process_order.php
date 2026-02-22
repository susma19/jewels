<?php
// Prevent any PHP errors from being sent to the client (so we always return JSON)
ini_set('display_errors', '0');
error_reporting(E_ALL);
ob_start();

require_once __DIR__ . '/config.php';

function sendJson($data) {
    if (ob_get_level()) ob_end_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    sendJson(['success' => false, 'message' => 'Method not allowed']);
}

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    sendJson(['success' => false, 'message' => 'Please login to checkout']);
}

$conn = db();
$user_id = (int)$_SESSION['user_id'];
$cart_items = json_decode($_POST['cart_items'] ?? '[]', true);
$payment_method = $_POST['payment_method'] ?? '';
$total_amount = (float)($_POST['total_amount'] ?? 0);
$shipping_address = trim($_POST['shipping_address'] ?? '');
$ordered_by = trim($_POST['ordered_by'] ?? '');
$sent_by = trim($_POST['sent_by'] ?? '');

if (strlen($ordered_by) < 1 || strlen($sent_by) < 1 || strlen($shipping_address) < 1) {
    sendJson(['success' => false, 'message' => 'Please fill in Ordered By, Sent By, and Shipping Address.']);
}

// Validate cart
if (empty($cart_items) || !is_array($cart_items) || $total_amount <= 0) {
    sendJson(['success' => false, 'message' => 'Invalid order data.']);
}

// Resolve product IDs and check they exist in DB (avoids FK failure)
$product_ids = [];
foreach ($cart_items as $item) {
    $item_id = $item['id'] ?? '';
    $pid = 0;
    if (is_string($item_id) && strpos($item_id, 'db-') === 0) {
        $pid = (int) str_replace('db-', '', $item_id);
    } else {
        $pid = (int) $item_id;
    }
    if ($pid > 0) $product_ids[$pid] = true;
}
$product_ids = array_keys($product_ids);
if (empty($product_ids)) {
    sendJson(['success' => false, 'message' => 'No valid items in cart. Please add products and try again.']);
}
$check = $conn->prepare('SELECT id FROM products WHERE id = ?');
if (!$check) {
    sendJson(['success' => false, 'message' => 'Unable to validate cart. Please try again.']);
}
foreach ($product_ids as $pid) {
    $check->bind_param('i', $pid);
    $check->execute();
    $res = $check->get_result();
    if ($res->num_rows === 0) {
        sendJson(['success' => false, 'message' => 'One or more items in your cart are no longer available. Please update your cart and try again.']);
    }
}

// Begin transaction
try {
    $conn->begin_transaction();
} catch (Exception $e) {
    sendJson(['success' => false, 'message' => 'Unable to start order. Please try again.']);
}

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
        throw new Exception('Unable to place order. Please try again.');
    }
    
    $stmt->bind_param('isdssss', $user_id, $order_number, $total_amount, $status, $shipping_address, $ordered_by, $sent_by);
    
    if (!$stmt->execute()) {
        throw new Exception('Database error creating order. Please try again.');
    }
    
    $order_id = $conn->insert_id;
    
    // Insert order items
    $item_stmt = $conn->prepare(
        'INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)'
    );
    if (!$item_stmt) {
        throw new Exception('Unable to place order. Please try again.');
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
            throw new Exception('Unable to save order items. Please try again.');
        }
        $items_inserted++;
    }
    
    if ($items_inserted === 0) {
        throw new Exception('No valid items in cart. Please add products and try again.');
    }
    
    // Commit transaction
    $conn->commit();
    
    sendJson([
        'success' => true,
        'message' => 'Order placed successfully',
        'order_id' => $order_id,
        'order_number' => $order_number,
        'payment_method' => $payment_method
    ]);

} catch (Exception $e) {
    try { $conn->rollback(); } catch (Exception $e2) { /* ignore */ }
    http_response_code(500);
    $msg = trim($e->getMessage());
    sendJson(['success' => false, 'message' => $msg ?: 'Unable to complete order. Please try again.']);
}
