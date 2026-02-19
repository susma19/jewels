<?php
/**
 * One-time setup: creates ecommerce_db database and tables.
 * Run once by visiting: http://localhost/test-2/setup.php
 * Delete this file after setup for security.
 */
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = new mysqli('127.0.0.1', 'root', '');
if ($conn->connect_error) {
    die('<h2>Database setup failed</h2><p>MySQL connection error: ' . htmlspecialchars($conn->connect_error) . '</p><p>Ensure MySQL is running in XAMPP.</p>');
}

$conn->query('CREATE DATABASE IF NOT EXISTS ecommerce_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
$conn->select_db('ecommerce_db');

$sql = file_get_contents(__DIR__ . '/schema.sql');
$conn->multi_query($sql);
while ($conn->next_result()) { /* flush results */ }

if ($conn->error) {
    die('<h2>Setup error</h2><p>' . htmlspecialchars($conn->error) . '</p>');
}

echo '<h2>Setup complete</h2><p>Database <code>ecommerce_db</code> and tables were created. <a href="./">Go to site</a></p><p><strong>Delete setup.php for security.</strong></p>';
