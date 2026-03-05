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

    /* ===== SANEAR TODOS LOS CAMPOS ===== */
    $nombre          = limpiar($_POST['nombre'] ?? '');
    $apellido1       = limpiar($_POST['apellido1'] ?? '');
    $apellido2       = limpiar($_POST['apellido2'] ?? '');
    $correo          = limpiar($_POST['correo'] ?? '');
    $telefono        = limpiar($_POST['telefono'] ?? '');
    $calle           = limpiar($_POST['calle'] ?? '');
    $numero_exterior = limpiar($_POST['numero_exterior'] ?? '');
    $numero_interior = limpiar($_POST['numero_interior'] ?? '');
    $colonia         = limpiar($_POST['colonia'] ?? '');
    $ciudad          = limpiar($_POST['ciudad'] ?? '');
    $estado          = limpiar($_POST['estado'] ?? '');
    $codigo_postal   = limpiar($_POST['codigo_postal'] ?? '');
    $pass            = $_POST['pas'] ?? '';
    $passrev         = $_POST['pasrev'] ?? '';
    $captcha         = $_POST['g-recaptcha-response'] ?? '';

    $errores = [];

    /* ===== VALIDACIONES SERVIDOR ===== */
    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre))
        $errores[] = "El nombre solo debe contener letras.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido1))
        $errores[] = "El primer apellido solo debe contener letras.";

    if (!empty($apellido2) && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido2))
        $errores[] = "El segundo apellido solo debe contener letras.";

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL))
        $errores[] = "Correo electrónico inválido.";

    if (!preg_match('/^[0-9]{7,14}$/', $telefono))
        $errores[] = "El teléfono debe tener entre 7 y 14 dígitos.";

    if (!preg_match('/^[0-9]+$/', $numero_exterior))
        $errores[] = "Número exterior inválido.";

    if (!empty($numero_interior) && !preg_match('/^[0-9]+$/', $numero_interior))
        $errores[] = "Número interior inválido.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $colonia))
        $errores[] = "Colonia solo debe contener letras.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $ciudad))
        $errores[] = "Ciudad solo debe contener letras.";

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $estado))
        $errores[] = "Estado solo debe contener letras.";

    if (!preg_match('/^[0-9]{4,10}$/', $codigo_postal))
        $errores[] = "Código postal inválido.";

    // Contraseña
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
        $db->beginTransaction();

        // Verificar si correo ya existe
        $existe = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo = ?");
        $existe->execute([$correo]);

        if ($existe->rowCount() > 0) {
            $alertas[] = ['error', 'Ya existe un usuario con ese correo'];
        } else {

            /* 1️⃣ INSERTAR DIRECCIÓN */
            $stmtDireccion = $db->prepare("
                INSERT INTO direcciones
                (calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
            ");

            $stmtDireccion->execute([
                $calle,
                $numero_exterior,
                $numero_interior,
                $colonia,
                $ciudad,
                $estado,
                $codigo_postal
            ]);

            // Obtener id generado
            $id_direccion = $db->lastInsertId();

            /* 2️⃣ INSERTAR USUARIO */
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $stmtUsuario = $db->prepare("
                INSERT INTO usuarios
                (primer_nombre, primer_apellido, segundo_apellido, correo, telefono, id_direccion, password, id_rol, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, 2, NOW())
            ");

            $stmtUsuario->execute([
                $nombre,
                $apellido1,
                $apellido2,
                $correo,
                $telefono,
                $id_direccion,
                $hash
            ]);

            $db->commit();

            $alertas[] = ['success', 'Usuario registrado correctamente'];
        }

    } catch (PDOException $e) {
        $db->rollBack();
        $alertas[] = ['error', $e->getMessage()];
    }
}
}

header('Content-Type: application/json');
echo json_encode($alertas);
exit();