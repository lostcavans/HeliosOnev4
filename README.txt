Poner eso al inicio para autenticacion de usuario en toda pagina
<?php
// map.php - Versión segura con depuración

// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Registrar datos de sesión para depuración
error_log("Acceso a map.php - Datos de sesión: " . print_r($_SESSION, true));

// Verificar autenticación
require_once 'auth_check.php';
try {
    check_auth();
} catch (Exception $e) {
    error_log("Error en autenticación: " . $e->getMessage());
    die("Error de autenticación. Por favor inicie sesión nuevamente.");
}

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");
?>