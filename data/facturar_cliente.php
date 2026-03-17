<?php
session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRoles([2]);

if(!isset($_SESSION['id_usuario'])){
    header("Location: ../index.php");
    exit();
}

$pdo = Conexion::conectar();
$id_usuario = $_SESSION['id_usuario'];

include("../views/navbar.php");

/* =========================
   DATOS FISCALES USUARIO
========================= */

$stmtUser = $pdo->prepare("SELECT rfc, razon_social FROM usuarios WHERE id_usuario = ?");
$stmtUser->execute([$id_usuario]);
$datosCliente = $stmtUser->fetch(PDO::FETCH_ASSOC);

$rfcGuardado = $datosCliente['rfc'] ?? '';
$razonGuardada = $datosCliente['razon_social'] ?? '';

/* =========================
   VENTAS
========================= */

$sql = "SELECT id_venta, total, fecha 
        FROM ventas 
        WHERE id_usuario = ?
        ORDER BY id_venta DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Generar Factura</title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/cliente.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="main-content">
<div class="catalogo-container">

<div class="topbar">
    <div class="topbar-left">
        <h4>Generar Factura</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre"><?= $_SESSION['nombre'] ?></span>
        <img src="../public/imagenes/avatar.png" class="avatar">
    </div>
</div>

<h2>Mis compras</h2>

<div class="card card-table">

<table class="tabla-sistema">

<thead>
<tr>
<th>ID Venta</th>
<th>Fecha</th>
<th>Total</th>
<th>Acción</th>
</tr>
</thead>

<tbody>

<?php foreach($ventas as $v): 

$stmtF = $pdo->prepare("SELECT id_factura FROM facturas WHERE id_venta = ?");
$stmtF->execute([$v['id_venta']]);
$facturada = $stmtF->fetch();
?>

<tr>
<td><?= $v['id_venta'] ?></td>
<td><?= $v['fecha'] ?></td>
<td>$<?= number_format($v['total'],2) ?></td>

<td>

<?php if($facturada): ?>

<a href="../data/factura_pdf.php?id=<?= $facturada['id_factura'] ?>" 
class="btn-sistema btn-editar" target="_blank">
Descargar
</a>

<?php else: ?>

<a href="?preview=<?= $v['id_venta'] ?>" 
class="btn-sistema btn-guardar">
Generar
</a>

<?php endif; ?>

</td>
</tr>

<?php endforeach; ?>

</tbody>
</table>
</div>

<!-- ================= PREVIEW ================= -->

<?php
if(isset($_GET['preview'])){

$id_venta = $_GET['preview'];

$stmt = $pdo->prepare("SELECT * FROM ventas WHERE id_venta = ? AND id_usuario = ?");
$stmt->execute([$id_venta, $id_usuario]);

$venta = $stmt->fetch(PDO::FETCH_ASSOC);

if($venta){
?>

<div class="card">

<h3>Vista previa</h3>

<p><b>ID Venta:</b> <?= $venta['id_venta'] ?></p>
<p><b>Total:</b> $<?= number_format($venta['total'],2) ?></p>

<form method="POST">

<input type="hidden" name="id_venta" value="<?= $venta['id_venta'] ?>">

<div class="form-group">
<label>RFC</label>
<input type="text" name="rfc" class="form-control" 
value="<?= $rfcGuardado ?>" 
style="text-transform: uppercase;" required>
</div>

<div class="form-group">
<label>Razón Social</label>
<input type="text" name="razon_social" class="form-control" 
value="<?= $razonGuardada ?>" 
style="text-transform: uppercase;" required>
</div>

<button class="btn-sistema btn-editar">
Confirmar factura
</button>

</form>

</div>

<?php
}
}
?>

<!-- ================= GUARDAR ================= -->

<?php
if($_SERVER['REQUEST_METHOD'] == 'POST'){

$id_venta = $_POST['id_venta'];
$rfc = strtoupper(trim($_POST['rfc']));
$razon = strtoupper(trim($_POST['razon_social']));

/* VALIDACIONES */

if(strlen($rfc) < 12 || strlen($rfc) > 13){
    echo "<script>Swal.fire('Error','RFC inválido','error');</script>";
    exit;
}

if($razon == ''){
    echo "<script>Swal.fire('Error','Razón social requerida','error');</script>";
    exit;
}

/* GUARDAR DATOS EN USUARIO */

$stmtU = $pdo->prepare("UPDATE usuarios SET rfc=?, razon_social=? WHERE id_usuario=?");
$stmtU->execute([$rfc,$razon,$id_usuario]);

/* VALIDAR DUPLICADO */

$stmt = $pdo->prepare("SELECT * FROM facturas WHERE id_venta=?");
$stmt->execute([$id_venta]);

if($stmt->rowCount() == 0){

$sql = "INSERT INTO facturas (id_venta, rfc, razon_social, fecha)
VALUES (?, ?, ?, NOW())";

$stmt = $pdo->prepare($sql);
$stmt->execute([$id_venta,$rfc,$razon]);

echo "<script>
Swal.fire('Éxito','Factura generada','success')
.then(()=>{window.location='facturar_cliente.php';});
</script>";

}else{

echo "<script>Swal.fire('Aviso','Ya existe factura','warning');</script>";

}
}
?>

</div>
</div>

<!-- ================= JS MAYUSCULAS ================= -->

<script>
document.addEventListener("DOMContentLoaded", function(){

let rfc = document.querySelector('input[name="rfc"]');
let razon = document.querySelector('input[name="razon_social"]');

if(rfc){
rfc.addEventListener("input", function(){
this.value = this.value.toUpperCase();
});
}

if(razon){
razon.addEventListener("input", function(){
this.value = this.value.toUpperCase();
});
}

});
</script>

</body>
</html>