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
        'nombre' => 'Dataloggers y Sistemas Adquisición de Datos',
        'descripcion' => 'Los dataloggers Campbell Scientific son el corazón de nuestros sistemas de adquisición de datos, caracterizados por su robustez y fiabilidad. Los distintos modelos comparten su funcionalidad y calidad.',
        'imagen' => 'ruta/a/imagen1.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Adquisición de datos distribuida',
        'descripcion' => 'La adquisición de datos distribuida conecta a la perfección todos los componentes de un sistema de monitorización integrado. La sincronización resultante disminuye la cantidad y longitud de los cables.',
        'imagen' => 'ruta/a/imagen2.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Periféricos expansión canales',
        'descripcion' => 'Los periféricos de expansión se utilizan junto con los dataloggers para diferentes propósitos: añadir canales, ampliar redes de comunicaciones de datos, adaptadores de medios, leer sensores de cuerda.',
        'imagen' => 'ruta/a/imagen3.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Conversores y adaptadores',
        'descripcion' => 'Almacenamiento de datos externo',
        'imagen' => 'ruta/a/imagen4.jpg', // Cambia esto a la ruta de la imagen del producto
    ],
    [
        'nombre' => 'Almacenamiento de datos externo',
        'descripcion' => 'En Campbell Scientific disponemos de varios dispositivos de almacenamiento. Estos se usan para backup de datos, almacenamiento extra o como sistema alternativo de descarga de datos in-situ.',
        'imagen' => 'ruta/a/imagen5.jpg', // Cambia esto a la ruta de la imagen del producto
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
    <h1>Dispositivos para medida y control</h1>

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
