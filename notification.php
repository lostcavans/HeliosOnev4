<?php
session_start(); // Iniciar sesión aquí
// registro_notificacion.php

// Incluir la conexión a la base de datos
require 'db.php';

// Inicializar variables
$success = "";
$error = "";

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_user = $_POST['id_user'];
    $msg = $_POST['msg'];
    $date_create = date('Y-m-d H:i:s'); // Fecha actual
    $date_end = $_POST['date_end'];
    $target = $_POST['target'];
    $status_not = 1; // Asignar siempre el valor 1

    // Validar campos obligatorios
    if (empty($id_user) || empty($msg) || empty($date_end) || empty($target) || empty($status_not)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Insertar notificación en la base de datos
        $sql = "INSERT INTO notification (id_user, msg, date_create, date_end, target, status_not) 
                VALUES (:id_user, :msg, :date_create, :date_end, :target, :status_not)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([
            ':id_user' => $id_user,
            ':msg' => $msg,
            ':date_create' => $date_create,
            ':date_end' => $date_end,
            ':target' => $target,
            ':status_not' => $status_not
        ])) {
            $success = "Notificación registrada exitosamente.";
        } else {
            $error = "Error al registrar la notificación.";
        }
    }
}

// Obtener la lista de cargos desde la base de datos
$stmt = $pdo->prepare("SELECT id_cargo, nom_cargo FROM cargo"); // Asumiendo que la tabla de cargos se llama 'cargos' y tiene los campos 'id_cargo' y 'nombre_cargo'
$stmt->execute();
$cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Notificación</title>
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
    <h1>Registrar Notificación</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form method="POST">
            <input type="text" name="id_user" value="<?php echo $_SESSION['id_user']; ?>" readonly required hidden>
            <textarea name="msg" placeholder="Mensaje de la notificación" required></textarea>
            <input type="date" name="date_end" placeholder="Fecha de cierre" required>
            <select name="target" required>
                <option value="">Seleccione el cargo</option>
                <?php foreach ($cargos as $cargo): ?>
                    <option value="<?php echo $cargo['id_cargo']; ?>"><?php echo htmlspecialchars($cargo['nom_cargo']); ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="button">Registrar Notificación</button>
        </form>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
