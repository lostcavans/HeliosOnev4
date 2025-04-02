<?php
session_start(); // Iniciar sesión aquí
?>
<?php
// index.php
include 'header.php';
include 'sidebar.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map of User Locations</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        /* Map container */
        #map {
            margin-left: 0px; /* Espacio para el sidebar */
            height: calc(100vh - 120px); /* Ajustar según la altura del navbar y el footer */
            flex: 0;
        }
    </style>
</head>
<body>  
<?php include 'header.php';?>
<?php include 'sidebar.php';?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <div id="map"></div>
    
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map;
        let markers = {};

        // Función para obtener los datos de las últimas ubicaciones de los usuarios
        async function fetchStations() {
            const response = await fetch('get_dataMap.php');
            const data = await response.json();
            
            // Verificar si la respuesta fue exitosa
            if (data.status === 'success') {
                return data.locations; // Cambia a la clave adecuada según el JSON de get_dataMap.php
            } else {
                console.error('Error fetching stations:', data.message);
                return [];
            }
        }

        // Crear o actualizar un marcador en el mapa para cada usuario
        function createOrUpdateMarker(user) {
            const { id_user, latitude, longitude, speed, altitude, satelites, timestamp } = user;

            const popupContent = `
    <div style="font-family: Arial, sans-serif; font-size: 14px; padding: 8px; width: 250px;">
        <h3 style="margin: 0; color: #007bff;">🧑 ${user.nom_user} ${user.apel_user}</h3>
        <hr style="margin: 8px 0; border: 0.5px solid #ddd;">
        <p><b>📍 Latitud:</b> <span style="color: #28a745;">${latitude}</span></p>
        <p><b>📍 Longitud:</b> <span style="color: #28a745;">${longitude}</span></p>
        <p><b>🚀 Velocidad:</b> <span style="color: #ff5733;">${speed ?? 'N/A'} km/h</span></p>
        <p><b>⛰️ Altitud:</b> <span style="color: #17a2b8;">${altitude ?? 'N/A'} m</span></p>
        <p><b>🛰️ Satélites:</b> <span style="color: #ffc107;">${satelites ?? 'N/A'}</span></p>
        <hr style="margin: 8px 0; border: 0.5px solid #ddd;">
        <p><b>⏳ Última Actualización:</b> <span style="color: #6c757d;">${timestamp}</span></p>
    </div>
`;


            if (markers[id_user]) {
                markers[id_user].setPopupContent(popupContent);
            } else {
                const customIcon = L.icon({
                    iconUrl: 'assets/img/emblema.png',
                    iconSize: [32, 32],
                    iconAnchor: [16, 32],
                    popupAnchor: [0, -32]
                });

                const marker = L.marker([latitude, longitude], { icon: customIcon })
                    .addTo(map)
                    .bindPopup(popupContent);

                marker.on('mouseover', function() {
                    this.openPopup();
                });

                marker.on('mouseout', function() {
                    this.closePopup();
                });

                marker.on('click', function() {
                    alert(`Detalle del usuario ${id_user}`); // Cambia según la acción deseada
                });

                markers[id_user] = marker;
            }
        }

        // Inicializar el mapa y obtener datos de ubicaciones periódicamente
        async function initializeMap() {
            const users = await fetchStations();

            if (!map) {
                map = L.map('map').setView([-16.7268881, -64.8831763], 6);

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
            }

            users.forEach(user => {
                createOrUpdateMarker(user);
            });
        }

        // Llamar a la función para inicializar el mapa y actualizar cada 30 segundos
        initializeMap();
        setInterval(initializeMap, 30000);
    </script>
</body>
</section>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
