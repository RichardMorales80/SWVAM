<?php
session_start();
require_once '../config/Conexion.php';

$pdo = Conexion::conectar();

// ============================
// VALIDAR SESIÓN
// ============================

if (!isset($_SESSION['id_usuario'])) {
    die("Acceso no autorizado");
}

$id_usuario = $_SESSION['id_usuario'];
$id_rol = $_SESSION['id_rol'];

// ============================
// CONSULTA DE VENTAS SEGÚN ROL
// ============================

if ($id_rol == 1) {

    // ADMIN -> TODAS LAS VENTAS
    $sql = "SELECT * FROM ventas ORDER BY fecha DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

} else {

    // CLIENTE -> SOLO SUS VENTAS
    $sql = "SELECT * FROM ventas 
            WHERE id_usuario = :id_usuario 
            ORDER BY fecha DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':id_usuario' => $id_usuario
    ]);
}

$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ============================
// GRAN TOTAL SEGÚN ROL
// ============================

if ($id_rol == 1) {

    $sqlTotal = "SELECT SUM(total) AS gran_total FROM ventas";
    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute();

} else {

    $sqlTotal = "SELECT SUM(total) AS gran_total 
                 FROM ventas 
                 WHERE id_usuario = :id_usuario";

    $stmtTotal = $pdo->prepare($sqlTotal);
    $stmtTotal->execute([
        ':id_usuario' => $id_usuario
    ]);
}

$granTotal = $stmtTotal->fetch(PDO::FETCH_ASSOC)['gran_total'] ?? 0;

// ============================
// TITULO DINÁMICO
// ============================

$titulo = ($id_rol == 1) ? "Ventas Generales" : "Mis Compras";
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="../public/estilos/principal.css">

<title><?= $titulo ?></title>

<style>
h2 {
    text-align: center;
}
table {
    width: 100%;
    border-collapse: collapse;
    background: white;
}
th, td {
    padding: 10px;
    border: 1px solid #ddd;
    text-align: center;
}
th {
    background: #343a40;
    color: white;
}
tr:nth-child(even) {
    background: #f2f2f2;
}
.total-final {
    text-align: right;
    font-size: 18px;
    margin-top: 15px;
    font-weight: bold;
}
</style>
</head>

<body>

<!-- NAV -->
<nav class="main_nav">
<ul class="menu">
    <li class="logo-item">
        <img src="../public/imagenes/logo1.png" class="logo">
    </li>

    <?php if($id_rol == 1): ?>
        <li><a href="../views/administrador.php" class="main_menu_link">Atrás</a></li>
    <?php else: ?>
        <li><a href="../views/clientes.php" class="main_menu_link">Atrás</a></li>
    <?php endif; ?>

    <li><a href="../config/cerrar_sesion.php" class="main_menu_link">Salir</a></li>
</ul>
</nav>

<br><br><br><br><br><br><br><br><br><br><br><br>

<h2><?= $titulo ?></h2>

<table>
<thead>
<tr>
    <th>ID Venta</th>
    <th>ID Producto</th>
    <th>Descripción</th>
    <th>Precio</th>
    <th>Cantidad</th>
    <th>Total</th>
    <th>Fecha</th>
</tr>
</thead>

<tbody>
<?php if (!empty($ventas)): ?>
    <?php foreach ($ventas as $venta): ?>
        <tr>
            <td><?= $venta['id_venta']; ?></td>
            <td><?= $venta['id_producto']; ?></td>
            <td><?= htmlspecialchars($venta['descripcion']); ?></td>
            <td>$<?= number_format($venta['precio'], 2); ?></td>
            <td><?= $venta['cantidad']; ?></td>
            <td>$<?= number_format($venta['total'], 2); ?></td>
            <td><?= $venta['fecha']; ?></td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr>
        <td colspan="7">No hay ventas registradas</td>
    </tr>
<?php endif; ?>
</tbody>
</table>

<div class="total-final">
    Gran Total: $<?= number_format($granTotal, 2); ?>
</div>

</body>
</html>
