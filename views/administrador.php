<?php
 session_start();
	//error_reporting(0);

	$usuario = $_SESSION['nombre'];
	$_SESSION['usuario'] = 'Richard';
	if(!isset($usuario)){
		
		header("Location: ../public/login.php");

	}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="../public/estilos/estilos.css">
       
    <title>Opciones del Administrador</title>

</head>
<body>

<header class="header">
    <div class="container">
        <div class="btn-menu">
            <label for="btn-menu">☰</label>
        </div>
        <div class="logo">
            <h1>Opciones de administrador</h1>
        </div>
        <nav class="menu">
            <a href="../public/index.html">Inicio</a>
            <a href="../config/cerrar_sesion.php">Salir</a>
        </nav>
    </div>
</header>
    

<div class="capa"></div>

<input type="checkbox" id="btn-menu">
<div class="container-menu">
    <div class="cont-menu">
        <nav>
            <a href="../privadas/modificar_usuarios.php">Modificar usuarios</a>
            <a href="../privadas/ingresa_productos.php">Ingresar productos</a>
            <a href="ingresa_proveedor.php">Ingresa proveedores</a>
            <a href="../app/index.php">Realizar compras</a>
            <a href="../data/ventas.php">Ventas</a>
            <a href="../privadas/gastos.php">Gastos</a>
            <a href="../data/reporte.php">Reporte</a>
        </nav>
        <label for="btn-menu">✖️</label>
    </div>
</div>

<br><br><br><br><br>

<h2 class="title">Hola: <?php echo $_SESSION['usuario']; ?></h2>
<h2 class="title">Bienvenido a MATTHEW NDT</h2>
    
    
 

</body>
</html>
