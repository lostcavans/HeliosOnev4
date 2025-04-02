<?php
// archivo: register_grupo.php

// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Conectar a la base de datos
include 'db.php';

// Obtener los datos del formulario
$nom_grup = trim($_POST['nom_grup'] ?? ''); // Nombre del grupo
$id_user = 1; // ID del usuario logueado

// Validar el nombre del grupo
if (empty($nom_grup)) {
    echo json_encode(["success" => false, "message" => "El nombre del grupo es obligatorio."]);
    exit;
}

try {
    // Preparar la consulta SQL para insertar el grupo
    $stmt = $pdo->prepare("INSERT INTO grupo (nom_grup, stat_grupo) VALUES (:nom_grup, :stat_grupo)");
    $stmt->bindParam(':nom_grup', $nom_grup, PDO::PARAM_STR);
    $stmt->bindParam(':stat_grupo', $id_user, PDO::PARAM_INT);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Grupo registrado exitosamente."]);
    } else {
        echo json_encode(["success" => false, "message" => "Error al registrar el grupo."]);
    }
} catch (Exception $e) {
    // Capturar y mostrar cualquier error
    echo json_encode(["success" => false, "message" => "Error al registrar el grupo: " . $e->getMessage()]);
}
?>
