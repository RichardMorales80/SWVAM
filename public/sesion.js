// =========================
// MONITOREO DE SESIONES
// =========================

let ultimoID = null;

function monitorearSesiones(){

    fetch('../data/bitacora_tiempo_real.php')
    .then(res => res.json())
    .then(data => {

        if(!data || data.length === 0) return;

        let nuevo = data[0];

        //  PRIMERA VEZ (NO ALERTAR)
        if(ultimoID === null){
            ultimoID = nuevo.id_bitacora;
            return;
        }

        //  SOLO SI HAY NUEVO REGISTRO
        if(nuevo.id_bitacora > ultimoID){

            ultimoID = nuevo.id_bitacora;

            // LOGIN
            if(nuevo.accion.includes("Inició sesión")){
                mostrarAlerta("success", `${nuevo.nombre_usuario} inició sesión`);
            }

            // LOGOUT
            if(nuevo.accion.includes("Cerró sesión")){
                mostrarAlerta("info", `${nuevo.nombre_usuario} cerró sesión`);
            }

            // INACTIVIDAD
            if(nuevo.accion.includes("inactividad")){
                mostrarAlerta("warning", `${nuevo.nombre_usuario} salió por inactividad`);
            }

        }

    })
    .catch(err => console.error("Error monitoreo:", err));
}


// =========================
// ALERTA SWEETALERT
// =========================
function mostrarAlerta(tipo, mensaje){

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: tipo,
        title: mensaje,
        showConfirmButton: false,
        timer: 3000
    });

}


// =========================
// INICIAR MONITOREO
// =========================
function iniciarMonitoreo(){

    monitorearSesiones(); //  primera carga (solo guarda ID)
    setInterval(monitorearSesiones, 5000);

}