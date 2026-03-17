<?php
session_start();

require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRol(1);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$buscar = trim($_GET['buscar'] ?? '');
$desde  = trim($_GET['desde'] ?? '');
$hasta  = trim($_GET['hasta'] ?? '');

try {
    $pdo = Conexion::conectar();

    $query = "SELECT 
                g.id_gasto, 
                CONCAT(u.primer_nombre, ' ', u.primer_apellido) AS usuario,
                g.concepto, 
                g.fecha, 
                g.total
              FROM gastos g
              INNER JOIN usuarios u ON g.id_usuario = u.id_usuario
              WHERE 1=1";

    $params = [];

    if($buscar !== ''){
        $query .= " AND g.concepto LIKE :buscar";
        $params[':buscar'] = "%$buscar%";
    }

    if($desde !== ''){
        $query .= " AND DATE(g.fecha) >= :desde";
        $params[':desde'] = $desde;
    }

    if($hasta !== ''){
        $query .= " AND DATE(g.fecha) <= :hasta";
        $params[':hasta'] = $hasta;
    }

    $query .= " ORDER BY g.fecha DESC";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    header("Content-Disposition: attachment; filename=gastos.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    echo "ID\tUsuario\tConcepto\tFecha\tTotal\n";

    while($row = $stmt->fetch(PDO::FETCH_ASSOC)){
        echo $row['id_gasto'] . "\t" .
             $row['usuario'] . "\t" .
             $row['concepto'] . "\t" .
             $row['fecha'] . "\t" .
             $row['total'] . "\n";
    }

} catch (Exception $e){
    http_response_code(500);
    echo "Error al generar Excel: " . $e->getMessage();
}
?>