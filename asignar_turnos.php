<?php
session_start();
require_once 'db.php';
include 'header.php';
include 'sidebar.php';

$feedback = null;

// Procesar asignación de turnos
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['id'])) {
    try {
        // Validación básica
        if (empty($_POST['id_user']) || empty($_POST['id_turno']) || empty($_POST['fecha'])) {
            throw new Exception("Todos los campos requeridos deben estar completos");
        }

        // Insertar directamente sin bitácora
        $stmt = $pdo->prepare("INSERT INTO asignacion_turnos 
                              (id_turno, id_user, fecha, id_mision, observaciones) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['id_turno'],
            $_POST['id_user'],
            $_POST['fecha'],
            $_POST['id_mision'] ?: null,
            $_POST['observaciones'] ?: null
        ]);

        $feedback = ['success' => true, 'message' => 'Turno asignado exitosamente'];
    } catch (PDOException $e) {
        $feedback = ['success' => false, 'message' => 'Error al asignar turno: ' . $e->getMessage()];
    } catch (Exception $e) {
        $feedback = ['success' => false, 'message' => $e->getMessage()];
    }
}

// Obtener turnos disponibles
$turnos = $pdo->query("SELECT * FROM turnos ORDER BY hora_inicio")->fetchAll();

// Obtener usuarios SIN asignación para la fecha de hoy
$usuarios = $pdo->query("
    SELECT u.id_user, CONCAT(u.nom_user, ' ', u.apel_user) AS nombre_completo
    FROM user u
    WHERE u.id_user NOT IN (
        SELECT id_user FROM asignacion_turnos WHERE fecha = CURDATE() AND estado = 1
    )
    ORDER BY u.nom_user
")->fetchAll();

// Obtener misiones activas
$misiones = $pdo->query("SELECT id_mis, nom_mis FROM mision WHERE stat_mis = 1 ORDER BY fec_mis DESC")->fetchAll();

// Obtener asignaciones
$asignaciones = $pdo->query("
    SELECT at.*, t.nombre AS turno_nombre, t.hora_inicio, t.hora_fin,
           CONCAT(u.nom_user, ' ', u.apel_user) AS usuario_nombre,
           m.nom_mis AS mision_nombre
    FROM asignacion_turnos at
    JOIN turnos t ON at.id_turno = t.id_turno
    JOIN user u ON at.id_user = u.id_user
    LEFT JOIN mision m ON at.id_mision = m.id_mis
    WHERE at.estado = 1
    ORDER BY at.fecha DESC, t.hora_inicio
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Turnos - Sistema Bomberos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <div class="container-fluid">
        <div class="page-header">
            <h1 class="text-titles">
                <i class="fas fa-calendar-plus"></i> Asignar Turnos
                <small class="text-muted">Programación de personal</small>
            </h1>
        </div>

        <?php if ($feedback): ?>
        <div class="alert alert-<?= $feedback['success'] ? 'success' : 'danger' ?>">
            <?= $feedback['message'] ?>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="id_user" class="form-label">Bombero</label>
                                <select class="form-select" id="id_user" name="id_user" required>
                                    <option value="">Seleccionar bombero</option>
                                    <?php foreach ($usuarios as $usuario): ?>
                                    <option value="<?= $usuario['id_user'] ?>">
                                        <?= htmlspecialchars($usuario['nombre_completo']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="id_turno" class="form-label">Turno</label>
                                <select class="form-select" id="id_turno" name="id_turno" required>
                                    <option value="">Seleccionar turno</option>
                                    <?php foreach ($turnos as $turno): ?>
                                    <option value="<?= $turno['id_turno'] ?>">
                                        <?= htmlspecialchars($turno['nombre']) ?> (<?= $turno['hora_inicio'] ?> - <?= $turno['hora_fin'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="id_mision" class="form-label">Misión (opcional)</label>
                                <select class="form-select" id="id_mision" name="id_mision">
                                    <option value="">Sin misión específica</option>
                                    <?php foreach ($misiones as $mision): ?>
                                    <option value="<?= $mision['id_mis'] ?>">
                                        <?= htmlspecialchars($mision['nom_mis']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="observaciones" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones" name="observaciones" rows="2"></textarea>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Asignar Turno</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header">
                <h4 class="card-title">Asignaciones Programadas</h4>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Bombero</th>
                            <th>Turno</th>
                            <th>Horario</th>
                            <th>Misión</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asignaciones as $asignacion): ?>
                        <tr>
                            <td><?= date('d/m/Y', strtotime($asignacion['fecha'])) ?></td>
                            <td><?= htmlspecialchars($asignacion['usuario_nombre']) ?></td>
                            <td><?= htmlspecialchars($asignacion['turno_nombre']) ?></td>
                            <td><?= $asignacion['hora_inicio'] ?> - <?= $asignacion['hora_fin'] ?></td>
                            <td><?= $asignacion['mision_nombre'] ? htmlspecialchars($asignacion['mision_nombre']) : 'N/A' ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="<?= $asignacion['id_asignacion'] ?>">
                                    <i class="fas fa-trash-alt"></i> Eliminar
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
        // Manejar eliminación de asignaciones
        $('.btn-eliminar').click(function(e) {
            e.preventDefault();
            const button = $(this);
            const id = button.data('id');
            
            if (!confirm('¿Estás seguro de eliminar esta asignación?')) {
                return;
            }

            button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'eliminar_asignacion.php',
                method: 'POST',
                data: { id: id },
                dataType: 'json'
            })
            .done(function(response) {
                if (response.success) {
                    button.closest('tr').fadeOut();
                } else {
                    alert('Error: ' + response.message);
                }
            })
            .fail(function() {
                alert('Error de conexión');
            })
            .always(function() {
                button.prop('disabled', false).html('<i class="fas fa-trash-alt"></i>');
            });
        });
    });
    </script>
</section>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>