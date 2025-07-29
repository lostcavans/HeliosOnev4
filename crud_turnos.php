<?php
session_start();
require_once 'db.php';
require_once 'config/paths.php';
require_once 'auth_check.php';

if (!isset($_SESSION['id_user'])) {
    header('Location: login.php'); // Redirigir a página de login
    exit;
}

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

$feedback = ['success' => false, 'message' => ''];

try {
    switch ($action) {
        case 'create':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("INSERT INTO turnos (nombre, hora_inicio, hora_fin, descripcion) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['hora_inicio'],
                    $_POST['hora_fin'],
                    $_POST['descripcion'] ?: null
                ]);
                $feedback = ['success' => true, 'message' => 'Turno creado exitosamente'];
            }
            break;

        case 'edit':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("UPDATE turnos SET 
                                       nombre = ?, hora_inicio = ?, hora_fin = ?, descripcion = ?
                                       WHERE id_turno = ?");
                $stmt->execute([
                    $_POST['nombre'],
                    $_POST['hora_inicio'],
                    $_POST['hora_fin'],
                    $_POST['descripcion'] ?: null,
                    $id
                ]);
                $feedback = ['success' => true, 'message' => 'Turno actualizado exitosamente'];
            }
            break;

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM asignacion_turnos WHERE id_turno = ?");
                $stmt->execute([$id]);
                $result = $stmt->fetch();

                if ($result['count'] > 0) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el turno porque está asignado']);
                    exit;
                }

                $stmt = $pdo->prepare("DELETE FROM turnos WHERE id_turno = ?");
                $stmt->execute([$id]);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Turno eliminado exitosamente']);
                exit;
            }
            break;
    }
} catch (PDOException $e) {
    $feedback = ['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()];
}

$turno = null;
if (in_array($action, ['edit', 'view'])) {
    $stmt = $pdo->prepare("SELECT * FROM turnos WHERE id_turno = ?");
    $stmt->execute([$id]);
    $turno = $stmt->fetch();

    if (!$turno) {
        header('Location: crud_turnos.php');
        exit;
    }
}

if ($action === 'list') {
    $stmt = $pdo->query("SELECT * FROM turnos ORDER BY hora_inicio");
    $turnos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Turnos - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Íconos -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    

    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="container-fluid">
            <div class="page-header">
                <h1 class="text-titles">
                    <i class="fas fa-clock"></i> <?= ucfirst($action) ?> Turno
                </h1>
            </div>

            <?php if ($feedback['message']): ?>
            <div class="alert alert-<?= $feedback['success'] ? 'success' : 'danger' ?>">
                <?= $feedback['message'] ?>
            </div>
            <?php endif; ?>

            <?php if (in_array($action, ['create', 'edit'])): ?>
            <!-- Formulario Crear/Editar -->
            <div class="card">
                <div class="card-body">
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="nombre" class="form-label">Nombre del Turno</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" 
                                           value="<?= htmlspecialchars($turno['nombre'] ?? '') ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" 
                                           value="<?= htmlspecialchars($turno['hora_inicio'] ?? '08:00') ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_fin" class="form-label">Hora de Fin</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" 
                                           value="<?= htmlspecialchars($turno['hora_fin'] ?? '16:00') ?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($turno['descripcion'] ?? '') ?></textarea>
                        </div>
                        <div class="text-end">
                            <a href="crud_turnos.php" class="btn btn-secondary">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php elseif ($action === 'view'): ?>
            <!-- Vista detalle -->
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre:</strong> <?= htmlspecialchars($turno['nombre']) ?></p>
                            <p><strong>Hora Inicio:</strong> <?= htmlspecialchars($turno['hora_inicio']) ?></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Hora Fin:</strong> <?= htmlspecialchars($turno['hora_fin']) ?></p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <h5>Descripción</h5>
                        <p><?= nl2br(htmlspecialchars($turno['descripcion'] ?? 'Sin descripción')) ?></p>
                    </div>
                    <div class="text-end">
                        <a href="crud_turnos.php" class="btn btn-secondary">Volver</a>
                        <a href="crud_turnos.php?action=edit&id=<?= $turno['id_turno'] ?>" class="btn btn-warning">Editar</a>
                    </div>
                </div>
            </div>

            <?php else: ?>
            <!-- Lista de turnos -->
            <div class="mb-4">
                <a href="crud_turnos.php?action=create" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Turno
                </a>
                <a href="asignar_turnos.php" class="btn btn-info">
                    <i class="fas fa-calendar-plus"></i> Asignar Turnos
                </a>
            </div>

            <div class="card">
                <div class="card-body">
                    <table id="turnos-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Hora Inicio</th>
                                <th>Hora Fin</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($turnos as $turno): ?>
                            <tr>
                                <td><?= htmlspecialchars($turno['id_turno']) ?></td>
                                <td><?= htmlspecialchars($turno['nombre']) ?></td>
                                <td><?= htmlspecialchars($turno['hora_inicio']) ?></td>
                                <td><?= htmlspecialchars($turno['hora_fin']) ?></td>
                                <td>
                                    <a href="crud_turnos.php?action=view&id=<?= $turno['id_turno'] ?>" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="crud_turnos.php?action=edit&id=<?= $turno['id_turno'] ?>" class="btn btn-warning btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $turno['id_turno'] ?>">
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
                $('#turnos-table').DataTable({
                    responsive: true,
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                    }
                });

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
                            $.post('crud.php?action=delete&id=' + id, function(response) {
                                if(response.success) {
                                    Swal.fire('¡Eliminado!', response.message, 'success').then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire('Error', response.message || 'Ocurrió un error al eliminar', 'error');
                                }
                            }, 'json');
                        }
                    });
                });
            });
        </script>

        <!-- Protección temporal en caso de estilos invisibles -->
        <style>
            .btn i {
                color: inherit;
                font-size: 1rem;
            }
        </style>
    </section>

    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>
