DROP DATABASE IF EXISTS cursolandia;
CREATE DATABASE cursolandia CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE cursolandia;

-- 1. TABLAS DE USUARIOS Y PERFILES
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    antecedentes TEXT,
    imagen_perfil VARCHAR(255) DEFAULT 'default.png',
    es_admin BOOLEAN DEFAULT FALSE,
    plan_pro BOOLEAN DEFAULT FALSE,
    vencimiento_pro DATE NULL
);

CREATE TABLE Intereses (
    id_interes INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Usuario_Interes (
    id_usuario INT,
    id_interes INT,
    PRIMARY KEY (id_usuario, id_interes),
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    FOREIGN KEY (id_interes) REFERENCES Intereses(id_interes) ON DELETE CASCADE
);

CREATE TABLE Solicitudes_Pro (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    comprobante_url VARCHAR(255) NOT NULL,
    estado ENUM('Pendiente', 'Aprobada', 'Rechazada') DEFAULT 'Pendiente',
    fecha_solicitud DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

-- 2. TABLAS DE CURSOS
CREATE TABLE Cursos (
    id_curso INT AUTO_INCREMENT PRIMARY KEY,
    id_creador INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    costo DECIMAL(10,2) NULL,
    cupo INT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_creador) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE,
    CONSTRAINT chk_costo CHECK (costo >= 0),
    CONSTRAINT chk_cupo CHECK (cupo > 0),
    CONSTRAINT chk_fechas CHECK (fecha_fin >= fecha_inicio)
);

CREATE TABLE Etiquetas (
    id_etiqueta INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) UNIQUE NOT NULL
);

CREATE TABLE Curso_Etiqueta (
    id_curso INT,
    id_etiqueta INT,
    PRIMARY KEY (id_curso, id_etiqueta),
    FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_etiqueta) REFERENCES Etiquetas(id_etiqueta) ON DELETE CASCADE
);

CREATE TABLE Materiales (
    id_material INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    nombre_archivo VARCHAR(150) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    tamanio_mb DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE,
    CONSTRAINT chk_tamanio CHECK (tamanio_mb <= 5.00)
);

-- 3. INSCRIPCIONES
CREATE TABLE Inscripciones (
    id_inscripcion INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_usuario INT NOT NULL,
    estado ENUM('Pendiente', 'Aceptado', 'Rechazado') DEFAULT 'Aceptado',
    fecha_inscripcion DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (id_curso, id_usuario),
    FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

-- 4. FORO Y NOTIFICACIONES
CREATE TABLE Preguntas (
    id_pregunta INT AUTO_INCREMENT PRIMARY KEY,
    id_curso INT NOT NULL,
    id_usuario INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    cuerpo TEXT NOT NULL,
    estado ENUM('Abierta', 'Cerrada') DEFAULT 'Abierta',
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_curso) REFERENCES Cursos(id_curso) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE Respuestas (
    id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
    id_pregunta INT NOT NULL,
    id_usuario INT NOT NULL,
    cuerpo TEXT NOT NULL,
    es_destacada BOOLEAN DEFAULT FALSE,
    fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_pregunta) REFERENCES Preguntas(id_pregunta) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

CREATE TABLE Notificaciones (
    id_notificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    mensaje TEXT NOT NULL,
    leida BOOLEAN DEFAULT FALSE,
    fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

-- Insertamos usuarios de prueba
-- Nota para el profesor: La contraseña de los usuarios de prueba es 123456
INSERT INTO Usuarios (nombre, apellido, email, contrasena, es_admin) 
VALUES ('Admin', 'Sistema', 'admin@cursolandia.com', '$2y$10$WkBXvAEkjTbEVSbeqLwbVuNEZNqFUEEvNWoYl9VIal40Aywn/Nfsq', TRUE);

INSERT INTO Usuarios (nombre, apellido, email, contrasena) 
VALUES ('Alumno', 'Prueba', 'alumno@mail.com', '$2y$10$WkBXvAEkjTbEVSbeqLwbVuNEZNqFUEEvNWoYl9VIal40Aywn/Nfsq');