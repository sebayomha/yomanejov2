-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 02, 2020 at 03:43 PM
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
  `fechaAlta` date NOT NULL,
  `activo` varchar(6) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `fecha_nacimiento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `alumno`
--

INSERT INTO `alumno` (`idAlumno`, `idDireccion`, `fechaAlta`, `activo`, `nombre`, `apellido`, `fecha_nacimiento`) VALUES
(1, 1, '2020-01-09', 'true', 'Sebastian', 'Yomha', '0000-00-00'),
(2, 1, '2020-01-09', 'true', 'Matias', 'Guazzaroni', '0000-00-00');

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
(1, '456ABC', 'Rojo', 'A', '', 2),
(2, '123YTR', 'Naranja', 'A', '', 3),
(3, '124214', 'Naranja', 'T', 'Auto automatico', 1);

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
  `idZona` int(11) NOT NULL,
  `idDireccion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `clase`
--

INSERT INTO `clase` (`idClase`, `alumno`, `auto`, `fecha`, `horaInicio`, `horaFin`, `idZona`, `idDireccion`) VALUES
(1, 1, 1, '2020-02-03', '13:15', '09:00', 5, 1),
(2, 2, 2, '2020-02-03', '09:00', '10:00', 4, 1),
(3, 1, 1, '2020-02-03', '14:30', '15:00', 4, 1),
(5, 2, 3, '2020-02-10', '14:30', '15:30', 5, 1);

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
  `ciudad` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `direccion`
--

INSERT INTO `direccion` (`idDireccion`, `calle`, `calle_diag`, `calle_a`, `calle_a_diag`, `calle_b`, `calle_b_diag`, `numero`, `ciudad`) VALUES
(1, '123', 'false', '47', 'false', '', 'false', '755', 'Ensenada');

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
(1, 'plazaitaliahasta1', '-34.905929,-57.961377', '-34.900759,-57.956332', '-34.905114,-57.949352', '-34.910191,-57.955877', 1),
(2, 'Plazaitaliaenadelante', '-34.911222,-57.968101', '-34.906245,-57.962427', '-34.911345,-57.956196', '-34.915900,-57.961811', 1),
(3, 'plazabelgranoaplazaguemes', '-34.916448,-57.973737', '-34.911724,-57.968456', '-34.916812,-57.962182', '-34.921247,-57.966954', 1),
(4, 'plazaguemeshastaalberti', '-34.917603,-57.974834', '-34.921534,-57.979446', '-34.927008,-57.974227', '-34.922504,-57.968400', 3),
(5, 'ZonaMiCasa', '-34.896444, -57.941498', '-34.893470, -57.938214', '-34.903799, -57.924555', '-34.907129, -57.927009', 2);

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
(1, 2, 1),
(1, 4, 2),
(2, 5, 3),
(4, 1, 4),
(4, 3, 5),
(3, 4, 6),
(4, 1, 7),
(5, 1, 8),
(1, 3, 9);

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
-- Indexes for table `direccion`
--
ALTER TABLE `direccion`
  ADD PRIMARY KEY (`idDireccion`);

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
-- AUTO_INCREMENT for table `direccion`
--
ALTER TABLE `direccion`
  MODIFY `idDireccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `idZona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `zonamaster`
--
ALTER TABLE `zonamaster`
  MODIFY `idZonaMaster` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `zonasvecinas`
--
ALTER TABLE `zonasvecinas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
