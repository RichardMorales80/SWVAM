
document.addEventListener("DOMContentLoaded", function () {

    const form = document.getElementById("formProducto");

    if (!form) return;

    const nombre = document.getElementById("nombre");
    const precio = document.getElementById("precio");
    const cantidad = document.getElementById("cantidad");

    function marcar(input, valido) {
        input.classList.remove("valid", "invalid");
        if (valido) input.classList.add("valid");
        else input.classList.add("invalid");
    }

    // ===== NOMBRE (SOLO LETRAS Y NÚMEROS) =====
    nombre.addEventListener("input", () => {
        nombre.value = nombre.value.replace(/[^A-Za-z0-9\s]/g, "");
        marcar(nombre, nombre.value.trim().length > 0);
    });

    // ===== PRECIO (SOLO NÚMEROS POSITIVOS CON DECIMAL) =====
    precio.addEventListener("input", () => {

        precio.value = precio.value
            .replace(/[^0-9.]/g, "")        // elimina letras y símbolos
            .replace(/(\..*?)\..*/g, "$1"); // evita más de un punto

        marcar(precio, parseFloat(precio.value) > 0);
    });

    // ===== CANTIDAD (SOLO ENTEROS POSITIVOS) =====
    cantidad.addEventListener("input", () => {

        cantidad.value = cantidad.value.replace(/[^0-9]/g, "");

        marcar(cantidad, parseInt(cantidad.value) > 0);
    });

    // ===== BLOQUEAR PEGADO =====
    [precio, cantidad].forEach(input => {
        input.addEventListener("paste", (e) => {
            e.preventDefault();
        });
    });

    // ===== VALIDAR ANTES DE ENVIAR =====
    form.addEventListener("submit", function (e) {

        if (
            nombre.value.trim() === "" ||
            isNaN(precio.value) || parseFloat(precio.value) <= 0 ||
            isNaN(cantidad.value) || parseInt(cantidad.value) <= 0
        ) {
            e.preventDefault();

            Swal.fire({
                icon: 'error',
                title: 'Campos inválidos',
                text: 'Verifica los datos del producto'
            });
        }
    });

});