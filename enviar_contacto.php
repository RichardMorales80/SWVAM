<?php

$nombre  = trim($_POST['nombre'] ?? '');
$correo  = trim($_POST['correo'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if(
    empty($nombre) ||
    empty($correo) ||
    empty($mensaje)
){
    exit('Todos los campos son obligatorios');
}

/* =========================
   DESTINO
========================= */

$destino = "richardmr77@gmail.com";

/* =========================
   ASUNTO
========================= */

$asunto = "Nuevo mensaje desde Matthew NDT";

/* =========================
   CONTENIDO
========================= */

$contenido = "
Has recibido un nuevo mensaje desde tu página web.

================================

Nombre: $nombre

Correo: $correo

Mensaje:

$mensaje

================================
";

/* =========================
   CABECERAS
========================= */

$headers  = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/plain;charset=UTF-8" . "\r\n";
$headers .= "From: Matthew NDT <no-reply@morqui.org>" . "\r\n";
$headers .= "Reply-To: $correo" . "\r\n";

/* =========================
   ENVIAR CORREO
========================= */

if(mail($destino, $asunto, $contenido, $headers)){

    echo "

    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <script>

    Swal.fire({
        icon: 'success',
        title: 'Mensaje enviado',
        text: 'Tu mensaje fue enviado correctamente',
        confirmButtonColor: '#b30000'
    }).then(() => {

        window.location.href='contacto.php';

    });

    </script>

    ";

}else{

    echo "

    <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>

    <script>

    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo enviar el mensaje',
        confirmButtonColor: '#b30000'
    });

    </script>

    ";
}
?>