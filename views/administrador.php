<?php
session_start();

// Simulación de datos de sesión
$_SESSION['usuario'] = 'Richard';
$_SESSION['rol'] = 'Administrador';

$usuario = $_SESSION['usuario'];
$rol = $_SESSION['rol'];

if(!isset($usuario)){
    header("Location: ../public/login.php");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="../public/estilos/estilos.css">
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
        <a href="../privadas/gastos.php">Gastos</a>
        <a href="../data/reporte.php">Reporte</a>
        <a href="../config/cerrar_sesion.php">Salir</a>
      </nav>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
      <header class="header">
        <h1>Opciones de Administrador</h1>
      </header>
      <section class="content">
        <h2>Hola: <?php echo $usuario; ?></h2>
        <h2>Bienvenido a MATTHEW NDT</h2>
      </section>
    </main>
  </div>

  <!-- Script SweetAlert -->
  <script>
    Swal.fire({
      title: '¡Bienvenido!',
      text: 'Hola <?php echo $usuario; ?>, tu rol es: <?php echo $rol; ?>',
      icon: 'success',
      confirmButtonText: 'Continuar'
    });
  </script>
</body>
</html>