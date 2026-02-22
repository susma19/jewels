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

// Handle making admin
if (isset($_GET['make_admin'])) {
    $id = (int)$_GET['make_admin'];
    $conn->query("UPDATE users SET role = 'admin' WHERE id = $id");
    header('Location: admin_users.php?message=updated');
    exit;
}

// Handle removing admin
if (isset($_GET['remove_admin'])) {
    $id = (int)$_GET['remove_admin'];
    $conn->query("UPDATE users SET role = 'user' WHERE id = $id");
    header('Location: admin_users.php?message=updated');
    exit;
}

// Get all users
$users = [];
$result = $conn->query('SELECT id, name, email, role, created_at FROM users ORDER BY created_at DESC');
if ($result) {
    $users = $result->fetch_all(MYSQLI_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
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
        .users-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .users-table th,
        .users-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        .users-table th {
            background-color: #333;
            color: white;
        }
        .users-table tr:hover {
            background-color: #f9f9f9;
        }
        .role-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .role-admin {
            background-color: #d4af37;
            color: #333;
        }
        .role-user {
            background-color: #ddd;
            color: #666;
        }
        .btn-action {
            padding: 8px 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            margin-right: 5px;
            font-size: 12px;
        }
        .btn-make-admin {
            background-color: #d4af37;
            color: #333;
        }
        .btn-make-admin:hover {
            background-color: #b8941e;
        }
        .btn-remove-admin {
            background-color: #ff9800;
            color: white;
        }
        .btn-remove-admin:hover {
            background-color: #e68900;
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="admin-header">
        <h1>👥 Manage Users</h1>
        <div>
            <a href="admin_logout.php" style="color: white;">Logout</a>
        </div>
    </div>

    <div class="admin-container">
        <a href="admin_dashboard.php" class="btn-back">← Back to Dashboard</a>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="message">
                <?php 
                if (htmlspecialchars($_GET['message']) === 'updated') echo 'User role updated successfully!';
                ?>
            </div>
        <?php endif; ?>

        <h2>All Users (<?= count($users) ?>)</h2>

        <?php if (!empty($users)): ?>
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td>
                        <span class="role-badge role-<?= htmlspecialchars($user['role'] ?? 'user') ?>">
                            <?= ucfirst(htmlspecialchars($user['role'] ?? 'user')) ?>
                        </span>
                    </td>
                    <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                    <td>
                        <?php if (($user['role'] ?? 'user') === 'user'): ?>
                            <button class="btn-action btn-make-admin" onclick="window.location='admin_users.php?make_admin=<?= (int)$user['id'] ?>'">Make Admin</button>
                        <?php else: ?>
                            <button class="btn-action btn-remove-admin" onclick="if(confirm('Remove admin rights?')) window.location='admin_users.php?remove_admin=<?= (int)$user['id'] ?>'">Remove Admin</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="background: white; padding: 20px; border-radius: 4px;">No users found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
