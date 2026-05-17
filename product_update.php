<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once "main.php";

$conexion = conexion();

/* ================= ACTIVAR / INACTIVAR ================= */
if(isset($_POST['producto_id']) && isset($_POST['estado'])){

    $id = limpiar_cadena($_POST['producto_id']);
    $estado = limpiar_cadena($_POST['estado']);

    $sql = "UPDATE producto SET estado = :estado WHERE producto_id = :id";
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":estado" => $estado,
        ":id" => $id
    ]);

    echo json_encode([
        "status" => "success",
        "msg" => "Estado actualizado"
    ]);
    exit();
}


/* ================= ACTUALIZAR PRODUCTO ================= */

if(isset($_POST['producto_id']) && isset($_POST['producto_nombre'])){

    $producto_id = limpiar_cadena($_POST['producto_id']);
    $nombre = limpiar_cadena($_POST['producto_nombre']);
    $precio = limpiar_cadena($_POST['producto_precio']);
    $stock = limpiar_cadena($_POST['producto_stock']);

    /* VALIDACIONES */
    if($nombre=="" || $precio=="" || $stock==""){
        echo json_encode([
            "status" => "error",
            "msg" => "Campos incompletos"
        ]);
        exit();
    }

    /* UPDATE */
    $sql = "UPDATE producto SET 
        producto_nombre = :nombre,
        producto_precio = :precio,
        producto_stock = :stock
        WHERE producto_id = :id";

    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ":nombre"=>$nombre,
        ":precio"=>$precio,
        ":stock"=>$stock,
        ":id"=>$producto_id
    ]);

    echo json_encode([
        "status" => "success",
        "msg" => "Producto actualizado correctamente"
    ]);
    exit();
}


/* ================= ERROR GENERAL ================= */

echo json_encode([
    "status" => "error",
    "msg" => "Datos no válidos"
]);