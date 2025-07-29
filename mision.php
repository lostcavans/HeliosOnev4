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



/**
 * Obtiene las misiones activas (stat_mis = 0)
 */
$misiones_activas = $pdo->query("
    SELECT m.*, g.nom_grup 
    FROM mision m
    LEFT JOIN grupo g ON m.id_grupo = g.id_grupo
    WHERE m.stat_mis = 0 
    ORDER BY m.fec_mis DESC
")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Obtiene las misiones concluidas (stat_mis = 1)
 */
$misiones_concluidas = $pdo->query("
    SELECT m.*, g.nom_grup 
    FROM mision m
    LEFT JOIN grupo g ON m.id_grupo = g.id_grupo
    WHERE m.stat_mis = 1 
    ORDER BY m.fin_mis DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Obtiene equipos libres (no asignados a misiones activas)
 */
$equipos_libres = $pdo->query("
    SELECT g.* 
    FROM grupo g
    WHERE g.id_grupo NOT IN (
        SELECT m.id_grupo 
        FROM mision m 
        WHERE m.stat_mis = 0 AND m.id_grupo IS NOT NULL
    )
    AND g.stat_grupo = 1
")->fetchAll(PDO::FETCH_ASSOC);

/**
 * Calcula el tiempo transcurrido desde el inicio de la misión
 */
function tiempo_transcurrido($fecha_inicio) {
    $inicio = new DateTime($fecha_inicio);
    $ahora = new DateTime();
    $diferencia = $ahora->diff($inicio);
    
    if ($diferencia->d > 0) {
        return $diferencia->format('%d días %h hrs');
    }
    return $diferencia->format('%h hrs %i min');
}

/**
 * Calcula la duración total de una misión
 */
function duracion_mision($inicio, $fin) {
    $inicio = new DateTime($inicio);
    $fin = new DateTime($fin);
    $diferencia = $fin->diff($inicio);
    
    if ($diferencia->d > 0) {
        return $diferencia->format('%d días %h hrs');
    }
    return $diferencia->format('%h hrs %i min');
}

/**
 * Cuenta los miembros de un equipo
 */
function contar_miembros($pdo, $id_grupo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM user_grup WHERE id_grupo = ?");
    $stmt->execute([$id_grupo]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Helios - Gestión de Misiones</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3498db;
            --primary-dark: #2980b9;
            --success: #2ecc71;
            --warning: #f39c12;
            --danger: #e74c3c;
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
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        h1 {
            color: var(--dark);
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .section-title {
            color: var(--dark);
            margin: 20px 0 15px;
            font-size: 1.5rem;
        }
        
        /* Sistema de pestañas */
        .tab-container {
            margin-bottom: 30px;
        }
        
        .tab-buttons {
            display: flex;
            border-bottom: 1px solid #ddd;
            margin-bottom: 20px;
        }
        
        .tab-button {
            padding: 12px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-weight: 500;
            color: #7f8c8d;
            position: relative;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        
        .tab-button:hover {
            color: var(--primary);
        }
        
        .tab-button.active {
            color: var(--primary);
            font-weight: 600;
        }
        
        .tab-button.active::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            height: 3px;
            background: var(--primary);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .tab-content.active {
            display: block;
        }
        
        /* Tarjetas de misiones */
        .mission-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .mission-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .mission-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .mission-card.active {
            border-left-color: var(--success);
        }
        
        .mission-card.completed {
            border-left-color: var(--warning);
            opacity: 0.9;
        }
        
        .mission-card h3 {
            margin-top: 0;
            color: var(--dark);
            font-size: 1.3rem;
            margin-bottom: 10px;
        }
        
        .mission-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9em;
            color: #7f8c8d;
            margin: 10px 0;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .mission-meta span {
            display: flex;
            align-items: center;
        }
        
        .mission-card p {
            color: #34495e;
            margin: 15px 0;
            line-height: 1.5;
        }
        
        /* Badges */
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.8em;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-active {
            background-color: var(--success);
            color: white;
        }
        
        .badge-completed {
            background-color: var(--warning);
            color: white;
        }
        
        .badge-free {
            background-color: var(--primary);
            color: white;
        }
        
        /* Equipos */
        .team-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--primary);
        }
        
        .team-card h3 {
            margin-top: 0;
            color: var(--dark);
        }
        
        /* Botones */
        .button {
            display: inline-block;
            padding: 8px 16px;
            background-color: var(--primary);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 14px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
            margin-right: 10px;
        }
        
        .button:hover {
            background-color: var(--primary-dark);
            color: white;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .dashboard-contentPage {
                margin-left: 0;
            }
            
            .mission-cards {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            }
        }
        
        @media (max-width: 768px) {
            .tab-buttons {
                flex-direction: column;
            }
            
            .tab-button {
                text-align: left;
                border-bottom: 1px solid #eee;
            }
            
            .mission-cards {
                grid-template-columns: 1fr;
            }
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    
    <div class="dashboard-container">
        <h1>Gestión de Misiones</h1>
        
        <div class="tab-container">
            <div class="tab-buttons">
                <button class="tab-button active" data-tab="active-missions">Misiones Activas</button>
                <button class="tab-button" data-tab="completed-missions">Misiones Concluidas</button>
                <button class="tab-button" data-tab="free-teams">Equipos Libres</button>
            </div>
            
            <!-- Pestaña de Misiones Activas -->
            <div id="active-missions" class="tab-content active">
                <h2 class="section-title">Misiones en Curso</h2>
                
                <?php if (empty($misiones_activas)): ?>
                    <div class="mission-card">
                        <p>No hay misiones activas en este momento.</p>
                        <a href="create_mission.php" class="button">Crear Nueva Misión</a>
                    </div>
                <?php else: ?>
                    <div class="mission-cards">
                        <?php foreach ($misiones_activas as $mision): ?>
                            <div class="mission-card active">
                                <h3><?= htmlspecialchars($mision['nom_mis']) ?></h3>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-calendar"></i> Inició: <?= date('d/m/Y H:i', strtotime($mision['fec_mis'])) ?></span>
                                    <span class="badge badge-active">En Progreso</span>
                                </div>
                                <p><?= htmlspecialchars($mision['des_mis']) ?></p>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-accounts"></i> Equipo: <?= htmlspecialchars($mision['nom_grup'] ?? 'Sin asignar') ?></span>
                                    <span><i class="zmdi zmdi-time"></i> Duración: <?= tiempo_transcurrido($mision['fec_mis']) ?></span>
                                </div>
                                
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pestaña de Misiones Concluidas -->
            <div id="completed-missions" class="tab-content">
                <h2 class="section-title">Historial de Misiones</h2>
                
                <?php if (empty($misiones_concluidas)): ?>
                    <div class="mission-card">
                        <p>No hay misiones concluidas recientemente.</p>
                    </div>
                <?php else: ?>
                    <div class="mission-cards">
                        <?php foreach ($misiones_concluidas as $mision): ?>
                            <div class="mission-card completed">
                                <h3><?= htmlspecialchars($mision['nom_mis']) ?></h3>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-calendar"></i> Finalizó: <?= date('d/m/Y H:i', strtotime($mision['fin_mis'])) ?></span>
                                    <span class="badge badge-completed">Concluida</span>
                                </div>
                                <p><?= htmlspecialchars($mision['des_mis']) ?></p>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-time"></i> Duración: <?= duracion_mision($mision['fec_mis'], $mision['fin_mis']) ?></span>
                                    <span><i class="zmdi zmdi-accounts"></i> Equipo: <?= htmlspecialchars($mision['nom_grup'] ?? 'N/A') ?></span>
                                </div>
                                <div>
                                    
                                    <?php if ($_SESSION['id_cargo'] == 1): // Solo para administradores ?>
                                        
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Pestaña de Equipos Libres -->
            <div id="free-teams" class="tab-content">
                <h2 class="section-title">Equipos Disponibles</h2>
                
                <?php if (empty($equipos_libres)): ?>
                    <div class="mission-card">
                        <p>Todos los equipos están asignados actualmente.</p>
                        <a href="create_team.php" class="button">Crear Nuevo Equipo</a>
                    </div>
                <?php else: ?>
                    <div class="mission-cards">
                        <?php foreach ($equipos_libres as $equipo): ?>
                            <div class="team-card">
                                <h3><?= htmlspecialchars($equipo['nom_grup']) ?></h3>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-accounts"></i> Miembros: <?= contar_miembros($pdo, $equipo['id_grupo']) ?></span>
                                    <span class="badge badge-free">Disponible</span>
                                </div>
                                <div class="mission-meta">
                                    <span><i class="zmdi zmdi-time"></i> Última misión: 
                                        <?php 
                                        $ultima_mision = $pdo->query("SELECT MAX(fin_mis) as ultima FROM mision WHERE id_grupo = ".$equipo['id_grupo'])->fetch(PDO::FETCH_ASSOC);
                                        echo $ultima_mision['ultima'] ? date('d/m/Y', strtotime($ultima_mision['ultima'])) : 'Nunca';
                                        ?>
                                    </span>
                                </div>
                                
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script>
    // Sistema de pestañas
    document.addEventListener('DOMContentLoaded', function() {
        const tabButtons = document.querySelectorAll('.tab-button');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remover clase active de todos los botones y contenidos
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Agregar clase active al botón clickeado
                button.classList.add('active');
                
                // Mostrar el contenido correspondiente
                const tabId = button.getAttribute('data-tab');
                document.getElementById(tabId).classList.add('active');
                
                // Opcional: Guardar la pestaña activa en localStorage
                localStorage.setItem('activeTab', tabId);
            });
        });
        
        // Recuperar pestaña activa si existe
        const activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            document.querySelector(`.tab-button[data-tab="${activeTab}"]`).click();
        }
        
        // Actualización periódica de datos (cada 60 segundos)
        setInterval(() => {
            fetch('update_missions.php')
                .then(response => response.json())
                .then(data => {
                    // Aquí puedes actualizar la interfaz si lo deseas
                    console.log('Datos actualizados:', data);
                });
        }, 60000);
    });
</script>
</body>
</html>