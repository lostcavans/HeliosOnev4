<?php
session_start();
require_once 'db.php';
require_once 'config/paths.php';
require_once 'auth_check.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php'); // Redirigir a página de login
    exit;
}


include 'header.php';
include 'sidebar.php';

// Inicializar variable de notificación
$notification_count = 0;

// Procesar asignación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();
        
        // Validar campos requeridos
        $required_fields = ['id_repuesto', 'id_dispositivo', 'cantidad', 'motivo'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("El campo " . str_replace('_', ' ', $field) . " es requerido");
            }
        }
        
        // Validar cantidad
        if (!is_numeric($_POST['cantidad']) || $_POST['cantidad'] <= 0) {
            throw new Exception("La cantidad debe ser un número positivo");
        }
        
        // Obtener ID del usuario responsable desde la sesión
        $id_responsable = $_SESSION['id_user'];
        
        // Registrar la asignación
        $stmt = $pdo->prepare("INSERT INTO asignacion_repuestos 
                              (id_repuesto, id_dispositivo, cantidad, fecha_asignacion, motivo, id_responsable) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['id_repuesto'],
            $_POST['id_dispositivo'],
            $_POST['cantidad'],
            date('Y-m-d'),
            $_POST['motivo'],
            $id_responsable  // Usamos la variable de sesión correcta
        ]);
        
        // Actualizar el stock
        $stmt = $pdo->prepare("UPDATE repuestos SET cantidad = cantidad - ? WHERE id_repuesto = ?");
        $stmt->execute([$_POST['cantidad'], $_POST['id_repuesto']]);
        
        $pdo->commit();
        
        $feedback = [
            'success' => true,
            'message' => 'Repuesto asignado exitosamente'
        ];
    } catch (PDOException $e) {
        $pdo->rollBack();
        $feedback = [
            'success' => false,
            'message' => 'Error al asignar repuesto: ' . $e->getMessage()
        ];
    } catch (Exception $e) {
        $feedback = [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

// Obtener repuestos disponibles
$repuestos = $pdo->query("SELECT * FROM repuestos WHERE cantidad > 0 ORDER BY nombre")->fetchAll();

// Obtener dispositivos
$dispositivos = $pdo->query("SELECT * FROM equipos WHERE estado = 'disponible' OR estado = 'en_uso' ORDER BY nombre")->fetchAll();

// Obtener historial de asignaciones
$asignaciones = $pdo->query("SELECT ar.*, r.nombre as repuesto_nombre, e.nombre as dispositivo_nombre, 
                            CONCAT(u.nom_user, ' ', u.apel_user) as responsable_nombre
                            FROM asignacion_repuestos ar
                            JOIN repuestos r ON ar.id_repuesto = r.id_repuesto
                            JOIN equipos e ON ar.id_dispositivo = e.id_equipo
                            JOIN user u ON ar.id_responsable = u.id_user
                            ORDER BY ar.fecha_asignacion DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Repuestos - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .fade-out {
            animation: fadeOut 0.4s;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        #ajax-messages {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1100;
            max-width: 400px;
        }
    </style>
</head>
<body>  
    
    
    <div id="ajax-messages"></div>
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-hand-holding"></i> Asignar Repuestos
                    <small class="text-muted">Distribución de repuestos a equipos</small>
                </h1>
            </div>
            
            <?php if (isset($feedback)): ?>
            <div class="alert alert-<?= $feedback['success'] ? 'success' : 'danger' ?> alert-dismissible fade show">
                <?= $feedback['message'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php endif; ?>
            
            <div class="card mb-4">
                <div class="card-body">
                    <form method="POST" id="formAsignar" novalidate>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_repuesto" class="form-label">Repuesto <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_repuesto" name="id_repuesto" required>
                                        <option value="">Seleccionar repuesto</option>
                                        <?php foreach ($repuestos as $repuesto): ?>
                                        <option value="<?= $repuesto['id_repuesto'] ?>" data-stock="<?= $repuesto['cantidad'] ?>">
                                            <?= htmlspecialchars($repuesto['nombre']) ?> (Stock: <?= $repuesto['cantidad'] ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un repuesto</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" value="1" required>
                                    <small id="stock-disponible" class="form-text text-muted"></small>
                                    <div class="invalid-feedback">Ingrese una cantidad válida</div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_dispositivo" class="form-label">Dispositivo <span class="text-danger">*</span></label>
                                    <select class="form-select" id="id_dispositivo" name="id_dispositivo" required>
                                        <option value="">Seleccionar dispositivo</option>
                                        <?php foreach ($dispositivos as $dispositivo): ?>
                                        <option value="<?= $dispositivo['id_equipo'] ?>">
                                            <?= htmlspecialchars($dispositivo['nombre']) ?> 
                                            (<?= ucfirst(str_replace('_', ' ', $dispositivo['estado'])) ?>)
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="invalid-feedback">Por favor seleccione un dispositivo</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="motivo" class="form-label">Motivo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="motivo" name="motivo" required>
                                    <div class="invalid-feedback">Por favor ingrese un motivo</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Asignar Repuesto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-history me-2"></i>Historial de Asignaciones</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="asignaciones-table" class="table table-striped table-hover" style="width:100%">
                            <thead class="table-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Repuesto</th>
                                    <th>Dispositivo</th>
                                    <th>Cantidad</th>
                                    <th>Motivo</th>
                                    <th>Responsable</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asignaciones as $asignacion): ?>
                                <tr>
                                    <td><?= date('d/m/Y', strtotime($asignacion['fecha_asignacion'])) ?></td>
                                    <td><?= htmlspecialchars($asignacion['repuesto_nombre']) ?></td>
                                    <td><?= htmlspecialchars($asignacion['dispositivo_nombre']) ?></td>
                                    <td><?= htmlspecialchars($asignacion['cantidad']) ?></td>
                                    <td><?= htmlspecialchars($asignacion['motivo']) ?></td>
                                    <td><?= htmlspecialchars($asignacion['responsable_nombre']) ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#asignaciones-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            order: [[0, 'desc']],
            dom: '<"top"lf>rt<"bottom"ip>'
        });

        // Actualizar stock disponible
        $('#id_repuesto').change(function() {
            const selected = $(this).find('option:selected');
            const stock = selected.data('stock') || 0;
            $('#cantidad').attr('max', stock);
            $('#stock-disponible').text(`Stock disponible: ${stock}`);
        }).trigger('change');

        // Validación de formulario
        $('#formAsignar').submit(function(e) {
            const form = this;
            
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();
            }
            
            // Validación adicional de stock
            const cantidad = parseInt($('#cantidad').val());
            const stock = parseInt($('#id_repuesto option:selected').data('stock'));
            
            if (cantidad > stock) {
                e.preventDefault();
                showMessage('danger', 'La cantidad solicitada supera el stock disponible');
                $('#cantidad').addClass('is-invalid');
                return false;
            }
            
            $(form).addClass('was-validated');
            return true;
        });

        // Función para mostrar mensajes
        function showMessage(type, message) {
            const alertClass = `alert-${type}`;
            const alert = $(`
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `);
            
            $('#ajax-messages').append(alert);
            
            setTimeout(() => {
                alert.alert('close');
            }, 5000);
        }
    });
    </script>

    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>