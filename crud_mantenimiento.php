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

// Obtener equipos y técnicos para selects
$equipos = $pdo->query("SELECT id_equipo, nombre FROM equipos ORDER BY nombre")->fetchAll();
$tecnicos = $pdo->query("SELECT id_user, nom_user, apel_user FROM user WHERE id_cargo IN (SELECT id_cargo FROM cargo WHERE nom_cargo LIKE '%Técnico%')")->fetchAll();

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO mantenimientos 
                                      (id_equipo, tipo, descripcion, fecha_programada, fecha_realizacion, estado, id_tecnico, observaciones, costo, repuestos_utilizados) 
                                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['id_equipo'],
                    $_POST['tipo'],
                    $_POST['descripcion'],
                    $_POST['fecha_programada'],
                    $_POST['fecha_realizacion'] ?: null,
                    $_POST['estado'] ?? 'pendiente',
                    $_POST['id_tecnico'],
                    $_POST['observaciones'] ?: null,
                    $_POST['costo'] ?: null,
                    $_POST['repuestos_utilizados'] ?: null
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Mantenimiento programado exitosamente'
                ];
            }
            break;
            
        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("UPDATE mantenimientos SET 
                                      id_equipo = ?, tipo = ?, descripcion = ?, fecha_programada = ?, 
                                      fecha_realizacion = ?, estado = ?, id_tecnico = ?, observaciones = ?, 
                                      costo = ?, repuestos_utilizados = ?
                                      WHERE id_mantenimiento = ?");
                $stmt->execute([
                    $_POST['id_equipo'],
                    $_POST['tipo'],
                    $_POST['descripcion'],
                    $_POST['fecha_programada'],
                    $_POST['fecha_realizacion'] ?: null,
                    $_POST['estado'],
                    $_POST['id_tecnico'],
                    $_POST['observaciones'] ?: null,
                    $_POST['costo'] ?: null,
                    $_POST['repuestos_utilizados'] ?: null,
                    $id
                ]);
                
                $feedback = [
                    'success' => true,
                    'message' => 'Mantenimiento actualizado exitosamente'
                ];
            }
            break;
            
        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("DELETE FROM mantenimientos WHERE id_mantenimiento = ?");
                $stmt->execute([$id]);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => true,
                    'message' => 'Mantenimiento eliminado exitosamente'
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
$mantenimiento = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT m.*, e.nombre as equipo_nombre, 
                          CONCAT(u.nom_user, ' ', u.apel_user) as tecnico_nombre
                          FROM mantenimientos m
                          JOIN equipos e ON m.id_equipo = e.id_equipo
                          JOIN user u ON m.id_tecnico = u.id_user
                          WHERE m.id_mantenimiento = ?");
    $stmt->execute([$id]);
    $mantenimiento = $stmt->fetch();
    
    if (!$mantenimiento) {
        header('Location: crud_mantenimiento.php');
        exit;
    }
}

// Listar todos los mantenimientos si no es una acción específica
if ($action === 'list') {
    $stmt = $pdo->query("SELECT m.*, e.nombre as equipo_nombre 
                         FROM mantenimientos m
                         JOIN equipos e ON m.id_equipo = e.id_equipo
                         ORDER BY m.fecha_programada DESC");
    $mantenimientos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Mantenimiento - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>  
    
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-tools"></i> <?= ucfirst($action) ?> Mantenimiento
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
                                    <label for="id_equipo" class="form-label">Equipo</label>
                                    <select class="form-select" id="id_equipo" name="id_equipo" required>
                                        <option value="">Seleccionar equipo</option>
                                        <?php foreach ($equipos as $equipo): ?>
                                        <option value="<?= $equipo['id_equipo'] ?>" <?= 
                                            (isset($mantenimiento['id_equipo']) && $mantenimiento['id_equipo'] == $equipo['id_equipo']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($equipo['nombre']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="tipo" class="form-label">Tipo de Mantenimiento</label>
                                    <select class="form-select" id="tipo" name="tipo" required>
                                        <option value="preventivo" <?= 
                                            (isset($mantenimiento['tipo']) && $mantenimiento['tipo'] == 'preventivo') ? 'selected' : '' ?>>Preventivo</option>
                                        <option value="correctivo" <?= 
                                            (isset($mantenimiento['tipo']) && $mantenimiento['tipo'] == 'correctivo') ? 'selected' : '' ?>>Correctivo</option>
                                        <option value="predictivo" <?= 
                                            (isset($mantenimiento['tipo']) && $mantenimiento['tipo'] == 'predictivo') ? 'selected' : '' ?>>Predictivo</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="fecha_programada" class="form-label">Fecha Programada</label>
                                    <input type="datetime-local" class="form-control" id="fecha_programada" name="fecha_programada" 
                                           value="<?= isset($mantenimiento['fecha_programada']) ? 
                                               date('Y-m-d\TH:i', strtotime($mantenimiento['fecha_programada'])) : '' ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="fecha_realizacion" class="form-label">Fecha de Realización</label>
                                    <input type="datetime-local" class="form-control" id="fecha_realizacion" name="fecha_realizacion" 
                                           value="<?= isset($mantenimiento['fecha_realizacion']) && $mantenimiento['fecha_realizacion'] ? 
                                               date('Y-m-d\TH:i', strtotime($mantenimiento['fecha_realizacion'])) : '' ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="id_tecnico" class="form-label">Técnico Responsable</label>
                                    <select class="form-select" id="id_tecnico" name="id_tecnico" required>
                                        <option value="">Seleccionar técnico</option>
                                        <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?= $tecnico['id_user'] ?>" <?= 
                                            (isset($mantenimiento['id_tecnico']) && $mantenimiento['id_tecnico'] == $tecnico['id_user']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tecnico['nom_user'] . ' ' . $tecnico['apel_user']) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="estado" class="form-label">Estado</label>
                                    <select class="form-select" id="estado" name="estado" required>
                                        <option value="pendiente" <?= 
                                            (isset($mantenimiento['estado']) && $mantenimiento['estado'] == 'pendiente' ? 'selected' : '' )?>>Pendiente</option>
                                        <option value="en_proceso" <?= 
                                            (isset($mantenimiento['estado']) && $mantenimiento['estado'] == 'en_proceso' ? 'selected' : '') ?>>En Proceso</option>
                                        <option value="completado" <?= 
                                            (isset($mantenimiento['estado']) && $mantenimiento['estado'] == 'completado' ? 'selected' : '') ?>>Completado</option>
                                        <option value="cancelado" <?= 
                                            (isset($mantenimiento['estado']) && $mantenimiento['estado'] == 'cancelado' ? 'selected' : '' )?>>Cancelado</option>
                                    </select>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="costo" class="form-label">Costo (Bs.)</label>
                                    <input type="number" step="0.01" class="form-control" id="costo" name="costo" 
                                           value="<?= htmlspecialchars($mantenimiento['costo'] ?? '') ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="repuestos_utilizados" class="form-label">Repuestos Utilizados</label>
                                    <input type="text" class="form-control" id="repuestos_utilizados" name="repuestos_utilizados" 
                                           value="<?= htmlspecialchars($mantenimiento['repuestos_utilizados'] ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= 
                                htmlspecialchars($mantenimiento['descripcion'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="observaciones" class="form-label">Observaciones</label>
                            <textarea class="form-control" id="observaciones" name="observaciones" rows="3"><?= 
                                htmlspecialchars($mantenimiento['observaciones'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="text-end">
                            <a href="crud_mantenimiento.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <?php elseif ($action === 'view'): ?>
            <!-- Vista de un solo mantenimiento -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Equipo:</strong> <?= htmlspecialchars($mantenimiento['equipo_nombre']) ?></p>
                            <p><strong>Tipo:</strong> <?= ucfirst(htmlspecialchars($mantenimiento['tipo'])) ?></p>
                            <p><strong>Estado:</strong> 
                                <span class="badge 
                                    <?= $mantenimiento['estado'] == 'completado' ? 'bg-success' : 
                                       ($mantenimiento['estado'] == 'en_proceso' ? 'bg-warning' : 
                                       ($mantenimiento['estado'] == 'cancelado' ? 'bg-danger' : 'bg-secondary')) ?>">
                                    <?= ucfirst(str_replace('_', ' ', $mantenimiento['estado'])) ?>
                                </span>
                            </p>
                            <p><strong>Técnico:</strong> <?= htmlspecialchars($mantenimiento['tecnico_nombre']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Programado:</strong> <?= date('d/m/Y H:i', strtotime($mantenimiento['fecha_programada'])) ?></p>
                            <p><strong>Realizado:</strong> <?= 
                                $mantenimiento['fecha_realizacion'] ? date('d/m/Y H:i', strtotime($mantenimiento['fecha_realizacion'])) : 'N/A' ?></p>
                            <p><strong>Costo:</strong> <?= 
                                $mantenimiento['costo'] ? 'Bs. ' . number_format($mantenimiento['costo'], 2) : 'N/A' ?></p>
                            <p><strong>Repuestos:</strong> <?= 
                                htmlspecialchars($mantenimiento['repuestos_utilizados'] ?? 'N/A') ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($mantenimiento['descripcion'])) ?></p>
                    </div>
                    
                    <div class="mb-3">
                        <h5>Observaciones</h5>
                        <p><?= nl2br(htmlspecialchars($mantenimiento['observaciones'] ?? 'Sin observaciones')) ?></p>
                    </div>
                    
                    <div class="text-end">
                        <a href="crud_mantenimiento.php" class="btn btn-secondary">Volver</a>
                        <a href="crud_mantenimiento.php?action=edit&id=<?= $mantenimiento['id_mantenimiento'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>
            
            <?php else: ?>
            <!-- Listado de mantenimientos -->
            <div class="mb-4">
                <a href="crud_mantenimiento.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Mantenimiento
                </a>
                <a href="calendar_mantenimiento.php" class="btn btn-info">
                    <i class="fas fa-calendar-alt"></i> Ver Calendario
                </a>
            </div>
            
            <div class="card">
                <div class="card-body">
                    <table id="mantenimientos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Equipo</th>
                                <th>Tipo</th>
                                <th>Programado</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mantenimientos as $m): ?>
                            <tr>
                                <td><?= htmlspecialchars($m['id_mantenimiento']) ?></td>
                                <td><?= htmlspecialchars($m['equipo_nombre']) ?></td>
                                <td><?= ucfirst(htmlspecialchars($m['tipo'])) ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($m['fecha_programada'])) ?></td>
                                <td>
                                    <span class="badge 
                                        <?= $m['estado'] == 'completado' ? 'bg-success' : 
                                           ($m['estado'] == 'en_proceso' ? 'bg-warning' : 
                                           ($m['estado'] == 'cancelado' ? 'bg-danger' : 'bg-secondary')) ?>">
                                        <?= ucfirst(str_replace('_', ' ', $m['estado'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="crud_mantenimiento.php?action=view&id=<?= $m['id_mantenimiento'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="crud_mantenimiento.php?action=edit&id=<?= $m['id_mantenimiento'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $m['id_mantenimiento'] ?>">
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
                $('#mantenimientos-table').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                    },
                    order: [[3, 'desc']]
                });
                
                // Eliminar mantenimiento
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
                            $.post('crud_mantenimiento.php?action=delete&id=' + id, function(response) {
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