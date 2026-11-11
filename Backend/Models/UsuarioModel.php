<?php
class UsuarioModel {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    // Busca un usuario por su correo (para el Login)
    public function obtenerPorEmail($email) {
        $stmt = $this->conexion->prepare("SELECT id_usuario, nombre, apellido, contrasena, es_admin, plan_pro FROM Usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();

        return $usuario; // Devuelve el array del usuario o null si no existe
    }

    // Verifica si un email ya está en uso (para el Registro)
    public function existeEmail($email) {
        $stmt = $this->conexion->prepare("SELECT id_usuario FROM Usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $existe = $resultado->num_rows > 0;
        $stmt->close();

        return $existe;
    }

    // Inserta un usuario nuevo en la base de datos
    public function registrar($nombre, $apellido, $email, $contrasena_hash, $antecedentes, $imagen_perfil) {
        $stmt = $this->conexion->prepare("INSERT INTO Usuarios (nombre, apellido, email, contrasena, antecedentes, imagen_perfil) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $apellido, $email, $contrasena_hash, $antecedentes, $imagen_perfil);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }
}