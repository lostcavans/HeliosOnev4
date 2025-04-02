<?php  
session_start(); // Iniciar sesión aquí
?>

<?php
// index.php
include 'header.php';
include 'sidebar.php';
include 'db.php'; // Incluir el archivo db.php para la conexión a la base de datos
?>

<?php 

// Conectar a la base de datos
$servername = "66.94.116.235"; // Cambia esto por la dirección de tu servidor
$username = "Janco"; // Cambia esto por tu usuario
$password = ""; // Cambia esto por tu contraseña
$dbname = "aptec"; // Nombre de tu base de datos

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Comprobar conexión
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Obtener el id_est de la URL
$id_est = isset($_GET['id_est']) ? intval($_GET['id_est']) : 0;

// Obtener fechas del formulario
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : '';
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : '';

// Si no hay fechas definidas, establecer el último día por defecto
if (empty($start_date) && empty($end_date)) {
    $start_date = date('Y-m-d', strtotime('-1 day'));
    $end_date = date('Y-m-d', strtotime('+1 day')); // Aumentar un día a la fecha actual
}

// Obtener las estaciones
$sqlEstaciones = "SELECT id_est, Descr FROM est"; // Cambia 'Descr' por el nombre del campo que quieras mostrar
$resultEstaciones = $conn->query($sqlEstaciones);

// Crear un array para almacenar las estaciones
$estaciones = [];
if ($resultEstaciones->num_rows > 0) {
    while ($row = $resultEstaciones->fetch_assoc()) {
        $estaciones[] = $row;
    }
}


// Manejo de filtros predefinidos
if (isset($_POST['filter'])) {
    switch ($_POST['filter']) {
        case 'last_week':
            $start_date = date('Y-m-d', strtotime('-1 week'));
            $end_date = date('Y-m-d');
            break;
        case 'last_day':
            $start_date = date('Y-m-d', strtotime('-1 day'));
            $end_date = date('Y-m-d', strtotime('+1 day')); // Aumentar un día a la fecha actual
            break;
        case 'last_month':
            $start_date = date('Y-m-d', strtotime('-1 month'));
            $end_date = date('Y-m-d');
            break;
        case 'last_year':
            $start_date = date('Y-m-d', strtotime('-1 year'));
            $end_date = date('Y-m-d');
            break;
    }
}// Consulta para combinar datos en intervalos de 10 minutos
$sqlCombined = "
SELECT 
    FLOOR(UNIX_TIMESTAMP(u1.timestamp) / 600) * 600 AS time_group,
    u1.id_est,
    AVG(u1.BattV) AS AvgBattV,
    AVG(u1.TempAmb) AS AvgTempAmb,
    AVG(u1.Pbar) AS AvgPbar,
    AVG(u2.PrecipP) AS AvgPrecipP,
    AVG(u2.Rad) AS AvgRad,
    AVG(u2.RH) AS AvgRH,
    AVG(u2.DirV) AS AvgDirV
FROM 
    ub1 AS u1
LEFT JOIN 
    ub_2 AS u2 
ON 
    u1.id_est = u2.id_est 
    AND FLOOR(UNIX_TIMESTAMP(u1.timestamp) / 600) = FLOOR(UNIX_TIMESTAMP(u2.timestamp) / 600)
WHERE 
    u1.id_est = $id_est 
    AND u1.timestamp BETWEEN '$start_date' AND '$end_date'
GROUP BY 
    time_group, u1.id_est
ORDER BY 
    time_group DESC";

$dataUb1 = [];
$sqlUb1 = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') AS timestamp, BattV, TempAmb, Pbar 
            FROM ub1 
            WHERE id_est = $id_est 
            AND timestamp BETWEEN '$start_date' AND '$end_date' 
            ORDER BY timestamp";
$resultUb1 = $conn->query($sqlUb1);
if ($resultUb1->num_rows > 0) {
    while ($row = $resultUb1->fetch_assoc()) {
        $dataUb1[] = $row;
    }
}
$dataUb2 = [];
$sqlUb2 = "SELECT DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:%s') AS timestamp, PrecipP, Rad, RH, DirV 
            FROM ub_2 
            WHERE id_est = $id_est 
            AND timestamp BETWEEN '$start_date' AND '$end_date' 
            ORDER BY timestamp";
$resultUb2 = $conn->query($sqlUb2);
if ($resultUb2->num_rows > 0) {
    while ($row = $resultUb2->fetch_assoc()) {
        $dataUb2[] = $row;
    }
}


$resultCombined = $conn->query($sqlCombined);

// Obtener los datos combinados
$dataCombined = [];
if ($resultCombined->num_rows > 0) {
    while ($row = $resultCombined->fetch_assoc()) {
        $dataCombined[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datos de la Estación <?php echo $id_est; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/1.5.3/jspdf.debug.js" integrity="sha384-NaWTHo/8YCBYJ59830LTz/P4aQZK1sS0SneOgAvhsIl3zBu8r9RevNg5lHCHAuQ/" crossorigin="anonymous"></script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
    .btn-volver-mapa1 {
        position: absolute;
        top: 100px;
        right: 20px;
        font-size: 1.5rem; /* Tamaño más grande del texto */
        padding: 15px 30px; /* Espaciado más amplio */
        background-color: rgba(0, 0, 0, 1); /* Color azul llamativo */
        color: #fff; /* Texto en blanco */
        border-radius: 8px; /* Bordes redondeados */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Sombra para destacar */
        z-index: 1000; /* Asegurarse de que esté sobre otros elementos */
    }

    .btn-volver-mapa1:hover {
        background-color: rgba(3, 3, 66, 1); /* Color más oscuro en hover */
        text-decoration: none; /* Sin subrayado */
        transform: scale(1.05); /* Efecto de zoom al pasar el mouse */
    }
</style>

</head>
<body>
<?php include 'header.php';?>
<?php include 'sidebar.php';?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">

         <!-- Formulario de filtro de fechas -->
    <form method="POST" class="mb-4">
        <div class="form-group">
        <label for="estacion">Seleccionar Estación:</label>
<select name="estacion" id="estacion" class="form-control" onchange="cambiarEstacion(this.value)" required>
    <option value="">Seleccione una estación</option>
    <?php foreach ($estaciones as $estacion): ?>
        <option value="<?php echo htmlspecialchars($estacion['id_est']); ?>" <?php echo $estacion['id_est'] == $id_est ? 'selected' : ''; ?>>
            <?php echo htmlspecialchars($estacion['Descr']); ?>
        </option>
    <?php endforeach; ?>
</select>
    </div>
        <div class="form-row">
            <div class="col">
                <label for="start_date">Fecha de Inicio:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" value="<?php echo htmlspecialchars($start_date); ?>">
            </div>
            <div class="col">
                <label for="end_date">Fecha de Fin:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" value="<?php echo htmlspecialchars($end_date); ?>">
            </div>
            <div class="col align-self-end">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <!-- Botones de filtros predefinidos -->
    <div class="mb-4">
    <form method="POST">
        <button type="submit" name="filter" value="last_day" class="btn btn-secondary">Último Día</button>
        <button type="submit" name="filter" value="last_week" class="btn btn-secondary">Última Semana</button>
        <button type="submit" name="filter" value="last_month" class="btn btn-secondary">Último Mes</button>
<button 
    type="button" 
    name="max_min" 
    class="btn btn-secondary" 
    onclick="window.location.href='filtro_max_min.php';">
    Filtro de máximas y mínimas
</button>

        <a href="export_xml.php?id_est=<?php echo $id_est; ?>" class="btn btn-success">Descargar XML</a>
                
                <a href="map.php" class="btn-volver-mapa1">Volver al Mapa</a>

    </form>
                <!-- Formulario para descargar el PDF -->
            <form action="generate_pdf_report.php" method="POST">
                <input type="hidden" name="id_est" value="<?php echo $id_est; ?>">
                <input type="hidden" name="start_date" value="<?php echo $start_date; ?>">
                <input type="hidden" name="end_date" value="<?php echo $end_date; ?>">
                <button type="submit" class="btn btn-primary">Descargar PDF</button>
                
            </form>
</div>
                
                
                
<div class="col-md-12">
   <div class="mb-4">
    <button class="btn btn-primary" onclick="updateChart('BattV')">BattV</button>
    <button class="btn btn-primary" onclick="updateChart('TempAmb')">TempAmb</button>
    <button class="btn btn-primary" onclick="updateChart('Pbar')">Pbar</button>
    <button class="btn btn-primary" onclick="updateChart('PrecipP')">PrecipP</button>
    <button class="btn btn-primary" onclick="updateChart('Rad')">Rad</button>
    <button class="btn btn-primary" onclick="updateChart('RH')">RH</button>
    <button class="btn btn-primary" onclick="updateChart('DirV')">DirV</button>
</div>
<canvas id="combinedChart" width="400" height="200"></canvas>

</div>




    <div class="row mb-5">
        <table class="table mt-3">
    <thead>
        <tr>
            <th>Intervalo (Inicio)</th>
            <th>ID Estación</th>
            <th>Promedio BattV</th>
            <th>Promedio TempAmb</th>
            <th>Promedio Pbar</th>
            <th>Promedio PrecipP</th>
            <th>Promedio Rad</th>
            <th>Promedio RH</th>
            <th>Promedio DirV</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($dataCombined)): ?>
            <?php foreach ($dataCombined as $row): ?>
                <tr>
                    <td><?php echo date('Y-m-d H:i:s', $row['time_group']); ?></td>
                    <td><?php echo $row['id_est']; ?></td>
                    <td><?php echo round($row['AvgBattV'], 2); ?></td>
                    <td><?php echo round($row['AvgTempAmb'], 2); ?></td>
                    <td><?php echo round($row['AvgPbar'], 2); ?></td>
                    <td><?php echo round($row['AvgPrecipP'], 2); ?></td>
                    <td><?php echo round($row['AvgRad'], 2); ?></td>
                    <td><?php echo round($row['AvgRH'], 2); ?></td>
                    <td><?php echo round($row['AvgDirV'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">No hay datos para mostrar.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

        </div>
    </div>
    
    
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/dayjs"></script>
        <script src="https://cdn.jsdelivr.net/npm/dayjs"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>



<script>
// Datos iniciales
const ub1Data = <?php echo json_encode($dataUb1); ?>;
const ub2Data = <?php echo json_encode($dataUb2); ?>;

// Mapear variables a datos
const dataMapping = {
    BattV: ub1Data.map(row => row.BattV),
    TempAmb: ub1Data.map(row => row.TempAmb),
    Pbar: ub1Data.map(row => row.Pbar),
    PrecipP: ub2Data.map(row => row.PrecipP),
    Rad: ub2Data.map(row => row.Rad),
    RH: ub2Data.map(row => row.RH),
    DirV: ub2Data.map(row => row.DirV),
};

// Etiquetas para el gráfico
const labels = ub1Data.map(row => new Date(row.timestamp).toLocaleString());

// Configuración inicial del gráfico
let currentDataset = 'BattV';
const ctx = document.getElementById('combinedChart').getContext('2d');
let combinedChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'BattV',
            data: dataMapping[currentDataset],
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            fill: false,
        }],
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top',
            },
        },
        scales: {
            x: {
                title: {
                    display: true,
                    text: 'Fecha y Hora',
                },
            },
            y: {
                title: {
                    display: true,
                    text: 'Valor',
                },
            },
        },
    },
});

// Función para actualizar el gráfico
function updateChart(variable) {
    currentDataset = variable;
    combinedChart.data.datasets[0].label = variable;
    combinedChart.data.datasets[0].data = dataMapping[variable];
    combinedChart.update();
}

</script>



</section>
<script>
function cambiarEstacion(estacionId) {
    if (estacionId) {
        window.location.href = `data_view.php?id_est=${estacionId}`;
    }
}
</script>
<script>
    const dataUb1 = <?php echo json_encode($dataUb1); ?>;
    const dataUb2 = <?php echo json_encode($dataUb2); ?>;
</script>


</body>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>