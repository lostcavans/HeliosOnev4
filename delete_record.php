<?php
include 'db.php';
session_start();

// Verificar si se pasa un ID en la URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Eliminar el registro de la base de datos
    $query = "DELETE FROM reg_dis WHERE id_dis = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    // Ejecutar la consulta y verificar si la eliminación fue exitosa
    if ($stmt->execute()) {
        // Redirigir a la lista de registros después de eliminar
        header('Location: list_dis.php');
        exit;
    } else {
        echo "Error al eliminar el registro.";
    }
} else {
    // Si no se pasa un ID, redirigir a la lista de registros
    header('Location: list_dis.php');
    exit;
}
?>
