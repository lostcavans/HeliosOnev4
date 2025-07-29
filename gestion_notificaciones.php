<?php
session_start();
require 'db.php';

// Variables para mensajes
$success = "";
$error = "";

// Procesar eliminación lógica (cambiar estado a 0)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    
    $stmt = $pdo->prepare("UPDATE notification SET status_not = 0 WHERE id_not = :id");
    if ($stmt->execute([':id' => $delete_id])) {
        $success = "Notificación desactivada correctamente.";
    } else {
        $error = "Error al desactivar la notificación.";
    }
}

// Procesar actualización de notificación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_id'])) {
    $update_id = $_POST['update_id'];
    $msg = $_POST['msg'];
    $date_end = $_POST['date_end'];
    $target = $_POST['target'];
    
    $stmt = $pdo->prepare("UPDATE notification SET msg = :msg, date_end = :date_end, target = :target WHERE id_not = :id");
    
    if ($stmt->execute([
        ':msg' => $msg,
        ':date_end' => $date_end,
        ':target' => $target,
        ':id' => $update_id
    ])) {
        $success = "Notificación actualizada correctamente.";
    } else {
        $error = "Error al actualizar la notificación.";
    }
}

// Obtener todas las notificaciones activas (status_not = 1)
$stmt = $pdo->prepare("SELECT n.*, c.nom_cargo 
                      FROM notification n 
                      JOIN cargo c ON n.target = c.id_cargo 
                      WHERE n.status_not = 1 
                      ORDER BY n.date_create DESC");
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de cargos para el formulario de edición
$stmt = $pdo->prepare("SELECT id_cargo, nom_cargo FROM cargo");
$stmt->execute();
$cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Notificaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 0;
        }
        h1 {
            color: #2c3e50;
            text-align: center;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #3498db;
            color: white;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            color: white;
            font-size: 14px;
        }
        .edit-btn {
            background-color: #f39c12;
        }
        .edit-btn:hover {
            background-color: #e67e22;
        }
        .delete-btn {
            background-color: #e74c3c;
        }
        .delete-btn:hover {
            background-color: #c0392b;
        }
        .success {
            color: green;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 20px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
            max-width: 600px;
            border-radius: 10px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .close:hover {
            color: black;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            resize: vertical;
        }
        input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        .save-btn {
            background-color: #2ecc71;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .save-btn:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>
<?php include 'header.php';?>
<?php include 'sidebar.php';?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <div class="container">
        <h1>Gestión de Notificaciones</h1>

        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (count($notificaciones) > 0): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Mensaje</th>
                        <th>Fecha Creación</th>
                        <th>Fecha Fin</th>
                        <th>Destinatario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $notificacion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notificacion['id_not']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['msg']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['date_create']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['date_end']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['nom_cargo']); ?></td>
                            <td>
                                <button class="button edit-btn" onclick="openEditModal(
                                    '<?php echo $notificacion['id_not']; ?>',
                                    '<?php echo htmlspecialchars($notificacion['msg'], ENT_QUOTES); ?>',
                                    '<?php echo $notificacion['date_end']; ?>',
                                    '<?php echo $notificacion['target']; ?>'
                                )">Editar</button>
                                <a href="gestion_notificaciones.php?delete_id=<?php echo $notificacion['id_not']; ?>" class="button delete-btn" onclick="return confirm('¿Está seguro que desea desactivar esta notificación?')">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay notificaciones activas.</p>
        <?php endif; ?>
    </div>

    <!-- Modal para edición -->
    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeEditModal()">&times;</span>
            <h2>Editar Notificación</h2>
            <form id="editForm" method="POST">
                <input type="hidden" name="update_id" id="update_id">
                <textarea name="msg" id="edit_msg" placeholder="Mensaje" required></textarea>
                <input type="date" name="date_end" id="edit_date_end" required>
                <select name="target" id="edit_target" required>
                    <option value="">Seleccione el cargo</option>
                    <?php foreach ($cargos as $cargo): ?>
                        <option value="<?php echo $cargo['id_cargo']; ?>"><?php echo htmlspecialchars($cargo['nom_cargo']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="save-btn">Guardar Cambios</button>
            </form>
        </div>
    </div>

    <script>
        // Función para abrir el modal de edición
        function openEditModal(id, msg, date_end, target) {
            document.getElementById('update_id').value = id;
            document.getElementById('edit_msg').value = msg;
            document.getElementById('edit_date_end').value = date_end;
            document.getElementById('edit_target').value = target;
            document.getElementById('editModal').style.display = 'block';
        }

        // Función para cerrar el modal de edición
        function closeEditModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera del contenido
        window.onclick = function(event) {
            const modal = document.getElementById('editModal');
            if (event.target == modal) {
                closeEditModal();
            }
        }
    </script>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>