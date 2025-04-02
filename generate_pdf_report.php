<?php
ini_set('memory_limit', '3G'); // O un valor mayor, según sea necesario
set_time_limit(90);  // Aumenta el límite de tiempo a 300 segundos (5 minutos)

require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

$servername = "66.94.116.235";
$username = "Janco";
$password = "";
$dbname = "aptec";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id_est = isset($_POST['id_est']) ? intval($_POST['id_est']) : 0;
$start_date = isset($_POST['start_date']) ? $_POST['start_date'] : null;
$end_date = isset($_POST['end_date']) ? $_POST['end_date'] : null;

// Validar formato de las fechas si es necesario
if ($start_date && $end_date) {
    $start_date = date('Y-m-d H:i:s', strtotime($start_date));
    $end_date = date('Y-m-d H:i:s', strtotime($end_date));
}

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);
$dompdf = new Dompdf($options);

ob_start(); // Iniciar el buffer de salida

// Encabezado del reporte
echo '<h2 style="text-align:center;">Promedios por Intervalo de 1 Hora - Estación ' . $id_est . '</h2>';

// Consulta de datos con promedios por intervalos de 1 hora
$sql = "
    SELECT 
        id_est,
        DATE_FORMAT(timestamp, '%Y-%m-%d %H:00:00') AS interval_1h,  
        AVG(BattV) AS avg_BattV,
        AVG(TempAmb) AS avg_TempAmb,
        AVG(Pbar) AS avg_Pbar,
        AVG(PrecipP) AS avg_PrecipP,
        AVG(Rad) AS avg_Rad,
        AVG(RH) AS avg_RH,
        AVG(DirV) AS avg_DirV
    FROM (
        SELECT id_est, BattV, TempAmb, Pbar, NULL AS PrecipP, NULL AS Rad, NULL AS RH, NULL AS DirV, timestamp
        FROM ub1 
        WHERE id_est = $id_est 
            AND timestamp >= '$start_date' AND timestamp <= '$end_date'
        UNION ALL
        SELECT id_est, NULL AS BattV, NULL AS TempAmb, NULL AS Pbar, PrecipP, Rad, RH, DirV, timestamp
        FROM ub_2 
        WHERE id_est = $id_est 
            AND timestamp >= '$start_date' AND timestamp <= '$end_date'
    ) AS combined_data
    GROUP BY id_est, interval_1h
    ORDER BY interval_1h
";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    echo '<table border="1" cellpadding="5" cellspacing="0" style="margin-top:20px; width:100%;">';
    echo '<tr>
            <th>Intervalo (Hora)</th>
            <th>Voltaje Batería (Promedio)</th>
            <th>Temperatura Ambiente (Promedio)</th>
            <th>Presión (Promedio)</th>
            <th>Precipitación (Promedio)</th>
            <th>Radiación Solar (Promedio)</th>
            <th>Humedad Relativa (Promedio)</th>
            <th>Dirección del Viento (Promedio)</th>
          </tr>';

    // Iterar sobre los resultados y generar filas
    while ($row = $result->fetch_assoc()) {
        echo '<tr>
                <td>' . $row["interval_1h"] . '</td>
                <td>' . (!is_null($row["avg_BattV"]) ? round($row["avg_BattV"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_TempAmb"]) ? round($row["avg_TempAmb"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_Pbar"]) ? round($row["avg_Pbar"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_PrecipP"]) ? round($row["avg_PrecipP"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_Rad"]) ? round($row["avg_Rad"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_RH"]) ? round($row["avg_RH"], 2) : '-') . '</td>
                <td>' . (!is_null($row["avg_DirV"]) ? round($row["avg_DirV"], 2) : '-') . '</td>
              </tr>';
    }
    echo '</table>';
} else {
    echo "<p>No se encontraron datos para la estación especificada en el rango de fechas seleccionado.</p>";
}

$html = ob_get_clean(); // Guardar la salida en la variable $html

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Descargar el archivo PDF automáticamente
$dompdf->stream('Promedios_Estacion_' . $id_est . '.pdf', array('Attachment' => 1)); // Cambiado a Attachment => 1
?>
