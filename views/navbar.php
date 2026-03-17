<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idRol = $_SESSION['id_rol'] ?? 0;
?>

<aside class="sidebar-app">

    <div class="logo-container">
        <img src="../public/imagenes/logo.png" class="logo" alt="Logo">
    </div>

    <?php if ($idRol == 1): ?>
        <h2>Administrador</h2>

        <nav>
            <a href="../views/administrador.php">
                <i class="bi bi-house"></i> Inicio
            </a>

            <a href="../privadas/modificar_usuarios.php">
                <i class="bi bi-people"></i> Modificar usuarios
            </a>

            <a href="../privadas/ingresa_productos.php">
                <i class="bi bi-box-seam"></i> Ingresar productos
            </a>

            <a href="../views/ingresa_proveedor.php">
                <i class="bi bi-truck"></i> Ingresar proveedores
            </a>

            <a href="../app/aplicacion.php">
                <i class="bi bi-cart-check"></i> Realizar compras
            </a>

            <a href="../data/ventas.php">
                <i class="bi bi-graph-up"></i> Ventas
            </a>
            <a href="../data/facturas.php">
                <i class="bi bi-receipt"></i> Facturas
            </a>

            <a href="../privadas/gastos.php">
                <i class="bi bi-cash-stack"></i> Gastos
            </a>

            <a href="../data/reporte.php">
                <i class="bi bi-file-earmark-text"></i> Reporte
            </a>

            <a href="../config/cerrar_sesion.php" class="salir">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </nav>

    <?php elseif ($idRol == 3): ?>
        <h2>Vendedor</h2>

        <nav>
            <a href="../views/vendedor.php">
                <i class="bi bi-house"></i> Inicio
            </a>

            <a href="../app/aplicacion.php">
                <i class="bi bi-cart-check"></i> Realizar compras
            </a>

            <a href="../data/facturas.php">
                <i class="bi bi-receipt"></i> Facturas
            </a>

            <a href="../privadas/gastos.php">
                <i class="bi bi-cash-stack"></i> Gastos
            </a>

            <a href="../data/ventas.php">
                <i class="bi bi-graph-up"></i> Ventas
            </a>

            <a href="../privadas/modificar_usuarios.php">
                <i class="bi bi-people"></i> Usuarios
            </a>

            <a href="../config/cerrar_sesion.php" class="salir">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </nav>

    <?php elseif ($idRol == 2): ?>
        <h2>Cliente</h2>

        <nav>
            <a href="/SWVAM/public/cliente.php">
                <i class="bi bi-house"></i> Inicio
           </a>

            <a href="../app/aplicacion.php">
                <i class="bi bi-cart-check"></i> Aplicación
            </a>

            <a href="../data/facturas.php">
                <i class="bi bi-receipt"></i> Facturas
            </a>

            <a href="../config/cerrar_sesion.php" class="salir">
                <i class="bi bi-box-arrow-right"></i> Salir
            </a>
        </nav>
    <?php endif; ?>

</aside>