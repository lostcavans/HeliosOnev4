<?php
session_start(); // Iniciar sesión aquí
?>
<?php
// registrar_estacion.php

// Incluir la conexión a la base de datos
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $id_est = $_POST['id_est'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];
    $descripcion = $_POST['descripcion'];

    // Validación simple (puedes mejorarla)
    if (empty($id_est) || empty($latitud) || empty($longitud) || empty($descripcion)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Verificar si la latitud y longitud ya existen en la base de datos
        $sql_check = "SELECT COUNT(*) FROM est WHERE Latitud = :latitud AND Longitud = :longitud";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute([
            ':latitud' => $latitud,
            ':longitud' => $longitud
        ]);
        
        $exists = $stmt_check->fetchColumn();

        if ($exists > 0) {
            $error = "Ya existe una estación meteorológica registrada con esta latitud y longitud.";
        } else {
            // Insertar en la base de datos
            $sql = "INSERT INTO est (id_est, Latitud, Longitud, Descr) VALUES (:id_est, :latitud, :longitud, :descripcion)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_est' => $id_est,
                ':latitud' => $latitud,
                ':longitud' => $longitud,
                ':descripcion' => $descripcion
            ]);

            // Redirigir o mostrar mensaje de éxito
            $success = "Estación meteorológica registrada exitosamente.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Estación Meteorológica</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
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
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #34495e;
        }
        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            box-sizing: border-box;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            transition: border 0.3s;
        }
        .form-group input:focus, .form-group textarea:focus {
            border-color: #3498db;
            outline: none;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
        #map {
            height: 250px;
            margin-top: 20px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
        }
        button {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            display: block;
            width: 100%;
        }
        button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<body>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Registrar Estación Meteorológica</h1>

    <?php if (isset($error)): ?>
        <div class="error"><?php echo $error; ?></div>
    <?php endif; ?>

    <?php if (isset($success)): ?>
        <div class="success"><?php echo $success; ?></div>
    <?php endif; ?>

    <div class="form-container">
        <form action="" method="POST" id="estacion-form">
            <div class="form-group">
                <label for="id_est">ID de Estación:</label>
                <input type="text" id="id_est" name="id_est" required>
            </div>
            <div class="form-group">
                <label for="latitud">Latitud:</label>
                <input type="text" id="latitud" name="latitud" required>
            </div>
            <div class="form-group">
                <label for="longitud">Longitud:</label>
                <input type="text" id="longitud" name="longitud" required>
            </div>
            <div class="form-group">
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
            </div>
            <div id="map"></div>
            <button type="submit">Registrar Estación</button>
        </form>
    </div>
</section>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script>
    const map = L.map('map').setView([-17.3939, -66.1577], 6);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    let marker;

    function updateMap(lat, lon) {
        if (marker) {
            map.removeLayer(marker);
        }
        marker = L.marker([lat, lon]).addTo(map);
        map.setView([lat, lon], 13);
    }

    document.getElementById('latitud').addEventListener('input', function() {
        const lat = parseFloat(this.value);
        const lon = parseFloat(document.getElementById('longitud').value);
        if (!isNaN(lat) && !isNaN(lon)) {
            updateMap(lat, lon);
        }
    });

    document.getElementById('longitud').addEventListener('input', function() {
        const lat = parseFloat(document.getElementById('latitud').value);
        const lon = parseFloat(this.value);
        if (!isNaN(lat) && !isNaN(lon)) {
            updateMap(lat, lon);
        }
    });
</script>

</body>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
