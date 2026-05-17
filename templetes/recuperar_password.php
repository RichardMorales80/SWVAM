<?php
require_once '../config/Conexion.php';
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Recuperar contraseña</title>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

<style>

*{
    margin:0;
    padding:0;
    box-sizing:border-box;
}

body{

    font-family:'Poppins', sans-serif;

    background:transparent;

    display:flex;
    justify-content:center;
    align-items:center;

    min-height:100vh;

    padding:20px;
}

/* ================= MODAL ================= */

.modal-box{

    width:100%;
    max-width:460px;

    background:white;

    border-radius:20px;

    padding:35px;

    box-shadow:0 10px 30px rgba(0,0,0,0.25);

    position:relative;

    animation:mostrar .3s ease;
}

/* ================= BOTON CERRAR ================= */

.btn-cerrar{

    position:absolute;

    top:15px;
    right:18px;

    font-size:32px;

    cursor:pointer;

    color:#64748b;

    transition:.3s;

    font-weight:bold;
}

.btn-cerrar:hover{

    color:#ef4444;

    transform:scale(1.1);
}

/* ================= LOGO ================= */

.logo{

    text-align:center;

    margin-bottom:20px;
}

.logo img{

    width:170px;
}

/* ================= TITULO ================= */

h2{

    text-align:center;

    margin-bottom:10px;

    color:#1e293b;

    font-size:36px;
}

/* ================= DESCRIPCION ================= */

.descripcion{

    text-align:center;

    color:#64748b;

    font-size:15px;

    margin-bottom:25px;

    line-height:1.6;
}

/* ================= LABEL ================= */

label{

    display:block;

    margin-bottom:8px;

    color:#334155;

    font-weight:600;

    font-size:15px;
}

/* ================= INPUT ================= */

input{

    width:100%;

    padding:14px;

    border:1px solid #dbeafe;

    border-radius:12px;

    font-size:15px;

    background:#f8fafc;

    margin-bottom:20px;

    transition:0.3s;
}

input:focus{

    outline:none;

    border-color:#2563eb;

    background:white;

    box-shadow:0 0 0 4px rgba(37,99,235,0.1);
}

/* ================= BOTON ================= */

button{

    width:100%;

    padding:14px;

    border:none;

    border-radius:12px;

    background:linear-gradient(135deg,#2563eb,#1d4ed8);

    color:white;

    font-size:16px;

    font-weight:600;

    cursor:pointer;

    transition:0.3s;
}

button:hover{

    transform:translateY(-2px);

    box-shadow:0 10px 20px rgba(37,99,235,0.3);
}

/* ================= FOOTER ================= */

.footer{

    text-align:center;

    margin-top:20px;

    font-size:13px;

    color:#64748b;
}

/* ================= ANIMACION ================= */

@keyframes mostrar{

    from{

        opacity:0;

        transform:translateY(-20px);
    }

    to{

        opacity:1;

        transform:translateY(0);
    }
}

/* ================= RESPONSIVE ================= */

@media(max-width:768px){

    .modal-box{

        padding:25px;
    }

    h2{

        font-size:28px;
    }

    .logo img{

        width:140px;
    }
}

</style>

</head>

<body>

<div class="modal-box">

    <!-- BOTON CERRAR -->
    <span class="btn-cerrar" onclick="cerrarModal()">&times;</span>

    <!-- LOGO -->
    <div class="logo">
        <img src="../img/logo.png" alt="Matthew NDT">
    </div>

    <!-- TITULO -->
    <h2>Recuperar contraseña</h2>

    <!-- DESCRIPCION -->
    <p class="descripcion">
        Ingresa tu correo electrónico para generar un enlace seguro de recuperación.
    </p>

    <!-- FORM -->
    <form id="formRecuperar">

        <label>Correo electrónico</label>

        <input
            type="email"
            name="correo"
            placeholder="Ingresa tu correo"
            required
        >

        <button type="submit">
            Enviar enlace de recuperación
        </button>

    </form>

    <!-- FOOTER -->
    <div class="footer">
        Matthew NDT Technology & Solutions
    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>

function cerrarModal(){

    /* MODAL LOGIN */
    const modalLogin =
    window.parent.document
    .getElementById("modalLogin");

    if(modalLogin){

        modalLogin.style.display = "none";
        modalLogin.classList.remove("mostrar");
    }

    /* RESTAURAR BODY */
    window.parent.document.body.style.overflow = "auto";

    /* QUITAR FONDO OSCURO */
    const modales =
    window.parent.document.querySelectorAll(".modal");

    modales.forEach(modal => {

        modal.style.display = "none";
        modal.classList.remove("mostrar");
    });

    /* RECARGAR LIMPIO */
    setTimeout(() => {

        window.parent.location.reload();

    }, 100);
}

/* ENVIAR FORMULARIO */
document.getElementById("formRecuperar")
.addEventListener("submit", function(e){

    e.preventDefault();

    const correo =
    document.querySelector('input[name="correo"]').value;

    fetch("../templetes/procesar_recuperacion.php", {

        method: "POST",

        headers: {
            "Content-Type":
            "application/x-www-form-urlencoded"
        },

        body:
        "correo=" + encodeURIComponent(correo)

    })

    .then(response => response.text())

    .then(data => {

        Swal.fire({

            icon: 'success',
            title: 'Correo enviado',
            text: 'Enlace enviado correctamente',
            confirmButtonColor: '#2563eb'

        }).then(() => {

            cerrarModal();

        });

    })

    .catch(error => {

       window.top.Swal.fire({

    icon: "success",

    title: "Correo enviado",

    html: `
    
        Se generó el enlace correctamente.
        <br><br>

        <a href="<?php echo $link; ?>"
        target="_blank"
        style="
            background:#2563eb;
            color:white;
            padding:12px 20px;
            border-radius:10px;
            text-decoration:none;
            font-weight:bold;
            display:inline-block;
        ">
            Cambiar contraseña
        </a>

    `,

    confirmButtonText: "Cerrar",

    confirmButtonColor: "#2563eb"

}).then(() => {

    // CERRAR MODAL
    const modal = window.top.document.getElementById("modalLogin");

    if(modal){

        modal.style.display = "none";
    }

    // RESTAURAR BODY
    window.top.document.body.style.overflow = "auto";

    // QUITAR FONDO OSCURO
    window.top.document.body.classList.remove("modal-open");

});

    });

});

</script>

</body>
</html>