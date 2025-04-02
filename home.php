<?php
session_start();
require 'db.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    die("Acceso no autorizado.");
}

// Configuración de la paginación
$usuarios_por_pagina = 6; // Número de usuarios por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Página actual
$offset = ($pagina_actual - 1) * $usuarios_por_pagina; // Cálculo del offset

// Obtener el total de usuarios (excluyendo los desactivados)
$stmt_total = $pdo->prepare("SELECT COUNT(*) as total FROM user WHERE status_user != 0");
$stmt_total->execute();
$total_usuarios = $stmt_total->fetch(PDO::FETCH_ASSOC)['total'];

// Calcular el número total de páginas
$total_paginas = ceil($total_usuarios / $usuarios_por_pagina);

// Obtener los usuarios para la página actual (excluyendo los desactivados)
$stmt = $pdo->prepare("SELECT u.id_user, u.nom_user, u.apel_user, u.email_user, u.cel_user 
                       FROM user u
                       WHERE u.status_user != 0
                       ORDER BY u.nom_user
                       LIMIT :limit OFFSET :offset");
$stmt->execute([
    ':limit' => $usuarios_por_pagina,
    ':offset' => $offset
]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <!-- Integrar Leaflet.js para los mapas -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Declarar customIcon una sola vez
        const customIcon = L.icon({
            iconUrl: 'assets/img/emblema.png', // URL del ícono
            iconSize: [40, 40], // Tamaño del ícono
            iconAnchor: [0, 0], // Punto de anclaje del ícono
            popupAnchor: [1, -34] // Punto de anclaje del popup
        });
    </script>
    <style>
        /* Estilos específicos para el contenedor de home.php */
        .home-container {
            font-family: 'Arial', sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 0;
        }
        .home-container h1 {
            text-align: center;
            color: #2c3e50;
            margin-top: 20px;
        }
        .home-container .container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px; /* Espacio entre las cartas */
            padding: 20px;
        }
        .home-container .card {
            background: linear-gradient(135deg, #f9f9f9, #e0e0e0);
            border-radius: 15px;
            box-shadow: 5px 5px 15px rgba(0, 0, 0, 0.3);
            padding: 20px;
            width: 400px; /* Ancho más grande */
            height: 600px; /* Altura más grande */
            transition: transform 0.2s ease-in-out;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            border: 5px solid #333;
            position: relative;
        }
        .home-container .card:hover {
            transform: scale(1.05);
        }
        .home-container .card h3 {
            margin: 15px 0;
            color: #222;
            font-size: 28px; /* Tamaño de fuente más grande */
            font-weight: bold;
            text-transform: uppercase;
        }
        .home-container .card p {
            margin: 8px 0;
            color: #333;
            font-size: 18px; /* Tamaño de fuente más grande */
        }
        .home-container .card img {
            width: 150px; /* Imagen más grande */
            height: 150px; /* Imagen más grande */
            border-radius: 50%;
            border: 4px solid #fff;
            margin-top: 15px;
            background-color: #fff;
        }
        .home-container .card .footer {
            position: absolute;
            bottom: 15px;
            width: 90%;
            font-size: 18px; /* Tamaño de fuente más grande */
            font-weight: bold;
            color: #000;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px;
            border-radius: 5px;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .home-container .card .type {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #ffcc00;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 16px; /* Tamaño de fuente más grande */
            font-weight: bold;
            color: #000;
            box-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .home-container .card .map {
            width: 100%;
            height: 250px; /* Altura del mapa más grande */
            margin-bottom: 15px;
            border-radius: 10px; /* Bordes redondeados */
            overflow: hidden; /* Evita que el mapa se desborde */
        }
        /* Estilos para la paginación */
        .pagination {
            display: flex;
            justify-content: center;
            margin-top: 20px;
            gap: 10px;
        }
        .pagination a, .pagination span {
            padding: 8px 12px;
            background-color: #2c3e50;
            color: #fff;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }
        .pagination a:hover {
            background-color: #1a252f;
        }
        .pagination .current {
            background-color: #ffcc00;
            color: #000;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>

    <!-- Contenedor específico para home.php -->
    <div class="home-container">
        <h1>Usuarios</h1>

        <div class="container">
            <?php if (!empty($usuarios)): ?>
                <?php foreach ($usuarios as $usuario): ?>
                    <?php
                    // Obtener los datos de GPS del usuario
                    $stmt_gps = $pdo->prepare("SELECT latitude, longitude FROM gps_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_gps->execute([':id_user' => $usuario['id_user']]);
                    $gps_data = $stmt_gps->fetch(PDO::FETCH_ASSOC);

                    // Obtener los datos de salud del usuario
                    $stmt_bpm = $pdo->prepare("SELECT bpm, SPo2 FROM bpm_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_bpm->execute([':id_user' => $usuario['id_user']]);
                    $bpm_data = $stmt_bpm->fetch(PDO::FETCH_ASSOC);

                    $stmt_gas = $pdo->prepare("SELECT ppm FROM gas_data WHERE id_user = :id_user ORDER BY timestamp DESC LIMIT 1");
                    $stmt_gas->execute([':id_user' => $usuario['id_user']]);
                    $gas_data = $stmt_gas->fetch(PDO::FETCH_ASSOC);
                    ?>
                    <div class="card">
    <!-- Mapa -->
    <div id="map-<?php echo $usuario['id_user']; ?>" class="map"></div>
    <h3><?php echo htmlspecialchars($usuario['nom_user'] . ' ' . $usuario['apel_user']); ?></h3>
    <!-- Datos de salud -->
    <p><strong>BPM:</strong> <span id="bpm-<?php echo $usuario['id_user']; ?>"><?php echo htmlspecialchars($bpm_data['bpm']); ?></span></p>
    <p><strong>SPO2:</strong> <span id="spo2-<?php echo $usuario['id_user']; ?>"><?php echo htmlspecialchars($bpm_data['SPo2']); ?>%</span></p>
    <p><strong>PPM:</strong> <span id="ppm-<?php echo $usuario['id_user']; ?>"><?php echo htmlspecialchars($gas_data['ppm']); ?></span></p>
    <div class="footer">ID: <?php echo htmlspecialchars($usuario['id_user']); ?></div>
</div>
                    <!-- Script para inicializar el mapa -->
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            <?php if ($gps_data): ?>
                                var map = L.map('map-<?php echo $usuario['id_user']; ?>', {
                                    center: [<?php echo $gps_data['latitude']; ?>, <?php echo $gps_data['longitude']; ?>],
                                    zoom: 13
                                });

                                // Añadir capa de tiles de OpenStreetMap
                                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                                }).addTo(map);

                                // Añadir marcador con el ícono personalizado
                                L.marker([<?php echo $gps_data['latitude']; ?>, <?php echo $gps_data['longitude']; ?>], { icon: customIcon })
                                    .addTo(map)
                                    .bindPopup('<?php echo htmlspecialchars($usuario['nom_user'] . ' ' . $usuario['apel_user']); ?>');
                            <?php else: ?>
                                document.getElementById('map-<?php echo $usuario['id_user']; ?>').innerHTML = '<p>Ubicación no disponible</p>';
                            <?php endif; ?>
                        });
                    </script>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay usuarios para mostrar.</p>
            <?php endif; ?>
        </div>

        <!-- Paginación -->
        <div class="pagination">
            <?php if ($pagina_actual > 1): ?>
                <a href="?pagina=<?php echo $pagina_actual - 1; ?>">Anterior</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                <?php if ($i == $pagina_actual): ?>
                    <span class="current"><?php echo $i; ?></span>
                <?php else: ?>
                    <a href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endif; ?>
            <?php endfor; ?>

            <?php if ($pagina_actual < $total_paginas): ?>
                <a href="?pagina=<?php echo $pagina_actual + 1; ?>">Siguiente</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
<script>
    function updateUserData(id_user) {
        fetch(`get_updated_data.php?id_user=${id_user}`)
            .then(response => response.json())
            .then(data => {
                // Actualizar los datos de salud
                document.getElementById(`bpm-${id_user}`).textContent = data.bpm_data.bpm;
                document.getElementById(`spo2-${id_user}`).textContent = `${data.bpm_data.SPo2}%`;
                document.getElementById(`ppm-${id_user}`).textContent = data.gas_data.ppm;

                // Actualizar el mapa si es necesario
                if (data.gps_data) {
                    var map = L.map(`map-${id_user}`, {
                        center: [data.gps_data.latitude, data.gps_data.longitude],
                        zoom: 13
                    });

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                    }).addTo(map);

                    L.marker([data.gps_data.latitude, data.gps_data.longitude], { icon: customIcon })
                        .addTo(map)
                        .bindPopup(`<?php echo htmlspecialchars($usuario['nom_user'] . ' ' . $usuario['apel_user']); ?>`);
                } else {
                    document.getElementById(`map-${id_user}`).innerHTML = '<p>Ubicación no disponible</p>';
                }
            })
            .catch(error => console.error('Error al actualizar los datos:', error));
    }

    // Actualizar los datos cada 10 segundos
    document.addEventListener('DOMContentLoaded', function() {
        <?php foreach ($usuarios as $usuario): ?>
            setInterval(() => updateUserData(<?php echo $usuario['id_user']; ?>), 10000);
        <?php endforeach; ?>
    });
</script>
</body>
</html>