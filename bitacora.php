<?php
session_start();
require_once 'db.php';

include 'header.php';
include 'sidebar.php';

// Obtener registros de bitácora
$bitacora = $pdo->query("SELECT b.*, CONCAT(u.nom_user, ' ', u.apel_user) as usuario 
                         FROM bitacora b
                         JOIN user u ON b.id_user = u.id_user
                         ORDER BY b.fecha_accion DESC")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Bitácora</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
</head>

<body>
    
    <section class="full-box dashboard-contentPage">
         <?php include 'navbar.php'; ?>
    <div class="container-fluid">
         
        <h2>Registro de Bitácora</h2>
        <table id="bitacora-table" class="table table-striped">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Tabla</th>
                    <th>ID Registro</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bitacora as $registro): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($registro['fecha_accion'])) ?></td>
                    <td><?= htmlspecialchars($registro['usuario']) ?></td>
                    <td><?= htmlspecialchars($registro['accion']) ?></td>
                    <td><?= htmlspecialchars($registro['tabla_afectada']) ?></td>
                    <td><?= $registro['id_registro_afectado'] ?></td>
                    <td><?= htmlspecialchars($registro['detalles']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#bitacora-table').DataTable({
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.11.5/i18n/es-ES.json'
                },
                order: [[0, 'desc']]
            });
        });
    </script>
    </section>
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>
