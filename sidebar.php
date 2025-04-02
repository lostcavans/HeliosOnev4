<?php
// sidebar.php


// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user']) || !isset($_SESSION['id_cargo'])) {
    die("Acceso no autorizado.");
}

// Obtener el ID del cargo del usuario logueado
$id_cargo_usuario = $_SESSION['id_cargo'];
?>

<!-- SideBar -->
<section class="full-box cover dashboard-sideBar">
    <div class="full-box dashboard-sideBar-bg btn-menu-dashboard"></div>
    <div class="full-box dashboard-sideBar-ct">
        <!--SideBar Title -->
        <div class="full-box text-uppercase text-center text-titles dashboard-sideBar-title">
            Bien-Venido <i class="zmdi zmdi-close btn-menu-dashboard visible-xs"></i>
        </div>
        <!-- SideBar User info -->
        <div class="full-box dashboard-sideBar-UserInfo">
            <figure class="full-box">
                <img src="assets\img\bomb-removebg-preview.png" alt="UserIcon">
                <figcaption class="text-center text-titles">Sistema de gerenciamiento de cuerpo de Bomberos - La Paz/Bolivia</figcaption>
            </figure>
            <ul class="full-box list-unstyled text-center">
                <li>
                    <a href="#!">
                        <i class="zmdi zmdi-settings"></i>
                    </a>
                </li>
                <li>
					<a href="#!" class="btn-exit-system" onclick="confirmLogout()">
						<i class="zmdi zmdi-power"></i>
					</a>
				</li>
            </ul>
        </div>
        <!-- SideBar Menu -->
        <ul class="list-unstyled full-box dashboard-sideBar-Menu">
    <!-- Menú principal para el cargo 1: Acceso total -->
    <?php if ($id_cargo_usuario == 1): ?>
        <!-- Enlace: Notificaciones -->
        <li>
            <a href="home.php" class="menu-link">
                <i class="zmdi zmdi-notifications zmdi-hc-fw"></i> Notificaciones
            </a>
        </li>

        <!-- Enlace: Actividades -->
        <li>
            <a href="home.php" class="menu-link">
                <i class="zmdi zmdi-assignment zmdi-hc-fw"></i> Actividades
            </a>
        </li>

        <!-- Enlace: Mapa -->
        <li>
            <a href="map.php" class="menu-link">
                <i class="zmdi zmdi-map zmdi-hc-fw"></i> Mapa
            </a>
        </li>

        <!-- Enlace: Situación médica -->
        <li>
            <a href="home.php" class="menu-link">
                <i class="zmdi zmdi-favorite zmdi-hc-fw"></i> Situación médica
            </a>
        </li>

        <!-- Submenú: Usuarios -->
        <li>
            <a href="#" class="btn-sideBar-SubMenu">
                <i class="zmdi zmdi-accounts zmdi-hc-fw"></i> Usuarios <i class="zmdi zmdi-caret-down pull-right"></i>
            </a>
            <ul class="list-unstyled submenu">
                <li><a href="register_user.php"><i class="zmdi zmdi-account-add zmdi-hc-fw"></i> Registrar</a></li>
                <li><a href="list_users.php"><i class="zmdi zmdi-edit zmdi-hc-fw"></i> Modificar/Eliminar</a></li>
            </ul>
        </li>


        <!-- Submenú: Grupos -->
        <li>
            <a href="#" class="btn-sideBar-SubMenu">
                <i class="zmdi zmdi-group zmdi-hc-fw"></i> Grupos <i class="zmdi zmdi-caret-down pull-right"></i>
            </a>
            <ul class="list-unstyled submenu">
                <li><a href="reg_group.php"><i class="zmdi zmdi-people zmdi-hc-fw"></i> Registrar</a></li>
                <li><a href="list_group.php"><i class="zmdi zmdi-people-outline zmdi-hc-fw"></i> Modificar/Eliminar</a></li>
            </ul>
        </li>

        <!-- Submenú: Misiones -->
        <li>
            <a href="#" class="btn-sideBar-SubMenu">
                <i class="zmdi zmdi-flight-takeoff zmdi-hc-fw"></i> Misiones <i class="zmdi zmdi-caret-down pull-right"></i>
            </a>
            <ul class="list-unstyled submenu">
                <li><a href="reg_mis.php"><i class="zmdi zmdi-pin zmdi-hc-fw"></i> Registrar</a></li>
                <li><a href="list_mision.php"><i class="zmdi zmdi-edit zmdi-hc-fw"></i> Modificar/Eliminar</a></li>
            </ul>
        </li>

        <!-- Submenú: Reportes -->
        <li>
            <a href="#" class="btn-sideBar-SubMenu">
                <i class="zmdi zmdi-chart zmdi-hc-fw"></i> Reportes <i class="zmdi zmdi-caret-down pull-right"></i>
            </a>
            <ul class="list-unstyled submenu">
                <li><a href="reporte_logs.php"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Login/Logout</a></li>
                <li><a href="reporte_misiones_finalizadas.php"><i class="zmdi zmdi-assignment-check zmdi-hc-fw"></i> Misiones</a></li>
                <li><a href="report_salud.php"><i class="zmdi zmdi-healing zmdi-hc-fw"></i> Salud</a></li>
            </ul>
        </li>

        <!-- Enlace: Backup -->
        <li>
            <a href="map.php" class="menu-link">
                <i class="zmdi zmdi-cloud zmdi-hc-fw"></i> Backup
            </a>
        </li>

        <!-- Submenú: Avisos -->
        <li>
            <a href="#" class="btn-sideBar-SubMenu">
                <i class="zmdi zmdi-alert-circle zmdi-hc-fw"></i> Avisos <i class="zmdi zmdi-caret-down pull-right"></i>
            </a>
            <ul class="list-unstyled submenu">
                <li><a href="notification.php"><i class="zmdi zmdi-notifications-add zmdi-hc-fw"></i> Registrar</a></li>
                <li><a href="list_notification.php"><i class="zmdi zmdi-notifications-off zmdi-hc-fw"></i> Modificar/Eliminar</a></li>
            </ul>
        </li>
    <?php endif; ?>
</ul>

    </div>
</section>
