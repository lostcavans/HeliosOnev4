<?php
include 'db.php';

$id = $_GET['id'] ?? 0;
$status = $_GET['status'] ?? 0;

if ($id && ($status == 0 || $status == 1)) {
    $query = "UPDATE user SET status_user = :status WHERE id_user = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['status' => $status, 'id' => $id]);

    header('Location: list_users.php'); // Redirigir a la lista de usuarios
    exit;
} else {
    echo "Datos inválidos.";
    exit;
}
