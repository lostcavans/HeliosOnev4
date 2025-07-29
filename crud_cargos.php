<?php
session_start();
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
                $stmt = $pdo->prepare("INSERT INTO cargo (nom_cargo, stat_cargo, descripcion) 
                                      VALUES (?, ?, ?)");
                $stmt->execute([
                    $_POST['nom_cargo'],
                    $_POST['stat_cargo'] ?? 0,
                    $_POST['descripcion'] ?: null
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Cargo creado exitosamente'
                ];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("UPDATE cargo SET 
                                      nom_cargo = ?, stat_cargo = ?, descripcion = ?
                                      WHERE id_cargo = ?");
                $stmt->execute([
                    $_POST['nom_cargo'],
                    $_POST['stat_cargo'] ?? 0,
                    $_POST['descripcion'] ?: null,
                    $id
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Cargo actualizado exitosamente'
                ];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Verificar si el cargo está en uso
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user WHERE id_cargo = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'No se puede eliminar el cargo porque está asignado a usuarios'
                    ]);
                    exit;
                }
                
                $stmt = $pdo->prepare("DELETE FROM cargo WHERE id_cargo = ?");
                $stmt->execute([$id]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Cargo eliminado exitosamente'
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
$cargo = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT * FROM cargo WHERE id_cargo = ?");
    $stmt->execute([$id]);
    $cargo = $stmt->fetch();
    
    if (!$cargo) {
        header('Location: crud_cargos.php');
        exit;
    }
}

// Listar todos los cargos si no es una acción específica
if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM cargo ORDER BY id_cargo");
    $cargos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Cargos - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* Estilos adicionales para los botones */
        .btn-action {
            margin: 2px;
            min-width: 30px;
            text-align: center;
        }
        .table-actions {
            white-space: nowrap;
        }
        
        /* Estilo para el modal personalizado */
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
        }
        
        .modal-content {
            background-color: #fefefe;
            margin: 15% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 500px;
            border-radius: 5px;
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
        }
        
        .modal-buttons {
            text-align: right;
            margin-top: 20px;
        }
        
        .modal-button {
            padding: 8px 16px;
            margin-left: 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .confirm-btn {
            background-color: #3085d6;
            color: white;
        }
        
        .cancel-btn {
            background-color: #d33;
            color: white;
        }
    </style>
</head>
<body>  
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-id-card"></i> <?= ucfirst($action) ?> Cargo
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
                                    <label for="nom_cargo" class="form-label">Nombre del Cargo</label>
                                    <input type="text" class="form-control" id="nom_cargo" name="nom_cargo" 
                                           value="<?= htmlspecialchars($cargo['nom_cargo'] ?? '') ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="stat_cargo" class="form-label">Estado</label>
                                    <select class="form-select" id="stat_cargo" name="stat_cargo" required>
                                        <option value="1" <?= isset($cargo['stat_cargo']) && $cargo['stat_cargo'] ? 'selected' : '' ?>>Activo</option>
                                        <option value="0" <?= isset($cargo['stat_cargo']) && !$cargo['stat_cargo'] ? 'selected' : '' ?>>Inactivo</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="descripcion" class="form-label">Descripción</label>
                                    <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= 
                                        htmlspecialchars($cargo['descripcion'] ?? '') ?></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-end">
                            <a href="crud_cargos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo cargo -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($cargo['nom_cargo']) ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge <?= $cargo['stat_cargo'] ? 'bg-success' : 'bg-secondary' ?>">
                                    <?= $cargo['stat_cargo'] ? 'Activo' : 'Inactivo' ?>
                                </span>
                            </p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($cargo['descripcion'] ?? 'Sin descripción')) ?></p>
                    </div>
                    
                    <div class="text-end">
                        <a href="crud_cargos.php" class="btn btn-secondary">Volver</a>
                        <a href="crud_cargos.php?action=edit&id=<?= $cargo['id_cargo'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Listado de cargos -->
            <div class="mb-4">
                <a href="crud_cargos.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Cargo
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="cargos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Estado</th>
                                <th class="table-actions">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cargos as $cargo): ?>
                            <tr>
                                <td><?= htmlspecialchars($cargo['id_cargo']) ?></td>
                                <td><?= htmlspecialchars($cargo['nom_cargo']) ?></td>
                                <td>
                                    <span class="badge <?= $cargo['stat_cargo'] ? 'bg-success' : 'bg-secondary' ?>">
                                        <?= $cargo['stat_cargo'] ? 'Activo' : 'Inactivo' ?>
                                    </span>
                                </td>
                                <td class="table-actions">
                                    <a href="crud_cargos.php?action=view&id=<?= $cargo['id_cargo'] ?>" 
                                       class="btn btn-info btn-sm btn-action" title="Ver">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="crud_cargos.php?action=edit&id=<?= $cargo['id_cargo'] ?>" 
                                       class="btn btn-warning btn-sm btn-action" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-action btn-eliminar" 
                                            data-id="<?= $cargo['id_cargo'] ?>" title="Eliminar">
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
        
        <!-- Modal personalizado para confirmación -->
        <div id="confirmModal" class="custom-modal">
            <div class="modal-content">
                <h3 id="modalTitle">¿Estás seguro?</h3>
                <p id="modalMessage">¡No podrás revertir esta acción!</p>
                <div class="modal-buttons">
                    <button id="modalCancel" class="modal-button cancel-btn">Cancelar</button>
                    <button id="modalConfirm" class="modal-button confirm-btn">Confirmar</button>
                </div>
            </div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="js/scripts.js"></script>
        <script>
            $(document).ready(function() {
                // Variables para el modal
                let currentIdToDelete = null;
                const modal = document.getElementById('confirmModal');
                const modalConfirm = document.getElementById('modalConfirm');
                const modalCancel = document.getElementById('modalCancel');
                
                // Mostrar modal personalizado
                function showConfirmModal(title, message, confirmCallback) {
                    document.getElementById('modalTitle').textContent = title;
                    document.getElementById('modalMessage').textContent = message;
                    modal.style.display = 'block';
                    
                    // Configurar eventos de los botones del modal
                    modalConfirm.onclick = function() {
                        modal.style.display = 'none';
                        confirmCallback();
                    };
                    
                    modalCancel.onclick = function() {
                        modal.style.display = 'none';
                    };
                    
                    // Cerrar al hacer clic fuera del modal
                    window.onclick = function(event) {
                        if (event.target == modal) {
                            modal.style.display = 'none';
                        }
                    };
                }
                
                // Eliminar cargo
                $(document).on('click', '.btn-eliminar', function() {
                    currentIdToDelete = $(this).data('id');
                    
                    // Opción 1: Usar el modal personalizado
                    showConfirmModal(
                        '¿Estás seguro?', 
                        '¡No podrás revertir esta acción!', 
                        function() {
                            deleteCargo(currentIdToDelete);
                        }
                    );
                    
                    // Opción 2: Usar confirm() nativo de JavaScript (más simple)
                    /*
                    if (confirm('¿Estás seguro que deseas eliminar este cargo?\n¡No podrás revertir esta acción!')) {
                        deleteCargo($(this).data('id'));
                    }
                    */
                });
                
                // Función para eliminar el cargo
                function deleteCargo(id) {
                    $.post('crud_cargos.php?action=delete&id=' + id, function(response) {
                        if(response.success) {
                            alert('¡Cargo eliminado exitosamente!');
                            location.reload();
                        } else {
                            alert('Error: ' + (response.message || 'Ocurrió un error al eliminar'));
                        }
                    }, 'json').fail(function() {
                        alert('Error en la solicitud al servidor');
                    });
                }
            });
        </script>
    </section>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>