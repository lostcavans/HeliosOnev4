<?php
// equipos/categorias_equipos.php
session_start();
require_once 'db.php';
require_once 'config/paths.php';

// Procesar acciones CRUD
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Mensajes de feedback
$feedback = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO categorias_equipos (nombre, descripcion, estado) VALUES (?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['estado']
                ]);
                
                $feedback = ['success' => true, 'message' => 'Categoría creada exitosamente'];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("UPDATE categorias_equipos SET nombre=?, descripcion=?, estado=? WHERE id_categoria=?");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['estado'],
                    $id
                ]);
                $feedback = ['success' => true, 'message' => 'Categoría actualizada exitosamente'];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verificar si hay equipos asociados
                $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM equipos WHERE id_categoria = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
                
                if ($result['total'] > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false, 
                        'message' => 'No se puede eliminar: Hay equipos asociados a esta categoría'
                    ]);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM categorias_equipos WHERE id_categoria = ?");
                $stmt->execute([$id]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true, 
                    'message' => 'Categoría eliminada correctamente',
                    'deleted_id' => $id
                ]);
                exit;
            }
            break;
    }
} catch (PDOException $e) {
    $feedback = ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
} catch (Exception $e) {
    $feedback = ['success' => false, 'message' => $e->getMessage()];
}

// Obtener datos para editar/ver
$categoria = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT * FROM categorias_equipos WHERE id_categoria = ?");
    $stmt->execute([$id]);
    $categoria = $stmt->fetch();
    
    if (!$categoria) {
        header('Location: categorias_equipos.php');
        exit;
    }
}

// Listar todas las categorías si no es una acción específica
if ($action === 'list') {
    $categorias = $pdo->query("SELECT * FROM categorias_equipos ORDER BY nombre")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categorías de Equipos - Sistema Bomberos</title>
    
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
                    <i class="fas fa-tags"></i> <?= ucfirst($action) ?> Categoría de Equipos
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
                                           value="<?= htmlspecialchars($categoria['nombre'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Descripción</label>
                                    <textarea class="form-control" name="descripcion" rows="3"><?= 
                                        htmlspecialchars($categoria['descripcion'] ?? '') ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Estado</label>
                                    <select class="form-select" name="estado" required>
                                        <option value="activo" <?= ($categoria['estado'] ?? '') == 'activo' ? 'selected' : '' ?>>Activo</option>
                                        <option value="inactivo" <?= ($categoria['estado'] ?? '') == 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="categorias_equipos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de una sola categoría -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($categoria['nombre']) ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge <?= $categoria['estado'] == 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= ucfirst($categoria['estado']) ?>
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($categoria['created_at'])) ?></p>
                            <p><strong>Actualizado:</strong> <?= date('d/m/Y H:i', strtotime($categoria['updated_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($categoria['descripcion'] ?? 'Sin descripción')) ?></p>
                    </div>
                    
                    <div class="text-end">
                        <a href="categorias_equipos.php" class="btn btn-secondary">Volver</a>
                        <a href="categorias_equipos.php?action=edit&id=<?= $categoria['id_categoria'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <div class="mb-4">
                <a href="categorias_equipos.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="categorias-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th>Creado</th>
                                <th>Actualizado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?= htmlspecialchars($cat['id_categoria']) ?></td>
                                <td><?= htmlspecialchars($cat['nombre']) ?></td>
                                <td>
                                    <span class="badge <?= $cat['estado'] == 'activo' ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= ucfirst($cat['estado']) ?>
                                    </span>
                                </td>
                                <td><?= date('d/m/Y H:i', strtotime($cat['created_at'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($cat['updated_at'])) ?></td>
                                <td>
                                    <a href="categorias_equipos.php?action=view&id=<?= $cat['id_categoria'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="categorias_equipos.php?action=edit&id=<?= $cat['id_categoria'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $cat['id_categoria'] ?>">
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
            $('#categorias-table').DataTable({
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
            $('#categorias-table').addClass('table').addClass('table-striped');
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
        
        // Eliminar categoría con confirmación nativa
        $(document).on('click', '.btn-eliminar', function() {
            const id = $(this).data('id');
            const $row = $(this).closest('tr');
            
            // Confirmación nativa
            if (!confirm('¿Estás seguro que deseas eliminar esta categoría?\nEsta acción no se puede deshacer.')) {
                return false;
            }
            
            // Mostrar indicador de carga
            $row.css('opacity', '0.5');
            
            $.ajax({
                url: 'categorias_equipos.php?action=delete&id=' + id,
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
                    showMessage('danger', response?.message || 'Error al eliminar la categoría');
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
    });
    </script>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>