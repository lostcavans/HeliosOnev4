<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Obtener el id_user desde el parámetro
$id_user = $_GET['id_user'] ?? '';

// Obtener los detalles del usuario
$sql = "SELECT * FROM user WHERE id_user = :id_user";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':id_user', $id_user);
$stmt->execute();
$user = $stmt->fetch();

if (!$user) {
    die("Usuario no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f9;
        }
        h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        button {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <h1>Detalles del Usuario</h1>
    <table>
        <tr>
            <th>Nombres</th>
            <td><?php echo htmlspecialchars($user['nombres']); ?></td>
        </tr>
        <tr>
            <th>Apellido Materno</th>
            <td><?php echo htmlspecialchars($user['apel_mat']); ?></td>
        </tr>
        <tr>
            <th>Apellido Paterno</th>
            <td><?php echo htmlspecialchars($user['apel_pat']); ?></td>
        </tr>
        <tr>
            <th>Celular</th>
            <td><?php echo htmlspecialchars($user['cel']); ?></td>
        </tr>
        <tr>
            <th>Fecha de Nacimiento</th>
            <td><?php echo htmlspecialchars($user['fec_nac']); ?></td>
        </tr>
        <tr>
            <th>CI</th>
            <td><?php echo htmlspecialchars($user['CI']); ?></td>
        </tr>
        <tr>
            <th>Ubicación</th>
            <td><?php echo htmlspecialchars($user['ubi']); ?></td>
        </tr>
        <tr>
            <th>País</th>
            <td><?php echo htmlspecialchars($user['pais']); ?></td>
        </tr>
        <tr>
            <th>Ciudad</th>
            <td><?php echo htmlspecialchars($user['ciud']); ?></td>
        </tr>
        <tr>
            <th>Zona</th>
            <td><?php echo htmlspecialchars($user['zona']); ?></td>
        </tr>
        <tr>
            <th>Complemento</th>
            <td><?php echo htmlspecialchars($user['comp']); ?></td>
        </tr>
        <tr>
            <th>Cargo</th>
            <td><?php echo htmlspecialchars($user['id_cargo']); ?></td>
        </tr>
        <tr>
            <th>Email</th>
            <td><?php echo htmlspecialchars($user['email']); ?></td>
        </tr>
        <tr>
            <th>Institución</th>
            <td><?php echo htmlspecialchars($user['inst']); ?></td>
        </tr>
        <tr>
            <th>Estado</th>
            <td><?php echo htmlspecialchars($user['stat']); ?></td>
        </tr>
    </table>
    <a href="report_user.php"><button>Volver</button></a>
</body>
</html>
