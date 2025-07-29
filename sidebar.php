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
                <img src="assets/img/bomb-removebg-preview.png" alt="UserIcon">
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
            <?php if ($id_cargo_usuario == 51): ?>
                
                <!-- Enlace: Actividades -->
                <li>
                    <a href="mision.php" class="menu-link">
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

                <!-- Submenú: Inventario -->
                <li>
                    <a href="#" class="btn-sideBar-SubMenu">
                        <i class="zmdi zmdi-warehouse zmdi-hc-fw"></i> Inventario <i class="zmdi zmdi-caret-down pull-right"></i>
                    </a>
                    <ul class="list-unstyled submenu">
                        <!-- Almacén -->
                        <li>
                            <a href="crud_almacen.php"><i class="zmdi zmdi-collection-item zmdi-hc-fw"></i> Almacén</a>
                        </li>
                        <!-- Movimientos de almacén -->
                        <li>
                            <a href="movimientos_almacen.php"><i class="zmdi zmdi-swap zmdi-hc-fw"></i> Movimientos</a>
                        </li>
                        <!-- Equipos -->
                        <li>
                            <a href="crud_equipos.php"><i class="zmdi zmdi-devices zmdi-hc-fw"></i> Equipos</a>
                        </li>
                        <!-- Repuestos -->
                        <li>
                            <a href="crud_repuestos.php"><i class="zmdi zmdi-cog zmdi-hc-fw"></i> Repuestos</a>
                        </li>
                        <!-- Asignar repuestos -->
                        <li>
                            <a href="asignar_repuestos.php"><i class="zmdi zmdi-check-all zmdi-hc-fw"></i> Asignar Repuestos</a>
                        </li>
                        <!-- Categorías de almacén -->
                        <li>
                            <a href="categorias_almacen.php"><i class="zmdi zmdi-label zmdi-hc-fw"></i> Categorías</a>
                        </li>
                    </ul>
                </li>

                <!-- Submenú: Mantenimiento -->
                <li>
                    <a href="#" class="btn-sideBar-SubMenu">
                        <i class="zmdi zmdi-wrench zmdi-hc-fw"></i> Mantenimiento <i class="zmdi zmdi-caret-down pull-right"></i>
                    </a>
                    <ul class="list-unstyled submenu">
                        <li><a href="crud_mantenimiento.php"><i class="zmdi zmdi-format-list-bulleted zmdi-hc-fw"></i> Listado</a></li>
                        <li><a href="calendar_mantenimiento.php"><i class="zmdi zmdi-calendar zmdi-hc-fw"></i> Calendario</a></li>
                    </ul>
                </li>

                <!-- Submenú: Turnos -->
                <li>
                    <a href="#" class="btn-sideBar-SubMenu">
                        <i class="zmdi zmdi-time zmdi-hc-fw"></i> Turnos <i class="zmdi zmdi-caret-down pull-right"></i>
                    </a>
                    <ul class="list-unstyled submenu">
                        <li><a href="crud_turnos.php"><i class="zmdi zmdi-format-list-bulleted zmdi-hc-fw"></i> Gestión de Turnos</a></li>
                        <li><a href="asignar_turnos.php"><i class="zmdi zmdi-assignment-account zmdi-hc-fw"></i> Asignar Turnos</a></li>
                    </ul>
                </li>

                <!-- Submenú: Cargos -->
                <li>
                    <a href="crud_cargos.php" class="menu-link">
                        <i class="zmdi zmdi-accounts-list zmdi-hc-fw"></i> Cargos
                    </a>
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
                        <li><a href="reporte_inventario.php"><i class="zmdi zmdi-warehouse zmdi-hc-fw"></i> Inventario</a></li>
                    </ul>
                </li>

                <!-- Submenú: Bitacora -->
                <li>
                    <a href="bitacora.php" class="menu-link">
                        <i class="zmdi zmdi-accounts-list zmdi-hc-fw"></i> Bitácora
                    </a>
                </li>

                <!-- Enlace: Backup -->
                <li>
                    <a href="backup.php" class="menu-link">
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
                        <li><a href="gestion_notificaciones.php"><i class="zmdi zmdi-notifications-off zmdi-hc-fw"></i> Modificar/Eliminar</a></li>
                    </ul>
                </li>
            <?php endif; ?>

            <!-- Menú para otros cargos con acceso limitado -->
            <?php if (in_array($id_cargo_usuario, [52, 53])): ?>
                <!-- Enlace: Actividades -->
                <li>
                    <a href="mision.php" class="menu-link">
                        <i class="zmdi zmdi-assignment zmdi-hc-fw"></i> Actividades
                    </a>
                </li>

                <!-- Enlace: Situación médica -->
                <li>
                    <a href="home.php" class="menu-link">
                        <i class="zmdi zmdi-favorite zmdi-hc-fw"></i> Situación médica
                    </a>
                </li>

                <!-- Submenú: Inventario (acceso limitado) -->
                <li>
                    <a href="#" class="btn-sideBar-SubMenu">
                        <i class="zmdi zmdi-warehouse zmdi-hc-fw"></i> Inventario <i class="zmdi zmdi-caret-down pull-right"></i>
                    </a>
                    <ul class="list-unstyled submenu">
                        <li>
                            <a href="movimientos_almacen.php"><i class="zmdi zmdi-swap zmdi-hc-fw"></i> Movimientos</a>
                        </li>
                        <li>
                            <a href="crud_equipos.php"><i class="zmdi zmdi-devices zmdi-hc-fw"></i> Equipos</a>
                        </li>
                    </ul>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</section>

<script>
function confirmLogout() {
    if (confirm("¿Está seguro que desea cerrar sesión?")) {
        window.location.href = "logout.php";
    }
}
</script>