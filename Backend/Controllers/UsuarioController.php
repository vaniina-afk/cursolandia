<?php
require_once __DIR__ . '/../Models/UsuarioModel.php';

class UsuarioController {
    private $usuarioModel;
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    // Carga las variables de la campanita de notificaciones (puente temporal)
    private function cargarNotificaciones() {
        if (isset($_SESSION['id_usuario'])) {
            require_once __DIR__ . '/../Models/NotificacionModel.php';
            $notiModel = new NotificacionModel($this->conexion);
            $id_usuario = (int)$_SESSION['id_usuario'];
            return [
                'no_leidas' => $notiModel->contarNoLeidas($id_usuario),
                'result_noti' => $notiModel->obtenerUltimasPorUsuario($id_usuario, 5)
            ];
        }
        return [
            'no_leidas' => 0,
            'result_noti' => null
        ];
    }

    // 1. Editar Perfil y Contraseña
    public function editarPerfil() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php?accion=login");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $mensaje_perfil = "";
        $mensaje_pass = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // CASO 1: Actualizar Datos Personales y Foto
            if (isset($_POST['actualizar_datos'])) {
                $nombre = trim($_POST['nombre']);
                $apellido = trim($_POST['apellido']);
                $email = trim($_POST['email']);
                $antecedentes = trim($_POST['antecedentes']);
                $nueva_imagen = null;

                if (isset($_FILES['imagen_perfil']) && $_FILES['imagen_perfil']['error'] == UPLOAD_ERR_OK) {
                    $file_tmp = $_FILES['imagen_perfil']['tmp_name'];
                    $file_ext = strtolower(pathinfo($_FILES['imagen_perfil']['name'], PATHINFO_EXTENSION));
                    $permitidos = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (in_array($file_ext, $permitidos)) {
                        $nuevo_nombre = "perfil_" . $id_usuario . "_" . time() . "." . $file_ext;
                        $carpeta_destino = __DIR__ . '/../../Resources/uploads/';

                        if (!is_dir($carpeta_destino)) {
                            mkdir($carpeta_destino, 0777, true);
                        }

                        if (move_uploaded_file($file_tmp, $carpeta_destino . $nuevo_nombre)) {
                            $nueva_imagen = $nuevo_nombre;
                        } else {
                            $mensaje_perfil = "<div class='alert alert-warning p-2 small mb-3 auto-ocultar'>No se pudo guardar la imagen. Verificá los permisos de Resources/uploads.</div>";
                        }
                    } else {
                        $mensaje_perfil = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Formato de imagen no permitido. Usá JPG, PNG o GIF.</div>";
                    }
                }

                if ($this->usuarioModel->actualizarPerfil($id_usuario, $nombre, $apellido, $email, $antecedentes, $nueva_imagen)) {
                    $_SESSION['nombre'] = $nombre;
                    if (empty($mensaje_perfil)) {
                        $mensaje_perfil = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Datos del perfil actualizados correctamente.</div>";
                    }
                } else {
                    $mensaje_perfil = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Hubo un error al actualizar los datos en la base de datos.</div>";
                }
            }

            // CASO 2: Actualizar Contraseña
            if (isset($_POST['actualizar_password'])) {
                $pass_actual = trim($_POST['pass_actual']);
                $pass_nueva = trim($_POST['pass_nueva']);
                $pass_confirmar = trim($_POST['pass_confirmar']);

                if ($pass_nueva !== $pass_confirmar) {
                    $mensaje_pass = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Las contraseñas nuevas no coinciden.</div>";
                } elseif (strlen($pass_nueva) < 6) {
                    $mensaje_pass = "<div class='alert alert-warning p-2 small mb-3 auto-ocultar'>La nueva contraseña debe tener al menos 6 caracteres.</div>";
                } else {
                    $hash_actual = $this->usuarioModel->obtenerPasswordHash($id_usuario);
                    if ($hash_actual && password_verify($pass_actual, $hash_actual)) {
                        $hash_nuevo = password_hash($pass_nueva, PASSWORD_DEFAULT);
                        $this->usuarioModel->actualizarPassword($id_usuario, $hash_nuevo);
                        $mensaje_pass = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Contraseña actualizada con éxito.</div>";
                    } else {
                        $mensaje_pass = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>La contraseña actual es incorrecta.</div>";
                    }
                }
            }
        }

        $datos_usuario = $this->usuarioModel->obtenerPorId($id_usuario);
        require_once __DIR__ . '/../../Frontend/views/perfil/editar_perfil.php';
    }

    // 2. Solicitar Plan PRO
    public function solicitarPro() {
        if (!isset($_SESSION['id_usuario'])) {
            header("Location: index.php?accion=login");
            exit();
        }

        $id_usuario = (int)$_SESSION['id_usuario'];
        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];

        $mensaje_ui = "";
        $puede_solicitar = true;

        if ($_SESSION['plan_pro'] == 1) {
            $mensaje_ui = "<div class='alert alert-info p-3 text-center shadow-sm'>¡Ya sos un usuario PRO! Tenés acceso a todas las funciones sin límite.</div>";
            $puede_solicitar = false;
        } elseif ($this->usuarioModel->tieneSolicitudProPendiente($id_usuario)) {
            $mensaje_ui = "<div class='alert alert-warning p-3 text-center shadow-sm'>Ya tenés una solicitud en revisión. Por favor, esperá a que un administrador la evalúe.</div>";
            $puede_solicitar = false;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && $puede_solicitar) {
            $mensaje_texto = trim($_POST['mensaje']);

            if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] == 0) {
                $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
                $nombre_imagen = "comprobante_" . time() . "_" . uniqid() . "." . $extension;
                $carpeta_destino = __DIR__ . '/../../Resources/uploads/';

                if (!is_dir($carpeta_destino)) {
                    mkdir($carpeta_destino, 0777, true);
                }

                if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $carpeta_destino . $nombre_imagen)) {
                    if ($this->usuarioModel->crearSolicitudPro($id_usuario, $mensaje_texto, $nombre_imagen)) {
                        $mensaje_ui = "<div class='alert alert-success p-3 text-center shadow-sm'>¡Solicitud enviada con éxito! Un administrador la revisará pronto.</div>";
                        $puede_solicitar = false;
                    } else {
                        $mensaje_ui = "<div class='alert alert-danger p-2 small mb-3'>Error al guardar la solicitud en la base de datos.</div>";
                    }
                }
            } else {
                $mensaje_ui = "<div class='alert alert-danger p-2 small mb-3 auto-ocultar'>Por favor, adjuntá un comprobante válido.</div>";
            }
        }

        require_once __DIR__ . '/../../Frontend/views/perfil/solicitar_pro.php';
    }

    // 3. Administrar Solicitudes PRO (Solo Admin)
    public function adminSolicitudes() {
        if (!isset($_SESSION['id_usuario']) || $_SESSION['es_admin'] == 0) {
            header("Location: index.php");
            exit();
        }

        $notis = $this->cargarNotificaciones();
        $no_leidas = $notis['no_leidas'];
        $result_noti = $notis['result_noti'];
        $mensaje_ui = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['decision'])) {
            $id_solicitud = (int)$_POST['id_solicitud'];
            $id_user_solicitante = (int)$_POST['id_usuario_solicitante'];
            $decision = $_POST['decision'];

            if ($decision == 'aprobar') {
                $this->usuarioModel->aprobarSolicitudPro($id_solicitud, $id_user_solicitante);
                $mensaje_ui = "<div class='alert alert-success p-2 small mb-3 auto-ocultar'>Solicitud aprobada. El usuario ahora es PRO.</div>";
            } elseif ($decision == 'rechazar') {
                $this->usuarioModel->rechazarSolicitudPro($id_solicitud);
                $mensaje_ui = "<div class='alert alert-secondary p-2 small mb-3 auto-ocultar'>Solicitud rechazada.</div>";
            }
        }

        $solicitudes = $this->usuarioModel->obtenerSolicitudesProPendientes();
        require_once __DIR__ . '/../../Frontend/views/perfil/admin_solicitudes.php';
    }
}