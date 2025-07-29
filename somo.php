<?php
// somo.php - Versión corregida

// Iniciar sesión de forma segura
require_once 'auth_check.php';
check_auth();

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Quiénes Somos? - Aplicaciones Tecnológicas S.R.L.</title>
    <style>
        /* [Mantener todos tus estilos actuales] */
    </style>
</head>
<body>
    <?php 
    // Incluir componentes de la interfaz
    include 'header.php'; 
    include 'sidebar.php';
    include 'navbar.php';
    ?>
    
    <section class="full-box dashboard-contentPage">
        <header>
            <h1>¿Quiénes Somos?</h1>
        </header>
        
        <div class="content">
            <!-- [Mantener todo tu contenido HTML actual] -->
        </div>
    </section>

    <?php 
    // Incluir componentes finales
    include 'footer.php';
    include 'notifications.php';
    ?>
</body>
</html>