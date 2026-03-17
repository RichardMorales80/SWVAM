<?php
session_start();

require __DIR__ . '/../config/seguridad.php';
require_once __DIR__ . '/../config/Conexion.php';

verificarRoles([1,2,3]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'];
$id_rol     = $_SESSION['id_rol'];

$tipoMenu = ($id_rol == 1) ? "admin" : (($id_rol == 3) ? "vendedor" : "cliente");

include("../views/navbar.php");

$titulo = ($id_rol == 1) ? "Facturas Generales" : "Mis Facturas";

/* =========================
   FILTROS
========================= */

$buscar = $_GET['buscar'] ?? '';
$desde  = $_GET['desde'] ?? '';
$hasta  = $_GET['hasta'] ?? '';

/* =========================
   PAGINACION
========================= */

$porPagina = 10;
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
if($pagina < 1) $pagina = 1;

$offset = ($pagina - 1) * $porPagina;

/* =========================
   SQL BASE
========================= */

$sqlBase = "FROM facturas f
LEFT JOIN ventas v ON v.id_venta = f.id_venta
LEFT JOIN usuarios u ON u.id_usuario = v.id_usuario
WHERE 1=1";

$params = [];

/* SOLO CLIENTE */
if($id_rol == 2){
    $sqlBase .= " AND v.id_usuario = ?";
    $params[] = $id_usuario; 
}

/* BUSQUEDA */
if($buscar != ''){
    $sqlBase .= " AND (f.id_factura LIKE ? OR u.primer_nombre LIKE ?)";
    $params[] = "%$buscar%";
    $params[] = "%$buscar%";
}

/* FECHAS */
if($desde && $hasta){
    $sqlBase .= " AND f.fecha BETWEEN ? AND ?";
    $params[] = $desde;
    $params[] = $hasta;
}

/* =========================
   TOTAL REGISTROS
========================= */

$stmtTotal = $pdo->prepare("SELECT COUNT(*) $sqlBase");
$stmtTotal->execute($params);
$totalRegistros = $stmtTotal->fetchColumn();

$totalPaginas = ceil($totalRegistros / $porPagina);

/* =========================
   CONSULTA FINAL
========================= */

$sql = "SELECT f.id_factura, f.fecha,
v.total,
CONCAT(u.primer_nombre,' ',u.primer_apellido) AS cliente
$sqlBase
ORDER BY f.fecha DESC
LIMIT $porPagina OFFSET $offset";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   TOTAL GENERAL
========================= */

$stmtTotalDinero = $pdo->prepare("SELECT COALESCE(SUM(v.total),0) $sqlBase");
$stmtTotalDinero->execute($params);
$totalGeneral = $stmtTotalDinero->fetchColumn();
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<title><?= $titulo ?></title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="../public/estilos/ventas.css">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

<div class="topbar">
    <div class="topbar-left">
        <h4><?= $titulo ?></h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre"><?= $_SESSION['nombre'] ?></span>
        <img src="../public/imagenes/avatar.png" class="avatar">
    </div>
</div>

<div class="main-content">
<div class="catalogo-container">

<h2><?= $titulo ?></h2>

<!-- ================= FILTROS ================= -->

<div class="card">
<form method="GET" class="filtro-form">

<div class="form-group">
<label>Buscar</label>
<input type="text" name="buscar" class="form-control" value="<?= htmlspecialchars($buscar) ?>">
</div>

<div class="form-group">
<label>Desde</label>
<input type="date" name="desde" class="form-control" value="<?= $desde ?>">
</div>

<div class="form-group">
<label>Hasta</label>
<input type="date" name="hasta" class="form-control" value="<?= $hasta ?>">
</div>

<button class="btn-sistema btn-editar">Filtrar</button>



</form>
</div>

<!-- ================= TOTAL ================= -->

<div class="total-gastos">
<label>Total:</label>
<span>$<?= number_format($totalGeneral,2) ?></span>
</div>

<!-- ================= TABLA ================= -->

<div class="card card-table">

<table class="tabla-sistema">

<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Fecha</th>
<th>Total</th>
<th>Acciones</th>
</tr>
</thead>

<tbody>

<?php if(count($facturas) > 0): ?>

<?php foreach($facturas as $f): ?>

<tr>

<td><?= $f['id_factura'] ?></td>
<td><?= htmlspecialchars($f['cliente']) ?></td>
<td><?= $f['fecha'] ?></td>
<td>$<?= number_format($f['total'],2) ?></td>

<td>
<a href="../data/factura_pdf.php?id=<?= $f['id_factura'] ?>" 
class="btn-sistema btn-editar" target="_blank">
PDF
</a>
</td>

</tr>

<?php endforeach; ?>

<?php else: ?>

<tr>
<td colspan="5" style="text-align:center;">No hay facturas</td>
</tr>

<?php endif; ?>

</tbody>

</table>

<!-- ================= PAGINACION ================= -->

<div class="paginacion-container">

<?php if($pagina > 1): ?>
<a href="?pagina=<?= $pagina-1 ?>&buscar=<?= $buscar ?>&desde=<?= $desde ?>&hasta=<?= $hasta ?>" class="btn-sistema">
Anterior
</a>
<?php endif; ?>

<span> Página <?= $pagina ?> de <?= $totalPaginas ?> </span>

<?php if($pagina < $totalPaginas): ?>
<a href="?pagina=<?= $pagina+1 ?>&buscar=<?= $buscar ?>&desde=<?= $desde ?>&hasta=<?= $hasta ?>" class="btn-sistema">
Siguiente
</a>
<?php endif; ?>

</div>

</div>

</div>
</div>

</body>
</html>