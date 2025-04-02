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
        'nombre' => 'Armarios',
        'descripcion' => 'Los armarios intemperie Campbell Scientific están diseñados específicamente para aplicaciones en adquisición de datos. Protegen al equipo del polvo, agua, sol o polución.',
        'imagen' => 'ruta/a/imagen_armarios.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Trípodes',
        'descripcion' => 'Nuestros trípodes para instrumentación se utilizan en variedad de aplicaciones tanto en instalaciones fijas como portátiles. En aplicaciones meteorológicas, el trípode...',
        'imagen' => 'ruta/a/imagen_tripodes.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Torretas de instrumentación',
        'descripcion' => 'Our instrumentation towers are constructed of rust-free aluminum with a steel base. They consist of 10-ft sections that ease shipping and on-site installation.',
        'imagen' => 'ruta/a/imagen_torretas.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Postes de montaje',
        'descripcion' => 'Campbell Scientific offers several vertical poles for mounting sensors, enclosures, or other instrumentation. The poles differ in their length and material.',
        'imagen' => 'ruta/a/imagen_postes.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Brazos y anclajes',
        'descripcion' => 'Crossarms provide a rugged attachment point for securing meteorological sensors, antennas, and other peripherals to our tripods and towers.',
        'imagen' => 'ruta/a/imagen_brazos.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
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
    <h1>Armarios intemperie, trípodes y torretas</h1>

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
