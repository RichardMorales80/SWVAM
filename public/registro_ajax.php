<?php
// public/registro_ajax.php
header('Content-Type: application/json');
require '../config/Conexion.php';

// Función para sanitizar datos
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

// Solo procesar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status'=>'error', 'msg'=>'Método no permitido']);
    exit;
}

// Recibir datos
$nombre     = limpiar($_POST['nombre'] ?? '');
$apellido   = limpiar($_POST['apellidos'] ?? '');
$correo     = limpiar($_POST['correo'] ?? '');
$telefono   = limpiar($_POST['telefono'] ?? '');
$direccion  = limpiar($_POST['direccion'] ?? '');
$pass       = $_POST['pas'] ?? '';
$passrev    = $_POST['pasrev'] ?? '';
$captcha    = $_POST['g-recaptcha-response'] ?? '';

$errores = [];

// Validaciones básicas
if (!$nombre || !$apellido || !$correo || !$pass || !$passrev) {
    $errores[] = "Todos los campos son obligatorios.";
}

if ($pass !== $passrev) {
    $errores[] = "Las contraseñas no coinciden.";
}

if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "Correo inválido.";
}

if (!preg_match('/^[0-9]{7,14}$/', $telefono)) {
    $errores[] = "Teléfono inválido (7-14 dígitos).";
}

if (
    strlen($pass) < 8 ||
    !preg_match('/[A-Z]/', $pass) ||
    !preg_match('/[a-z]/', $pass) ||
    !preg_match('/[0-9]/', $pass) ||
    !preg_match('/[!@#$%^&*]/', $pass)
) {
    $errores[] = "Contraseña debe tener mínimo 8 caracteres, mayúscula, minúscula, número y símbolo.";
}

// Validar reCAPTCHA
if (!$captcha) {
    $errores[] = "Verifica el reCAPTCHA.";
} else {
    $secretKey = "TU_SECRET_KEY"; // Cambia por tu secret key
    $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
    $resp = json_decode($response, true);
    if (!$resp['success']) {
        $errores[] = "Error en verificación CAPTCHA.";
    }
}

// Si hay errores, devolver JSON
if (!empty($errores)) {
    echo json_encode(['status'=>'error', 'msg'=>implode("<br>", $errores)]);
    exit;
}

// Registrar en BD
try {
    $db = Conexion::conectar();

    // Verificar si correo existe
    $existe = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo=?");
    $existe->execute([$correo]);
    if ($existe->rowCount() > 0) {
        echo json_encode(['status'=>'error', 'msg'=>'Correo ya registrado']);
        exit;
    }

    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $insert = $db->prepare("
        INSERT INTO usuarios
        (primer_nombre, primer_apellido, correo, telefono, lugar_residencia, password, id_rol)
        VALUES (?, ?, ?, ?, ?, ?, 2)
    ");
    $insert->execute([$nombre, $apellido, $correo, $telefono, $direccion, $hash]);

    echo json_encode(['status'=>'ok', 'msg'=>'Usuario registrado correctamente']);

} catch (Exception $e) {
    echo json_encode(['status'=>'error', 'msg'=>'Error en servidor']);
}
?>
