<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();

// Verificar si el ID de la misión está presente en la URL
if (!isset($_GET['id'])) {
    echo json_encode(["success" => false, "message" => "ID de misión no proporcionado."]);
    exit;
}

$missionId = intval($_GET['id']);

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Actualizar la misión: marcar como finalizada y guardar el timestamp
try {
    // Obtener la fecha y hora actual
    $finishTime = date('Y-m-d H:i:s');

    // Preparar la consulta para actualizar la misión
    $stmt = $pdo->prepare("UPDATE mision SET stat_mis = 1, fin_mis = :fin_mis WHERE id_mis = :id_mis");
    $stmt->bindParam(':fin_mis', $finishTime, PDO::PARAM_STR);
    $stmt->bindParam(':id_mis', $missionId, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        // Redirigir de vuelta a la lista de misiones con un mensaje de éxito
        $_SESSION['registro_exitoso'] = "Misión finalizada exitosamente.";
        header("Location: list_mision.php");
        exit;
    } else {
        echo json_encode(["success" => false, "message" => "Error al finalizar la misión."]);
    }
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "Error al finalizar la misión: " . $e->getMessage()]);
}
?>