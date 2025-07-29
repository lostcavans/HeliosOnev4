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
// Incluir la conexión a la base de datos
include 'db.php';
// Consulta para obtener todas las misiones
$query = "SELECT * FROM mision";
$stmt = $pdo->query($query);
$missions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Misiones</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .status-circle {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
        }

        .active {
            background-color: green;
        }

        .completed {
            background-color: red;
        }

        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Lista de Misiones</h2>

    <table>
        <thead>
            <tr>
                <th>Nombre de la Misión</th>
                <th>Descripción</th>
                <th>Fecha de Inicio</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($missions as $mission): ?>
                <tr>
                    <td><?php echo htmlspecialchars($mission['nom_mis']); ?></td>
                    <td><?php echo htmlspecialchars($mission['des_mis']); ?></td>
                    <td><?php echo htmlspecialchars($mission['fec_mis']); ?></td>
                    <td>
                        <?php if ($mission['stat_mis'] == 0 && is_null($mission['fin_mis'])): ?>
                            <span class="status-circle active"></span> Activa
                        <?php elseif ($mission['stat_mis'] == 1 && !is_null($mission['fin_mis'])): ?>
                            <span class="status-circle completed"></span> Terminada
                        <?php else: ?>
                            <span class="status-circle"></span> No Definida
                        <?php endif; ?>
                    </td>
                    <td>
                        <button class="btn" onclick="window.location.href='edit_mission.php?id=<?php echo $mission['id_mis']; ?>'">Editar</button>
                        <button class="btn-danger" onclick="confirmFinishMission(<?php echo $mission['id_mis']; ?>)">Finalizar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include 'footer.php'; ?>

<script>
    function confirmFinishMission(missionId) {
        if (confirm("¿Estás seguro de que quieres finalizar esta misión?")) {
            // Redirigir a finish_mission.php para finalizar la misión
            window.location.href = "finish_mission.php?id=" + missionId;
        }
    }
</script>

</body>
</html>