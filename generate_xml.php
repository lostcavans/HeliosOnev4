<?php
// Verifica que los datos hayan sido enviados
if (!isset($_POST['tableHTML'])) {
    die('Faltan datos para generar el XML.');
}

// Obtener la tabla HTML del POST
$tableHTML = $_POST['tableHTML'];

// Utilizar DOMDocument para cargar el HTML de la tabla
$dom = new DOMDocument;
libxml_use_internal_errors(true); // Ignorar errores de parseo
$dom->loadHTML('<?xml encoding="UTF-8">' . $tableHTML);
libxml_clear_errors();

// Obtener todas las filas de la tabla
$rows = $dom->getElementsByTagName('tr');

// Crear un objeto SimpleXMLElement
$xml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><EstacionData></EstacionData>');

// Iterar sobre las filas para crear elementos XML
foreach ($rows as $row) {
    $cells = $row->getElementsByTagName('td'); // Cambiar getElementById por getElementsByTagName
    if ($cells->length > 0) {
        $entry = $xml->addChild('Entry');
        $entry->addChild('Fecha', $cells->item(0)->nodeValue);
        $entry->addChild('VoltajeBateria', $cells->item(1)->nodeValue);
        $entry->addChild('TemperaturaAmbiental', $cells->item(2)->nodeValue);
    }
}

// Configurar las cabeceras para la descarga del archivo XML
header('Content-Disposition: attachment; filename="EstacionData_' . date('d-m-Y') . '.xml"');
header('Content-Type: text/xml');

// Imprimir el XML
echo $xml->asXML();
?>
