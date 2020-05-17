-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 17, 2020 at 10:54 PM
-- Server version: 10.4.11-MariaDB
-- PHP Version: 7.4.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `yomanejo`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `removePendingSchedules` ()  NO SQL
BEGIN

DELETE FROM clase
WHERE clase.status = 'NO CONFIRMADO' AND DATE(clase.fecha) >= CURRENT_DATE();

DELETE FROM cronograma
WHERE cronograma.status = 'NO CONFIRMADO' AND
str_to_date(cronograma.timestampGuardado, '%m/%d/%Y') <= CURRENT_DATE();

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `setFinishedSchedules` ()  NO SQL
UPDATE cronograma
INNER JOIN alumnocronogramaclasestomadas as act
ON act.idCronograma = cronograma.idCronograma AND act.cantClasesTomadas = act.cantClasesTotales
SET status = "FINALIZADO", timestampFinalizado = NOW()
WHERE cronograma.status != "FINALIZADO"$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `setInactiveStudents` ()  NO SQL
UPDATE alumno
INNER JOIN alumnocronogramaclasestomadas as act
ON act.idAlumno = alumno.idAlumno AND act.cantClasesTomadas = act.cantClasesTotales
INNER JOIN cronograma ON act.idCronograma = cronograma.idCronograma AND act.idAlumno = alumno.idAlumno
SET activo = 'false', motivoBaja = "Completó su cronograma", fechaBaja = DATE_FORMAT(NOW(), '%Y-%m-%d')$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sumDoneClasses` ()  NO SQL
BEGIN

UPDATE alumnocronogramaclasestomadas act
INNER JOIN clase ON clase.status = 'CONFIRMADO'
AND STR_TO_DATE(CONCAT(clase.fecha, ' ', clase.horaInicio, ':00'), '%Y-%m-%d%H:%i:%s') < NOW()
AND clase.sumada = 'false'
SET act.cantClasesTomadas = act.cantClasesTomadas + 1
WHERE clase.idCronograma = act.idCronograma;

UPDATE clase
SET clase.sumada = 'true'
WHERE clase.status = 'CONFIRMADO'
AND STR_TO_DATE(CONCAT(clase.fecha, ' ', clase.horaInicio, ':00'), '%Y-%m-%d%H:%i:%s') < NOW();

END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `alumno`
--

CREATE TABLE `alumno` (
  `idAlumno` int(11) NOT NULL,
  `idDireccion` int(11) NOT NULL,
  `idDireccionAlt` varchar(255) DEFAULT NULL,
  `fechaAlta` date NOT NULL,
  `activo` varchar(6) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `confirmado` varchar(5) NOT NULL,
  `fechaConfirmacion` date NOT NULL,
  `idDisponibilidad` int(11) NOT NULL,
  `idDireccionFisica` int(11) DEFAULT NULL,
  `documento` varchar(255) DEFAULT NULL,
  `motivoBaja` varchar(255) NOT NULL,
  `fechaBaja` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `alumnocronogramaclasestomadas`
--

CREATE TABLE `alumnocronogramaclasestomadas` (
  `idAlumnoCronograma` int(11) NOT NULL,
  `idAlumno` int(11) NOT NULL,
  `idCronograma` int(11) NOT NULL,
  `cantClasesTomadas` int(11) NOT NULL,
  `cantClasesTotales` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `auto`
--

CREATE TABLE `auto` (
  `idAuto` int(11) NOT NULL,
  `patente` varchar(8) NOT NULL,
  `color` varchar(30) NOT NULL,
  `disponibilidad` varchar(1) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `zonaMaster` int(11) NOT NULL,
  `modelo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auto`
--

INSERT INTO `auto` (`idAuto`, `patente`, `color`, `disponibilidad`, `descripcion`, `zonaMaster`, `modelo`) VALUES
(1, '456ABC', 'Rojo', 'A', '', 1, 'Corsa classic 1.6'),
(2, '123YTR', 'Naranja', 'A', '', 2, 'Nissan Kicks'),
(3, '124214', 'Naranja', 'T', '', 3, 'Nissan Kicks'),
(4, '1324asf', 'Gris', 'A', 'Auto automatico', 4, 'Nissan Kicks');

-- --------------------------------------------------------

--
-- Table structure for table `autoinactivo`
--

CREATE TABLE `autoinactivo` (
  `idAuto` int(11) NOT NULL,
  `fechaInicioinactividad` date NOT NULL,
  `fechaFininactividad` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clase`
--

CREATE TABLE `clase` (
  `idClase` int(11) NOT NULL,
  `alumno` int(11) NOT NULL,
  `auto` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `horaInicio` varchar(11) NOT NULL,
  `idZona` int(11) NOT NULL,
  `idDireccion` int(11) NOT NULL,
  `idCronograma` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `sumada` varchar(5) NOT NULL DEFAULT 'false',
  `nroClase` int(11) NOT NULL,
  `claseCancelada` varchar(5) NOT NULL,
  `motivoCancelacion` varchar(255) DEFAULT NULL,
  `fechaClaseCancelada` varchar(255) NOT NULL,
  `fechaClaseReactivada` varchar(255) NOT NULL,
  `claseExtra` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `clasemodificadaregistro`
--

CREATE TABLE `clasemodificadaregistro` (
  `idClaseModificadaRegistro` int(11) NOT NULL,
  `idClaseAnterior` int(11) NOT NULL,
  `idClaseNueva` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `cronograma`
--

CREATE TABLE `cronograma` (
  `idCronograma` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `idAlumno` int(11) NOT NULL,
  `timestampGuardado` varchar(255) NOT NULL,
  `timestampActivo` varchar(255) NOT NULL,
  `timestampCancelado` varchar(255) NOT NULL,
  `timestampFinalizado` varchar(255) NOT NULL,
  `timestampModificado` varchar(255) NOT NULL,
  `motivoBaja` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cronograma`
--

INSERT INTO `cronograma` (`idCronograma`, `status`, `idAlumno`, `timestampGuardado`, `timestampActivo`, `timestampCancelado`, `timestampFinalizado`, `timestampModificado`, `motivoBaja`) VALUES
(100, 'CANCELADO', 107, '02/26/2020 11:43:15 am', '', '2020-03-19 01:34:19 pm', '', '', 'cronogramaBAJA'),
(101, 'CANCELADO', 108, '02/26/2020 12:02:55 pm', '', '2020-03-20 12:42:51 am', '', '', 'Error en el alumno'),
(102, 'CONFIRMADO', 109, '02/26/2020 07:21:33 pm', '', '0000-00-00', '', '', ''),
(104, 'CANCELADO', 111, '02/26/2020 07:57:02 pm', '', '2020-03-15 08:14:37 pm', '', '', ''),
(105, 'CANCELADO', 112, '02/26/2020 08:25:07 pm', '', '0000-00-00', '', '', ''),
(106, 'CANCELADO', 113, '02/27/2020 03:35:28 pm', '', '2020-03-15', '', '', ''),
(108, 'CANCELADO', 115, '03/06/2020 10:54:39 am', '', '0000-00-00', '', '', ''),
(109, 'FINALIZADO', 116, '03/08/2020 12:05:36 am', '', '0000-00-00', '2020-05-03 19:06:13', '', ''),
(111, 'CANCELADO', 118, '03/08/2020 12:23:01 am', '', '0000-00-00', '', '', ''),
(112, 'FINALIZADO', 119, '03/08/2020 04:35:52 pm', '', '0000-00-00', '2020-03-18 18:27:36', '', ''),
(113, 'CANCELADO', 120, '03/09/2020 09:40:56 am', '', '0000-00-00', '', '', ''),
(114, 'FINALIZADO', 121, '03/09/2020 04:11:26 pm', '', '0000-00-00', '2020-03-18 18:27:36', '', ''),
(117, 'CANCELADO', 124, '03/15/2020 06:09:35 pm', '', '0000-00-00', '', '', ''),
(120, 'CONFIRMADO', 127, '03/30/2020 06:14:29 pm', '2020-04-01 09:08:57 pm', '', '', '', ''),
(125, 'CONFIRMADO', 132, '03/17/2020 11:35:52 pm', '03/19/2020 11:35:52 am', '', '', '', ''),
(127, 'FINALIZADO', 134, '03/20/2020 05:15:34 pm', '', '', '2020-04-06 23:27:44', '', ''),
(128, 'FINALIZADO', 135, '03/20/2020 06:26:01 pm', '2020-03-20 06:30:44 pm', '', '2020-03-25 16:15:02', '03/23/2020 03:13:56 pm', ''),
(129, 'FINALIZADO', 136, '03/23/2020 03:54:41 pm', '2020-03-23 03:54:49 pm', '', '2020-03-24 20:01:38', '', ''),
(130, 'FINALIZADO', 137, '03/24/2020 06:08:04 pm', '2020-03-24 06:08:14 pm', '', '2020-04-01 18:08:33', '', ''),
(131, 'FINALIZADO', 138, '03/24/2020 06:08:41 pm', '2020-03-24 06:08:49 pm', '', '2020-04-14 08:10:59', '03/28/2020 02:34:50 pm', ''),
(132, 'CONFIRMADO', 139, '03/25/2020 12:55:54 pm', '2020-03-25 12:56:08 pm', '', '', '04/01/2020 03:00:40 pm', ''),
(133, 'FINALIZADO', 140, '03/28/2020 03:05:42 pm', '2020-03-28 03:05:53 pm', '', '2020-05-03 19:06:13', '03/28/2020 04:44:46 pm', ''),
(134, 'CONFIRMADO', 141, '03/28/2020 04:36:08 pm', '2020-03-28 04:36:18 pm', '', '', '03/30/2020 02:35:11 pm', ''),
(135, 'FINALIZADO', 142, '03/30/2020 06:11:52 pm', '2020-03-30 06:12:06 pm', '', '2020-05-03 19:06:13', '04/01/2020 08:53:51 pm', ''),
(136, 'FINALIZADO', 143, '04/01/2020 09:18:39 pm', '2020-04-01 09:19:20 pm', '', '2020-04-22 18:13:51', '04/15/2020 12:26:06 pm', ''),
(137, 'FINALIZADO', 144, '04/01/2020 09:22:03 pm', '2020-04-01 09:22:20 pm', '', '2020-04-20 02:12:51', '', ''),
(158, 'CANCELADO', 165, '04/06/2020 01:12:48 pm', '2020-04-06 01:23:34 pm', '2020-04-15 12:25:12 pm', '', '04/13/2020 08:42:30 pm', 'El alumno dejo de pagar');

-- --------------------------------------------------------

--
-- Table structure for table `direccion`
--

CREATE TABLE `direccion` (
  `idDireccion` int(11) NOT NULL,
  `calle` varchar(100) NOT NULL,
  `calle_diag` varchar(5) NOT NULL,
  `calle_a` varchar(100) NOT NULL,
  `calle_a_diag` varchar(5) NOT NULL,
  `calle_b` varchar(100) NOT NULL,
  `calle_b_diag` varchar(5) NOT NULL,
  `numero` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `floor_` varchar(100) NOT NULL,
  `observaciones` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `disponibilidad`
--

CREATE TABLE `disponibilidad` (
  `idDisponibilidad` int(11) NOT NULL,
  `Monday` varchar(255) DEFAULT NULL,
  `Tuesday` varchar(255) DEFAULT NULL,
  `Wednesday` varchar(255) DEFAULT NULL,
  `Thursday` varchar(255) DEFAULT NULL,
  `Friday` varchar(255) DEFAULT NULL,
  `Saturday` varchar(255) DEFAULT NULL,
  `Sunday` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `excepcion`
--

CREATE TABLE `excepcion` (
  `idExcepcion` int(11) NOT NULL,
  `fecha` varchar(255) NOT NULL,
  `no_puede` varchar(255) NOT NULL,
  `idAlumno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `excepcionhorarios`
--

CREATE TABLE `excepcionhorarios` (
  `idExcepcionHorario` int(11) NOT NULL,
  `dir_alt` varchar(255) NOT NULL,
  `horarios` varchar(255) NOT NULL,
  `idExcepcion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `instructor`
--

CREATE TABLE `instructor` (
  `idInstructor` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `apellido` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `instructor`
--

INSERT INTO `instructor` (`idInstructor`, `nombre`, `apellido`) VALUES
(1, 'Gerardo', 'Romano');

-- --------------------------------------------------------

--
-- Table structure for table `parametros`
--

CREATE TABLE `parametros` (
  `idParametro` int(11) NOT NULL,
  `maximoDiasTolerancia` int(11) NOT NULL,
  `diasToleranciaBajo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parametros`
--

INSERT INTO `parametros` (`idParametro`, `maximoDiasTolerancia`, `diasToleranciaBajo`) VALUES
(1, 10, 3);

-- --------------------------------------------------------

--
-- Table structure for table `tokenusuario`
--

CREATE TABLE `tokenusuario` (
  `idTokenUsuario` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `refreshToken` varchar(255) NOT NULL,
  `expirationRefreshToken` varchar(255) NOT NULL,
  `loginTime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `tokenusuario`
--

INSERT INTO `tokenusuario` (`idTokenUsuario`, `idUsuario`, `refreshToken`, `expirationRefreshToken`, `loginTime`) VALUES
(119, 1, '15ea0b0da96b1e4.80578963', '1590181338', '1587589338');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `idUsuario` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstPasswordChange` varchar(5) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `role` varchar(255) NOT NULL,
  `changePasswordTime` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`idUsuario`, `email`, `password`, `firstPasswordChange`, `nombre`, `role`, `changePasswordTime`) VALUES
(1, 'sebastian_yomha@hotmail.com', '$2y$10$dJtypunXx91GTty70sPWBOIcO.FVxIee7w6fviJjEjSr930jKb3ZG', 'true', 'Sebastian Yomha', 'ADMIN', '2020-04-20 09:56:20 pm'),
(2, 'mauriangelucci@gmail.com', '$1$UbbARLMr$AWPC5xuEFSYtEuaQl/1uK0', 'false', 'Mauricio Angelucci', 'ADMIN', '');

-- --------------------------------------------------------

--
-- Table structure for table `usuariosuscripcion`
--

CREATE TABLE `usuariosuscripcion` (
  `idSuscripcion` int(11) NOT NULL,
  `idUsuario` int(11) NOT NULL,
  `endpoint` varchar(255) NOT NULL,
  `auth` varchar(255) NOT NULL,
  `p256dh` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `usuariosuscripcion`
--

INSERT INTO `usuariosuscripcion` (`idSuscripcion`, `idUsuario`, `endpoint`, `auth`, `p256dh`) VALUES
(19, 1, 'https://fcm.googleapis.com/fcm/send/dnmqoPBWmlg:APA91bEjqcfp1UIi4lujJTV898ZlZ7bXz-g6KYtzh4dUisYu2SjU5-XOWIhzI76bTIBBPsuAdCo_9wpNLE5eJxw1ljy0zqNj_yZ5zyWgc6jEKZ8AW8rXV65ST4KFOBm22y00s7omaqwk', 'q3CasGEHb4GXWThjzJE_iw', 'BJq3CfS0kY22F_AQFTfio2cVGxlJbX89CTmItV0EmuOLKLSVsa4d56vfItRdkGoC5DFOaSOmo0Foko7Ohik3wc4');

-- --------------------------------------------------------

--
-- Table structure for table `zona`
--

CREATE TABLE `zona` (
  `idZona` int(11) NOT NULL,
  `nombreZona` varchar(255) NOT NULL,
  `puntoSuperiorIzquierdo` varchar(255) NOT NULL,
  `puntoSuperiorDerecho` varchar(255) NOT NULL,
  `puntoInferiorIzquierdo` varchar(255) NOT NULL,
  `puntoInferiorDerecho` varchar(255) NOT NULL,
  `zonaMaster` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zona`
--

INSERT INTO `zona` (`idZona`, `nombreZona`, `puntoSuperiorIzquierdo`, `puntoSuperiorDerecho`, `puntoInferiorIzquierdo`, `puntoInferiorDerecho`, `zonaMaster`) VALUES
(6, 'tolosaBajo', '-34.880080,-57.971327', '-34.890289,-57.982442', '-34.899934,-57.969438', '-34.888212,-57.956049', 2),
(7, 'tolosaDesdeCalle7HastaCalle13', '-34.890470,-57.982642', '-34.896064,-57.988632', '-34.905683,-57.975496', '-34.900163,-57.969525', 2),
(8, 'tolosaCalle13HastaCalle19', '-34.896090,-57.988739', '-34.901699,-57.994586', '-34.911347,-57.981667', '-34.905902,-57.975784', 2),
(9, 'tolosaCalle19HastaCalle31', '-34.901856,-57.994910', '-34.913398,-58.007685', '-34.923636,-57.994477', '-34.911504,-57.981593', 2),
(10, 'zonaCalle126HastaCalle1', '-34.884810,-57.951583', '-34.895080,-57.962984', '-34.900096,-57.955907', '-34.887250,-57.942096', 2),
(11, 'zonaCalle1HastaCalle7', '-34.895018,-57.962996', '-34.900543,-57.968671', '-34.906136,-57.962880', '-34.900381,-57.956322', 2),
(12, 'zonaCalle7HastaCalle13', '-34.900604,-57.968670', '-34.906138,-57.974947', '-34.911181,-57.968414', '-34.906238,-57.962351', 2),
(13, 'zonaCalle13HastaCalle19', '-34.906364,-57.975304', '-34.911877,-57.981130', '-34.916962,-57.974496', '-34.911250,-57.968448', 2),
(14, 'zonaCalle19HastCalle31', '-34.911982,-57.981309', '-34.923511,-57.993609', '-34.928155,-57.986979', '-34.917547,-57.974909', 2),
(15, 'zonaCalle31HastaCalle19', '-34.928162,-57.986729', '-34.933193,-57.979905', '-34.917219,-57.974713', '-34.922638,-57.968147', 2),
(16, 'zonaCalle19HastCalle7', '-34.916913,-57.974618', '-34.905965,-57.962185', '-34.911055,-57.955725', '-34.921588,-57.967434', 2),
(17, 'zonaCalle7HastCalle1', '-34.905887,-57.961876', '-34.900113,-57.956019', '-34.911038,-57.955291', '-34.905140,-57.948918', 2),
(18, 'zonaCalle1HastaCalle127', '-34.900162,-57.955685', '-34.887618,-57.942212', '-34.905090,-57.949039', '-34.893418,-57.938176', 2),
(19, 'zonaCalle31HastaCalle25', '-34.933218,-57.979792', '-34.938078,-57.973225', '-34.931688,-57.968064', '-34.926978,-57.973002', 2),
(20, 'ZonaCalle25HastaCalle19', '-34.927136,-57.973386', '-34.922284,-57.967215', '-34.927584,-57.960197', '-34.931516,-57.967873', 2),
(21, 'ZonaCalle19HastaCalle13', '-34.921495,-57.967286', '-34.916515,-57.961290', '-34.922520,-57.954961', '-34.927416,-57.960065', 1),
(22, 'zonaCalle13HastaCalle7', '-34.915946,-57.961241', '-34.910945,-57.955354', '-34.916170,-57.947859', '-34.922756,-57.954678', 1),
(23, 'zonaCalle7HastaCalle1', '-34.910244,-57.954878', '-34.905124,-57.948780', '-34.910392,-57.941887', '-34.916340,-57.947841', 1),
(24, 'zonaCalle1Hasta126', '-34.905094,-57.949032', '-34.893522,-57.937989', '-34.897073,-57.929689', '-34.910623,-57.941415', 1),
(25, 'zonaCalle31HastaCalle25', '-34.938316,-57.973018', '-34.943347,-57.966753', '-34.937472,-57.960401', '-34.932969,-57.966538', 3),
(26, 'zonaCalle25HastaCalle19', '-34.932538,-57.966425', '-34.927438,-57.959749', '-34.931916,-57.953741', '-34.937762,-57.960235', 3),
(27, 'zonaCalle19HastCalle7', '-34.932015,-57.954044', '-34.921174,-57.942048', '-34.916323,-57.947717', '-34.927503,-57.959909', 1),
(28, 'zonaCalle7HastCalle1', '-34.915533,-57.947888', '-34.910443,-57.942475', '-34.920435,-57.941290', '-34.914995,-57.935396', 1),
(29, 'zonaCalle31HastaCalle19', '-34.943478,-57.966093', '-34.948469,-57.959206', '-34.937286,-57.947006', '-34.932098,-57.954049', 3),
(30, 'zonaCalle19HastaCalle13', '-34.932224,-57.954049', '-34.926408,-57.947720', '-34.931973,-57.940626', '-34.937203,-57.947057', 3),
(31, 'zonaCalle13HastaCalle7', '-34.931052,-57.940473', '-34.926324,-57.947669', '-34.920801,-57.941800', '-34.925906,-57.934553', 3),
(32, 'zonaCalle7HastaCalle1', '-34.925864,-57.934859', '-34.920717,-57.941443', '-34.915151,-57.935523', '-34.920340,-57.928378', 3),
(33, 'zonaCalle1HastaCalle126', '-34.920340,-57.928378', '-34.915151,-57.935574', '-34.903169,-57.923676', '-34.909445,-57.914305', 3),
(34, 'zonaCalle31HastaCalle19', '-34.948975,-57.960006', '-34.953927,-57.953019', '-34.942473,-57.940044', '-34.937263,-57.947031', 3),
(35, 'zonaCalle19HastaCalle13', '-34.937177,-57.946978', '-34.931535,-57.940832', '-34.936574,-57.933793', '-34.942344,-57.940044', 3),
(36, 'zonaCalle13HastaCalle7', '-34.931707,-57.941042', '-34.926023,-57.934739', '-34.931363,-57.927752', '-34.936875,-57.934003', 3),
(37, 'zonaCalle7HastaCalle1', '-34.931320,-57.927647', '-34.925894,-57.934604', '-34.920421,-57.928717', '-34.925894,-57.921312', 3),
(38, 'zonaCalle1HastaCalle126', '-34.925944,-57.921737', '-34.920620,-57.929142', '-34.910243,-57.914333', '-34.912466,-57.908457', 3),
(39, 'zonaCalle31HastaCalle19', '-34.953975,-57.952483', '-34.942613,-57.939908', '-34.948453,-57.932098', '-34.959533,-57.944286', 3),
(40, 'zonaCalle19HastaCalle7', '-34.948558,-57.932312', '-34.942648,-57.939737', '-34.931531,-57.927720', '-34.937442,-57.919695', 3),
(41, 'zonaCalle7HastaCalle1', '-34.937442,-57.919695', '-34.931531,-57.927635', '-34.925972,-57.921197', '-34.931637,-57.913473', 3),
(42, 'zonaCalle1HastaCalle126', '-34.931637,-57.913473', '-34.925937,-57.921369', '-34.912219,-57.908733', '-34.918428,-57.901268', 3);

-- --------------------------------------------------------

--
-- Table structure for table `zonamaster`
--

CREATE TABLE `zonamaster` (
  `idZonaMaster` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `zonamaster`
--

INSERT INTO `zonamaster` (`idZonaMaster`) VALUES
(1),
(2),
(3);

-- --------------------------------------------------------

--
-- Table structure for table `zonasvecinas`
--

CREATE TABLE `zonasvecinas` (
  `idZona` int(11) NOT NULL,
  `idZonaVecina` int(11) NOT NULL,
  `id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zonasvecinas`
--

INSERT INTO `zonasvecinas` (`idZona`, `idZonaVecina`, `id`) VALUES
(6, 7, 1),
(6, 10, 2),
(7, 8, 3),
(7, 13, 4),
(8, 7, 5),
(7, 12, 6),
(8, 9, 7),
(6, 11, 8),
(7, 6, 9),
(8, 13, 10),
(9, 8, 11),
(9, 14, 12),
(10, 6, 13),
(10, 11, 14),
(10, 17, 15),
(10, 18, 16),
(11, 6, 17),
(11, 10, 18),
(11, 12, 19),
(11, 17, 20),
(12, 7, 21),
(12, 11, 22),
(12, 13, 23),
(12, 16, 24),
(13, 7, 25),
(13, 8, 26),
(13, 12, 27),
(13, 16, 28),
(14, 9, 29),
(14, 13, 30),
(14, 15, 31),
(15, 14, 32),
(15, 16, 33),
(15, 19, 34),
(15, 20, 35),
(16, 13, 36),
(16, 12, 37),
(16, 15, 38),
(16, 17, 39),
(16, 22, 40),
(16, 21, 41),
(17, 16, 42),
(17, 11, 43),
(17, 10, 44),
(17, 18, 45),
(17, 23, 46),
(18, 17, 47),
(18, 10, 48),
(18, 24, 49),
(19, 15, 50),
(19, 20, 51),
(19, 25, 52),
(20, 19, 53),
(20, 15, 54),
(20, 21, 55),
(20, 26, 56),
(21, 16, 57),
(21, 20, 58),
(21, 22, 59),
(21, 27, 60),
(22, 16, 61),
(22, 21, 62),
(22, 23, 63),
(22, 27, 64),
(23, 17, 65),
(23, 22, 66),
(23, 24, 67),
(23, 28, 68),
(24, 18, 69),
(24, 23, 70),
(0, 0, 71),
(25, 19, 72),
(25, 26, 73),
(25, 29, 74),
(26, 20, 75),
(26, 25, 76),
(26, 27, 77),
(26, 29, 78),
(27, 21, 79),
(27, 22, 80),
(27, 26, 81),
(27, 28, 82),
(27, 30, 83),
(27, 31, 84),
(28, 23, 85),
(28, 27, 86),
(28, 32, 87),
(29, 25, 88),
(29, 26, 89),
(29, 30, 90),
(29, 34, 91),
(30, 27, 92),
(30, 29, 93),
(30, 31, 94),
(30, 35, 95),
(31, 27, 96),
(31, 30, 97),
(31, 32, 98),
(31, 36, 99),
(32, 28, 100),
(32, 31, 101),
(32, 33, 102),
(32, 37, 103),
(33, 32, 104),
(33, 34, 105),
(33, 38, 106),
(34, 29, 107),
(34, 33, 108),
(34, 35, 109),
(34, 39, 110),
(35, 30, 111),
(35, 34, 112),
(35, 36, 113),
(35, 40, 114),
(36, 31, 115),
(36, 35, 116),
(36, 37, 117),
(36, 40, 118),
(37, 32, 119),
(37, 36, 120),
(37, 38, 121),
(37, 41, 122),
(38, 33, 123),
(38, 37, 124),
(38, 42, 125),
(39, 34, 126),
(39, 40, 127),
(40, 35, 128),
(40, 36, 129),
(40, 39, 130),
(40, 41, 131),
(40, 36, 132),
(41, 37, 133),
(41, 40, 134),
(41, 42, 135),
(42, 38, 136),
(42, 41, 137);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumno`
--
ALTER TABLE `alumno`
  ADD PRIMARY KEY (`idAlumno`);

--
-- Indexes for table `alumnocronogramaclasestomadas`
--
ALTER TABLE `alumnocronogramaclasestomadas`
  ADD PRIMARY KEY (`idAlumnoCronograma`);

--
-- Indexes for table `auto`
--
ALTER TABLE `auto`
  ADD PRIMARY KEY (`idAuto`);

--
-- Indexes for table `clase`
--
ALTER TABLE `clase`
  ADD PRIMARY KEY (`idClase`);

--
-- Indexes for table `clasemodificadaregistro`
--
ALTER TABLE `clasemodificadaregistro`
  ADD PRIMARY KEY (`idClaseModificadaRegistro`);

--
-- Indexes for table `cronograma`
--
ALTER TABLE `cronograma`
  ADD PRIMARY KEY (`idCronograma`);

--
-- Indexes for table `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`idDireccion`);

--
-- Indexes for table `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD PRIMARY KEY (`idDisponibilidad`);

--
-- Indexes for table `excepcion`
--
ALTER TABLE `excepcion`
  ADD PRIMARY KEY (`idExcepcion`);

--
-- Indexes for table `excepcionhorarios`
--
ALTER TABLE `excepcionhorarios`
  ADD PRIMARY KEY (`idExcepcionHorario`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`idInstructor`);

--
-- Indexes for table `parametros`
--
ALTER TABLE `parametros`
  ADD PRIMARY KEY (`idParametro`);

--
-- Indexes for table `tokenusuario`
--
ALTER TABLE `tokenusuario`
  ADD PRIMARY KEY (`idTokenUsuario`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`idUsuario`);

--
-- Indexes for table `usuariosuscripcion`
--
ALTER TABLE `usuariosuscripcion`
  ADD PRIMARY KEY (`idSuscripcion`);

--
-- Indexes for table `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`idZona`);

--
-- Indexes for table `zonamaster`
--
ALTER TABLE `zonamaster`
  ADD PRIMARY KEY (`idZonaMaster`);

--
-- Indexes for table `zonasvecinas`
--
ALTER TABLE `zonasvecinas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumno`
--
ALTER TABLE `alumno`
  MODIFY `idAlumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- AUTO_INCREMENT for table `alumnocronogramaclasestomadas`
--
ALTER TABLE `alumnocronogramaclasestomadas`
  MODIFY `idAlumnoCronograma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `auto`
--
ALTER TABLE `auto`
  MODIFY `idAuto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1214218;

--
-- AUTO_INCREMENT for table `clase`
--
ALTER TABLE `clase`
  MODIFY `idClase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=884;

--
-- AUTO_INCREMENT for table `clasemodificadaregistro`
--
ALTER TABLE `clasemodificadaregistro`
  MODIFY `idClaseModificadaRegistro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `cronograma`
--
ALTER TABLE `cronograma`
  MODIFY `idCronograma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=160;

--
-- AUTO_INCREMENT for table `direccion`
--
ALTER TABLE `direccion`
  MODIFY `idDireccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=236;

--
-- AUTO_INCREMENT for table `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `idDisponibilidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=177;

--
-- AUTO_INCREMENT for table `excepcion`
--
ALTER TABLE `excepcion`
  MODIFY `idExcepcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `excepcionhorarios`
--
ALTER TABLE `excepcionhorarios`
  MODIFY `idExcepcionHorario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `idInstructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `parametros`
--
ALTER TABLE `parametros`
  MODIFY `idParametro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tokenusuario`
--
ALTER TABLE `tokenusuario`
  MODIFY `idTokenUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=120;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `idUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `usuariosuscripcion`
--
ALTER TABLE `usuariosuscripcion`
  MODIFY `idSuscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `zona`
--
ALTER TABLE `zona`
  MODIFY `idZona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT for table `zonamaster`
--
ALTER TABLE `zonamaster`
  MODIFY `idZonaMaster` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zonasvecinas`
--
ALTER TABLE `zonasvecinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=138;

DELIMITER $$
--
-- Events
--
CREATE DEFINER=`root`@`localhost` EVENT `setInactiveStudentsSchedule` ON SCHEDULE EVERY 1 SECOND STARTS '2020-03-13 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL setInactiveStudents()$$

CREATE DEFINER=`root`@`localhost` EVENT `sumDoneClassesSchedule` ON SCHEDULE EVERY 1 SECOND STARTS '2020-03-13 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL sumDoneClasses()$$

CREATE DEFINER=`root`@`localhost` EVENT `setFinishedSchedules` ON SCHEDULE EVERY 1 SECOND STARTS '2020-03-17 00:00:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL setFinishedSchedules()$$

CREATE DEFINER=`root`@`localhost` EVENT `removePendingSchedules` ON SCHEDULE EVERY 24 HOUR STARTS '2020-04-05 23:55:00' ON COMPLETION NOT PRESERVE ENABLE DO CALL removePendingSchedules()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
