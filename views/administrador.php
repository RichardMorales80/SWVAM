<?php
require '../config/inactividad.php'; // Control de sesión + cache + expiración

// VALIDAR SESIÓN Y ROL ADMIN
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 1) {
    header("Location: ../public/login.php");
    exit();
}

// Obtener datos seguros
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
</head>
<body>

<div class="dashboard">

    <!-- Menú lateral -->
    <aside class="sidebar">
        <h2>Administrador</h2>
        <nav>
            <a href="../privadas/modificar_usuarios.php">Modificar usuarios</a>
            <a href="../privadas/ingresa_productos.php">Ingresar productos</a>
            <a href="ingresa_proveedor.php">Ingresar proveedores</a>
            <a href="../app/index.php">Realizar compras</a>
            <a href="../data/ventas.php">Ventas</a>
            <a href="gastos.php">Gastos</a>
            <a href="../data/reporte.php">Reporte</a>
            <a href="../config/cerrar_sesion.php">Salir</a>
        </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
        <header class="header">
            <h1>Panel de Administración</h1>
        </header>

        <section class="content">
            <h2>Bienvenido, <?php echo $nombre . " " . $apellido; ?></h2>
            <p>Correo: <?php echo $correo; ?></p>
        </section>
    </main>

</div>

</body>
</html>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard Administrador</title>
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
      <a href="../privadas/ingresa_proveedor.php">Ingresar proveedores</a>
      <a href="../app/index.php">Realizar compras</a>
      <a href="../data/ventas.php">Ventas</a>
      <a href="../privadas/gastos.php">Gastos</a>
      <a href="../data/reporte.php">Reporte</a>
      <a href="../config/cerrar_sesion.php">Salir</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="main-content">
    <header class="header">
      <h1>Panel de Administración</h1>
    </header>

    <section class="content">
      <h2>Hola: <?php echo $nombre . " " . $apellido; ?></h2>
      <h3>Correo: <?php echo $correo; ?></h3>
      <h2>Bienvenido a MATTHEW NDT</h2>
    </section>
  </main>

</div>

<script>
Swal.fire({
  title: '¡Bienvenido Administrador!',
  text: 'Hola <?php echo $nombre; ?>',
  icon: 'success',
  confirmButtonText: 'Continuar'
});
</script>
<script>
window.addEventListener("pageshow", function (event) {
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

</body>
</html>
