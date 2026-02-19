<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    exit;
}

$conn = db();
$stmt = $conn->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    exit;
}
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();
if ($result === false) {
    echo json_encode(['success' => false, 'message' => 'Server error. Please try again.']);
    exit;
}
$user = $result->fetch_assoc();

if (!$user || !password_verify($password, $user['password_hash'])) {
    echo json_encode(['success' => false, 'message' => 'Email or password is incorrect']);
    exit;
}

session_start();
$_SESSION['user_id'] = $user['id'];
$_SESSION['user_name'] = $user['name'];

echo json_encode(['success' => true, 'message' => 'Login successful', 'user' => ['name' => $user['name']]]);
