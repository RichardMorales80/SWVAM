<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require_once '../global/configuracion.php';
require_once '../config/Conexion.php';
require __DIR__ . '/../config/seguridad.php';
verificarRol(1,2,3);
$pdo = Conexion::conectar();
include '../app/caqrrito.php';
include '../templetes/cabecera.php';
/* ==========================
   MENSAJE DEL CARRITO
========================== */
if (!empty($mensaje)) {
    echo '
    <div class="alert alert-success text-center">
        ' . htmlspecialchars($mensaje) . '
        <br>
        <a href="mostrarcarro.php" class="btn btn-sm btn-success mt-2">
            Ver carrito
        </a>
    </div>';
}

/* ==========================
   CONSULTA (CON ALIAS)
========================== */
$sql = "
    SELECT 
        id_producto,
        nombre,
        descripcion,
        precio AS precio,
        cantidad AS stock,
        imagen
    FROM productos
    WHERE estado = 1
    ORDER BY id_producto DESC
";


$consulta = $pdo->prepare($sql);
$consulta->execute();
$productos = $consulta->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
<div class="row">

<?php if (count($productos) === 0): ?>
    <p class="text-center">No hay productos registrados</p>
<?php endif; ?>

<?php foreach ($productos as $p): ?>

<?php
    $id     = (int)$p['id_producto'];
    $nombre = $p['nombre'];
    $precio = (float)$p['precio'];
    $stock  = (int)$p['stock'];
    $desc   = $p['descripcion'];
    $img    = $p['imagen'];

    // Rutas imagen
   $rutaFisica = __DIR__ . '/../uploads/' . $img;

$imgPublica = (!empty($img) && file_exists($rutaFisica))
    ? '../uploads/' . $img
    : '../uploads/default.png';

?>

<div class="col-md-4 mb-4">
    <div class="card h-100 shadow-sm">

        <img src="<?= $imgPublica ?>"
             class="card-img-top"
             style="height:260px;object-fit:cover;">

        <div class="card-body d-flex flex-column">

            <h5><?= htmlspecialchars($nombre) ?></h5>

            <p class="text-success fw-bold">
                $<?= number_format($precio, 2) ?>
            </p>

            <p class="text-muted small">
                <?= htmlspecialchars($desc) ?>
            </p>

            <p><strong>Stock disponible:</strong> <?= $stock ?></p>

            <form method="post" class="mt-auto">

               <input type="hidden" name="id"
               value="<?= openssl_encrypt($id, COD, KEY) ?>">

        <input type="hidden" name="nombre"
              value="<?= openssl_encrypt($nombre, COD, KEY) ?>">

        <input type="hidden" name="precio"
              value="<?= openssl_encrypt($precio, COD, KEY) ?>">


                <input type="hidden" name="stock"
                       value="<?= $stock ?>">

                <?php if ($stock > 0): ?>

                    <label for="cantidad_<?= $id ?>">Cantidad</label>
                    <input type="number"
                           id="cantidad_<?= $id ?>"
                           name="cantidad"
                           value="1"
                           min="1"
                           max="<?= $stock ?>"
                           class="form-control mb-2">

                    <button type="submit"
                            name="btnaccion"
                            value="Agregar"
                            class="btn btn-primary w-100">
                        Agregar al carrito
                    </button>

                <?php else: ?>

                    <button class="btn btn-danger w-100" disabled>
                        Agotado
                    </button>

                <?php endif; ?>

            </form>

        </div>
    </div>
</div>

<?php endforeach; ?>

</div>
</div>

<?php include '../templetes/pie.php'; ?>
