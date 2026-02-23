<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');



$allowed_domains = [
    'gmail.com',
    'yahoo.com',
    'outlook.com',
];

$blocked_domains = [
    'example.com', 'test.com', 'fake.com', 'invalid.com',
    'mailinator.com', 'guerrillamail.com', 'sharklasers.com',
    'tempmail.com', '10minutemail.com', 'throwaway.email',
    'yopmail.com', 'trashmail.com', 'dispostable.com',
    'maildrop.cc', 'spamgourmet.com', 'getairmail.com',
];



function validate_email_domain(string $email, array $allowed, array $blocked): array {
    $domain = strtolower(explode('@', $email)[1] ?? '');

    if (in_array($domain, $blocked)) {
        return [
            'valid'   => false,
            'message' => "The email domain \"$domain\" is not allowed. Please use a real email provider."
        ];
    }

    if (!in_array($domain, $allowed)) {
        return [
            'valid'   => false,
            'message' => "\"$domain\" is not supported. Please use a Gmail, Yahoo, or Outlook email address."
        ];
    }

    return ['valid' => true, 'message' => 'Domain accepted.'];
}



if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}


$name     = trim($_POST['name'] ?? '');
$email    = strtolower(trim($_POST['email'] ?? ''));
$password = $_POST['password'] ?? '';


if ($name === '') {
    echo json_encode(['success' => false, 'message' => 'Name is required.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
    exit;
}

if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters.']);
    exit;
}


$domain_check = validate_email_domain($email, $allowed_domains, $blocked_domains);
if (!$domain_check['valid']) {
    echo json_encode(['success' => false, 'message' => $domain_check['message']]);
    exit;
}


$conn = db();

// Check if email already exists
$check = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
$check->bind_param('s', $email);
$check->execute();
if ($check->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'This email is already registered.']);
    exit;
}

// Hash password and insert user
$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt   = $conn->prepare('INSERT INTO users (name, email, password_hash) VALUES (?, ?, ?)');
$stmt->bind_param('sss', $name, $email, $hashed);

if ($stmt->execute()) {
    $user_id = $conn->insert_id;

    // Auto-login after registration
    session_start();
    $_SESSION['user_id']   = $user_id;
    $_SESSION['user_name'] = $name;
    $_SESSION['user_role'] = 'user';

    echo json_encode([
        'success' => true,
        'message' => 'Registration successful! You are now logged in.',
        'user'    => ['name' => $name, 'role' => 'user']
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not register user. Please try again.']);
}