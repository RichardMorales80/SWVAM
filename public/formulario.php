<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require '../config/Conexion.php';

/* =========================
   FUNCIÓN DE SANEAMIENTO
========================= */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$alertas = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* ===== SANEAR ===== */
    $nombre     = limpiar($_POST['nombre'] ?? '');
    $apellido   = limpiar($_POST['apellidos'] ?? '');
    $correo     = limpiar($_POST['correo'] ?? '');
    $telefono   = limpiar($_POST['telefono'] ?? '');
    $direccion  = limpiar($_POST['direccion'] ?? '');
    $pass       = $_POST['pas'] ?? '';
    $passrev    = $_POST['pasrev'] ?? '';
    $captcha    = $_POST['g-recaptcha-response'] ?? '';

    $errores = [];

    /* ===== VALIDACIONES SERVIDOR ===== */
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre))
        $errores[] = "El nombre solo debe contener letras.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido))
        $errores[] = "Los apellidos solo deben contener letras.";

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
        $errores[] = "Correo electrónico inválido.";

    if (!preg_match('/^[0-9]{7,14}$/', $telefono))
        $errores[] = "El teléfono debe tener entre 7 y 14 dígitos.";

    if (
        strlen($pass) < 8 ||
        !preg_match('/[A-Z]/', $pass) ||
        !preg_match('/[a-z]/', $pass) ||
        !preg_match('/[0-9]/', $pass) ||
        !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $pass)
    ) {
        $errores[] = "La contraseña no cumple los requisitos.";
    }

    if ($pass !== $passrev)
        $errores[] = "Las contraseñas no coinciden.";

    /* ===== CAPTCHA ===== */
    if (empty($captcha)) {
        $errores[] = "Verifica el reCAPTCHA.";
    } else {
        $secret = '6LeXHIMrAAAAAEZH2eoiGhX0bFdUk4xIPVlXZe-A';
        $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=$captcha");
        $captchaOK = json_decode($verify, true);
        if (!$captchaOK['success']) $errores[] = "reCAPTCHA inválido.";
    }

    /* ===== INSERTAR EN BD ===== */
    if (empty($errores)) {
        try {
            $db = Conexion::conectar();
            $existe = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
            $existe->execute([$correo]);

            if ($existe->rowCount() > 0) {
                $alertas[] = ['error', 'Ya existe un usuario con ese correo'];
            } else {
                $hash = password_hash($pass, PASSWORD_DEFAULT);

                $insert = $db->prepare("INSERT INTO usuarios
                    (primer_nombre, primer_apellido, correo, telefono, lugar_residencia, password, id_rol)
                    VALUES (?, ?, ?, ?, ?, ?, 2)");
                $insert->execute([$nombre, $apellido, $correo, $telefono, $direccion, $hash]);

                $alertas[] = ['success', 'Usuario registrado correctamente'];
            }
        } catch (PDOException $e) {
            $alertas[] = ['error', $e->getMessage()];
        }
    } else {
        foreach ($errores as $e) {
            $alertas[] = ['error', $e];
        }
    }
}
header('Content-Type: application/json');
echo json_encode($alertas);
exit();

?>
