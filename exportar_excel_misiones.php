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

// Cabeceras para descarga de archivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reporte_misiones_finalizadas.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Título
$sheet->setCellValue('A1', 'REPORTE DE MISIONES FINALIZADAS');
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

// Filtros aplicados
$filtros = 'Filtros aplicados: ';
$filtros .= !empty($fecha_inicio) ? 'Desde ' . $fecha_inicio . ' ' : '';
$filtros .= !empty($fecha_fin) ? 'Hasta ' . $fecha_fin . ' ' : '';
$filtros .= $grupo_id > 0 ? 'Grupo: ' . obtenerNombreGrupo($pdo, $grupo_id) : '';

$sheet->setCellValue('A2', $filtros);
$sheet->setCellValue('A3', 'Generado el: ' . date('d/m/Y H:i:s'));

// Cabeceras de tabla
$sheet->setCellValue('A5', 'Nombre');
$sheet->setCellValue('B5', 'Descripción');
$sheet->setCellValue('C5', 'Grupo');
$sheet->setCellValue('D5', 'Fecha Inicio');
$sheet->setCellValue('E5', 'Fecha Fin');
$sheet->setCellValue('F5', 'Duración');

// Estilo para cabeceras
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['argb' => 'FF2980B9']
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
];
$sheet->getStyle('A5:F5')->applyFromArray($headerStyle);

// Datos
$row = 6;
foreach ($misiones as $mision) {
    $inicio = new DateTime($mision['fec_mis']);
    $fin = new DateTime($mision['fin_mis']);
    $duracion = $inicio->diff($fin);
    
    $sheet->setCellValue('A' . $row, $mision['nom_mis']);
    $sheet->setCellValue('B' . $row, $mision['des_mis']);
    $sheet->setCellValue('C' . $row, $mision['nom_grup']);
    $sheet->setCellValue('D' . $row, $inicio->format('d/m/Y H:i'));
    $sheet->setCellValue('E' . $row, $fin->format('d/m/Y H:i'));
    $sheet->setCellValue('F' . $row, $duracion->format('%d días %H:%I'));
    
    // Alternar colores de fila
    if ($row % 2 == 0) {
        $sheet->getStyle('A' . $row . ':F' . $row)
              ->getFill()
              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
              ->getStartColor()->setARGB('FFECF0F1');
    }
    
    $row++;
}

// Ajustar ancho de columnas
foreach (range('A', 'F') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Bordes para la tabla
$tableStyle = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
        ]
    ]
];
$sheet->getStyle('A5:F' . ($row-1))->applyFromArray($tableStyle);

// Crear y descargar archivo
$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save('php://output');

// Función auxiliar para obtener nombre de grupo
function obtenerNombreGrupo($pdo, $id_grupo) {
    $stmt = $pdo->prepare("SELECT nom_grup FROM grupo WHERE id_grupo = ?");
    $stmt->execute([$id_grupo]);
    $grupo = $stmt->fetch(PDO::FETCH_ASSOC);
    return $grupo ? $grupo['nom_grup'] : '';
}
?>