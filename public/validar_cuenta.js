document.addEventListener("DOMContentLoaded", function(){

const formRegistro = document.getElementById("formRegistro");
const modalRegistro = document.getElementById("modalRegistro");

const inputs = {
    nombre: formRegistro.nombre,
    apellido1: formRegistro.apellido1,
    apellido2: formRegistro.apellido2,
    correo: formRegistro.correo,
    telefono: formRegistro.telefono,
    calle: formRegistro.calle,
    numero_exterior: formRegistro.numero_exterior,
    numero_interior: formRegistro.numero_interior,
    colonia: formRegistro.colonia,
    ciudad: formRegistro.ciudad,
    estado: formRegistro.estado,
    codigo_postal: formRegistro.codigo_postal,
    pas: formRegistro.pas,
    pasrev: formRegistro.pasrev
};

// ================= UTILIDAD =================
function marcarValido(input, valido){
    input.classList.remove("valid","invalid");

    if(valido === true){
        input.classList.add("valid");
    } 
    else if(valido === false){
        input.classList.add("invalid");
    }
}

// ================= RESET PROFESIONAL =================
function resetFormulario(){

    formRegistro.reset();

    formRegistro.querySelectorAll("input").forEach(input=>{
        input.classList.remove("valid","invalid");
    });

    tooltipPass.innerHTML = "";
    tooltipConfirm.innerHTML = "";
    tooltipConfirm.style.visibility = "hidden";
    tooltipConfirm.style.opacity = "0";

    if(typeof grecaptcha !== "undefined"){
        grecaptcha.reset();
    }
}function resetFormulario(){

    formRegistro.reset();

    // Quitar clases valid/invalid
    formRegistro.querySelectorAll("input").forEach(input=>{
        input.classList.remove("valid","invalid");
    });

    // Limpiar tooltips
    tooltipPass.innerHTML = "";
    tooltipConfirm.innerHTML = "";
    tooltipConfirm.style.visibility = "hidden";
    tooltipConfirm.style.opacity = "0";

    // Resetear reCAPTCHA correctamente
    if(typeof grecaptcha !== "undefined"){
        grecaptcha.reset();
    }

    // Forzar limpieza visual del contenedor captcha
    const recaptcha = document.querySelector(".g-recaptcha");
    if(recaptcha){
        recaptcha.classList.remove("error");
    }
}

// ================= SOLO LETRAS =================
[inputs.nombre, inputs.apellido1, inputs.apellido2, inputs.colonia, inputs.ciudad, inputs.estado]
.forEach(input=>{
    input.addEventListener("input", ()=>{
        input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g,'');
    });

    input.addEventListener("blur", ()=>{
        marcarValido(input, input.value.trim().length > 0);
    });
});

// ================= CALLE =================
inputs.calle.addEventListener("input", ()=>{
    inputs.calle.value = inputs.calle.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s\.\#\,\-\/]/g,'');
});

inputs.calle.addEventListener("blur", ()=>{
    marcarValido(inputs.calle, inputs.calle.value.trim().length > 0);
});

// ================= SOLO NUMEROS =================
[inputs.telefono, inputs.numero_exterior, inputs.numero_interior, inputs.codigo_postal]
.forEach(input=>{
    input.addEventListener("input", ()=>{
        input.value = input.value.replace(/[^0-9]/g,'');
    });

    input.addEventListener("blur", ()=>{
        if(input === inputs.telefono){
            marcarValido(input, /^[0-9]{10}$/.test(input.value));
        } else {
            marcarValido(input, input.value.trim().length > 0);
        }
    });
});

// ================= CORREO =================
const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

inputs.correo.addEventListener("blur", ()=>{
    if(inputs.correo.value.trim() === ""){
        marcarValido(inputs.correo, null);
        return;
    }

    if(regexCorreo.test(inputs.correo.value)){
        validarCuenta('correo', inputs.correo.value).then(respuesta=>{
            respuesta.forEach(r=>{
                if(r[0] !== 'ok'){
                    marcarValido(inputs.correo, false);
                    Swal.fire({
                        icon:'error',
                        title:'Correo no disponible',
                        text:r[1]
                    });
                } else {
                    marcarValido(inputs.correo, true);
                }
            });
        });
    } else {
        marcarValido(inputs.correo, false);
    }
});

// ================= CONTRASEÑA =================
const tooltipPass = document.getElementById("tooltip-pass");
const tooltipConfirm = document.getElementById("tooltip-confirm");

const reglas = [
    {text:"Mínimo 8 caracteres", regex:/^.{8,}$/},
    {text:"Al menos una mayúscula", regex:/[A-Z]/},
    {text:"Al menos una minúscula", regex:/[a-z]/},
    {text:"Al menos un número", regex:/[0-9]/},
    {text:"Al menos un símbolo especial", regex:/[^A-Za-z0-9]/}
];

function passwordEsValida(){
    return reglas.every(r => r.regex.test(inputs.pas.value));
}

function actualizarTooltipPass(){

    let contenido = "";
    let todasValidas = true;

    reglas.forEach(r=>{
        const valido = r.regex.test(inputs.pas.value);
        contenido += (valido ? "✅ " : "❌ ") + r.text + "<br>";
        if(!valido) todasValidas = false;
    });

    tooltipPass.innerHTML = contenido;

    marcarValido(inputs.pas, todasValidas);

    actualizarTooltipConfirm();
}

function actualizarTooltipConfirm(){

    if(inputs.pasrev.value.length === 0){
        tooltipConfirm.style.visibility = "hidden";
        tooltipConfirm.style.opacity = "0";
        marcarValido(inputs.pasrev, null);
        return;
    }

    const validoFinal = inputs.pasrev.value === inputs.pas.value && passwordEsValida();

    tooltipConfirm.style.visibility = "visible";
    tooltipConfirm.style.opacity = "1";

    tooltipConfirm.innerHTML = validoFinal
        ? "✅ Las contraseñas coinciden"
        : "❌ Las contraseñas no coinciden";

    marcarValido(inputs.pasrev, validoFinal);
}

inputs.pas.addEventListener("input", actualizarTooltipPass);
inputs.pasrev.addEventListener("input", actualizarTooltipConfirm);

// ================= VALIDACIÓN AJAX =================
function validarCuenta(campo, valor){
    const formData = new FormData();
    formData.append('campo', campo);
    formData.append('valor', valor);

    return fetch('public/validar_disponibilidad.php',{
        method:'POST',
        body:formData
    })
    .then(res=>res.json())
    .catch(()=>[['error','Error de conexión']]);
}

// ================= ENVÍO =================
formRegistro.addEventListener("submit", function(e){
    e.preventDefault();

    const formData = new FormData(formRegistro);

    fetch('registro_ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(respuesta => {

    const r = respuesta[0]; // solo tomamos la primera respuesta

    if(r[0] === 'success' || r[0] === 'ok'){

        Swal.fire({
            icon:'success',
            title:'Registro exitoso',
            text:r[1]
        }).then(() => {

            resetFormulario();

            if(modalRegistro){
                modalRegistro.style.display = "none";
            }

        });

    } else {

        Swal.fire({
            icon:'error',
            title:'Error',
            text:r[1]
        }).then(() => {

            if(typeof grecaptcha !== "undefined"){
                grecaptcha.reset();
            }

        });

    }

})

// ================= RESET AL RECARGAR =================
window.addEventListener("pageshow", function(){
    resetFormulario();
});

});})