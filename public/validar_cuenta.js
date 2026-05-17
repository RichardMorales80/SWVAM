
document.addEventListener("DOMContentLoaded", function () {
const esPublico = document.querySelector('.g-recaptcha') !== null;
    const formRegistro = document.getElementById("formRegistro");
     if (!formRegistro) return;

    if (!formRegistro) return;

    const inputs = {
        nombre: document.getElementById("nombre") || document.getElementById("edit_primer_nombre"),
        apellido1: document.getElementById("apellido1") || document.getElementById("edit_primer_apellido"),
        apellido2: document.getElementById("apellido2") || document.getElementById("edit_segundo_apellido"),
        correo: document.getElementById("correo") || document.getElementById("edit_correo"),
        telefono: document.getElementById("telefono") || document.getElementById("edit_telefono"),
        calle: document.getElementById("calle") || document.getElementById("edit_calle"),
        numero_exterior: document.getElementById("numero_exterior") || document.getElementById("edit_num_ext"),
        numero_interior: document.getElementById("numero_interior") || document.getElementById("edit_num_int"),
        colonia: document.getElementById("colonia") || document.getElementById("edit_colonia"),
        ciudad: document.getElementById("ciudad") || document.getElementById("edit_ciudad"),
        estado: document.getElementById("estado") || document.getElementById("edit_estado"),
        codigo_postal: document.getElementById("codigo_postal") || document.getElementById("edit_cp"),
        pas: document.getElementById("pas") || document.getElementById("edit_password"),
        pasrev: document.getElementById("pasrev") || document.getElementById("edit_confirmar_password")
    };

    const tooltipPass = document.getElementById("tooltip-pass");
    const tooltipConfirm = document.getElementById("tooltip-confirm");
    // VALIDAR PASSWORD
if(inputs.pas){
    inputs.pas.addEventListener("input", function(){

        const valor = inputs.pas.value.trim();

        if(valor.length >= 8){
            inputs.pas.classList.add("valid");
            inputs.pas.classList.remove("invalid");
        }else{
            inputs.pas.classList.add("invalid");
            inputs.pas.classList.remove("valid");
        }

    });
}

// VALIDAR CONFIRMAR PASSWORD
if(inputs.pasrev){
    inputs.pasrev.addEventListener("input", function(){

        const pass = inputs.pas.value.trim();
        const confirm = inputs.pasrev.value.trim();

        if(confirm !== "" && confirm === pass){
            inputs.pasrev.classList.add("valid");
            inputs.pasrev.classList.remove("invalid");
        }else{
            inputs.pasrev.classList.add("invalid");
            inputs.pasrev.classList.remove("valid");
        }

    });
}

    function marcarValido(input, valido) {
        if (!input) return;

        input.classList.remove("valid", "invalid");

        if (valido === true) input.classList.add("valid");
        if (valido === false) input.classList.add("invalid");
    }

    function resetFormulario() {

    formRegistro.reset();

    formRegistro.querySelectorAll("input, select").forEach(campo => {
        campo.classList.remove("valid", "invalid");
    });

    // PROTECCIÓN TOTAL reCAPTCHA
    if (typeof grecaptcha !== "undefined") {
        try {
            let response = grecaptcha.getResponse();
            if (response !== undefined) {
                grecaptcha.reset();
            }
        } catch (e) {
            console.warn("reCAPTCHA no inicializado");
        }
    }
}

    function validarTextoSoloLetras(input, obligatorio = true) {
        if (!input) return;

        input.addEventListener("input", () => {
            input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");

            const valor = input.value.trim();

            if (!obligatorio && valor === "") {
                marcarValido(input, null);
                return;
            }

            marcarValido(input, valor.length > 0);
        });
    }

    validarTextoSoloLetras(inputs.nombre, true);
    validarTextoSoloLetras(inputs.apellido1, true);
    validarTextoSoloLetras(inputs.apellido2, false);

    if (inputs.calle) {
        inputs.calle.addEventListener("input", () => {
            inputs.calle.value = inputs.calle.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]/g, "");
            marcarValido(inputs.calle, inputs.calle.value.trim().length > 0);
        });
    }

    if (inputs.telefono) {
        inputs.telefono.addEventListener("input", () => {
            inputs.telefono.value = inputs.telefono.value.replace(/[^0-9]/g, "");
            marcarValido(inputs.telefono, /^[0-9]{10}$/.test(inputs.telefono.value));
        });
    }

    function soloNumeros(input, obligatorio = true) {
        if (!input) return;

        input.addEventListener("input", () => {
            input.value = input.value.replace(/[^0-9]/g, "");

            if (!obligatorio && input.value.trim() === "") {
                marcarValido(input, null);
                return;
            }

            marcarValido(input, input.value.trim().length > 0);
        });
    }

    soloNumeros(inputs.numero_exterior, true);
    soloNumeros(inputs.numero_interior, false);

    if (inputs.codigo_postal) {
        inputs.codigo_postal.addEventListener("input", () => {
            inputs.codigo_postal.value = inputs.codigo_postal.value.replace(/[^0-9]/g, "");
            marcarValido(inputs.codigo_postal, /^[0-9]{5}$/.test(inputs.codigo_postal.value));
        });
    }

    if (inputs.colonia) {
        inputs.colonia.addEventListener("change", () => {
            marcarValido(inputs.colonia, inputs.colonia.value !== "");
        });
    }

    if (inputs.ciudad) {
        inputs.ciudad.addEventListener("input", () => {
            marcarValido(inputs.ciudad, inputs.ciudad.value.trim() !== "");
        });
    }

    if (inputs.estado) {
        inputs.estado.addEventListener("input", () => {
            marcarValido(inputs.estado, inputs.estado.value.trim() !== "");
        });
    }

    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    if (inputs.correo) {
        inputs.correo.addEventListener("input", () => {
            const valor = inputs.correo.value.trim();

            if (valor === "") {
                marcarValido(inputs.correo, null);
                return;
            }

            marcarValido(inputs.correo, regexCorreo.test(valor));
        });
    }

    const reglas = [
        { regex: /^.{8,}$/ },
        { regex: /[A-Z]/ },
        { regex: /[a-z]/ },
        { regex: /[0-9]/ },
        { regex: /[^A-Za-z0-9]/ }
    ];

    function passwordValida() {
        return reglas.every(r => r.regex.test(inputs.pas?.value || ""));
    }

    function validarPassword() {
        if (!inputs.pas) return;

        marcarValido(inputs.pas, passwordValida());
        validarConfirmacion();
    }

    function validarConfirmacion() {
        if (!inputs.pasrev) return;

        if (inputs.pasrev.value === "") {
            marcarValido(inputs.pasrev, null);
            return;
        }

        const ok = inputs.pasrev.value === inputs.pas.value && passwordValida();
        marcarValido(inputs.pasrev, ok);
    }

    if (inputs.pas) inputs.pas.addEventListener("input", validarPassword);
    if (inputs.pasrev) inputs.pasrev.addEventListener("input", validarConfirmacion);

    window.addEventListener("pageshow", resetFormulario);

});


/* ================= VALIDACION GLOBAL PARA MODAL ================= */

document.addEventListener("input", function(e){

    const id = e.target.id;

    if(id === "edit_primer_nombre" || id === "edit_primer_apellido" || id === "edit_segundo_apellido"){
        e.target.value = e.target.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");
    }

    if(id === "edit_telefono" || id === "edit_cp" || id === "edit_num_ext" || id === "edit_num_int"){
        e.target.value = e.target.value.replace(/[^0-9]/g, "");
    }

    if(id === "edit_calle"){
        e.target.value = e.target.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]/g, "");
    }

});

// ================= BLOQUEO TOTAL TECLADO =================
document.addEventListener("keypress", function(e){

    const id = e.target.id;

    // SOLO LETRAS (nombre y apellidos)
    if(id === "edit_primer_nombre" || 
       id === "edit_primer_apellido" || 
       id === "edit_segundo_apellido"){

        if(!/[A-Za-zÁÉÍÓÚáéíóúÑñ\s]/.test(e.key)){
            e.preventDefault();
        }
    }

    // SOLO NUMEROS
    if(id === "edit_telefono" || 
       id === "edit_cp" || 
       id === "edit_num_ext" || 
       id === "edit_num_int"){

        if(!/[0-9]/.test(e.key)){
            e.preventDefault();
        }
    }

    // CALLE (letras y numeros)
    if(id === "edit_calle"){
        if(!/[A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]/.test(e.key)){
            e.preventDefault();
        }
    }

});


// ================= SEGURIDAD EXTRA (PEGAR TEXTO) =================
document.addEventListener("input", function(e){

    const id = e.target.id;

    if(id === "edit_primer_nombre" || 
       id === "edit_primer_apellido" || 
       id === "edit_segundo_apellido"){

        e.target.value = e.target.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");
    }

    if(id === "edit_telefono" || 
       id === "edit_cp" || 
       id === "edit_num_ext" || 
       id === "edit_num_int"){

        e.target.value = e.target.value.replace(/[^0-9]/g, "");
    }

    if(id === "edit_calle"){
        e.target.value = e.target.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s]/g, "");
    }

});
// ================= SOLO LETRAS CIUDAD Y ESTADO =================
document.addEventListener("keypress", function(e){

    const id = e.target.id;

    if(id === "edit_ciudad" || id === "edit_estado"){

        if(!/[A-Za-zÁÉÍÓÚáéíóúÑñ\s]/.test(e.key)){
            e.preventDefault();
        }
    }

});

// ================= SEGURIDAD EXTRA (PEGAR TEXTO) =================
document.addEventListener("input", function(e){

    const id = e.target.id;

    if(id === "edit_ciudad" || id === "edit_estado"){
        e.target.value = e.target.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");
    }

});
