<!DOCTYPE html>
<html lang="es">

<head>
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Página principal - Matthew NDT</title>

<link rel="icon" href="../public/imagenes/logo.png">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CSS PRINCIPAL -->
<link rel="stylesheet" href="public/estilos/encabezado.css">
<!-- CSS MODAL REGISTRO -->
<link rel="stylesheet" href="public/estilos/registro.css">
<link rel="stylesheet" href="public/estilos/estilos.css">
<link rel="stylesheet" href="public/estilos/login.css">
</head>

<body>

<!-- FONDO -->
<div class="background-shapes">
    <div class="circle circle1"></div>
    <div class="circle circle2"></div>
    <div class="circle circle3"></div>
</div>

<!-- NAV -->
<nav class="main_nav">
    <div class="menu_toggle" id="menuToggle">☰</div>
    <ul class="menu" id="menu">
        <li class="logo-item">
            <a href="#"><img src="public/imagenes/logo.png" class="logo" alt="logo"></a>
        </li>
        <li>
            <button id="btnRegistro" class="main_menu_link">Crear cuenta</button>
        </li>
        <li>
            <button id="btnLogin" class="main_menu_link">Iniciar sesión</button>
        </li>
        <li>
            <a href="public/productos.html" class="main_menu_link">Catálogo de productos</a>
        </li>
    </ul>
</nav>

<!-- CONTENIDO -->
<section class="quienes-somos">
    <h2>¿Quiénes somos?</h2>
<p>
    Somos una empresa 100% mexicana que ofrece venta de equipos,
    servicio y capacitaciones para la industria en general.
    Contamos con personal capacitado para la elaboración de servicios
    en la industria.
</p>
</section>

<section class="carrusel">
    <div class="slider">
        <ul class="fotos">
            <li><img src="img/VIDEOSCOPIO.png"></li>
            <li><img src="img/FLUXO-8702.png"></li>
            <li><img src="img/FLUXO-P123.png"></li>
            <li><img src="img/FLUXO-R175.png"></li>
            <li><img src="img/FLUXO-S190.png"></li>
            <li><img src="img/WELD-W6-306.png"></li>
            <li><img src="img/YUGO_Y7.png"></li>
            <li><img src="img/MEDIDOR_ESPESORES.png"></li>
            <li><img src="img/LAMP_SL8104.PNG"></li>
        </ul>
    </div>
</section>

<div class="cuerpo">
    <section class="section">
        <img src="img/mision.png" class="imagenes">
        <h2 class="title">Misión</h2>
        <p class="copy">
            Ofrecer productos y servicios de calidad a empresas de cualquier actividad,
            ofreciendo siempre el mejor producto del mercado para satisfacer las necesidades del cliente.
        </p>
    </section>

    <section class="section">
        <img src="img/valores1.png" class="imagenes">
        <h2 class="title">Valores</h2>
        <p class="copy">
            Siempre podrás contar con nuestro optimismo, disciplina y transparencia.
            ¡Lo más importante eres tú!
        </p>
    </section>

    <section class="section">
        <img src="img/vision1.png" class="imagenes">
        <h2 class="title">Visión</h2>
        <p class="copy">
            Ser líder en la comercialización y producción de bienes y servicios integrales,
            comprometida con el bienestar del país al generar empleo.
        </p>
    </section>
</div>

<!-- FOOTER -->
<div class="wave-container">
    <svg viewBox="0 0 1440 120" preserveAspectRatio="none" class="wave">
        <path d="M0,60 C240,100 480,20 720,50 960,80 1200,110 1440,60 L1440,0 L0,0 Z"></path>
    </svg>
</div>

<div class="zona1">
    <div class="box"><img src="public/imagenes/logo.png" class="logo-footer"></div>
    <div class="box">
        <h2>Sobre nosotros</h2>
<p>
    Somos Matthew NDT, empresa mexicana dedicada a la venta de equipos,
    productos NDT, servicios y capacitaciones para la industria.
</p>
    </div>
    <div class="box">
        <h2>SÍGUENOS</h2>
        <div class="red-social">
            <a href="#" class="fa-brands fa-facebook"></a>
            <a href="#" class="fa-brands fa-youtube"></a>
            <a href="#" class="fa-solid fa-phone"></a>
            <a href="#" class="fa-solid fa-envelope"></a>
        </div>
    </div>
</div>

<div class="zona2">
    <small>&copy; 2026 Matthew NDT - Todos los derechos reservados</small>
</div>

<!-- WHATSAPP -->
<a href="https://wa.me/525548929587" class="whatsapp-float" target="_blank">
    <img src="https://cdn-icons-png.flaticon.com/512/124/124034.png">
</a>

<!-- MODAL LOGIN -->
<div id="modalLogin" class="modal modal-login">

<div class="modal-content login-modal">

<span class="close" id="cerrarLogin">&times;</span>

<iframe src="public/login.php" class="login-frame" scrolling="no"></iframe>

</div>

</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const btnLogin = document.getElementById("btnLogin");
    const modalLogin = document.getElementById("modalLogin");
    const cerrarLogin = document.getElementById("cerrarLogin");

    if (btnLogin && modalLogin) {
        btnLogin.addEventListener("click", function () {
            modalLogin.classList.add("mostrar");
            document.body.style.overflow = "hidden";
        });
    }

    if (cerrarLogin && modalLogin) {
        cerrarLogin.addEventListener("click", function () {
            modalLogin.classList.remove("mostrar");
            document.body.style.overflow = "auto";
        });
    }

    window.addEventListener("click", function (e) {
        if (e.target === modalLogin) {
            modalLogin.classList.remove("mostrar");
            document.body.style.overflow = "auto";
        }
    });
});
</script>
<?php
$rutaBase = "";
include("views/modal_registro_usuario.php");
?>
<!-- VALIDACIONES EXTERNAS -->
<script src="public/validar_cuenta.js"></script>

</body>
</html>