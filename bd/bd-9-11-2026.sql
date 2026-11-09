-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 09-11-2026 a las 19:17:31
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `cafeteria_express`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_ventas`
--

CREATE TABLE `detalle_ventas` (
  `id` int NOT NULL,
  `venta_id` int NOT NULL,
  `producto_id` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `detalle_ventas`
--

INSERT INTO `detalle_ventas` (`id`, `venta_id`, `producto_id`, `cantidad`, `precio_unitario`) VALUES
(1, 1, 2, 1, 6000.00),
(2, 1, 3, 1, 3500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `rol` enum('admin','cliente') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'cliente',
  `fecha_registro` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id`, `nombre`, `telefono`, `email`, `password`, `rol`, `fecha_registro`) VALUES
(1, 'Admin Sistema', '3000000000', 'admin@cafeteria.com', '$2y$10$HaOk5jkWIgoFwoduQatdWeF0wxyC4e/V/y/1SB6IHi86B/xgv/bHK', 'admin', '2026-08-31 21:15:10'),
(2, 'Juan Pérez', '3001234567', 'juan@correo.com', '$2y$10$5T3rNtb79n1Q4AMgW3YSYebnpAwDcf/46TC9wFmxqdQi/GAGbiBwW', 'cliente', '2026-08-31 21:15:10'),
(3, 'María Gómez', '3109876543', NULL, '$2y$10$Vi8BJqCO8pElmu.xvDjokuLcQz7iBsXqN9NBnUWWxhOsGFjfEIrYC', 'cliente', '2026-08-31 21:15:10'),
(4, 'juan', '3144707632', 'juanjo2023orpu@gmail.com', '$2y$10$vcIs.48l5N81SIZ52O3uAeEtpUcsodvsymoSnP6hjAKQGqS1E36R.', 'cliente', '2026-10-28 19:54:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `categoria` enum('alimento','postre','bebida') NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen` varchar(60) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `categoria`, `precio`, `imagen`, `estado`) VALUES
(1, 'Empanada de Carne', 'alimento', 5000.00, 'empanada-carne.jpg', 'activo'),
(2, 'Sándwich de Pollo', 'alimento', 800000.00, 'sanduiwch-pollo.jpg', 'activo'),
(3, 'Jugo de Mora', 'bebida', 1500.00, 'producto_6abc3ed7814790.75138286.jpg', 'activo'),
(4, 'jugo de naranja', 'bebida', 1200.00, 'producto_6abac9c8ea5980.79603282.jpg', 'activo'),
(5, 'torta de chocolate', 'postre', 4000.00, 'producto_6abb0a4915cdd0.68247025.jpg', 'activo'),
(6, 'arroz con leche ', 'postre', 1200.00, 'producto_6abd63ba97bee0.10951664.webp', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `restablecimiento_contrasenas`
--

CREATE TABLE `restablecimiento_contrasenas` (
  `id` int NOT NULL,
  `persona_id` int NOT NULL,
  `token_hash` char(64) NOT NULL,
  `vence_en` datetime NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `restablecimiento_contrasenas`
--

INSERT INTO `restablecimiento_contrasenas` (`id`, `persona_id`, `token_hash`, `vence_en`, `creado_en`) VALUES
(1, 4, 'bc62d053fc159a2d1951ea8f91c78bae7f6f7374e805330ddc627603f1e687da', '2026-10-28 15:54:35', '2026-10-28 19:54:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_venta` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cliente_id`, `total`, `fecha_venta`) VALUES
(1, 2, 8500.00, '2026-08-31 21:15:10');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `venta_id` (`venta_id`),
  ADD KEY `producto_id` (`producto_id`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `restablecimiento_contrasenas`
--
ALTER TABLE `restablecimiento_contrasenas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `persona_id` (`persona_id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `restablecimiento_contrasenas`
--
ALTER TABLE `restablecimiento_contrasenas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_ventas`
--
ALTER TABLE `detalle_ventas`
  ADD CONSTRAINT `detalle_ventas_ibfk_1` FOREIGN KEY (`venta_id`) REFERENCES `ventas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `detalle_ventas_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `restablecimiento_contrasenas`
--
ALTER TABLE `restablecimiento_contrasenas`
  ADD CONSTRAINT `restablecimiento_contrasenas_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `personas` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
