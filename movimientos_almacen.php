<?php
session_start();
require_once 'db.php';
require_once 'config/paths.php';
require_once 'auth_check.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php'); // Redirigir a página de login
    exit;
}

// Procesar acciones CRUD
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Mensajes de feedback
$feedback = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar datos
                $required = ['id_item', 'id_tipo', 'cantidad', 'fecha_movimiento'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("El campo $field es requerido");
                    }
                }

                // Obtener tipo de movimiento para determinar si es entrada
                $stmt = $pdo->prepare("SELECT es_entrada FROM tipos_movimiento_almacen WHERE id_tipo = ?");
                $stmt->execute([$_POST['id_tipo']]);
                $tipo = $stmt->fetch();
                
                if (!$tipo) {
                    throw new Exception("Tipo de movimiento no válido");
                }

                // Iniciar transacción
                $pdo->beginTransaction();

                // 1. Registrar el movimiento
                $stmt = $pdo->prepare("INSERT INTO movimientos_almacen 
                    (id_item, id_tipo, id_usuario, id_responsable, cantidad, fecha_movimiento, destino, motivo, observaciones)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['id_item'],
                    $_POST['id_tipo'],
                    $_SESSION['id_user'],
                    $_POST['id_responsable'] ?? null,
                    $_POST['cantidad'],
                    $_POST['fecha_movimiento'],
                    $_POST['destino'] ?? null,
                    $_POST['motivo'] ?? null,
                    $_POST['observaciones'] ?? null
                ]);

                // 2. Actualizar stock en almacén
                $operacion = $tipo['es_entrada'] ? '+' : '-';
                $stmt = $pdo->prepare("UPDATE almacen SET cantidad = cantidad $operacion ? WHERE id_item = ?");
                $stmt->execute([$_POST['cantidad'], $_POST['id_item']]);

                // Confirmar transacción
                $pdo->commit();

                $feedback = ['success' => true, 'message' => 'Movimiento registrado exitosamente'];
            }
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Validar datos
                $required = ['id_item', 'id_tipo', 'cantidad', 'fecha_movimiento'];
                foreach ($required as $field) {
                    if (empty($_POST[$field])) {
                        throw new Exception("El campo $field es requerido");
                    }
                }

                // Obtener movimiento original
                $stmt = $pdo->prepare("SELECT id_item, id_tipo, cantidad FROM movimientos_almacen WHERE id_movimiento = ?");
                $stmt->execute([$id]);
                $movimiento_original = $stmt->fetch();

                if (!$movimiento_original) {
                    throw new Exception("Movimiento no encontrado");
                }

                // Obtener tipo de movimiento original y nuevo
                $stmt = $pdo->prepare("SELECT es_entrada FROM tipos_movimiento_almacen WHERE id_tipo IN (?, ?)");
                $stmt->execute([$movimiento_original['id_tipo'], $_POST['id_tipo']]);
                $tipos = $stmt->fetchAll();

                if (count($tipos) < 2) {
                    throw new Exception("Tipo de movimiento no válido");
                }

                $tipo_original = $tipos[0]['es_entrada'];
                $tipo_nuevo = $tipos[1]['es_entrada'];

                // Iniciar transacción
                $pdo->beginTransaction();

                // 1. Revertir movimiento original
                $operacion_revertir = $tipo_original ? '-' : '+';
                $stmt = $pdo->prepare("UPDATE almacen SET cantidad = cantidad $operacion_revertir ? WHERE id_item = ?");
                $stmt->execute([$movimiento_original['cantidad'], $movimiento_original['id_item']]);

                // 2. Aplicar nuevo movimiento
                $operacion_aplicar = $tipo_nuevo ? '+' : '-';
                $stmt = $pdo->prepare("UPDATE almacen SET cantidad = cantidad $operacion_aplicar ? WHERE id_item = ?");
                $stmt->execute([$_POST['cantidad'], $_POST['id_item']]);

                // 3. Actualizar registro de movimiento
                $stmt = $pdo->prepare("UPDATE movimientos_almacen SET
                    id_item = ?, id_tipo = ?, id_responsable = ?, cantidad = ?, 
                    fecha_movimiento = ?, destino = ?, motivo = ?, observaciones = ?
                    WHERE id_movimiento = ?");
                $stmt->execute([
                    $_POST['id_item'],
                    $_POST['id_tipo'],
                    $_POST['id_responsable'] ?? null,
                    $_POST['cantidad'],
                    $_POST['fecha_movimiento'],
                    $_POST['destino'] ?? null,
                    $_POST['motivo'] ?? null,
                    $_POST['observaciones'] ?? null,
                    $id
                ]);

                // Confirmar transacción
                $pdo->commit();

                $feedback = ['success' => true, 'message' => 'Movimiento actualizado exitosamente'];
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Obtener movimiento para revertirlo
                $stmt = $pdo->prepare("SELECT m.id_item, m.cantidad, t.es_entrada 
                                      FROM movimientos_almacen m
                                      JOIN tipos_movimiento_almacen t ON m.id_tipo = t.id_tipo
                                      WHERE m.id_movimiento = ?");
                $stmt->execute([$id]);
                $movimiento = $stmt->fetch();

                if (!$movimiento) {
                    throw new Exception("Movimiento no encontrado");
                }

                // Iniciar transacción
                $pdo->beginTransaction();

                // 1. Revertir movimiento en el stock
                $operacion = $movimiento['es_entrada'] ? '-' : '+';
                $stmt = $pdo->prepare("UPDATE almacen SET cantidad = cantidad $operacion ? WHERE id_item = ?");
                $stmt->execute([$movimiento['cantidad'], $movimiento['id_item']]);

                // 2. Eliminar el movimiento
                $stmt = $pdo->prepare("DELETE FROM movimientos_almacen WHERE id_movimiento = ?");
                $stmt->execute([$id]);

                // Confirmar transacción
                $pdo->commit();

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Movimiento eliminado exitosamente']);
                exit;
            }
            break;
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    $feedback = ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    $feedback = ['success' => false, 'message' => $e->getMessage()];
}

// Obtener datos para formularios
$items = $pdo->query("SELECT id_item, nombre FROM almacen ORDER BY nombre")->fetchAll();
$tipos_movimiento = $pdo->query("SELECT * FROM tipos_movimiento_almacen WHERE estado = 'activo' ORDER BY nombre")->fetchAll();
$usuarios = $pdo->query("SELECT id_user, CONCAT(nom_user, ' ', apel_user) as nombre FROM user WHERE status_user = 1 ORDER BY nom_user")->fetchAll();

// Obtener datos para editar/ver
$movimiento = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT m.*, 
                          i.nombre as item_nombre,
                          t.nombre as tipo_nombre,
                          CONCAT(u.nom_user, ' ', u.apel_user) as usuario_nombre,
                          CONCAT(r.nom_user, ' ', r.apel_user) as responsable_nombre
                          FROM movimientos_almacen m
                          JOIN almacen i ON m.id_item = i.id_item
                          JOIN tipos_movimiento_almacen t ON m.id_tipo = t.id_tipo
                          JOIN user u ON m.id_usuario = u.id_user
                          LEFT JOIN user r ON m.id_responsable = r.id_user
                          WHERE m.id_movimiento = ?");
    $stmt->execute([$id]);
    $movimiento = $stmt->fetch();
    
    if (!$movimiento) {
        header('Location: movimientos_almacen.php');
        exit;
    }
}

// Listar todos los movimientos si no es una acción específica
if ($action === 'list') {
    $movimientos = $pdo->query("SELECT m.*, 
                              i.nombre as item_nombre,
                              t.nombre as tipo_nombre,
                              t.es_entrada,
                              CONCAT(u.nom_user, ' ', u.apel_user) as usuario_nombre,
                              CONCAT(r.nom_user, ' ', r.apel_user) as responsable_nombre
                              FROM movimientos_almacen m
                              JOIN almacen i ON m.id_item = i.id_item
                              JOIN tipos_movimiento_almacen t ON m.id_tipo = t.id_tipo
                              JOIN user u ON m.id_usuario = u.id_user
                              LEFT JOIN user r ON m.id_responsable = r.id_user
                              ORDER BY m.fecha_movimiento DESC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos de Almacén - Sistema Bomberos</title>
    
    <!-- jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Estilos personalizados -->
    <link rel="stylesheet" href="<?= CSS_PATH ?>styles.css">
    
    <style>
        .badge-entrada {
            background-color: #28a745;
        }
        .badge-salida {
            background-color: #dc3545;
        }
        .badge-ajuste {
            background-color: #6c757d;
        }
        .fade-out {
            animation: fadeOut 0.4s;
        }
        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
    </style>
</head>
<body>  
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <!-- Contenedor para mensajes dinámicos -->
    <div id="ajax-messages" style="position: fixed; top: 20px; right: 20px; z-index: 1100;"></div>
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-exchange-alt"></i> <?= ucfirst($action) ?> Movimiento de Almacén
                </h1>
            </div>
            
            <?php if ($feedback['message']): ?>
            <div class="alert alert-<?= $feedback['success'] ? 'success' : 'danger' ?>">
                <?= $feedback['message'] ?>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($action, ['create', 'edit'])): ?>
            <!-- Formulario para Crear/Editar -->
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Item del Almacén</label>
                                    <select class="form-select" name="id_item" required>
                                        <option value="">Seleccionar item</option>
                                        <?php foreach ($items as $item): ?>
                                        <option value="<?= $item['id_item'] ?>" 
                                            <?= ($movimiento['id_item'] ?? '') == $item['id_item'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($item['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipo de Movimiento</label>
                                    <select class="form-select" name="id_tipo" id="tipo_movimiento" required>
                                        <option value="">Seleccionar tipo</option>
                                        <?php foreach ($tipos_movimiento as $tipo): ?>
                                        <option value="<?= $tipo['id_tipo'] ?>" 
                                            data-es-entrada="<?= $tipo['es_entrada'] ?>" 
                                            <?= ($movimiento['id_tipo'] ?? '') == $tipo['id_tipo'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tipo['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" name="cantidad" min="1" 
                                           value="<?= htmlspecialchars($movimiento['cantidad'] ?? '1') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fecha y Hora</label>
                                    <input type="datetime-local" class="form-control" name="fecha_movimiento" 
                                           value="<?= isset($movimiento['fecha_movimiento']) ? 
                                               date('Y-m-d\TH:i', strtotime($movimiento['fecha_movimiento'])) : 
                                               date('Y-m-d\TH:i') ?>" required>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3" id="responsable-container">
                                    <label class="form-label">Responsable (quien recibe/entrega)</label>
                                    <select class="form-select" name="id_responsable">
                                        <option value="">Seleccionar responsable</option>
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <option value="<?= $usuario['id_user'] ?>" 
                                            <?= ($movimiento['id_responsable'] ?? '') == $usuario['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($usuario['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Destino/Origen</label>
                                    <input type="text" class="form-control" name="destino" 
                                           value="<?= htmlspecialchars($movimiento['destino'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Motivo</label>
                                    <textarea class="form-control" name="motivo" rows="2"><?= 
                                        htmlspecialchars($movimiento['motivo'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Observaciones</label>
                                    <textarea class="form-control" name="observaciones" rows="2"><?= 
                                        htmlspecialchars($movimiento['observaciones'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="movimientos_almacen.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <script>
            $(document).ready(function() {
                // Mostrar/ocultar campo responsable según tipo de movimiento
                function toggleResponsable() {
                    const tipoSeleccionado = $('#tipo_movimiento option:selected').data('es-entrada');
                    if (tipoSeleccionado === false) {
                        $('#responsable-container').show();
                    } else if (tipoSeleccionado === true) {
                        $('#responsable-container').hide();
                    } else {
                        $('#responsable-container').show();
                    }
                }
                
                // Ejecutar al cargar y al cambiar selección
                toggleResponsable();
                $('#tipo_movimiento').change(toggleResponsable);
            });
            </script>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo movimiento -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Item:</strong> <?= htmlspecialchars($movimiento['item_nombre']) ?></p>
                            <p><strong>Tipo de Movimiento:</strong> 
                                <span class="badge 
                                    <?= $movimiento['es_entrada'] === '1' ? 'badge-entrada' : 
                                       ($movimiento['es_entrada'] === '0' ? 'badge-salida' : 'badge-ajuste') ?>">
                                    <?= htmlspecialchars($movimiento['tipo_nombre']) ?>
                                </span>
                            </p>
                            <p><strong>Cantidad:</strong> <?= htmlspecialchars($movimiento['cantidad']) ?></p>
                            <p><strong>Registrado por:</strong> <?= htmlspecialchars($movimiento['usuario_nombre']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <?php if ($movimiento['id_responsable']): ?>
                            <p><strong>Responsable:</strong> <?= htmlspecialchars($movimiento['responsable_nombre']) ?></p>
                            <?php endif; ?>
                            <p><strong>Fecha y Hora:</strong> <?= date('d/m/Y H:i', strtotime($movimiento['fecha_movimiento'])) ?></p>
                            <?php if ($movimiento['destino']): ?>
                            <p><strong>Destino/Origen:</strong> <?= htmlspecialchars($movimiento['destino']) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($movimiento['motivo']): ?>
                    <div class="mb-3">
                        <h5>Motivo</h5>
                        <p><?= nl2br(htmlspecialchars($movimiento['motivo'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($movimiento['observaciones']): ?>
                    <div class="mb-3">
                        <h5>Observaciones</h5>
                        <p><?= nl2br(htmlspecialchars($movimiento['observaciones'])) ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="text-end">
                        <a href="movimientos_almacen.php" class="btn btn-secondary">Volver</a>
                        <a href="movimientos_almacen.php?action=edit&id=<?= $movimiento['id_movimiento'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Listado de movimientos -->
            <div class="mb-4">
                <a href="movimientos_almacen.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Movimiento
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="movimientos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Item</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($movimientos as $mov): ?>
                            <tr>
                                <td><?= htmlspecialchars($mov['id_movimiento']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($mov['fecha_movimiento'])) ?></td>
                                <td><?= htmlspecialchars($mov['item_nombre']) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $mov['es_entrada'] === '1' ? 'badge-entrada' : 
                                           ($mov['es_entrada'] === '0' ? 'badge-salida' : 'badge-ajuste') ?>">
                                        <?= htmlspecialchars($mov['tipo_nombre']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($mov['cantidad']) ?></td>
                                <td><?= htmlspecialchars($mov['responsable_nombre'] ?? 'N/A') ?></td>
                                <td>
                                    <a href="movimientos_almacen.php?action=view&id=<?= $mov['id_movimiento'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="movimientos_almacen.php?action=edit&id=<?= $mov['id_movimiento'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $mov['id_movimiento'] ?>">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        try {
            $('#movimientos-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[1, 'desc']],
                dom: '<"top"lf>rt<"bottom"ip>',
                pageLength: 25
            });
        } catch (e) {
            console.error('Error al inicializar DataTables:', e);
            $('#movimientos-table').addClass('table').addClass('table-striped');
        }
        
        // Función para mostrar mensajes
        function showMessage(type, message) {
            const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
            const html = `
                <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            `;
            $('#ajax-messages').append(html);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                $('.alert').alert('close');
            }, 5000);
        }
        
        // Eliminar movimiento con confirmación
        $(document).on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            const $row = $(this).closest('tr');
            
            if (!confirm('¿Estás seguro que deseas eliminar este movimiento?\nEsta acción afectará el inventario y no se puede deshacer.')) {
                return false;
            }
            
            $row.css('opacity', '0.5');
            
            $.ajax({
                url: 'movimientos_almacen.php?action=delete&id=' + id,
                type: 'POST',
                dataType: 'json'
            })
            .done(function(response) {
                if (response && response.success) {
                    $row.addClass('fade-out');
                    setTimeout(() => {
                        $row.remove();
                    }, 400);
                    
                    showMessage('success', response.message);
                } else {
                    showMessage('danger', response?.message || 'Error al eliminar el movimiento');
                    $row.css('opacity', '1');
                }
            })
            .fail(function(jqXHR) {
                let errorMsg = 'Error al comunicarse con el servidor';
                try {
                    const response = JSON.parse(jqXHR.responseText);
                    errorMsg = response.message || errorMsg;
                } catch (e) {
                    console.error("Error parsing response:", e);
                }
                showMessage('danger', errorMsg);
                $row.css('opacity', '1');
            });
        });
    });
    </script>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>