<?php  
session_start(); // Iniciar sesión aquí
?>
<?php
// index.php
include 'header.php';
include 'sidebar.php';
?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
<?php include 'navbar.php'; ?>
<!-- Código principal aquí -->
    <h2>Max / Min / Promedio</h2>

    <!-- Formulario para seleccionar la fecha y la estación -->
    <form action="" method="POST" class="data-form">
        <label for="station">Selecciona la estación:</label>
        <select name="station" id="station" required>
            <?php
                // Conexión a la base de datos para obtener las estaciones
                include 'db.php'; // Asegúrate de incluir tu archivo de conexión a la base de datos
                
                // Consulta SQL para obtener las estaciones disponibles
                $sql_stations = "SELECT id_est, Descr FROM est";
                $stmt_stations = $pdo->prepare($sql_stations);
                $stmt_stations->execute();
                
                // Mostrar las estaciones en el select
                while ($row = $stmt_stations->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='" . $row['id_est'] . "'>" . $row['Descr'] . "</option>";
                }
            ?>
        </select>

        <label for="selected_date">Selecciona el día:</label>
		<input type="date" name="selected_date" id="selected_date" required>

        <button type="submit" class="btn-submit">Mostrar Datos</button>
    </form>

    <?php
// Verificar si se ha enviado el formulario
if (isset($_POST['selected_date']) && isset($_POST['station'])) {
    // Recuperar la fecha seleccionada y la estación seleccionada
    $selected_date = $_POST['selected_date'];
    $selected_station = $_POST['station'];

    // Conexión a la base de datos (asegúrate de tener tu archivo db.php correctamente incluido)
    include 'db.php'; // Asegúrate de incluir tu archivo de conexión a la base de datos

    // Consulta SQL para obtener la descripción de la estación seleccionada
    $sql_station = "SELECT Descr FROM est WHERE id_est = :selected_station";
    $stmt_station = $pdo->prepare($sql_station);
    $stmt_station->bindParam(':selected_station', $selected_station, PDO::PARAM_INT);
    $stmt_station->execute();

    // Obtener la descripción de la estación
    $station_data = $stmt_station->fetch(PDO::FETCH_ASSOC);
    $station_descr = $station_data['Descr']; // Almacenar la descripción de la estación

    // Consulta SQL para obtener las máximas, mínimas y promedios de los datos de ese día para la estación seleccionada
    $sql = "
        SELECT 
            DATE(ub1.timestamp) AS Fecha,
            MIN(ub1.BattV) AS MinBattV,
            MAX(ub1.BattV) AS MaxBattV,
            AVG(ub1.BattV) AS AvgBattV,
            MIN(ub1.TempAmb) AS MinTempAmb,
            MAX(ub1.TempAmb) AS MaxTempAmb,
            AVG(ub1.TempAmb) AS AvgTempAmb,
            MIN(ub1.Pbar) AS MinPbar,
            MAX(ub1.Pbar) AS MaxPbar,
            AVG(ub1.Pbar) AS AvgPbar,
            MIN(ub_2.PrecipP) AS MinPrecipP,
            MAX(ub_2.PrecipP) AS MaxPrecipP,
            AVG(ub_2.PrecipP) AS AvgPrecipP,
            MIN(ub_2.Rad) AS MinRad,
            MAX(ub_2.Rad) AS MaxRad,
            AVG(ub_2.Rad) AS AvgRad,
            MIN(ub_2.RH) AS MinRH,
            MAX(ub_2.RH) AS MaxRH,
            AVG(ub_2.RH) AS AvgRH,
            MIN(ub_2.DirV) AS MinDirV,
            MAX(ub_2.DirV) AS MaxDirV,
            AVG(ub_2.DirV) AS AvgDirV
        FROM 
            ub1
        JOIN 
            ub_2 ON ub1.id_est = ub_2.id_est
        WHERE 
            DATE(ub1.timestamp) = :selected_date
            AND ub1.id_est = :selected_station
        GROUP BY 
            DATE(ub1.timestamp), ub1.id_est
    ";

    // Preparar la consulta
    $stmt = $pdo->prepare($sql);

    // Bind los parámetros de la fecha y estación seleccionada
    $stmt->bindParam(':selected_date', $selected_date, PDO::PARAM_STR);
    $stmt->bindParam(':selected_station', $selected_station, PDO::PARAM_INT);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si hay resultados
    if ($stmt->rowCount() > 0) {
        // Mostrar los resultados
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mostrar la información de la estación seleccionada (usamos la descripción)
        echo "<h3>Datos del Día: " . $data['Fecha'] . " - Estación: " . $station_descr . "</h3>";

        // Muestra los resultados dentro de tarjetas con iconos
        echo "<div class='info-cards-container'>";

        // Crear las tarjetas para cada parámetro
        $parameters = [
            'BattV' => ['label' => 'Batería', 'unit' => 'V', 'min' => $data['MinBattV'], 'max' => $data['MaxBattV'], 'avg' => $data['AvgBattV'], 'icon' => '🔋'],
            'TempAmb' => ['label' => 'Temperatura Amb.', 'unit' => '°C', 'min' => $data['MinTempAmb'], 'max' => $data['MaxTempAmb'], 'avg' => $data['AvgTempAmb'], 'icon' => '🌡️'],
            'Pbar' => ['label' => 'Presión Barométrica', 'unit' => 'hPa', 'min' => $data['MinPbar'], 'max' => $data['MaxPbar'], 'avg' => $data['AvgPbar'], 'icon' => '🌬️'],
            'PrecipP' => ['label' => 'Precipitación', 'unit' => 'mm', 'min' => $data['MinPrecipP'], 'max' => $data['MaxPrecipP'], 'avg' => $data['AvgPrecipP'], 'icon' => '🌧️'],
            'Rad' => ['label' => 'Radiación', 'unit' => 'W/m²', 'min' => $data['MinRad'], 'max' => $data['MaxRad'], 'avg' => $data['AvgRad'], 'icon' => '☀️'],
            'RH' => ['label' => 'Humedad Relativa', 'unit' => '%', 'min' => $data['MinRH'], 'max' => $data['MaxRH'], 'avg' => $data['AvgRH'], 'icon' => '💧'],
            'DirV' => ['label' => 'Dirección del Viento', 'unit' => '°', 'min' => $data['MinDirV'], 'max' => $data['MaxDirV'], 'avg' => $data['AvgDirV'], 'icon' => '🌬️']
        ];

        foreach ($parameters as $key => $param) {
            echo "<div class='info-card'>";
            echo "<h4>" . $param['icon'] . " " . $param['label'] . "</h4>";
            echo "<p><strong>Min:</strong> " . $param['min'] . " " . $param['unit'] . "</p>";
            echo "<p><strong>Max:</strong> " . $param['max'] . " " . $param['unit'] . "</p>";
            echo "<p><strong>Promedio:</strong> " . $param['avg'] . " " . $param['unit'] . "</p>";
            echo "</div>";
        }

        echo "</div>";
    } else {
        echo "<p>No se encontraron datos para el día y estación seleccionados.</p>";
    }
}
?>

<!-- End Content page -->
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
<!-- Estilos CSS -->
<style>
    /* Formulario de selección */
    .data-form {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        width: 100%;
        max-width: 600px;
        margin: 0 auto;
        margin-bottom: 30px;
    }

    .data-form label {
        font-weight: bold;
        margin-bottom: 10px;
        display: block;
    }

    .data-form select,
    .data-form input,
    .data-form button {
        width: 100%;
        padding: 12px;
        margin: 10px 0;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-sizing: border-box;
    }

    .btn-submit {
        background-color: #007BFF;
        color: white;
        font-size: 1em;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }

    .btn-submit:hover {
        background-color: #0056b3;
    }

    /* Estilos de las tarjetas */
    .info-cards-container {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
        justify-content: space-around;
    }

    .info-card {
        background-color: #f4f4f4;
        padding: 20px;
        border-radius: 10px;
        width: 280px; /* Ajusta el tamaño de las tarjetas */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        text-align: center;
        transition: transform 0.3s ease, background-color 0.3s ease;
        flex: 1 1 280px; /* Permite que las tarjetas se ajusten en el contenedor */
    }

    .info-card h4 {
        font-size: 1.2em;
        margin-bottom: 10px;
    }

    .info-card p {
        font-size: 1em;
    }
</style>
<script>
    // Obtener la fecha actual
    const today = new Date();
    
    // Restar un día para no permitir seleccionar el día actual
    today.setDate(today.getDate() - 1);

    // Formatear la fecha en el formato YYYY-MM-DD
    const formattedDate = today.toISOString().split('T')[0];

    // Establecer la fecha máxima (sin permitir el día actual)
    document.getElementById("selected_date").setAttribute("max", formattedDate);
</script>


</section>
