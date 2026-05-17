<?php
require '../config/Conexion.php';

$pdo = Conexion::conectar();

$sql = "SELECT 
            b.id_bitacora,
            b.accion,
            b.fecha,
            CONCAT(u.primer_nombre,' ',u.primer_apellido) AS nombre_usuario
        FROM bitacora b
        LEFT JOIN usuarios u ON u.id_usuario = b.id_usuario
        ORDER BY b.fecha DESC
        LIMIT 5";

$stmt = $pdo->prepare($sql);
$stmt->execute();

$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);