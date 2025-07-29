<?php
session_start();
require_once 'db.php';
require_once 'config/paths.php';
require_once 'auth_check.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit;
}

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");

// Directorio para imágenes
$uploadDir = 'uploads/almacen/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// Procesar acciones CRUD
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$feedback = [
    'success' => false,
    'message' => ''
];

// Obtener categorías disponibles
$categorias = $pdo->query("SELECT * FROM categorias_almacen WHERE estado = 'activo' ORDER BY nombre")->fetchAll();

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Procesar imagen
                $foto_item = '';
                if (!empty($_FILES['foto_item']['name'])) {
                    $photo = $_FILES['foto_item'];
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if (!in_array($photo['type'], $allowedTypes)) {
                        throw new Exception("Solo se permiten imágenes JPG o PNG");
                    }
                    
                    if ($photo['size'] > $maxSize) {
                        throw new Exception("La imagen no debe superar 2MB");
                    }
                    
                    $fileExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
                    $fileName = 'item_' . time() . '.' . $fileExt;
                    $filePath = $uploadDir . $fileName;
                    
                    if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
                        throw new Exception("Error al guardar la imagen");
                    }
                    
                    $foto_item = $uploadDir . $fileName;
                }
                
                $stmt = $pdo->prepare("INSERT INTO almacen 
                    (nombre, descripcion, categoria, id_categoria, cantidad, 
                    unidad_medida, minimo_stock, ubicacion, foto_item) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['categoria'],
                    $_POST['id_categoria'] ?: null,
                    $_POST['cantidad'],
                    $_POST['unidad_medida'],
                    $_POST['minimo_stock'] ?: null,
                    $_POST['ubicacion'] ?: null,
                    $foto_item ?: null  // Nuevo campo para la foto
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Item creado exitosamente'
                ];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Obtener datos actuales
                $stmt = $pdo->prepare("SELECT foto_item FROM almacen WHERE id_item = ?");
                $stmt->execute([$id]);
                $currentData = $stmt->fetch();
                
                // Procesar imagen
                $foto_item = $currentData['foto_item'];
                if (!empty($_FILES['foto_item']['name'])) {
                    $photo = $_FILES['foto_item'];
                    $allowedTypes = ['image/jpeg', 'image/png'];
                    $maxSize = 2 * 1024 * 1024; // 2MB
                    
                    if (!in_array($photo['type'], $allowedTypes)) {
                        throw new Exception("Solo se permiten imágenes JPG o PNG");
                    }
                    
                    if ($photo['size'] > $maxSize) {
                        throw new Exception("La imagen no debe superar 2MB");
                    }
                    
                    // Eliminar foto anterior si existe
                    if (!empty($foto_item) && file_exists($foto_item)) {
                        unlink($foto_item);
                    }
                    
                    $fileExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
                    $fileName = 'item_' . time() . '.' . $fileExt;
                    $filePath = $uploadDir . $fileName;
                    
                    if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
                        throw new Exception("Error al guardar la imagen");
                    }
                    
                    $foto_item = $uploadDir . $fileName;
                }
                
                $stmt = $pdo->prepare("UPDATE almacen SET 
                    nombre = ?, descripcion = ?, categoria = ?, id_categoria = ?, 
                    cantidad = ?, unidad_medida = ?, minimo_stock = ?, 
                    ubicacion = ?, foto_item = ?
                    WHERE id_item = ?");
                
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['descripcion'],
                    $_POST['categoria'],
                    $_POST['id_categoria'] ?: null,
                    $_POST['cantidad'],
                    $_POST['unidad_medida'],
                    $_POST['minimo_stock'] ?: null,
                    $_POST['ubicacion'] ?: null,
                    $foto_item ?: null,  // Nuevo campo para la foto
                    $id
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Item actualizado exitosamente'
                ];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Eliminar foto asociada si existe
                $stmt = $pdo->prepare("SELECT foto_item FROM almacen WHERE id_item = ?");
                $stmt->execute([$id]);
                $item = $stmt->fetch();
                
                if ($item && !empty($item['foto_item']) && file_exists($item['foto_item'])) {
                    unlink($item['foto_item']);
                }
                
                $stmt = $pdo->prepare("DELETE FROM almacen WHERE id_item = ?");
                $stmt->execute([$id]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Item eliminado exitosamente'
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
} catch (Exception $e) {
    $feedback = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

// Obtener datos para editar o listar
$item = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT a.*, c.nombre as nombre_categoria 
                          FROM almacen a 
                          LEFT JOIN categorias_almacen c ON a.id_categoria = c.id_categoria
                          WHERE a.id_item = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    
    if (!$item) {
        header('Location: '.BASE_URL.'crud_almacen.php');
        exit;
    }
}

// Listar todos los items si no es una acción específica
if ($action === 'list') {
    $stmt = $pdo->query("SELECT a.*, c.nombre as nombre_categoria 
                        FROM almacen a 
                        LEFT JOIN categorias_almacen c ON a.id_categoria = c.id_categoria
                        ORDER BY a.created_at DESC");
    $items = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Almacén - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/credencial.css">
    <link rel="stylesheet" href="css/main.css">
    
    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .credential-card {
            max-width: 900px;
            margin: 20px auto;
        }
        
        .photo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .photo-preview {
            width: 200px;
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            margin-bottom: 10px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #ccc;
        }
        
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .photo-input {
            display: none;
        }
        
        .photo-placeholder {
            text-align: center;
            padding: 20px;
            cursor: pointer;
        }
        
        .photo-placeholder i {
            font-size: 48px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .full-width {
            grid-column: span 2;
        }
        
        .credential-field {
            margin-bottom: 15px;
        }
        
        .credential-label {
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        
        .credential-input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .form-actions {
            grid-column: span 2;
            text-align: right;
            margin-top: 20px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            border: none;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        
        .table-responsive {
            margin-top: 20px;
        }
        
        .item-photo {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .no-photo {
            width: 60px;
            height: 60px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        
        <div class="credential-container">
            <?php if ($feedback['message']): ?>
            <div class="alert <?= $feedback['success'] ? 'alert-success' : 'alert-danger' ?>">
                <?= $feedback['message'] ?>
            </div>
            <?php endif; ?>
            
            <?php if (in_array($action, ['create', 'edit'])): ?>
            <!-- Formulario para Crear/Editar con estilo de credencial -->
            <div class="credential-card">
                <div class="credential-header">
                    <div class="credential-title">
                        <?= $action === 'create' ? 'NUEVO ITEM' : 'EDITAR ITEM' ?>
                    </div>
                </div>
                
                <form method="POST" enctype="multipart/form-data" class="credential-body">
                    <div class="photo-section">
                        <div class="photo-preview" id="photoPreview">
                            <?php if (!empty($item['foto_item'])): ?>
                                <img src="<?= htmlspecialchars($item['foto_item']) ?>" alt="Foto del item">
                            <?php else: ?>
                                <div class="photo-placeholder">
                                    <i class="fas fa-camera"></i>
                                    <span>Subir foto del item</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="foto_item" name="foto_item" accept="image/*" class="photo-input">
                    </div>
                    
                    <div class="form-grid">
                        <div class="credential-field">
                            <label for="nombre" class="credential-label">Nombre</label>
                            <input type="text" id="nombre" name="nombre" class="credential-input" 
                                   value="<?= htmlspecialchars($item['nombre'] ?? '') ?>" required>
                        </div>
                        
                        <div class="credential-field">
                            <label for="cantidad" class="credential-label">Cantidad</label>
                            <input type="number" id="cantidad" name="cantidad" class="credential-input" 
                                   value="<?= htmlspecialchars($item['cantidad'] ?? '0') ?>" required min="0">
                        </div>
                        
                        <div class="credential-field">
                            <label for="categoria" class="credential-label">Categoría (Texto libre)</label>
                            <input type="text" id="categoria" name="categoria" class="credential-input" 
                                   value="<?= htmlspecialchars($item['categoria'] ?? '') ?>">
                        </div>
                        
                        <div class="credential-field">
                            <label for="id_categoria" class="credential-label">Categoría (Predefinida)</label>
                            <select id="id_categoria" name="id_categoria" class="credential-input">
                                <option value="">Seleccionar categoría</option>
                                <?php foreach ($categorias as $cat): ?>
                                <option value="<?= $cat['id_categoria'] ?>" <?= ($item['id_categoria'] ?? '') == $cat['id_categoria'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="credential-field">
                            <label for="unidad_medida" class="credential-label">Unidad de Medida</label>
                            <input type="text" id="unidad_medida" name="unidad_medida" class="credential-input" 
                                   value="<?= htmlspecialchars($item['unidad_medida'] ?? '') ?>" required>
                        </div>
                        
                        <div class="credential-field">
                            <label for="minimo_stock" class="credential-label">Stock Mínimo</label>
                            <input type="number" id="minimo_stock" name="minimo_stock" class="credential-input" 
                                   value="<?= htmlspecialchars($item['minimo_stock'] ?? '') ?>">
                        </div>
                        
                        <div class="credential-field">
                            <label for="ubicacion" class="credential-label">Ubicación</label>
                            <input type="text" id="ubicacion" name="ubicacion" class="credential-input" 
                                   value="<?= htmlspecialchars($item['ubicacion'] ?? '') ?>">
                        </div>
                        
                        <div class="credential-field full-width">
                            <label for="descripcion" class="credential-label">Descripción</label>
                            <textarea id="descripcion" name="descripcion" class="credential-input" rows="4"><?= 
                                htmlspecialchars($item['descripcion'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="form-actions">
                            <a href="crud_almacen.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo item con estilo de credencial -->
            <div class="credential-card">
                <div class="credential-header">
                    <div class="credential-title">DETALLES DEL ITEM</div>
                </div>
                
                <div class="credential-body">
                    <div class="photo-section">
                        <?php if (!empty($item['foto_item'])): ?>
                            <img src="<?= htmlspecialchars($item['foto_item']) ?>" alt="Foto del item" style="max-width: 300px;">
                        <?php else: ?>
                            <div class="no-photo">
                                <i class="fas fa-box-open" style="font-size: 48px;"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="form-grid">
                        <div class="credential-field">
                            <label class="credential-label">Nombre</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['nombre']) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Cantidad</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['cantidad']) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Categoría</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['categoria']) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Categoría Predefinida</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['nombre_categoria'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Unidad de Medida</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['unidad_medida']) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Stock Mínimo</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['minimo_stock'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Ubicación</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= htmlspecialchars($item['ubicacion'] ?? 'N/A') ?>
                            </div>
                        </div>
                        
                        <div class="credential-field full-width">
                            <label class="credential-label">Descripción</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px; min-height: 100px;">
                                <?= nl2br(htmlspecialchars($item['descripcion'] ?? 'Sin descripción')) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Creado</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="credential-field">
                            <label class="credential-label">Actualizado</label>
                            <div class="credential-input" style="background: #f8f9fa; padding: 10px;">
                                <?= date('d/m/Y H:i', strtotime($item['updated_at'])) ?>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <a href="crud_almacen.php" class="btn btn-secondary">Volver</a>
                            <a href="crud_almacen.php?action=edit&id=<?= $item['id_item'] ?>" class="btn btn-primary">Editar</a>
                        </div>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Listado de items -->
            <div style="margin-bottom: 20px;">
                <a href="crud_almacen.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Item
                </a>
                <a href="categorias_almacen.php" class="btn btn-secondary" style="margin-left: 10px;">
                    <i class="fas fa-tags"></i> Gestionar Categorías
                </a>
            </div>
            
            <div class="table-responsive">
                <table id="almacen-table" class="display" style="width:100%">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Unidad</th>
                            <th>Stock Mín.</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr <?= $item['minimo_stock'] !== null && $item['cantidad'] <= $item['minimo_stock'] ? 'style="background-color: #fff3cd;"' : '' ?>>
                            <td>
                                <?php if (!empty($item['foto_item'])): ?>
                                    <img src="<?= htmlspecialchars($item['foto_item']) ?>" class="item-photo" alt="Foto">
                                <?php else: ?>
                                    <div class="no-photo">
                                        <i class="fas fa-box-open"></i>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($item['nombre']) ?></td>
                            <td><?= htmlspecialchars($item['nombre_categoria'] ?? $item['categoria']) ?></td>
                            <td><?= htmlspecialchars($item['cantidad']) ?></td>
                            <td><?= htmlspecialchars($item['unidad_medida']) ?></td>
                            <td><?= htmlspecialchars($item['minimo_stock'] ?? 'N/A') ?></td>
                            <td>
                                <a href="crud_almacen.php?action=view&id=<?= $item['id_item'] ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="crud_almacen.php?action=edit&id=<?= $item['id_item'] ?>" class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button class="btn btn-sm btn-danger btn-eliminar" data-id="<?= $item['id_item'] ?>">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </section>
    
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
    
    <script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#almacen-table').DataTable({
            responsive: true,
            language: {
                url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
            },
            columnDefs: [
                { orderable: false, targets: [0, 6] } // Deshabilitar orden en foto y acciones
            ]
        });
        
        // Vista previa de foto
        $('#foto_item').change(function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#photoPreview').html(`<img src="${event.target.result}" alt="Vista previa">`);
                }
                reader.readAsDataURL(file); 
            }
        });
        
        // Eliminar con confirmación
        $('.btn-eliminar').click(function() {
            const id = $(this).data('id');
            const $row = $(this).closest('tr');
            
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'crud_almacen.php?action=delete&id=' + id,
                        type: 'POST',
                        dataType: 'json'
                    })
                    .done(function(response) {
                        if (response.success) {
                            $row.fadeOut(400, function() {
                                $(this).remove();
                            });
                            
                            Swal.fire(
                                '¡Eliminado!',
                                response.message,
                                'success'
                            );
                        } else {
                            Swal.fire(
                                'Error',
                                response.message || 'Error al eliminar',
                                'error'
                            );
                        }
                    })
                    .fail(function() {
                        Swal.fire(
                            'Error',
                            'Error al comunicarse con el servidor',
                            'error'
                        );
                    });
                }
            });
        });
    });
    </script>
</body>
</html>