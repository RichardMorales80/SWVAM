-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 25-07-2025 a las 04:59:58
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
-- Base de datos: `matthew`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `idusuario` int(15) NOT NULL,
  `primernombre` varchar(40) NOT NULL,
  `primerapellido` varchar(40) NOT NULL,
  `correo` varchar(40) NOT NULL,
  `telefono` varchar(16) NOT NULL,
  `lugarderesidencia` varchar(40) NOT NULL,
  `Password` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idusuario`, `primernombre`, `primerapellido`, `correo`, `telefono`, `lugarderesidencia`, `Password`) VALUES
(33, 'Carlos', 'Perez', 'carlos@gmail.com', '5578913258', 'Estado de Mexicio', 'Negrito789#   '),
(36, 'Richard', 'Morales', 'ricahrd@gmail.com', '5578900123', 'Guadalajara', 'Es1821011884#'),
(52, 'Luis', 'Rodriguez', 'luisr@hotmail.com', '5567656987', 'Puebla', 'Progweb2#'),
(53, 'Alberto', 'Dominguez', 'dominguez7@hotmail.com', '5533578123', 'Campeche', 'Alberto25$'),
(54, 'Armando', 'Juarez', 'armando12@gmail.com', '27159871568', 'Queretaro', 'Ajuarez12#'),
(57, 'Francisco', 'Tellez', 'tellez58@hotmail.com', '5548791230', 'Toluca', 'Vargas789#'),
(58, 'Rodrigo', 'lara', 'laras@gmail.com', '558912345', 'Estado de Mexico', 'Mexico1020#');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
