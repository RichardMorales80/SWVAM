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
            <a href="public/productos.html" class="main_menu_link">Catálogo productos</a>
        </li>
    </ul>
</nav>

<!-- CONTENIDO -->
<section class="quienes-somos">
    <h2>¿Quiénes Somos?</h2>
    <p>
        Somos una empresa 100% mexicana que ofrece venta de equipos,
        servicio y capacitaciones para la industria en general.
        Contamos con personal capacitado para la elaboración de servicios
        en la industria en general.
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
            Ofrecer productos y servicios de calidad a empresas de cualquier actividad
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
            Ser líder en comercialización y producción de bienes y servicios integrales,
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
        <h2>SOBRE NOSOTROS</h2>
        <p>
            Somos Matthew NDT. Empresa mexicana dedicada a venta de equipos,
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
        <span class="close" id="cerrarLogin">&times;</span>
        <iframe src="public/login.php" width="100%" height="600px" style="border:none;"></iframe>
    </div>
</div>

<!-- MODAL REGISTRO -->
<div id="modalRegistro" class="modal">
    <div class="modal-content">
        <span class="close" id="cerrarRegistro">&times;</span>
        <h2>Registro de Usuario</h2>

        <form id="formRegistro" class="registro form-grid" method="POST">
            <div>
                <label>Nombre</label>
                <input type="text" name="nombre" class="control" required>

                <label>Apellidos</label>
                <input type="text" name="apellidos" class="control" required>

                <label>Correo electrónico</label>
                <input type="email" name="correo" class="control" required>

                <label>Teléfono</label>
                <input type="text" name="telefono" class="control" required>
            </div>

            <div>
                <label>Dirección</label>
                <input type="text" name="direccion" class="control" required>

               
                <div class="tooltip">
                     <label>Contraseña</label>
                    <input type="password" name="pas" id="pas" class="control" required>
                    <span class="tooltiptext">
                        - Mínimo 8 caracteres<br>
                        - Al menos una mayúscula<br>
                        - Al menos una minúscula<br>
                        - Al menos un número<br>
                        - Al menos un símbolo
                    </span>
                </div>
                <ul id="feedback-pass" class="text-danger"></ul>

                <label>Confirmar contraseña</label>
                <input type="password" name="pasrev" id="pasrev" class="control" required>
                <small id="feedback-confirm" class="text-danger"></small>

                <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>

                <input type="submit" class="boton" value="Registrar">
            </div>
        </form>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>
<script>
// MENU
const toggle = document.getElementById("menuToggle");
const menu = document.getElementById("menu");
toggle.addEventListener("click", () => menu.classList.toggle("active"));

// MODALES
const modalLogin = document.getElementById("modalLogin");
const btnLogin = document.getElementById("btnLogin");
const cerrarLogin = document.getElementById("cerrarLogin");
btnLogin.onclick = () => { modalLogin.style.display="block"; document.body.style.overflow="hidden"; }
cerrarLogin.onclick = () => { modalLogin.style.display="none"; document.body.style.overflow="auto"; }
window.onclick = e => { if(e.target==modalLogin) { modalLogin.style.display="none"; document.body.style.overflow="auto"; } }

const modalReg = document.getElementById("modalRegistro");
const btnReg = document.getElementById("btnRegistro");
const cerrarReg = document.getElementById("cerrarRegistro");
const formRegistro = document.getElementById("formRegistro");
btnReg.onclick = () => { modalReg.style.display="block"; document.body.style.overflow="hidden"; formRegistro.reset(); }
cerrarReg.onclick = () => { modalReg.style.display="none"; document.body.style.overflow="auto"; }
window.onclick = e => { if(e.target==modalReg) { modalReg.style.display="none"; document.body.style.overflow="auto"; } }

// VALIDACIONES CLIENTE
const nombreInput = formRegistro.nombre;
const apellidoInput = formRegistro.apellidos;
const telefonoInput = formRegistro.telefono;
const passInput = formRegistro.pas;
const passRevInput = formRegistro.pasrev;
const feedbackPass = document.getElementById("feedback-pass");
const feedbackConfirm = document.getElementById("feedback-confirm");

// Nombres y apellidos solo letras
[nombreInput, apellidoInput].forEach(input => {
    input.addEventListener("input", () => {
        input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g,'');
        if(input.value.length>0) input.classList.add("valid");
        else input.classList.remove("valid");
    });
});

// Teléfono solo números
telefonoInput.addEventListener("input", () => {
    telefonoInput.value = telefonoInput.value.replace(/[^0-9]/g,'');
    if(telefonoInput.value.length>0) telefonoInput.classList.add("valid");
    else telefonoInput.classList.remove("valid");
});

// Contraseña
function validarPasswordJS(password){
    const errores=[];
    if(password.length<8) errores.push("Debe tener al menos 8 caracteres.");
    if(!/[A-Z]/.test(password)) errores.push("Debe contener una letra mayúscula.");
    if(!/[a-z]/.test(password)) errores.push("Debe contener una letra minúscula.");
    if(!/[0-9]/.test(password)) errores.push("Debe contener un número.");
    if(!/[!@#$%^&*(),.?\":{}|<>]/.test(password)) errores.push("Debe contener un símbolo especial.");
    return errores;
}

passInput.addEventListener("input",()=>{
    const errores=validarPasswordJS(passInput.value);
    feedbackPass.innerHTML = errores.length ? errores.map(e=>`<li>${e}</li>`).join('') : "<li class='text-success'>Contraseña válida ✅</li>";
    if(errores.length===0) passInput.classList.add("valid"); else passInput.classList.remove("valid");
});

// Confirmación
passRevInput.addEventListener("input",()=>{
    if(passRevInput.value===passInput.value && passRevInput.value.length>0){
        feedbackConfirm.textContent="";
        passRevInput.classList.add("valid");
    } else {
        feedbackConfirm.textContent="Las contraseñas no coinciden.";
        passRevInput.classList.remove("valid");
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const form = document.getElementById("formRegistro");
    const modalReg = document.getElementById("modalRegistro");

    form.addEventListener("submit", function(e) {

        e.preventDefault();

        let datos = new FormData(this);

        fetch("public/formulario.php", {
            method: "POST",
            body: datos
        })
        .then(res => res.json())
        .then(respuesta => {

            respuesta.forEach(alerta => {

                let tipo = alerta[0];
                let mensaje = alerta[1];

                if (tipo === "success") {

                    Swal.fire({
                        icon: 'success',
                        title: 'Registro exitoso',
                        text: mensaje,
                        confirmButtonColor: '#3085d6',
                        backdrop: true,
                        allowOutsideClick: false,
                        didOpen: () => {
                            const swalContainer = document.querySelector('.swal2-container');
                            if (swalContainer) {
                                swalContainer.style.zIndex = '20000';
                            }
                        }
                    }).then(() => {

                        form.reset();
                        modalReg.style.display = "none";
                        document.body.style.overflow = "auto";

                    });

                } else {

                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: mensaje,
                        confirmButtonColor: '#d33',
                        didOpen: () => {
                            const swalContainer = document.querySelector('.swal2-container');
                            if (swalContainer) {
                                swalContainer.style.zIndex = '20000';
                            }
                        }
                    });

                }

            });

        })
        .catch(err => {
            Swal.fire({
                icon: 'error',
                title: 'Error del servidor',
                text: 'Ocurrió un problema inesperado.',
                didOpen: () => {
                    const swalContainer = document.querySelector('.swal2-container');
                    if (swalContainer) {
                        swalContainer.style.zIndex = '20000';
                    }
                }
            });
            console.error(err);
        });

    });

});
</script>



</body>
</html>

