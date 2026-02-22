<?php
// Admin login is now handled through main login page
// Redirect to main site - admins can login there and will be redirected to admin panel
session_start();
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}
header('Location: index.php');
exit;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Your Choice Jewelry</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            padding: 40px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .login-container h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .btn-submit {
            width: 100%;
            padding: 12px;
            background-color: #d4af37;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-submit:hover {
            background-color: #b8941e;
        }
        .message {
            margin-bottom: 20px;
            padding: 12px;
            border-radius: 4px;
            display: none;
        }
        .message.error {
            background-color: #fee;
            color: #c33;
        }
        .message.success {
            background-color: #efe;
            color: #3c3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>Admin Portal</h1>
        <div id="message" class="message"></div>
        <form id="adminLoginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn-submit">Login</button>
        </form>
    </div>

    <script>
        document.getElementById('adminLoginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            
            try {
                const response = await fetch('admin_login.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                const messageEl = document.getElementById('message');
                
                if (data.success) {
                    messageEl.textContent = data.message;
                    messageEl.classList.add('success');
                    messageEl.style.display = 'block';
                    setTimeout(() => {
                        window.location.href = 'admin_dashboard.php';
                    }, 1000);
                } else {
                    messageEl.textContent = data.message;
                    messageEl.classList.add('error');
                    messageEl.style.display = 'block';
                }
            } catch (error) {
                console.error('Error:', error);
            }
        });
    </script>
</body>
</html>
