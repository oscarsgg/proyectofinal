-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-11-2024 a las 16:34:29
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
-- Base de datos: `outsourcing`
--
CREATE DATABASE IF NOT EXISTS `outsourcing` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `outsourcing`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrera`
--

CREATE TABLE `carrera` (
  `codigo` varchar(5) NOT NULL,
  `nombre` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrera`
--

INSERT INTO `carrera` (`codigo`, `nombre`) VALUES
('INDS', 'Ingeniería en Desarrollo de Software'),
('INGA', 'Ingeniería en Administración'),
('INGC', 'Ingeniería en Computación'),
('INGE', 'Ingeniería en Electrónica'),
('INGF', 'Ingeniería en Finanzas'),
('INGG', 'Ingeniería en Geología'),
('INGM', 'Ingeniería en Mecánica'),
('INGN', 'Ingeniería en Naval'),
('INGP', 'Ingeniería en Petróleo'),
('INGQ', 'Ingeniería en Química'),
('INGR', 'Ingeniería en Recursos Humanos'),
('INGT', 'Ingeniería en Turismo'),
('LICB', 'Licenciatura en Biología'),
('LICD', 'Licenciatura en Derecho'),
('LICF', 'Licenciatura en Física'),
('LICL', 'Licenciatura en Lengua y Literatura'),
('LICM', 'Licenciatura en Medicina'),
('LICN', 'Licenciatura en Nutrición'),
('LICP', 'Licenciatura en Psicología');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras_estudiadas`
--

CREATE TABLE `carreras_estudiadas` (
  `prospecto` int(11) NOT NULL,
  `carrera` varchar(30) NOT NULL,
  `anioConcluido` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras_estudiadas`
--

INSERT INTO `carreras_estudiadas` (`prospecto`, `carrera`, `anioConcluido`) VALUES
(1, 'INDS', 2023),
(1, 'INGC', 2020),
(2, 'LICM', 2019);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras_solicitadas`
--

CREATE TABLE `carreras_solicitadas` (
  `vacante` int(11) NOT NULL,
  `carrera` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras_solicitadas`
--

INSERT INTO `carreras_solicitadas` (`vacante`, `carrera`) VALUES
(1, 'INGC'),
(1, 'LICM'),
(2, 'INGE'),
(3, 'INGC'),
(3, 'LICF'),
(6, 'INDS'),
(6, 'INGA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contrato`
--

CREATE TABLE `contrato` (
  `numero` int(11) NOT NULL,
  `fechaInicio` date NOT NULL,
  `fechaCierre` date NOT NULL,
  `prospecto` int(11) NOT NULL,
  `vacante` int(11) NOT NULL,
  `tipo_contrato` varchar(5) NOT NULL,
  `salario` decimal(10,2) NOT NULL,
  `horasDiarias` int(11) NOT NULL,
  `horario` varchar(100) NOT NULL,
  `firma_empresa` varchar(256) DEFAULT NULL,
  `firma_prospecto` varchar(256) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `contrato`
--

INSERT INTO `contrato` (`numero`, `fechaInicio`, `fechaCierre`, `prospecto`, `vacante`, `tipo_contrato`, `salario`, `horasDiarias`, `horario`, `firma_empresa`, `firma_prospecto`) VALUES
(1, '2024-04-01', '2024-06-30', 1, 1, 'TC03', 8000.00, 8, 'De 10 de la mañana a 8 de la noche', 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAZAAAADICAYAAADGFbfiAAAAAXNSR0IArs4c6QAAIABJREFUeF7tnQf8f+d0x4+oXVsSasZMxUiMVm1KURUlVmKlEatG1Z5BJKhZYo8oMRIjMRsjiUqUpGZtUSUINUKLSjRavW85p33c3O/d+/s5r9ffP36/+4z7ee7/Oc9zxuecwyRCQAgIASEgBFogcI4WbdRECAgBISAEhIBJge', NULL),
(52, '2024-11-08', '2024-11-29', 2, 1, 'TC02', 8000.00, 8, '0', '/Outsourcing/firmas/firma_1731046121.png', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `numero` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `ciudad` varchar(30) NOT NULL,
  `calle` varchar(30) NOT NULL,
  `numeroCalle` int(11) NOT NULL,
  `colonia` varchar(30) NOT NULL,
  `codigoPostal` int(11) NOT NULL,
  `nombreCont` varchar(30) NOT NULL,
  `primerApellidoCont` varchar(30) NOT NULL,
  `segundoApellidoCont` varchar(30) DEFAULT NULL,
  `usuario` int(11) NOT NULL,
  `numTel` varchar(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`numero`, `nombre`, `ciudad`, `calle`, `numeroCalle`, `colonia`, `codigoPostal`, `nombreCont`, `primerApellidoCont`, `segundoApellidoCont`, `usuario`, `numTel`) VALUES
(1, 'BitLab', 'Tijuana', 'Avenida Independencia', 123, 'Centro', 22000, 'Josue Isaac', 'Lara', 'Lopez', 2, ''),
(2, 'Microsoft', 'Tijuana', 'Calle 5 de Mayo', 456, 'Zona Río', 22400, 'Ana', 'Martínez', 'López', 4, ''),
(5, 'Monster', 'Tijuana', 'Calle Olivo', 123, 'Maclovio', 22236, 'Oscar', 'Soto', 'Garcia', 9, '6658787789');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estatus_solicitud`
--

CREATE TABLE `estatus_solicitud` (
  `codigo` varchar(5) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estatus_solicitud`
--

INSERT INTO `estatus_solicitud` (`codigo`, `nombre`) VALUES
('APRO', 'Aprobada'),
('CERR', 'Cerrada'),
('PEND', 'Pendiente'),
('PFRM', 'Por firmar'),
('RECH', 'Rechazada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `experiencia`
--

CREATE TABLE `experiencia` (
  `numero` int(8) NOT NULL,
  `puesto` varchar(30) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `nombreEmpresa` varchar(40) DEFAULT NULL,
  `fechaInicio` date NOT NULL,
  `fechaFin` date NOT NULL,
  `prospecto` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `experiencia`
--

INSERT INTO `experiencia` (`numero`, `puesto`, `descripcion`, `nombreEmpresa`, `fechaInicio`, `fechaFin`, `prospecto`) VALUES
(1, 'Desarrollador', 'Desarrollo de aplicaciones web.', 'Tech Solutions', '2021-01-01', '2022-01-01', 1),
(3, 'Gerente de Proyectos', 'Gestión y supervisión de proyectos de IT.', 'Global Tech', '2019-05-01', '2021-05-01', 2),
(4, 'Consultor IT', 'Asesoría en soluciones tecnológicas.', 'Tech Advisors', '2018-01-01', '2019-04-30', 2),
(5, 'Asistente del gerente regional', 'Asistente del gerente regional para la empresa bitlab', 'BitLab', '2024-03-04', '2024-11-01', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `membresia`
--

CREATE TABLE `membresia` (
  `numero` int(11) NOT NULL,
  `fechaVencimiento` date NOT NULL,
  `empresa` int(11) NOT NULL,
  `plan_suscripcion` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `membresia`
--

INSERT INTO `membresia` (`numero`, `fechaVencimiento`, `empresa`, `plan_suscripcion`) VALUES
(1, '2025-01-10', 1, 'PRO01'),
(2, '2025-06-30', 2, 'PRE01'),
(4, '2024-10-26', 5, 'BAS01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_suscripcion`
--

CREATE TABLE `plan_suscripcion` (
  `codigo` varchar(5) NOT NULL,
  `duracion` int(11) NOT NULL,
  `precio` decimal(8,2) NOT NULL,
  `precioMensual` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plan_suscripcion`
--

INSERT INTO `plan_suscripcion` (`codigo`, `duracion`, `precio`, `precioMensual`) VALUES
('BAS01', 1, 999.99, 999.99),
('PRE01', 6, 4999.99, 833.33),
('PRO01', 12, 8999.99, 750.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prospecto`
--

CREATE TABLE `prospecto` (
  `numero` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `primerApellido` varchar(30) NOT NULL,
  `segundoApellido` varchar(30) DEFAULT NULL,
  `resumen` varchar(300) DEFAULT NULL,
  `fechaNacimiento` date NOT NULL,
  `usuario` int(11) NOT NULL,
  `numTel` varchar(15) NOT NULL,
  `aniosExperiencia` decimal(5,1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prospecto`
--

INSERT INTO `prospecto` (`numero`, `nombre`, `primerApellido`, `segundoApellido`, `resumen`, `fechaNacimiento`, `usuario`, `numTel`, `aniosExperiencia`) VALUES
(1, 'Juan', 'Pérez', 'González', 'Hola', '1990-01-01', 3, '6641234566', 3.7),
(2, 'María', 'García', 'Rodríguez', 'Hola', '1992-02-02', 5, '6640678733', NULL),
(3, 'Chanchito', 'Muy', 'Feliz', 'Amante de los chanchitos felices', '2005-12-12', 7, '6645638876', NULL),
(4, 'Chanchito', 'Feliz', 'Garcia', 'hola', '2005-03-20', 8, '6644879993', NULL),
(5, 'Ramses', 'Aguirre', 'Espinoza', 'Heroico en Free Fire', '2005-03-02', 10, '6645768878', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `renovacion`
--

CREATE TABLE `renovacion` (
  `numero` int(8) NOT NULL,
  `fechaRenovacion` date NOT NULL,
  `membresia` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `renovacion`
--

INSERT INTO `renovacion` (`numero`, `fechaRenovacion`, `membresia`) VALUES
(1, '2023-01-15', 1),
(2, '2023-06-20', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `requerimiento`
--

CREATE TABLE `requerimiento` (
  `numero` int(11) NOT NULL,
  `descripcion` varchar(200) NOT NULL,
  `vacante` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `requerimiento`
--

INSERT INTO `requerimiento` (`numero`, `descripcion`, `vacante`) VALUES
(1, 'Experiencia en desarrollo de software con Java y Spring', 1),
(2, 'Conocimientos de bases de datos relacionales y no relacionales', 1),
(3, 'Experiencia en trabajo en equipo y colaboración continua', 1),
(4, 'Experiencia en configuración de routers y switches', 2),
(5, 'Conocimientos de protocolos de red y seguridad', 2),
(6, 'Experiencia en trabajo en equipo y colaboración continua', 2),
(7, 'Experiencia en análisis de datos con SQL ', 3),
(8, 'Conocimientos de estadística y matemáticas', 3),
(9, 'Experiencia en trabajo en equipo y colaboración continua', 3),
(11, 'Conocer redes', 6),
(12, 'Hacer telnet', 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsabilidades`
--

CREATE TABLE `responsabilidades` (
  `numero` int(8) NOT NULL,
  `descripcion` varchar(150) NOT NULL,
  `experiencia` int(8) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `responsabilidades`
--

INSERT INTO `responsabilidades` (`numero`, `descripcion`, `experiencia`) VALUES
(1, 'Desarrollar código eficiente y limpio.', 1),
(2, 'Colaborar con el equipo de diseño.', 1),
(3, 'Realizar pruebas de calidad del software.', 1),
(6, 'Planificar y ejecutar proyectos de IT.', 3),
(7, 'Coordinar equipos de trabajo.', 3),
(8, 'Presentar informes de avance a la dirección.', 3),
(9, 'Evaluar nuevas tecnologías para su implementación.', 4),
(10, 'Asesorar a clientes en la adopción de tecnologías.', 4),
(11, 'Responsable\r', 5),
(12, 'Amable\r', 5),
(13, 'Creativo', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `codigo` varchar(3) NOT NULL,
  `nombre` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`codigo`, `nombre`) VALUES
('ADM', 'Administrador'),
('EMP', 'Empresa'),
('PRO', 'Prospecto');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitud`
--

CREATE TABLE `solicitud` (
  `prospecto` int(8) NOT NULL,
  `vacante` int(8) NOT NULL,
  `estatus` varchar(5) NOT NULL,
  `es_cancelada` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitud`
--

INSERT INTO `solicitud` (`prospecto`, `vacante`, `estatus`, `es_cancelada`) VALUES
(1, 1, 'PFRM', 0),
(1, 2, 'PEND', 0),
(1, 5, 'PEND', 0),
(2, 1, 'PFRM', 0),
(2, 2, 'RECH', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_contrato`
--

CREATE TABLE `tipo_contrato` (
  `codigo` varchar(5) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `descripcion` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_contrato`
--

INSERT INTO `tipo_contrato` (`codigo`, `nombre`, `descripcion`) VALUES
('TC01', 'Contrato permanente', 'Contrato sin plazo fijo, puede ser renovado o terminado en cualquier momento'),
('TC02', 'Contrato temporal (plazo fijo)', 'Contrato con un plazo fijo de duración, puede ser renovado o terminado al final del plazo'),
('TC03', 'Contrato por obra o proyecto', 'Contrato para realizar una obra o servicio específico, se termina cuando se completa la obra o servicio');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `numero` int(11) NOT NULL,
  `correo` varchar(25) NOT NULL,
  `contrasenia` varchar(25) NOT NULL,
  `rol` varchar(3) NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`numero`, `correo`, `contrasenia`, `rol`, `estado`) VALUES
(1, 'admin@gmail.com', '12345', 'ADM', 1),
(2, 'empresa@gmail.com', '12345', 'EMP', 1),
(3, 'prospecto@gmail.com', '12345', 'PRO', 1),
(4, 'empresa2@gmail.com', '12345', 'EMP', 1),
(5, 'prospecto2@gmail.com', '12345', 'PRO', 1),
(6, 'ewjfoewjfew@gmail.com', '12345', 'EMP', 1),
(7, 'chanchito@feliz.com', 'chanchitofeliz', 'PRO', 1),
(8, 'oscargael.gar7@gmail.com', '12345', 'PRO', 1),
(9, 'empresa3@gmail.com', '12345', 'EMP', 1),
(10, 'ramses@gmail.com', '12345', 'PRO', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacante`
--

CREATE TABLE `vacante` (
  `numero` int(11) NOT NULL,
  `titulo` varchar(30) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `salario` int(11) DEFAULT NULL,
  `es_directo` tinyint(1) NOT NULL,
  `cantPostulantes` int(11) DEFAULT NULL,
  `fechaInicio` date NOT NULL,
  `fechaCierre` date NOT NULL,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `tipo_contrato` varchar(5) NOT NULL,
  `empresa` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vacante`
--

INSERT INTO `vacante` (`numero`, `titulo`, `descripcion`, `salario`, `es_directo`, `cantPostulantes`, `fechaInicio`, `fechaCierre`, `estado`, `tipo_contrato`, `empresa`) VALUES
(1, 'Desarrollador de software', 'Se busca un desarrollador de software con experiencia en Java y Spring Boot para trabajar en un proyecto de desarrollo de software', 50000, 1, 1, '2024-01-01', '2024-01-31', 1, 'TC03', 1),
(2, 'Ingeniero de redes', 'Se busca un ingeniero de redes con experiencia en configuración de routers y switches para trabajar en un proyecto de implementación de redes', 60000, 0, 2, '2024-02-01', '2024-02-28', 1, 'TC03', 2),
(3, 'Analista de datos', 'Se busca un analista de datos con experiencia en SQL y Tableau para trabajar en un proyecto de análisis de datos', 40000, 1, 1, '2024-03-01', '2024-03-31', 1, 'TC02', 1),
(5, 'Recepcionista de GYM', '1', 0, 1, 12, '2024-10-27', '2024-12-24', 1, 'TC02', 1),
(6, 'Técnico de Cisco Packet Tracer', 'Hola mundo', 0, 1, NULL, '2024-11-09', '2024-11-23', 1, 'TC01', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carrera`
--
ALTER TABLE `carrera`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `carreras_estudiadas`
--
ALTER TABLE `carreras_estudiadas`
  ADD PRIMARY KEY (`prospecto`,`carrera`),
  ADD KEY `carrera` (`carrera`);

--
-- Indices de la tabla `carreras_solicitadas`
--
ALTER TABLE `carreras_solicitadas`
  ADD PRIMARY KEY (`vacante`,`carrera`),
  ADD KEY `carrera` (`carrera`);

--
-- Indices de la tabla `contrato`
--
ALTER TABLE `contrato`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `prospecto` (`prospecto`),
  ADD KEY `vacante` (`vacante`),
  ADD KEY `tipo_contrato` (`tipo_contrato`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `usuario` (`usuario`);

--
-- Indices de la tabla `estatus_solicitud`
--
ALTER TABLE `estatus_solicitud`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `experiencia`
--
ALTER TABLE `experiencia`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `prospecto` (`prospecto`);

--
-- Indices de la tabla `membresia`
--
ALTER TABLE `membresia`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `empresa` (`empresa`),
  ADD KEY `plan_suscripcion` (`plan_suscripcion`);

--
-- Indices de la tabla `plan_suscripcion`
--
ALTER TABLE `plan_suscripcion`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `prospecto`
--
ALTER TABLE `prospecto`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `usuario` (`usuario`);

--
-- Indices de la tabla `renovacion`
--
ALTER TABLE `renovacion`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `membresia` (`membresia`);

--
-- Indices de la tabla `requerimiento`
--
ALTER TABLE `requerimiento`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `vacante` (`vacante`);

--
-- Indices de la tabla `responsabilidades`
--
ALTER TABLE `responsabilidades`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `experiencia` (`experiencia`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD PRIMARY KEY (`prospecto`,`vacante`),
  ADD KEY `vacante` (`vacante`),
  ADD KEY `estatus` (`estatus`);

--
-- Indices de la tabla `tipo_contrato`
--
ALTER TABLE `tipo_contrato`
  ADD PRIMARY KEY (`codigo`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `rol` (`rol`);

--
-- Indices de la tabla `vacante`
--
ALTER TABLE `vacante`
  ADD PRIMARY KEY (`numero`),
  ADD KEY `tipo_contrato` (`tipo_contrato`),
  ADD KEY `empresa` (`empresa`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contrato`
--
ALTER TABLE `contrato`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `experiencia`
--
ALTER TABLE `experiencia`
  MODIFY `numero` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `membresia`
--
ALTER TABLE `membresia`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `prospecto`
--
ALTER TABLE `prospecto`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `renovacion`
--
ALTER TABLE `renovacion`
  MODIFY `numero` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `requerimiento`
--
ALTER TABLE `requerimiento`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `responsabilidades`
--
ALTER TABLE `responsabilidades`
  MODIFY `numero` int(8) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `vacante`
--
ALTER TABLE `vacante`
  MODIFY `numero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carreras_estudiadas`
--
ALTER TABLE `carreras_estudiadas`
  ADD CONSTRAINT `carreras_estudiadas_ibfk_1` FOREIGN KEY (`prospecto`) REFERENCES `prospecto` (`numero`),
  ADD CONSTRAINT `carreras_estudiadas_ibfk_2` FOREIGN KEY (`carrera`) REFERENCES `carrera` (`codigo`);

--
-- Filtros para la tabla `carreras_solicitadas`
--
ALTER TABLE `carreras_solicitadas`
  ADD CONSTRAINT `carreras_solicitadas_ibfk_1` FOREIGN KEY (`vacante`) REFERENCES `vacante` (`numero`),
  ADD CONSTRAINT `carreras_solicitadas_ibfk_2` FOREIGN KEY (`carrera`) REFERENCES `carrera` (`codigo`);

--
-- Filtros para la tabla `contrato`
--
ALTER TABLE `contrato`
  ADD CONSTRAINT `contrato_ibfk_1` FOREIGN KEY (`prospecto`) REFERENCES `prospecto` (`numero`),
  ADD CONSTRAINT `contrato_ibfk_2` FOREIGN KEY (`vacante`) REFERENCES `vacante` (`numero`),
  ADD CONSTRAINT `contrato_ibfk_3` FOREIGN KEY (`tipo_contrato`) REFERENCES `tipo_contrato` (`codigo`);

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `empresa_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`numero`);

--
-- Filtros para la tabla `experiencia`
--
ALTER TABLE `experiencia`
  ADD CONSTRAINT `experiencia_ibfk_1` FOREIGN KEY (`prospecto`) REFERENCES `prospecto` (`numero`);

--
-- Filtros para la tabla `membresia`
--
ALTER TABLE `membresia`
  ADD CONSTRAINT `membresia_ibfk_1` FOREIGN KEY (`empresa`) REFERENCES `empresa` (`numero`),
  ADD CONSTRAINT `membresia_ibfk_2` FOREIGN KEY (`plan_suscripcion`) REFERENCES `plan_suscripcion` (`codigo`);

--
-- Filtros para la tabla `prospecto`
--
ALTER TABLE `prospecto`
  ADD CONSTRAINT `prospecto_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuario` (`numero`);

--
-- Filtros para la tabla `renovacion`
--
ALTER TABLE `renovacion`
  ADD CONSTRAINT `renovacion_ibfk_1` FOREIGN KEY (`membresia`) REFERENCES `membresia` (`numero`);

--
-- Filtros para la tabla `requerimiento`
--
ALTER TABLE `requerimiento`
  ADD CONSTRAINT `requerimiento_ibfk_1` FOREIGN KEY (`vacante`) REFERENCES `vacante` (`numero`);

--
-- Filtros para la tabla `responsabilidades`
--
ALTER TABLE `responsabilidades`
  ADD CONSTRAINT `responsabilidades_ibfk_1` FOREIGN KEY (`experiencia`) REFERENCES `experiencia` (`numero`);

--
-- Filtros para la tabla `solicitud`
--
ALTER TABLE `solicitud`
  ADD CONSTRAINT `solicitud_ibfk_1` FOREIGN KEY (`prospecto`) REFERENCES `prospecto` (`numero`),
  ADD CONSTRAINT `solicitud_ibfk_2` FOREIGN KEY (`vacante`) REFERENCES `vacante` (`numero`),
  ADD CONSTRAINT `solicitud_ibfk_3` FOREIGN KEY (`estatus`) REFERENCES `estatus_solicitud` (`codigo`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`rol`) REFERENCES `rol` (`codigo`);

--
-- Filtros para la tabla `vacante`
--
ALTER TABLE `vacante`
  ADD CONSTRAINT `vacante_ibfk_1` FOREIGN KEY (`tipo_contrato`) REFERENCES `tipo_contrato` (`codigo`),
  ADD CONSTRAINT `vacante_ibfk_2` FOREIGN KEY (`empresa`) REFERENCES `empresa` (`numero`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
