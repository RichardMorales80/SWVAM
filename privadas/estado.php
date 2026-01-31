<?php
require '../config/Conexion.php';

$db=Conexion::conectar();

$id=$_POST['id'];
$estado=$_POST['estado'];

$sql="UPDATE productos SET estado=? WHERE id_producto=?";
$db->prepare($sql)->execute([$estado,$id]);

echo "ok";
