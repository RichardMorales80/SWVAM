<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();

require_once '../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php';

verificarRoles([2]);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../index.php");
    exit();
}

$db = Conexion::conectar();

$nombreUsuario = $_SESSION['nombre'] ?? 'Cliente';
$rolUsuario    = 'Cliente';
$avatar        = "../public/imagenes/avatar.png";

$totalFacturas = 0;
$totalCotizaciones = 0;
$totalPedidos = 0;
try {

    $stmtFacturas = $db->prepare("
        SELECT COUNT(*) AS total 
        FROM facturas f
        INNER JOIN ventas v ON v.id_venta = f.id_venta
        WHERE v.id_usuario = ?
    ");

    $stmtFacturas->execute([$_SESSION['id_usuario']]);

    $row = $stmtFacturas->fetch(PDO::FETCH_ASSOC);

    $totalFacturas = (int)($row['total'] ?? 0);

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

include("../views/navbar.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Cliente</title>

    <link rel="stylesheet" href="../public/estilos/cliente.css">
    <link rel="stylesheet" href="../public/estilos/estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="main">
    <div class="topbar">
        <div class="titulo">Panel del Cliente</div>

        <div class="usuario-box">
            <img src="<?php echo htmlspecialchars($avatar); ?>" alt="Avatar">
            <div class="usuario-info">
                <span class="nombre"><?php echo htmlspecialchars($nombreUsuario); ?></span>
                <span class="rol"><?php echo htmlspecialchars($rolUsuario); ?></span>
            </div>
        </div>
    </div>

    <div class="content">

        <div class="cards">
            <div class="card">
                <h3>Mis Facturas</h3>
                <p><?php echo (int)$totalFacturas; ?></p>
            </div>

           
        </div>

        <div class="seccion">
            <h3>Accesos permitidos</h3>

            <div class="accesos">
                <div class="acceso-card">
                    <h4>Aplicación</h4>
                    <p>Ver catálogo de productos y gestionar carrito.</p>
                    <a href="../app/aplicacion.php" class="btn-ir">Entrar</a>
                </div>

                <div class="acceso-card">
                    <h4>Facturas</h4>
                    <p>Consultar y revisar tus facturas registradas.</p>
                    <a href="../data/facturas.php" class="btn-ir">Entrar</a>
                </div>

                
                <div class="acceso-card">
                    <h4>Generar factura</h4>
                    <p>Generar factura.</p>
                    <a href="../data/facturar_cliente.php" class="btn-ir">Entrar</a>
                </div>

                
            </div>
        </div>

        <div class="seccion">
            <h3>Resumen visual</h3>
            <div class="chart-container">
                <canvas id="graficaCliente"></canvas>
            </div>
        </div>

    </div>
</div>

<script>
const ctx = document.getElementById('graficaCliente').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Facturas', 'Cotizaciones', 'Pedidos'],
        datasets: [{
            label: 'Cantidad',
            data: [
                <?php echo (int)$totalFacturas; ?>,
                <?php echo (int)$totalCotizaciones; ?>,
                <?php echo (int)$totalPedidos; ?>
            ],
            backgroundColor: [
                'rgba(30, 60, 114, 0.8)',
                'rgba(42, 82, 152, 0.8)',
                'rgba(90, 120, 200, 0.8)'
            ],
            borderColor: [
                'rgba(30, 60, 114, 1)',
                'rgba(42, 82, 152, 1)',
                'rgba(90, 120, 200, 1)'
            ],
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});
</script>

<script>
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
</script>

</body>
</html>