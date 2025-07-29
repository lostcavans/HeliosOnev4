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
<?php
// Conexión a la base de datos
include 'db.php';

// Validar datos enviados por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_grup = trim($_POST['nom_grup'] ?? '');
    $id_user = 1;

    // Validar que el nombre del grupo no esté vacío
    if (empty($nom_grup)) {
        echo json_encode(["success" => false, "message" => "El nombre del grupo es obligatorio."]);
        exit;
    }

    try {
        // Preparar la consulta
        $stmt = $pdo->prepare("INSERT INTO grupo (nom_grup, id_user) VALUES (:nom_grup, :id_user)");
        $stmt->bindParam(':nom_grup', $nom_grup, PDO::PARAM_STR);
        $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Grupo registrado exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar el grupo."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error: " . $e->getMessage()]);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Grupo</title>

    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .form-container {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.5em;
            color: #333;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-size: 1em;
            color: #555;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
            font-size: 1em;
            color: #333;
        }

        input:focus {
            border-color: #4CAF50;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            border: none;
            border-radius: 5px;
            color: #fff;
            font-size: 1.1em;
            cursor: pointer;
        }

        button:hover {
            background-color: #45a049;
        }

        .notification {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
            font-size: 1em;
            color: #fff;
            display: none;
            text-align: center;
        }

        .notification.success {
            background-color: #4CAF50;
        }

        .notification.error {
            background-color: #f44336;
        }
    </style>

    <script>
        async function submitForm(event) {
            event.preventDefault(); // Prevenir el envío normal del formulario

            const form = document.forms['grupoForm'];
            const formData = new FormData(form);

            const notification = document.getElementById('notification');

            try {
                const response = await fetch('register_grupo.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                notification.style.display = 'block';
                notification.textContent = result.message;
                notification.className = `notification ${result.success ? 'success' : 'error'}`;

                if (result.success) {
                    form.reset(); // Reiniciar el formulario
                }
            } catch (error) {
                console.error('Error:', error);
                notification.style.display = 'block';
                notification.textContent = 'Error al enviar el formulario. Inténtalo nuevamente.';
                notification.className = 'notification error';
            }
        }
    </script>
</head>

<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Registrar Estación</h2>
    <form name="grupoForm" onsubmit="submitForm(event)">
            <label for="nom_grup">Nombre del Grupo:</label>
            <input type="text" id="nom_grup" name="nom_grup" placeholder="Ingresa el nombre del grupo" required>

            <button type="submit">Registrar</button>
        </form>

        <div id="notification" class="notification"></div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
