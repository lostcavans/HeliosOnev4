<?php
header('Content-Type: application/json');

// Conexión a la base de datos
$servername = "66.94.116.235";
$username = "Janco";
$password = "";
$dbname = "aptec";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Conexión fallida: ' . $conn->connect_error]));
}

// Validar el id de la estación
$id_est = isset($_GET['id_est']) ? (int)$_GET['id_est'] : 0;
if ($id_est <= 0) {
    echo json_encode(['error' => 'ID de estación no válido']);
    exit;
}

// Validar fechas
$start_date = $_GET['start_date'] ?? '';
$end_date = $_GET['end_date'] ?? '';
if ($start_date && !strtotime($start_date)) {
    echo json_encode(['error' => 'Fecha de inicio no válida']);
    exit;
}
if ($end_date && !strtotime($end_date)) {
    echo json_encode(['error' => 'Fecha de fin no válida']);
    exit;
}

// Inicializar la consulta
$sql = "SELECT Ub1.BattV, Ub1.TempAmb, Ub1.Pbar, ub_2.PrecipP, ub_2.Rad, ub_2.RH, ub_2.DirV, Ub1.timestamp AS fecha
FROM Ub1
JOIN ub_2 
  ON Ub1.id_est = ub_2.id_est
  AND ub_2.timestamp = (
    SELECT MIN(ub_2_sub.timestamp)
    FROM ub_2 AS ub_2_sub
    WHERE ub_2_sub.id_est = Ub1.id_est
    AND ub_2_sub.timestamp >= Ub1.timestamp
  )
WHERE Ub1.id_est = ?";

// Si se proporcionan fechas, agregarlas a la consulta
if ($start_date && $end_date) {
    $sql .= " AND Ub1.timestamp BETWEEN ? AND ?";
}

// Ordenar por fecha
$sql .= " ORDER BY Ub1.timestamp ASC";

// Preparar la consulta
$stmt = $conn->prepare($sql);
if ($start_date && $end_date) {
    $stmt->bind_param("iss", $id_est, $start_date, $end_date);
} else {
    $stmt->bind_param("i", $id_est);
}

// Ejecutar la consulta
$stmt->execute();
$result = $stmt->get_result();

// Obtener los resultados
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

// Cerrar la declaración y la conexión
$stmt->close();
$conn->close();

// Retornar los datos en formato JSON
if (empty($data)) {
    echo json_encode(['error' => 'No se encontraron datos para la estación especificada']);
} else {
    echo json_encode($data);
}

function validar_fecha($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

if ($start_date && !validar_fecha($start_date)) {
    echo json_encode(['error' => 'Fecha de inicio no válida']);
    exit;
}
if ($end_date && !validar_fecha($end_date)) {
    echo json_encode(['error' => 'Fecha de fin no válida']);
    exit;
}

?>
