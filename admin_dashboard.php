<?php
require_once __DIR__ . '/config.php';
session_start();

$isAdmin = isset($_SESSION['admin_id'])
    || (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin');
if (!$isAdmin) {
    header('Location: admin_login.php');
    exit;
}
if (!isset($_SESSION['admin_name']) && isset($_SESSION['user_name'])) {
    $_SESSION['admin_name'] = $_SESSION['user_name'];
}

$conn = db();

$products_count = 0;
$products_result = $conn->query('SELECT COUNT(*) as count FROM products');
if ($products_result) {
    $row = $products_result->fetch_assoc();
    $products_count = (int) ($row['count'] ?? 0);
}

$orders_count = 0;
$orders_result = $conn->query('SELECT COUNT(*) as count FROM orders');
if ($orders_result) {
    $row = $orders_result->fetch_assoc();
    $orders_count = (int) ($row['count'] ?? 0);
}

$users_count = 0;
$users_result = $conn->query('SELECT COUNT(*) as count FROM users');
if ($users_result) {
    $row = $users_result->fetch_assoc();
    $users_count = (int) ($row['count'] ?? 0);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Your Choice Jewelry</title>
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
        .admin-header h1 {
            margin: 0;
            font-size: 24px;
        }
        .admin-logout {
            background-color: #d4af37;
            color: #333;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
        }
        .admin-logout:hover {
            background-color: #b8941e;
            color: white;
        }
        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-card h3 {
            margin: 0;
            color: #666;
            font-size: 14px;
            text-transform: uppercase;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #d4af37;
            margin: 10px 0;
        }
        .admin-nav {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .admin-nav h2 {
            margin-top: 0;
            color: #333;
        }
        .nav-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .nav-btn {
            padding: 15px;
            background-color: #d4af37;
            color: #333;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: background-color 0.3s;
        }
        .nav-btn:hover {
            background-color: #b8941e;
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>🔐 Admin Dashboard</h1>
        <div>
            <span style="margin-right: 20px;">Welcome, <?= htmlspecialchars($_SESSION['admin_name']) ?></span>
            <a href="admin_logout.php" class="admin-logout">Logout</a>
        </div>
    </div>

    <div class="admin-container">
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="number"><?= $products_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Orders</h3>
                <div class="number"><?= $orders_count ?></div>
            </div>
            <div class="stat-card">
                <h3>Total Users</h3>
                <div class="number"><?= $users_count ?></div>
            </div>
        </div>

        <div class="admin-nav">
            <h2>Management Panel</h2>
            <div class="nav-buttons">
                <a href="admin_products.php" class="nav-btn">📦 Manage Products</a>
                <a href="admin_orders.php" class="nav-btn">📋 View Orders</a>
                <a href="admin_users.php" class="nav-btn">👥 Manage Users</a>
                <a href="index.php" class="nav-btn">🏠 Back to Shop</a>
            </div>
        </div>
    </div>
</body>
</html>
