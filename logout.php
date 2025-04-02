<?php
session_start();
require_once 'db.php'; // Archivo para la conexión a la base de datos

if (isset($_SESSION['id_user'])) {
    $id_user = $_SESSION['id_user'];

    try {
        // Obtener la IP del cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if ($ip === '::1') {
            $ip = '127.0.0.1'; // Corregir para pruebas en localhost
        }

        // Intentar obtener la MAC (esto puede variar según el sistema y red)
        $mac = 'unknown';
        if (PHP_OS_FAMILY !== 'Windows') {
            $macCommand = "ip link show | awk '/ether/ {print $2}'";
            $mac = shell_exec($macCommand) ?: 'unknown';
        }

        // Registrar el evento de logout en la base de datos
        $sql = "INSERT INTO reg_user (id_user, datetime, log, ip, mac) VALUES (?, NOW(), 0, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_user, $ip, $mac]);

        // Destruir todas las variables de sesión
        $_SESSION = [];

        // Destruir la sesión
        session_destroy();

        // Redirigir al usuario a la página de inicio de sesión
        header("Location: login.php");
        exit();
    } catch (PDOException $e) {
        // Manejo de errores
        echo "Error al registrar el logout: " . $e->getMessage();
    }
} else {
    // Si no hay sesión activa, redirigir al login
    header("Location: login.php");
    exit();
}
?>
