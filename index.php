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
<div id="modalLogin" class="modal">
    <div class="modal-content">
        <div style="text-align:center; margin-bottom: 15px;">
            <img src="public/imagenes/logo.png" alt="Logo Matthew NDT" style="max-width:120px;">
        </div>
        <span class="close" id="cerrarLogin">&times;</span>
        <iframe src="public/login.php" width="100%" height="600px" style="border:none;"></iframe>
    </div>
</div>

<!-- MODAL REGISTRO -->
<div id="modalRegistro" class="modal">
    <div class="modal-content registro-modal-large">
        <span class="close" id="cerrarRegistro">&times;</span>

        <!-- LOGO ARRIBA -->
        <div style="text-align:center; margin-bottom: 15px;">
            <img src="public/imagenes/logo.png" alt="Logo Matthew NDT" style="max-width:120px;">
        </div>

        <h2>Registro de usuario</h2>

        <form id="formRegistro" class="form-grid-3" method="POST">

  <!-- COLUMNA 1 (Fila 1-5) -->
  <div class="col">
    <label>Nombre</label>
    <input type="text" id="nombre" name="nombre" class="control" required data-next="apellido1">

    <label>Primer Apellido</label>
    <input type="text" id="apellido1" name="apellido1" class="control" required data-next="apellido2">

    <label>Segundo Apellido</label>
    <input type="text" id="apellido2" name="apellido2" class="control" data-next="correo">

    <label>Correo electrónico</label>
    <input type="email" id="correo" name="correo" class="control" required data-next="telefono">

    <label>Teléfono</label>
    <input type="text" id="telefono" name="telefono" class="control" required data-next="calle">
  </div>

  <!-- COLUMNA 2 (Fila 6-10) -->
  <div class="col">
    <label>Calle</label>
    <input type="text" id="calle" name="calle" class="control" required data-next="numero_exterior">

    <label>Número Exterior</label>
    <input type="text" id="numero_exterior" name="numero_exterior" class="control" required data-next="numero_interior">

    <label>Número Interior</label>
    <input type="text" id="numero_interior" name="numero_interior" class="control" data-next="colonia">

    <label>Colonia</label>
    <input type="text" id="colonia" name="colonia" class="control" required data-next="ciudad">

    <label>Ciudad</label>
    <input type="text" id="ciudad" name="ciudad" class="control" required data-next="estado">
  </div>

  <!-- COLUMNA 3 (Fila 11-14) -->
  <div class="col">
    <label>Estado</label>
    <input type="text" id="estado" name="estado" class="control" required data-next="codigo_postal">

    <label>Código Postal</label>
    <input type="text" id="codigo_postal" name="codigo_postal" class="control" required data-next="pas">

    <div class="tooltip">
    <label>Contraseña</label>
    <input type="password" name="pas" id="pas" class="control" required data-next="pasrev">
    <span class="tooltiptext">
        - Mínimo 8 caracteres<br>
        - Al menos una mayúscula<br>
        - Al menos una minúscula<br>
        - Al menos un número<br>
        - Al menos un símbolo
    </span>
</div>

<ul id="feedback-pass" class="text-danger"></ul>


    <div class="tooltip">
    <label>Confirmar contraseña</label>
    <input type="password" name="pasrev" id="pasrev" class="control" required>
    <span class="tooltiptext" id="tooltip-confirm"></span>
    </div>

  <!-- SUBMIT -->
  <div style="flex-basis:100%; margin-top:10px;">
    <input type="submit" class="boton" value="Registrar">
  </div>
   <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>
 



</form>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- SCRIPTS MODALES Y MENÚ -->
<script>
const toggle = document.getElementById("menuToggle");
const menu = document.getElementById("menu");
toggle.addEventListener("click", () => menu.classList.toggle("active"));

const modalLogin = document.getElementById("modalLogin");
const btnLogin = document.getElementById("btnLogin");
const cerrarLogin = document.getElementById("cerrarLogin");

const modalReg = document.getElementById("modalRegistro");
const btnReg = document.getElementById("btnRegistro");
const cerrarReg = document.getElementById("cerrarRegistro");
const formRegistro = document.getElementById("formRegistro");

/* ASEGURAR QUE AL CARGAR ESTÉN CERRADOS */
window.addEventListener("load", function() {
    modalLogin.style.display = "none";
    modalReg.style.display = "none";
    document.body.style.overflow = "auto";
});
const inputPass = document.getElementById("pas");
const tooltipPass = document.getElementById("tooltip-pass");

/* MOSTRAR EN CELULAR AL HACER FOCUS */
inputPass.addEventListener("focus", function(){
    tooltipPass.style.visibility = "visible";
    tooltipPass.style.opacity = "1";
});

/* OCULTAR CUANDO PIERDE EL FOCO */
inputPass.addEventListener("blur", function(){
    tooltipPass.style.visibility = "hidden";
    tooltipPass.style.opacity = "0";
});

/* LOGIN */
btnLogin.onclick = () => {
    modalLogin.style.display="block";
    document.body.style.overflow="hidden";
};

cerrarLogin.onclick = () => {
    modalLogin.style.display="none";
    document.body.style.overflow="auto";
};

/* REGISTRO */
btnReg.onclick = () => {
    modalReg.style.display="block";
    document.body.style.overflow="hidden";
    formRegistro.reset();
};

cerrarReg.onclick = () => {
    modalReg.style.display="none";
    document.body.style.overflow="auto";
};

/* CERRAR HACIENDO CLICK FUERA (UNA SOLA VEZ, NO DUPLICADO) */
window.addEventListener("click", function(e){
    if(e.target === modalLogin){
        modalLogin.style.display="none";
        document.body.style.overflow="auto";
    }
    if(e.target === modalReg){
        modalReg.style.display="none";
        document.body.style.overflow="auto";
    }
});

// ENVÍO AJAX FORM (sin validaciones, se hacen en validar_cuenta.js)
document.addEventListener("DOMContentLoaded", function() {
    formRegistro.addEventListener("submit", function(e) {
        e.preventDefault();
        let datos = new FormData(this);
        fetch("public/formulario.php", { method:"POST", body:datos })
        .then(res => res.json())
        .then(respuesta => {
            respuesta.forEach(alerta => {
                let tipo = alerta[0];
                let mensaje = alerta[1];
                if(tipo==="success"){
                    Swal.fire({ icon:'success', title:'Registro exitoso', text:mensaje }).then(()=>{
                        formRegistro.reset();
                        modalReg.style.display="none";
                        document.body.style.overflow="auto";
                    });
                } else {
                    Swal.fire({ icon:'error', title:'Error', text:mensaje });
                }
            });
        }).catch(err=>{
            Swal.fire({ icon:'error', title:'Error del servidor', text:'Ocurrió un problema inesperado.' });
            console.error(err);
        });
    });
});
</script>

<!-- VALIDACIONES EXTERNAS -->
<script src="public/validar_cuenta.js"></script>

</body>
</html>