<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';
require_once '../config/seguridad.php';

verificarRoles([2]);

$pdo = Conexion::conectar();

include __DIR__ . '/caqrrito.php';

// =========================
// PRODUCTOS
// =========================
$sql = "SELECT id_producto, nombre, descripcion, precio, cantidad AS stock, imagen 
        FROM productos WHERE estado = 1";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =========================
// TOTAL CARRITO ACTUAL
// =========================
$id_cliente = $_SESSION['id_usuario'];
$_SESSION['id_cliente'] = $id_cliente;
$_SESSION['nombre_cliente'] = $_SESSION['nombre'];

$totalCarrito = 0;

if ($id_cliente) {
    $stmt = $pdo->prepare("SELECT SUM(cantidad) FROM carrito WHERE id_usuario=?");
    $stmt->execute([$id_cliente]);
    $totalCarrito = $stmt->fetchColumn() ?? 0;
}

include '../templetes/cabecera.php';
?>


<?php if(isset($_SESSION['mensaje'])): ?>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
    title: 'Error',
    text: '<?= $_SESSION['error_stock'] ?>'
});
</script>
<?php unset($_SESSION['error_stock']); endif; ?>

<div class="topbar">
    <div class="topbar-left">
        <h4>Realizar compras</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre'] ?? 'Usuario' ?>
        </span>

        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<!-- CLIENTE ACTUAL -->
<div style="text-align:center; margin-top:30px;">
Cliente actual: 
<strong>
<?= $_SESSION['nombre_cliente'] ?? 'No seleccionado' ?>
</strong>
</div>

<!-- CARRITO -->
<div style="text-align:center; margin:10px; font-size:18px;">
<a href="../app/mostrarcarro.php" class="btn btn-verde-suave">
🛒 Carrito (<?= $totalCarrito ?>)
</a>
</div>

<!-- ================= PRODUCTOS ================= -->

<div class="container">
<div class="row">

<?php foreach($productos as $p): ?>

<?php
$id = $p['id_producto'];
$nombre = $p['nombre'];
$precio = $p['precio'];
$stock = $p['stock'];
$descripcion = $p['descripcion'];
$imagen = $p['imagen'] ?? 'default.png';

$ruta = "../uploads/" . $imagen;
if (!file_exists(__DIR__ . "/../uploads/" . $imagen)) {
    $ruta = "../uploads/default.png";
}
?>

<div class="col-md-3">
<div class="card mb-4">

<img src="<?= $ruta ?>" class="card-img-top" style="height:200px; object-fit:cover;">

<div class="card-body">

<h5><?= htmlspecialchars($nombre) ?></h5>

<p>$<?= number_format($precio,2) ?></p>

<p><?= htmlspecialchars($descripcion) ?></p>

<p>
Stock: 
<span style="color:<?= $stock > 0 ? 'green':'red' ?>">
<?= $stock ?>
</span>
</p>

<form method="post">

<input type="hidden" name="id" value="<?= openssl_encrypt($id, COD, KEY) ?>">
<input type="hidden" name="nombre" value="<?= openssl_encrypt($nombre, COD, KEY) ?>">
<input type="hidden" name="precio" value="<?= openssl_encrypt($precio, COD, KEY) ?>">

<input type="number" name="cantidad" value="1" min="1" max="<?= $stock ?>" class="form-control mb-2">

<?php if($stock > 0): ?>
<button class="btn btn-primary w-100" name="btnaccion" value="Agregar">
Agregar
</button>
<?php else: ?>
<button class="btn btn-secondary w-100" disabled>Sin stock</button>
<?php endif; ?>

</form>

</div>
</div>
</div>

<?php endforeach; ?>

</div>
</div>

<script>
let select = document.getElementById("selectCliente");

if(select){
    select.addEventListener("change", function(){
        let nombre = this.options[this.selectedIndex].getAttribute("data-nombre");
        document.getElementById("nombre_cliente").value = nombre;
    });
}
</script>