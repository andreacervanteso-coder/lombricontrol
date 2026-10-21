-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-10-2026 a las 05:27:29
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
-- Base de datos: `lombricario_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `camas_lombriz`
--

CREATE TABLE `camas_lombriz` (
  `id_cama` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `nombre_identificador` varchar(100) NOT NULL,
  `tamano_m2` decimal(5,2) NOT NULL,
  `cantidad_lombrices_inicial` int(11) NOT NULL,
  `fecha_inicio_cuidado` date NOT NULL,
  `estado_actual` varchar(50) DEFAULT 'Activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `camas_lombriz`
--

INSERT INTO `camas_lombriz` (`id_cama`, `id_usuario`, `nombre_identificador`, `tamano_m2`, `cantidad_lombrices_inicial`, `fecha_inicio_cuidado`, `estado_actual`) VALUES
(1, 1, 'Cama de lombricita67', 1.00, 2000, '2026-10-21', 'Activo'),
(2, 2, 'Cama de lombriz_seca', 1.00, 2000, '2026-10-21', 'Activo'),
(3, 3, 'Cama de lombriz_caliente', 1.00, 2000, '2026-10-21', 'Activo'),
(4, 5, 'Cama de andreacervanteso@cbta197.net', 1.00, 2000, '2026-10-21', 'Activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos_sensores`
--

CREATE TABLE `dispositivos_sensores` (
  `id_dispositivo` int(11) NOT NULL,
  `id_cama` int(11) NOT NULL,
  `mac_address` varchar(17) NOT NULL,
  `fecha_instalacion` datetime DEFAULT current_timestamp(),
  `estado_conexion` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `dispositivos_sensores`
--

INSERT INTO `dispositivos_sensores` (`id_dispositivo`, `id_cama`, `mac_address`, `fecha_instalacion`, `estado_conexion`) VALUES
(1, 1, 'AA:BB:CC:DD:EE:FF', '2026-10-20 17:14:08', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lecturas_sensores`
--

CREATE TABLE `lecturas_sensores` (
  `id_lectura` bigint(20) NOT NULL,
  `id_dispositivo` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `temperatura` decimal(5,2) DEFAULT NULL,
  `humedad` decimal(5,2) DEFAULT NULL,
  `nivel_lixiviado` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lecturas_sensores`
--

INSERT INTO `lecturas_sensores` (`id_lectura`, `id_dispositivo`, `fecha_hora`, `temperatura`, `humedad`, `nivel_lixiviado`) VALUES
(3, 1, '2026-10-20 17:14:20', 22.50, 78.00, 0.20),
(4, 1, '2026-10-20 17:21:57', 8.00, 35.00, 0.10),
(6, 1, '2026-10-20 18:48:42', 38.00, 95.00, 1.40),
(7, 1, '2026-10-20 20:06:29', 22.00, 75.00, 0.20),
(8, 1, '2026-10-20 20:06:58', 8.00, 35.00, 0.10),
(9, 1, '2026-10-20 20:07:20', 22.00, 75.00, 0.20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `tamano` enum('poco','medio','mucho') DEFAULT NULL,
  `tiempo` enum('inicial','intermedio','veterano') DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre_usuario`, `password_hash`, `tamano`, `tiempo`, `fecha_registro`) VALUES
(1, 'lombricita67', '$2y$10$ZiSjtLeRL3be.CkeV.Vb0.PfdnIccBy3QfZRLMTaHEoPoBZrN7SKS', 'poco', 'inicial', '2026-10-20 17:06:52'),
(2, 'lombriz_seca', '$2y$10$3Hz2Z71qHtcBiBO4pwAv8eFy.WCSX96oUX5wYu7hwKICXfMxX7sYC', 'poco', 'inicial', '2026-10-20 17:19:45'),
(3, 'lombriz_caliente', '$2y$10$s3xTCNpGBoNrcKbt7CUeluFiQwQSw.v.0Gcuykw81hX7caOFYMZl2', 'poco', 'inicial', '2026-10-20 17:20:10'),
(5, 'andreacervanteso@cbta197.net', '$2y$10$Rkh/.JldDG8ky9CQv4ldA.uuFbKdWeOQ8RFOF/rkk7ZTTHhmU7nBS', 'poco', 'inicial', '2026-10-20 20:11:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `camas_lombriz`
--
ALTER TABLE `camas_lombriz`
  ADD PRIMARY KEY (`id_cama`),
  ADD KEY `fk_cama_usuario` (`id_usuario`);

--
-- Indices de la tabla `dispositivos_sensores`
--
ALTER TABLE `dispositivos_sensores`
  ADD PRIMARY KEY (`id_dispositivo`),
  ADD UNIQUE KEY `mac_address` (`mac_address`),
  ADD KEY `fk_dispositivo_cama` (`id_cama`);

--
-- Indices de la tabla `lecturas_sensores`
--
ALTER TABLE `lecturas_sensores`
  ADD PRIMARY KEY (`id_lectura`),
  ADD KEY `fk_lectura_dispositivo` (`id_dispositivo`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `nombre_usuario` (`nombre_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `camas_lombriz`
--
ALTER TABLE `camas_lombriz`
  MODIFY `id_cama` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `dispositivos_sensores`
--
ALTER TABLE `dispositivos_sensores`
  MODIFY `id_dispositivo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `lecturas_sensores`
--
ALTER TABLE `lecturas_sensores`
  MODIFY `id_lectura` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `camas_lombriz`
--
ALTER TABLE `camas_lombriz`
  ADD CONSTRAINT `fk_cama_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `dispositivos_sensores`
--
ALTER TABLE `dispositivos_sensores`
  ADD CONSTRAINT `fk_dispositivo_cama` FOREIGN KEY (`id_cama`) REFERENCES `camas_lombriz` (`id_cama`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `lecturas_sensores`
--
ALTER TABLE `lecturas_sensores`
  ADD CONSTRAINT `fk_lectura_dispositivo` FOREIGN KEY (`id_dispositivo`) REFERENCES `dispositivos_sensores` (`id_dispositivo`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
