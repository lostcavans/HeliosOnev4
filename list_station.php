<?php
session_start(); // Iniciar sesión aquí
?>
<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Eliminar estación si se solicita
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    $sql = "DELETE FROM est WHERE id_est = :id_est";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_est' => $delete_id]);
    $success = "Estación eliminada exitosamente.";
}

// Obtener estaciones de la base de datos
$sql = "SELECT * FROM est";
$stmt = $pdo->query($sql);
$estaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Estaciones Meteorológicas</title>
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
            margin-top: 20px;
        }
        .table-container {
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
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #ecf0f1;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        .button {
            background-color: #3498db;
            color: white;
            padding: 7px 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #2980b9;
        }
        .button-delete {
            background-color: #e74c3c;
        }
        .button-delete:hover {
            background-color: #c0392b;
        }
        .success, .error {
            width: 90%;
            max-width: 1200px;
            margin: 20px auto;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .success {
            background-color: #2ecc71;
            color: white;
        }
        .error {
            background-color: #e74c3c;
            color: white;
        }
    </style>
</head>
<body>
<?php
// index.php
include 'header.php';
include 'sidebar.php';
?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Lista de Estaciones Meteorológicas</h1>

    <?php if (isset($success)): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Descripción</th>
                    <th>Latitud</th>
                    <th>Longitud</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($estaciones) > 0): ?>
                    <?php foreach ($estaciones as $estacion): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($estacion['id_est']); ?></td>
                            <td><?php echo htmlspecialchars($estacion['Descr']); ?></td>
                            <td><?php echo htmlspecialchars($estacion['Latitud']); ?></td>
                            <td><?php echo htmlspecialchars($estacion['Longitud']); ?></td>
                            <td>
                                <a href="modificar_estacion.php?id=<?php echo $estacion['id_est']; ?>" class="button">Modificar</a>
                                <a href="?delete_id=<?php echo $estacion['id_est']; ?>" class="button button-delete" onclick="return confirm('¿Estás seguro de que deseas eliminar esta estación?');">Eliminar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" style="text-align:center;">No hay estaciones registradas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
