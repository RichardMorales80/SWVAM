<?php
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

require_once __DIR__ . '/../config/Conexion.php';
require_once __DIR__ . '/../config/seguridad.php'; 

verificarRoles([2]);

if (!isset($_SESSION['id_usuario'])) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$db = Conexion::conectar();

$nombreUsuario = $_SESSION['nombre'] ?? 'Cliente';
$rolUsuario    = 'Cliente';
$avatar        = BASE_URL . "public/imagenes/avatar.png";

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

// INCLUDE CORRECTO
include(__DIR__ . "/../views/navbar.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Cliente</title>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= BASE_URL ?>public/estilos/cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>public/estilos/estilos.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

<div class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div class="titulo">Panel del Cliente</div>

        <div class="usuario-box">
            <img src="<?= htmlspecialchars($avatar); ?>" alt="Avatar">
            <div class="usuario-info">
                <span class="nombre"><?= htmlspecialchars($nombreUsuario); ?></span>
                <span class="rol"><?= htmlspecialchars($rolUsuario); ?></span>
            </div>
        </div>
    </div>

    <!-- CONTENIDO -->
    <div class="content">

        <!-- CARDS -->
        <div class="cards">
            <div class="card">
                <h3>Mis Facturas</h3>
                <p><?= (int)$totalFacturas; ?></p>
            </div>
        </div>

        <!-- ACCESOS -->
        <div class="seccion">
            <h3>Accesos permitidos</h3>

            <div class="accesos">

                <div class="acceso-card">
                    <h4>Aplicación</h4>
                    <p>Ver catálogo de productos y gestionar carrito.</p>
                    <a href="<?= BASE_URL ?>app/cliente_app.php" class="btn-ir">Entrar</a>
                </div>

                <div class="acceso-card">
                    <h4>Facturas</h4>
                    <p>Consultar y revisar tus facturas registradas.</p>
                    <a href="<?= BASE_URL ?>data/facturas.php" class="btn-ir">Entrar</a>
                </div>

                

            </div>
        </div>

        <!-- GRÁFICA -->
        <div class="seccion">
            <h3>Resumen visual</h3>
            <div class="chart-container">
                <canvas id="graficaCliente"></canvas>
            </div>
        </div>

    </div>
</div>

<!-- SCRIPT GRÁFICA -->
<script>
const ctx = document.getElementById('graficaCliente').getContext('2d');

new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['Facturas', 'Cotizaciones', 'Pedidos'],
        datasets: [{
            label: 'Cantidad',
            data: [
                <?= (int)$totalFacturas; ?>,
                <?= (int)$totalCotizaciones; ?>,
                <?= (int)$totalPedidos; ?>
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

<!-- BLOQUEAR BOTÓN ATRÁS -->
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
            window.location.href = "<?= BASE_URL ?>config/cerrar_sesion.php";
        } else {
            history.pushState(null, null, location.href);
        }
    });
});
</script>

</body>
</html>