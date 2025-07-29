-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-07-2025 a las 04:42:16
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_helios`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacen`
--

CREATE TABLE `almacen` (
  `id_item` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `unidad_medida` varchar(20) NOT NULL,
  `minimo_stock` int(11) DEFAULT NULL,
  `ubicacion` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_repuestos`
--

CREATE TABLE `asignacion_repuestos` (
  `id_asignacion` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `id_responsable` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignacion_turnos`
--

CREATE TABLE `asignacion_turnos` (
  `id_asignacion` int(11) NOT NULL,
  `id_turno` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `id_mision` int(11) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bpm_data`
--

CREATE TABLE `bpm_data` (
  `id_user` int(11) NOT NULL,
  `bpm` float NOT NULL,
  `SPo2` float NOT NULL,
  `estado` varchar(50) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `nom_cargo` varchar(100) NOT NULL,
  `stat_cargo` int(11) NOT NULL DEFAULT 1,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos`
--

CREATE TABLE `equipos` (
  `id_equipo` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` varchar(50) NOT NULL,
  `serial` varchar(50) DEFAULT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `estado` enum('disponible','en_uso','mantenimiento','baja') NOT NULL DEFAULT 'disponible',
  `id_responsable` int(11) DEFAULT NULL,
  `ultimo_mantenimiento` date DEFAULT NULL,
  `proximo_mantenimiento` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gas_data`
--

CREATE TABLE `gas_data` (
  `id_user` int(11) NOT NULL,
  `ppm` float NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gps_data`
--

CREATE TABLE `gps_data` (
  `id_user` int(11) NOT NULL,
  `latitude` float NOT NULL,
  `longitude` float NOT NULL,
  `speed` float NOT NULL,
  `altitude` float NOT NULL,
  `satelites` int(11) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `grupo`
--

CREATE TABLE `grupo` (
  `id_grupo` int(11) NOT NULL,
  `nom_grup` varchar(50) NOT NULL,
  `stat_grupo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos`
--

CREATE TABLE `mantenimientos` (
  `id_mantenimiento` int(11) NOT NULL,
  `id_equipo` int(11) NOT NULL,
  `tipo` enum('preventivo','correctivo','predictivo') NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_programada` date NOT NULL,
  `fecha_realizacion` date DEFAULT NULL,
  `estado` enum('pendiente','en_proceso','completado','cancelado') NOT NULL DEFAULT 'pendiente',
  `id_tecnico` int(11) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `repuestos_utilizados` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mision`
--

CREATE TABLE `mision` (
  `id_mis` int(11) NOT NULL,
  `nom_mis` varchar(100) NOT NULL,
  `des_mis` varchar(200) NOT NULL,
  `id_grupo` int(11) NOT NULL,
  `fec_mis` datetime NOT NULL,
  `stat_mis` int(11) NOT NULL,
  `fin_mis` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_almacen`
--

CREATE TABLE `movimientos_almacen` (
  `id_movimiento` int(11) NOT NULL,
  `id_item` int(11) NOT NULL,
  `tipo` enum('entrada','salida','ajuste') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `motivo` varchar(255) DEFAULT NULL,
  `id_responsable` int(11) NOT NULL,
  `id_mision` int(11) DEFAULT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notification`
--

CREATE TABLE `notification` (
  `id_not` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `msg` text NOT NULL,
  `date_create` datetime NOT NULL,
  `date_end` date NOT NULL,
  `target` int(11) NOT NULL COMMENT 'ID del cargo destinatario',
  `status_not` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=activa, 0=inactiva'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prev_data`
--

CREATE TABLE `prev_data` (
  `id_user` int(11) NOT NULL,
  `estado_prev` int(11) NOT NULL,
  `timestamp_prev` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reg_user`
--

CREATE TABLE `reg_user` (
  `id_user` int(11) NOT NULL,
  `id_reg` int(11) NOT NULL,
  `log` tinyint(1) NOT NULL,
  `mac` varchar(100) NOT NULL,
  `ip` varchar(100) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuestos`
--

CREATE TABLE `repuestos` (
  `id_repuesto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `modelo_compatible` varchar(100) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 0,
  `ubicacion` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE `user` (
  `id_user` int(11) NOT NULL,
  `id_dis` int(11) NOT NULL,
  `nom_user` varchar(50) NOT NULL,
  `apel_user` varchar(50) NOT NULL,
  `cel_user` varchar(10) NOT NULL,
  `dir_user` varchar(50) NOT NULL,
  `fec_nac_user` date NOT NULL,
  `email_user` varchar(80) NOT NULL,
  `CI_user` varchar(8) NOT NULL,
  `gen_user` int(1) NOT NULL,
  `pass_user` varchar(255) NOT NULL,
  `status_user` int(11) NOT NULL,
  `id_cargo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_grup`
--

CREATE TABLE `user_grup` (
  `id_grupo` int(11) NOT NULL,
  `id_user` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `almacen`
--
ALTER TABLE `almacen`
  ADD PRIMARY KEY (`id_item`);

--
-- Indices de la tabla `asignacion_repuestos`
--
ALTER TABLE `asignacion_repuestos`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `id_repuesto` (`id_repuesto`),
  ADD KEY `id_dispositivo` (`id_dispositivo`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `asignacion_turnos`
--
ALTER TABLE `asignacion_turnos`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `id_turno` (`id_turno`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_mision` (`id_mision`);

--
-- Indices de la tabla `bpm_data`
--
ALTER TABLE `bpm_data`
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`);

--
-- Indices de la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD PRIMARY KEY (`id_equipo`),
  ADD KEY `id_responsable` (`id_responsable`);

--
-- Indices de la tabla `gas_data`
--
ALTER TABLE `gas_data`
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `gps_data`
--
ALTER TABLE `gps_data`
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `grupo`
--
ALTER TABLE `grupo`
  ADD PRIMARY KEY (`id_grupo`);

--
-- Indices de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_tecnico` (`id_tecnico`);

--
-- Indices de la tabla `mision`
--
ALTER TABLE `mision`
  ADD PRIMARY KEY (`id_mis`),
  ADD KEY `id_grupo` (`id_grupo`);

--
-- Indices de la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `id_item` (`id_item`),
  ADD KEY `id_responsable` (`id_responsable`),
  ADD KEY `id_mision` (`id_mision`);

--
-- Indices de la tabla `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`id_not`),
  ADD KEY `id_user` (`id_user`),
  ADD KEY `target` (`target`);

--
-- Indices de la tabla `prev_data`
--
ALTER TABLE `prev_data`
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `reg_user`
--
ALTER TABLE `reg_user`
  ADD PRIMARY KEY (`id_reg`),
  ADD KEY `id_user` (`id_user`);

--
-- Indices de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  ADD PRIMARY KEY (`id_repuesto`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD KEY `id_cargo` (`id_cargo`);

--
-- Indices de la tabla `user_grup`
--
ALTER TABLE `user_grup`
  ADD KEY `id_grupo` (`id_grupo`),
  ADD KEY `id_user` (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `almacen`
--
ALTER TABLE `almacen`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignacion_repuestos`
--
ALTER TABLE `asignacion_repuestos`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asignacion_turnos`
--
ALTER TABLE `asignacion_turnos`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipos`
--
ALTER TABLE `equipos`
  MODIFY `id_equipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `grupo`
--
ALTER TABLE `grupo`
  MODIFY `id_grupo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mision`
--
ALTER TABLE `mision`
  MODIFY `id_mis` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notification`
--
ALTER TABLE `notification`
  MODIFY `id_not` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reg_user`
--
ALTER TABLE `reg_user`
  MODIFY `id_reg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `repuestos`
--
ALTER TABLE `repuestos`
  MODIFY `id_repuesto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asignacion_repuestos`
--
ALTER TABLE `asignacion_repuestos`
  ADD CONSTRAINT `asignacion_repuestos_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuestos` (`id_repuesto`),
  ADD CONSTRAINT `asignacion_repuestos_ibfk_2` FOREIGN KEY (`id_dispositivo`) REFERENCES `equipos` (`id_equipo`),
  ADD CONSTRAINT `asignacion_repuestos_ibfk_3` FOREIGN KEY (`id_responsable`) REFERENCES `user` (`id_user`);

--
-- Filtros para la tabla `asignacion_turnos`
--
ALTER TABLE `asignacion_turnos`
  ADD CONSTRAINT `asignacion_turnos_ibfk_1` FOREIGN KEY (`id_turno`) REFERENCES `turnos` (`id_turno`),
  ADD CONSTRAINT `asignacion_turnos_ibfk_2` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `asignacion_turnos_ibfk_3` FOREIGN KEY (`id_mision`) REFERENCES `mision` (`id_mis`);

--
-- Filtros para la tabla `bpm_data`
--
ALTER TABLE `bpm_data`
  ADD CONSTRAINT `bpm_data_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `equipos`
--
ALTER TABLE `equipos`
  ADD CONSTRAINT `equipos_ibfk_1` FOREIGN KEY (`id_responsable`) REFERENCES `user` (`id_user`) ON DELETE SET NULL;

--
-- Filtros para la tabla `gas_data`
--
ALTER TABLE `gas_data`
  ADD CONSTRAINT `gas_data_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `gps_data`
--
ALTER TABLE `gps_data`
  ADD CONSTRAINT `gps_data_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `mantenimientos`
--
ALTER TABLE `mantenimientos`
  ADD CONSTRAINT `mantenimientos_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos` (`id_equipo`),
  ADD CONSTRAINT `mantenimientos_ibfk_2` FOREIGN KEY (`id_tecnico`) REFERENCES `user` (`id_user`);

--
-- Filtros para la tabla `mision`
--
ALTER TABLE `mision`
  ADD CONSTRAINT `mision_ibfk_1` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `movimientos_almacen`
--
ALTER TABLE `movimientos_almacen`
  ADD CONSTRAINT `movimientos_almacen_ibfk_1` FOREIGN KEY (`id_item`) REFERENCES `almacen` (`id_item`),
  ADD CONSTRAINT `movimientos_almacen_ibfk_2` FOREIGN KEY (`id_responsable`) REFERENCES `user` (`id_user`),
  ADD CONSTRAINT `movimientos_almacen_ibfk_3` FOREIGN KEY (`id_mision`) REFERENCES `mision` (`id_mis`);

--
-- Filtros para la tabla `prev_data`
--
ALTER TABLE `prev_data`
  ADD CONSTRAINT `prev_data_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reg_user`
--
ALTER TABLE `reg_user`
  ADD CONSTRAINT `reg_user_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `user_ibfk_1` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `user_grup`
--
ALTER TABLE `user_grup`
  ADD CONSTRAINT `user_grup_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_grup_ibfk_2` FOREIGN KEY (`id_grupo`) REFERENCES `grupo` (`id_grupo`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
