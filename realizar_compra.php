<!DOCTYPE html>
<html lang="en">
  <head>
    <title>Tienda</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Mukta:3000,40,7000"> 
    <link rel="stylesheet" href="fonts/icomoon/style.css">
    <link rel="stylesheet" href="css/bootstrap.min.css">         
    <link rel="stylesheet" href="estilos/principal.css">  
    
  </head>
  <body><br><br><br>

        <p style="text-align:center;  border-width: 3px; background-color: #DDD9D9; padding:8px;font-size: 80px; color:blue">Realizar compra</p> 



  
  
  <div class="site-wrap"><br><br>
      
   

    <div class="site-section">
      <div class="container">

        <div class="row mb-5">
          <div class="col-md-9 order-2">

      
            <div class="row mb-5">
              <?php
                include('Conexion.php');
                $resultado = $conexion->query("select * from productos order by idproducto DESC limit 10")or die ($conexion -> error);
                while($fila = mysqli_fetch_array($resultado)){

             

              ?>

              <div class="col-sm-6 col-lg-4 mb-4" data-aos="fade-up">
                <div class="block-4 text-center border">
                  <figure class="block--image">
                    <a  href="carrito.php?id=<?php echo $fila['idproducto'];?>" >
                    <img src="imagenes/<?php echo $fila['imagen'];?>" alt="<?php echo ['nombre'];?>" ></a>
                  </figure>
                  <div class="block-4-text p-4">
                    <h3><a href="carrito.php?id=<?php echo $fila['id'];?>"><?php echo $fila['nombre'];?></a></h3>
                    <p class="mb-0"><?php echo $fila ['descripcion'];?></p>
                    <p style="font-size: 50px; color:brown">$<?php echo $fila['precio'];?></p>
                  </div>
                </div>
              </div>
            
               <?php } ?>

            </div>
           
          </div>

      </div>
    </div>
    
    
  </div>

  <script src="js/jquery-3.3.1.min.js"></script>
  <script src="js/jquery-ui.js"></script>
  <script src="js/popper.min.js"></script>
  <script src="js/bootstrap.min.js"></script>
  <script src="js/owl.carousel.min.js"></script>
  <script src="js/jquery.magnific-popup.min.js"></script>
  <script src="js/aos.js"></script>

  <script src="js/main.js"></script>
    
  </body>
</html>