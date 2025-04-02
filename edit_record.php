<?php
session_start(); // Iniciar sesión aquí
?>
<?php
include 'db.php';

// Obtener el ID del registro que se desea modificar
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los datos del registro desde la base de datos
    $query = "SELECT * FROM reg_dis WHERE id_dis = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch();
    
    if (!$record) {
        // Si no se encuentra el registro, redirigir a la lista de registros
        header('Location: list_dis.php');
        exit;
    }
}

// Verificar si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $linkGps = $_POST['link_Gps'];
    $linkSen = $_POST['link_sen'];

    // Actualizar el registro en la base de datos
    $query = "UPDATE reg_dis SET link_Gps = :linkGps, link_sen = :linkSen WHERE id_dis = :id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':linkGps', $linkGps);
    $stmt->bindParam(':linkSen', $linkSen);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    
    if ($stmt->execute()) {
        // Redirigir a la lista de registros después de modificar
        header('Location: list_dis.php');
        exit;
    } else {
        echo "Error al actualizar el registro.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Registro</title>
    <link rel="stylesheet" href="css/edit_dis.css"> <!-- Agrega el CSS necesario -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }

        .full-box {
            padding: 20px;
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            display: flex;
            flex-direction: column;
            width: 50%;
            margin: 0 auto;
        }

        label {
            font-size: 16px;
            margin-bottom: 5px;
            color: #555;
        }

        input[type="text"] {
            padding: 10px;
            font-size: 14px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            transition: all 0.3s ease;
        }

        input[type="text"]:focus {
            border-color: #007bff;
        }

        button {
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 200px;
            margin-top: 20px;
        }

        button:hover {
            background-color: #218838;
        }

        .btn-back {
            background-color: #007bff;
            margin-top: 10px;
        }

        .btn-back:hover {
            background-color: #0056b3;
        }

        .center {
            display: flex;
            justify-content: center;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Modificar Registro</h2>

    <form action="edit_record.php?id=<?php echo $record['id_dis']; ?>" method="POST" class="center">
        <div class="form-group">
            <label for="link_Gps">Link GPS:</label>
            <input type="text" name="link_Gps" id="link_Gps" value="<?php echo htmlspecialchars($record['link_Gps']); ?>" required>
        </div>
        <div class="form-group">
            <label for="link_sen">Link Sensor:</label>
            <input type="text" name="link_sen" id="link_sen" value="<?php echo htmlspecialchars($record['link_sen']); ?>" required>
        </div>
        <div class="center">
            <button type="submit">Guardar Cambios</button>
        </div>
    </form>

    <div class="center">
        <a href="list_dis.php">
            <button class="btn-back">Volver a la lista</button>
        </a>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
