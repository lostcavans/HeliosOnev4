<?php
include 'db.php';

$groupId = $_GET['id'];

$query = "SELECT u.id_user, u.nom_user, u.apel_user 
          FROM user u
          JOIN user_grup ug ON u.id_user = ug.id_user
          WHERE ug.id_grupo = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$groupId]);
$integrantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($integrantes);
?>