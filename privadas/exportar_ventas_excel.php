<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRoles([1,3]);

if(ob_get_length()) ob_clean();

// HEADERS
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Ventas.xls");
header("Pragma: no-cache");
header("Expires: 0");

$pdo = Conexion::conectar();
$id_rol = $_SESSION['id_rol'];
$id_usuario = $_SESSION['id_usuario'];

$buscar = $_GET['buscar'] ?? '';
$inicio = $_GET['inicio'] ?? '';
$fin    = $_GET['fin'] ?? '';

$where = [];
$params = [];

/* FILTRAR POR ROL */
if ($id_rol != 1) {
    $where[] = "id_usuario = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
}

/* BUSCADOR */
if (!empty($buscar)) {
    $where[] = "(descripcion LIKE :buscar OR id_producto LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

/* FECHAS */
if (!empty($inicio) && !empty($fin)) {
    $where[] = "fecha BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio . " 00:00:00";
    $params[':fin']    = $fin . " 23:59:59";
}

$whereSQL = "";
if(!empty($where)) $whereSQL = "WHERE " . implode(" AND ", $where);

/* CONSULTA */
$sql = "SELECT * FROM ventas $whereSQL ORDER BY fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* CALCULAR GRAN TOTAL */
$granTotal = 0;
foreach($ventas as $v) {
    $granTotal += $v['total'];
}

/* GENERAR EXCEL */
echo "<table border='1'>";
echo "<tr>
        <th>ID</th>
        <th>Producto</th>
        <th>Descripción</th>
        <th>Precio</th>
        <th>Cantidad</th>
        <th>Total</th>
        <th>Fecha</th>
      </tr>";

foreach($ventas as $v){
    echo "<tr>
        <td>{$v['id_venta']}</td>
        <td>{$v['id_producto']}</td>
        <td>".htmlspecialchars($v['descripcion'])."</td>
        <td>{$v['precio']}</td>
        <td>{$v['cantidad']}</td>
        <td>{$v['total']}</td>
        <td>{$v['fecha']}</td>
    </tr>";
}

// FILA DE GRAN TOTAL
echo "<tr>
        <td colspan='5' style='text-align:right;font-weight:bold;'>Gran Total:</td>
        <td colspan='2' style='font-weight:bold;'>$".number_format($granTotal,2)."</td>
      </tr>";
echo "</table>";

flush();
exit;