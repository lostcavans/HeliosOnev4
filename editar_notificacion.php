<?php
session_start(); // Iniciar sesión

// Incluir la conexión a la base de datos
require 'db.php';

// Verificar si se recibió la ID de la notificación para editar
if (!isset($_GET['id'])) {
    die("ID de notificación no proporcionado.");
}

$notification_id = $_GET['id'];
$success = "";
$error = "";

// Obtener los datos de la notificación a editar
$stmt = $pdo->prepare("SELECT * FROM notification WHERE id_not = :id");
$stmt->execute([':id' => $notification_id]);
$notification = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$notification) {
    die("Notificación no encontrada.");
}

// Procesar el formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $msg = $_POST['msg'];
    $date_end = $_POST['date_end'];
    $target = $_POST['target'];
    $status_not = $_POST['status_not'];

    // Validar campos obligatorios
    if (empty($msg) || empty($date_end) || empty($target) || empty($status_not)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Actualizar la notificación en la base de datos
        $sql = "UPDATE notification SET msg = :msg, date_end = :date_end, target = :target, status_not = :status_not WHERE id_not = :id";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([
            ':msg' => $msg,
            ':date_end' => $date_end,
            ':target' => $target,
            ':status_not' => $status_not,
            ':id' => $notification_id
        ])) {
            $success = "Notificación actualizada exitosamente.";
            
            // Redirigir a la lista de notificaciones después de la actualización
            header("Location: list_notification.php");
            exit; // Asegúrate de salir después de la redirección
        } else {
            $error = "Error al actualizar la notificación.";
        }
    }
}

// Obtener la lista de cargos desde la base de datos
$stmt = $pdo->prepare("SELECT id_cargo, nom_cargo FROM cargo");
$stmt->execute();
$cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Notificación</title>
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
        .form-container {
            width: 80%;
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="date"], select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        textarea {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            resize: vertical;
        }
        .button {
            background-color: #3498db;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            width: 100%;
            display: block;
            text-align: center;
        }
        .button:hover {
            background-color: #2980b9;
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
        .form-container select {
            background-color: #fff;
            cursor: pointer;
        }
    </style>
</head>
<body>
<?php include 'header.php';?>
<?php include 'sidebar.php';?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Editar Notificación</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <textarea name="msg" placeholder="Mensaje de la notificación" required><?php echo htmlspecialchars($notification['msg']); ?></textarea>
            <input type="date" name="date_end" value="<?php echo htmlspecialchars($notification['date_end']); ?>" required>
            <select name="target" required>
                <option value="">Seleccione el cargo</option>
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?php echo $cargo['id_cargo']; ?>" <?php echo $cargo['id_cargo'] == $notification['target'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cargo['nom_cargo']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="status_not" required>
                <option value="1" <?php echo $notification['status_not'] == 1 ? 'selected' : ''; ?>>Activo</option>
                <option value="0" <?php echo $notification['status_not'] == 0 ? 'selected' : ''; ?>>Desactivado</option>
            </select>
            <button type="submit" class="button">Actualizar Notificación</button>
        </form>
    </div>
</section>


</body>
</html>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>