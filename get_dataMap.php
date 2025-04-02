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
    SELECT u.nom_user, u.apel_user, g1.id_user, g1.latitude, g1.longitude, 
           g1.speed, g1.altitude, g1.satelites, g1.timestamp
    FROM gps_data g1
    INNER JOIN (
        SELECT id_user, MAX(timestamp) AS last_timestamp
        FROM gps_data
        GROUP BY id_user
    ) g2 ON g1.id_user = g2.id_user AND g1.timestamp = g2.last_timestamp
    INNER JOIN user u ON g1.id_user = u.id_user
    ORDER BY g1.id_user;
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