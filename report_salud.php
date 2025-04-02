<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Obtener parámetros de filtro
$id_user = isset($_GET['id_user']) ? intval($_GET['id_user']) : null;
$periodo = isset($_GET['periodo']) ? $_GET['periodo'] : 'semana'; // Por defecto, última semana

// Calcular fechas según el período
$fechaActual = date('Y-m-d H:i:s');
switch ($periodo) {
    case 'dia':
        $fechaInicio = date('Y-m-d H:i:s', strtotime('-1 day'));
        break;
    case 'semana':
        $fechaInicio = date('Y-m-d H:i:s', strtotime('-1 week'));
        break;
    case 'mes':
        $fechaInicio = date('Y-m-d H:i:s', strtotime('-1 month'));
        break;
    default:
        $fechaInicio = date('Y-m-d H:i:s', strtotime('-1 week'));
        break;
}

// Consulta para obtener los datos de salud de los bomberos con filtros
try {
    $query = "
        SELECT b.*, u.nom_user, u.apel_user 
        FROM bpm_data b
        JOIN user u ON b.id_user = u.id_user
        WHERE b.timestamp BETWEEN :fechaInicio AND :fechaActual
    ";
    if ($id_user) {
        $query .= " AND b.id_user = :id_user";
    }
    $query .= " ORDER BY b.timestamp DESC";

    $stmt = $pdo->prepare($query);
    $stmt->bindValue(':fechaInicio', $fechaInicio, PDO::PARAM_STR);
    $stmt->bindValue(':fechaActual', $fechaActual, PDO::PARAM_STR);
    if ($id_user) {
        $stmt->bindValue(':id_user', $id_user, PDO::PARAM_INT);
    }
    $stmt->execute();
    $datosSalud = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $datosSalud = [];
    echo json_encode(["success" => false, "message" => "Error al obtener los datos de salud: " . $e->getMessage()]);
    exit;
}

// Obtener la lista de bomberos para los botones de filtro
try {
    $queryUsuarios = "SELECT id_user, nom_user, apel_user FROM user";
    $stmtUsuarios = $pdo->query($queryUsuarios);
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $usuarios = [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Salud de Bomberos</title>
    <link rel="stylesheet" href="css/main.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .chart-container {
            width: 80%;
            margin: 20px auto;
        }
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
        .filtros {
            margin-bottom: 20px;
        }
        .filtros button {
            margin: 5px;
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .filtros button.active {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Reporte de Salud de Bomberos</h2>

    <!-- Filtros -->
    <div class="filtros">
        <strong>Período:</strong>
        <button onclick="cambiarPeriodo('dia')" <?php echo ($periodo == 'dia') ? 'class="active"' : ''; ?>>Último día</button>
        <button onclick="cambiarPeriodo('semana')" <?php echo ($periodo == 'semana') ? 'class="active"' : ''; ?>>Última semana</button>
        <button onclick="cambiarPeriodo('mes')" <?php echo ($periodo == 'mes') ? 'class="active"' : ''; ?>>Último mes</button>

        <strong>Bombero:</strong>
        <?php foreach ($usuarios as $usuario): ?>
            <button onclick="cambiarUsuario(<?php echo $usuario['id_user']; ?>)" <?php echo ($id_user == $usuario['id_user']) ? 'class="active"' : ''; ?>>
                <?php echo htmlspecialchars($usuario['nom_user'] . ' ' . htmlspecialchars($usuario['apel_user'])); ?>
            </button>
        <?php endforeach; ?>
        <button onclick="cambiarUsuario(null)" <?php echo ($id_user === null) ? 'class="active"' : ''; ?>>Todos</button>
    </div>

    <!-- Gráfica de BPM y SPO2 -->
    <div class="chart-container">
        <canvas id="saludChart"></canvas>
    </div>

    <!-- Tabla de datos -->
    <table id="tablaDatos">
        <thead>
            <tr>
                <th>Bombero</th>
                <th>BPM</th>
                <th>SPO2</th>
                <th>Fecha y Hora</th>
                <th>Riesgo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($datosSalud)): ?>
                <tr>
                    <td colspan="5">No se encontraron registros.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($datosSalud as $dato): ?>
                    <?php
                    // Determinar si hay riesgo
                    $riesgoBPM = ($dato['bpm'] < 60 || $dato['bpm'] > 100) ? 'Sí' : 'No';
                    $riesgoSPO2 = ($dato['SPo2'] < 95) ? 'Sí' : 'No';
                    $riesgo = ($riesgoBPM == 'Sí' || $riesgoSPO2 == 'Sí') ? 'Sí' : 'No';
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($dato['nom_user'] . ' ' . htmlspecialchars($dato['apel_user'])); ?></td>
                        <td><?php echo htmlspecialchars($dato['bpm']); ?></td>
                        <td><?php echo htmlspecialchars($dato['SPo2']); ?></td>
                        <td><?php echo htmlspecialchars($dato['timestamp']); ?></td>
                        <td style="color: <?php echo ($riesgo == 'Sí') ? 'red' : 'green'; ?>"><?php echo $riesgo; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</section>

<?php include 'footer.php'; ?>

<script>
    let saludChart;

    // Función para cambiar el período
    function cambiarPeriodo(periodo) {
        // Actualizar la URL con el nuevo período
        const url = new URL(window.location.href);
        url.searchParams.set('periodo', periodo);
        window.location.href = url.toString();
    }

    // Función para cambiar el usuario
    function cambiarUsuario(id_user) {
        // Actualizar la URL con el nuevo usuario
        const url = new URL(window.location.href);
        if (id_user) {
            url.searchParams.set('id_user', id_user);
        } else {
            url.searchParams.delete('id_user');
        }
        window.location.href = url.toString();
    }

    // Inicializar la gráfica
    function inicializarGrafica(datos) {
        const ctx = document.getElementById('saludChart').getContext('2d');
        if (saludChart) {
            saludChart.destroy(); // Destruir la gráfica anterior si existe
        }
        saludChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: datos.map(d => new Date(d.timestamp).toLocaleString()),
                datasets: [
                    {
                        label: 'BPM',
                        data: datos.map(d => d.bpm),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                    },
                    {
                        label: 'SPO2',
                        data: datos.map(d => d.SPo2),
                        borderColor: 'rgba(54, 162, 235, 1)',
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderWidth: 2,
                    }
                ]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Valores'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Fecha y Hora'
                        }
                    }
                },
                plugins: {
                    annotation: {
                        annotations: {
                            bpmRisk: {
                                type: 'line',
                                yMin: 100,
                                yMax: 100,
                                borderColor: 'red',
                                borderWidth: 2,
                                label: {
                                    content: 'Riesgo BPM Alto',
                                    enabled: true,
                                    position: 'end'
                                }
                            },
                            spo2Risk: {
                                type: 'line',
                                yMin: 95,
                                yMax: 95,
                                borderColor: 'orange',
                                borderWidth: 2,
                                label: {
                                    content: 'Riesgo SPO2 Bajo',
                                    enabled: true,
                                    position: 'end'
                                }
                            }
                        }
                    }
                }
            }
        });
    }

    // Inicializar la gráfica con los datos actuales
    inicializarGrafica(<?php echo json_encode($datosSalud); ?>);
</script>

</body>
</html>