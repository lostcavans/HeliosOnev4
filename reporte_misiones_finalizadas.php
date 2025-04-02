<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Consulta para obtener las misiones finalizadas
try {
    $query = "
        SELECT m.*, g.nom_grup 
        FROM mision m
        JOIN grupo g ON m.id_grupo = g.id_grupo
        WHERE m.stat_mis = 1
        ORDER BY m.fin_mis DESC
    ";
    $stmt = $pdo->query($query);
    $misiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $misiones = [];
    echo json_encode(["success" => false, "message" => "Error al obtener las misiones: " . $e->getMessage()]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Misiones Finalizadas</title>
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

        .download-box {
            margin-bottom: 20px;
            text-align: right;
        }

        .download-box button {
            padding: 8px 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .download-box button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Reporte de Misiones Finalizadas</h2>

    <!-- Botón para descargar el PDF -->
    <div class="download-box">
        <form method="GET" action="generate_pdf_misiones.php">
            <button type="submit" class="btn">Descargar PDF</button>
        </form>
    </div>

    <!-- Tabla de misiones finalizadas -->
    <table>
        <thead>
            <tr>
                <th>Nombre de la Misión</th>
                <th>Descripción</th>
                <th>Grupo</th>
                <th>Fecha de Inicio</th>
                <th>Fecha de Finalización</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($misiones)): ?>
                <tr>
                    <td colspan="5">No se encontraron misiones finalizadas.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($misiones as $mision): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($mision['nom_mis']); ?></td>
                        <td><?php echo htmlspecialchars($mision['des_mis']); ?></td>
                        <td><?php echo htmlspecialchars($mision['nom_grup']); ?></td>
                        <td><?php echo htmlspecialchars($mision['fec_mis']); ?></td>
                        <td><?php echo htmlspecialchars($mision['fin_mis']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include 'footer.php'; ?>

</body>
</html>