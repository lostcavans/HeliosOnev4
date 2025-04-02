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
                        JOIN user u ON n.id_user = u.id_user  -- JOIN para obtener el nombre del usuario
                        WHERE n.status_not = 1 
                        AND n.target = :id_cargo 
                        AND n.date_end >= CURDATE()  -- Filtrar notificaciones antes de la fecha de cierre
                        ORDER BY n.date_create DESC");
$stmt->execute([':id_cargo' => $id_cargo_usuario]);
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Definir las categorías de productos de Campbell Scientific
$categorias = [
    "Sensores" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/12598-_1_.png",
    "Dispositivos para medida y control" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/10090_1.png",
    "Telecomunicaciones" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/11331.png",
    "Fuentes de alimentacion" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/5129.png",
    "Armarios intemperie, Tripodes y torretas" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/10117.png",
    "Software" => "http://66.94.116.235/InTec3.0/assets/img/productos/categorias/144.png"
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #eef2f5;
            margin: 0;
            padding: 0;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            padding: 20px 0;
        }
        .logo {
            display: flex;
            justify-content: center;
            padding: 20px;
        }
        .logo img {
            max-width: 350px; /* Ajusta el tamaño del logo */
        }
        .container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: auto;
        }
        .card {
            position: relative;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            background-size: cover;
            background-position: center;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 200px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: scale(1.05);
        }
        .card h2 {
            background: rgba(238, 203, 73, 0.8);
            padding: 10px;
            border-radius: 5px;
            width: 100%;
            font-size: 1.5em;
            margin: 0;
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    
    <!-- Logo de Campbell Scientific -->
    <div class="logo">
        <img src="http://66.94.116.235/InTec3.0/assets/img/CSLOGO_HW_STAN.png" alt="Logo de Campbell Scientific">
    </div>

    <div class="container">
        <?php 
        // Limitar categorías a 6 y asegurarte de que no haya categorías vacías
        $categorias = array_slice($categorias, 0, 6); 
        foreach ($categorias as $categoria => $imagen): ?>
            <div class="card" style="background-image: url('<?php echo $imagen; ?>');" onclick="window.location.href='<?php echo strtolower(str_replace(' ', '_', $categoria)); ?>.php'">
                <h2><?php echo $categoria; ?></h2>
            </div>
        <?php endforeach; ?>
        
        <!-- Espacios vacíos si hay menos de 6 elementos -->
        <?php for ($i = count($categorias); $i < 6; $i++): ?>
            <div class="card" style="background-color: transparent;"></div>
        <?php endfor; ?>
    </div>

    <div class="container" style="display: grid; grid-template-columns: repeat(3, 1fr); grid-gap: 20px; padding: 20px; max-width: 1200px; margin: auto;">
        <!-- Aquí podrías agregar más contenido si es necesario -->
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
</body>
</html>
