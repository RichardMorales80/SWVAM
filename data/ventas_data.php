<?php
require_once __DIR__ . '/../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';

verificarRoles([1,3]);

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

$buscar = $_POST['buscar'] ?? '';
$inicio = $_POST['inicio'] ?? '';
$fin    = $_POST['fin'] ?? '';
$pagina = $_POST['pagina'] ?? 1;

$limite = 5;
$offset = ($pagina - 1) * $limite;

$where = [];
$params = [];

/* ROL */
if ($id_rol != 1) {
    $where[] = "id_usuario = :id_usuario";
    $params[':id_usuario'] = $id_usuario;
}

/* BUSCADOR */
if (!empty($buscar)) {
    $where[] = "(descripcion LIKE :buscar OR id_producto LIKE :buscar)";
    $params[':buscar'] = "%$buscar%";
}

/* FECHA */
if (!empty($inicio) && !empty($fin)) {
    $where[] = "fecha BETWEEN :inicio AND :fin";
    $params[':inicio'] = $inicio . " 00:00:00";
    $params[':fin']    = $fin . " 23:59:59";
}

$whereSQL = "";
if (!empty($where)) {
    $whereSQL = "WHERE " . implode(" AND ", $where);
}

/* TOTAL REGISTROS */
$sqlTotalReg = "SELECT COUNT(*) as total FROM ventas $whereSQL";
$stmtTotalReg = $pdo->prepare($sqlTotalReg);
$stmtTotalReg->execute($params);
$totalRegistros = $stmtTotalReg->fetch(PDO::FETCH_ASSOC)['total'];

$totalPaginas = ceil($totalRegistros / $limite);

/* CONSULTA PAGINADA */
$sql = "SELECT * FROM ventas $whereSQL 
        ORDER BY fecha DESC 
        LIMIT $limite OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* GRAN TOTAL */
$sqlTotal = "SELECT SUM(total) as gran_total FROM ventas $whereSQL";
$stmtGran = $pdo->prepare($sqlTotal);
$stmtGran->execute($params);
$granTotal = $stmtGran->fetch(PDO::FETCH_ASSOC)['gran_total'] ?? 0;

/* TABLA */
ob_start();
?>

<table class="table-pro">
<thead>
<tr>
    <th>ID</th>
    <th>Producto</th>
    <th>Descripción</th>
    <th>Precio</th>
    <th>Cantidad</th>
    <th>Total</th>
    <th>Fecha</th>
</tr>
</thead>
<tbody>
<?php if($ventas): ?>
<?php foreach($ventas as $v): ?>
<tr>
    <td><?= $v['id_venta'] ?></td>
    <td><?= $v['id_producto'] ?></td>
    <td><?= htmlspecialchars($v['descripcion']) ?></td>
    <td>$<?= number_format($v['precio'],2) ?></td>
    <td><?= $v['cantidad'] ?></td>
    <td><strong>$<?= number_format($v['total'],2) ?></strong></td>
    <td><?= date("d/m/Y H:i", strtotime($v['fecha'])) ?></td>
</tr>
<?php endforeach; ?>
<?php else: ?>
<tr>
    <td colspan="7">No hay resultados</td>
</tr>
<?php endif; ?>
</tbody>
</table>

<div class="total-box">
    Gran Total: $<?= number_format($granTotal,2) ?>
</div>

<?php
$tabla = ob_get_clean();

/* PAGINACIÓN */
$paginacion = '';
for ($i=1; $i <= $totalPaginas; $i++) {
    $activo = ($i == $pagina) ? 'style="font-weight:bold;"' : '';
    $paginacion .= "<button onclick='cargarVentas($i)' $activo>$i</button> ";
}

echo json_encode([
    "tabla" => $tabla,
    "paginacion" => $paginacion
]);