-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 25-07-2025 a las 04:48:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET FOREIGN_KEY_CHECKS=0;
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
CREATE DATABASE IF NOT EXISTS `matthew` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `matthew`;

DELIMITER $$
--
-- Procedimientos
--
DROP PROCEDURE IF EXISTS `agregarGastos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarGastos` (IN `fechaGasto` DATE, IN `conceptoGasto` VARCHAR(255), IN `montoGasto` DECIMAL(10,2))   BEGIN
    INSERT INTO tablaGastos (fechaCarga, fecha, concepto, monto)
    VALUES (
        NOW(),
        fechaGasto,
        LOWER(conceptoGasto),
        montoGasto
    );
END$$

DROP PROCEDURE IF EXISTS `agregarProducto`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `agregarProducto` (IN `rfcProveedor` VARCHAR(12), IN `nombreProveedor` VARCHAR(255), IN `paisProveedor` VARCHAR(255), IN `correoProveedor` VARCHAR(255), IN `telefonoProveedor` VARCHAR(255), IN `codigoBarrasProducto` VARCHAR(13), IN `nombreProducto` VARCHAR(255), IN `descripcionProducto` VARCHAR(255), IN `precioVentaProducto` DECIMAL(10,2))   BEGIN
 call crearProveedores(rfcProveedor,nombreProveedor,paisProveedor,correoProveedor,telefonoProveedor);
 call insertarTablaproductos(codigoBarrasProducto,nombreProducto,descripcionProducto,precioVentaProducto);
END$$

DROP PROCEDURE IF EXISTS `auxiliarShowTables`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `auxiliarShowTables` ()   BEGIN
select*From tablaProductos;
select*from tablaProveedores;
select*from tablaDirectorio;
select*from tablaCatalogo;
select*from tablaAlmacen;
select*from tablaCompras;
select*from tablaGastos;
select*from tablaClientes;
select*from tablaVentas;
END$$

DROP PROCEDURE IF EXISTS `buscarProveedor`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `buscarProveedor` (IN `proveedor` VARCHAR(255))   BEGIN
select*from tablaProveedores where nombre = proveedor;
END$$

DROP PROCEDURE IF EXISTS `crearProveedores`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `crearProveedores` (IN `rfcProveedor` VARCHAR(12), IN `nombreProveedor` VARCHAR(255), IN `paisProveedor` VARCHAR(255), IN `correoProveedor` VARCHAR(255), IN `telefonoProveedor` VARCHAR(255))   BEGIN
    -- Verificamos si el proveedor ya existe
    IF NOT EXISTS (SELECT 1 FROM tablaProveedores WHERE rfc = rfcProveedor) THEN
        INSERT INTO tablaProveedores (rfc, nombre, pais)
        VALUES (rfcProveedor, nombreProveedor, paisProveedor);
    END IF;

    -- Verificamos si el directorio ya existe
    IF NOT EXISTS (SELECT 1 FROM tablaDirectorio WHERE rfc = rfcProveedor) THEN
        INSERT INTO tablaDirectorio (rfc, correo, telefono)
        VALUES (rfcProveedor, correoProveedor, telefonoProveedor);
    END IF;
END$$

DROP PROCEDURE IF EXISTS `eliminarGastos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarGastos` (IN `fechaResgistro` DATETIME)   BEGIN
SET SQL_SAFE_UPDATES = 0;
delete from tablaGastos where fechaCarga=fechaResgistro;
SET SQL_SAFE_UPDATES = 1;
END$$

DROP PROCEDURE IF EXISTS `eliminarProveedores`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `eliminarProveedores` (IN `rfcProveedor` VARCHAR(12))   BEGIN
delete from tablaDirectorio where rfc= rfcProveedor; 
delete from tablaProveedores where rfc= rfcProveedor;
END$$

DROP PROCEDURE IF EXISTS `insertarProductos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `insertarProductos` (IN `codigoBarras` VARCHAR(13), IN `nombre` VARCHAR(255), IN `descripcion` VARCHAR(255), IN `precioVenta` DECIMAL(10,2))   BEGIN
  INSERT INTO tablaProductos (codigoBarras, nombre, descripcion, precioVenta)
  VALUES (
    codigoBarras,
    LOWER(nombre),
    LOWER(descripcion),
    precioVenta
  );
END$$

DROP PROCEDURE IF EXISTS `insertar_tablaProductos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `insertar_tablaProductos` (IN `codigoBarrasProducto` VARCHAR(13), IN `nombreProducto` VARCHAR(255), IN `descripcionProducto` VARCHAR(255), IN `precioVentaProducto` DECIMAL(10,2))   BEGIN
insert into tablaProductos (codigoBarras,nombre,descripcion,precioVenta)
values(
codigoBarrasProducto,
lower(codigoBarrasProducto),
lower(descripcionProducto),
precioVentaProducto
);
END$$

DROP PROCEDURE IF EXISTS `insertar_tablaProveedores`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `insertar_tablaProveedores` (IN `rfcProveedor` VARCHAR(12), IN `nombreProveedor` VARCHAR(255), IN `paisProveedor` VARCHAR(255))   BEGIN
insert into tablaProveedores (rfc,nombre,pais)
values(
rfcProveedor,
lower(nombreProveedor),
lower(paisProveedor)
);
END$$

DROP PROCEDURE IF EXISTS `leerProveedores`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `leerProveedores` ()   BEGIN
select 
	p.rfc,
    p.nombre,
    p.pais,
    d.correo,
    d.telefono
from 
	tablaproveedores p 
inner join 
	tablaDirectorio d 
on 
	p.rfc=d.rfc
order by rfc asc;
END$$

DROP PROCEDURE IF EXISTS `modificarGastos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `modificarGastos` (IN `fechaResgistro` DATETIME, IN `fechaGasto` DATE, IN `conceptoGasto` VARCHAR(255), IN `montoGasto` DECIMAL(10,2))   BEGIN
SET SQL_SAFE_UPDATES = 0;
update tablaGastos 
set 
	fecha = fechaGasto,
    concepto = conceptoGasto,
    monto = montoGasto
where fechaCarga = fechaResgistro;
SET SQL_SAFE_UPDATES = 1;
END$$

DROP PROCEDURE IF EXISTS `modificarProveedores`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `modificarProveedores` (IN `rfcProveedor` VARCHAR(12), IN `nombreProveedor` VARCHAR(255), IN `paisProveedor` VARCHAR(255), IN `correoProveedor` VARCHAR(255), IN `telefonoProveedor` VARCHAR(255))   BEGIN
SET SQL_SAFE_UPDATES = 0;
UPDATE tablaProveedores set
	nombre = lower(nombreProveedor),
    pais = lower(paisProveedor)
where rfc = rfcProveedor;

UPDATE tablaDirectorio set
	correo = lower(correoProveedor),
    telefono = telefonoProveedor
where rfc = rfcProveedor;
SET SQL_SAFE_UPDATES = 1;
END$$

DROP PROCEDURE IF EXISTS `mostrarGastos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `mostrarGastos` ()   BEGIN
select*from tablaGastos;
END$$

DROP PROCEDURE IF EXISTS `mostrarProductos`$$
CREATE DEFINER=`root`@`localhost` PROCEDURE `mostrarProductos` ()   BEGIN
select 
	p.nombre,
	p.descripcion,
	p.precioVenta as precio,
	a.cantidad as disponibles 
From 
	tablaProductos p 
inner join 
	tablaalmacen a 
on 
	p.codigoBarras = a.codigoBarras;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablaalmacen`
--

DROP TABLE IF EXISTS `tablaalmacen`;
CREATE TABLE IF NOT EXISTS `tablaalmacen` (
  `codigoBarras` varchar(13) DEFAULT NULL,
  `cantidad` float DEFAULT NULL,
  `observacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablaalmacen`
--

TRUNCATE TABLE `tablaalmacen`;
--
-- Volcado de datos para la tabla `tablaalmacen`
--

INSERT DELAYED IGNORE INTO `tablaalmacen` (`codigoBarras`, `cantidad`, `observacion`) VALUES
('1234567890123', 12, 'La caja esta abierta');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablacatalogo`
--

DROP TABLE IF EXISTS `tablacatalogo`;
CREATE TABLE IF NOT EXISTS `tablacatalogo` (
  `rfc` varchar(12) DEFAULT NULL,
  `codigoBarras` varchar(13) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablacatalogo`
--

TRUNCATE TABLE `tablacatalogo`;
--
-- Volcado de datos para la tabla `tablacatalogo`
--

INSERT DELAYED IGNORE INTO `tablacatalogo` (`rfc`, `codigoBarras`) VALUES
('123456789012', '1234567890123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablaclientes`
--

DROP TABLE IF EXISTS `tablaclientes`;
CREATE TABLE IF NOT EXISTS `tablaclientes` (
  `rfcCliente` varchar(13) DEFAULT NULL,
  `cliente` varchar(255) DEFAULT NULL,
  `contactoCliente` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablaclientes`
--

TRUNCATE TABLE `tablaclientes`;
--
-- Volcado de datos para la tabla `tablaclientes`
--

INSERT DELAYED IGNORE INTO `tablaclientes` (`rfcCliente`, `cliente`, `contactoCliente`) VALUES
('MOQD020405G28', 'daniel', 'damoralesquiroz@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablacompras`
--

DROP TABLE IF EXISTS `tablacompras`;
CREATE TABLE IF NOT EXISTS `tablacompras` (
  `registro` datetime DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `rfc` varchar(12) DEFAULT NULL,
  `codigoBarras` varchar(13) DEFAULT NULL,
  `precioCompra` decimal(10,2) DEFAULT NULL,
  `cantidad` float DEFAULT NULL,
  `total` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablacompras`
--

TRUNCATE TABLE `tablacompras`;
--
-- Volcado de datos para la tabla `tablacompras`
--

INSERT DELAYED IGNORE INTO `tablacompras` (`registro`, `fecha`, `rfc`, `codigoBarras`, `precioCompra`, `cantidad`, `total`) VALUES
('2025-07-21 15:37:35', '2025-07-02', '123456789012', '1234567890123', 120.50, 12, 1446.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tabladirectorio`
--

DROP TABLE IF EXISTS `tabladirectorio`;
CREATE TABLE IF NOT EXISTS `tabladirectorio` (
  `rfc` varchar(12) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `telefono` varchar(255) DEFAULT NULL,
  KEY `rfc` (`rfc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tabladirectorio`
--

TRUNCATE TABLE `tabladirectorio`;
--
-- Volcado de datos para la tabla `tabladirectorio`
--

INSERT DELAYED IGNORE INTO `tabladirectorio` (`rfc`, `correo`, `telefono`) VALUES
('000000000006', 'correo@gmail.com', '00000001'),
('000000000007', 'correo@gmail.com', '00000001'),
('000000000008', 'correo@gmail.com', '00000001'),
('000000000009', 'correo@gmail.com', '00000001'),
('000000000001', 'correo@gmail.com', '00000001'),
('000000000003', 'correo@gmail.com', '00000001'),
('000000000002', 'megatools', 'megatools'),
('000000000010', 'correo@gmail.com', '00000001'),
('000000000011', 'correo@gmail.com', '00000001'),
('000000000004', 'correo@gmail.com', '00000001'),
('000000000012', 'correo2@gmail.com', '00000002'),
('000000000013', 'correo@gmail.com', '00000001'),
('123456789012', 'correo@gmail.com', '5616517951'),
('123456789013', 'correo@gmail.com', '5616517951'),
('000000000014', 'correo@gmail.com', '00000001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablagastos`
--

DROP TABLE IF EXISTS `tablagastos`;
CREATE TABLE IF NOT EXISTS `tablagastos` (
  `fechaCarga` datetime DEFAULT NULL,
  `fecha` date DEFAULT NULL,
  `concepto` varchar(255) DEFAULT NULL,
  `monto` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablagastos`
--

TRUNCATE TABLE `tablagastos`;
--
-- Volcado de datos para la tabla `tablagastos`
--

INSERT DELAYED IGNORE INTO `tablagastos` (`fechaCarga`, `fecha`, `concepto`, `monto`) VALUES
('2025-07-21 16:45:27', '2025-07-01', 'escritorio', 1500.90),
('2025-07-21 17:17:51', '2025-07-24', 'casa', 1300000.00),
('2025-07-21 21:15:40', '2025-07-24', 'papeleria', 500.00),
('2025-07-22 21:43:16', '0000-00-00', 'renta', 1500.00),
('2025-07-22 21:43:27', '0000-00-00', 'renta', 1500.00),
('2025-07-22 21:43:46', '2025-10-10', 'renta', 1500.00),
('2025-07-22 21:44:02', '0000-00-00', 'renta', 1500.00),
('2025-07-22 21:44:19', '0000-00-00', 'renta', 1500.00),
('2025-07-22 21:44:32', '0000-00-00', 'renta', 1500.00),
('2025-07-22 21:45:24', '2025-12-12', 'renta', 1500.00),
('2025-07-22 21:46:02', '2025-12-07', 'renta', 1500.00),
('2025-07-22 21:46:21', '2025-12-07', 'renta', 1500.00),
('2025-07-22 21:49:28', '2025-12-07', 'gas', 1500.00),
('2025-07-23 00:01:11', '2025-07-26', 'papeleria', 555.00),
('2025-07-23 00:03:08', '2025-07-26', 'casa', 99999999.99),
('2025-07-23 00:04:13', '2025-08-03', 'departamento', 555.90),
('2025-07-23 00:05:08', '2025-07-27', 'departamento', 1.00),
('2025-07-23 00:48:25', '2025-07-27', 'papeleria', 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablaproductos`
--

DROP TABLE IF EXISTS `tablaproductos`;
CREATE TABLE IF NOT EXISTS `tablaproductos` (
  `codigoBarras` varchar(13) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precioVenta` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablaproductos`
--

TRUNCATE TABLE `tablaproductos`;
--
-- Volcado de datos para la tabla `tablaproductos`
--

INSERT DELAYED IGNORE INTO `tablaproductos` (`codigoBarras`, `nombre`, `descripcion`, `precioVenta`) VALUES
('1234567890123', 'pinzas', 'pinzas de corte', 150.00),
('1234567890123', 'pinzas', 'pinzas de corte', 150.00),
('100000000000', 'producto', 'producto numero 1', 1500.00),
('100000000001', 'producto 2', 'producto numero 2', 1500.00),
('100000000002', 'producto 3', 'producto numero 3', 1500.00),
('100000000010', 'producto', 'producto numero 1', 1500.00),
('100000000010', 'producto 2', 'producto numero 2', 1500.00),
('100000000022', 'producto 3', 'producto numero 3', 1500.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablaproveedores`
--

DROP TABLE IF EXISTS `tablaproveedores`;
CREATE TABLE IF NOT EXISTS `tablaproveedores` (
  `rfc` varchar(12) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `pais` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`rfc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablaproveedores`
--

TRUNCATE TABLE `tablaproveedores`;
--
-- Volcado de datos para la tabla `tablaproveedores`
--

INSERT DELAYED IGNORE INTO `tablaproveedores` (`rfc`, `nombre`, `pais`) VALUES
('000000000001', 'xxxxxxxxxa', 'mexico'),
('000000000002', 'megatools', 'megatools'),
('000000000003', 'xxxxxxxxxa', 'mexico'),
('000000000004', 'xxxxxxxxxa', 'mexico'),
('000000000006', 'xxxxxxxxxa', 'mexico'),
('000000000007', 'xxxxxxxxxa', 'japon'),
('000000000008', 'xxxxxxxxxa', 'mexico'),
('000000000009', 'xxxxxxxxxa', 'mexico'),
('000000000010', 'xxxxxxxxxa', 'mexico'),
('000000000011', 'xxxxxxxxxa', 'mexico'),
('000000000012', 'xxxxxxxxxb', 'japon'),
('000000000013', 'xxxxxxxxxa', 'cuernavaca'),
('000000000014', 'xxxxxxxxxa', 'paris'),
('123456789012', 'megatools', 'pachuca'),
('123456789013', 'megatools', 'mexico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tablaventas`
--

DROP TABLE IF EXISTS `tablaventas`;
CREATE TABLE IF NOT EXISTS `tablaventas` (
  `fecha` datetime DEFAULT NULL,
  `rfcCliente` varchar(255) DEFAULT NULL,
  `codigoBarras` varchar(13) DEFAULT NULL,
  `cantidadComprada` float DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `tablaventas`
--

TRUNCATE TABLE `tablaventas`;
--
-- Volcado de datos para la tabla `tablaventas`
--

INSERT DELAYED IGNORE INTO `tablaventas` (`fecha`, `rfcCliente`, `codigoBarras`, `cantidadComprada`) VALUES
('2025-07-21 15:37:35', 'MOQD020405g28', '1234567890123', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE IF NOT EXISTS `usuarios` (
  `idusuario` int(15) NOT NULL,
  `primernombre` varchar(40) NOT NULL,
  `primerapellido` varchar(40) NOT NULL,
  `correo` varchar(40) NOT NULL,
  `telefono` varchar(16) NOT NULL,
  `lugarderesidencia` varchar(40) NOT NULL,
  `Password` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Truncar tablas antes de insertar `usuarios`
--

TRUNCATE TABLE `usuarios`;
--
-- Volcado de datos para la tabla `usuarios`
--

INSERT DELAYED IGNORE INTO `usuarios` (`idusuario`, `primernombre`, `primerapellido`, `correo`, `telefono`, `lugarderesidencia`, `Password`) VALUES
(33, 'Carlos', 'Perez', 'carlos@gmail.com', '5578913258', 'Estado de Mexicio', 'Negrito789#   '),
(36, 'Richard', 'Morales', 'ricahrd@gmail.com', '5578900123', 'Guadalajara', 'Es1821011884#'),
(52, 'Luis', 'Rodriguez', 'luisr@hotmail.com', '5567656987', 'Puebla', 'Progweb2#'),
(53, 'Alberto', 'Dominguez', 'dominguez7@hotmail.com', '5533578123', 'Campeche', 'Alberto25$'),
(54, 'Armando', 'Juarez', 'armando12@gmail.com', '27159871568', 'Queretaro', 'Ajuarez12#'),
(57, 'Francisco', 'Tellez', 'tellez58@hotmail.com', '5548791230', 'Toluca', 'Vargas789#'),
(58, 'Rodrigo', 'lara', 'laras@gmail.com', '558912345', 'Estado de Mexico', 'Mexico1020#');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tabladirectorio`
--
ALTER TABLE `tabladirectorio`
  ADD CONSTRAINT `tabladirectorio_ibfk_1` FOREIGN KEY (`rfc`) REFERENCES `tablaproveedores` (`rfc`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
