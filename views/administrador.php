<?php
require __DIR__ . '/../config/seguridad.php';

verificarRol(1);

$nombre   = htmlspecialchars($_SESSION['nombre']);
$apellido = htmlspecialchars($_SESSION['apellido']);
$correo   = htmlspecialchars($_SESSION['correo']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrador</title>
    <link rel="stylesheet" href="../public/estilos/estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="dashboard">

    <!-- Sidebar -->
    <aside class="sidebar">
        <h2>Administrador</h2>

        

        <nav>
            <a href="../privadas/modificar_usuarios.php">Modificar usuarios</a>
            <a href="../privadas/ingresa_productos.php">Ingresar productos</a>
            <a href="../views/ingresa_proveedor.php">Ingresar proveedores</a>
            <a href="../app/aplicacion.php">Realizar compras</a>
            <a href="../data/ventas.php">Ventas</a>
            <a href="../privadas/gastos.php">Gastos</a>
            <a href="../data/reporte.php">Reporte</a>
            <a href="#" id="btnSalir">Salir</a>
        </nav>
    </aside>

    <main class="main-content">

    <header class="header">
        <div class="user-info">
            <h1>Panel de Administración:</h1>
            <p><strong>Bienvenido, <?php echo $nombre . " " . $apellido; ?></strong></p>
            <p>Correo: <?php echo $correo; ?></p>
            <hr>
        </div>
    </header>

    <section class="content">
        <h2>Bienvenido a MATTHEW NDT</h2>
    </section>

</main>


</div>

<script>
// Bienvenida
Swal.fire({
  title: '¡Bienvenido Administrador!',
  text: 'Hola <?php echo $nombre; ?>',
  icon: 'success',
  confirmButtonText: 'Continuar'
});
</script>

<script>
  // Confirmación al salir
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
        } else {
            history.pushState(null, null, location.href);
        }
    });
});

// Bloquear historial hacia atrás
history.pushState(null, null, location.href);
</script>

<script>
document.addEventListener("visibilitychange", function () {
    if (document.visibilityState === "visible") {
        fetch(window.location.href, { method: "POST" })
            .then(() => {
                window.location.reload();
            })
            .catch(() => {
                window.location.href = "../public/index.php";
            });
    }
});
</script>


</body>
</html>
