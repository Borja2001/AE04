<?php
//Crea la connexió a la base de dades
function connectToDatabase() {
    $servidor = "localhost";
    $usuario = "root";
    $password = "";
    $dbname = "proyectoapi";

    $conexion = mysqli_connect($servidor, $usuario, $password, $dbname);

    if (!$conexion) {
        die("Error en la conexió a MySQL: " . mysqli_connect_error());
    }

    return $conexion;
}

if (isset($_GET["valor"])) {
    $conexion = connectToDatabase();
    $valor = mysqli_real_escape_string($conexion, $_GET['valor']);
    $sql_Recetas = "SELECT * FROM receta  WHERE name LIKE '%$valor%'";
    $resultRecepta = mysqli_query($conexion, $sql_Recetas);
    $json = ["meals" => []];
    
    if ($resultRecepta) {
        //Primer busque totes les receptes amb la paraula enviada
        while ($fila = mysqli_fetch_assoc($resultRecepta)) {
            $id = $fila['id'];
            $sql_ingredients = "SELECT * FROM ingredientes WHERE id_receta LIKE '$id'";
            $resultIngredients = mysqli_query($conexion, $sql_ingredients);
            $ingredients = [];
            $i = 0;
            //Despres busque els ingredients que te 
            while ($filaIngredient = mysqli_fetch_assoc($resultIngredients)) {
                $descripcion[$i] = [
                    $filaIngredient['descripcion']            
                ];
                $cantidad[$i] = [
                    $filaIngredient['cantidad']
                ];
                $i++;
            }

            while ($i < 20) {
                $descripcion[$i] = [
                    ""
                ];
                $cantidad[$i] = [
                    ""
                ];
                $i++;
            }

            /*No he conseguit una altra forma de mantindre el format,  o es craba un array 
            ingredients o es quedaben els ingredients separats amb {}  ells a soles.
            m'he adonat massa tard que el format deste json era massa complicat per a mi de gestionar i ho he tingut que apanyar aixina  :( 
            També he intentat crear el json a partir de un string pero despres donava caracters no valids    */

            $json["meals"][] = [
                "strMeal" => $fila['name'],
                "strInstructions" => $fila['instrucciones'],
                "strMealThumb" => $fila['image'],
                "strIngredient1" => $descripcion[0],
                "strIngredient2" => $descripcion[1],
                "strIngredient3" => $descripcion[2],
                "strIngredient4" => $descripcion[3],
                "strIngredient5" => $descripcion[4],
                "strIngredient6" => $descripcion[5],
                "strIngredient7" => $descripcion[6],
                "strIngredient8" => $descripcion[7],
                "strIngredient9" => $descripcion[8],
                "strIngredient10" => $descripcion[9],
                "strIngredient11" => $descripcion[10],
                "strIngredient12" => $descripcion[11],
                "strIngredient13" => $descripcion[12],
                "strIngredient14" => $descripcion[13],
                "strIngredient15" => $descripcion[14],
                "strIngredient16" => $descripcion[15],
                "strIngredient17" => $descripcion[16],
                "strIngredient18" => $descripcion[17],
                "strIngredient19" => $descripcion[18],
                "strIngredient20" => $descripcion[19],
                "strMeasure1" => $cantidad[0],
                "strMeasure2" => $cantidad[1],
                "strMeasure3" => $cantidad[2],
                "strMeasure4" => $cantidad[3],
                "strMeasure5" => $cantidad[4],
                "strMeasure6" => $cantidad[5],
                "strMeasure7" => $cantidad[6],
                "strMeasure8" => $cantidad[7],
                "strMeasure9" => $cantidad[8],
                "strMeasure10" => $cantidad[9],
                "strMeasure11" => $cantidad[10],
                "strMeasure12" => $cantidad[11],
                "strMeasure13" => $cantidad[12],
                "strMeasure14" => $cantidad[13],
                "strMeasure15" => $cantidad[14],
                "strMeasure16" => $cantidad[15],
                "strMeasure17" => $cantidad[16],
                "strMeasure18" => $cantidad[17],
                "strMeasure19" => $cantidad[18],
                "strMeasure20" => $cantidad[19],
            ];
        }

        echo json_encode($json);
    } else {
        echo json_encode(["error" => "Error executing query: " . mysqli_error($conexion)]);
    }

    mysqli_close($conexion);
}

if (isset($_POST["valor"])) {
    $conexion = connectToDatabase();
    $json_data = json_decode($_POST["valor"], true);
    $name = $json_data['strMeal'];
    $instrucciones = mysqli_real_escape_string($conexion, str_replace(["\n", "\r"], ["\\n", "\\r"], $json_data['strInstructions']));
    $image = $json_data['strMealThumb'];
    $sql_Existe = "SELECT COUNT(*) FROM receta where name like '$name' ";
    $result = mysqli_query($conexion, $sql_Existe);
    $count = mysqli_fetch_array($result)[0];

    if ($count < 1) {
        $sql_receta = "INSERT INTO receta (name,image, instrucciones) VALUES ('$name','$image', '$instrucciones')";

        if (mysqli_query($conexion, $sql_receta)) {
            echo "Registro de receta insertado correctamente.";
        } else {
            echo "Error: " . $sql_receta . "<br>" . mysqli_error($conexion);
        }

        $idReceta = mysqli_insert_id($conexion);

        for ($i = 1; $i <= 20; $i++) {
            $ingredient = $json_data["strIngredient{$i}"];
            $measure = $json_data["strMeasure{$i}"];

            if ($ingredient && $measure) {
                $sql_ingrediente = "INSERT INTO ingredientes (id_receta,descripcion, cantidad) VALUES ('$idReceta','$ingredient', '$measure')";
                mysqli_query($conexion, $sql_ingrediente);
            }
        }
    } else {
        echo "Ja existeix eixe menjar a la base de dades.";
    }

    mysqli_close($conexion);
}
?>
