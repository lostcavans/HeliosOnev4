<?php
session_start(); // Iniciar sesión aquí
?>
<?php
// grafica_institucion.php


// Incluir la conexión a la base de datos
require 'db.php';

// Consulta para contar usuarios por institución
$sql_institucion = "SELECT inst, COUNT(*) AS cantidad FROM user GROUP BY inst";
$stmt_institucion = $pdo->prepare($sql_institucion);
$stmt_institucion->execute();
$datos_institucion = $stmt_institucion->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de instituciones
$instituciones = [];
$cantidades_institucion = [];

foreach ($datos_institucion as $dato) {
    $instituciones[] = $dato['inst'];
    $cantidades_institucion[] = (int)$dato['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráfica de Instituciones Registradas</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        h1 {
            color: #000000;
        }
        .chart-container {
            width: 80%;
            margin: auto;
            margin-bottom: 40px;
        }
        canvas {
            max-height: 400px; /* Ajustar la altura del canvas */
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Gráfica de Instituciones Registradas</h1>
    
    <div class="chart-container">
        <canvas id="chartInstitucion"></canvas>
    </div>
</section>

<script>
    // Gráfica de Instituciones
    const ctxInstitucion = document.getElementById('chartInstitucion').getContext('2d');
    const chartInstitucion = new Chart(ctxInstitucion, {
        type: 'bar', // Puedes cambiar a 'line' si deseas una línea
        data: {
            labels: <?php echo json_encode($instituciones); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por Institución',
                data: <?php echo json_encode($cantidades_institucion); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>
</body>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>