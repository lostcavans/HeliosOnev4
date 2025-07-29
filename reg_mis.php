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


// Conectar a la base de datos
include 'db.php';

// Obtener solo los grupos sin misión activa
try {
    $stmt = $pdo->query("
        SELECT g.id_grupo, g.nom_grup 
        FROM grupo g
        LEFT JOIN mision m ON g.id_grupo = m.id_grupo AND m.stat_mis = 0
        WHERE m.id_grupo IS NULL
    ");
    $grupos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $grupos = [];
}

// Validar si los datos han sido enviados a través de POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_mis = trim($_POST['nom_mis'] ?? '');
    $des_mis = trim($_POST['des_mis'] ?? '');
    $id_grupo = intval($_POST['id_grupo'] ?? 0);
    $fec_mis = date('Y-m-d H:i:s');
    $stat_mis = 0; // 0 para misiones en curso

    $fin_mis = NULL;

    if (empty($nom_mis) || empty($des_mis) || empty($id_grupo)) {
        echo json_encode(["success" => false, "message" => "Todos los campos obligatorios deben ser completados."]);
        exit;
    }

    // Verificar si el grupo ya tiene una misión activa
    try {
        $stmt = $pdo->prepare("SELECT id_mis FROM mision WHERE id_grupo = :id_grupo AND stat_mis = 0");
        $stmt->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmt->execute();
        $mision_activa = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($mision_activa) {
            echo json_encode(["success" => false, "message" => "El grupo seleccionado ya tiene una misión activa."]);
            exit;
        }

        // Insertar la nueva misión
        $stmt = $pdo->prepare("INSERT INTO mision (nom_mis, des_mis, id_grupo, fec_mis, stat_mis, fin_mis) 
                               VALUES (:nom_mis, :des_mis, :id_grupo, :fec_mis, :stat_mis, :fin_mis)");
        $stmt->bindParam(':nom_mis', $nom_mis, PDO::PARAM_STR);
        $stmt->bindParam(':des_mis', $des_mis, PDO::PARAM_STR);
        $stmt->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmt->bindParam(':fec_mis', $fec_mis, PDO::PARAM_STR);
        $stmt->bindParam(':stat_mis', $stat_mis, PDO::PARAM_INT);
        $stmt->bindParam(':fin_mis', $fin_mis, PDO::PARAM_NULL);

        if ($stmt->execute()) {
            echo json_encode(["success" => true, "message" => "Misión registrada exitosamente."]);
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar la misión."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error al registrar la misión: " . $e->getMessage()]);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Misión</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-top: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 15px;
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
            width: 100%;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            padding: 12px 20px;
            font-size: 16px;
            color: #fff;
            background-color: #007BFF;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        input:invalid {
            border-color: #dc3545;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Registrar Misión</h1>
    <form method="POST" action="register_mision.php" id="misionForm">
        <label for="nom_mis">Nombre de la Misión:</label>
        <input type="text" id="nom_mis" name="nom_mis" required>

        <label for="des_mis">Descripción de la Misión:</label>
        <textarea id="des_mis" name="des_mis" required></textarea>

        <label for="id_grupo">Grupo:</label>
        <select id="id_grupo" name="id_grupo" required>
            <option value="">Seleccione un grupo</option>
            <?php foreach ($grupos as $grupo): ?>
                <option value="<?= htmlspecialchars($grupo['id_grupo']) ?>">
                    <?= htmlspecialchars($grupo['nom_grup']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit">Registrar Misión</button>
    </form>

    <div id="notification" class="notification"></div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>