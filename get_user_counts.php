<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Obtener el número de usuarios por zona
$sql = "SELECT zona, COUNT(*) as user_count
        FROM user
        GROUP BY zona";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$userCounts = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Convertir a JSON
header('Content-Type: application/json');
echo json_encode($userCounts);
?>
