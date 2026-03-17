<?php
$rutaBase = $rutaBase ?? "";
?>

<div id="modalRegistro" class="modal">
    <div class="modal-content registro-modal-large">
        <span class="close" id="cerrarRegistro">&times;</span>

        <div style="text-align:center; margin-bottom:15px;">
            <img src="<?= $rutaBase ?>public/imagenes/logo.png" alt="Logo Matthew NDT" style="max-width:120px;">
        </div>

        <h2>Registro de usuario</h2>

        <form id="formRegistro" class="form-grid-3" method="POST">

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
                <input type="text" id="telefono" name="telefono" class="control" required data-next="codigo_postal">
            </div>

            <div class="col">
                <label>Código Postal</label>
                <input type="text" id="codigo_postal" name="codigo_postal" class="control" maxlength="5" required data-next="colonia">

                <label>Colonia</label>
                <select id="colonia" name="colonia" class="control" required data-next="ciudad">
                    <option value="">Seleccione una colonia</option>
                </select>

                <label>Ciudad</label>
                <input type="text" id="ciudad" name="ciudad" class="control" readonly required data-next="estado">

                <label>Estado</label>
                <input type="text" id="estado" name="estado" class="control" readonly required data-next="calle">

                <label>Calle</label>
                <input type="text" id="calle" name="calle" class="control" required data-next="numero_exterior">
            </div>

            <div class="col">
                <label>Número Exterior</label>
                <input type="text" id="numero_exterior" name="numero_exterior" class="control" required data-next="numero_interior">

                <label>Número Interior</label>
                <input type="text" id="numero_interior" name="numero_interior" class="control" data-next="pas">

                <div class="tooltip">
                    <label>Contraseña</label>
                    <input type="password" name="pas" id="pas" class="control" required data-next="pasrev">
                    <span class="tooltiptext" id="tooltip-pass">
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
            </div>

            <div style="flex-basis:100%; margin-top:10px;">
                <input type="submit" class="boton" value="Registrar">
            </div>

            <div style="flex-basis:100%; margin-top:10px;">
                <div class="g-recaptcha" data-sitekey="6LeXHIMrAAAAAOGSyamoisUJUxeRIv8kwcxuki77"></div>
            </div>

        </form>
    </div>
</div>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const modalReg = document.getElementById("modalRegistro");
    const btnReg = document.getElementById("btnAbrirRegistro") || document.getElementById("btnRegistro");
    const cerrarReg = document.getElementById("cerrarRegistro");
    const formRegistro = document.getElementById("formRegistro");

    const codigoPostal = document.getElementById("codigo_postal");
    const estado = document.getElementById("estado");
    const ciudad = document.getElementById("ciudad");
    const colonia = document.getElementById("colonia");

    const inputPass = document.getElementById("pas");
    const tooltipPass = document.getElementById("tooltip-pass");

    function limpiarDireccionCP() {
        if (estado) estado.value = "";
        if (ciudad) ciudad.value = "";
        if (colonia) {
            colonia.innerHTML = '<option value="">Seleccione una colonia</option>';
        }
    }

    function resetFormularioCompleto() {
        if (formRegistro) {
            formRegistro.reset();
        }

        limpiarDireccionCP();

        document.querySelectorAll("#formRegistro input, #formRegistro select").forEach(campo => {
            campo.classList.remove("valid", "invalid");
        });

        const tooltipConfirm = document.getElementById("tooltip-confirm");
        if (tooltipConfirm) {
            tooltipConfirm.innerHTML = "";
            tooltipConfirm.style.visibility = "hidden";
            tooltipConfirm.style.opacity = "0";
        }

        if (tooltipPass) {
            tooltipPass.style.visibility = "hidden";
            tooltipPass.style.opacity = "0";
        }

        const feedbackPass = document.getElementById("feedback-pass");
        if (feedbackPass) {
            feedbackPass.innerHTML = "";
        }

        if (typeof grecaptcha !== "undefined") {
            grecaptcha.reset();
        }
    }

    function cerrarModalRegistro() {
        resetFormularioCompleto();
        if (modalReg) modalReg.style.display = "none";
        document.body.style.overflow = "auto";
    }

    function abrirModalRegistro() {
        resetFormularioCompleto();
        if (modalReg) modalReg.style.display = "block";
        document.body.style.overflow = "hidden";
    }

    if (modalReg) {
        modalReg.style.display = "none";
    }

    if (btnReg) {
        btnReg.addEventListener("click", function () {
            abrirModalRegistro();
        });
    }

    if (cerrarReg) {
        cerrarReg.addEventListener("click", function () {
            cerrarModalRegistro();
        });
    }

    window.addEventListener("click", function (e) {
        if (e.target === modalReg) {
            cerrarModalRegistro();
        }
    });

    if (inputPass && tooltipPass) {
        inputPass.addEventListener("focus", function () {
            tooltipPass.style.visibility = "visible";
            tooltipPass.style.opacity = "1";
        });

        inputPass.addEventListener("blur", function () {
            tooltipPass.style.visibility = "hidden";
            tooltipPass.style.opacity = "0";
        });
    }

    if (codigoPostal) {
        codigoPostal.addEventListener("input", function () {
            let cp = this.value.replace(/\D/g, "");
            this.value = cp;

            limpiarDireccionCP();

            if (cp.length !== 5) {
                return;
            }

            const datos = new FormData();
            datos.append("codigo_postal", cp);

            fetch("<?= $rutaBase ?>public/buscar_cp.php", {
                method: "POST",
                body: datos
            })
            .then(res => res.json())
            .then(respuesta => {
                if (!respuesta.success) {
                    Swal.fire({
                        icon: "warning",
                        title: "Código postal no encontrado",
                        text: respuesta.message || "No se encontró información para ese código postal."
                    });
                    return;
                }

                if (estado) estado.value = respuesta.estado || "";
                if (ciudad) ciudad.value = respuesta.ciudad || "";

                if (colonia) {
                    colonia.innerHTML = '<option value="">Seleccione una colonia</option>';

                    if (respuesta.colonias && respuesta.colonias.length > 0) {
                        respuesta.colonias.forEach(nombreColonia => {
                            const option = document.createElement("option");
                            option.value = nombreColonia;
                            option.textContent = nombreColonia;
                            colonia.appendChild(option);
                        });
                    }
                }
            })
            .catch(error => {
                console.error("Error al buscar código postal:", error);
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "No se pudo consultar el código postal."
                });
            });
        });
    }

    if (formRegistro) {
        formRegistro.addEventListener("submit", function (e) {
            e.preventDefault();

            let datos = new FormData(this);

            fetch("<?= $rutaBase ?>public/formulario.php", {
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
                            icon: "success",
                            title: "Registro exitoso",
                            text: mensaje
                        }).then(() => {
                            cerrarModalRegistro();
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: mensaje
                        });
                    }
                });
            })
            .catch(err => {
                Swal.fire({
                    icon: "error",
                    title: "Error del servidor",
                    text: "Ocurrió un problema inesperado."
                });
                console.error(err);
            });
        });
    }
});
</script>