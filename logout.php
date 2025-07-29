<?php
session_start();
require_once 'db.php';

// Verificar si es una petición beacon
$isBeacon = isset($_SERVER['HTTP_USER_AGENT']) && 
            strpos($_SERVER['HTTP_USER_AGENT'], 'Speculative') !== false;

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];

    try {
        // Obtener la IP del cliente
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($ip === '::1') {
            $ip = '127.0.0.1';
        }

        // Intentar obtener la MAC (solo para Linux)
        $mac = 'unknown';
        if (PHP_OS_FAMILY === 'Linux') {
            $mac = shell_exec("ip link show | awk '/ether/ {print $2}' | head -1");
            $mac = $mac ? trim($mac) : 'unknown';
        }

        // Registrar el evento de logout
        $sql = "INSERT INTO reg_user (id_user, datetime, log, ip, mac) VALUES (?, NOW(), 0, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user, $ip, $mac]);

        // Limpiar y destruir la sesión
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }

        session_destroy();

        // Para peticiones beacon, solo terminar
        if ($isBeacon) {
            exit;
        }

        // Responder con JSON para AJAX o redirección normal
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'success']);
            exit;
        }

        header("Location: login.php");
        exit();

    } catch (PDOException $e) {
        error_log("Error al registrar logout - User ID: $id_user - Error: " . $e->getMessage());
        
        if ($isBeacon) {
            exit;
        }

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Error en el servidor']);
            exit;
        }

        echo "Error al cerrar sesión. Por favor intenta nuevamente.";
        exit;
    }
} else {
    if ($isBeacon) {
        exit;
    }

    header("Location: login.php");
    exit;
}
?>