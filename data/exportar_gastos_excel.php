<?php
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRoles([1,3]);

$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

try {
    $pdo = Conexion::conectar();

    $query = "SELECT g.id_gasto, CONCAT(u.primer_nombre,' ',u.primer_apellido) AS usuario,
                     g.concepto, g.fecha, g.total
              FROM gastos g
              INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
              WHERE 1=1";

    $params = [];
    if($buscar){
        $query .= " AND g.concepto LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }
    if($desde){
        $query .= " AND g.fecha >= :desde";
        $params[':desde'] = $desde;
    }
    if($hasta){
        $query .= " AND g.fecha <= :hasta";
        $params[':hasta'] = $hasta;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Cabeceras para descargar Excel
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=gastos.xls");

    echo "ID\tUsuario\tConcepto\tFecha\tTotal\n";

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        echo "{$row['id_gasto']}\t{$row['usuario']}\t{$row['concepto']}\t{$row['fecha']}\t{$row['total']}\n";
    }

} catch (Exception $e){
    http_response_code(500);
    echo "Error al generar Excel: ".$e->getMessage();
}