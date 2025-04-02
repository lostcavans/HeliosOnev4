<?php
session_start(); // Iniciar sesión aquí
require 'db.php';

// Inicializar variables
$success = "";
$error = "";
$estacion = null;

// Verificar si se ha enviado un ID para modificar
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Obtener los datos de la estación
    $sql = "SELECT * FROM est WHERE id_est = :id_est";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id_est' => $id]);
    $estacion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra la estación, redirigir o mostrar un error
    if (!$estacion) {
        $error = "Estación no encontrada.";
    }
}

// Procesar el formulario de modificación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = $_POST['descripcion'];
    $latitud = $_POST['latitud'];
    $longitud = $_POST['longitud'];

    // Actualizar los datos de la estación
    $sql = "UPDATE est SET Descr = :descr, Latitud = :lat, Longitud = :lon WHERE id_est = :id_est";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':descr' => $descripcion,
        ':lat' => $latitud,
        ':lon' => $longitud,
        ':id_est' => $id
    ]);
    
    // Redirigir a list_station.php después de la actualización
    header("Location: list_station.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Estación Meteorológica</title>
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
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        input[type="text"], input[type="number"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
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
        #map {
            width: 100%;
            height: 400px; /* Altura del mapa */
            margin-top: 20px; /* Espaciado superior */
        }
    </style>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <script>
        function initMap() {
            // Latitud y longitud de la estación
            var latitud = <?php echo json_encode($estacion['Latitud']); ?>;
            var longitud = <?php echo json_encode($estacion['Longitud']); ?>;

            // Crear el mapa
            var map = L.map('map').setView([latitud, longitud], 10); // Nivel de zoom

            // Añadir una capa de tiles
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            // Crear un marcador
            var marker = L.marker([latitud, longitud]).addTo(map)
                .bindPopup('Estación Meteorológica') // Título del marcador
                .openPopup();
        }

        // Inicializar el mapa cuando se cargue la página
        window.onload = initMap;
    </script>
</head>
<body>
<?php
// index.php
include 'header.php';
include 'sidebar.php';
?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h1>Modificar Estación Meteorológica</h1>

    <?php if ($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="form-container">
        <?php if ($estacion): ?>
            <form method="POST">
                <input type="text" name="descripcion" placeholder="Descripción" value="<?php echo htmlspecialchars($estacion['Descr']); ?>" required>
                <input type="number" name="latitud" placeholder="Latitud" step="any" value="<?php echo htmlspecialchars($estacion['Latitud']); ?>" required>
                <input type="number" name="longitud" placeholder="Longitud" step="any" value="<?php echo htmlspecialchars($estacion['Longitud']); ?>" required>
                <button type="submit" class="button">Actualizar Estación</button>
            </form>
            <div id="map"></div> <!-- Contenedor para el mapa -->
        <?php else: ?>
            <p>No se encontró la estación para modificar.</p>
        <?php endif; ?>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
