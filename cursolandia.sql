-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-11-2026 a las 01:54:09
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
-- Base de datos: `cursolandia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cursos`
--

CREATE TABLE `cursos` (
  `id_curso` int(11) NOT NULL,
  `id_creador` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `cupo` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `es_privado` tinyint(1) NOT NULL DEFAULT 0
) ;

--
-- Volcado de datos para la tabla `cursos`
--

INSERT INTO `cursos` (`id_curso`, `id_creador`, `titulo`, `descripcion`, `costo`, `cupo`, `fecha_inicio`, `fecha_fin`, `activo`, `es_privado`) VALUES
(6, 2, 'Curso de Programación', 'aja1', NULL, 3, '2026-10-02', '2026-10-04', 0, 0),
(7, 2, 'Curso de Pastelería', 'aja2', 5000.00, 20, '2026-10-05', '2026-10-09', 0, 0),
(8, 2, 'Curso de Ajedrez', 'aja3', NULL, 51, '2026-10-07', '2026-10-10', 0, 0),
(9, 3, 'Curso de Programación', 'aja3', NULL, 4, '2026-10-27', '2026-11-07', 0, 0),
(10, 3, 'curso de celulares', 'lalgo', NULL, 1, '2026-10-06', '2026-10-14', 0, 0),
(11, 6, 'Curso de Diseño Web', 'Aprende a crear sitios web atractivos, modernos y adaptables a cualquier pantalla desde cero.', NULL, 3, '2026-10-10', '2050-10-12', 1, 0),
(12, 3, 'curso de celulares', 'asdasd', NULL, 1, '2026-11-27', '2026-12-02', 0, 0),
(13, 3, 'Curso de Programación', 'Enseña a crear instrucciones lógicas para que las computadoras ejecuten tareas, desarrollen aplicaciones o resuelvan problemas mediante un lenguaje de código.', 5000.00, 0, '2026-11-09', '2026-11-25', 1, 0),
(14, 3, 'Curso de Ajedrez', 'asda', NULL, NULL, '2026-11-29', '2026-12-02', 0, 0),
(15, 3, 'Curso de Diseño Gráfico', 'Formación práctica y teórica que enseña a comunicar mensajes, ideas y conceptos a través de elementos visuales.', NULL, 2, '2026-11-12', '2026-11-21', 1, 0),
(16, 3, 'Curso de Pastelería', '132', NULL, 3, '2026-12-02', '2026-12-03', 0, 0),
(18, 3, 'Curso de Inglés', 'Aprende a comunicarte con fluidez en situaciones reales desde la primera clase.', NULL, 3, '2026-11-11', '2026-11-13', 1, 0),
(19, 3, 'Curso de Marketing Digital', 'Enseña a promocionar marcas, productos o servicios a través de canales en línea.', NULL, 2, '2026-11-16', '2026-11-17', 1, 0),
(20, 2, 'Curso de Matemáticas', 'Domina los conceptos clave de la matemática de forma clara, práctica y paso a paso.', NULL, NULL, '2026-11-11', '2026-11-22', 1, 1),
(21, 2, 'Curso de Base de Datos', 'Aprende a diseñar, gestionar y consultar bases de datos relacionales desde cero.', 4999.82, 3, '2026-11-10', '2026-11-23', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `curso_etiqueta`
--

CREATE TABLE `curso_etiqueta` (
  `id_curso` int(11) NOT NULL,
  `id_etiqueta` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `curso_etiqueta`
--

INSERT INTO `curso_etiqueta` (`id_curso`, `id_etiqueta`) VALUES
(11, 2),
(13, 1),
(14, 8),
(15, 7),
(16, 6),
(18, 5),
(19, 6),
(20, 4),
(21, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `etiquetas`
--

CREATE TABLE `etiquetas` (
  `id_etiqueta` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `etiquetas`
--

INSERT INTO `etiquetas` (`id_etiqueta`, `nombre`) VALUES
(3, 'Bases de Datos'),
(9, 'Cocina'),
(7, 'Diseño Gráfico'),
(2, 'Diseño Web'),
(5, 'Inglés'),
(6, 'Marketing Digital'),
(4, 'Matemática'),
(8, 'Ofimática'),
(1, 'Programación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripciones`
--

CREATE TABLE `inscripciones` (
  `id_inscripcion` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `estado` varchar(20) NOT NULL DEFAULT 'Aceptado',
  `fecha_inscripcion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inscripciones`
--

INSERT INTO `inscripciones` (`id_inscripcion`, `id_curso`, `id_usuario`, `estado`, `fecha_inscripcion`) VALUES
(11, 10, 2, 'Aceptado', '2026-10-05 20:19:20'),
(19, 13, 2, 'Aceptado', '2026-11-11 19:17:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intereses`
--

CREATE TABLE `intereses` (
  `id_interes` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `intereses`
--

INSERT INTO `intereses` (`id_interes`, `nombre`) VALUES
(3, 'Bases de Datos'),
(9, 'Cocina'),
(7, 'Diseño Gráfico'),
(2, 'Diseño Web'),
(5, 'Inglés'),
(6, 'Marketing Digital'),
(4, 'Matemática'),
(8, 'Ofimática'),
(1, 'Programación');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id_material` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `nombre_archivo` varchar(150) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tamanio_mb` decimal(5,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id_material`, `id_curso`, `nombre_archivo`, `ruta_archivo`, `tamanio_mb`) VALUES
(3, 9, 'Diapositivas Clase 1', 'mat_9_1790989408_6ac05460c6b19.pdf', 0.88),
(4, 6, 'Diapositivas Clase 1', 'mat_6_1791214255_6ac3c2af4c65c.pdf', 0.88),
(16, 13, 'Diapositivas Clase 1', 'mat_13_1794696195_6af8e4034f25b.pdf', 10.49),
(17, 13, 'Diapositivas Clase 2', 'mat_13_1794696262_6af8e44604f4c.pdf', 0.89),
(18, 13, 'Diapositivas Clase 3', 'mat_13_1794696270_6af8e44e0ee03.pdf', 0.71),
(19, 13, 'Diapositivas Clase 4', 'mat_13_1794696282_6af8e45a6c6d5.pdf', 0.53),
(20, 13, 'Diapositivas Clase 5', 'mat_13_1794696292_6af8e46418780.pdf', 0.11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `mensaje`, `enlace`, `leida`, `fecha`) VALUES
(1, 3, 'Alumno Prueba ha respondido a tu consulta en el foro.', NULL, 1, '2026-10-05 13:24:39'),
(2, 3, 'alumno2 bla ha respondido a tu consulta en el foro.', NULL, 1, '2026-10-05 13:25:35'),
(3, 3, 'Alumno Prueba ha respondido a tu consulta en el foro.', 'pregunta_foro.php?id=1', 1, '2026-10-05 18:27:40'),
(4, 2, 'Vanina Muñoz ha respondido a tu consulta en el foro.', 'pregunta_foro.php?id=2', 1, '2026-10-05 20:30:07'),
(5, 3, 'Bau Dones ha respondido a tu consulta en el foro.', 'pregunta_foro.php?id=4', 1, '2026-10-08 18:19:33'),
(6, 6, 'Vaniina Muñoz publicó una nueva consulta en tu curso \'Creacion de alfajores artesanales\'.', 'index.php?accion=pregunta_foro&id=6', 1, '2026-11-11 17:42:04'),
(7, 6, 'Vaniina Muñoz ha dejado una respuesta en el foro de tu curso.', 'index.php?accion=pregunta_foro&id=6', 1, '2026-11-11 17:42:24'),
(8, 3, 'Bau Dones ha respondido a tu consulta en el foro.', 'index.php?accion=pregunta_foro&id=6', 1, '2026-11-11 17:45:03'),
(9, 6, 'Vaniina Muñoz publicó una nueva consulta en tu curso \'Creacion de alfajores artesanales\'.', 'index.php?accion=pregunta_foro&id=8', 1, '2026-11-11 18:48:17'),
(10, 3, 'Alumno Prueba publicó una nueva consulta en tu curso \'Curso de Pastelería\'.', 'index.php?accion=pregunta_foro&id=9', 1, '2026-11-11 19:20:42'),
(11, 3, 'Alumno Prueba ha dejado una respuesta en el foro de tu curso.', 'index.php?accion=pregunta_foro&id=9', 0, '2026-11-11 19:20:48'),
(12, 3, 'Alumno Prueba ha dejado una respuesta en el foro de tu curso.', 'index.php?accion=pregunta_foro&id=9', 0, '2026-11-11 19:20:50'),
(13, 3, 'Alumno Prueba ha dejado una respuesta en el foro de tu curso.', 'index.php?accion=pregunta_foro&id=9', 0, '2026-11-11 19:20:53'),
(14, 1, '⭐ Alumno Prueba ha enviado una solicitud para obtener el Plan PRO.', 'index.php?accion=admin_solicitudes', 1, '2026-11-11 20:54:16'),
(15, 2, '🎉 ¡Tu solicitud de Plan PRO fue aprobada! Ya podés crear cursos ilimitados y destacar en el catálogo.', 'index.php?accion=panel', 1, '2026-11-11 20:56:40'),
(16, 2, 'Tu suscripción al Plan PRO ha finalizado y tu cuenta volvió al plan Gratuito.', 'index.php?accion=solicitar_pro', 1, '2026-11-13 13:24:34'),
(17, 3, 'Tu suscripción al Plan PRO ha finalizado y tu cuenta volvió al plan Gratuito.', 'index.php?accion=solicitar_pro', 0, '2026-11-13 13:24:41'),
(18, 1, '⭐ Vaniina Muñoz ha enviado una solicitud para obtener el Plan PRO.', 'index.php?accion=admin_solicitudes', 1, '2026-11-13 13:25:26'),
(19, 3, '🎉 ¡Tu solicitud de Plan PRO fue aprobada! Tenés beneficios PRO vigentes hasta el 14/01/2027.', 'index.php?accion=editar_perfil', 1, '2026-11-13 13:26:01'),
(21, 3, '📩 alumno2 bla solicitó inscribirse a tu curso pago \'hola\'.', 'index.php?accion=curso_interno&id=15', 0, '2026-11-13 14:27:09'),
(24, 3, '📩 alumno 3 solicitó inscribirse a tu curso pago \'hola\'.', 'index.php?accion=curso_interno&id=15', 0, '2026-11-13 19:31:52'),
(25, 3, '📩 alumno 3 solicitó inscribirse a tu curso pago \'Curso de Pastelería\'.', 'index.php?accion=curso_interno&id=16', 1, '2026-11-13 19:34:40'),
(28, 6, '📩 alumno 3 solicitó inscribirse a tu curso pago \'Creacion de alfajores artesanales\'.', 'index.php?accion=curso_interno&id=11', 1, '2026-11-13 19:36:56'),
(31, 6, '📩 alumno2 bla solicitó inscribirse a tu curso pago \'Creacion de alfajores artesanales\'.', 'index.php?accion=curso_interno&id=11', 0, '2026-11-13 19:54:11'),
(32, 2, '💬 alumno 3 respondió en la consulta \'Curso de Ajedrez\'.', 'index.php?accion=pregunta_foro&id=9', 1, '2026-11-14 20:03:11'),
(33, 2, '💬 alumno 3 respondió en la consulta \'Curso de Ajedrez\'.', 'index.php?accion=pregunta_foro&id=9', 1, '2026-11-14 20:04:24'),
(36, 1, '⭐ Alumno Prueba ha enviado una solicitud para obtener el Plan PRO.', 'index.php?accion=admin_solicitudes', 1, '2026-11-15 14:43:27'),
(37, 2, 'Tu solicitud de Plan PRO fue rechazada. Podés revisar los requisitos o volver a enviarla.', 'index.php?accion=solicitar_pro', 1, '2026-11-15 14:44:09'),
(38, 3, '💬 Alumno Prueba publicó una nueva consulta en \'Curso de Programación\': \"Paro de hoy\".', 'index.php?accion=pregunta_foro&id=10', 1, '2026-11-15 21:19:32'),
(39, 2, '💬 Vaniina Muñoz respondió en la consulta \'Paro de hoy\'.', 'index.php?accion=pregunta_foro&id=10', 0, '2026-11-15 21:19:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas`
--

CREATE TABLE `preguntas` (
  `id_pregunta` int(11) NOT NULL,
  `id_curso` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `cuerpo` text NOT NULL,
  `estado` enum('Abierta','Cerrada') DEFAULT 'Abierta',
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `cerrada` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `preguntas`
--

INSERT INTO `preguntas` (`id_pregunta`, `id_curso`, `id_usuario`, `titulo`, `cuerpo`, `estado`, `fecha_creacion`, `cerrada`) VALUES
(1, 6, 3, 'Duda sobre ejercicio 1', 'pdf o word', 'Abierta', '2026-10-05 13:06:57', 0),
(4, 10, 3, 'Aviso', 'mañana no hay clases', 'Abierta', '2026-10-07 14:52:45', 1),
(9, 16, 2, 'Curso de Ajedrez', 'dadsa', '', '2026-11-11 19:20:42', 1),
(10, 13, 2, 'Paro de hoy', '¿Hoy habrá clases?', 'Abierta', '2026-11-15 21:19:32', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas`
--

CREATE TABLE `respuestas` (
  `id_respuesta` int(11) NOT NULL,
  `id_pregunta` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `cuerpo` text NOT NULL,
  `es_destacada` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `editada` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `respuestas`
--

INSERT INTO `respuestas` (`id_respuesta`, `id_pregunta`, `id_usuario`, `cuerpo`, `es_destacada`, `fecha_creacion`, `editada`) VALUES
(1, 1, 2, 'pdf', 1, '2026-10-05 13:24:39', 0),
(3, 1, 2, 'no, pdf', 1, '2026-10-05 18:27:40', 0),
(6, 4, 6, 'si', 1, '2026-10-08 18:19:33', 0),
(16, 9, 2, 'aja', 0, '2026-11-14 20:05:21', 0),
(18, 10, 3, 'Sí.', 0, '2026-11-15 21:19:58', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_pro`
--

CREATE TABLE `solicitudes_pro` (
  `id_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `comprobante_url` varchar(255) NOT NULL,
  `estado` enum('Pendiente','Aprobada','Rechazada') DEFAULT 'Pendiente',
  `fecha_solicitud` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitudes_pro`
--

INSERT INTO `solicitudes_pro` (`id_solicitud`, `id_usuario`, `mensaje`, `comprobante_url`, `estado`, `fecha_solicitud`) VALUES
(3, 6, 'adada', 'comprobante_1791494405_6ac8090550e9b.png', 'Rechazada', '2026-10-08 18:20:05'),
(4, 3, 'aha', 'comprobante_1794432085_6af4dc551fac0.png', 'Aprobada', '2026-11-11 18:21:25'),
(5, 2, 'asdad', 'comprobante_1794441256_6af500283dc71.jpg', 'Aprobada', '2026-11-11 20:54:16'),
(6, 3, 'qpa', 'comprobante_1794587126_6af739f6a3564.jpg', 'Aprobada', '2026-11-13 13:25:26'),
(7, 2, 'alo', 'comprobante_1794764607_6af9ef3f55e48.webp', 'Rechazada', '2026-11-15 14:43:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `antecedentes` text DEFAULT NULL,
  `imagen_perfil` varchar(255) DEFAULT 'default.png',
  `es_admin` tinyint(1) DEFAULT 0,
  `plan_pro` tinyint(1) DEFAULT 0,
  `vencimiento_pro` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `apellido`, `email`, `contrasena`, `antecedentes`, `imagen_perfil`, `es_admin`, `plan_pro`, `vencimiento_pro`) VALUES
(1, 'Admin', 'Sistema', 'admin@cursolandia.com', '$2y$10$WkBXvAEkjTbEVSbeqLwbVuNEZNqFUEEvNWoYl9VIal40Aywn/Nfsq', NULL, 'default.png', 1, 0, NULL),
(2, 'Alumno', 'Prueba', 'alumno@mail.com', '$2y$10$WkBXvAEkjTbEVSbeqLwbVuNEZNqFUEEvNWoYl9VIal40Aywn/Nfsq', '', 'perfil_2_1794763594.png', 0, 0, NULL),
(3, 'Vaniina', 'Muñoz', 'vaniinabelen@gmail.com', '$2y$10$UCNqWyF4zXTHyXPPX/cXjOGaFrUTYbCeoZ4S70SvbJRFqOFmzrKiS', '', 'perfil_3_1794762686.png', 0, 1, '2027-01-14'),
(6, 'Alumno2', 'Prueba', 'alumno2@mail.com', '$2y$10$UCNqWyF4zXTHyXPPX/cXjOGaFrUTYbCeoZ4S70SvbJRFqOFmzrKiS', '', 'perfil_6_1794787416.png', 0, 0, NULL),
(8, 'Usuario', 'Vista', 'usuario@mail.com', '$2y$10$WCO1qEuyRL185BtUmJGfW.bkblgdenFnlh53YttgZ3LMVavk0BZQC', '', '1794788004_6afa4aa4b18a2.jpeg', 0, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_interes`
--

CREATE TABLE `usuario_interes` (
  `id_usuario` int(11) NOT NULL,
  `id_interes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_interes`
--

INSERT INTO `usuario_interes` (`id_usuario`, `id_interes`) VALUES
(2, 1),
(2, 2),
(3, 1),
(3, 2),
(3, 6);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`id_curso`),
  ADD KEY `id_creador` (`id_creador`);

--
-- Indices de la tabla `curso_etiqueta`
--
ALTER TABLE `curso_etiqueta`
  ADD PRIMARY KEY (`id_curso`,`id_etiqueta`),
  ADD KEY `id_etiqueta` (`id_etiqueta`);

--
-- Indices de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  ADD PRIMARY KEY (`id_etiqueta`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD PRIMARY KEY (`id_inscripcion`),
  ADD UNIQUE KEY `id_curso` (`id_curso`,`id_usuario`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `intereses`
--
ALTER TABLE `intereses`
  ADD PRIMARY KEY (`id_interes`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id_material`),
  ADD KEY `id_curso` (`id_curso`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD PRIMARY KEY (`id_pregunta`),
  ADD KEY `id_curso` (`id_curso`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD PRIMARY KEY (`id_respuesta`),
  ADD KEY `id_pregunta` (`id_pregunta`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuario_interes`
--
ALTER TABLE `usuario_interes`
  ADD PRIMARY KEY (`id_usuario`,`id_interes`),
  ADD KEY `id_interes` (`id_interes`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `cursos`
--
ALTER TABLE `cursos`
  MODIFY `id_curso` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `etiquetas`
--
ALTER TABLE `etiquetas`
  MODIFY `id_etiqueta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  MODIFY `id_inscripcion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `intereses`
--
ALTER TABLE `intereses`
  MODIFY `id_interes` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id_material` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `preguntas`
--
ALTER TABLE `preguntas`
  MODIFY `id_pregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `respuestas`
--
ALTER TABLE `respuestas`
  MODIFY `id_respuesta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cursos`
--
ALTER TABLE `cursos`
  ADD CONSTRAINT `cursos_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `curso_etiqueta`
--
ALTER TABLE `curso_etiqueta`
  ADD CONSTRAINT `curso_etiqueta_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `Cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `curso_etiqueta_ibfk_2` FOREIGN KEY (`id_etiqueta`) REFERENCES `etiquetas` (`id_etiqueta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `inscripciones`
--
ALTER TABLE `inscripciones`
  ADD CONSTRAINT `inscripciones_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `Cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `inscripciones_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `materiales_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `Cursos` (`id_curso`) ON DELETE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `preguntas`
--
ALTER TABLE `preguntas`
  ADD CONSTRAINT `preguntas_ibfk_1` FOREIGN KEY (`id_curso`) REFERENCES `Cursos` (`id_curso`) ON DELETE CASCADE,
  ADD CONSTRAINT `preguntas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `respuestas`
--
ALTER TABLE `respuestas`
  ADD CONSTRAINT `respuestas_ibfk_1` FOREIGN KEY (`id_pregunta`) REFERENCES `preguntas` (`id_pregunta`) ON DELETE CASCADE,
  ADD CONSTRAINT `respuestas_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes_pro`
--
ALTER TABLE `solicitudes_pro`
  ADD CONSTRAINT `solicitudes_pro_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario_interes`
--
ALTER TABLE `usuario_interes`
  ADD CONSTRAINT `usuario_interes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `Usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `usuario_interes_ibfk_2` FOREIGN KEY (`id_interes`) REFERENCES `intereses` (`id_interes`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
