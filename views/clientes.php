<?php
session_start();

$usuario = $_SESSION['nombre'] ?? null;

// Validar sesión
if(!$usuario){
    header("Location: ../public/login.php");
    exit();
}

// Nombre visible
$_SESSION['usuario'] = $usuario;
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../public/estilos/estilos.css">

<title>Panel del Cliente</title>

<style>
/* Espacio para menú fijo */
body{
    padding-top:90px;
}

/* Botones del panel */
.btn-panel {
    display: inline-block;
    margin: 10px;
    padding: 12px 25px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 6px;
    font-weight: bold;
    font-size: 16px;
    transition: background 0.3s;
}

.btn-panel:hover {
    background-color: #0056b3;
}
</style>

</head>
<body>

<header class="header">
    <div class="container">

        

        <div class="logo">
            <h1>Área del Cliente</h1>
        </div>

        <nav class="menu">
            <a href="/">Inicio</a>
            <a href="../config/cerrar_sesion.php">Salir</a>
        </nav>

    </div>
</header>
  <br><br><br><br><br>


<div class="container">

    <h2 class="title">Hola: <?= $_SESSION['usuario']; ?></h2>
    <h2 class="title">Bienvenido a MATTHEW NDT</h2>

    <p style="text-align:center;font-size:18px;">
        Desde aquí puedes revisar tu carrito de compras y finalizar tus pedidos.
    </p>

    <!-- BOTONES DE ACCIÓN -->
    <div style="text-align:center; margin-top:25px;">
        <a href="../app/index.php" class="btn-panel"> Realizar Compras</a>
        <a href="../app/mostrarcarro.php" class="btn-panel"> Ver Carrito</a>
    </div>

</div>

</body>
</html>
