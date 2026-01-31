<?php
session_start();
require '../config/Conexion.php';

/* =========================
   PROTECCIÓN DE SESIÓN
========================= */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit;
}

/* =========================
   FUNCIÓN LIMPIAR
========================= */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

$alertas = [];
$db = Conexion::conectar();

/* =========================
   MENSAJE POST-REDIRECT
========================= */
if (!empty($_SESSION['success'])) {
    $alertas[] = ['success', $_SESSION['success']];
    unset($_SESSION['success']);
}

/* =========================
   OBTENER PROVEEDORES
========================= */
$proveedores = [];
$stmt = $db->query("SELECT id_proveedor, nombre FROM proveedores ORDER BY nombre");
$proveedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PROCESAR FORMULARIO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre        = limpiar($_POST['nombre'] ?? '');
    $descripcion   = limpiar($_POST['descripcion'] ?? '');
    $precio        = floatval($_POST['precio'] ?? 0);
    $cantidad      = intval($_POST['cantidad'] ?? 0);
    $id_proveedor  = intval($_POST['id_proveedor'] ?? 0);

    if (
        empty($nombre) ||
        empty($descripcion) ||
        $precio <= 0 ||
        $cantidad < 0 ||
        $id_proveedor <= 0
    ) {
        $alertas[] = ['error', 'Todos los campos son obligatorios'];
    }

    /* =========================
       PROCESAR IMAGEN
    ========================= */
   $imagenNombre = null;

if (!empty($_FILES['imagen']['name'])) {

    $permitidos = ['image/jpeg', 'image/png', 'image/webp'];

    if (!in_array($_FILES['imagen']['type'], $permitidos)) {
        $alertas[] = ['error', 'Formato de imagen no permitido'];
    } else {

        $carpeta = __DIR__ . '/../uploads/';

        if (!is_dir($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $imagenNombre = time() . '_producto.' . $extension;
        $rutaFinal = $carpeta . $imagenNombre;

        if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
            $alertas[] = ['error', 'Error al subir la imagen'];
        }
    }
}

    /* =========================
       INSERTAR PRODUCTO
    ========================= */
    if (empty($alertas)) {

        $sql = "INSERT INTO productos
                (nombre, descripcion, precio, cantidad, imagen, id_proveedor)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $nombre,
            $descripcion,
            $precio,
            $cantidad,
            $imagenNombre,
            $id_proveedor
        ]);

        // 🔥 POST → REDIRECT → GET (EVITA DUPLICADOS)
        $_SESSION['success'] = 'Producto registrado correctamente';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ingresar Productos</title>

<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>

<body>

<!-- NAV -->
<ul class="menu">
    <li><a href="../views/administrador.php">Atrás</a></li>
    <li><a href="../config/cerrar_sesion.php">Salir</a></li>
    <li><a href="../views/productos.php">Editar productos</a></li>
</ul>

<br><br><br><br><br><br>

<div class="container">
    <h1 class="text-center">Ingreso de Productos</h1>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow mt-4">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Nombre</label>
                <input type="text" name="nombre" class="form-control" required>
            </div>

            <div class="col-md-6 mb-3">
                <label>Proveedor</label>
                <select name="id_proveedor" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['id_proveedor']; ?>">
                            <?= htmlspecialchars($p['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" required></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Precio</label>
                <input type="number" step="0.01" name="precio" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Cantidad</label>
                <input type="number" name="cantidad" class="form-control" required>
            </div>

            <div class="col-md-4 mb-3">
                <label>Imagen</label>
                <input type="file" name="imagen" class="form-control">
            </div>
        </div>

        <div class="text-center">
            <button class="btn btn-primary px-5">Guardar Producto</button>
        </div>
    </form>
</div>

<?php if (!empty($alertas)): ?>
<script>
<?php foreach ($alertas as $a): ?>
swal({
    title: "<?= $a[0] === 'success' ? 'Éxito' : 'Error'; ?>",
    text: "<?= $a[1]; ?>",
    icon: "<?= $a[0]; ?>"
});
<?php endforeach; ?>
</script>
<?php endif; ?>

</body>
</html>
