<?php
session_start();
require_once 'db.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;

    if (!$id) {
        echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
        exit;
    }

    try {
        // Eliminación directa sin bitácora
        $stmt = $pdo->prepare("UPDATE asignacion_turnos SET estado = 0 WHERE id_asignacion = ?");
        $stmt->execute([$id]);

        echo json_encode([
            'success' => true,
            'message' => 'Asignación eliminada correctamente'
        ]);
    } catch (PDOException $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error en la base de datos: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}