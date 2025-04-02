<?php
session_start(); // Iniciar sesión
require 'db.php'; // Incluir la conexión a la base de datos

// Verificar si el usuario está logueado y tiene un cargo
if (!isset($_SESSION['id_user']) || !isset($_SESSION['id_cargo'])) {
    die("Acceso no autorizado.");
}

// Obtener el ID del cargo del usuario logueado
$id_cargo_usuario = $_SESSION['id_cargo'];

// Obtener todas las notificaciones activas (status_not = 1) del cargo del usuario
$stmt = $pdo->prepare("SELECT n.id_not, n.msg, n.date_create, n.date_end, n.target, n.status_not, c.nom_cargo, u.nombres, u.apel_pat, u.apel_mat 
                        FROM notification n 
                        JOIN cargo c ON n.target = c.id_cargo 
                        JOIN user u ON n.id_user = u.id_user  
                        WHERE n.status_not = 1 
                        AND n.target = :id_cargo 
                        AND n.date_end >= CURDATE()  
                        ORDER BY n.date_create DESC");
$stmt->execute([':id_cargo' => $id_cargo_usuario]);
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Datos de productos
$productos = [
    [
        'nombre' => 'Temperatura del aire',
        'descripcion' => 'Para la medida de la temperatura del aire, en Campbell Scientific ofrecemos termistores, termopares y RTD (Pt100, Pt1000). Disponemos de protectores para la radiación solar, del tipo con ventilación.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/1.webp' // Cambiar la ruta a la imagen correspondiente
    ],
    [
        'nombre' => 'Temperatura y humedad relativa del aire',
        'descripcion' => 'Air temperature and relative humidity sensors typically consist of two separate sensors packaged in the same housing.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/2.webp'
    ],
    [
        'nombre' => 'Sensores meteorológicos todo en uno',
        'descripcion' => 'All-in-one weather sensors measure multiple parameters. For example, these may include wind speed and direction, precipitation, barometric pressure, temperature, and relative humidity.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/3.webp'
    ],
    [
        'nombre' => 'Presión barométrica',
        'descripcion' => 'Los sensores de presión barométrica miden las fluctuaciones de presión en la atmósfera. Estos sensores requieren estar protegidos de la intemperie, de condensación, y de la lluvia.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/4.webp'
    ],
    [
        'nombre' => 'Ceilómetros SkyVUE™',
        'descripcion' => 'La gama de ceilómetros SkyVUE™ de Campbell Scientific utilizan la tecnología (LIght Detection And Ranging) para medir altura de nubes, visibilidad vertical, y altura capa mezcla.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/5.webp'
    ],
    [
        'nombre' => 'Cámara fotos digital',
        'descripcion' => 'Si considera que una imagen vale más que mil palabras, entonces nuestra cámara de fotos digital es lo que necesita! Está diseñada para trabajar en duras condiciones ambientales y es de muy bajo consumo.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/6.webp'
    ],
    [
        'nombre' => 'Oxígeno disuelto en agua',
        'descripcion' => 'Dissolved oxygen sensors measure the amount of oxygen present in a medium, typically water. The sensors generate signals proportional to the amount of oxygen present.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/7.webp'
    ],
    [
        'nombre' => 'Corriente eléctrica',
        'descripcion' => 'Los sensores de medida de la corriente eléctrica detectan el flujo de corriente a lo largo de un cable eléctrico, midiendo el campo magnético que este produce.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/8.webp'
    ],
    [
        'nombre' => 'Evaporación',
        'descripcion' => 'Los medidores de evaporación determinan la evaporación midiendo el cambio de nivel de agua en un tanque evaporímetro.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/9.webp'
    ],
    [
        'nombre' => 'Lluvia helada y detectores hielo',
        'descripcion' => 'Los sensores de lluvia helada detectan la presencia de formación de hielo de forma que se pueden tomar acciones para prevenir daños en líneas eléctricas y de comunicación.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/10.webp'
    ],
    [
        'nombre' => 'Humedad y temperatura del combustible',
        'descripcion' => 'Campbell Scientific fabrica sensores que emulan y miden el contenido de agua y temperatura de ramitas de tamaño similar a las existentes en el suelo del bosque.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/11.webp'
    ],
    [
        'nombre' => 'Global Positioning System (GPS)',
        'descripcion' => 'Estos sensores usan el "Global Positioning Satellites" (GPS) para determinar la posición. Hay tres segmentos para determinar la posición: satélites, estaciones terrestres, y sensores.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/12.webp'
    ],
    [
        'nombre' => 'Flujo calor, vapor de agua, CO2',
        'descripcion' => 'Encontrando la covarianza entre las fluctuaciones del viento vertical y las fluctuaciones de temperatura del aire, vapor de agua y CO2, se puede determinar el flujo de calor sensible y latente.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/13.webp'
    ],
    [
        'nombre' => 'Humectación en hoja',
        'descripcion' => 'Los sensores de humectación en hoja se pueden clasificar en tres tipos: Tipo contacto con superficie, en que mide la resistencia eléctrica de una película de agua sobre la superficie de la hoja.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/14.webp'
    ],
    [
        'nombre' => 'Aviso relámpagos',
        'descripcion' => 'Lightning warning sensors measure the local, atmospheric electric field at the earths’ surface and the fluctuations in field strength.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/15.webp'
    ],
    [
        'nombre' => 'pH agua',
        'descripcion' => 'Nuestros sensores de pH miden la concentración de iones de hidrógeno de una solución. Cuanto mayor es la concentración de iones de hidrógeno, menor es el pH.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/16.webp'
    ],
    [
        'nombre' => 'Precipitación',
        'descripcion' => 'Campbell Scientific comercializa varios dispositivos para la medida de la precipitación: pluviómetro de cazoletas basculantes, pluviómetro calefactado, entre otros.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/17.webp'
    ],
    [
        'nombre' => 'Tiempo presente',
        'descripcion' => 'Los sensores de tiempo presente forman parte a menudo de estaciones meteorológicas automáticas para uso en carreteras, aplicaciones marinas y en aeropuertos.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/18.webp'
    ],
    [
        'nombre' => 'Recording Sensors',
        'descripcion' => 'The sensors in this category have the ability to store measurements without the use of an external datalogger.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/19.webp'
    ],
    [
        'nombre' => 'Redox (ORP)',
        'descripcion' => 'Redox (ORP) sensors measure the oxidation-reduction potential—the tendency to gain or lose electrons when a solution comes in contact with a chemical substance.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/20.webp'
    ],
    [
        'nombre' => 'Sensores equivalencia nieve/agua y grosor capa de nieve',
        'descripcion' => 'Esta sección contiene sensores que miden la cantidad de agua contenida en un manto de nieve o el grosor del manto de nieve.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/21.webp'
    ],
    [
        'nombre' => 'Flujo de calor en suelo',
        'descripcion' => 'Los sensores de flujo de calor miden la cantidad de energía transferida a través de una superficie.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/22.webp'
    ],
    [
        'nombre' => 'Sensores suelo para medida humedad, temperatura y EC',
        'descripcion' => 'Soil moisture sensors measure the water content of soil. These sensors can be used to estimate the amount of stored water in a profile or horizon.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/23.webp'
    ],
    [
        'nombre' => 'Temperatura suelo',
        'descripcion' => 'Para la medida de temperatura de suelo disponemos de termistores, termopares, cable de termopar y termopares promedio.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/24.webp'
    ],
    [
        'nombre' => 'Potencial de agua en suelo',
        'descripcion' => 'Los sensores de potencial de agua en suelo determinan el grado de energía del agua en el suelo.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/25.webp'
    ],
    [
        'nombre' => 'Sensores de Radiación Solar',
        'descripcion' => 'Los sensores de radiación solar que comercializamos en Campbell Scientific son diversos: piranómetros, radiómetros de neta, sensores PAR quantum.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/26.webp'
    ],
    [
        'nombre' => 'Temperatura superficie',
        'descripcion' => 'Campbell Scientific offers two different technologies to measure surface temperature. Surface contact sensors measure the temperature of the surface.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/27.webp'
    ],
    [
        'nombre' => 'Analizadores de CO2 y H2O por infrarrojos',
        'descripcion' => 'Campbell Scientific fabrica analizadores por infrarrojos que miden dióxido de carbono, vapor de agua, temperatura, y presión.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/28.webp'
    ],
    [
        'nombre' => 'Turbidez',
        'descripcion' => 'Los sensores de turbidez miden los sólidos en suspensión del agua, determinando la cantidad de luz transmitida a través del agua.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/29.webp'
    ],
    [
        'nombre' => 'Niveles de agua',
        'descripcion' => 'Los sensores de nivel de agua se utilizan en cuerpos de agua para monitorear cambios en el nivel de agua.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/30.webp'
    ],
    [
        'nombre' => 'Anemómetros',
        'descripcion' => 'Anemómetros miden la velocidad del viento, pudiendo medir además dirección, presión, y temperatura del aire.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/31.webp'
    ],
    [
        'nombre' => 'Medidores de velocidad de agua',
        'descripcion' => 'Los medidores de velocidad de agua utilizan el principio del efecto Doppler para medir la velocidad del agua en ríos o canales.',
        'imagen' => 'http://66.94.116.235/InTec3.0/assets/sensores/32.webp'
    ]
];


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensores</title>
     <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        header {
            background: #007bff;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        header img {
            max-width: 150px;
            margin-bottom: 10px;
        }
        .container {
            width: 90%;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .notification {
            border: 1px solid #007bff;
            border-radius: 5px;
            margin: 15px 0;
            padding: 15px;
            background: #f9f9f9;
            transition: 0.3s;
        }
        .notification:hover {
            box-shadow: 0 2px 15px rgba(0, 123, 255, 0.3);
        }
        .producto {
            display: flex;
            align-items: center;
            border: 1px solid #ccc; /* Borde del cuadrado */
            border-radius: 8px; /* Bordes redondeados */
            margin: 10px 0; /* Espaciado entre productos */
            padding: 10px; /* Espaciado interno */
        }
        .producto img {
            width: 100px; /* Tamaño de la imagen */
            height: auto; /* Mantiene la proporción de la imagen */
            margin-left: 10px; /* Espacio entre la descripción y la imagen */
        }
		.logo {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .logo img {
            max-width: 350px; /* Ajusta el tamaño del logo */
        }
.descripcion {
            flex: 1; /* La descripción toma el espacio disponible */
        }
    </style>
</head>

    


<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <div class="logo">
        <img src="http://66.94.116.235/InTec3.0/assets/img/CSLOGO_HW_STAN.png" alt="Logo de Campbell Scientific">
    </div>
    <h1>Sensores</h1>

<?php foreach ($productos as $producto): ?>
    <div class="producto">
        <div class="descripcion">
            <h2><?php echo $producto['nombre']; ?></h2>
            <p><?php echo $producto['descripcion']; ?></p>
        </div>
        <img src="<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>">
    </div>
<?php endforeach; ?>
</section>

</body>
</html>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
