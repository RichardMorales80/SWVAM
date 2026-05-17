<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
session_start();

require_once __DIR__ . '/../global/configuracion.php';
require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

include __DIR__ . '/../templetes/cabecera.php';

verificarRoles([1,2,3]);

$pdo = Conexion::conectar();

$id_usuario = $_SESSION['id_usuario'] ?? null;
$id_cliente = $_SESSION['id_cliente'] ?? null;

// USAR CLIENTE SI EXISTE
$usuario_carrito = $id_cliente ?? $id_usuario;

if(!$usuario_carrito){
    header("Location: " . BASE_URL . "index.php");
    exit;
}

if(!$id_cliente){
    header("Location: seleccionar_cliente.php");
    exit;
}

/* =========================
   ACCIONES
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['accion_cantidad'])) {

        $id_producto = openssl_decrypt($_POST['id'], COD, KEY);

        if ($id_producto) {

            $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_producto=? AND id_usuario=?");
            $stmt->execute([$id_producto, $usuario_carrito]);
            $producto = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($producto) {

                $cantidad = $producto['cantidad'];

                $stmtStock = $pdo->prepare("SELECT cantidad FROM productos WHERE id_producto=?");
                $stmtStock->execute([$id_producto]);
                $stock = $stmtStock->fetchColumn() ?? 0;

                if ($_POST['accion_cantidad'] == 'sumar') {

                    if ($cantidad < $stock) {
                        $cantidad++;
                    } else {
                        $_SESSION['error_stock'] = "No hay stock disponible";
                    }
                }

                if ($_POST['accion_cantidad'] == 'restar') {
                    $cantidad--;
                }

                if ($cantidad <= 0) {
                    $delete = $pdo->prepare("DELETE FROM carrito WHERE id_producto=? AND id_usuario=?");
                    $delete->execute([$id_producto, $usuario_carrito]);
                } else {
                    $update = $pdo->prepare("UPDATE carrito SET cantidad=? WHERE id_producto=? AND id_usuario=?");
                    $update->execute([$cantidad, $id_producto, $usuario_carrito]);
                }
            }

            header("Location: mostrarcarro.php");
            exit;
        }
    }

    if (isset($_POST['btnaccion']) && $_POST['btnaccion'] == "Eliminar") {

        $id_producto = openssl_decrypt($_POST['id'], COD, KEY);

        if ($id_producto) {

            $sql = "DELETE FROM carrito WHERE id_producto = ? AND id_usuario = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_producto, $usuario_carrito]);
              $_SESSION['mensaje'] = "Producto eliminado correctamente";
            header("Location: mostrarcarro.php");
            exit;
        }
    }
}

/* =========================
   OBTENER PRODUCTOS
========================= */

$sql = "SELECT c.*, IFNULL(p.cantidad, 9999) AS stock 
        FROM carrito c
        LEFT JOIN productos p ON c.id_producto = p.id_producto
        WHERE c.id_usuario=?";

$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario_carrito]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="contenedor-carrito">
<div class="topbar">
    <div class="topbar-left">
        <h4>Carrito de compras</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre'] ?? 'Usuario' ?>
        </span>

        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>
<div style="text-align:center; margin-bottom:10px;">
Cliente: <strong><?= $_SESSION['nombre_cliente'] ?? 'Sin cliente' ?></strong>
</div>

<h3 style="text-align:center;">Lista de artículos del carrito</h3>
<?php if(isset($_SESSION['mensaje'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: '<?= $_SESSION['mensaje'] ?>',
    timer: 2000,
    showConfirmButton: false
});
</script>
<?php unset($_SESSION['mensaje']); endif; ?>

<?php if(isset($_SESSION['error_stock'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Stock insuficiente',
    text: 'No hay stock disponible'
});
</script>
<?php unset($_SESSION['error_stock']); endif; ?>

<?php if(count($productos) > 0): ?>

<table class="table table-striped">
<tr>
<th>Descripción</th>
<th class="text-center">Cantidad</th>
<th class="text-center">Precio</th>
<th class="text-center">Total</th>
<th></th>
</tr>
<tr>
<td colspan="5" style="text-align:center; padding:20px;">



</td>
</tr>

<?php $total = 0; ?>

<?php foreach($productos as $producto): ?>

<tr>

<td><?= htmlspecialchars($producto['descripcion']) ?></td>

<td class="text-center">
<form method="post" style="display:flex; justify-content:center; gap:5px;">

<input type="hidden" name="id"
value="<?= openssl_encrypt($producto['id_producto'], COD, KEY); ?>">

<button type="submit" name="accion_cantidad" value="restar" class="btn btn-secondary btn-sm">-</button>

<span><?= $producto['cantidad'] ?></span>

<button type="submit" name="accion_cantidad" value="sumar"
class="btn btn-secondary btn-sm"
<?= ($producto['cantidad'] >= $producto['stock']) ? 'disabled' : '' ?>>
+
</button>

</form>
</td>

<td class="text-center">$<?= number_format($producto['precio'],2) ?></td>

<td class="text-center">
$<?= number_format($producto['precio'] * $producto['cantidad'],2) ?>
</td>

<td>
<form method="post">
<input type="hidden" name="id"
value="<?= openssl_encrypt($producto['id_producto'], COD, KEY); ?>">
<button class="btn btn-danger btn-sm" name="btnaccion" value="Eliminar">X</button>
</form>
</td>

</tr>


<?php 
$total += $producto['precio'] * $producto['cantidad']; 
?>

<?php endforeach; ?>

<tr>
<td colspan="3" align="right"><strong>Total:</strong></td>
<td align="center"><strong>$<?= number_format($total,2) ?></strong></td>
<td></td>
</tr>

</table>
<tr>
<td colspan="5" style="text-align:center; padding:20px;">

<form action="../privadas/pagar.php" method="POST">
    <button class="btn btn-success btn-lg" name="btnaccion" value="proceder">
        Confirmar Pedido
    </button>
</form>

</td>
</tr>
<?php else: ?>

<div class="alert alert-success">
No hay ningún producto en el carrito...
</div>

<?php endif; ?>

</div>