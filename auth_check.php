<?php
// auth_check.php - Versión mejorada con manejo de errores

function secure_session_start() {
    if (session_status() === PHP_SESSION_NONE) {
        // Configuración segura de cookies de sesión
        session_set_cookie_params([
            'lifetime' => 86400, // 1 día
            'path' => '/',
            'domain' => $_SERVER['HTTP_HOST'],
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        session_start();
        
        // Protección contra session fixation
        if (empty($_SESSION['initiated'])) {
            session_regenerate_id(true);
            $_SESSION['initiated'] = true;
        }
    }
}

function check_auth() {
    secure_session_start();
    
    // Verificar variables esenciales de sesión
    $required_vars = ['id_user', 'id_cargo', 'loggedin'];
    
    foreach ($required_vars as $var) {
        if (!isset($_SESSION[$var])) {
            error_log("Variable de sesión faltante: $var");
            header('Location: login.php?error=missing_session_var');
            exit();
        }
    }
    
    // Verificar valores no vacíos
    if (empty($_SESSION['id_user']) || empty($_SESSION['id_cargo'])) {
        error_log("Valores de sesión vacíos");
        header('Location: login.php?error=empty_session_values');
        exit();
    }
    
    // Verificar autenticación
    if ($_SESSION['loggedin'] !== true) {
        error_log("Intento de acceso no autenticado");
        header('Location: login.php?error=not_authenticated');
        exit();
    }
    
    // Verificar inactividad (30 minutos)
    $inactivity = 1800; // 30 minutos en segundos
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $inactivity)) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=session_expired');
        exit();
    }
    
    $_SESSION['last_activity'] = time();
    
    // Verificar IP del usuario (seguridad adicional)
    if (!isset($_SESSION['ip_address'])) {
        $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    } elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
        session_unset();
        session_destroy();
        header('Location: login.php?error=ip_mismatch');
        exit();
    }
}
?>