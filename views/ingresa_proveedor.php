<?php
require '../config/inactividad.php';
require '../config/Conexion.php';

// Validar rol administrador
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit();
}

$db = Conexion::conectar();

/* =========================
   MENSAJE POST-REDIRECT
========================= */
if (!empty($_SESSION['success'])) {
    $alertas[] = ['success', $_SESSION['success']];
    unset($_SESSION['success']);
}

/* =========================
   PROCESAR FORMULARIO
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre    = limpiar($_POST['nombre'] ?? '');
    $correo    = limpiar($_POST['correo'] ?? '');
    $telefono  = limpiar($_POST['telefono'] ?? '');
    $direccion = limpiar($_POST['direccion'] ?? '');

    /* ===== VALIDACIONES ===== */

    if (
        empty($nombre) ||
        empty($correo) ||
        empty($telefono) ||
        empty($direccion)
    ) {
        $alertas[] = ['error', 'Todos los campos son obligatorios'];
    }

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $alertas[] = ['error', 'Correo no válido'];
    }

    if (!preg_match('/^[0-9]+$/', $telefono)) {
        $alertas[] = ['error', 'El teléfono solo debe contener números'];
    }

    if (!preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $nombre)) {
        $alertas[] = ['error', 'El nombre solo debe contener letras'];
    }

    /* ===== INSERT ===== */

    if (empty($alertas)) {

        $sql = "INSERT INTO proveedores 
                (nombre, correo, telefono, direccion)
                VALUES (?, ?, ?, ?)";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $nombre,
            $correo,
            $telefono,
            $direccion
        ]);

        $_SESSION['success'] = 'Proveedor registrado correctamente';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ingresar Proveedor</title>

<link rel="stylesheet" href="../public/estilos/principal.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

</head>
<body>

<!-- ================= NAV ================= -->

<ul class="menu">
    <li><a href="../views/administrador.php">Atrás</a></li>
    <li><a href="../config/cerrar_sesion.php">Salir</a></li>
    <li><a href="../privadas/editar_proveedor.php">Editar proveedor</a></li>
</ul>

<br><br><br><br><br><br>

<div class="container">

<h1 class="text-center">Ingreso de Proveedores</h1>

<form method="POST" class="card p-4 shadow mt-4">

<div class="row">

    <div class="col-md-6 mb-3">
        <label>Nombre</label>
        <input type="text" 
               name="nombre" 
               class="form-control"
               pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
               title="Solo letras"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Correo</label>
        <input type="email" 
               name="correo" 
               class="form-control"
               required>
    </div>

</div>

<div class="row">

    <div class="col-md-6 mb-3">
        <label>Teléfono</label>
        <input type="text" 
               name="telefono" 
               class="form-control"
               pattern="[0-9]+"
               maxlength="10"
               title="Solo números"
               required>
    </div>

    <div class="col-md-6 mb-3">
        <label>Dirección</label>
        <input type="text" 
               name="direccion" 
               class="form-control"
               required>
    </div>

</div>

<div class="text-center mt-3">
    <button class="btn btn-primary px-5">
        Guardar Proveedor
    </button>
</div>

</form>

</div>

<!-- ================= ALERTAS ================= -->

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

<!-- ================= JS VALIDACIÓN TIEMPO REAL ================= -->

<script>

// SOLO LETRAS EN NOMBRE
document.querySelector('input[name="nombre"]').addEventListener('input', function(){
    this.value = this.value.replace(/[^a-zA-ZÁÉÍÓÚáéíóúÑñ\s]/g,'');
});

// SOLO NUMEROS EN TELEFONO
document.querySelector('input[name="telefono"]').addEventListener('input', function(){
    this.value = this.value.replace(/[^0-9]/g,'');
});

</script>

</body>
</html>
