<?php
session_start();
require 'db.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    die("Acceso no autorizado.");
}

$id_user = $_GET['id_user']; // Obtener el ID del usuario desde la solicitud

// Obtener los datos de GPS del usuario
$stmt_gps = $pdo->prepare("SELECT latitude, longitude FROM gps_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
$stmt_gps->execute([':id_user' => $id_user]);
$gps_data = $stmt_gps->fetch(PDO::FETCH_ASSOC);

// Obtener los datos de salud del usuario
$stmt_bpm = $pdo->prepare("SELECT bpm, SPo2 FROM bpm_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
$stmt_bpm->execute([':id_user' => $id_user]);
$bpm_data = $stmt_bpm->fetch(PDO::FETCH_ASSOC);

$stmt_gas = $pdo->prepare("SELECT ppm FROM gas_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
$stmt_gas->execute([':id_user' => $id_user]);
$gas_data = $stmt_gas->fetch(PDO::FETCH_ASSOC);

// Devolver los datos en formato JSON
header('Content-Type: application/json');
echo json_encode([
    'gps_data' => $gps_data,
    'bpm_data' => $bpm_data,
    'gas_data' => $gas_data
]);
?>