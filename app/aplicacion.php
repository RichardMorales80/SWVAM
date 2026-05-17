<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

require_once '../global/configuracion.php';
require_once '../config/Conexion.php';
require_once '../config/seguridad.php';

verificarRoles([1,3]);

$pdo = Conexion::conectar();


include __DIR__ . '/caqrrito.php';

// =========================
// OBTENER CLIENTES + CARRITO
// =========================
$stmtClientes = $pdo->prepare("
SELECT u.id_usuario, u.correo,
       IFNULL(SUM(c.cantidad),0) AS total_carrito
FROM usuarios u
LEFT JOIN carrito c ON u.id_usuario = c.id_usuario
WHERE u.id_rol = 2
GROUP BY u.id_usuario
");
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// =========================
// SELECCIONAR CLIENTE
// =========================
if (isset($_POST['seleccionar_cliente'])) {

    $_SESSION['id_cliente'] = $_POST['id_cliente'];
    $_SESSION['nombre_cliente'] = $_POST['nombre_cliente'];

    header("Location: aplicacion.php");
    exit;
}

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
$id_cliente = $_SESSION['id_cliente'] ?? null;

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
    timer: 1500,
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
<!-- ================= CLIENTE ================= -->

<div class="container mt-3">

<form method="post" style="display:flex; gap:10px; align-items:center;">

<select name="id_cliente" id="selectCliente" required class="form-control">

<option value="">-- Seleccionar cliente --</option>

<?php foreach($clientes as $c): ?>

<?php
$color = ($c['total_carrito'] > 0) ? 'style="color:green; font-weight:bold;"' : '';
?>

<option value="<?= $c['id_usuario'] ?>" 
        data-nombre="<?= htmlspecialchars($c['correo']) ?>" 
        <?= $color ?>>
    <?= htmlspecialchars($c['correo']) ?> (<?= $c['total_carrito'] ?>)
</option>

<?php endforeach; ?>

</select>

<input type="hidden" name="nombre_cliente" id="nombre_cliente">

<button type="submit" name="seleccionar_cliente" class="btn btn-success">
Seleccionar Cliente
</button>

</form>

</div>

<!-- CLIENTE ACTUAL -->
<div style="text-align:center; margin-top:10px;">
Cliente actual: 
<strong>
<?= $_SESSION['nombre_cliente'] ?? 'No seleccionado' ?>
</strong>
</div>

<!-- BOTÓN CARRITO -->
<div style="display:flex; justify-content:center; margin:15px 0;">

    <a href="../app/mostrarcarro.php" class="btn-carrito">
        
        <span class="icono-carrito">🛒</span>

        <span>
            Carrito
            <span class="contador-carrito">
                <?= $totalCarrito ?>
            </span>
        </span>

    </a>

</div>

<style>

.btn-carrito{
    display:flex;
    align-items:center;
    gap:12px;

    background: linear-gradient(135deg, #28a745, #20c997);

    color:white;
    text-decoration:none;

    padding:14px 24px;

    border-radius:15px;

    font-size:18px;
    font-weight:bold;

    box-shadow:0 4px 10px rgba(0,0,0,0.2);

    transition:all 0.3s ease;
}

.btn-carrito:hover{

    transform:translateY(-3px) scale(1.03);

    box-shadow:0 6px 14px rgba(0,0,0,0.3);

    background: linear-gradient(135deg, #218838, #17a589);
}

.icono-carrito{
    font-size:26px;
}

.contador-carrito{

    background:white;
    color:#28a745;

    padding:4px 10px;

    border-radius:50px;

    margin-left:8px;

    font-size:15px;
    font-weight:bold;
}

</style>

<!-- ================= PRODUCTOS ================= -->

<div class="container">
<div class="row">
<?php

$productosPorPagina = 8;

$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;

$inicio = ($pagina - 1) * $productosPorPagina;

$totalProductos = count($productos);

$totalPaginas = ceil($totalProductos / $productosPorPagina);

$productosPagina = array_slice($productos, $inicio, $productosPorPagina);

?>
<?php foreach($productosPagina as $p): ?>

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

<div class="col-12 col-sm-6 col-md-3 producto-item">

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
<div class="pagination-custom">

<?php for($i=1; $i <= $totalPaginas; $i++): ?>

<a href="?pagina=<?= $i ?>"
class="<?= ($pagina == $i) ? 'active' : '' ?>">
<?= $i ?>
</a>

<?php endfor; ?>

</div>
<script>
document.getElementById("selectCliente").addEventListener("change", function(){
    let nombre = this.options[this.selectedIndex].getAttribute("data-nombre");
    document.getElementById("nombre_cliente").value = nombre;
});
</script>