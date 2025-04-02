<?php
session_start();
require 'db.php';

// Verificar si el usuario está logueado y tiene un cargo
if (!isset($_SESSION['id_user']) || !isset($_SESSION['id_cargo'])) {
    die("Acceso no autorizado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¿Quiénes Somos? - Aplicaciones Tecnológicas S.R.L.</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom right, #eef2f5, #dce1e3);
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #2c3e50;
            color: #fff;
            padding: 20px;
            text-align: center;
            font-size: 2em;
            font-weight: bold;
        }
        .content {
            padding: 20px;
            max-width: 900px;
            margin: auto;
            text-align: center;
            background-color: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            animation: fadeIn 1.2s ease-in-out;
        }
        h1 {
            color: #3498db;
            font-size: 2.5em;
            margin-top: 0;
        }
        h2 {
            color: #2c3e50;
            margin-top: 30px;
        }
        .logo {
            margin: 20px auto;
            width: 150px;
            transition: transform 0.3s;
        }
        .logo:hover {
            transform: scale(1.1);
        }
        p {
            color: #555;
            font-size: 1.1em;
            line-height: 1.6em;
            padding: 0 10px;
        }
        .contact-info {
            margin-top: 30px;
        }
        .contact-info p {
            margin: 10px 0;
        }
        .contact-info a {
            color: #3498db;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s;
        }
        .contact-info a:hover {
            color: #2c3e50;
        }
        .section-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 30px;
            gap: 20px;
        }
        .section {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            animation: fadeInUp 1.2s ease-in-out;
        }
        .map {
            margin-top: 20px;
            height: 350px;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        footer {
            text-align: center;
            padding: 15px;
            background-color: #2c3e50;
            color: #fff;
            font-size: 0.9em;
        }

        /* Animaciones */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <header>
        <h1>¿Quiénes Somos?</h1>
    </header>
    <div class="content">
        <img src="http://66.94.116.235/InTec3.0/assets/img/20240916_173639-La1Jdudcy-transformed-removebg-preview.png" alt="Logo de Aplicaciones Tecnológicas S.R.L." class="logo">

        <p>
            Aplicaciones Tecnológicas S.R.L. es una empresa dedicada a la innovación tecnológica. Nuestra misión es ofrecer soluciones tecnológicas avanzadas para satisfacer las necesidades de nuestros clientes y mejorar sus procesos productivos.
        </p>

        <div class="section-container">
            <div class="section">
                <h2>Misión</h2>
                <p>
                    Proveer soluciones tecnológicas innovadoras y personalizadas, promoviendo la eficiencia y desarrollo sostenible de nuestros clientes y contribuyendo al avance tecnológico de nuestra comunidad.
                </p>
            </div>

            <div class="section">
                <h2>Visión</h2>
                <p>
                    Ser líderes en el sector tecnológico a nivel regional, reconocidos por nuestra excelencia, calidad y compromiso en transformar los procesos de nuestros clientes mediante la tecnología.
                </p>
            </div>
        </div>

        <div class="contact-info">
            <h2>Contacto</h2>
            <p>Dirección: Av. 20 de Octubre, esquina, La Paz - Bolivia</p>
            <p>Teléfono: <a href="https://wa.me/+59167141005" target="_blank">+591 6714-1005</a></p>
            <p>Email: <a href="mailto:milenkaaplitecsrl@gmail.com">milenkaaplitecsrl@gmail.com</a></p>
        </div>

        <div class="map">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d15301.333123507176!2d-68.1274877!3d-16.5092637!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x915f20623e358e65%3A0x42157ac0bd97364f!2sEDIFICIO%20GUADALQUIVIR%20%232332%20MEZANINE%20OFICINA%20104!5e0!3m2!1ses!2sbo!4v1730468040181!5m2!1ses!2sbo"
                width="100%"
                height="100%"
                style="border:0;"
                allowfullscreen=""
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<footer>
    &copy; <?php echo date("Y"); ?> Aplicaciones Tecnológicas S.R.L. Todos los derechos reservados.
</footer>

</body>
</html>
<?php include 'footer.php'; ?>
<?php include 'notifications.php'; ?>
