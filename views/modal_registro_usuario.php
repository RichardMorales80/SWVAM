<?php
$rutaBase = $rutaBase ?? "";
?>
<div id="modalRegistro" class="modal">

    <div class="modal-content registro-modal-large">

        <span class="close" id="cerrarRegistro">&times;</span>

        <!--  LOGO -->
        <div style="text-align:center; margin-bottom:15px;">
            <img src="<?= $rutaBase ?>public/imagenes/logo.png" 
                 alt="Logo empresa" 
                 style="max-width:120px;">
        </div>

        <form id="formRegistro" class="form-grid-3">

            <input type="hidden" name="modo" value="<?= $modo ?>">

            <!-- COLUMNA 1 -->
            <div class="col">
                <label>Nombre</label>
                <input type="text" id="nombre" name="nombre" class="control">

                <label>Primer Apellido</label>
                <input type="text" id="apellido1" name="apellido1" class="control">

                <label>Segundo Apellido</label>
                <input type="text" id="apellido2" name="apellido2" class="control">

                <label>Correo</label>
                <input type="email" id="correo" name="correo" class="control">

                <label>Teléfono</label>
                <input type="text" id="telefono" name="telefono" class="control">
            </div>

            <!-- COLUMNA 2 -->
            <div class="col">
                <label>Código Postal</label>
                <input type="text" id="codigo_postal" name="codigo_postal" class="control">

                <label>Colonia</label>
                <select id="colonia" name="colonia" class="control">
                    <option value="">Seleccione</option>
                </select>

                <label>Ciudad</label>
                <input type="text" id="ciudad" class="control" readonly>

                <label>Estado</label>
                <input type="text" id="estado" class="control" readonly>

                <label>Calle</label>
                <input type="text" id="calle" name="calle" class="control">
            </div>

            <!-- COLUMNA 3 -->
            <div class="col">
                <label>Número Exterior</label>
                <input type="text" id="numero_exterior" name="numero_exterior" class="control">

                <label>Número Interior</label>
                <input type="text" id="numero_interior" name="numero_interior" class="control">

                <label>Contraseña</label>
                <input type="password" id="pas" name="pas" class="control">

                <div id="tooltip-pass" class="tooltip-box">
                    Mínimo 8 caracteres, mayúscula, número y símbolo
                </div>

                <label>Confirmar contraseña</label>
                <input type="password" id="pasrev" name="pasrev" class="control">

                <!-- CAPTCHA -->
                <div style="flex-basis:100%; margin-top:10px;">
                    <div class="g-recaptcha" data-sitekey="6LfDwd8rAAAAAO5jGdE_f9Es4QHlAH9KOzJWN7aK"></div>	
                </div>

                <div style="flex-basis:100%; margin-top:10px;">
                    <button type="submit" class="boton" id="btnSubmit">
                        <span id="textoBtn">Registrar</span>
                        <span id="loaderBtn" style="display:none;">⏳ Guardando...</span>
                    </button>
                </div>

                <div id="mensajeForm" style="margin-top:10px;"></div>
            </div>

        </form>

    </div>

</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
document.addEventListener("DOMContentLoaded", function(){

const modal = document.getElementById("modalRegistro");
const btn = document.getElementById("btnAbrirRegistro");
const cerrar = document.getElementById("cerrarRegistro");

const form = document.getElementById("formRegistro");
const btnSubmit = document.getElementById("btnSubmit");
const textoBtn = document.getElementById("textoBtn");
const loaderBtn = document.getElementById("loaderBtn");
const mensaje = document.getElementById("mensajeForm");

const pass = document.getElementById("pas");
const tooltip = document.getElementById("tooltip-pass");

const codigoPostal = document.getElementById("codigo_postal");
const estado = document.getElementById("estado");
const ciudad = document.getElementById("ciudad");
const colonia = document.getElementById("colonia");


// ===== TOOLTIP =====
if(pass && tooltip){
    pass.addEventListener("focus", ()=> tooltip.style.display="block");
    pass.addEventListener("blur", ()=> tooltip.style.display="none");
}


// ===== LIMPIAR DIRECCIÓN =====
function limpiarDireccion(){
    if(estado) estado.value="";
    if(ciudad) ciudad.value="";
    if(colonia) colonia.innerHTML='<option value="">Seleccione</option>';
}


codigoPostal?.addEventListener("input", function(){

    let cp = this.value.replace(/\D/g,"");
    this.value = cp;

    //  LIMPIAR MENSAJE CUANDO ESCRIBE
    if(mensaje) mensaje.innerHTML = "";

    limpiarDireccion();

    if(cp.length !== 5) return;

    fetch("<?= $rutaBase ?>public/buscar_cp.php",{
        method:"POST",
        body:new URLSearchParams({codigo_postal:cp})
    })
    .then(r=>r.json())
    .then(data=>{

        if(!data.success){

            mensaje.innerHTML = `<div class="mensaje-error">
                Código postal no encontrado
            </div>`;

            limpiarDireccion();
            return;
        }

        if(estado) estado.value = data.estado;
        if(ciudad) ciudad.value = data.ciudad;

        if(colonia){
            colonia.innerHTML='<option value="">Seleccione</option>';

            data.colonias.forEach(c=>{
                let op = document.createElement("option");
                op.value = c;
                op.textContent = c;
                colonia.appendChild(op);
            });
        }
    });
});


// ===== RESET =====
function resetForm(){
    if(form) form.reset();
    limpiarDireccion();

    document.querySelectorAll("#formRegistro input").forEach(i=>{
        i.classList.remove("valid","invalid");
    });

    if(mensaje) mensaje.innerHTML="";

    if(typeof grecaptcha !== "undefined"){
        grecaptcha.reset();
    }
}


// ===== ABRIR MODAL =====
btn?.addEventListener("click", ()=>{
    if(modal) modal.classList.add("mostrar");
});


// ===== CERRAR =====
function cerrarModal(){
    if(modal) modal.classList.remove("mostrar");
    resetForm();
}

cerrar?.addEventListener("click", cerrarModal);

modal?.addEventListener("click", (e)=>{
    if(e.target === modal) cerrarModal();
});


// ===== SUBMIT =====
form?.addEventListener("submit", async function(e){
    e.preventDefault();

    if(mensaje) mensaje.innerHTML="";

    if(typeof grecaptcha === "undefined" || grecaptcha.getResponse() === ""){
        mensaje.innerHTML = `<div class="mensaje-error">Confirma el captcha</div>`;
        return;
    }

    // VALIDAR COLONIA
    if(colonia.value === ""){
        mensaje.innerHTML = `<div class="mensaje-error">
            Selecciona una colonia válida
        </div>`;
        return;
    }

    btnSubmit.disabled = true;
    textoBtn.style.display = "none";
    loaderBtn.style.display = "inline";

    try{

        const datos = new FormData(form);

        const res = await fetch("<?= $rutaBase ?>public/formulario.php", {
            method: "POST",
            body: datos
        });

        let respuesta;

        try{
            respuesta = await res.json();
        }catch(e){
            console.error("Error servidor");
            mensaje.innerHTML = `<div class="mensaje-error">Error interno del servidor</div>`;
            return;
        }

        let errores = respuesta.filter(r => r[0] === "error");
        let success = respuesta.find(r => r[0] === "success");

        if(errores.length > 0){

            mensaje.innerHTML = `<div class="mensaje-error">
                ${errores.map(e => `<div>• ${e[1]}</div>`).join("")}
            </div>`;

        } 
        else if(success){

            cerrarModal();

//  FORZAR OCULTAR MODAL COMPLETAMENTE
const modal = document.getElementById("modalRegistro");
if(modal){
    modal.style.display = "none";
}

cerrarModal();

setTimeout(() => {
    Swal.fire({
        icon: 'success',
        title: 'Registro exitoso',
        text: success[1],
        confirmButtonColor: '#3085d6',
        backdrop: true
    }).then(() => {
        location.reload(); //  ACTUALIZA AUTOMÁTICAMENTE LA TABLA SIN RECARGAR LA PAGINA
    });
}, 400);

        }

    }catch(err){
        console.error(err);
        mensaje.innerHTML = `<div class="mensaje-error">Error al enviar</div>`;
    }

    btnSubmit.disabled = false;
    textoBtn.style.display = "inline";
    loaderBtn.style.display = "none";
});

});
</script>