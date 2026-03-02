<?php
session_start();
require '../config/Conexion.php';
require '../config/validaciones.php'; // Usamos tus funciones ya definidas
require __DIR__ . '/../config/seguridad.php';
verificarRol(1,2);

/* =========================
   PROTECCIÓN DE SESIÓN
========================= */
if (!isset($_SESSION['id_usuario']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../index.php");
    exit;
}


/* =========================
   INICIALIZAR VARIABLES
========================= */
$nombre = '';
$descripcion = '';
$precio = 0;
$cantidad = 0;
$id_proveedor = 0;
$imagenNombre = null;
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
$proveedores = $db->query("SELECT id_proveedor, nombre FROM proveedores ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   PROCESAR FORMULARIO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre        = limpiar($_POST['nombre'] ?? '');
    $descripcion   = limpiar($_POST['descripcion'] ?? '');
    $precio        = $_POST['precio'] ?? 0;
    $cantidad      = $_POST['cantidad'] ?? 0;
    $id_proveedor  = intval($_POST['id_proveedor'] ?? 0);

    // =========================
    // VALIDACIONES REUTILIZANDO validaciones.php
    // =========================
    if (!validarSoloLetras($nombre)) {
        $alertas[] = ['error', 'El nombre solo debe contener letras.'];
    }

    if (empty($descripcion)) {
        $alertas[] = ['error', 'La descripción no puede estar vacía.'];
    }

    if (!validarPrecio($precio)) {
        $alertas[] = ['error', 'El precio debe ser un número mayor a 0.'];
    }

    if (!validarCantidad($cantidad)) {
        $alertas[] = ['error', 'La cantidad debe ser un número entero igual o mayor a 0.'];
    }

    if ($id_proveedor <= 0) {
        $alertas[] = ['error', 'Debes seleccionar un proveedor.'];
    }

    // =========================
    // PROCESAR IMAGEN
    // =========================
    if (!empty($_FILES['imagen']['name'])) {
        $permitidos = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($_FILES['imagen']['type'], $permitidos)) {
            $alertas[] = ['error', 'Formato de imagen no permitido'];
        } else {
            $carpeta = __DIR__ . '/../uploads/';
            if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

            $extension = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
            $imagenNombre = time() . '_producto.' . $extension;
            $rutaFinal = $carpeta . $imagenNombre;

            if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaFinal)) {
                $alertas[] = ['error', 'Error al subir la imagen'];
            }
        }
    }

    // =========================
    // INSERTAR PRODUCTO SOLO SI NO HAY ERRORES
    // =========================
    if (empty($alertas)) {
        $stmt = $db->prepare("INSERT INTO productos (nombre, descripcion, precio, cantidad, imagen, id_proveedor) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nombre, $descripcion, $precio, $cantidad, $imagenNombre, $id_proveedor]);

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
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
</head>
<body>

<!-- NAV -->
<nav class="main_nav">
    <div class="menu_toggle" id="menuToggle">☰</div>
    <ul class="menu" id="menu">
        <li class="logo-item">
            <a href="#"><img src="../public/imagenes/logo.png" class="logo" alt="logo"></a>
        </li>
        <li>
            <a href="../views/administrador.php" class="main_menu_link">Atrás</a>
        </li>
    </ul>
</nav>

<div class="container">
    <h1 class="text-center">Ingreso de Productos</h1>

    <form method="POST" enctype="multipart/form-data" class="card p-4 shadow mt-4">

        <div class="row">
            <!-- Campo Nombre con validaciones -->
<div class="col-md-6 mb-3">
    <label>Nombre</label>
    <input type="text" name="nombre" class="form-control" required
           pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
           title="Solo letras y espacios"
           value="<?= htmlspecialchars($nombre); ?>">
           <?php generarJSValidarSoloLetras('nombre'); ?>
</div>

            <div class="col-md-6 mb-3">
                <label>Proveedor</label>
                <select name="id_proveedor" class="form-control" required>
                    <option value="">Seleccione</option>
                    <?php foreach ($proveedores as $p): ?>
                        <option value="<?= $p['id_proveedor']; ?>" <?= $p['id_proveedor'] == $id_proveedor ? 'selected' : ''; ?>>
                            <?= htmlspecialchars($p['nombre']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label>Descripción</label>
            <textarea name="descripcion" class="form-control" required><?= htmlspecialchars($descripcion); ?></textarea>
        </div>

        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Precio</label>
                <input type="number" step="0.01" name="precio" class="form-control" required value="<?= htmlspecialchars($precio); ?>">
            </div>

            <div class="col-md-4 mb-3">
                <label>Cantidad</label>
                <input type="number" name="cantidad" class="form-control" required value="<?= htmlspecialchars($cantidad); ?>">
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