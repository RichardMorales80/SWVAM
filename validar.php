              <?php
            if(isset($_POST['Agregar'])){
                 if(empty($nom)){
				echo "<p class='error'>*Agregta tu nombre</p>";
			   }else{
				if(strlen($nom) > 17){
					echo "<p class='error'>*El nombre es muy largo</p>";
				}
			   }

			   if(empty($precio)){
				echo "<p class='error'>*Agregta un precio al producto</p>";
			   }else{
				if(!is_numeric($precio)){
					echo "<p class='error'>*El precio debe de ser numerico</p>";
				}
			   }
			   if(empty($descripcion)){
				echo "<p class='error'>*Ingresa una descripción</p>";
			   }
			   if(empty($cantidad)){
				echo "<p class='error'>*ingresa cantidad de productos</p>";
			   }else{
				if($cantidad){
					echo "<p class='error'>*La ccantidad debe de ser numerica</p>";
				}

			   }
			   if(empty($imagen)){
				echo "<p class='error'>*Agrega una imagen de tu producto</p>";
			   }
            }

			  

			   ?>