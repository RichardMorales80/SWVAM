<?php
session_start();
include 'Conexion.php';
$nombre = $_SESSION['nombre'];
if(!isset($nombre)){
    header("location:login.php");
}


?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="estilos/principal.css">
    <title>Document</title>
</head>
<body>

<nav class="main_nav"> 
                              
         
                               
         
                              <ul class="menu" id="main-menu" class="main-menu">
                      
                                  <div>    
                                  <a href="#" class="enlace"> <img src="imagenes/logo1.png" alt="" class="logo"></a></div>
                                 
                                  
                                  <!-- <li> <a href="#">Inicio</a></li> -->
             
                                  <li class="main_menu_item">
                                      <a href="index.html" class="main_menu_link">Inicio</a>
                                  </li>
                                  
                                  <li class="main_menu_item">
                                      <a href="productos.html" class="main_menu_link">Productos</a>
                                  </li>
                                  <li class="main_menu_item">
                                       <a href="login.php" class="main_menu_link">Inicio sesión</a>
                                      </li>
                                  <li class="main_menu_item">
                                       <a href="#" class="main_menu_link">Cotizaciones</a>
                                  </li>

                                  <li class="main_menu_item">
                                       <a href="#" class="main_menu_link">Factutas</a>
                                  </li>

                                  <li class="main_menu_item">
                                       <a href="cerrar_sesion.php" class="main_menu_link">Salir</a>
                                  </li>

                                  
                                

                              </ul>
                      
                          </nav><br><br><br><br><br><br>


                          <h2 class="title">Hola: <?php echo $nombre;  ?> <h2 class="title">Bienbenido a MATTHEW NDT</h2> </h2>


                          

    

    


</body>
</html>