<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRol(1);

header('Content-Type: application/json');

try {

    $pdo = Conexion::conectar();
    
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin    = $_GET['fecha_fin'] ?? '';

    $condicion = "";
    $params = [];

    if($fecha_inicio && $fecha_fin){
        $condicion = " WHERE v.fecha BETWEEN :inicio AND :fin ";
        $params[':inicio'] = $fecha_inicio;
        $params[':fin'] = $fecha_fin;
    }

   $sql = "SELECT 
            dv.descripcion AS nombre,
            SUM(dv.cantidad) AS cantidad,
            dv.precio,
            SUM(dv.total) AS total
        FROM detalle_venta dv
        INNER JOIN ventas v ON dv.id_venta = v.id_venta
        $condicion
        GROUP BY dv.descripcion, dv.precio
        ORDER BY cantidad DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $granTotal = 0;
    foreach($productos as $p){
        $granTotal += $p['total'];
    }

    echo json_encode([
        'productos' => $productos,
        'granTotal' => $granTotal
    ]);

} catch (Exception $e) {

    echo json_encode([
        'productos' => [],
        'granTotal' => 0,
        'error' => $e->getMessage()
    ]);

}