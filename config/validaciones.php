<?php
/* =========================
   FUNCIONES DE VALIDACIÓN
========================= */

/**
 * Limpiar datos (seguridad básica)
 */
function limpiar($dato) {
    return htmlspecialchars(trim($dato), ENT_QUOTES, 'UTF-8');
}

/**
 * Validar que un texto solo contenga letras y espacios
 */
function validarSoloLetras($texto) {
    return preg_match('/^[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+$/', $texto);
}

/**
 * Generar JS para permitir solo letras en tiempo real
 */
function generarJSValidarSoloLetras($nombreInput) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.querySelector('input[name=\"$nombreInput\"]');
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^A-Za-zÁÉÍÓÚáéíóúÑñ\\s]/g, '');
            });
        }
    });
    </script>";
}

/**
 * Validar correo electrónico
 */
function validarCorreo($correo) {
    return filter_var($correo, FILTER_VALIDATE_EMAIL);
}

/**
 * Validar teléfono (solo números, 7 a 14 dígitos)
 */
function validarTelefono($telefono) {
    return preg_match('/^[0-9]{7,14}$/', $telefono);
}

/**
 * Generar JS para permitir solo números en tiempo real
 */
function generarJSValidarSoloNumeros($nombreInput) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        var input = document.querySelector('input[name=\"$nombreInput\"]');
        if (input) {
            input.addEventListener('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        }
    });
    </script>";
}

/**
 * Validar contraseña (mínimo 8 caracteres, mayúscula, minúscula, número, símbolo)
 */
function validarPassword($pass) {
    return strlen($pass) >= 8 &&
           preg_match('/[A-Z]/', $pass) &&
           preg_match('/[a-z]/', $pass) &&
           preg_match('/[0-9]/', $pass) &&
           preg_match('/[!@#$%^&*(),.?":{}|<>]/', $pass);
}

/**
 * Validar precio (número positivo)
 */
function validarPrecio($precio) {
    return is_numeric($precio) && $precio > 0;
}

/**
 * Validar cantidad (entero >= 0)
 */
function validarCantidad($cantidad) {
    return is_numeric($cantidad) && intval($cantidad) >= 0;
}
?>