<?php
session_start();
require_once 'db.php';
require 'vendor/autoload.php';

// Verificar sesión
if (!isset($_SESSION['id_user'])) {
    die('Acceso no autorizado');
}

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;

// Construir consulta con filtros
$query = "SELECT m.*, g.nom_grup 
          FROM mision m
          JOIN grupo g ON m.id_grupo = g.id_grupo
          WHERE m.stat_mis = 1";

$params = [];

if (!empty($fecha_inicio)) {
    $query .= " AND m.fin_mis >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $query .= " AND m.fin_mis <= ?";
    $params[] = $fecha_fin;
}

if ($grupo_id > 0) {
    $query .= " AND m.id_grupo = ?";
    $params[] = $grupo_id;
}

$query .= " ORDER BY m.fin_mis DESC";

// Obtener misiones con PDO
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $misiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener misiones: " . $e->getMessage());
}

// Crear nuevo documento PDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Información del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema Helios');
$pdf->SetTitle('Reporte de Misiones Finalizadas');
$pdf->SetSubject('Reporte PDF');

// Margenes
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

// Saltos de página automáticos
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Establecer fuente
$pdf->SetFont('helvetica', '', 10);

// Añadir página
$pdf->AddPage();

// Título
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 15, 'REPORTE DE MISIONES FINALIZADAS', 0, 1, 'C');

// Filtros aplicados
$pdf->SetFont('helvetica', '', 10);
$filtros = 'Filtros aplicados: ';
$filtros .= !empty($fecha_inicio) ? 'Desde ' . $fecha_inicio . ' ' : '';
$filtros .= !empty($fecha_fin) ? 'Hasta ' . $fecha_fin . ' ' : '';
$filtros .= $grupo_id > 0 ? 'Grupo: ' . obtenerNombreGrupo($pdo, $grupo_id) : '';

$pdf->Cell(0, 10, $filtros, 0, 1);
$pdf->Cell(0, 10, 'Generado el: ' . date('d/m/Y H:i:s'), 0, 1);

// Espacio
$pdf->Ln(5);

// Cabecera de tabla
$pdf->SetFont('helvetica', 'B', 10);
$header = array('Nombre', 'Descripción', 'Grupo', 'Fecha Inicio', 'Fecha Fin', 'Duración');
$widths = array(40, 50, 30, 30, 30, 30);

// Colores de cabecera
$pdf->SetFillColor(41, 128, 185);
$pdf->SetTextColor(255);
$pdf->SetDrawColor(128, 128, 128);
$pdf->SetLineWidth(0.3);

// Cabecera
for ($i = 0; $i < count($header); $i++) {
    $pdf->Cell($widths[$i], 7, $header[$i], 1, 0, 'C', 1);
}
$pdf->Ln();

// Datos
$pdf->SetTextColor(0);
$pdf->SetFont('helvetica', '', 8);
$fill = false;

foreach ($misiones as $mision) {
    $inicio = new DateTime($mision['fec_mis']);
    $fin = new DateTime($mision['fin_mis']);
    $duracion = $inicio->diff($fin);
    
    $pdf->Cell($widths[0], 6, $mision['nom_mis'], 'LR', 0, 'L', $fill);
    $pdf->Cell($widths[1], 6, $mision['des_mis'], 'LR', 0, 'L', $fill);
    $pdf->Cell($widths[2], 6, $mision['nom_grup'], 'LR', 0, 'C', $fill);
    $pdf->Cell($widths[3], 6, $inicio->format('d/m/Y H:i'), 'LR', 0, 'C', $fill);
    $pdf->Cell($widths[4], 6, $fin->format('d/m/Y H:i'), 'LR', 0, 'C', $fill);
    $pdf->Cell($widths[5], 6, $duracion->format('%d días %H:%I'), 'LR', 0, 'C', $fill);
    $pdf->Ln();
    $fill = !$fill;
}

// Cierre de tabla
$pdf->Cell(array_sum($widths), 0, '', 'T');

// Pie de página
$pdf->SetY(-15);
$pdf->SetFont('helvetica', 'I', 8);
$pdf->Cell(0, 10, 'Página ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');

// Salida del PDF
$pdf->Output('reporte_misiones_finalizadas.pdf', 'D');

// Función auxiliar para obtener nombre de grupo
function obtenerNombreGrupo($pdo, $id_grupo) {
    $stmt = $pdo->prepare("SELECT nom_grup FROM grupo WHERE id_grupo = ?");
    $stmt->execute([$id_grupo]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    return $grupo ? $grupo['nom_grup'] : '';
}
?>