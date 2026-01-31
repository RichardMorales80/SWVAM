<?php
// ============================
// CONEXIÓN A LA BASE DE DATOS
// ============================
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

// ============================
// OBTENER CORREO DEL FORMULARIO
// ============================
$correo = trim($_POST['correo'] ?? '');

// Validar que no venga vacío
if(empty($correo)){
    echo "Debes ingresar un correo";
    exit;
}

// ============================
// BUSCAR USUARIO EN LA TABLA
// ============================
$sql = "SELECT id_usuario FROM usuarios WHERE correo = ? LIMIT 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$correo]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Si no existe el correo
if(!$usuario){
    echo "Este correo no está registrado";
    exit;
}

// ============================
// GENERAR TOKEN SEGURO
// ============================

// token aleatorio seguro
$token = bin2hex(random_bytes(32));

// Fecha de expiración (1 hora)
$expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

// ============================
// GUARDAR TOKEN EN USUARIOS
// ============================
$update = $pdo->prepare("
    UPDATE usuarios 
    SET token_recuperacion = ?, token_expira = ?
    WHERE correo = ?
");

$update->execute([$token, $expira, $correo]);

// ============================
// MOSTRAR TOKEN (PRUEBA)
// ============================
// Luego aquí mandaremos correo

echo "Token generado correctamente:<br>";
echo $token;
?>
