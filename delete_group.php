<?php
// Incluir la conexión a la base de datos
include 'db.php';

// Verificar si se ha pasado un ID a través de la URL
if (isset($_GET['id'])) {
    $groupId = $_GET['id'];

    // Preparar la consulta para eliminar el grupo
    $query = "DELETE FROM grupo WHERE id_grupo = :id_grupo";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id_grupo', $groupId, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir a la página de la lista de grupos
        header("Location: list_group.php?message=Grupo eliminado con éxito");
        exit();
    } else {
        // Si hubo un error en la eliminación, redirigir con un mensaje de error
        header("Location: list_group.php?message=Error al eliminar el grupo");
        exit();
    }
} else {
    // Si no se ha pasado un ID, redirigir con un mensaje de error
    header("Location: list_group.php?message=ID de grupo no válido");
    exit();
}
?>
