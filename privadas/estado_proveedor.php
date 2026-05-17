<?php
require '../config/Conexion.php';

header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if(!$id){
    echo json_encode(["error"=>"ID no recibido"]);
    exit;
}

try{

    $db = Conexion::conectar();

    $stmt = $db->prepare("
        UPDATE proveedores 
        SET estado = IF(estado=1,0,1)
        WHERE id_proveedor = ?
    ");

    $stmt->execute([$id]);

    echo json_encode(["success"=>true]);

}catch(Exception $e){
    echo json_encode(["error"=>$e->getMessage()]);
}