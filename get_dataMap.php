<?php
// Habilitar la visualización de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Iniciar sesión y validar acceso
session_start();
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado.']);
    exit();
}

// Incluir el archivo de conexión a la base de datos
require 'db.php'; // Asegúrate de que la ruta de 'db.php' sea correcta

// Configurar el encabezado para la respuesta en formato JSON
header('Content-Type: application/json');

// Consulta para obtener la última ubicación de cada usuario
$sql = "
    SELECT 
        u.id_user, u.nom_user, u.apel_user,
        g.latitude, g.longitude, g.speed, g.altitude, g.satelites, g.timestamp
    FROM user u
    LEFT JOIN gps_data g ON g.id_user = u.id_user AND g.timestamp = (
        SELECT MAX(timestamp) 
        FROM gps_data 
        WHERE id_user = u.id_user
    )
    WHERE u.status_user = 1  -- Opcional
    ORDER BY u.id_user;
";

try {
    // Preparar y ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $user_locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si no se encuentran resultados, devolver un array vacío
    if (empty($user_locations)) {
        echo json_encode(['status' => 'success', 'locations' => []]);
    } else {
        // Devolver los datos en formato JSON
        echo json_encode(['status' => 'success', 'locations' => $user_locations]);
    }
} catch (PDOException $e) {
    // Manejar errores en la consulta SQL
    echo json_encode(['status' => 'error', 'message' => 'Error en la consulta SQL: ' . $e->getMessage()]);
    exit();
}

// Cerrar la conexión (opcional, PDO lo hace automáticamente al final del script)
$pdo = null;