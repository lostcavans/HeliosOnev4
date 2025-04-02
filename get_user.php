<?php
session_start();
require 'db.php'; // Asegúrate de tener un archivo de conexión a la base de datos

if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];

    // Consulta para obtener los datos del usuario
    $stmt = $pdo->prepare("SELECT nombres, apellidos FROM user WHERE id_user = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo json_encode([
            'status' => 'success',
            'full_name' => $user['nombres'] . ' ' . $user['apellidos']
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Usuario no encontrado']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No estás autenticado']);
}
?>
