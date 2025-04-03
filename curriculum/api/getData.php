<?php
// api/getData.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["error"=>"Not logged in"]);
    exit;
}
require_once __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT json_data FROM users WHERE id = :id LIMIT 1");
$stmt->execute([':id'=>$user_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    echo json_encode([]);
    exit;
}

// Return the raw JSON data
echo $row['json_data'];
exit;
