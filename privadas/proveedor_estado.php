<?php
require '../config/Conexion.php';

$db = Conexion::conectar();

if($_SERVER['REQUEST_METHOD'] === 'POST'){

    $id = $_POST['id'];
    $estado = $_POST['estado'];

    $sql = "UPDATE proveedores SET estado = ? WHERE id_proveedor = ?";
    $stmt = $db->prepare($sql);

    if($stmt->execute([$estado, $id])){
        echo "ok";
    }else{
        http_response_code(500);
        echo "error";
    }
}

