<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Incluir la biblioteca FPDF
require('fpdf/fpdf.php');

// Parámetros de búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Consulta base con JOIN para obtener los registros de reg_user y user
$query = "
    SELECT r.*, u.nom_user, u.email_user 
    FROM reg_user r
    JOIN user u ON r.id_user = u.id_user
";

// Aplicar filtro de búsqueda si se proporciona
if (!empty($search)) {
    $query .= " WHERE u.nom_user LIKE :search OR u.email_user LIKE :search OR r.mac LIKE :search OR r.ip LIKE :search";
}

// Ordenar por fecha y hora descendente
$query .= " ORDER BY r.datetime DESC";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Reporte de Logs (Login/Logout)', 0, 1, 'C'); // Título centrado
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255); // Color de fondo para los encabezados
$pdf->Cell(30, 10, 'Nombre', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Correo', 1, 0, 'C', true);
$pdf->Cell(20, 10, 'Accion', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'MAC', 1, 0, 'C', true);
$pdf->Cell(25, 10, 'IP', 1, 0, 'C', true);
$pdf->Cell(40, 10, 'Fecha y Hora', 1, 1, 'C', true);

// Datos de la tabla
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255); // Color de fondo para las filas
foreach ($logs as $log) {
    $pdf->Cell(30, 10, $log['nom_user'], 1, 0, 'L', true);
    $pdf->Cell(40, 10, $log['email_user'], 1, 0, 'L', true);
    $pdf->Cell(20, 10, ($log['log'] == 1 ? 'Login' : 'Logout'), 1, 0, 'C', true);
    $pdf->Cell(25, 10, $log['mac'], 1, 0, 'L', true);
    $pdf->Cell(25, 10, $log['ip'], 1, 0, 'L', true);
    $pdf->Cell(40, 10, $log['datetime'], 1, 1, 'L', true);
}

// Salida del PDF
$pdf->Output('D', 'reporte_logs.pdf'); // 'D' fuerza la descarga del archivo
?>