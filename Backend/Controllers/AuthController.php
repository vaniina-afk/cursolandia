<?php
require_once __DIR__ . '/../Models/UsuarioModel.php';

class AuthController {
    private $usuarioModel;

    public function __construct($conexion) {
        $this->usuarioModel = new UsuarioModel($conexion);
    }

    // Procesa y muestra el Login
    public function login() {
        if (isset($_SESSION['id_usuario'])) {
            header("Location: index.php");
            exit();
        }

        $mensaje = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = trim($_POST['email']);
            $contrasena_ingresada = $_POST['contrasena'];

            $usuario = $this->usuarioModel->obtenerPorEmail($email);

            if ($usuario) {
                if (password_verify($contrasena_ingresada, $usuario['contrasena'])) {
                    $_SESSION['id_usuario'] = $usuario['id_usuario'];
                    $_SESSION['nombre'] = $usuario['nombre'];
                    $_SESSION['apellido'] = $usuario['apellido'];
                    $_SESSION['es_admin'] = $usuario['es_admin'];
                    $_SESSION['plan_pro'] = $usuario['plan_pro'];

                    header("Location: index.php");
                    exit();
                } else {
                    $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Contraseña incorrecta.</div>";
                }
            } else {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>No existe una cuenta con este correo.</div>";
            }
        }

        require_once __DIR__ . '/../../Frontend/views/auth/login.php';
    }

    // Procesa y muestra el Registro
    public function registro() {
        if (isset($_SESSION['id_usuario'])) {
            header("Location: index.php");
            exit();
        }

        $mensaje = "";

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = trim($_POST['nombre']);
            $apellido = trim($_POST['apellido']);
            $email = trim($_POST['email']);
            $antecedentes = trim($_POST['antecedentes']);
            $contrasena_hash = password_hash($_POST['contrasena'], PASSWORD_DEFAULT);

            if ($this->usuarioModel->existeEmail($email)) {
                $mensaje = "<div class='alert alert-danger p-2 small mb-3'>El correo electrónico ya está registrado.</div>";
            } else {
                $imagen_perfil = 'default.png';

                if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
                    $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
                    $nombre_imagen = time() . "_" . uniqid() . "." . $extension;
                    $carpeta_destino = __DIR__ . '/../../Resources/uploads/';

                    if (!is_dir($carpeta_destino)) {
                        mkdir($carpeta_destino, 0777, true);
                    }

                    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $carpeta_destino . $nombre_imagen)) {
                        $imagen_perfil = $nombre_imagen;
                    }
                }

                if ($this->usuarioModel->registrar($nombre, $apellido, $email, $contrasena_hash, $antecedentes, $imagen_perfil)) {
                    $mensaje = "<div class='alert alert-success p-2 small mb-3'>¡Registro exitoso! Ya puedes <a href='index.php?accion=login' class='fw-bold text-success'>iniciar sesión</a>.</div>";
                } else {
                    $mensaje = "<div class='alert alert-danger p-2 small mb-3'>Ocurrió un error al registrar la cuenta.</div>";
                }
            }
        }

        require_once __DIR__ . '/../../Frontend/views/auth/registro.php';
    }

    // Cierra la sesión
    public function logout() {
        session_unset();
        session_destroy();
        header("Location: index.php");
        exit();
    }
}