<?php
include 'db.php';

$query = "SELECT id_user, nom_user, apel_user 
          FROM user 
          WHERE id_user NOT IN (SELECT id_user FROM user_grup)";
$stmt = $pdo->query($query);
$personas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($personas);
?>