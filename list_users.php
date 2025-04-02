<?php
include 'db.php';

// Obtener todos los usuarios de la base de datos
$query = "SELECT * FROM user";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll();
?>
    <?php
    session_start(); // Iniciar sesión aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <link rel="stylesheet" href="css/main.css"> <!-- Incluye el CSS para el estilo -->
    <style>
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
        background-color: #007bff;
        color: #fff; /* Texto blanco para mejor contraste */
        border: none;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        margin-right: 5px;
    }

    button:hover {
        background-color: #0056b3; /* Color más oscuro para hover */
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
    <h2>Lista de Usuarios</h2>
    
    <table>
        <thead>
            <tr>
                <th>Nombres</th>
                <th>Apellido</th>
                <th>Celular</th>
                <th>Correo Electrónico</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['nom_user']); ?></td>
                    <td><?php echo htmlspecialchars($user['apel_user']); ?></td>
                    <td><?php echo htmlspecialchars($user['cel_user']); ?></td>
                    <td><?php echo htmlspecialchars($user['email_user']); ?></td>
                    <td>
                        <?php if ($user['status_user'] == 1): ?>
                            Activo
                        <?php else: ?>
                            Inactivo
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="edit_user.php?id=<?php echo $user['id_user']; ?>">
                            <button>Modificar</button>
                        </a>
                        <a href="change_status_user.php?id=<?php echo $user['id_user']; ?>&status=<?php echo $user['status_user'] == 1 ? 0 : 1; ?>">
                            <button class="status-button <?php echo $user['status_user'] == 1 ? '' : 'inactive'; ?>">
                                <?php echo $user['status_user'] == 1 ? 'Desactivar' : 'Activar'; ?>
                            </button>
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
