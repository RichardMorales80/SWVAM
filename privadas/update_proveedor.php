<?php
require '../config/Conexion.php';

$base = Conexion::conectar();

if($_SERVER['REQUEST_METHOD']=='POST'){

$id = $_POST['id'];
$nombre = trim($_POST['nombre']);
$correo = trim($_POST['correo']);
$telefono = trim($_POST['telefono']);
$direccion = trim($_POST['direccion']);

/* ==== VALIDACIONES ==== */

if(
 empty($nombre) ||
 empty($correo) ||
 empty($telefono) ||
 empty($direccion)
){
 http_response_code(400);
 exit;
}

if(!filter_var($correo,FILTER_VALIDATE_EMAIL)){
 http_response_code(400);
 exit;
}

/* ==== UPDATE ==== */

$sql="UPDATE proveedores SET
 nombre=?,
 correo=?,
 telefono=?,
 direccion=?
 WHERE id_proveedor=?";

$base->prepare($sql)->execute([
 $nombre,
 $correo,
 $telefono,
 $direccion,
 $id
]);

echo "ok";

}
