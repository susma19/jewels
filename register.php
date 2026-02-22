<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$conn = db();

$check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$check->bind_param('s', $email);
$check->execute();
$existing = $check->get_result();
if ($existing->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Email already registered']);
    exit;
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $hashed);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;
    
    // Auto-login user after registration
    session_start();
    $_SESSION['user_id'] = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user'; // Default role
    
    echo json_encode([
        'success' => true, 
        'message' => 'Registration successful! You are now logged in.',
        'user' => ['name' => $name, 'role' => 'user']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Could not register user']);
}
