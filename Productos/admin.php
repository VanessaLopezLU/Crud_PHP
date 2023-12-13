<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include("../Conexion/conexion.php");

$host = "localhost";
$dbname = "Tienda";
$username = "root";
$password = "";

$accionAgregar = "";
$accionModificar = $accionEliminar = $accionCancelar = "disabled";
$mostrarmodal = false;

$txtnombre = (isset($_POST['txtnombre'])) ? $_POST['txtnombre'] : "";
$txtdescripcion = (isset($_POST['txtdescripcion'])) ? $_POST['txtdescripcion'] : "";
$txtprecio = (isset($_POST['txtprecio'])) ? $_POST['txtprecio'] : "";
$txtimagen = (isset($_FILES['txtimagen']["name"])) ? $_FILES['txtimagen']["name"] : "";
$categorizacion = isset($_POST['Categorizacion']) ? $_POST['Categorizacion'] : "";


$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {
    case "registrar":
        $sentencia = $pdo->prepare("INSERT INTO Productos (NombreProducto, Descripcion, Precio, Imagen, Categorizacion_id) 
           VALUES (:NombreProducto, :Descripcion, :Precio, :Imagen, :Categorizacion_id)");
        $sentencia->bindParam(':NombreProducto', $txtnombre);
        $sentencia->bindParam(':Descripcion', $txtdescripcion);
        $sentencia->bindParam(':Precio', $txtprecio);
        $sentencia->bindParam(':Categorizacion_id', $categorizacion); // Aquí está el valor de la categorización


        $fecha = new DateTime();
        $nombrearchivo = ($txtimagen != "" ? $fecha->getTimestamp() . "_" . $_FILES["txtimagen"]["name"] : "logo.jpg");
        $tmpimagen = $_FILES["txtimagen"]["tmp_name"];

        if ($tmpimagen != "") {
            move_uploaded_file($tmpimagen, "../Imagenes/" . $nombrearchivo);
        }
        $sentencia->bindParam(':Imagen', $nombrearchivo);
        
        if ($sentencia) {
            echo "Producto agregado con éxito.";
        } else {
            echo "Error al agregar el producto.";
        }
        $sentencia->execute();

        break;


        case "editar":
            $sentencia = $pdo->prepare("UPDATE productos SET NombreProducto = :NombreProducto,
            Descripcion = :Descripcion,
            Precio = :Precio,
            Imagen = :Imagen
            WHERE Id = :Id");
        
            $sentencia->bindParam(':NombreProducto', $txtnombre);
            $sentencia->bindParam(':Descripcion', $txtdescripcion);
            $sentencia->bindParam(':Precio', $txtprecio);
            $sentencia->bindParam(':Imagen', $txtimagen);
            $sentencia->bindParam(':Id', $txtid);
        
            $fecha = new DateTime();
            $nombrearchivo = ($txtimagen != "" ? $fecha->getTimestamp() . "_" . $_FILES['txtimagen']["name"] : "producto.jpg");
            $tmpimagen = $_FILES['txtimagen']["tmp_name"];
        
            if ($tmpimagen != "") {
                move_uploaded_file($tmpimagen, "../Imagenes/" . $nombrearchivo);
        
                // Eliminar imagen anterior si existe
                $sentencia = $pdo->prepare("SELECT Imagen FROM productos WHERE Id=:Id");
                $sentencia->bindParam(':Id', $txtid);
                $sentencia->execute();
                $producto = $sentencia->fetch(PDO::FETCH_ASSOC);
        
                if (file_exists("../Imagenes/" . $producto['Imagen'])) {
                    unlink("../Imagenes/" . $producto["Imagen"]);
                }
        
                // Actualizar el nombre del archivo en la base de datos
                $sentencia = $pdo->prepare("UPDATE productos SET Imagen = :Imagen WHERE Id = :Id");
                $sentencia->bindParam(':Imagen', $nombrearchivo);
                $sentencia->bindParam(':Id', $txtid);
                $sentencia->execute();
            }
        
            // Ejecutar la actualización de los datos del producto
            if ($sentencia->execute()) {
                echo "Producto modificado con éxito.";
            } else {
                echo "Error al modificar el producto.";
            }
            break;
        

    case "btneliminar":
        $sentencia = $pdo->prepare("SELECT Imagen  FROM Productos  WHERE Id = :Id");
        $sentencia->bindParam(':Id', $txtid);
        $sentencia->execute();
        $producto = $sentencia->fetch(PDO::FETCH_LAZY);

        if (isset($_POST["txtimagen"])) {
            if (file_exists("../Imagenes/" . $producto["Imagen"])) {
                unlink("../Imagenes/" . $producto["Imagen"]);
            }
        }

        $sentencia = $pdo->prepare("DELETE FROM productos WHERE Id = :Id");
        $sentencia->bindParam(':Id', $txtid);

        if ($sentencia->execute()) {
            echo "El producto ha sido eliminado con éxito.";
            header("location: index.php");
        } else {
            echo "Error al eliminar el producto.";
        }
        break;

    case "Seleccionar":
        $accionAgregar = "disabled";
        $accionModificar = $accionEliminar = $accionCancelar = "";
        $mostrarmodal = true;
        $sentencia = $pdo->prepare("SELECT * FROM productos WHERE Id = :Id");
        $sentencia->bindParam(':Id', $txtid);
        $sentencia->execute();
        $producto = $sentencia->fetch(PDO::FETCH_LAZY);
        $txtnombre = $producto['Nombre'];
        $txtdescripcion = $producto['Descripcion'];
        $txtprecio = $producto['Precio'];
        $txtimagen = $producto['Imagen'];
        break;
}

if (isset($_POST['btnbuscar']) && !empty($_POST['txtbuscar'])) {
    $searchTerm = '%' . $_POST['txtbuscar'] . '%';
    $sentencia = $pdo->prepare('SELECT * FROM productos WHERE Nombre LIKE :searchTerm OR Descripcion LIKE :searchTerm OR id LIKE :searchTerm');
    $sentencia->bindParam(':searchTerm', $searchTerm);
    $sentencia->execute();
    $listaproductos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sentencia = $pdo->prepare("SELECT * FROM productos");
    $sentencia->execute();
    $listaproductos = $sentencia->fetchAll(PDO::FETCH_ASSOC);
}
try {
    $categorizacionQuery = $pdo->query("SELECT Id, Nombre FROM Categorizacion");
    $categorizacion = $categorizacionQuery->fetchAll(PDO::FETCH_ASSOC);

    if (!$categorizacion) {
        echo "No se encontraron categorías.";
        
    }

} catch (PDOException $e) {
    echo "Error al obtener categorías: " . $e->getMessage();
    
}
$sentencia->execute();


// Si se envía el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar si se ha seleccionado una categorización
    if (isset($_POST['Categorizacion'])) {
        // Obtener el ID de la categorización seleccionada
        $categorizacion = $_POST['Categorizacion'];

    } else {
        echo "Por favor, selecciona una categorización.";
    }
    $sentencia->execute();

}
if (isset($_GET['logout']) && $_GET['logout'] == 'true') {
    // Si se hace clic en "Cerrar Sesión", destruir la sesión
    session_destroy();
    // Redirigir a una página o realizar cualquier otra acción después de cerrar sesión
    header("Location: index.php");
    exit();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Panel de Administrador - Makeup Glam</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            background-color: #ffffff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #007bff;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #495057;
        }

        input[type="file"] {
            margin-bottom: 15px;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .image-container {
            text-align: center;
        }

        .image-container img {
            max-width: 100%;
            height: auto;
            margin-top: 10px;
        }

        .btn-cerrar {
            color: #fff;
            background-color: #D2B4DE;
            border-color: #D2B4DE;

        }

        .btn-cerrar:hover {
            background-color: #D2B4DE;
            /* Color de fondo al pasar el cursor */
            border-color: #BB8FCE;
            /* Color del borde al pasar el cursor */
        }

        .btn-block {
            color: #fff;
            background-color: #BB8FCE;
            border-color: #D2B4DE;
            margin-left: 80%,
        }

        .btn-block:hover {
            background-color: #D2B4DE;
            /* Color de fondo al pasar el cursor */
            border-color: #BB8FCE;
            /* Color del borde al pasar el cursor */
        }
        .ti{
            width: 30%;
         
        }
        .img{
            width: 40%;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="img">
        <img src="../Imagenes/logo.jpg" alt="" width="100px" height="50px" style="margin-top: 10px;">
        </div>
        
        <div  style="font-size: 20%" class="ti" >
        <h1 style="font-size: 40px,color: black">Makeup Glam</h1> 
        </div>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php if (isset($_SESSION['NombreAdmin'])) : ?>
                <a style="margin-left: 50%," class="btn  ml-2 btn-cerrar" href="?logout=true">Cerrar Sesión</a>

            <?php endif; ?>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 style="color: #BB8FCE ; " class="text-center mb-4">Panel de Administración</h1>

        <!-- Formulario de registro de producto -->
        <form method="post" action="" enctype="multipart/form-data" style="max-width: 500px; margin: auto;">
            <div class="form-group">
                <label for="txtnombre">Nombre:</label>
                <input type="text" name="txtnombre" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="txtdescripcion">Descripción:</label>
                <textarea name="txtdescripcion" class="form-control" rows="3" required></textarea>
            </div>
            <div class="form-group">
                <label for="txtprecio">Precio:</label>
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                    </div>
                    <input type="number" name="txtprecio" class="form-control" required>
                </div>
            </div>
            <div class="form-group">
                <label for="txtimagen">Imagen:</label>
                <?php if ($txtimagen != "") { ?>
                    <br>
                    <img class="img-thumbnail rounded  mx-auto d-block" width="200px" src="../Imagenes/<?php echo $txtimagen; ?>" />
                    <br><br>
                <?php } ?>
                <input type="file" accept="image/*" class="form-control" name="txtimagen" value="<?php echo "$txtimagen"; ?>" id="txtimagen">
                <br>
            </div>
            <div class="form-group">
                <label for="Categorizacion">Categorización:</label>
             <select name="Categorizacion" class="form-control" required>
                <?php foreach ($categorizacion as $categoria) : ?>
                   <option value="<?= $categoria['Id']; ?>"><?= $categoria['Nombre']; ?></option>
               <?php endforeach; ?>
               
             </select>

            </div>
            <button <?php echo $accionAgregar ?> value="registrar" type="submit" name="accion" class=" btn-block">registrar producto</button>

        </form>
    </div>
    <table class="table">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
    <?php
    $sentenciaProductos = $pdo->prepare("SELECT * FROM Productos");
    $sentenciaProductos->execute();
    $productos = $sentenciaProductos->fetchAll(PDO::FETCH_ASSOC);
    
    // Mostrar la lista de productos en la tabla
    foreach ($productos as $producto) {
        echo "<tr>";
        // Mostrar la imagen del producto
        echo "<td><img src='../Imagenes/" . $producto['Imagen'] . "' alt='" . $producto['NombreProducto'] . "' style='max-width: 100px;'></td>";
        // Mostrar el nombre, descripción, precio del producto en celdas separadas
        echo "<td>{$producto['NombreProducto']}</td>";
        echo "<td>{$producto['Descripcion']}</td>";
        echo "<td>{$producto['Precio']}</td>";
        // Agregar botones para editar y eliminar
        echo "<td>";
        echo "<form action='' method='post' style='display:inline;'>";
        echo "<input type='hidden' name='id' value='{$producto['Id']}'>";
        echo "<button type='submit' class='btn btn-primary'>Editar</button>";
        echo "</form>";
        echo "<form action='eliminar_producto.php' method='post' style='display:inline;' onsubmit='return confirm(\"¿Estás seguro?\");'>";
        echo "<input type='hidden' name='id' value='{$producto['Id']}'>";
        echo "<button type='submit' class='btn btn-danger'>Eliminar</button>";
        echo "</form>";
        echo "</td>";
        echo "</tr>";
    }
    ?>
</tbody>

    </table>

    <footer style="text-align: center; padding: 10px; background-color: #f8f9fa;">
        <p>© 2023 Makeup Glam- Todos los derechos reservados</p>
    </footer>


    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>