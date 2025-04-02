<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$groupId = $data['id_grupo'];
$userId = $data['id_user'];

$query = "DELETE FROM user_grup WHERE id_grupo = ? AND id_user = ?";
$stmt = $pdo->prepare($query);
$success = $stmt->execute([$groupId, $userId]);

echo json_encode(['success' => $success]);
?>