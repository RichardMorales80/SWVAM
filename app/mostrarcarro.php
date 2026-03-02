<?php

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';
include 'caqrrito.php';
include '../templetes/cabecera.php';
require __DIR__ . '/../config/seguridad.php';

verificarRol(1,2,3);

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'] ?? null;

if(!$id_usuario){
    echo "<div class='alert alert-danger'>Inicia sesión</div>";
    exit;
}

$sql = "SELECT * FROM carrito WHERE id_usuario=?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_usuario]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<br>
<h3 style="text-align:center; color:burlywood;">
Lista de artículos del carrito
</h3>

<?php if(count($productos) > 0): ?>

<table class="table table-striped table-responsive">
<tbody>

<tr>
   <th width="40%">Descripción</th>
   <th width="15%" class="text-center">Cantidad</th>
   <th width="20%" class="text-center">Precio</th>
   <th width="20%" class="text-center">Total</th>
   <th width="5%">--</th>
</tr>

<?php $total = 0; ?>

<?php foreach($productos as $producto): ?>

<tr>

<td width="40%">
    <?= htmlspecialchars($producto['descripcion']) ?>
</td>

<td width="15%" class="text-center">
    <?= $producto['cantidad'] ?>
</td>

<td width="20%" class="text-center">
    $<?= number_format($producto['precio'],2) ?>
</td>

<td width="20%" class="text-center">
    $<?= number_format($producto['precio'] * $producto['cantidad'],2) ?>
</td>

<td width="5%">

<form method="post">

<input type="hidden" name="id"
value="<?= openssl_encrypt($producto['id_producto'], COD, KEY); ?>">

<button class="btn btn-danger"
 type="submit"
 name="btnaccion"
 value="Eliminar">
Eliminar
</button>

</form>

</td>

</tr>

<?php 
$total += $producto['precio'] * $producto['cantidad']; 
?>

<?php endforeach; ?>

<tr>
<td colspan="3" align="right"><h4>Total</h4></td>
<td align="right"><h4>$<?= number_format($total,2) ?></h4></td>
<td></td>
</tr>

<tr>
<td colspan="5">

<form action="../privadas/pagar.php" method="post">

<div class="alert alert-dark">

<div class="form-group">
<label>Correo de contacto:</label>

<input name="email"
 class="form-control"
 type="email"
 placeholder="Ingrese su correo electrónico"
 required>

<small class="form-text text-muted">
Los datos de compra se enviarán a este correo
</small>

</div>
</div>

<button class="btn btn-primary btn-lg btn-block"
 type="submit"
 name="btnaccion"
 value="proceder">
Enviar pedido al correo >>
</button>

</form>

</td>
</tr>

</tbody>
</table>

<?php else: ?>

<div class="alert alert-success">
No hay ningún producto en el carrito...
</div>

<?php endif; ?>

<?php include '../templetes/pie.php'; ?>
