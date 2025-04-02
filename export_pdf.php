<?php
require_once 'vendor/autoload.php'; // Si usas dompdf, asegúrate de haberlo instalado

use Dompdf\Dompdf;

// Conectar a la base de datos
$servername = "66.94.116.235";
$username = "Janco";
$password = "";
$dbname = "aptec";

$conn = new mysqli($servername, $username, $password, $dbname);
$id_est = $_GET['id_est'];

// Consultar datos de Ub1
$sqlUb1 = "SELECT id_est, BattV, TempAmb, Pbar, timestamp FROM Ub1 WHERE id_est = $id_est";
$resultUb1 = $conn->query($sqlUb1);

// Consultar datos de Ub2
$sqlUb2 = "SELECT id_est, PrecipP, Rad, RH, DirV, timestamp FROM Ub_2 WHERE id_est = $id_est";
$resultUb2 = $conn->query($sqlUb2);

// Inicializar Dompdf
$dompdf = new Dompdf();
$html = '<h1>Datos de la Estación ' . $id_est . '</h1>';

// Tabla de Ub1
$html .= '<h2>Ub1</h2><table border="1" cellpadding="5">';
$html .= '<tr><th>ID Est</th><th>BattV</th><th>TempAmb</th><th>Pbar</th><th>Timestamp</th></tr>';
while($row = $resultUb1->fetch_assoc()) {
    $html .= '<tr><td>' . $row['id_est'] . '</td><td>' . $row['BattV'] . '</td><td>' . $row['TempAmb'] . '</td><td>' . $row['Pbar'] . '</td><td>' . $row['timestamp'] . '</td></tr>';
}
$html .= '</table>';

// Tabla de Ub2
$html .= '<h2>Ub_2</h2><table border="1" cellpadding="5">';
$html .= '<tr><th>ID Est</th><th>PrecipP</th><th>Rad</th><th>RH</th><th>DirV</th><th>Timestamp</th></tr>';
while($row = $resultUb2->fetch_assoc()) {
    $html .= '<tr><td>' . $row['id_est'] . '</td><td>' . $row['PrecipP'] . '</td><td>' . $row['Rad'] . '</td><td>' . $row['RH'] . '</td><td>' . $row['DirV'] . '</td><td>' . $row['timestamp'] . '</td></tr>';
}
$html .= '</table>';

// Agregar gráficos como imágenes en el PDF (asegúrate de generar y guardar estos gráficos antes)
$html .= '<h2>Gráficos</h2>';
$html .= '<img src="path_to_image/ub1_chart.png" alt="Ub1 Chart">';
$html .= '<img src="path_to_image/ub2_chart.png" alt="Ub2 Chart">';

// Generar PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("datos_estacion_$id_est.pdf");
?>
