<?php
header('Content-Type: text/xml');

// Obtener la fecha y hora actual
$date = date('Y-m-d_H-i-s'); // Formato: Año-Mes-Día_Hora-Minuto-Segundo

// Definir el nombre del archivo con la fecha y hora actual
$filename = "datos_estacion" . $date . ".xml";
header('Content-Disposition: attachment; filename="' . $filename . '"');

// Conectar a la base de datos
$servername = "66.94.116.235";
$username = "Janco";
$password = "";
$dbname = "aptec";

$conn = new mysqli($servername, $username, $password, $dbname);
$id_est = $_GET['id_est'];

// Consulta para obtener los datos de ambas tablas, agrupados por intervalos de 10 minutos
$sql = "
    SELECT 
        interval_10min,
        AVG(BattV) AS avg_BattV, 
        AVG(TempAmb) AS avg_TempAmb, 
        AVG(Pbar) AS avg_Pbar, 
        AVG(PrecipP) AS avg_PrecipP, 
        AVG(Rad) AS avg_Rad, 
        AVG(RH) AS avg_RH, 
        AVG(DirV) AS avg_DirV
    FROM (
        SELECT 
            BattV, 
            TempAmb, 
            Pbar, 
            NULL AS PrecipP, 
            NULL AS Rad, 
            NULL AS RH, 
            NULL AS DirV,
            DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:00') AS interval_10min
        FROM ub1
        WHERE id_est = $id_est
        UNION ALL
        SELECT 
            NULL AS BattV, 
            NULL AS TempAmb, 
            NULL AS Pbar, 
            PrecipP, 
            Rad, 
            RH, 
            DirV,
            DATE_FORMAT(timestamp, '%Y-%m-%d %H:%i:00') AS interval_10min
        FROM ub_2
        WHERE id_est = $id_est
    ) AS combined_data
    GROUP BY interval_10min
    ORDER BY interval_10min";

$result = $conn->query($sql);

// Crear el XML
$xml = new SimpleXMLElement('<estacion/>');
$xml->addAttribute('id_est', $id_est);

// Agregar los datos al XML
$ub_data = $xml->addChild('combined_data');
while($row = $result->fetch_assoc()) {
    $record = $ub_data->addChild('record');
    $record->addChild('BattV', !is_null($row['avg_BattV']) ? round($row['avg_BattV'], 2) : '-');
    $record->addChild('TempAmb', !is_null($row['avg_TempAmb']) ? round($row['avg_TempAmb'], 2) : '-');
    $record->addChild('Pbar', !is_null($row['avg_Pbar']) ? round($row['avg_Pbar'], 2) : '-');
    $record->addChild('PrecipP', !is_null($row['avg_PrecipP']) ? round($row['avg_PrecipP'], 2) : '-');
    $record->addChild('Rad', !is_null($row['avg_Rad']) ? round($row['avg_Rad'], 2) : '-');
    $record->addChild('RH', !is_null($row['avg_RH']) ? round($row['avg_RH'], 2) : '-');
    $record->addChild('DirV', !is_null($row['avg_DirV']) ? round($row['avg_DirV'], 2) : '-');
    $record->addChild('timestamp', $row['interval_10min']);
}

// Imprimir el XML
echo $xml->asXML();


?>
