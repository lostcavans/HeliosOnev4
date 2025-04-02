<?php
include 'db.php';

// Verificar si se ha pasado el ID del grupo
if (isset($_GET['id'])) {
    $id_grupo = $_GET['id'];

    // Obtener los detalles del grupo desde la base de datos
    $query = "SELECT * FROM grupo WHERE id_grupo = :id_grupo";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id_grupo' => $id_grupo]);
    $group = $stmt->fetch();

    if ($group) {
        echo json_encode($group);
    } else {
        echo json_encode(['error' => 'Grupo no encontrado']);
    }
} else {
    echo json_encode(['error' => 'ID de grupo no especificado']);
}
?>
