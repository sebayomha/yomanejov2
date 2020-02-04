-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 04, 2020 at 07:09 PM
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
(3, '124214', 'Naranja', 'T', '', 1),
(4, '1324asf', 'Gris', 'A', 'Auto automatico', 3);

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
(1, 1, 1, '2020-02-03', '13:15', '09:00', 24, 1),
(2, 2, 2, '2020-02-03', '09:00', '10:00', 24, 1),
(3, 1, 4, '2020-02-04', '12:15', '09:00', 9, 1),
(5, 2, 4, '2020-02-10', '14:30', '15:30', 19, 1);

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
(6, 'tolosaBajo', '-34.880080,-57.971327', '-34.890289,-57.982442', '-34.899934,-57.969438', '-34.888212,-57.956049', 1),
(7, 'tolosaDesdeCalle7HastaCalle13', '-34.890470,-57.982642', '-34.896064,-57.988632', '-34.905683,-57.975496', '-34.900163,-57.969525', 1),
(8, 'tolosaCalle13HastaCalle19', '-34.896090,-57.988739', '-34.901699,-57.994586', '-34.911347,-57.981667', '-34.905902,-57.975784', 1),
(9, 'tolosaCalle19HastaCalle31', '-34.901856,-57.994910', '-34.913398,-58.007685', '-34.923636,-57.994477', '-34.911504,-57.981593', 1),
(10, 'zonaCalle126HastaCalle1', '-34.884810,-57.951583', '-34.895080,-57.962984', '-34.900096,-57.955907', '-34.887250,-57.942096', 1),
(11, 'zonaCalle1HastaCalle7', '-34.895018,-57.962996', '-34.900543,-57.968671', '-34.906136,-57.962880', '-34.900381,-57.956322', 1),
(12, 'zonaCalle7HastaCalle13', '-34.900604,-57.968670', '-34.906138,-57.974947', '-34.911181,-57.968414', '-34.906238,-57.962351', 1),
(13, 'zonaCalle13HastaCalle19', '-34.906364,-57.975304', '-34.911877,-57.981130', '-34.916962,-57.974496', '-34.911250,-57.968448', 1),
(14, 'zonaCalle19HastCalle31', '-34.911982,-57.981309', '-34.923511,-57.993609', '-34.928155,-57.986979', '-34.917547,-57.974909', 1),
(15, 'zonaCalle31HastaCalle19', '-34.928162,-57.986729', '-34.933193,-57.979905', '-34.917219,-57.974713', '-34.922638,-57.968147', 1),
(16, 'zonaCalle19HastCalle7', '-34.916913,-57.974618', '-34.905965,-57.962185', '-34.911055,-57.955725', '-34.921588,-57.967434', 1),
(17, 'zonaCalle7HastCalle1', '-34.905887,-57.961876', '-34.900113,-57.956019', '-34.911038,-57.955291', '-34.905140,-57.948918', 1),
(18, 'zonaCalle1HastaCalle127', '-34.900162,-57.955685', '-34.887618,-57.942212', '-34.905090,-57.949039', '-34.893418,-57.938176', 1),
(19, 'zonaCalle31HastaCalle25', '-34.933218,-57.979792', '-34.938078,-57.973225', '-34.931688,-57.968064', '-34.926978,-57.973002', 1),
(20, 'ZonaCalle25HastaCalle19', '-34.927136,-57.973386', '-34.922284,-57.967215', '-34.927584,-57.960197', '-34.931516,-57.967873', 1),
(21, 'ZonaCalle19HastaCalle13', '-34.921495,-57.967286', '-34.916515,-57.961290', '-34.922520,-57.954961', '-34.927416,-57.960065', 1),
(22, 'zonaCalle13HastaCalle7', '-34.915946,-57.961241', '-34.910945,-57.955354', '-34.916170,-57.947859', '-34.922756,-57.954678', 1),
(23, 'zonaCalle7HastaCalle1', '-34.910244,-57.954878', '-34.905124,-57.948780', '-34.910392,-57.941887', '-34.916340,-57.947841', 1),
(24, 'zonaCalle1Hasta126', '-34.905094,-57.949032', '-34.893522,-57.937989', '-34.897073,-57.929689', '-34.910623,-57.941415', 1),
(25, 'zonaCalle31HastaCalle25', '-34.938316,-57.973018', '-34.943347,-57.966753', '-34.937472,-57.960401', '-34.932969,-57.966538', 1),
(26, 'zonaCalle25HastaCalle19', '-34.932538,-57.966425', '-34.927438,-57.959749', '-34.931916,-57.953741', '-34.937762,-57.960235', 1),
(27, 'zonaCalle19HastCalle7', '-34.932015,-57.954044', '-34.921174,-57.942048', '-34.916323,-57.947717', '-34.927503,-57.959909', 1),
(28, 'zonaCalle7HastCalle1', '-34.915533,-57.947888', '-34.910443,-57.942475', '-34.920435,-57.941290', '-34.914995,-57.935396', 1),
(29, 'zonaCalle31HastaCalle19', '-34.943478,-57.966093', '-34.948469,-57.959206', '-34.937286,-57.947006', '-34.932098,-57.954049', 1),
(30, 'zonaCalle19HastaCalle13', '-34.932224,-57.954049', '-34.926408,-57.947720', '-34.931973,-57.940626', '-34.937203,-57.947057', 1),
(31, 'zonaCalle13HastaCalle7', '-34.931052,-57.940473', '-34.926324,-57.947669', '-34.920801,-57.941800', '-34.925906,-57.934553', 1),
(32, 'zonaCalle7HastaCalle1', '-34.925864,-57.934859', '-34.920717,-57.941443', '-34.915151,-57.935523', '-34.920340,-57.928378', 1),
(33, 'zonaCalle1HastaCalle126', '-34.920340,-57.928378', '-34.915151,-57.935574', '-34.903169,-57.923676', '-34.909445,-57.914305', 1),
(34, 'zonaCalle31HastaCalle19', '-34.948975,-57.960006', '-34.953927,-57.953019', '-34.942473,-57.940044', '-34.937263,-57.947031', 1),
(35, 'zonaCalle19HastaCalle13', '-34.937177,-57.946978', '-34.931535,-57.940832', '-34.936574,-57.933793', '-34.942344,-57.940044', 1),
(36, 'zonaCalle13HastaCalle7', '-34.931707,-57.941042', '-34.926023,-57.934739', '-34.931363,-57.927752', '-34.936875,-57.934003', 1),
(37, 'zonaCalle7HastaCalle1', '-34.931320,-57.927647', '-34.925894,-57.934604', '-34.920421,-57.928717', '-34.925894,-57.921312', 1),
(38, 'zonaCalle1HastaCalle126', '-34.925944,-57.921737', '-34.920620,-57.929142', '-34.910243,-57.914333', '-34.912466,-57.908457', 1),
(39, 'zonaCalle31HastaCalle19', '-34.953975,-57.952483', '-34.942613,-57.939908', '-34.948453,-57.932098', '-34.959533,-57.944286', 1),
(40, 'zonaCalle19HastaCalle7', '-34.948558,-57.932312', '-34.942648,-57.939737', '-34.931531,-57.927720', '-34.937442,-57.919695', 1),
(41, 'zonaCalle7HastaCalle1', '-34.937442,-57.919695', '-34.931531,-57.927635', '-34.925972,-57.921197', '-34.931637,-57.913473', 1),
(42, 'zonaCalle1HastaCalle126', '-34.931637,-57.913473', '-34.925937,-57.921369', '-34.912219,-57.908733', '-34.918428,-57.901268', 1);

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
  MODIFY `idAuto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1214217;

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
