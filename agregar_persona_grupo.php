<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$groupId = $data['id_grupo'];
$userId = $data['id_user'];

$query = "INSERT INTO user_grup (id_grupo, id_user) VALUES (?, ?)";
$stmt = $pdo->prepare($query);
$success = $stmt->execute([$groupId, $userId]);

echo json_encode(['success' => $success]);
?>