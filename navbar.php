<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Inicia sesión solo si no hay una sesión activa
}
?>
<style>
.dashboard-Navbar .list-unstyled {
    margin: 0;
    padding: 0;
    display: flex;
    align-items: center; /* Centra verticalmente los elementos */
    flex-wrap: nowrap; /* Evita que los elementos se envuelvan en otra línea */
}

.dashboard-Navbar .list-unstyled li {
    margin-left: 0px; /* Espaciado entre los íconos */
}

.dashboard-Navbar a {
    color: white; /* Color de los íconos */
    font-size: 18px; /* Tamaño de los íconos */
    transition: color 0.3s; /* Transición para el hover */
}

.dashboard-Navbar a:hover {
    color: #1abc9c; /* Color al pasar el mouse */
}

.user-info {
    color: white; /* Color del texto */
    font-weight: bold; /* Negrita para el nombre */
    margin-left: 100px; /* Empuja el nombre del usuario hacia la derecha */
    white-space: nowrap; /* Evita el corte del nombre en varias líneas */
}
</style>
<!-- NavBar -->
<nav class="full-box dashboard-Navbar">
    <ul class="full-box list-unstyled text-right">
        <li class="pull-left">
            <a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
        </li>
        <li>
            <a href="#!" class="btn-Notifications-area">
                <i class="zmdi zmdi-notifications-none"></i>
                <span class="badge">3</span>
            </a>
        </li>
        <li>
            <a href="#!" class="btn-search">
                <i class="zmdi zmdi-search"></i>
            </a>
        </li>
        <li>
            <a href="#!" class="btn-modal-help">
                <i class="zmdi zmdi-help-outline"></i>
            </a>
        </li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
        <li></li>
       
        <li class="user-info">
            <span class="username"><?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Usuario'; ?></span>
        </li>
    </ul>
</nav>
