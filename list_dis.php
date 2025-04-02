<?php
include 'db.php';

// Obtener todos los registros de la nueva tabla
$query = "SELECT * FROM reg_dis"; // Cambia 'nombre_de_tu_tabla' por el nombre real de la nueva tabla
$stmt = $pdo->query($query);
$records = $stmt->fetchAll();
?>
<?php
session_start(); // Iniciar sesión aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Registros</title>
    <link rel="stylesheet" href="css/list_dis.css"> <!-- Incluye el CSS para el estilo -->

    <style>
        /* Estilo para el modal de confirmación */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            width: 300px;
        }

        .modal-button {
            padding: 10px 20px;
            border: none;
            background-color: #a31900;
            color: white;
            cursor: pointer;
            border-radius: 5px;
            margin: 5px;
        }

        .modal-button:hover {
            background-color: #ff5722;
        }

        .modal-button.cancel {
            background-color: #dc3545;
        }

        .modal-button.cancel:hover {
            background-color: #c82333;
        }



       
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
    }

    table, th, td {
        border: 1px solid #ddd;
    }

    th, td {
        padding: 10px;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
    }

    button {
        background-color: #a31900;
        color: #fff; /* Texto blanco para mejor contraste */
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-right: 5px;
    }

    button:hover {
        background-color: #ff5722; /* Color más oscuro para hover */
    }

    .status-button {
        background-color: #28a745; /* Verde para activo */
        color: #fff; /* Texto blanco para mejor contraste */
    }

    .status-button.inactive {
        background-color: #dc3545; /* Rojo para inactivo */
        color: #fff; /* Texto blanco para mejor contraste */
    }

    .status-button:hover {
        opacity: 0.8; /* Ligera opacidad en hover */
    }
</style>


</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Lista de Registros</h2>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Link GPS</th>
                <th>Link Sensor</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($records as $record): ?>
                <tr>
                    <td><?php echo htmlspecialchars($record['id_dis']); ?></td>
                    <td><?php echo htmlspecialchars($record['link_Gps']); ?></td>
                    <td><?php echo htmlspecialchars($record['link_sen']); ?></td>
                    <td>
                        <a href="edit_record.php?id=<?php echo $record['id_dis']; ?>">
                            <button>Modificar</button>
                        </a>
                        <!-- Eliminar botón ahora usa JavaScript -->
                        <button onclick="openModal(<?php echo $record['id_dis']; ?>)">Eliminar</button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<!-- Modal de confirmación -->
<div id="confirmModal" class="modal">
    <div class="modal-content">
        <h3>¿Estás seguro?</h3>
        <p>¡Esta acción eliminará permanentemente el registro!</p>
        <button class="modal-button" onclick="confirmDelete()">Sí, eliminar</button>
        <button class="modal-button cancel" onclick="closeModal()">Cancelar</button>
    </div>
</div>

<script>
    // Variables globales
    let recordIdToDelete = null;

    // Función para abrir el modal de confirmación
    function openModal(id) {
        recordIdToDelete = id;  // Guardamos el ID del registro a eliminar
        document.getElementById('confirmModal').style.display = 'flex'; // Mostramos el modal
    }

    // Función para cerrar el modal
    function closeModal() {
        document.getElementById('confirmModal').style.display = 'none'; // Ocultamos el modal
    }

    // Función para confirmar la eliminación
    function confirmDelete() {
        if (recordIdToDelete !== null) {
            // Redirigir al script de eliminación
            window.location.href = "delete_record.php?id=" + recordIdToDelete;
        }
    }
</script>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
