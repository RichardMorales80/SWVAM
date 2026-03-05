<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
require '../config/Conexion.php';

// Función de saneamiento
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$alertas = [];
$errores = [];

// Solo procesar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([['error', 'Método no permitido']]);
    exit;
}

// ======================
// RECIBIR DATOS
// ======================
$nombre         = limpiar($_POST['nombre'] ?? '');
$apellido1      = limpiar($_POST['apellido1'] ?? '');
$apellido2      = limpiar($_POST['apellido2'] ?? '');
$correo         = limpiar($_POST['correo'] ?? '');
$telefono       = limpiar($_POST['telefono'] ?? '');
$calle          = limpiar($_POST['calle'] ?? '');
$numero_ext     = limpiar($_POST['numero_exterior'] ?? '');
$numero_int     = limpiar($_POST['numero_interior'] ?? '');
$colonia        = limpiar($_POST['colonia'] ?? '');
$ciudad         = limpiar($_POST['ciudad'] ?? '');
$estado         = limpiar($_POST['estado'] ?? '');
$codigo_postal  = limpiar($_POST['codigo_postal'] ?? '');
$pass           = $_POST['pas'] ?? '';
$passrev        = $_POST['pasrev'] ?? '';
$captcha        = $_POST['g-recaptcha-response'] ?? '';

// ======================
// VALIDACIONES SERVIDOR
// ======================

// Campos vacíos obligatorios
$campos_obligatorios = [
    'nombre'=>$nombre, 'apellido1'=>$apellido1, 'correo'=>$correo, 'telefono'=>$telefono,
    'calle'=>$calle, 'numero_exterior'=>$numero_ext, 'colonia'=>$colonia, 'ciudad'=>$ciudad,
    'estado'=>$estado, 'codigo_postal'=>$codigo_postal, 'pas'=>$pass, 'pasrev'=>$passrev
];

foreach($campos_obligatorios as $campo => $valor){
    if(empty($valor)){
        $errores[] = "El campo $campo es obligatorio.";
    }
}

// Nombre y apellidos
if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) $errores[] = "El nombre solo debe contener letras.";
if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido1)) $errores[] = "El primer apellido solo debe contener letras.";
if ($apellido2 && !preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $apellido2)) $errores[] = "El segundo apellido solo debe contener letras.";

// Longitudes máximas
if(strlen($nombre) > 50) $errores[] = "El nombre no puede exceder 50 caracteres.";
if(strlen($apellido1) > 50) $errores[] = "El primer apellido no puede exceder 50 caracteres.";
if($apellido2 && strlen($apellido2) > 50) $errores[] = "El segundo apellido no puede exceder 50 caracteres.";
if(strlen($correo) > 100) $errores[] = "El correo no puede exceder 100 caracteres.";
if(strlen($calle) > 100) $errores[] = "La calle no puede exceder 100 caracteres.";
if(strlen($colonia) > 50) $errores[] = "La colonia no puede exceder 50 caracteres.";
if(strlen($ciudad) > 50) $errores[] = "La ciudad no puede exceder 50 caracteres.";
if(strlen($estado) > 50) $errores[] = "El estado no puede exceder 50 caracteres.";

// Correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) $errores[] = "Correo electrónico inválido.";

// Teléfono
if (!preg_match('/^[0-9]{7,14}$/', $telefono)) $errores[] = "Teléfono inválido (7-14 dígitos).";

// Calle y colonia
if (!preg_match('/^[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s\.\#\,\-\/]+$/', $calle)) $errores[] = "Calle inválida.";

if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $colonia)) $errores[] = "Colonia inválida.";

// Ciudad y estado
if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $ciudad)) $errores[] = "Ciudad inválida.";
if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $estado)) $errores[] = "Estado inválido.";

// Código Postal
if (!preg_match('/^[0-9]{4,6}$/', $codigo_postal)) $errores[] = "Código postal inválido.";

// Número exterior/interior
if (!preg_match('/^[0-9]+$/', $numero_ext)) $errores[] = "Número exterior inválido.";
if ($numero_int && !preg_match('/^[0-9]+$/', $numero_int)) $errores[] = "Número interior inválido.";

// Contraseña
if (
    strlen($pass) < 8 ||
    !preg_match('/[A-Z]/', $pass) ||
    !preg_match('/[a-z]/', $pass) ||
    !preg_match('/[0-9]/', $pass) ||
    !preg_match('/[!@#$%^&*(),.?":{}|<>]/', $pass)
) $errores[] = "La contraseña no cumple los requisitos (8 caracteres, mayúscula, minúscula, número y símbolo).";

// Confirmar contraseña
if ($pass !== $passrev) $errores[] = "Las contraseñas no coinciden.";

// CAPTCHA
if (empty($captcha)) {
    $errores[] = "Verifica el reCAPTCHA.";
} else {
    $secretKey = 'TU_SECRET_KEY'; 
    $verify = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=$captcha");
    $resp = json_decode($verify, true);
    if (!$resp['success']) $errores[] = "reCAPTCHA inválido.";
}

// ======================
// INSERTAR EN BD
// ======================
if (empty($errores)) {
    try {
        $db = Conexion::conectar();

        // Verificar si correo ya existe
        $stmt = $db->prepare("SELECT id_usuario FROM usuarios WHERE correo=?");
        $stmt->execute([$correo]);
        if ($stmt->rowCount() > 0) {
            $alertas[] = ['error', 'Correo ya registrado'];
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);

            $insert = $db->prepare("INSERT INTO usuarios 
                (primer_nombre, primer_apellido, segundo_apellido, correo, telefono, calle, numero_exterior, numero_interior, colonia, ciudad, estado, codigo_postal, password, id_rol)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 2)
            ");

            $insert->execute([
                $nombre, $apellido1, $apellido2, $correo, $telefono,
                $calle, $numero_ext, $numero_int, $colonia, $ciudad,
                $estado, $codigo_postal, $hash
            ]);

            $alertas[] = ['success', 'Usuario registrado correctamente'];
        }

    } catch (PDOException $e) {
        $alertas[] = ['error', 'Error en el servidor: '.$e->getMessage()];
    }
} else {
    foreach ($errores as $e) {
        $alertas[] = ['error', $e];
    }
}

echo json_encode($alertas);
exit;
?>