document.addEventListener("DOMContentLoaded", function () {
    const formRegistro = document.getElementById("formRegistro");
    const modalRegistro = document.getElementById("modalRegistro");

    if (!formRegistro) return;

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

    const tooltipPass = document.getElementById("tooltip-pass");
    const tooltipConfirm = document.getElementById("tooltip-confirm");

    function marcarValido(input, valido) {
        if (!input) return;

        input.classList.remove("valid", "invalid");

        if (valido === true) {
            input.classList.add("valid");
        } else if (valido === false) {
            input.classList.add("invalid");
        }
    }

    function limpiarDireccionVisual() {
        if (inputs.colonia && inputs.colonia.tagName === "SELECT") {
            inputs.colonia.innerHTML = '<option value="">Seleccione una colonia</option>';
        }

        if (inputs.ciudad) inputs.ciudad.value = "";
        if (inputs.estado) inputs.estado.value = "";

        marcarValido(inputs.colonia, null);
        marcarValido(inputs.ciudad, null);
        marcarValido(inputs.estado, null);
        marcarValido(inputs.codigo_postal, null);
    }

    function resetFormulario() {
        formRegistro.reset();

        formRegistro.querySelectorAll("input, select").forEach(campo => {
            campo.classList.remove("valid", "invalid");
        });

        limpiarDireccionVisual();

        if (tooltipPass) {
            tooltipPass.innerHTML = "";
        }

        if (tooltipConfirm) {
            tooltipConfirm.innerHTML = "";
            tooltipConfirm.style.visibility = "hidden";
            tooltipConfirm.style.opacity = "0";
        }

        const recaptcha = document.querySelector(".g-recaptcha");
        if (recaptcha) {
            recaptcha.classList.remove("error");
        }

        if (typeof grecaptcha !== "undefined") {
            grecaptcha.reset();
        }
    }

    function validarTextoSoloLetras(input, obligatorio = true) {
        if (!input) return;

        input.addEventListener("input", () => {
            input.value = input.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\s]/g, "");
        });

        input.addEventListener("blur", () => {
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
            inputs.calle.value = inputs.calle.value.replace(/[^A-Za-z0-9ÁÉÍÓÚáéíóúÑñ\s\.\#\,\-\/]/g, "");
        });

        inputs.calle.addEventListener("blur", () => {
            marcarValido(inputs.calle, inputs.calle.value.trim().length > 0);
        });
    }

    if (inputs.telefono) {
        inputs.telefono.addEventListener("input", () => {
            inputs.telefono.value = inputs.telefono.value.replace(/[^0-9]/g, "");
        });

        inputs.telefono.addEventListener("blur", () => {
            marcarValido(inputs.telefono, /^[0-9]{10}$/.test(inputs.telefono.value));
        });
    }

    if (inputs.numero_exterior) {
        inputs.numero_exterior.addEventListener("input", () => {
            inputs.numero_exterior.value = inputs.numero_exterior.value.replace(/[^0-9]/g, "");
        });

        inputs.numero_exterior.addEventListener("blur", () => {
            marcarValido(inputs.numero_exterior, inputs.numero_exterior.value.trim().length > 0);
        });
    }

    if (inputs.numero_interior) {
        inputs.numero_interior.addEventListener("input", () => {
            inputs.numero_interior.value = inputs.numero_interior.value.replace(/[^0-9]/g, "");
        });

        inputs.numero_interior.addEventListener("blur", () => {
            if (inputs.numero_interior.value.trim() === "") {
                marcarValido(inputs.numero_interior, null);
            } else {
                marcarValido(inputs.numero_interior, true);
            }
        });
    }

    if (inputs.codigo_postal) {
        inputs.codigo_postal.addEventListener("input", () => {
            inputs.codigo_postal.value = inputs.codigo_postal.value.replace(/[^0-9]/g, "");
        });

        inputs.codigo_postal.addEventListener("blur", () => {
            marcarValido(inputs.codigo_postal, /^[0-9]{5}$/.test(inputs.codigo_postal.value));
        });
    }

    if (inputs.colonia) {
        inputs.colonia.addEventListener("change", () => {
            marcarValido(inputs.colonia, inputs.colonia.value.trim() !== "");
        });
    }

    if (inputs.ciudad) {
        inputs.ciudad.addEventListener("blur", () => {
            marcarValido(inputs.ciudad, inputs.ciudad.value.trim() !== "");
        });
    }

    if (inputs.estado) {
        inputs.estado.addEventListener("blur", () => {
            marcarValido(inputs.estado, inputs.estado.value.trim() !== "");
        });
    }

    const regexCorreo = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    function validarCuenta(campo, valor) {
        const formData = new FormData();
        formData.append("campo", campo);
        formData.append("valor", valor);

        return fetch("public/validar_disponibilidad.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .catch(() => [["error", "Error de conexión"]]);
    }

    if (inputs.correo) {
        inputs.correo.addEventListener("blur", () => {
            const valor = inputs.correo.value.trim();

            if (valor === "") {
                marcarValido(inputs.correo, null);
                return;
            }

            if (!regexCorreo.test(valor)) {
                marcarValido(inputs.correo, false);
                return;
            }

            validarCuenta("correo", valor).then(respuesta => {
                respuesta.forEach(r => {
                    if (r[0] !== "ok") {
                        marcarValido(inputs.correo, false);
                        Swal.fire({
                            icon: "error",
                            title: "Correo no disponible",
                            text: r[1]
                        });
                    } else {
                        marcarValido(inputs.correo, true);
                    }
                });
            });
        });
    }

    const reglas = [
        { text: "Mínimo 8 caracteres", regex: /^.{8,}$/ },
        { text: "Al menos una mayúscula", regex: /[A-Z]/ },
        { text: "Al menos una minúscula", regex: /[a-z]/ },
        { text: "Al menos un número", regex: /[0-9]/ },
        { text: "Al menos un símbolo especial", regex: /[^A-Za-z0-9]/ }
    ];

    function passwordEsValida() {
        return reglas.every(r => r.regex.test(inputs.pas.value));
    }

    function actualizarTooltipPass() {
        if (!tooltipPass || !inputs.pas) return;

        let contenido = "";
        let todasValidas = true;

        reglas.forEach(r => {
            const valido = r.regex.test(inputs.pas.value);
            contenido += (valido ? "✅ " : "❌ ") + r.text + "<br>";

            if (!valido) {
                todasValidas = false;
            }
        });

        tooltipPass.innerHTML = contenido;
        marcarValido(inputs.pas, todasValidas);
        actualizarTooltipConfirm();
    }

    function actualizarTooltipConfirm() {
        if (!tooltipConfirm || !inputs.pasrev || !inputs.pas) return;

        if (inputs.pasrev.value.length === 0) {
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

    if (inputs.pas) {
        inputs.pas.addEventListener("input", actualizarTooltipPass);
    }

    if (inputs.pasrev) {
        inputs.pasrev.addEventListener("input", actualizarTooltipConfirm);
    }

    window.addEventListener("pageshow", function () {
        resetFormulario();
    });
});