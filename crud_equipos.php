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
                $stmt = $pdo->prepare("INSERT INTO equipos (nombre, descripcion, tipo, serial, fecha_adquisicion, estado, id_responsable, id_categoria, ultimo_mantenimiento, proximo_mantenimiento) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['tipo'],
                    $_POST['serial'],
                    $_POST['fecha_adquisicion'],
                    $_POST['estado'],
                    $_POST['id_responsable'] ?: null,
                    $_POST['id_categoria'] ?: null,
                    $_POST['ultimo_mantenimiento'] ?: null,
                    $_POST['proximo_mantenimiento'] ?: null
                ]);
                
                if (!empty($_POST['id_responsable'])) {
                    $pdo->prepare("INSERT INTO historial_asignaciones (id_equipo, id_responsable, id_asignador, fecha_asignacion) VALUES (?, ?, ?, NOW())")
                       ->execute([$pdo->lastInsertId(), $_POST['id_responsable'], $_SESSION['user_id']]);
                }
                
                $feedback = ['success' => true, 'message' => 'Equipo creado exitosamente'];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("SELECT id_responsable FROM equipos WHERE id_equipo = ?");
                $stmt->execute([$id]);
                $current = $stmt->fetch();
                
                if ($current['id_responsable'] != $_POST['id_responsable']) {
                    if (!empty($current['id_responsable'])) {
                        $pdo->prepare("UPDATE historial_asignaciones SET fecha_fin = NOW() WHERE id_equipo = ? AND id_responsable = ? AND fecha_fin IS NULL")
                           ->execute([$id, $current['id_responsable']]);
                    }
                    
                    if (!empty($_POST['id_responsable'])) {
                        $pdo->prepare("INSERT INTO historial_asignaciones (id_equipo, id_responsable, id_asignador, fecha_asignacion) VALUES (?, ?, ?, NOW())")
                           ->execute([$id, $_POST['id_responsable'], $_SESSION['user_id']]);
                    }
                }
                
                $stmt = $pdo->prepare("UPDATE equipos SET nombre=?, descripcion=?, tipo=?, serial=?, fecha_adquisicion=?, estado=?, id_responsable=?, id_categoria=?, ultimo_mantenimiento=?, proximo_mantenimiento=? WHERE id_equipo=?");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['tipo'],
                    $_POST['serial'],
                    $_POST['fecha_adquisicion'],
                    $_POST['estado'],
                    $_POST['id_responsable'] ?: null,
                    $_POST['id_categoria'] ?: null,
                    $_POST['ultimo_mantenimiento'] ?: null,
                    $_POST['proximo_mantenimiento'] ?: null,
                    $id
                ]);
                $feedback = ['success' => true, 'message' => 'Equipo actualizado exitosamente'];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                try {
                    $pdo->beginTransaction();
                    
                    // Eliminar primero el historial
                    $stmt = $pdo->prepare("DELETE FROM historial_asignaciones WHERE id_equipo = ?");
                    $stmt->execute([$id]);
                    
                    // Luego eliminar el equipo
                    $stmt = $pdo->prepare("DELETE FROM equipos WHERE id_equipo = ?");
                    $stmt->execute([$id]);
                    
                    $pdo->commit();
                    
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true, 
                        'message' => 'Equipo eliminado correctamente',
                        'deleted_id' => $id
                    ]);
                    exit;
                    
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'Error al eliminar: ' . $e->getMessage()
                    ]);
                    exit;
                }
            }
            break;
            
        case 'reasignar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_equipo = $_POST['id_equipo'];
                $id_responsable = $_POST['id_responsable'];
                $motivo = $_POST['motivo'] ?? 'Reasignación';
                
                $stmt = $pdo->prepare("SELECT id_responsable FROM equipos WHERE id_equipo = ?");
                $stmt->execute([$id_equipo]);
                $current = $stmt->fetch();
                
                if (!empty($current['id_responsable'])) {
                    $pdo->prepare("UPDATE historial_asignaciones SET fecha_fin = NOW() WHERE id_equipo = ? AND id_responsable = ? AND fecha_fin IS NULL")
                       ->execute([$id_equipo, $current['id_responsable']]);
                }
                
                if (!empty($id_responsable)) {
                    $pdo->prepare("INSERT INTO historial_asignaciones (id_equipo, id_responsable, id_asignador, fecha_asignacion, motivo) VALUES (?, ?, ?, NOW(), ?)")
                       ->execute([$id_equipo, $id_responsable, $_SESSION['user_id'], $motivo]);
                }
                
                $pdo->prepare("UPDATE equipos SET id_responsable = ? WHERE id_equipo = ?")
                   ->execute([$id_responsable, $id_equipo]);
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Responsable reasignado correctamente']);
                exit;
            }
            break;
    }
} catch (PDOException $e) {
    $feedback = ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
} catch (Exception $e) {
    $feedback = ['success' => false, 'message' => $e->getMessage()];
}

// Obtener categorías disponibles
$categorias = $pdo->query("SELECT * FROM categorias_equipos WHERE estado = 'activo' ORDER BY nombre")->fetchAll();

// Obtener datos para editar/ver
$equipo = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT e.*, c.nombre as categoria_nombre, CONCAT(u.nom_user, ' ', u.apel_user) as responsable_nombre 
                          FROM equipos e 
                          LEFT JOIN categorias_equipos c ON e.id_categoria = c.id_categoria
                          LEFT JOIN user u ON e.id_responsable = u.id_user 
                          WHERE e.id_equipo = ?");
    $stmt->execute([$id]);
    $equipo = $stmt->fetch();
    
    if (!$equipo) {
        header('Location: crud_equipos.php');
        exit;
    }
}

// Listar todos los equipos si no es una acción específica
if ($action === 'list') {
    $equipos = $pdo->query("SELECT e.*, c.nombre as categoria_nombre, CONCAT(u.nom_user, ' ', u.apel_user) as responsable_nombre 
                           FROM equipos e 
                           LEFT JOIN categorias_equipos c ON e.id_categoria = c.id_categoria
                           LEFT JOIN user u ON e.id_responsable = u.id_user 
                           ORDER BY e.nombre")->fetchAll();
}

// Obtener responsables disponibles
$responsables = $pdo->query("SELECT u.id_user, CONCAT(u.nom_user, ' ', u.apel_user) as nombre
                            FROM user u
                            LEFT JOIN equipos e ON u.id_user = e.id_responsable
                            WHERE e.id_responsable IS NULL
                            ORDER BY u.nom_user")->fetchAll();

// Obtener historial de asignaciones
if (isset($equipo)) {
    $historial = $pdo->prepare("SELECT h.*, 
                               CONCAT(r.nom_user, ' ', r.apel_user) as responsable,
                               CONCAT(a.nom_user, ' ', a.apel_user) as asignador
                               FROM historial_asignaciones h
                               JOIN user r ON h.id_responsable = r.id_user
                               JOIN user a ON h.id_asignador = a.id_user
                               WHERE h.id_equipo = ?
                               ORDER BY h.fecha_asignacion DESC");
    $historial->execute([$equipo['id_equipo']]);
    $historial = $historial->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Equipos - Sistema Bomberos</title>
    
    <!-- jQuery primero -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Estilos -->
    <link rel="stylesheet" href="<?= CSS_PATH ?>styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <style>
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
                    <i class="fas fa-tools"></i> <?= ucfirst($action) ?> Equipo
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
                                    <label class="form-label">Nombre</label>
                                    <input type="text" class="form-control" name="nombre" 
                                           value="<?= htmlspecialchars($equipo['nombre'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="3"><?= 
                                        htmlspecialchars($equipo['descripcion'] ?? '') ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Tipo</label>
                                    <input type="text" class="form-control" name="tipo" 
                                           value="<?= htmlspecialchars($equipo['tipo'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Categoría</label>
                                    <select class="form-select" name="id_categoria" required>
                                        <option value="">Seleccionar categoría</option>
                                        <?php foreach ($categorias as $cat): ?>
                                        <option value="<?= $cat['id_categoria'] ?>" <?= ($equipo['id_categoria'] ?? '') == $cat['id_categoria'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($cat['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Serial/Número</label>
                                    <input type="text" class="form-control" name="serial" 
                                           value="<?= htmlspecialchars($equipo['serial'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Fecha Adquisición</label>
                                    <input type="date" class="form-control" name="fecha_adquisicion" 
                                           value="<?= htmlspecialchars($equipo['fecha_adquisicion'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="estado" required>
                                        <option value="disponible" <?= ($equipo['estado'] ?? '') == 'disponible' ? 'selected' : '' ?>>Disponible</option>
                                        <option value="en_uso" <?= ($equipo['estado'] ?? '') == 'en_uso' ? 'selected' : '' ?>>En uso</option>
                                        <option value="mantenimiento" <?= ($equipo['estado'] ?? '') == 'mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
                                        <option value="baja" <?= ($equipo['estado'] ?? '') == 'baja' ? 'selected' : '' ?>>Baja</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Responsable</label>
                                    <select class="form-select" name="id_responsable">
                                        <option value="">Sin asignar</option>
                                        <?php foreach ($responsables as $res): ?>
                                        <option value="<?= $res['id_user'] ?>" <?= ($equipo['id_responsable'] ?? '') == $res['id_user'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($res['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Solo se muestran usuarios sin equipos asignados</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Último Mantenimiento</label>
                                    <input type="date" class="form-control" name="ultimo_mantenimiento" 
                                           value="<?= htmlspecialchars($equipo['ultimo_mantenimiento'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Próximo Mantenimiento</label>
                                    <input type="date" class="form-control" name="proximo_mantenimiento" 
                                           value="<?= htmlspecialchars($equipo['proximo_mantenimiento'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="crud_equipos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo equipo -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($equipo['nombre']) ?></p>
                            <p><strong>Categoría:</strong> <?= htmlspecialchars($equipo['categoria_nombre'] ?? 'Sin categoría') ?></p>
                            <p><strong>Tipo:</strong> <?= htmlspecialchars($equipo['tipo']) ?></p>
                            <p><strong>Serial/Número:</strong> <?= htmlspecialchars($equipo['serial'] ?? 'N/A') ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge 
                                    <?= $equipo['estado'] == 'disponible' ? 'bg-success' : 
                                       ($equipo['estado'] == 'en_uso' ? 'bg-primary' : 
                                       ($equipo['estado'] == 'mantenimiento' ? 'bg-warning' : 'bg-danger')) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $equipo['estado'])) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha Adquisición:</strong> <?= $equipo['fecha_adquisicion'] ? date('d/m/Y', strtotime($equipo['fecha_adquisicion'])) : 'N/A' ?></p>
                            <p><strong>Responsable:</strong> <?= htmlspecialchars($equipo['responsable_nombre'] ?? 'Sin asignar') ?></p>
                            <p><strong>Último Mantenimiento:</strong> <?= $equipo['ultimo_mantenimiento'] ? date('d/m/Y', strtotime($equipo['ultimo_mantenimiento'])) : 'N/A' ?></p>
                            <p><strong>Próximo Mantenimiento:</strong> <?= $equipo['proximo_mantenimiento'] ? date('d/m/Y', strtotime($equipo['proximo_mantenimiento'])) : 'N/A' ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($equipo['descripcion'] ?? 'Sin descripción')) ?></p>
                    </div>
                    
                    <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#reasignarModal">
                        <i class="fas fa-user-edit"></i> Reasignar Responsable
                    </button>
                    
                    <div class="text-end">
                        <a href="crud_equipos.php" class="btn btn-secondary">Volver</a>
                        <a href="crud_equipos.php?action=edit&id=<?= $equipo['id_equipo'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h5>Historial de Responsables</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Responsable</th>
                                <th>Asignado por</th>
                                <th>Fecha Asignación</th>
                                <th>Fecha Fin</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($historial as $asignacion): ?>
                            <tr>
                                <td><?= htmlspecialchars($asignacion['responsable']) ?></td>
                                <td><?= htmlspecialchars($asignacion['asignador']) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($asignacion['fecha_asignacion'])) ?></td>
                                <td><?= $asignacion['fecha_fin'] ? date('d/m/Y H:i', strtotime($asignacion['fecha_fin'])) : 'Actual' ?></td>
                                <td><?= $asignacion['fecha_fin'] ? 'Finalizada' : 'Activa' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="modal fade" id="reasignarModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Reasignar Responsable</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formReasignar">
                                <input type="hidden" name="id_equipo" value="<?= $equipo['id_equipo'] ?>">
                                <div class="mb-3">
                                    <label class="form-label">Nuevo Responsable</label>
                                    <select class="form-select" name="id_responsable" required>
                                        <option value="">Seleccionar responsable</option>
                                        <?php foreach ($responsables as $res): ?>
                                        <option value="<?= $res['id_user'] ?>">
                                            <?= htmlspecialchars($res['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">Solo se muestran usuarios sin equipos asignados</small>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Motivo de reasignación</label>
                                    <textarea class="form-control" name="motivo" required></textarea>
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="confirmReasignar">Confirmar</button>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <div class="mb-4">
                <a href="crud_equipos.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Equipo
                </a>
                <a href="categorias_equipos.php" class="btn btn-info ms-2">
                    <i class="fas fa-tags"></i> Gestionar Categorías
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="equipos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Tipo</th>
                                <th>Serial</th>
                                <th>Estado</th>
                                <th>Responsable</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($equipos as $eq): ?>
                            <tr>
                                <td><?= htmlspecialchars($eq['id_equipo']) ?></td>
                                <td><?= htmlspecialchars($eq['nombre']) ?></td>
                                <td><?= htmlspecialchars($eq['categoria_nombre'] ?? 'Sin categoría') ?></td>
                                <td><?= htmlspecialchars($eq['tipo']) ?></td>
                                <td><?= htmlspecialchars($eq['serial'] ?? 'N/A') ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $eq['estado'] == 'disponible' ? 'bg-success' : 
                                           ($eq['estado'] == 'en_uso' ? 'bg-primary' : 
                                           ($eq['estado'] == 'mantenimiento' ? 'bg-warning' : 'bg-danger')) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $eq['estado'])) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($eq['responsable_nombre'] ?? 'Sin asignar') ?></td>
                                <td>
                                    <a href="crud_equipos.php?action=view&id=<?= $eq['id_equipo'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="crud_equipos.php?action=edit&id=<?= $eq['id_equipo'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $eq['id_equipo'] ?>">
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
            $('#equipos-table').DataTable({
                responsive: true,
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']],
                dom: '<"top"lf>rt<"bottom"ip>',
                pageLength: 10
            });
        } catch (e) {
            console.error('Error al inicializar DataTables:', e);
            $('#equipos-table').addClass('table').addClass('table-striped');
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
        
        // Eliminar equipo con confirmación nativa
        $(document).on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            const $row = $(this).closest('tr');
            
            // Confirmación nativa
            if (!confirm('¿Estás seguro que deseas eliminar este equipo?\nEsta acción no se puede deshacer.')) {
                return false;
            }
            
            // Mostrar indicador de carga
            $row.css('opacity', '0.5');
            
            $.ajax({
                url: 'crud_equipos.php?action=delete&id=' + id,
                type: 'POST',
                dataType: 'json'
            })
            .done(function(response) {
                if (response && response.success) {
                    // Animación de desvanecimiento
                    $row.addClass('fade-out');
                    setTimeout(() => {
                        $row.remove();
                    }, 400);
                    
                    showMessage('success', response.message);
                } else {
                    showMessage('danger', response?.message || 'Error al eliminar el equipo');
                    $row.css('opacity', '1');
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
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
        
        // Reasignar responsable
        $('#confirmReasignar').click(function() {
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Procesando...');
            
            $.ajax({
                url: 'crud_equipos.php?action=reasignar',
                type: 'POST',
                data: $('#formReasignar').serialize(),
                dataType: 'json'
            })
            .done(function(response) {
                if(response && response.success) {
                    showMessage('success', response.message);
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    showMessage('danger', response?.message || 'Error desconocido');
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
            })
            .always(function() {
                $('#reasignarModal').modal('hide');
                $btn.prop('disabled', false).text('Confirmar');
            });
        });
    });
    </script>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>