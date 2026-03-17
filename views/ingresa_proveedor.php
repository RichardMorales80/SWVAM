<?php
session_start();

require '../config/Conexion.php';
require '../config/validaciones.php';
require __DIR__ . '/../config/seguridad.php';

verificarRol(1);

$tipoMenu = "admin";
include("../views/navbar.php");

$db = Conexion::conectar();

$alertas = [];

// Inicializar variables
$nombre = '';
$correo = '';
$telefono = '';
$direccion = '';

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
    if (empty($nombre) || empty($correo) || empty($telefono) || empty($direccion)) {
        $alertas[] = ['error', 'Todos los campos son obligatorios'];
    }

    if (!validarSoloLetras($nombre)) {
        $alertas[] = ['error', 'El nombre solo debe contener letras'];
    }

    if (!validarCorreo($correo)) {
        $alertas[] = ['error', 'Correo no válido'];
    }

    if (!validarTelefono($telefono)) {
        $alertas[] = ['error', 'El teléfono debe tener entre 7 y 14 dígitos'];
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

<link rel="stylesheet" href="../public/estilos/estilos.css">
<link rel="stylesheet" href="../public/estilos/registro.css">
<link rel="stylesheet" href="../public/estilos/encabezado.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<!-- TOPBAR -->
<div class="topbar">
    <div class="topbar-left">
        <h4>Ingreso de Proveedores</h4>
    </div>

    <div class="topbar-user">
        <span class="usuario-nombre">
            <?= $_SESSION['nombre_usuario'] ?? 'Usuario' ?>
        </span>
        <img src="../public/imagenes/avatar.png" class="avatar" alt="Avatar">
    </div>
</div>

<div class="main-content">
    <div class="form-container">
        <h1>Ingreso de Proveedores</h1>

        <div class="card">
            <form method="POST">

                <div class="form-group">
                    <label>Nombre</label>
                    <input
                        type="text"
                        id="nombre"
                        name="nombre"
                        class="form-control"
                        placeholder="Nombre del proveedor"
                        required
                        pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+"
                        title="El nombre solo puede contener letras y espacios"
                        oninput="this.value=this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g,'')"
                        value="<?= htmlspecialchars($nombre ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Correo</label>
                    <input 
                        type="email" 
                        id="correo"
                        name="correo" 
                        class="form-control"
                        placeholder="correo@empresa.com"
                        required
                        value="<?= htmlspecialchars($correo ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Teléfono</label>
                    <input
                        type="text"
                        id="telefono"
                        name="telefono"
                        class="form-control"
                        placeholder="5512345678"
                        required
                        pattern="[0-9]{10}"
                        title="El teléfono debe contener solo números (10 dígitos)"
                        inputmode="numeric"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'')"
                        value="<?= htmlspecialchars($telefono ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <input 
                        type="text" 
                        id="direccion"
                        name="direccion" 
                        class="form-control"
                        placeholder="Dirección del proveedor"
                        required
                        value="<?= htmlspecialchars($direccion ?? '') ?>">
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-sistema btn-guardar">
                        Guardar Proveedor
                    </button>

                    <a href="../privadas/editar_proveedor.php" class="btn-sistema btn-editar">
                        Editar Proveedor
                    </a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php if (!empty($alertas)): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
    <?php foreach ($alertas as $a): ?>
    Swal.fire({
        title: "<?= $a[0] === 'success' ? 'Éxito' : 'Error'; ?>",
        text: "<?= addslashes($a[1]); ?>",
        icon: "<?= $a[0]; ?>",
        confirmButtonText: "Aceptar"
    });
    <?php endforeach; ?>
});
</script>
<?php endif; ?>

<!-- ================= SEGURIDAD BOTON ATRAS ================= -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    history.pushState(null, null, location.href);

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
                window.location.href = "../config/cerrar_sesion.php";
            } else {
                history.pushState(null, null, location.href);
            }
        });
    });
});
</script>

</body>
</html>