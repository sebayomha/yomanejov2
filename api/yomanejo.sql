-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 18, 2020 at 03:21 AM
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
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(255) NOT NULL,
  `confirmado` varchar(5) NOT NULL,
  `fechaConfirmacion` date NOT NULL,
  `idDisponibilidad` int(11) NOT NULL,
  `idDireccionFisica` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `alumno`
--

INSERT INTO `alumno` (`idAlumno`, `idDireccion`, `idDireccionAlt`, `fechaAlta`, `activo`, `nombre`, `fecha_nacimiento`, `telefono`, `confirmado`, `fechaConfirmacion`, `idDisponibilidad`, `idDireccionFisica`) VALUES
(1, 1, '', '2020-01-09', 'true', 'Sebastian', '0000-00-00', '0', 'true', '0000-00-00', 0, 0),
(2, 1, '', '2020-01-09', 'true', 'Matias', '0000-00-00', '0', 'true', '0000-00-00', 0, 0),
(3, 25, '26', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2147483647', 'false', '0000-00-00', 10, 0),
(4, 27, '28', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2147483647', 'false', '0000-00-00', 11, 0),
(5, 29, '30', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 12, 0),
(6, 34, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 16, 0),
(7, 35, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 17, 0),
(8, 36, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 18, 0),
(9, 37, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 19, 0),
(10, 38, '39', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 20, 0),
(11, 40, '41', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 21, 0),
(12, 42, '43', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 22, 0),
(13, 44, '45', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 23, 0),
(14, 46, '47', '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 24, 0),
(15, 48, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 25, 0),
(16, 49, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 26, 0),
(17, 50, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 27, 0),
(18, 51, NULL, '2020-02-17', 'false', 'Sebastian Yomha', '0000-00-00', '2216754337', 'false', '0000-00-00', 28, 0);

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
  `zonaMaster` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auto`
--

INSERT INTO `auto` (`idAuto`, `patente`, `color`, `disponibilidad`, `descripcion`, `zonaMaster`) VALUES
(1, '456ABC', 'Rojo', 'A', '', 1),
(2, '123YTR', 'Naranja', 'A', '', 2),
(3, '124214', 'Naranja', 'T', '', 3),
(4, '1324asf', 'Gris', 'A', 'Auto automatico', 4);

-- --------------------------------------------------------

--
-- Table structure for table `autoinactivo`
--

CREATE TABLE `autoinactivo` (
  `idAuto` int(11) NOT NULL,
  `fechaInicioinactividad` date NOT NULL,
  `fechaFininactividad` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `autoinactivo`
--

INSERT INTO `autoinactivo` (`idAuto`, `fechaInicioinactividad`, `fechaFininactividad`) VALUES
(1, '2020-01-08', '2020-01-08');

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
  `status` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clase`
--

INSERT INTO `clase` (`idClase`, `alumno`, `auto`, `fecha`, `horaInicio`, `idZona`, `idDireccion`, `idCronograma`, `status`) VALUES
(6, 1, 1, '2020-02-06', '08:00', 34, 1, 0, 'CONFIRMADO'),
(7, 1, 1, '2020-02-06', '09:00', 31, 1, 0, 'CONFIRMADO'),
(8, 1, 1, '2020-02-06', '10:00', 29, 1, 0, 'CONFIRMADO'),
(9, 1, 1, '2020-02-06', '11:15', 32, 1, 0, 'CONFIRMADO'),
(10, 1, 1, '2020-02-06', '12:15', 32, 1, 0, 'CONFIRMADO'),
(11, 1, 1, '2020-02-06', '13:15', 22, 1, 0, 'CONFIRMADO'),
(12, 1, 1, '2020-02-06', '14:30', 41, 1, 0, 'CONFIRMADO'),
(13, 1, 1, '2020-02-06', '15:30', 33, 1, 0, 'CONFIRMADO'),
(14, 1, 1, '2020-02-06', '16:30', 40, 1, 0, 'CONFIRMADO'),
(15, 1, 1, '2020-02-06', '17:45', 32, 1, 0, 'CONFIRMADO'),
(16, 1, 1, '2020-02-06', '18:45', 31, 1, 0, 'CONFIRMADO'),
(17, 1, 2, '2020-02-06', '08:00', 17, 1, 0, 'CONFIRMADO'),
(18, 1, 2, '2020-02-06', '09:00', 6, 1, 0, 'CONFIRMADO'),
(19, 1, 2, '2020-02-06', '10:00', 9, 1, 0, 'CONFIRMADO'),
(20, 1, 2, '2020-02-06', '11:15', 20, 1, 0, 'CONFIRMADO'),
(21, 1, 2, '2020-02-06', '12:15', 27, 1, 0, 'CONFIRMADO'),
(22, 1, 1, '2020-02-08', '10:00', 31, 1, 0, 'CONFIRMADO'),
(23, 1, 1, '2020-02-08', '08:00', 32, 1, 0, 'CONFIRMADO'),
(24, 1, 1, '2020-02-08', '09:00', 31, 1, 0, 'CONFIRMADO'),
(25, 1, 1, '2020-02-08', '11:15', 32, 1, 0, 'CONFIRMADO'),
(26, 1, 1, '2020-02-08', '12:15', 32, 1, 0, 'CONFIRMADO'),
(27, 1, 1, '2020-02-08', '13:15', 36, 1, 0, 'CONFIRMADO'),
(28, 1, 1, '2020-02-08', '14:30', 40, 1, 0, 'CONFIRMADO'),
(29, 1, 1, '2020-02-08', '15:30', 42, 1, 0, 'CONFIRMADO'),
(30, 1, 1, '2020-02-08', '16:30', 32, 1, 0, 'CONFIRMADO'),
(31, 1, 1, '2020-02-08', '17:45', 32, 1, 0, 'CONFIRMADO'),
(32, 0, 0, '0000-00-00', '', 0, 0, 0, 'CONFIRMADO'),
(33, 1, 2, '2020-02-08', '09:00', 10, 1, 0, 'CONFIRMADO'),
(34, 1, 2, '2020-02-08', '10:00', 16, 1, 0, 'CONFIRMADO'),
(35, 1, 2, '2020-02-08', '11:15', 16, 1, 0, 'CONFIRMADO'),
(36, 1, 2, '2020-02-08', '12:15', 20, 1, 0, 'CONFIRMADO'),
(37, 1, 2, '2020-02-08', '13:15', 15, 1, 0, 'CONFIRMADO'),
(38, 1, 2, '2020-02-08', '14:30', 6, 1, 0, 'CONFIRMADO'),
(39, 1, 2, '2020-02-08', '15:30', 6, 1, 0, 'CONFIRMADO'),
(40, 1, 2, '2020-02-08', '16:30', 16, 1, 0, 'CONFIRMADO'),
(41, 1, 2, '2020-02-08', '17:45', 15, 1, 0, 'CONFIRMADO'),
(42, 1, 2, '2020-02-08', '18:45', 16, 1, 0, 'CONFIRMADO'),
(43, 1, 3, '2020-02-08', '15:30', 42, 1, 0, 'CONFIRMADO'),
(44, 1, 3, '2020-02-08', '16:30', 41, 1, 0, 'CONFIRMADO'),
(45, 1, 3, '2020-02-08', '17:45', 32, 1, 0, 'CONFIRMADO'),
(46, 1, 3, '2020-02-08', '14:30', 34, 1, 0, 'CONFIRMADO'),
(47, 1, 1, '2020-02-09', '09:00', 23, 1, 0, 'CONFIRMADO'),
(48, 1, 1, '2020-02-09', '10:00', 16, 1, 0, 'CONFIRMADO'),
(49, 1, 1, '2020-02-09', '11:15', 10, 1, 0, 'CONFIRMADO'),
(50, 1, 1, '2020-02-09', '14:30', 19, 1, 0, 'CONFIRMADO'),
(51, 1, 1, '2020-02-09', '15:30', 15, 1, 0, 'CONFIRMADO'),
(52, 1, 1, '2020-02-09', '13:15', 29, 1, 0, 'CONFIRMADO'),
(53, 1, 1, '2020-02-10', '08:00', 34, 1, 0, 'CONFIRMADO'),
(54, 1, 1, '2020-02-10', '09:00', 31, 1, 0, 'CONFIRMADO'),
(55, 1, 1, '2020-02-10', '10:00', 32, 1, 0, 'CONFIRMADO'),
(56, 1, 1, '2020-02-10', '11:15', 37, 1, 0, 'CONFIRMADO'),
(57, 1, 1, '2020-02-10', '12:15', 32, 1, 0, 'CONFIRMADO'),
(58, 1, 1, '2020-02-10', '13:15', 33, 1, 0, 'CONFIRMADO'),
(59, 1, 1, '2020-02-10', '14:30', 36, 1, 0, 'CONFIRMADO'),
(60, 1, 1, '2020-02-10', '15:30', 32, 1, 0, 'CONFIRMADO'),
(61, 1, 1, '2020-02-10', '16:30', 32, 1, 0, 'CONFIRMADO'),
(62, 1, 1, '2020-02-10', '17:45', 31, 1, 0, 'CONFIRMADO'),
(63, 1, 1, '2020-02-10', '18:45', 32, 1, 0, 'CONFIRMADO'),
(64, 1, 2, '2020-02-10', '08:00', 16, 1, 0, 'CONFIRMADO'),
(65, 1, 2, '2020-02-10', '09:00', 6, 1, 0, 'CONFIRMADO'),
(66, 1, 2, '2020-02-10', '10:00', 21, 1, 0, 'CONFIRMADO'),
(67, 1, 2, '2020-02-10', '11:15', 6, 1, 0, 'CONFIRMADO'),
(68, 1, 2, '2020-02-10', '12:15', 10, 1, 0, 'CONFIRMADO'),
(69, 1, 2, '2020-02-10', '13:15', 24, 1, 0, 'CONFIRMADO'),
(70, 1, 2, '2020-02-10', '14:30', 15, 1, 0, 'CONFIRMADO'),
(71, 1, 2, '2020-02-10', '15:30', 15, 1, 0, 'CONFIRMADO'),
(72, 1, 2, '2020-02-10', '16:30', 15, 1, 0, 'CONFIRMADO'),
(73, 1, 2, '2020-02-10', '18:45', 15, 1, 0, 'CONFIRMADO'),
(74, 1, 3, '2020-02-10', '08:00', 38, 1, 0, 'CONFIRMADO'),
(75, 1, 3, '2020-02-10', '09:00', 38, 1, 0, 'CONFIRMADO'),
(76, 1, 3, '2020-02-10', '10:00', 28, 1, 0, 'CONFIRMADO'),
(77, 1, 3, '2020-02-10', '12:15', 38, 1, 0, 'CONFIRMADO'),
(78, 1, 3, '2020-02-10', '13:15', 37, 1, 0, 'CONFIRMADO'),
(79, 1, 3, '2020-02-10', '14:30', 32, 1, 0, 'CONFIRMADO'),
(80, 1, 3, '2020-02-10', '15:30', 28, 1, 0, 'CONFIRMADO'),
(81, 1, 3, '2020-02-10', '16:30', 23, 1, 0, 'CONFIRMADO'),
(82, 1, 3, '2020-02-10', '18:45', 16, 1, 0, 'CONFIRMADO'),
(83, 1, 1, '2020-02-11', '08:00', 34, 1, 0, 'CONFIRMADO'),
(84, 1, 1, '2020-02-11', '09:00', 32, 1, 0, 'CONFIRMADO'),
(85, 1, 1, '2020-02-11', '10:00', 32, 1, 0, 'CONFIRMADO'),
(86, 1, 1, '2020-02-11', '11:15', 32, 1, 0, 'CONFIRMADO'),
(87, 1, 1, '2020-02-11', '12:15', 31, 1, 0, 'CONFIRMADO'),
(88, 1, 1, '2020-02-11', '13:15', 42, 1, 0, 'CONFIRMADO'),
(89, 1, 1, '2020-02-11', '14:30', 33, 1, 0, 'CONFIRMADO'),
(90, 1, 1, '2020-02-11', '15:30', 41, 1, 0, 'CONFIRMADO'),
(91, 1, 1, '2020-02-11', '16:30', 32, 1, 0, 'CONFIRMADO'),
(92, 1, 1, '2020-02-11', '17:45', 28, 1, 0, 'CONFIRMADO'),
(93, 1, 1, '2020-02-11', '18:45', 41, 1, 0, 'CONFIRMADO'),
(94, 1, 2, '2020-02-11', '08:00', 6, 1, 0, 'CONFIRMADO'),
(95, 1, 2, '2020-02-11', '10:00', 26, 1, 0, 'CONFIRMADO'),
(96, 1, 2, '2020-02-11', '11:15', 18, 1, 0, 'CONFIRMADO'),
(97, 1, 2, '2020-02-11', '12:15', 10, 1, 0, 'CONFIRMADO'),
(98, 1, 2, '2020-02-11', '13:15', 16, 1, 0, 'CONFIRMADO'),
(99, 1, 2, '2020-02-11', '14:30', 15, 1, 0, 'CONFIRMADO'),
(100, 1, 2, '2020-02-11', '15:30', 15, 1, 0, 'CONFIRMADO'),
(101, 1, 2, '2020-02-11', '16:30', 15, 1, 0, 'CONFIRMADO'),
(102, 1, 2, '2020-02-11', '17:45', 29, 1, 0, 'CONFIRMADO'),
(103, 1, 2, '2020-02-11', '18:45', 19, 1, 0, 'CONFIRMADO'),
(104, 1, 3, '2020-02-11', '13:15', 23, 1, 0, 'CONFIRMADO'),
(105, 1, 3, '2020-02-11', '14:30', 23, 1, 0, 'CONFIRMADO'),
(106, 1, 3, '2020-02-11', '16:30', 23, 1, 0, 'CONFIRMADO'),
(107, 1, 3, '2020-02-11', '17:45', 32, 1, 0, 'CONFIRMADO'),
(108, 1, 3, '2020-02-11', '18:45', 40, 1, 0, 'CONFIRMADO'),
(109, 1, 1, '2020-02-12', '08:00', 38, 1, 0, 'CONFIRMADO'),
(110, 1, 1, '2020-02-12', '09:00', 32, 1, 0, 'CONFIRMADO'),
(111, 1, 1, '2020-02-12', '10:00', 31, 1, 0, 'CONFIRMADO'),
(112, 1, 1, '2020-02-12', '11:15', 40, 1, 0, 'CONFIRMADO'),
(113, 1, 1, '2020-02-12', '12:15', 31, 1, 0, 'CONFIRMADO'),
(114, 1, 1, '2020-02-12', '13:15', 37, 1, 0, 'CONFIRMADO'),
(115, 1, 1, '2020-02-12', '14:30', 36, 1, 0, 'CONFIRMADO'),
(116, 1, 1, '2020-02-12', '15:30', 32, 1, 0, 'CONFIRMADO'),
(117, 1, 1, '2020-02-12', '17:45', 31, 1, 0, 'CONFIRMADO'),
(118, 1, 1, '2020-02-12', '18:45', 41, 1, 0, 'CONFIRMADO'),
(119, 1, 2, '2020-02-12', '08:00', 40, 1, 0, 'CONFIRMADO'),
(120, 1, 2, '2020-02-12', '11:15', 20, 1, 0, 'CONFIRMADO'),
(121, 1, 2, '2020-02-12', '12:15', 16, 1, 0, 'CONFIRMADO'),
(122, 1, 2, '2020-02-12', '13:15', 7, 1, 0, 'CONFIRMADO'),
(123, 1, 2, '2020-02-12', '14:30', 17, 1, 0, 'CONFIRMADO'),
(124, 1, 2, '2020-02-12', '15:30', 28, 1, 0, 'CONFIRMADO'),
(125, 1, 2, '2020-02-12', '16:30', 10, 1, 0, 'CONFIRMADO'),
(126, 1, 2, '2020-02-12', '17:45', 6, 1, 0, 'CONFIRMADO'),
(127, 1, 2, '2020-02-12', '18:45', 9, 1, 0, 'CONFIRMADO'),
(128, 1, 3, '2020-02-12', '08:00', 25, 1, 0, 'CONFIRMADO'),
(129, 1, 3, '2020-02-12', '09:00', 40, 1, 0, 'CONFIRMADO'),
(130, 1, 3, '2020-02-12', '10:00', 28, 1, 0, 'CONFIRMADO'),
(131, 1, 3, '2020-02-12', '12:15', 23, 1, 0, 'CONFIRMADO'),
(132, 1, 3, '2020-02-12', '13:15', 22, 1, 0, 'CONFIRMADO'),
(133, 1, 3, '2020-02-12', '14:30', 32, 1, 0, 'CONFIRMADO'),
(134, 1, 3, '2020-02-12', '16:30', 19, 1, 0, 'CONFIRMADO'),
(135, 1, 3, '2020-02-12', '17:45', 29, 1, 0, 'CONFIRMADO'),
(136, 1, 3, '2020-02-12', '18:45', 19, 1, 0, 'CONFIRMADO'),
(137, 1, 1, '2020-02-13', '09:00', 32, 1, 0, 'CONFIRMADO'),
(138, 1, 1, '2020-02-13', '10:00', 32, 1, 0, 'CONFIRMADO'),
(139, 1, 1, '2020-02-13', '11:15', 32, 1, 0, 'CONFIRMADO'),
(140, 1, 1, '2020-02-13', '12:15', 41, 1, 0, 'CONFIRMADO'),
(141, 1, 1, '2020-02-13', '13:15', 42, 1, 0, 'CONFIRMADO'),
(142, 1, 1, '2020-02-13', '14:30', 33, 1, 0, 'CONFIRMADO'),
(143, 1, 1, '2020-02-13', '16:30', 32, 1, 0, 'CONFIRMADO'),
(144, 1, 1, '2020-02-13', '17:45', 32, 1, 0, 'CONFIRMADO'),
(145, 1, 1, '2020-02-13', '18:45', 41, 1, 0, 'CONFIRMADO'),
(146, 1, 2, '2020-02-13', '08:00', 6, 1, 0, 'CONFIRMADO'),
(147, 1, 2, '2020-02-13', '09:00', 10, 1, 0, 'CONFIRMADO'),
(148, 1, 2, '2020-02-13', '12:15', 19, 1, 0, 'CONFIRMADO'),
(149, 1, 2, '2020-02-13', '13:15', 11, 1, 0, 'CONFIRMADO'),
(150, 1, 2, '2020-02-13', '14:30', 24, 1, 0, 'CONFIRMADO'),
(151, 1, 2, '2020-02-13', '16:30', 15, 1, 0, 'CONFIRMADO'),
(152, 1, 2, '2020-02-13', '17:45', 15, 1, 0, 'CONFIRMADO'),
(153, 1, 2, '2020-02-13', '18:45', 15, 1, 0, 'CONFIRMADO'),
(154, 1, 3, '2020-02-13', '13:15', 23, 1, 0, 'CONFIRMADO'),
(155, 1, 3, '2020-02-13', '14:30', 23, 1, 0, 'CONFIRMADO'),
(156, 1, 3, '2020-02-13', '16:30', 31, 1, 0, 'CONFIRMADO'),
(157, 1, 3, '2020-02-13', '17:45', 32, 1, 0, 'CONFIRMADO'),
(158, 1, 3, '2020-02-13', '18:45', 31, 1, 0, 'CONFIRMADO'),
(159, 1, 1, '2020-02-14', '08:00', 33, 1, 0, 'CONFIRMADO'),
(160, 1, 1, '2020-02-14', '09:00', 40, 1, 0, 'CONFIRMADO'),
(161, 1, 1, '2020-02-14', '10:00', 32, 1, 0, 'CONFIRMADO'),
(162, 1, 1, '2020-02-14', '11:15', 41, 1, 0, 'CONFIRMADO'),
(163, 1, 1, '2020-02-14', '13:15', 33, 1, 0, 'CONFIRMADO'),
(164, 1, 1, '2020-02-14', '14:30', 32, 1, 0, 'CONFIRMADO'),
(165, 1, 1, '2020-02-14', '15:30', 32, 1, 0, 'CONFIRMADO'),
(166, 1, 1, '2020-02-14', '16:30', 32, 1, 0, 'CONFIRMADO'),
(167, 1, 1, '2020-02-14', '17:45', 31, 1, 0, 'CONFIRMADO'),
(168, 1, 1, '2020-02-14', '18:45', 32, 1, 0, 'CONFIRMADO'),
(169, 1, 2, '2020-02-14', '13:15', 16, 1, 0, 'CONFIRMADO'),
(170, 1, 2, '2020-02-14', '14:30', 16, 1, 0, 'CONFIRMADO'),
(171, 1, 2, '2020-02-14', '15:30', 23, 1, 0, 'CONFIRMADO'),
(172, 1, 2, '2020-02-14', '16:30', 6, 1, 0, 'CONFIRMADO'),
(173, 1, 2, '2020-02-14', '17:45', 15, 1, 0, 'CONFIRMADO'),
(174, 1, 2, '2020-02-14', '18:45', 15, 1, 0, 'CONFIRMADO'),
(175, 13, 3, '2020-02-14', '08:00', 25, 1, 0, 'CONFIRMADO'),
(176, 1, 3, '2020-02-14', '09:00', 28, 1, 0, 'CONFIRMADO'),
(177, 1, 3, '2020-02-14', '10:00', 28, 1, 0, 'CONFIRMADO'),
(178, 1, 3, '2020-02-14', '11:15', 31, 1, 0, 'CONFIRMADO'),
(179, 1, 3, '2020-02-14', '13:15', 32, 1, 0, 'CONFIRMADO'),
(180, 1, 3, '2020-02-14', '14:30', 27, 1, 0, 'CONFIRMADO'),
(181, 1, 3, '2020-02-14', '16:30', 23, 1, 0, 'CONFIRMADO'),
(182, 1, 3, '2020-02-14', '18:45', 19, 1, 0, 'CONFIRMADO'),
(183, 1, 1, '2020-02-15', '08:00', 23, 1, 0, 'CONFIRMADO'),
(184, 1, 1, '2020-02-15', '09:00', 32, 1, 0, 'CONFIRMADO'),
(185, 1, 1, '2020-02-15', '10:00', 40, 1, 0, 'CONFIRMADO'),
(186, 1, 1, '2020-02-15', '11:15', 38, 1, 0, 'CONFIRMADO'),
(187, 1, 1, '2020-02-15', '12:15', 32, 1, 0, 'CONFIRMADO'),
(188, 1, 1, '2020-02-15', '13:15', 42, 1, 0, 'CONFIRMADO'),
(189, 1, 1, '2020-02-15', '14:30', 31, 1, 0, 'CONFIRMADO'),
(190, 1, 1, '2020-02-15', '15:30', 35, 1, 0, 'CONFIRMADO'),
(191, 1, 1, '2020-02-15', '16:30', 34, 1, 0, 'CONFIRMADO'),
(192, 1, 1, '2020-02-15', '17:45', 29, 1, 0, 'CONFIRMADO'),
(193, 1, 2, '2020-02-15', '08:00', 7, 1, 0, 'CONFIRMADO'),
(194, 1, 2, '2020-02-15', '09:00', 6, 1, 0, 'CONFIRMADO'),
(195, 1, 2, '2020-02-15', '10:00', 16, 1, 0, 'CONFIRMADO'),
(196, 1, 2, '2020-02-15', '15:30', 8, 1, 0, 'CONFIRMADO'),
(197, 1, 2, '2020-02-15', '16:30', 6, 1, 0, 'CONFIRMADO'),
(198, 1, 3, '2020-02-15', '11:15', 27, 1, 0, 'CONFIRMADO'),
(199, 1, 3, '2020-02-15', '12:15', 31, 1, 0, 'CONFIRMADO'),
(200, 1, 3, '2020-02-15', '13:15', 32, 1, 0, 'CONFIRMADO'),
(201, 1, 3, '2020-02-15', '14:30', 33, 1, 0, 'CONFIRMADO'),
(202, 1, 1, '2020-02-16', '09:00', 23, 1, 0, 'CONFIRMADO'),
(203, 1, 1, '2020-02-16', '11:15', 38, 1, 0, 'CONFIRMADO'),
(204, 1, 1, '2020-02-17', '08:00', 38, 1, 0, 'CONFIRMADO'),
(205, 1, 1, '2020-02-17', '09:00', 32, 1, 0, 'CONFIRMADO'),
(206, 1, 1, '2020-02-17', '10:00', 37, 1, 0, 'CONFIRMADO'),
(207, 1, 1, '2020-02-17', '11:15', 42, 1, 0, 'CONFIRMADO'),
(208, 1, 1, '2020-02-17', '12:15', 32, 1, 0, 'CONFIRMADO'),
(209, 1, 1, '2020-02-17', '13:15', 32, 1, 0, 'CONFIRMADO'),
(210, 1, 1, '2020-02-17', '14:30', 32, 1, 0, 'CONFIRMADO'),
(211, 1, 1, '2020-02-17', '15:30', 32, 1, 0, 'CONFIRMADO'),
(212, 1, 1, '2020-02-17', '16:30', 33, 1, 0, 'CONFIRMADO'),
(213, 1, 1, '2020-02-17', '17:45', 31, 1, 0, 'CONFIRMADO'),
(214, 1, 1, '2020-02-17', '18:45', 41, 1, 0, 'CONFIRMADO'),
(215, 1, 2, '2020-02-17', '08:00', 6, 1, 0, 'CONFIRMADO'),
(216, 1, 2, '2020-02-17', '09:00', 6, 1, 0, 'CONFIRMADO'),
(217, 1, 2, '2020-02-17', '11:15', 9, 1, 0, 'CONFIRMADO'),
(218, 1, 2, '2020-02-17', '12:15', 15, 1, 0, 'CONFIRMADO'),
(219, 1, 2, '2020-02-17', '13:15', 15, 1, 0, 'CONFIRMADO'),
(220, 1, 2, '2020-02-17', '14:30', 23, 1, 0, 'CONFIRMADO'),
(221, 1, 2, '2020-02-17', '15:30', 23, 1, 0, 'CONFIRMADO'),
(222, 1, 2, '2020-02-17', '16:30', 6, 1, 0, 'CONFIRMADO'),
(223, 1, 2, '2020-02-17', '17:45', 7, 1, 0, 'CONFIRMADO'),
(224, 1, 2, '2020-02-17', '18:45', 9, 1, 0, 'CONFIRMADO'),
(225, 1, 3, '2020-02-17', '12:15', 23, 1, 0, 'CONFIRMADO'),
(226, 1, 3, '2020-02-17', '13:15', 32, 1, 0, 'CONFIRMADO'),
(227, 1, 3, '2020-02-17', '14:30', 32, 1, 0, 'CONFIRMADO'),
(228, 1, 3, '2020-02-17', '15:30', 32, 1, 0, 'CONFIRMADO'),
(229, 1, 3, '2020-02-17', '16:30', 41, 1, 0, 'CONFIRMADO'),
(230, 1, 3, '2020-02-17', '17:45', 40, 1, 0, 'CONFIRMADO'),
(231, 1, 3, '2020-02-17', '18:45', 38, 1, 0, 'CONFIRMADO'),
(232, 1, 1, '2020-02-18', '09:00', 32, 1, 0, 'CONFIRMADO'),
(233, 1, 1, '2020-02-18', '11:15', 33, 1, 0, 'CONFIRMADO'),
(234, 1, 1, '2020-02-18', '12:15', 37, 1, 0, 'CONFIRMADO'),
(235, 1, 1, '2020-02-18', '13:15', 32, 1, 0, 'CONFIRMADO'),
(236, 1, 1, '2020-02-18', '14:30', 32, 1, 0, 'CONFIRMADO'),
(237, 1, 1, '2020-02-18', '16:30', 31, 1, 0, 'CONFIRMADO'),
(238, 1, 1, '2020-02-18', '17:45', 29, 1, 0, 'CONFIRMADO'),
(239, 1, 1, '2020-02-18', '18:45', 40, 1, 0, 'CONFIRMADO'),
(240, 1, 2, '2020-02-18', '11:15', 15, 1, 0, 'CONFIRMADO'),
(241, 1, 2, '2020-02-18', '14:30', 21, 1, 0, 'CONFIRMADO'),
(242, 1, 2, '2020-02-18', '15:30', 15, 1, 0, 'CONFIRMADO'),
(243, 1, 2, '2020-02-18', '16:30', 23, 1, 0, 'CONFIRMADO'),
(244, 1, 2, '2020-02-18', '17:45', 7, 1, 0, 'CONFIRMADO'),
(245, 1, 2, '2020-02-18', '18:45', 9, 1, 0, 'CONFIRMADO'),
(246, 1, 3, '2020-02-18', '12:15', 23, 1, 0, 'CONFIRMADO'),
(247, 1, 3, '2020-02-18', '13:15', 23, 1, 0, 'CONFIRMADO'),
(248, 1, 3, '2020-02-18', '14:30', 38, 1, 0, 'CONFIRMADO'),
(249, 1, 3, '2020-02-18', '15:30', 32, 1, 0, 'CONFIRMADO'),
(250, 1, 3, '2020-02-18', '16:30', 33, 1, 0, 'CONFIRMADO'),
(251, 1, 3, '2020-02-18', '17:45', 31, 1, 0, 'CONFIRMADO'),
(252, 1, 1, '2020-02-19', '08:00', 33, 1, 0, 'CONFIRMADO'),
(253, 1, 1, '2020-02-19', '09:00', 32, 1, 0, 'CONFIRMADO'),
(254, 1, 1, '2020-02-19', '10:00', 32, 1, 0, 'CONFIRMADO'),
(255, 1, 1, '2020-02-19', '11:15', 33, 1, 0, 'CONFIRMADO'),
(256, 1, 1, '2020-02-19', '13:15', 32, 1, 0, 'CONFIRMADO'),
(257, 1, 1, '2020-02-19', '14:30', 32, 1, 0, 'CONFIRMADO'),
(258, 1, 1, '2020-02-19', '16:30', 31, 1, 0, 'CONFIRMADO'),
(259, 1, 1, '2020-02-19', '17:45', 38, 1, 0, 'CONFIRMADO'),
(260, 1, 1, '2020-02-19', '18:45', 41, 1, 0, 'CONFIRMADO'),
(261, 1, 2, '2020-02-19', '09:00', 6, 1, 0, 'CONFIRMADO'),
(262, 1, 2, '2020-02-19', '11:15', 15, 1, 0, 'CONFIRMADO'),
(263, 1, 2, '2020-02-19', '15:30', 9, 1, 0, 'CONFIRMADO'),
(264, 1, 2, '2020-02-19', '16:30', 6, 1, 0, 'CONFIRMADO'),
(265, 1, 3, '2020-02-19', '12:15', 41, 1, 0, 'CONFIRMADO'),
(266, 1, 3, '2020-02-19', '16:30', 31, 1, 0, 'CONFIRMADO'),
(267, 1, 3, '2020-02-19', '17:45', 31, 1, 0, 'CONFIRMADO'),
(268, 1, 1, '2020-02-21', '08:00', 33, 1, 0, 'CONFIRMADO'),
(269, 1, 1, '2020-02-21', '09:00', 32, 1, 0, 'CONFIRMADO'),
(270, 1, 1, '2020-02-21', '10:00', 32, 1, 0, 'CONFIRMADO'),
(271, 1, 1, '2020-02-21', '12:15', 41, 1, 0, 'CONFIRMADO'),
(272, 1, 1, '2020-02-21', '16:30', 33, 1, 0, 'CONFIRMADO'),
(273, 1, 2, '2020-02-21', '08:00', 25, 1, 0, 'CONFIRMADO'),
(274, 1, 2, '2020-02-21', '11:15', 15, 1, 0, 'CONFIRMADO'),
(275, 1, 2, '2020-02-21', '15:30', 6, 1, 0, 'CONFIRMADO'),
(276, 1, 2, '2020-02-21', '17:45', 9, 1, 0, 'CONFIRMADO'),
(277, 12, 2, '2020-02-24', '09:00', 7, 43, 5, 'CONFIRMADO'),
(278, 13, 2, '2020-02-24', '09:00', 7, 45, 6, 'CONFIRMADO'),
(279, 14, 2, '2020-02-24', '11:15', 7, 47, 7, 'CONFIRMADO'),
(280, 14, 2, '2020-03-23', '11:15', 7, 47, 7, 'NO CONFIRMADO'),
(281, 15, 2, '2020-02-17', '10:00', 24, 48, 8, 'NO CONFIRMADO'),
(282, 15, 4, '2020-03-02', '14:30', 24, 48, 8, 'NO CONFIRMADO'),
(283, 16, 2, '2020-02-17', '10:00', 24, 49, 9, 'NO CONFIRMADO'),
(284, 16, 4, '2020-03-02', '14:30', 24, 49, 9, 'NO CONFIRMADO'),
(285, 17, 2, '2020-02-17', '10:00', 24, 50, 10, 'NO CONFIRMADO'),
(286, 17, 4, '2020-03-02', '14:30', 24, 50, 10, 'NO CONFIRMADO'),
(287, 18, 2, '2020-02-17', '10:00', 24, 51, 11, 'NO CONFIRMADO'),
(288, 18, 4, '2020-03-02', '14:30', 24, 51, 11, 'NO CONFIRMADO');

-- --------------------------------------------------------

--
-- Table structure for table `cronograma`
--

CREATE TABLE `cronograma` (
  `idCronograma` int(11) NOT NULL,
  `status` varchar(255) NOT NULL,
  `idAlumno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cronograma`
--

INSERT INTO `cronograma` (`idCronograma`, `status`, `idAlumno`) VALUES
(1, 'NO CONFIRMADO', 8),
(2, 'NO CONFIRMADO', 9),
(3, 'NO CONFIRMADO', 10),
(4, 'NO CONFIRMADO', 11),
(5, 'NO CONFIRMADO', 12),
(6, 'NO CONFIRMADO', 13),
(7, 'NO CONFIRMADO', 14),
(8, 'NO CONFIRMADO', 15),
(9, 'NO CONFIRMADO', 16),
(10, 'NO CONFIRMADO', 17),
(11, 'NO CONFIRMADO', 18);

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

--
-- Dumping data for table `direccion`
--

INSERT INTO `direccion` (`idDireccion`, `calle`, `calle_diag`, `calle_a`, `calle_a_diag`, `calle_b`, `calle_b_diag`, `numero`, `ciudad`, `departamento`, `floor_`, `observaciones`) VALUES
(1, '123', '0', '47', '0', '', '0', '755', 'Ensenada', '', '', ''),
(8, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '1', '44', ''),
(9, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '1', '44', 'LALALALALA'),
(10, '80', 'true', '', 'false', '5', 'false', '', 'Ensenada', '1', '44', 'LALALALALA'),
(11, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(12, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(13, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(14, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(15, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(16, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(17, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(18, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(19, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(20, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(21, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(22, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(23, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(24, '80', 'true', '5', 'false', '', 'false', '', 'La Plata', '', '', ''),
(25, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(26, '80', 'true', '5', 'false', '', 'false', '', 'La Plata', '', '', ''),
(27, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(28, '80', 'true', '5', 'false', '', 'false', '', 'La Plata', '', '', ''),
(29, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(30, '80', 'true', '5', 'false', '', 'false', '', 'La Plata', '', '', ''),
(31, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(32, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(33, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(34, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(35, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(36, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(37, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(38, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(39, '80', 'true', '5', 'false', '', 'false', '', 'La Plata', '', '', ''),
(40, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(41, '521', 'false', '10', 'false', '', 'false', '', 'La Plata', '', '', ''),
(42, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(43, '521', 'false', '10', 'false', '', 'false', '', 'La Plata', '', '', ''),
(44, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(45, '521', 'false', '10', 'false', '', 'false', '', 'La Plata', '', '', ''),
(46, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(47, '521', 'false', '10', 'false', '', 'false', '', 'La Plata', '', '', ''),
(48, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(49, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(50, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', ''),
(51, '123', 'false', '', 'false', '', 'false', '755', 'Ensenada', '', '', '');

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

--
-- Dumping data for table `disponibilidad`
--

INSERT INTO `disponibilidad` (`idDisponibilidad`, `Monday`, `Tuesday`, `Wednesday`, `Thursday`, `Friday`, `Saturday`, `Sunday`) VALUES
(1, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(2, NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(3, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45', NULL, NULL, NULL, NULL, NULL, NULL),
(4, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45', NULL, '', NULL, NULL, NULL, NULL),
(5, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45', NULL, '', NULL, NULL, NULL, NULL),
(6, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45', NULL, '', NULL, NULL, NULL, NULL),
(7, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45', NULL, '09:00, 10:00, 11:15, 12:15, 16:30, 17:45, 18:45, 19:45', NULL, NULL, NULL, NULL),
(8, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(9, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |true, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(10, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |true, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(11, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |true, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(12, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |true, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(13, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(14, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(15, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(16, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(17, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, '09:00, 10:00, 11:15, 12:15, |false, 16:30, 17:45, 18:45, 19:45, |false', NULL, NULL, NULL, NULL),
(18, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL),
(19, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL),
(20, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|true', NULL, NULL, NULL, NULL, NULL, NULL),
(21, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|true', NULL, NULL, NULL, NULL, NULL, NULL),
(22, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|true', NULL, NULL, NULL, NULL, NULL, NULL),
(23, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|true', NULL, NULL, NULL, NULL, NULL, NULL),
(24, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|true', NULL, NULL, NULL, NULL, NULL, NULL),
(25, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL),
(26, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL),
(27, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL),
(28, '08:00, 09:00, 10:00, 11:15, 12:15, 13:15, 14:30, 15:30, 16:30, 17:45, 18:45, 19:45|false', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `excepcion`
--

CREATE TABLE `excepcion` (
  `idExcepcion` int(11) NOT NULL,
  `fecha` int(11) NOT NULL,
  `no_puede` int(11) NOT NULL,
  `idAlumno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `excepcionhorarios`
--

CREATE TABLE `excepcionhorarios` (
  `idExcepcionHorario` int(11) NOT NULL,
  `dir_alt` int(11) NOT NULL,
  `horarios` int(11) NOT NULL,
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
(4);

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
  MODIFY `idAlumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `auto`
--
ALTER TABLE `auto`
  MODIFY `idAuto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1214217;

--
-- AUTO_INCREMENT for table `clase`
--
ALTER TABLE `clase`
  MODIFY `idClase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=289;

--
-- AUTO_INCREMENT for table `cronograma`
--
ALTER TABLE `cronograma`
  MODIFY `idCronograma` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `direccion`
--
ALTER TABLE `direccion`
  MODIFY `idDireccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `idDisponibilidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `excepcion`
--
ALTER TABLE `excepcion`
  MODIFY `idExcepcion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `excepcionhorarios`
--
ALTER TABLE `excepcionhorarios`
  MODIFY `idExcepcionHorario` int(11) NOT NULL AUTO_INCREMENT;

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
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
