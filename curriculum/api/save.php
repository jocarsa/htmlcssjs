<?php
// api/save.php
session_start();
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status"=>"error", "message"=>"Not logged in"]);
    exit;
}

$jsonInput = file_get_contents('php://input');
if (!$jsonInput) {
    echo json_encode(["status"=>"error", "message"=>"No JSON input received"]);
    exit;
}

// Optional: Validate JSON
$decoded = json_decode($jsonInput, true);
if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["status" => "error", "message" => "Malformed JSON"]);
    exit;
}

// Update DB
require_once __DIR__ . '/db.php';

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("UPDATE users SET json_data = :j WHERE id = :id");
$ok = $stmt->execute([':j'=>$jsonInput, ':id'=>$user_id]);

if ($ok) {
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode(["status"=>"error","message"=>"DB update failed"]);
}
exit;
