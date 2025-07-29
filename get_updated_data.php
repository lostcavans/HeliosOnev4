<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    die(json_encode(['error' => 'Acceso no autorizado']));
}

if (!isset($_GET['id_user']) || !is_numeric($_GET['id_user'])) {
    http_response_code(400);
    die(json_encode(['error' => 'ID de usuario no válido']));
}

$id_user = (int)$_GET['id_user'];

try {
    // 1. Obtener datos de GPS
    $stmt_gps = $pdo->prepare("SELECT latitude, longitude FROM gps_data WHERE id_user = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt_gps->execute([$id_user]);
    $gps_data = $stmt_gps->fetch(PDO::FETCH_ASSOC);

    // 2. Obtener datos de salud (BPM y SPO2)
    $stmt_bpm = $pdo->prepare("SELECT bpm, SPo2, estado FROM bpm_data WHERE id_user = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt_bpm->execute([$id_user]);
    $bpm_data = $stmt_bpm->fetch(PDO::FETCH_ASSOC);

    // 3. Obtener datos de gas
    $stmt_gas = $pdo->prepare("SELECT ppm FROM gas_data WHERE id_user = ? ORDER BY timestamp DESC LIMIT 1");
    $stmt_gas->execute([$id_user]);
    $gas_data = $stmt_gas->fetch(PDO::FETCH_ASSOC);

    // 4. Obtener datos de prevención (predicción del modelo)
    $stmt_prev = $pdo->prepare("SELECT estado_prev FROM prev_data WHERE id_user = ? ORDER BY timestamp_prev DESC LIMIT 1");
    $stmt_prev->execute([$id_user]);
    $prev_data = $stmt_prev->fetch(PDO::FETCH_ASSOC);

    // 5. Determinar estado general consolidado
    $estado_general = 'normal';
    $detalles_estado = [];
    
    // Evaluar estado basado en BPM/SPO2
    if ($bpm_data) {
        if (isset($bpm_data['estado'])) {
            $estado_general = $bpm_data['estado'];
        }
        
        // Verificación adicional por valores críticos
        if (isset($bpm_data['bpm'])) {
            if ($bpm_data['bpm'] < 50 || $bpm_data['bpm'] > 120) {
                $estado_general = 'peligro';
                $detalles_estado[] = 'BPM crítico';
            } elseif ($bpm_data['bpm'] < 60 || $bpm_data['bpm'] > 100) {
                if ($estado_general !== 'peligro') {
                    $estado_general = 'alerta';
                    $detalles_estado[] = 'BPM anormal';
                }
            }
        }
        
        if (isset($bpm_data['SPo2'])) {
            if ($bpm_data['SPo2'] < 90) {
                $estado_general = 'peligro';
                $detalles_estado[] = 'SPO2 crítico';
            } elseif ($bpm_data['SPo2'] < 95) {
                if ($estado_general !== 'peligro') {
                    $estado_general = 'alerta';
                    $detalles_estado[] = 'SPO2 bajo';
                }
            }
        }
    }
    
    // Evaluar estado basado en gases
    if ($gas_data && isset($gas_data['ppm'])) {
        if ($gas_data['ppm'] > 50) {
            $estado_general = 'peligro';
            $detalles_estado[] = 'Gas elevado';
        } elseif ($gas_data['ppm'] > 30) {
            if ($estado_general !== 'peligro') {
                $estado_general = 'alerta';
                $detalles_estado[] = 'Gas moderado';
            }
        }
    }
    
    // Evaluar estado basado en predicción de prevención
    $estado_prev_text = 'Normal';
    if ($prev_data && isset($prev_data['estado_prev'])) {
        switch ($prev_data['estado_prev']) {
            case 0:
                $estado_prev_text = 'Estrés cardíaco';
                if ($estado_general !== 'peligro') $estado_general = 'alerta';
                $detalles_estado[] = 'Predicción: Estrés';
                break;
            case 1:
                $estado_prev_text = 'Fatiga extrema';
                if ($estado_general !== 'peligro') $estado_general = 'alerta';
                $detalles_estado[] = 'Predicción: Fatiga';
                break;
            case 2:
                $estado_prev_text = 'Hipoxia';
                $estado_general = 'peligro';
                $detalles_estado[] = 'Predicción: Hipoxia';
                break;
            case 3:
                $estado_prev_text = 'Normal';
                break;
            default:
                $estado_prev_text = 'Desconocido';
        }
    }

    // Preparar respuesta
    echo json_encode([
        'success' => true,
        'gps_data' => $gps_data ?: null,
        'bpm_data' => $bpm_data ?: null,
        'gas_data' => $gas_data ?: null,
        'prev_data' => [
            'codigo' => $prev_data['estado_prev'] ?? null,
            'texto' => $estado_prev_text
        ],
        'estado_general' => $estado_general,
        'detalles_estado' => $detalles_estado,
        'timestamp' => date('Y-m-d H:i:s')
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Error en la base de datos']));
}