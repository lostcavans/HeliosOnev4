<?php
session_start();
require_once 'db.php';
require_once 'config/paths.php';
require_once 'auth_check.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php'); // Redirigir a página de login
    exit;
}


// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");

require_once 'db.php';
include 'header.php';
include 'sidebar.php';

// Obtener mantenimientos para el calendario
$mantenimientos = $pdo->query("SELECT m.*, e.nombre as equipo_nombre 
                              FROM mantenimientos m
                              JOIN equipos e ON m.id_equipo = e.id_equipo
                              ORDER BY m.fecha_programada")->fetchAll();

// Convertir a formato para FullCalendar
$events = [];
foreach ($mantenimientos as $m) {
    $events[] = [
        'id' => $m['id_mantenimiento'],
        'title' => $m['equipo_nombre'] . ' - ' . ucfirst($m['tipo']),
        'start' => $m['fecha_programada'],
        'end' => $m['fecha_programada'],
        'color' => $m['estado'] == 'completado' ? '#28a745' : 
                  ($m['estado'] == 'en_proceso' ? '#ffc107' : 
                  ($m['estado'] == 'cancelado' ? '#dc3545' : '#007bff')),
        'extendedProps' => [
            'tipo' => $m['tipo'],
            'estado' => $m['estado'],
            'equipo' => $m['equipo_nombre']
        ]
    ];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendario de Mantenimiento - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        #calendar {
            min-height: 600px;
            width: 100%;
        }
        .fc-event {
            cursor: pointer;
            font-size: 0.85em;
            padding: 2px;
        }
        .legend {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 20px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        .legend-item {
            display: flex;
            align-items: center;
        }
        .legend-color {
            width: 20px;
            height: 20px;
            margin-right: 8px;
            border-radius: 3px;
            border: 1px solid #dee2e6;
        }
        .fc-toolbar-title {
            font-size: 1.5em;
        }
        
        /* Estilos para el panel de información */
        #infoPanel {
            position: fixed;
            top: 100px;
            right: 20px;
            width: 350px;
            background: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            padding: 20px;
            z-index: 1000;
            display: none;
        }
        #infoPanel h4 {
            margin-top: 0;
            color: #2c3e50;
            border-bottom: 2px solid #f1f1f1;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .close-panel {
            position: absolute;
            top: 15px;
            right: 15px;
            cursor: pointer;
            font-size: 1.5em;
            color: #7f8c8d;
        }
        .close-panel:hover {
            color: #e74c3c;
        }
        .info-item {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 1px solid #f5f5f5;
        }
        .info-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .info-label {
            font-weight: 600;
            color: #34495e;
        }
        .info-value {
            color: #2c3e50;
        }
    </style>
</head>
<body>  
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-calendar-alt"></i> Calendario de Mantenimiento
                    <small class="text-muted">Programación visual</small>
                </h1>
            </div>
            
            <div class="mb-4">
                <a href="crud_mantenimiento.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Programar Mantenimiento
                </a>
                <a href="crud_mantenimiento.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Ver Listado
                </a>
            </div>
            
            <div class="legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #007bff;"></div>
                    <span>Pendiente</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #ffc107;"></div>
                    <span>En Proceso</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #28a745;"></div>
                    <span>Completado</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #dc3545;"></div>
                    <span>Cancelado</span>
                </div>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <div id="calendar"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Panel de información -->
    <div id="infoPanel">
        <span class="close-panel">&times;</span>
        <h4>Detalles del Mantenimiento</h4>
        
        <div class="info-item">
            <div class="info-label">Equipo:</div>
            <div class="info-value" id="panelEquipo">-</div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Tipo:</div>
            <div class="info-value" id="panelTipo">-</div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Estado:</div>
            <div class="info-value" id="panelEstado">-</div>
        </div>
        
        <div class="info-item">
            <div class="info-label">Fecha:</div>
            <div class="info-value" id="panelFecha">-</div>
        </div>
        
        <div class="mt-3">
            <a href="#" class="btn btn-primary btn-sm" id="panelEditLink">
                <i class="fas fa-edit"></i> Ver Detalles Completos
            </a>
        </div>
    </div>

    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/locales/es.min.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar calendario
        var calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
            initialView: 'dayGridMonth',
            locale: 'es',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listMonth'
            },
            events: <?php echo json_encode($events); ?>,
            eventClick: function(info) {
                info.jsEvent.preventDefault();
                
                // Obtener datos del evento
                var event = info.event;
                var fecha = event.start ? event.start.toLocaleDateString('es-ES', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) : 'No especificada';
                
                // Actualizar panel
                document.getElementById('panelEquipo').textContent = event.extendedProps.equipo || 'No especificado';
                document.getElementById('panelTipo').textContent = event.extendedProps.tipo || 'No especificado';
                document.getElementById('panelEstado').textContent = event.extendedProps.estado || 'No especificado';
                document.getElementById('panelFecha').textContent = fecha;
                
                // Configurar enlace
                if (event.id) {
                    document.getElementById('panelEditLink').href = 'crud_mantenimiento.php?action=view&id=' + event.id;
                }
                
                // Mostrar panel
                document.getElementById('infoPanel').style.display = 'block';
            }
        });
        
        calendar.render();
        
        // Cerrar panel al hacer clic en la X
        document.querySelector('.close-panel').addEventListener('click', function() {
            document.getElementById('infoPanel').style.display = 'none';
        });
        
        // Cerrar panel al hacer clic fuera de él
        document.addEventListener('click', function(e) {
            var panel = document.getElementById('infoPanel');
            var isClickInsidePanel = panel.contains(e.target);
            var isCalendarEvent = e.target.closest('.fc-event');
            
            if (!isClickInsidePanel && !isCalendarEvent && panel.style.display === 'block') {
                panel.style.display = 'none';
            }
        });
    });
    </script>
</body>
</html>