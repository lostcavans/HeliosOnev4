<?php
session_start(); // Iniciar sesión aquí
require 'db.php'; // Incluir la conexión a la base de datos

// Manejar la acción de eliminación lógica
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_notificacion = $_GET['eliminar'];
    $stmt = $pdo->prepare("UPDATE notification SET status_not = 0 WHERE id_not = :id");
    $stmt->execute([':id' => $id_notificacion]);
}

// Obtener todas las notificaciones que no estén eliminadas (status_not = 1) junto con el correo del usuario
$stmt = $pdo->prepare("SELECT n.id_not, n.msg, n.date_create, n.date_end, n.target, n.status_not, u.email 
                        FROM notification n 
                        JOIN user u ON n.id_user = u.id_user 
                        WHERE n.status_not = 1 
                        ORDER BY n.date_create DESC");
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Notificaciones</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f3f5;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
        }
        h1 {
            margin-top: 20px;
            text-align: center;
            color: #2c3e50;
        }
        .table-container {
            width: 90%;
            max-width: 1000px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 15px;
            box-shadow: 0 6px 30px rgba(0, 0, 0, 0.1);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            overflow: hidden;
            border-radius: 15px;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 15px;
            text-align: left;
            font-size: 16px;
            color: #34495e;
        }
        th {
            background-color: #3498db;
            color: white;
            font-size: 18px;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        .button {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            text-align: center;
            display: inline-block;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .delete-button {
            background-color: #e74c3c;
        }
        .delete-button:hover {
            background-color: #c0392b;
        }
        .empty-message {
            text-align: center;
            font-size: 18px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
<?php include 'header.php';?>
<?php include 'sidebar.php';?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Lista de Notificaciones</h1>

    <div class="table-container">
        <?php if ($notificaciones): ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario (Email)</th>
                        <th>Mensaje</th>
                        <th>Fecha de creación</th>
                        <th>Fecha de cierre</th>
                        <th>Cargo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($notificaciones as $notificacion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($notificacion['id_not']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['email']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['msg']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['date_create']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['date_end']); ?></td>
                            <td><?php echo htmlspecialchars($notificacion['target']); ?></td>
                            <td>
                                <a href="editar_notificacion.php?id=<?php echo $notificacion['id_not']; ?>" class="button">Editar</a>
                                <a href="?eliminar=<?php echo $notificacion['id_not']; ?>" class="button delete-button">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No hay notificaciones para mostrar.</p>
        <?php endif; ?>
    </div>
</section>


</body>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>