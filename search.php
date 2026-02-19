<?php
require_once __DIR__ . '/config.php';
header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    echo json_encode(['success' => true, 'products' => []]);
    exit;
}

$conn = db();
$like = '%' . $q . '%';
$stmt = $conn->prepare('SELECT id, name, price, image_url, material FROM products WHERE name LIKE ? ORDER BY name LIMIT 12');
$stmt->bind_param('s', $like);
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

echo json_encode(['success' => true, 'products' => $products]);
?>
