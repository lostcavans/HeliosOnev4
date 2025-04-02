<?php
session_start();
header('Content-Type: application/json');

// Incluir la conexión a la base de datos
require 'db.php';

// Obtener los datos enviados por POST
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validar que los datos no estén vacíos
if (empty($email) || empty($password)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Email y contraseña son obligatorios.'
    ]);
    exit;
}

// Preparar la consulta para verificar el usuario por email
$sql = "SELECT * FROM user WHERE email_user = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user) {
    // Verificar si el usuario está activo
    if ($user['status_user'] == 0) {
        // Usuario desactivado
        echo json_encode([
            'status' => 'deactivated',
            'message' => 'Tu cuenta ha sido desactivada, contacta con soporte.'
        ]);
        exit;
    }

    // Comparar la contraseña proporcionada con la almacenada en la base de datos
    if ($password === $user['pass_user']) { // Nota: usar hash en producción
        // Guardar datos en la sesión
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['id_cargo'] = $user['id_cargo'];
        $_SESSION['full_name'] = $user['nom_user'] . ' ' . $user['apel_user'];

        // Obtener la IP del cliente
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Si está detrás de un proxy, intentar obtener la IP real
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
        
        // Asegurarse de que la IP sea IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            // Si la IP es IPv6, se puede cambiar por IPv4
            $ip = '127.0.0.1'; // Valor por defecto para IPv6
        }

        // Intentar obtener la MAC (no siempre será posible)
        $mac = 'unknown';
        if (PHP_OS_FAMILY !== 'Windows') {
            $macCommand = "ip link show | awk '/ether/ {print $2}'";
            $mac = shell_exec($macCommand) ?: 'unknown';
        }

        // Registrar la fecha, hora, estado de inicio de sesión, IP y MAC en la tabla reg_user
        $sql = "INSERT INTO reg_user (id_user, datetime, log, ip, mac) VALUES (?, NOW(), 1, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$user['id_user'], $ip, $mac]);

        // Respuesta exitosa
        echo json_encode([
            'status' => 'success',
            'full_name' => $_SESSION['full_name'],
            'id_cargo' => $_SESSION['id_cargo'] // Opcional: devolver el id_cargo en la respuesta
        ]);
    } else {
        // Contraseña incorrecta
        echo json_encode([
            'status' => 'error',
            'message' => 'Credenciales incorrectas.'
        ]);
    }
} else {
    // Usuario no encontrado
    echo json_encode([
        'status' => 'error',
        'message' => 'Credenciales incorrectas.'
    ]);
}
?>
