<?php
// map.php - Versión segura con depuración

// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Registrar datos de sesión para depuración
error_log("Acceso a map.php - Datos de sesión: " . print_r($_SESSION, true));

// Verificar autenticación
require_once 'auth_check.php';
try {
    check_auth();
} catch (Exception $e) {
    error_log("Error en autenticación: " . $e->getMessage());
    die("Error de autenticación. Por favor inicie sesión nuevamente.");
}

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");
?>
<?php

require 'db.php';



// Obtener solo misiones activas
$stmt_misiones = $pdo->query("SELECT id_mis, nom_mis FROM mision WHERE stat_mis = 0");
$misiones = $stmt_misiones->fetchAll(PDO::FETCH_ASSOC);

// Obtener parámetros de filtro
$id_grupo_seleccionado = isset($_GET['id_grupo']) ? (int)$_GET['id_grupo'] : null;
$id_mision_seleccionada = isset($_GET['id_mision']) ? (int)$_GET['id_mision'] : null;

// Inicializar variables
$usuarios = [];
$grupos = [];
$mostrar_cards = false;

// Si se seleccionó misión y grupo, obtener usuarios
if ($id_mision_seleccionada && $id_grupo_seleccionado) {
    $mostrar_cards = true;
    
    $sql = "SELECT u.id_user, u.nom_user, u.apel_user, u.email_user, u.cel_user 
            FROM user u
            JOIN user_grup ug ON u.id_user = ug.id_user
            JOIN mision m ON ug.id_grupo = m.id_grupo
            WHERE u.status_user != 0
            AND m.id_mis = :id_mision
            AND ug.id_grupo = :id_grupo
            ORDER BY u.nom_user";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_mision' => $id_mision_seleccionada,
        ':id_grupo' => $id_grupo_seleccionado
    ]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Obtener grupos para la misión seleccionada
if ($id_mision_seleccionada) {
    $stmt_grupos = $pdo->prepare("SELECT g.id_grupo, g.nom_grup 
                                 FROM grupo g
                                 JOIN mision m ON g.id_grupo = m.id_grupo
                                 WHERE m.id_mis = :id_mision");
    $stmt_grupos->execute([':id_mision' => $id_mision_seleccionada]);
    $grupos = $stmt_grupos->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helios - Monitoreo Médico</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --danger: #e74c3c;
            --warning: #f39c12;
            --success: #2ecc71;
            --dark: #2c3e50;
            --light: #ecf0f1;
            --gray: #95a5a6;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Roboto', sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #34495e;
        }
        
        .dashboard-contentPage {
            padding: 20px;
            margin-left: 250px;
            transition: all 0.3s ease;
        }
        
        .home-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            text-align: center;
            color: var(--dark);
            margin-bottom: 30px;
            font-weight: 500;
            font-size: 2rem;
            position: relative;
            padding-bottom: 10px;
        }
        
        h1::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 3px;
            background: var(--primary);
            border-radius: 3px;
        }
        
        .filtros-container {
            background-color: white;
            padding: 20px;
            margin: 30px auto;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            text-align: center;
        }
        
        .filtros-container select {
            padding: 10px 15px;
            margin: 10px 5px;
            border-radius: 6px;
            border: 1px solid #ddd;
            font-size: 15px;
            background-color: white;
            color: var(--dark);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .filtros-container select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .filtros-container label {
            font-weight: 500;
            margin-right: 10px;
            color: var(--dark);
        }
        
        .mensaje-seleccion {
            text-align: center;
            margin: 40px;
            font-size: 16px;
            color: var(--gray);
            padding: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 25px;
            padding: 20px 0;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            padding: 20px;
            width: 400px;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
            position: relative;
            border: 1px solid #eee;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .card h3 {
            margin: 15px 0;
            color: var(--dark);
            font-size: 1.4rem;
            font-weight: 500;
            text-align: center;
        }
        
        .card p {
            margin: 10px 0;
            color: var(--dark);
            font-size: 1rem;
        }
        
        .card p strong {
            font-weight: 500;
            color: #7f8c8d;
        }
        
        .map {
            width: 100%;
            height: 200px;
            margin-bottom: 15px;
            border-radius: 8px;
            overflow: hidden;
            background-color: #f8f9fa;
            border: 1px solid #e0e0e0;
        }
        
        .no-location {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: var(--gray);
            font-style: italic;
        }
        
        .prevencion {
            margin: 15px 0;
            padding: 10px;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .prevencion-normal {
            background-color: rgba(46, 204, 113, 0.1);
            color: #27ae60;
            border-left: 4px solid var(--success);
        }
        
        .prevencion-alerta {
            background-color: rgba(243, 156, 18, 0.1);
            color: #d35400;
            border-left: 4px solid var(--warning);
        }
        
        .prevencion-peligro {
            background-color: rgba(231, 76, 60, 0.1);
            color: #c0392b;
            border-left: 4px solid var(--danger);
        }
        
        .prevencion-desconocido {
            background-color: rgba(149, 165, 166, 0.1);
            color: var(--gray);
            border-left: 4px solid var(--gray);
        }
        
        .footer {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            text-align: center;
            color: var(--gray);
            font-size: 0.9rem;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-contentPage {
                margin-left: 0;
            }
            
            .card {
                width: 350px;
            }
        }
        
        @media (max-width: 768px) {
            .filtros-container {
                padding: 15px;
            }
            
            .filtros-container select {
                width: 100%;
                margin: 8px 0;
            }
            
            .card {
                width: 100%;
                max-width: 400px;
            }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>

    <div class="home-container">
        <h1>Monitoreo de Situación Médica</h1>

        <!-- Formulario de filtros -->
        <div class="filtros-container">
            <form method="GET" action="">
                <div>
                    <label for="id_mision">Misión:</label>
                    <select name="id_mision" id="id_mision" required onchange="this.form.submit()">
                        <option value="">-- Seleccione una misión --</option>
                        <?php foreach ($misiones as $mision): ?>
                            <option value="<?= $mision['id_mis'] ?>" <?= ($id_mision_seleccionada == $mision['id_mis']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($mision['nom_mis']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if ($id_mision_seleccionada && !empty($grupos)): ?>
                    <div>
                        <label for="id_grupo">Equipo:</label>
                        <select name="id_grupo" id="id_grupo" required onchange="this.form.submit()">
                            <option value="">-- Seleccione un equipo --</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?= $grupo['id_grupo'] ?>" <?= ($id_grupo_seleccionado == $grupo['id_grupo']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($grupo['nom_grup']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>
            </form>
        </div>

        <?php if (!$mostrar_cards): ?>
            <div class="mensaje-seleccion">
                <?php if ($id_mision_seleccionada && !$id_grupo_seleccionado): ?>
                    <p>Por favor, seleccione un equipo para ver los datos médicos.</p>
                <?php else: ?>
                    <p>Seleccione una misión y un equipo para comenzar.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="container">
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                    // Obtener datos del usuario
                    $stmt_gps = $pdo->prepare("SELECT latitude, longitude FROM gps_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_gps->execute([':id_user' => $usuario['id_user']]);
                    $gps_data = $stmt_gps->fetch(PDO::FETCH_ASSOC);

                    $stmt_bpm = $pdo->prepare("SELECT bpm, SPo2 FROM bpm_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_bpm->execute([':id_user' => $usuario['id_user']]);
                    $bpm_data = $stmt_bpm->fetch(PDO::FETCH_ASSOC);

                    $stmt_gas = $pdo->prepare("SELECT ppm FROM gas_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_gas->execute([':id_user' => $usuario['id_user']]);
                    $gas_data = $stmt_gas->fetch(PDO::FETCH_ASSOC);

                    $stmt_prev = $pdo->prepare("SELECT estado_prev FROM prev_data WHERE id_user = :id_user ORDER BY timestamp_prev DESC LIMIT 1");
                    $stmt_prev->execute([':id_user' => $usuario['id_user']]);
                    $prev_data = $stmt_prev->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="card" id="card-<?= $usuario['id_user'] ?>">
                        <!-- Mapa -->
                        <div id="map-<?= $usuario['id_user'] ?>" class="map">
                            <?php if (!$gps_data): ?>
                                <div class="no-location">Ubicación no disponible</div>
                            <?php endif; ?>
                        </div>
                        
                        <h3><?= htmlspecialchars($usuario['nom_user'] . ' ' . $usuario['apel_user']) ?></h3>
                        
                        <p><strong>BPM:</strong> <span id="bpm-<?= $usuario['id_user'] ?>">
                            <?= isset($bpm_data['bpm']) ? htmlspecialchars($bpm_data['bpm']) : 'N/A' ?>
                        </span></p>
                        
                        <p><strong>SPO2:</strong> <span id="spo2-<?= $usuario['id_user'] ?>">
                            <?= isset($bpm_data['SPo2']) ? htmlspecialchars($bpm_data['SPo2']) . '%' : 'N/A' ?>
                        </span></p>
                        
                        <p><strong>PPM:</strong> <span id="ppm-<?= $usuario['id_user'] ?>">
                            <?= isset($gas_data['ppm']) ? htmlspecialchars($gas_data['ppm']) : 'N/A' ?>
                        </span></p>
                        
                        <div class="prevencion" id="prev-<?= $usuario['id_user'] ?>">
                            <strong>Prevención:</strong> 
                            <span>
                                <?php 
                                if (isset($prev_data['estado_prev'])) {
                                    switch ($prev_data['estado_prev']) {
                                        case 0: echo '<span class="prevencion-alerta">Estrés cardíaco</span>'; break;
                                        case 1: echo '<span class="prevencion-alerta">Fatiga extrema</span>'; break;
                                        case 2: echo '<span class="prevencion-peligro">Hipoxia</span>'; break;
                                        case 3: echo '<span class="prevencion-normal">Normal</span>'; break;
                                        default: echo '<span class="prevencion-desconocido">N/A</span>';
                                    }
                                } else {
                                    echo '<span class="prevencion-desconocido">N/A</span>';
                                }
                                ?>
                            </span>
                        </div>
                        
                        <div class="footer">ID: <?= htmlspecialchars($usuario['id_user']) ?></div>
                    </div>
                    
                    <script>
                        // Inicializar mapa para este usuario
                        document.addEventListener('DOMContentLoaded', function() {
                            initUserMap(
                                <?= $usuario['id_user'] ?>, 
                                <?= $gps_data['latitude'] ?? 'null' ?>, 
                                <?= $gps_data['longitude'] ?? 'null' ?>, 
                                '<?= htmlspecialchars($usuario['nom_user'] . ' ' . $usuario['apel_user']) ?>'
                            );
                        });
                    </script>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
    // Objeto para almacenar mapas y marcadores
    const userMaps = {};
    
    // Inicializar mapa para un usuario
    function initUserMap(userId, lat, lng, userName) {
        const mapElement = document.getElementById(`map-${userId}`);
        
        if (!lat || !lng) {
            return; // Ya mostramos mensaje en el HTML
        }

        try {
            // Crear el mapa
            const map = L.map(mapElement).setView([lat, lng], 15);
            
            // Almacenar referencia
            userMaps[userId] = {
                map: map,
                marker: null
            };
            
            // Añadir capa de OpenStreetMap
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
            }).addTo(map);

            // Añadir marcador
            userMaps[userId].marker = L.marker([lat, lng]).addTo(map)
                .bindPopup(`<b>${userName}</b><br>ID: ${userId}`);
            
            // Ajustar el mapa
            setTimeout(() => map.invalidateSize(), 100);
            
        } catch (error) {
            console.error(`Error al inicializar mapa para usuario ${userId}:`, error);
        }
    }
    
    // Actualizar datos del usuario
    function updateUserData(id_user) {
        fetch(`get_updated_data.php?id_user=${id_user}`)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red');
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    console.error('Error del servidor:', data.error);
                    return;
                }

                // 1. Actualizar datos básicos
                if (data.bpm_data) {
                    document.getElementById(`bpm-${id_user}`).textContent = data.bpm_data.bpm || 'N/A';
                    document.getElementById(`spo2-${id_user}`).textContent = data.bpm_data.SPo2 ? `${data.bpm_data.SPo2}%` : 'N/A';
                }

                if (data.gas_data) {
                    document.getElementById(`ppm-${id_user}`).textContent = data.gas_data.ppm || 'N/A';
                }

                // 2. Actualizar datos de prevención
                const prevElement = document.getElementById(`prev-${id_user}`);
                if (prevElement) {
                    let estadoTexto = 'N/A';
                    let estadoClass = 'prevencion-desconocido';
                    
                    if (data.prev_data && data.prev_data.codigo !== null) {
                        switch(data.prev_data.codigo) {
                            case 0: 
                                estadoTexto = 'Estrés cardíaco';
                                estadoClass = 'prevencion-alerta';
                                break;
                            case 1: 
                                estadoTexto = 'Fatiga extrema';
                                estadoClass = 'prevencion-alerta';
                                break;
                            case 2: 
                                estadoTexto = 'Hipoxia';
                                estadoClass = 'prevencion-peligro';
                                break;
                            case 3: 
                                estadoTexto = 'Normal';
                                estadoClass = 'prevencion-normal';
                                break;
                        }
                    }
                    
                    prevElement.innerHTML = `<strong>Prevención:</strong> <span class="${estadoClass}">${estadoTexto}</span>`;
                }

                // 3. Actualizar mapa si hay datos GPS
                if (data.gps_data) {
                    updateUserMap(id_user, data.gps_data.latitude, data.gps_data.longitude);
                }
            })
            .catch(error => console.error('Error al actualizar:', error));
    }
    
    // Actualizar mapa del usuario
    function updateUserMap(userId, lat, lng) {
        if (!userMaps[userId] || !lat || !lng) return;
        
        const { map, marker } = userMaps[userId];
        
        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            userMaps[userId].marker = L.marker([lat, lng]).addTo(map);
        }
        
        map.setView([lat, lng]);
    }
    
    // Iniciar actualizaciones periódicas para los usuarios visibles
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($mostrar_cards): ?>
            <?php foreach ($usuarios as $usuario): ?>
                // Actualizar inmediatamente al cargar
                updateUserData(<?= $usuario['id_user'] ?>);
                
                // Configurar actualización periódica cada 5 segundos
                setInterval(() => updateUserData(<?= $usuario['id_user'] ?>), 5000);
            <?php endforeach; ?>
        <?php endif; ?>
    });
</script>
</body>
</html>