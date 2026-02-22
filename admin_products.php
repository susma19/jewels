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

// Handle DELETE
if (isset($_GET['delete_id'])) {
    $delete_id = (int)$_GET['delete_id'];
    
    // Get image first
    $result = $conn->query("SELECT image_url FROM products WHERE id = $delete_id");
    $product = $result->fetch_assoc();
    
    // Delete product
    $conn->query("DELETE FROM products WHERE id = $delete_id");
    
    // Delete local image if exists
    if ($product && strpos($product['image_url'], 'uploads/') !== false) {
        @unlink(__DIR__ . '/' . $product['image_url']);
    }
    
    header('Location: admin_products.php?message=deleted');
    exit;
}

// Get all products
$products = [];
$result = $conn->query('SELECT id, name, price, material, image_url FROM products ORDER BY id DESC');
if ($result) {
    $products = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
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
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .btn-primary {
            background-color: #d4af37;
            color: #333;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
        }
        .btn-primary:hover {
            background-color: #b8941e;
            color: white;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .products-table th,
        .products-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .products-table th {
            background-color: #333;
            color: white;
        }
        .products-table tr:hover {
            background-color: #f9f9f9;
        }
        .product-img-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }
        .btn-edit, .btn-delete {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit:hover {
            background-color: #45a049;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
        .btn-delete:hover {
            background-color: #da190b;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
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
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>📦 Manage Products</h1>
        <div>
            <a href="admin_logout.php" style="color: white;">Logout</a>
        </div>
    </div>

    <div class="admin-container">
        <a href="admin_dashboard.php" class="btn-back">← Back to Dashboard</a>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message">
                <?php 
                $msg = htmlspecialchars($_GET['message']);
                if ($msg === 'added') echo 'Product added successfully!';
                elseif ($msg === 'updated') echo 'Product updated successfully!';
                elseif ($msg === 'deleted') echo 'Product deleted successfully!';
                ?>
            </div>
        <?php endif; ?>

        <div class="page-header">
            <h2>All Products</h2>
            <a href="admin_product_form.php" class="btn-primary">+ Add New Product</a>
        </div>

        <?php if (!empty($products)): ?>
        <table class="products-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Material</th>
                    <th>Price (Rs)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="product-img-thumb">
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['material'] ?? 'N/A') ?></td>
                    <td>Rs <?= number_format((float)$product['price'], 2) ?></td>
                    <td>
                        <a href="admin_product_form.php?id=<?= (int)$product['id'] ?>" class="btn-edit">Edit</a>
                        <button class="btn-delete" onclick="if(confirm('Delete this product?')) window.location='admin_products.php?delete_id=<?= (int)$product['id'] ?>'">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="background: white; padding: 20px; border-radius: 4px;">No products found. <a href="admin_product_form.php">Add the first product</a></p>
        <?php endif; ?>
    </div>
</body>
</html>
