<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Contacto</title>

<style>

body{

    font-family:Arial, sans-serif;

    background:#f4f4f4;

    display:flex;

    justify-content:center;

    align-items:center;

    min-height:100vh;
}

.formulario{

    width:400px;

    background:white;

    padding:30px;

    border-radius:15px;

    box-shadow:0 5px 15px rgba(0,0,0,0.2);
}

h2{

    text-align:center;

    margin-bottom:20px;

    color:#b30000;
}

input,
textarea{

    width:100%;

    padding:12px;

    margin-bottom:15px;

    border:1px solid #ccc;

    border-radius:8px;

    font-size:15px;
}

textarea{

    resize:none;

    height:120px;
}

button{

    width:100%;

    padding:12px;

    border:none;

    border-radius:8px;

    background:#b30000;

    color:white;

    font-size:16px;

    cursor:pointer;
}

button:hover{

    background:#8f0000;
}

</style>

</head>

<body>

<div class="formulario">

    <h2>Contáctanos</h2>

    <form action="enviar_contacto.php" method="POST">

        <input
            type="text"
            name="nombre"
            placeholder="Tu nombre"
            required
        >

        <input
            type="email"
            name="correo"
            placeholder="Tu correo"
            required
        >

        <textarea
            name="mensaje"
            placeholder="Escribe tu mensaje"
            required
        ></textarea>

        <button type="submit">
            Enviar mensaje
        </button>

    </form>

</div>

</body>
</html>