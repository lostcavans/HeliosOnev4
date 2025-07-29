<?php
// loginCN.php - Versión mejorada

// Iniciar sesión segura
require_once 'auth_check.php';
secure_session_start();

header('Content-Type: application/json');

// Validar método de solicitud
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['status' => 'error', 'message' => 'Método no permitido']));
}

// Validar y limpiar datos
$email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$password = $_POST['password'] ?? '';

// Validaciones
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Email inválido']));
}

if (empty($password)) {
    http_response_code(400);
    die(json_encode(['status' => 'error', 'message' => 'Contraseña requerida']));
}

// Conexión a base de datos
require 'db.php';

try {
    // Buscar usuario
    $stmt = $pdo->prepare("SELECT * FROM user WHERE email_user = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        error_log("Intento de login fallido para: $email");
        http_response_code(401);
        die(json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']));
    }

    // Verificar estado de la cuenta
    if ($user['status_user'] == 0) {
        http_response_code(403);
        die(json_encode(['status' => 'deactivated', 'message' => 'Cuenta desactivada']));
    }

    // Verificar contraseña
    if (!password_verify($password, $user['pass_user'])) {
        error_log("Intento de login con contraseña incorrecta para: $email");
        http_response_code(401);
        die(json_encode(['status' => 'error', 'message' => 'Credenciales incorrectas']));
    }

    // Configurar datos de sesión (parte a modificar)
$_SESSION = [
    'id_user' => $user['id_user'],  // Cambiado de 'id_user' a 'user_id'
    'id_cargo' => $user['id_cargo'],
    'loggedin' => true,
    'last_activity' => time(),
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    'full_name' => $user['nom_user'] . ' ' . $user['apel_user']
];

    // Registrar acceso
    $pdo->prepare("INSERT INTO reg_user (id_user, datetime, log, ip) VALUES (?, NOW(), 1, ?)")
        ->execute([$user['id_user'], $_SERVER['REMOTE_ADDR']]);

    // Respuesta exitosa
    echo json_encode([
    'status' => 'success',
    'redirect' => 'map.php',
    'user' => [
        'id' => $user['id_user'],
        'name' => $user['nom_user'] . ' ' . $user['apel_user'],
        'role' => $user['id_cargo']
    ]
]);

} catch (PDOException $e) {
    error_log("Error en login: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Error en el servidor']);
}
?>