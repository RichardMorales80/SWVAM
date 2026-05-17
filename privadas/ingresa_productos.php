<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);

require '../config/Conexion.php';
require '../config/validaciones.php';
require __DIR__ . '/../config/seguridad.php';

verificarRol(1);
$tipoMenu = "admin";

/* =========================
   VARIABLES
========================= */

$nombre = '';
$descripcion = '';
$precio = 0;
$cantidad = 0;
$id_proveedor = 0;
$imagenNombre = null;
$alertas = [];
$registroExitoso = false;

$db = Conexion::conectar();

/* =========================
   PROVEEDORES
========================= */

$proveedores = $db->query("
SELECT id_proveedor,nombre
FROM proveedores
ORDER BY nombre
")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   FORMULARIO
========================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre        = limpiar($_POST['nombre'] ?? '');
    $descripcion   = limpiar($_POST['descripcion'] ?? '');
    $precio        = $_POST['precio'] ?? 0;
    $cantidad      = $_POST['cantidad'] ?? 0;
    $id_proveedor  = intval($_POST['id_proveedor'] ?? 0);

    if(!validarSoloLetras($nombre)){
        $alertas[]=['error','El nombre solo debe contener letras'];
    }

    if(!validarPrecio($precio)){
        $alertas[]=['error','Precio inválido'];
    }

    if(!validarCantidad($cantidad)){
        $alertas[]=['error','Cantidad inválida'];
    }

    if(empty($alertas)){

        /* ===== SUBIR IMAGEN ===== */
        if (!empty($_FILES['imagen']['name'])) {

            $nombreArchivo = time() . "_" . preg_replace('/\s+/', '_', $_FILES['imagen']['name']);
            $rutaDestino = __DIR__ . "/../uploads/" . $nombreArchivo;

            if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
                $imagenNombre = $nombreArchivo;
            } else {
                $imagenNombre = "default.png";
            }

        } else {
            $imagenNombre = "default.png";
        }

        /* ===== INSERT ===== */
        $stmt = $db->prepare("
        INSERT INTO productos
        (nombre,descripcion,precio,cantidad,imagen,id_proveedor,estado)
        VALUES(?,?,?,?,?,?,1)
        ");

        $stmt->execute([
            $nombre,
            $descripcion,
            $precio,
            $cantidad,
            $imagenNombre,
            $id_proveedor
        ]);

        echo "<script>
window.location = 'ingresa_productos.php?ok=1';
</script>";
exit;

        // limpiar formulario
        $nombre = '';
        $descripcion = '';
        $precio = 0;
        $cantidad = 0;
        $id_proveedor = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ingreso de Productos</title>

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<!-- IMPORTANTE: SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <!-- ================= TOPBAR ================= -->

<div class="topbar">

<div class="topbar-left">
<h2>Ingreso de Productos</h2>
</div>

<div class="topbar-user">

<span class="usuario-nombre">
<?= $_SESSION['nombre'] ?? 'Usuario' ?>
</span>

<img src="../public/imagenes/avatar.png" class="avatar">

</div>

</div>



<?php include("../views/navbar.php"); ?>

<!-- ================= CONTENIDO ================= -->

<div class="main-content">
<div class="container">
<div class="form-container">

<form id="formProducto" method="POST" enctype="multipart/form-data" class="card form-card">

    <h4 class="form-title">Registrar Producto</h4>

    <div class="form-grid">
        <div class="form-group">
            <label>Nombre</label>
            <input type="text" id="nombre" name="nombre" class="form-control"
            value="<?= htmlspecialchars($nombre); ?>">
        </div>

        <div class="form-group">
            <label>Proveedor</label>

            <select name="id_proveedor" class="form-control">
                <option value="">Seleccione</option>

                <?php foreach($proveedores as $p): ?>
                    <option value="<?= $p['id_proveedor']; ?>"
                    <?= $p['id_proveedor']==$id_proveedor?'selected':''; ?>>
                        <?= htmlspecialchars($p['nombre']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label>Descripción</label>
        <textarea name="descripcion" class="form-control"><?= htmlspecialchars($descripcion); ?></textarea>
    </div>

    <div class="form-grid">
        <div class="form-group">
            <label>Precio</label>
            <input type="number" id="precio" step="0.01" name="precio"
            class="form-control"
            value="<?= htmlspecialchars($precio); ?>">
        </div>

        <div class="form-group">
            <label>Cantidad</label>
            <input type="number" id="cantidad" name="cantidad"
            class="form-control"
            value="<?= htmlspecialchars($cantidad); ?>">
        </div>
    </div>

    <div class="form-group">
        <label>Imagen</label>
        <input type="file" name="imagen" class="form-control">
    </div>

    <div class="form-actions">
        <a href="../views/productos.php" class="btn-sistema btn-editar">Ver productos</a>
        <button type="submit" class="btn-sistema btn-guardar">Guardar Producto</button>
    </div>

</form>

</div>
</div>
</div>

<!-- ================= ALERTA SUCCESS ================= -->

<?php if (isset($_GET['ok'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Producto agregado',
    text: 'Se registró correctamente'
});
</script>
<?php endif; ?>

<!-- ================= SEGURIDAD BOTON ATRAS ================= -->

<script>

let salirConfirmado = false;

window.addEventListener("popstate", function () {

Swal.fire({
title: "¿Quieres salir del panel?",
text: "Se cerrará tu sesión por seguridad.",
icon: "warning",
showCancelButton: true,
confirmButtonText: "Sí, salir",
cancelButtonText: "Cancelar"
}).then((result) => {

if (result.isConfirmed) {

salirConfirmado = true;
window.location.href = "../config/cerrar_sesion.php";

}else{

history.pushState(null, null, location.href);

}

});

});

history.pushState(null, null, location.href);

</script>
<script src="/public/validar_producto.js"></script>
</body>
</html></html>