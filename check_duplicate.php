<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['exists' => false]);
    exit;
}

$field = $_POST['field'] ?? '';
$value = $_POST['value'] ?? '';

// Validar campo permitido
$allowed_fields = ['CI_user', 'cel_user', 'email_user'];
if (!in_array($field, $allowed_fields)) {
    echo json_encode(['exists' => false]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE $field = ?");
    $stmt->execute([$value]);
    
    echo json_encode(['exists' => $stmt->rowCount() > 0]);
} catch (PDOException $e) {
    error_log("Error checking duplicate: " . $e->getMessage());
    echo json_encode(['exists' => false]);
}