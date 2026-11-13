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

    // Obtiene los datos completos de un usuario por su ID
    public function obtenerPorId($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT id_usuario, nombre, apellido, email, antecedentes, imagen_perfil, plan_pro, vencimiento_pro FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $usuario = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $usuario;
    }

    // Actualiza datos personales (con o sin cambio de foto de perfil)
    public function actualizarPerfil($id_usuario, $nombre, $apellido, $email, $antecedentes, $nueva_imagen = null) {
        if ($nueva_imagen !== null) {
            $stmt = $this->conexion->prepare("UPDATE Usuarios SET nombre = ?, apellido = ?, email = ?, antecedentes = ?, imagen_perfil = ? WHERE id_usuario = ?");
            $stmt->bind_param("sssssi", $nombre, $apellido, $email, $antecedentes, $nueva_imagen, $id_usuario);
        } else {
            $stmt = $this->conexion->prepare("UPDATE Usuarios SET nombre = ?, apellido = ?, email = ?, antecedentes = ? WHERE id_usuario = ?");
            $stmt->bind_param("ssssi", $nombre, $apellido, $email, $antecedentes, $id_usuario);
        }

        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }

    // Obtiene el hash de la contraseña actual para validarla
    public function obtenerPasswordHash($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT contrasena FROM Usuarios WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        return $fila ? $fila['contrasena'] : null;
    }

    // Actualiza la contraseña encriptada
    public function actualizarPassword($id_usuario, $hash_nuevo) {
        $stmt = $this->conexion->prepare("UPDATE Usuarios SET contrasena = ? WHERE id_usuario = ?");
        $stmt->bind_param("si", $hash_nuevo, $id_usuario);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // Verifica si el usuario ya tiene una solicitud PRO en estado Pendiente
    public function tieneSolicitudProPendiente($id_usuario) {
        $stmt = $this->conexion->prepare("SELECT id_solicitud FROM Solicitudes_Pro WHERE id_usuario = ? AND estado = 'Pendiente'");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $tiene = $stmt->get_result()->num_rows > 0;
        $stmt->close();

        return $tiene;
    }

    // Crea una nueva solicitud para pasar a Plan PRO
    public function crearSolicitudPro($id_usuario, $mensaje, $comprobante_url) {
        $stmt = $this->conexion->prepare("INSERT INTO Solicitudes_Pro (id_usuario, mensaje, comprobante_url) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_usuario, $mensaje, $comprobante_url);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // Trae todas las solicitudes PRO pendientes (para el Administrador)
    public function obtenerSolicitudesProPendientes() {
        $sql = "SELECT s.*, u.nombre, u.apellido, u.email 
                FROM Solicitudes_Pro s 
                JOIN Usuarios u ON s.id_usuario = u.id_usuario 
                WHERE s.estado = 'Pendiente' 
                ORDER BY s.fecha_solicitud ASC";
        return $this->conexion->query($sql);
    }

    // Aprueba una solicitud y activa el Plan PRO con fecha de vencimiento
    public function aprobarSolicitudPro($id_solicitud, $id_usuario_solicitante, $fecha_vencimiento) {
        $stmt1 = $this->conexion->prepare("UPDATE Solicitudes_Pro SET estado = 'Aprobada' WHERE id_solicitud = ?");
        $stmt1->bind_param("i", $id_solicitud);
        $stmt1->execute();
        $stmt1->close();

        $stmt2 = $this->conexion->prepare("UPDATE Usuarios SET plan_pro = 1, vencimiento_pro = ? WHERE id_usuario = ?");
        $stmt2->bind_param("si", $fecha_vencimiento, $id_usuario_solicitante);
        $exito = $stmt2->execute();
        $stmt2->close();

        return $exito;
    }

    // Trae todos los usuarios que actualmente tienen Plan PRO activo
    public function obtenerUsuariosPro() {
        $sql = "SELECT id_usuario, nombre, apellido, email, vencimiento_pro 
                FROM Usuarios 
                WHERE plan_pro = 1 
                ORDER BY vencimiento_pro ASC";
        return $this->conexion->query($sql);
    }

    // Le quita el Plan PRO a un usuario (lo vuelve a Gratuito)
    public function revocarPlanPro($id_usuario_target) {
        $stmt = $this->conexion->prepare("UPDATE Usuarios SET plan_pro = 0, vencimiento_pro = NULL WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario_target);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // Rechaza una solicitud PRO
    public function rechazarSolicitudPro($id_solicitud) {
        $stmt = $this->conexion->prepare("UPDATE Solicitudes_Pro SET estado = 'Rechazada' WHERE id_solicitud = ?");
        $stmt->bind_param("i", $id_solicitud);
        $exito = $stmt->execute();
        $stmt->close();

        return $exito;
    }

    // --- MÉTODOS PARA INTERESES DEL USUARIO ---

    public function obtenerTodosLosIntereses() {
        $sql = "SELECT id_interes, nombre FROM intereses ORDER BY nombre ASC";
        return $this->conexion->query($sql);
    }

    public function obtenerIdsInteresesUsuario($id_usuario) {
        $ids = [];
        $stmt = $this->conexion->prepare("SELECT id_interes FROM usuario_interes WHERE id_usuario = ?");
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($fila = $res->fetch_assoc()) {
            $ids[] = (int)$fila['id_interes'];
        }
        $stmt->close();
        return $ids;
    }

    public function guardarInteresesUsuario($id_usuario, $ids_intereses = [], $nuevos_intereses_texto = '') {
        // 1. Limpiamos intereses previos del usuario
        $stmt_del = $this->conexion->prepare("DELETE FROM usuario_interes WHERE id_usuario = ?");
        $stmt_del->bind_param("i", $id_usuario);
        $stmt_del->execute();
        $stmt_del->close();

        $ids_finales = array_map('intval', $ids_intereses);

        // 2. Si escribió intereses nuevos separados por coma, los creamos si no existen
        if (trim($nuevos_intereses_texto) !== '') {
            $partes = explode(',', $nuevos_intereses_texto);
            foreach ($partes as $nombre_raw) {
                $nombre = trim($nombre_raw);
                if ($nombre === '') continue;

                $stmt_busca = $this->conexion->prepare("SELECT id_interes FROM intereses WHERE LOWER(nombre) = LOWER(?)");
                $stmt_busca->bind_param("s", $nombre);
                $stmt_busca->execute();
                $existente = $stmt_busca->get_result()->fetch_assoc();
                $stmt_busca->close();

                if ($existente) {
                    $ids_finales[] = (int)$existente['id_interes'];
                } else {
                    $stmt_ins = $this->conexion->prepare("INSERT INTO intereses (nombre) VALUES (?)");
                    $stmt_ins->bind_param("s", $nombre);
                    if ($stmt_ins->execute()) {
                        $ids_finales[] = (int)$this->conexion->insert_id;
                    }
                    $stmt_ins->close();
                }
            }
        }

        // 3. Guardamos en usuario_interes sin duplicados
        $ids_finales = array_unique($ids_finales);
        if (!empty($ids_finales)) {
            $stmt_ui = $this->conexion->prepare("INSERT INTO usuario_interes (id_usuario, id_interes) VALUES (?, ?)");
            foreach ($ids_finales as $id_int) {
                if ($id_int > 0) {
                    $stmt_ui->bind_param("ii", $id_usuario, $id_int);
                    $stmt_ui->execute();
                }
            }
            $stmt_ui->close();
        }
    }

    // --- NOTIFICACIONES PARA SOLICITUDES PRO ---

    public function notificarAdministradores($mensaje, $enlace) {
        $res_admins = $this->conexion->query("SELECT id_usuario FROM Usuarios WHERE es_admin = 1");
        if ($res_admins && $res_admins->num_rows > 0) {
            $stmt = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES (?, ?, ?, 0)");
            while ($admin = $res_admins->fetch_assoc()) {
                $id_admin = (int)$admin['id_usuario'];
                $stmt->bind_param("iss", $id_admin, $mensaje, $enlace);
                $stmt->execute();
            }
            $stmt->close();
        }
    }

    public function notificarUsuario($id_usuario_destino, $mensaje, $enlace) {
        $stmt = $this->conexion->prepare("INSERT INTO notificaciones (id_usuario, mensaje, enlace, leida) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("iss", $id_usuario_destino, $mensaje, $enlace);
        $exito = $stmt->execute();
        $stmt->close();
        return $exito;
    }
}