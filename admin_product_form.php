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
$product = null;
$edit_mode = false;

// Get product if editing
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
    $edit_mode = !empty($product);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = (float)($_POST['price'] ?? 0);
    $material = trim($_POST['material'] ?? '');
    $image_url = $product['image_url'] ?? '';
    
    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (in_array($file_ext, $allowed_exts) && $_FILES['image']['size'] <= 2000000) {
            $filename = 'product_' . time() . '_' . uniqid() . '.' . $file_ext;
            $filepath = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filepath)) {
                $image_url = 'uploads/' . $filename;
            }
        }
    }
    
    if (!empty($name) && $price > 0) {
        if ($edit_mode && isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            $stmt = $conn->prepare('UPDATE products SET name = ?, price = ?, material = ?, image_url = ? WHERE id = ?');
            $stmt->bind_param('sdssi', $name, $price, $material, $image_url, $id);
            $stmt->execute();
            header('Location: admin_products.php?message=updated');
        } else {
            $stmt = $conn->prepare('INSERT INTO products (name, price, material, image_url) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('sdss', $name, $price, $material, $image_url);
            $stmt->execute();
            header('Location: admin_products.php?message=added');
        }
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $edit_mode ? 'Edit' : 'Add' ?> Product - Admin</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .admin-header {
            background-color: #333;
            color: white;
            padding: 20px;
        }
        .admin-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }
        .form-group textarea {
            resize: vertical;
            min-height: 80px;
        }
        .image-preview {
            margin: 15px 0;
            max-width: 200px;
        }
        .image-preview img {
            max-width: 100%;
            border-radius: 4px;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }
        .btn-submit, .btn-cancel {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-submit {
            background-color: #d4af37;
            color: #333;
            flex: 1;
        }
        .btn-submit:hover {
            background-color: #b8941e;
            color: white;
        }
        .btn-cancel {
            background-color: #666;
            color: white;
            flex: 1;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .btn-cancel:hover {
            background-color: #555;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1><?= $edit_mode ? '✏️ Edit Product' : '➕ Add New Product' ?></h1>
    </div>

    <div class="admin-container">
        <div class="form-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name *</label>
                    <input type="text" id="name" name="name" value="<?= $edit_mode ? htmlspecialchars($product['name']) : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Price (Rs) *</label>
                    <input type="number" id="price" name="price" step="0.01" value="<?= $edit_mode ? (float)$product['price'] : '' ?>" required>
                </div>

                <div class="form-group">
                    <label for="material">Material</label>
                    <input type="text" id="material" name="material" value="<?= $edit_mode ? htmlspecialchars($product['material'] ?? '') : '' ?>" placeholder="e.g., 18K Gold, Sterling Silver">
                </div>

                <div class="form-group">
                    <label for="image">Product Image</label>
                    <input type="file" id="image" name="image" accept="image/*">
                    <small style="color: #666;">Max size: 2MB (JPG, PNG, GIF)</small>
                    
                    <?php if ($edit_mode && !empty($product['image_url'])): ?>
                    <div class="image-preview">
                        <p style="margin: 10px 0; color: #666;">Current image:</p>
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" alt="Product image">
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">
                        <?= $edit_mode ? 'Update Product' : 'Add Product' ?>
                    </button>
                    <a href="admin_products.php" class="btn-cancel">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
