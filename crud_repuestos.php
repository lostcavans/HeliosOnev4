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
?>
<?php

require_once 'db.php';
include 'header.php';
include 'sidebar.php';


// Procesar acciones CRUD
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Mensajes de feedback
$feedback = [
    'success' => false,
    'message' => ''
];

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO repuestos (nombre, descripcion, modelo_compatible, cantidad, ubicacion) 
                                      VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['modelo_compatible'],
                    $_POST['cantidad'],
                    $_POST['ubicacion'] ?: null
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Repuesto creado exitosamente'
                ];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("UPDATE repuestos SET 
                                      nombre = ?, descripcion = ?, modelo_compatible = ?, 
                                      cantidad = ?, ubicacion = ?
                                      WHERE id_repuesto = ?");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['modelo_compatible'],
                    $_POST['cantidad'],
                    $_POST['ubicacion'] ?: null,
                    $id
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Repuesto actualizado exitosamente'
                ];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("DELETE FROM repuestos WHERE id_repuesto = ?");
                $stmt->execute([$id]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Repuesto eliminado exitosamente'
                ]);
                exit;
            }
            break;
    }
} catch (PDOException $e) {
    $feedback = [
        'success' => false,
        'message' => 'Error en la base de datos: ' . $e->getMessage()
    ];
}

// Obtener datos para editar o listar
$repuesto = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT * FROM repuestos WHERE id_repuesto = ?");
    $stmt->execute([$id]);
    $repuesto = $stmt->fetch();
    
    if (!$repuesto) {
        header('Location: crud_repuestos.php');
        exit;
    }
}

// Listar todos los repuestos si no es una acción específica
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM repuestos ORDER BY created_at DESC");
    $repuestos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Repuestos - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>  
    
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-cogs"></i> <?= ucfirst($action) ?> Repuesto
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
                                    <label for="nombre" class="form-label">Nombre</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($repuesto['nombre'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= 
                                        htmlspecialchars($repuesto['descripcion'] ?? '') ?></textarea>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="modelo_compatible" class="form-label">Modelo Compatible</label>
                                    <input type="text" class="form-control" id="modelo_compatible" name="modelo_compatible" 
                                           value="<?= htmlspecialchars($repuesto['modelo_compatible'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="cantidad" class="form-label">Cantidad</label>
                                    <input type="number" class="form-control" id="cantidad" name="cantidad" 
                                           value="<?= htmlspecialchars($repuesto['cantidad'] ?? '0') ?>" required min="0">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" class="form-control" id="ubicacion" name="ubicacion" 
                                           value="<?= htmlspecialchars($repuesto['ubicacion'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="crud_repuestos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo repuesto -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($repuesto['nombre']) ?></p>
                            <p><strong>Modelo Compatible:</strong> <?= htmlspecialchars($repuesto['modelo_compatible']) ?></p>
                            <p><strong>Cantidad:</strong> <?= htmlspecialchars($repuesto['cantidad']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Ubicación:</strong> <?= htmlspecialchars($repuesto['ubicacion'] ?? 'N/A') ?></p>
                            <p><strong>Creado:</strong> <?= date('d/m/Y H:i', strtotime($repuesto['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($repuesto['descripcion'] ?? 'Sin descripción')) ?></p>
                    </div>
                    
                    <div class="text-end">
                        <a href="crud_repuestos.php" class="btn btn-secondary">Volver</a>
                        <a href="crud_repuestos.php?action=edit&id=<?= $repuesto['id_repuesto'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Listado de repuestos -->
            <div class="mb-4">
                <a href="crud_repuestos.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Repuesto
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="repuestos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Modelo</th>
                                <th>Cantidad</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($repuestos as $repuesto): ?>
                            <tr>
                                <td><?= htmlspecialchars($repuesto['id_repuesto']) ?></td>
                                <td><?= htmlspecialchars($repuesto['nombre']) ?></td>
                                <td><?= htmlspecialchars($repuesto['modelo_compatible']) ?></td>
                                <td><?= htmlspecialchars($repuesto['cantidad']) ?></td>
                                <td>
                                    <a href="crud_repuestos.php?action=view&id=<?= $repuesto['id_repuesto'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="crud_repuestos.php?action=edit&id=<?= $repuesto['id_repuesto'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $repuesto['id_repuesto'] ?>">
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
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script src="js/scripts.js"></script>
        <script>
            $(document).ready(function() {
                $('#repuestos-table').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                    }
                });
                
                // Eliminar repuesto
                $('.btn-eliminar').click(function() {
                    const id = $(this).data('id');
                    
                    Swal.fire({
                        title: '¿Estás seguro?',
                        text: "¡No podrás revertir esta acción!",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, eliminar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.post('crud_repuestos.php?action=delete&id=' + id, function(response) {
                                if(response.success) {
                                    Swal.fire(
                                        '¡Eliminado!',
                                        response.message,
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        response.message || 'Ocurrió un error al eliminar',
                                        'error'
                                    );
                                }
                            }, 'json');
                        }
                    });
                });
            });
        </script>
    </section>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>