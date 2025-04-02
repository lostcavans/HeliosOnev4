<?php
session_start(); // Iniciar sesión aquí
?>
<?php


// Incluir la conexión a la base de datos
require 'db.php';

// Consulta para contar usuarios por país
$sql_pais = "SELECT pais, COUNT(*) AS cantidad FROM user GROUP BY pais";
$stmt_pais = $pdo->prepare($sql_pais);
$stmt_pais->execute();
$datos_pais = $stmt_pais->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de países
$paises = [];
$cantidades_pais = [];

foreach ($datos_pais as $dato) {
    $paises[] = $dato['pais'];
    $cantidades_pais[] = (int)$dato['cantidad'];
}

// Consulta para contar usuarios por departamento
$sql_departamento = "SELECT departamento, COUNT(*) AS cantidad FROM user GROUP BY departamento";
$stmt_departamento = $pdo->prepare($sql_departamento);
$stmt_departamento->execute();
$datos_departamento = $stmt_departamento->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de departamentos
$departamentos = [];
$cantidades_departamento = [];

foreach ($datos_departamento as $dato) {
    $departamentos[] = $dato['departamento'];
    $cantidades_departamento[] = (int)$dato['cantidad'];
}

// Consulta para contar usuarios por provincia
$sql_provincia = "SELECT provincia, COUNT(*) AS cantidad FROM user GROUP BY provincia";
$stmt_provincia = $pdo->prepare($sql_provincia);
$stmt_provincia->execute();
$datos_provincia = $stmt_provincia->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de provincias
$provincias = [];
$cantidades_provincia = [];

foreach ($datos_provincia as $dato) {
    $provincias[] = $dato['provincia'];
    $cantidades_provincia[] = (int)$dato['cantidad'];
}

// Consulta para contar usuarios por ciudad
$sql_ciudad = "SELECT ciud, COUNT(*) AS cantidad FROM user GROUP BY ciud";
$stmt_ciudad = $pdo->prepare($sql_ciudad);
$stmt_ciudad->execute();
$datos_ciudad = $stmt_ciudad->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de ciudades
$ciudades = [];
$cantidades_ciudad = [];

foreach ($datos_ciudad as $dato) {
    $ciudades[] = $dato['ciud'];
    $cantidades_ciudad[] = (int)$dato['cantidad'];
}

// Consulta para contar usuarios por zona
$sql_zona = "SELECT zona, COUNT(*) AS cantidad FROM user GROUP BY zona";
$stmt_zona = $pdo->prepare($sql_zona);
$stmt_zona->execute();
$datos_zona = $stmt_zona->fetchAll(PDO::FETCH_ASSOC);

// Preparar datos para la gráfica de zonas
$zonas = [];
$cantidades_zona = [];

foreach ($datos_zona as $dato) {
    $zonas[] = $dato['zona'];
    $cantidades_zona[] = (int)$dato['cantidad'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gráficas de Procedencia de Usuarios</title>
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
            max-height: 100px; /* Ajustar la altura para sparklines */
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Gráficas de Procedencia de Usuarios</h1>
    
    <div class="chart-container">
        <canvas id="chartPais"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="chartDepartamento"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="chartProvincia"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="chartCiudad"></canvas>
    </div>

    <div class="chart-container">
        <canvas id="chartZona"></canvas>
    </div>
</section>

<script>
    // Gráfica de Países
    const ctxPais = document.getElementById('chartPais').getContext('2d');
    const chartPais = new Chart(ctxPais, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($paises); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por País',
                data: <?php echo json_encode($cantidades_pais); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 2,
                fill: true, // Rellenar debajo de la línea
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    display: false // Ocultar etiquetas en el eje x
                }
            }
        }
    });

    // Gráfica de Departamentos
    const ctxDepartamento = document.getElementById('chartDepartamento').getContext('2d');
    const chartDepartamento = new Chart(ctxDepartamento, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($departamentos); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por Departamento',
                data: <?php echo json_encode($cantidades_departamento); ?>,
                backgroundColor: 'rgba(255, 99, 132, 0.5)',
                borderColor: 'rgba(255, 99, 132, 1)',
                borderWidth: 2,
                fill: true, // Rellenar debajo de la línea
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    display: false // Ocultar etiquetas en el eje x
                }
            }
        }
    });

    // Gráfica de Provincias
    const ctxProvincia = document.getElementById('chartProvincia').getContext('2d');
    const chartProvincia = new Chart(ctxProvincia, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($provincias); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por Provincia',
                data: <?php echo json_encode($cantidades_provincia); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.5)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 2,
                fill: true, // Rellenar debajo de la línea
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    display: false // Ocultar etiquetas en el eje x
                }
            }
        }
    });

    // Gráfica de Ciudades
    const ctxCiudad = document.getElementById('chartCiudad').getContext('2d');
    const chartCiudad = new Chart(ctxCiudad, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($ciudades); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por Ciudad',
                data: <?php echo json_encode($cantidades_ciudad); ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.5)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 2,
                fill: true, // Rellenar debajo de la línea
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    display: false // Ocultar etiquetas en el eje x
                }
            }
        }
    });

    // Gráfica de Zonas
    const ctxZona = document.getElementById('chartZona').getContext('2d');
    const chartZona = new Chart(ctxZona, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($zonas); ?>,
            datasets: [{
                label: 'Cantidad de Usuarios por Zona',
                data: <?php echo json_encode($cantidades_zona); ?>,
                backgroundColor: 'rgba(255, 206, 86, 0.5)',
                borderColor: 'rgba(255, 206, 86, 1)',
                borderWidth: 2,
                fill: true, // Rellenar debajo de la línea
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                },
                x: {
                    display: false // Ocultar etiquetas en el eje x
                }
            }
        }
    });
</script>
</body>
</html>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>