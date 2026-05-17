<?php
require '../config/Conexion.php';

$base = Conexion::conectar();

if($_SERVER['REQUEST_METHOD']=='POST'){

$id = $_POST['id'] ?? '';
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');

/* ===== VALIDACIONES ===== */

if(empty($nombre) || empty($correo) || empty($telefono) || empty($direccion)){
    header("Location: ../privadas/editar_proveedor.php?error=campos");
    exit;
}

if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
    header("Location: ../privadas/editar_proveedor.php?error=correo");
    exit;
}

/* ===== UPDATE ===== */

$sql = "UPDATE proveedores SET
        nombre=?,
        correo=?,
        telefono=?,
        direccion=?
        WHERE id_proveedor=?";

$stmt = $base->prepare($sql);
$stmt->execute([
    $nombre,
    $correo,
    $telefono,
    $direccion,
    $id
]);

/* ===== REDIRECCION ===== */

header("Location: ../privadas/editar_proveedor.php?success=1");
exit;

}
