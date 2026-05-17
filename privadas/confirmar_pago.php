<?php
session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

// SOLO ADMIN Y VENDEDOR
verificarRoles([1,3]);

$pdo = Conexion::conectar();

if(isset($_POST['id_venta'])){

    $id_venta = $_POST['id_venta'];

    try{

        // CAMBIAR ESTADO A PAGADO
        $stmt = $pdo->prepare("
            UPDATE ventas 
            SET estado_pago = 'pagado' 
            WHERE id_venta = ?
        ");
        $stmt->execute([$id_venta]);

        // CREAR FACTURA
        $stmt = $pdo->prepare("
            INSERT INTO facturas (id_venta, fecha)
            VALUES (?, NOW())
        ");
        $stmt->execute([$id_venta]);

        // MENSAJE
        $_SESSION['mensaje'] = "Pago confirmado y factura generada";

    }catch(Exception $e){

        $_SESSION['mensaje'] = "Error al confirmar pago";

    }

}

header("Location: ../data/ventas.php");
exit;