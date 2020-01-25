-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2020 at 12:41 AM
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
  `direccion` varchar(255) NOT NULL,
  `direccionClase` varchar(255) NOT NULL,
  `fechaAlta` date NOT NULL,
  `activo` varchar(6) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `alumno`
--

INSERT INTO `alumno` (`idAlumno`, `direccion`, `direccionClase`, `fechaAlta`, `activo`) VALUES
(1, '123 N755', '123 N755', '2020-01-09', 'true'),
(2, '59 y 10', '59 y 10', '2020-01-09', 'true');

-- --------------------------------------------------------

--
-- Table structure for table `auto`
--

CREATE TABLE `auto` (
  `idAuto` int(11) NOT NULL,
  `patente` varchar(8) NOT NULL,
  `color` varchar(30) NOT NULL,
  `disponibilidad` varchar(1) NOT NULL,
  `descripcion` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `auto`
--

INSERT INTO `auto` (`idAuto`, `patente`, `color`, `disponibilidad`, `descripcion`) VALUES
(1, '456ABC', 'Rojo', 'A', ''),
(2, '123YTR', 'Naranja', 'A', ''),
(3, '124214', 'Naranja', 'T', 'Auto automatico');

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
  `horaFin` varchar(11) NOT NULL,
  `idZona` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clase`
--

INSERT INTO `clase` (`idClase`, `alumno`, `auto`, `fecha`, `horaInicio`, `horaFin`, `idZona`) VALUES
(1, 1, 1, '2020-01-09', '13:00', '09:00', 1),
(2, 2, 2, '2020-01-09', '09:00', '10:00', 2),
(3, 1, 1, '2020-01-09', '14:00', '15:00', 4),
(5, 2, 2, '2020-01-09', '10:00', '27:00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `disponibilidad`
--

CREATE TABLE `disponibilidad` (
  `idDisponibilidad` int(11) NOT NULL,
  `nombreDia` varchar(14) NOT NULL,
  `rangoHorario` varchar(255) NOT NULL,
  `idAlumno` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `disponibilidad`
--

INSERT INTO `disponibilidad` (`idDisponibilidad`, `nombreDia`, `rangoHorario`, `idAlumno`) VALUES
(1, 'Jueves', '08:00-10:00,13:00-16:00', 1),
(2, 'Viernes', '08:00-20:00', 1),
(3, 'Lunes', '08:00-20:00', 1),
(4, 'Martes', '16:00-18:00', 1),
(5, 'Miercoles', 'null', 1),
(6, 'Sabado', 'null', 1),
(7, 'Domingo', '11:00-17:00', 1),
(8, 'Jueves', '08:00-10:00,13:00-16:00', 2),
(9, 'Domingo', '11:00-17:00', 2),
(10, 'Viernes', '11:00-15:00,19:00-20:00', 2),
(11, 'Sabado', 'null', 2),
(12, 'Martes', '08:00-20:00', 2),
(13, 'Lunes', '08:00-20:00', 2),
(14, 'Miercoles', '10:00-18:00', 2);

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
-- Table structure for table `zona`
--

CREATE TABLE `zona` (
  `idZona` int(11) NOT NULL,
  `nombreZona` varchar(255) NOT NULL,
  `puntoSuperiorIzquierdo` varchar(255) NOT NULL,
  `puntoSuperiorDerecho` varchar(255) NOT NULL,
  `puntoInferiorIzquierdo` varchar(255) NOT NULL,
  `puntoInferiorDerecho` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `zona`
--

INSERT INTO `zona` (`idZona`, `nombreZona`, `puntoSuperiorIzquierdo`, `puntoSuperiorDerecho`, `puntoInferiorIzquierdo`, `puntoInferiorDerecho`) VALUES
(1, 'plazaitaliahasta1', '-34.905929,-57.961377', '-34.900759,-57.956332', '-34.905114,-57.949352', '-34.910191,-57.955877'),
(2, 'Plazaitaliaenadelante', '-34.911222,-57.968101', '-34.906245,-57.962427', '-34.911345,-57.956196', '-34.915900,-57.961811'),
(3, 'plazabelgranoaplazaguemes', '-34.916448,-57.973737', '-34.911724,-57.968456', '-34.916812,-57.962182', '-34.921247,-57.966954'),
(4, 'plazaguemeshastaalberti', '-34.917603,-57.974834', '-34.921534,-57.979446', '-34.927008,-57.974227', '-34.922504,-57.968400'),
(5, 'ZonaMiCasa', '-34.896444, -57.941498', '-34.893470, -57.938214', '-34.903799, -57.924555', '-34.907129, -57.927009');

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
(1, 2, 1),
(1, 4, 2),
(2, 1, 3),
(4, 1, 4),
(4, 3, 5),
(3, 4, 6),
(4, 5, 7),
(5, 1, 8);

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
-- Indexes for table `disponibilidad`
--
ALTER TABLE `disponibilidad`
  ADD PRIMARY KEY (`idDisponibilidad`);

--
-- Indexes for table `instructor`
--
ALTER TABLE `instructor`
  ADD PRIMARY KEY (`idInstructor`);

--
-- Indexes for table `zona`
--
ALTER TABLE `zona`
  ADD PRIMARY KEY (`idZona`);

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
  MODIFY `idAlumno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `auto`
--
ALTER TABLE `auto`
  MODIFY `idAuto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1214216;

--
-- AUTO_INCREMENT for table `clase`
--
ALTER TABLE `clase`
  MODIFY `idClase` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `disponibilidad`
--
ALTER TABLE `disponibilidad`
  MODIFY `idDisponibilidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `instructor`
--
ALTER TABLE `instructor`
  MODIFY `idInstructor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `zona`
--
ALTER TABLE `zona`
  MODIFY `idZona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zonasvecinas`
--
ALTER TABLE `zonasvecinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
