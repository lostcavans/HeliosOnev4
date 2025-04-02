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

// Crear el PDF
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Reporte de Misiones Finalizadas', 0, 1, 'C'); // Título centrado
$pdf->Ln(10);

// Encabezados de la tabla
$pdf->SetFont('Arial', 'B', 10);
$pdf->SetFillColor(200, 220, 255); // Color de fondo para los encabezados
$pdf->Cell(50, 10, 'Nombre de la Misión', 1, 0, 'C', true);
$pdf->Cell(60, 10, 'Descripción', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Grupo', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Fecha de Inicio', 1, 0, 'C', true);
$pdf->Cell(30, 10, 'Fecha de Finalización', 1, 1, 'C', true);

// Datos de la tabla
$pdf->SetFont('Arial', '', 10);
$pdf->SetFillColor(255, 255, 255); // Color de fondo para las filas
foreach ($misiones as $mision) {
    $pdf->Cell(50, 10, $mision['nom_mis'], 1, 0, 'L', true);
    $pdf->Cell(60, 10, $mision['des_mis'], 1, 0, 'L', true);
    $pdf->Cell(30, 10, $mision['nom_grup'], 1, 0, 'L', true);
    $pdf->Cell(30, 10, $mision['fec_mis'], 1, 0, 'L', true);
    $pdf->Cell(30, 10, $mision['fin_mis'], 1, 1, 'L', true);
}

// Salida del PDF
$pdf->Output('D', 'reporte_misiones_finalizadas.pdf'); // 'D' fuerza la descarga del archivo
?>