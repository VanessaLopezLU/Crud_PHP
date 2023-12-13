<?php
include("../Conexion/conexion.php");
error_reporting(E_ALL);
ini_set('display_errors', 1);
$accionAgregar = "";
$accionModificar = $accionEliminar = $accionCancelar = "disabled";
$mostrarmodal = false;

$txtid = (isset($_POST['txtid'])) ? $_POST['txtid'] : "";
$txtnombre = (isset($_POST['txtnombre'])) ? $_POST['txtnombre'] : "";
$txtdescripcion = (isset($_POST['txtdescripcion'])) ? $_POST['txtdescripcion'] : "";
$txtprecio = (isset($_POST['txtprecio'])) ? $_POST['txtprecio'] : "";
$txtimagen = (isset($_FILES['txtimagen']["name"])) ? $_FILES['txtimagen']["name"] : "";

$accion = (isset($_POST['accion'])) ? $_POST['accion'] : "";

switch ($accion) {
    case "btnagregar":
        $sentencia = $pdo->prepare("INSERT INTO productos (Nombre, Descripcion, Precio, Imagen) 
        VALUES (:Nombre, :Descripcion, :Precio, :Imagen)");
        $sentencia->bindParam(':Nombre', $txtnombre);
        $sentencia->bindParam(':Descripcion', $txtdescripcion);
        $sentencia->bindParam(':Precio', $txtprecio);

        $fecha = new DateTime();
        $nombrearchivo = ($txtimagen != "" ? $fecha->getTimestamp() . "_" . $_FILES["txtimagen"]["name"] : "producto.jpg");
        $tmpimagen = $_FILES["txtimagen"]["tmp_name"];

        if ($tmpimagen != "") {
            move_uploaded_file($tmpimagen, "../Imagenes/" . $nombrearchivo);
        }
        $sentencia->bindParam(':Imagen', $nombrearchivo);
        $sentencia->execute();

        if ($sentencia) {
            echo "Producto agregado con éxito.";
        } else {
            echo "Error al agregar el producto.";
        }
        break;
        // ... (your previous code)

    case "btnmodificar":
        $sentencia = $pdo->prepare("UPDATE productos SET Nombre = :Nombre,
    Descripcion = :Descripcion,
    Precio = :Precio
    WHERE Id = :Id");
        $sentencia->bindParam(':Nombre', $txtnombre);
        $sentencia->bindParam(':Descripcion', $txtdescripcion);
        $sentencia->bindParam(':Precio', $txtprecio);
        $sentencia->bindParam(':Id', $txtid);

        $fecha = new DateTime();
        $nombrearchivo = ($txtimagen != "" ? $fecha->getTimestamp() . "_" . $_FILES['txtimagen']["name"] : "producto.jpg");
        $tmpimagen = $_FILES['txtimagen']["tmp_name"];

        if ($tmpimagen != "") {
            move_uploaded_file($tmpimagen, "../imagenes/" . $nombrearchivo);

            $sentencia = $pdo->prepare("SELECT Imagen FROM productos WHERE Id=:Id");
            $sentencia->bindParam(':Id', $txtid);
            $sentencia->execute();
            $producto = $sentencia->fetch(PDO::FETCH_LAZY);

            if (isset($_FILES["txtimagen"])) {
                if (file_exists("../imagenes/" . $producto['Imagen'])) {
                    unlink("../imagenes/" . $producto["Imagen"]);
                }
            }

            $sentencia = $pdo->prepare("UPDATE productos SET Imagen = :Imagen WHERE Id = :Id");
            $sentencia->bindParam(':Imagen', $nombrearchivo);
            $sentencia->bindParam(':Id', $txtid);
            $sentencia->execute();
        }

        header('location: index.php');

        if ($sentencia->execute()) {
            echo "Producto modificado con éxito.";
        } else {
            echo "Error al modificar el producto.";
        }
        break;

    case "btneliminar":
        $sentencia = $pdo->prepare("SELECT Imagen  FROM productos  WHERE Id = :Id");
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Makeup Glam - Tienda de Maquillaje</title>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .form-inline .form-control {
            width: 350px;
            /* Ancho del campo de búsqueda */
            margin-left: 0px;
            /* Espacio entre el campo de búsqueda y otros elementos */
            margin-top: 10px;
            /* Espacio desde la parte superior */
            font-size: 16px;
            /* Tamaño de fuente */

        }

        .encabezado {
            background-color: #E8DAEF;

        }
    </style>

</head>

<body>
    <div class="encabezado">
        <center>
            <div style="margin-right: 600px;">
                <img src="../Imagenes/logo.jpg" alt="" width="200px" height="150px" style="margin-top: 5px;">
                <h1 style="display: inline-block; vertical-align: middle;">Bienvenido a Makeup Glam</h1>
            </div>
        </center>
    </div>


    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div style="margin-left: 50%">
            <form class="form-inline ml-auto">
                <input class="form-control mr-sm-6" type="search" placeholder="Buscar" aria-label="Buscar">
            </form>
        </div>
        <div class="collapse navbar-collapse" id="navbarNav">

            <a class="btn btn-outline-info ml-2" href="#">Carrito</a>
        </div>
    </nav>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">

                <li style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="index.php">Inicio</a>
                </li>

                <li style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="maquillaje.php">Maquillajes</a>
                </li>
                <li style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="cuidado.php">Cuidado Facial</a>
                </li>
                <li style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="#">Accesorios</a>
                </li>
                <li style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="#">Perfumes</a>
                </li>

            </ul>


        </div>
    </nav>



    <div class="container mt-3">
        <h1 class="mb-4">Promociones Navideñas Makeup Glam</h1>
        <div class="card-deck">
            <div class="card">
                <img src="../Imagenes/combo.jpg" class="card-img-top" alt="Combo Navideño" style="height:55% ;">
                <div class="card-body">
                    <h5 class="card-title">Combo Navideño </h5>
                    <p class="card-text">Kit completo de maquillaje para un look profesional.</p>
                    <p class="card-text"><strong>Precio: $120.000</strong></p>
                    <a href="#" class="btn btn-primary">Agregar al carrito</a>
                </div>
            </div>


        </div>
    </div>
    <br>
    <footer style="background-color: #EBDEF0; color: black; padding: 20px 0;">

        <div class="row">
            <div style="margin-left: 20px" class="col-md-4">
                <h4>Redes Sociales</h4>
                <ul class="list-unstyled">
                    <li><a href="https://es-la.facebook.com/" style="color: #fff;"><img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/ee/Logo_de_Facebook.png/1200px-Logo_de_Facebook.png" alt="Facebook" style="max-width: 30px; max-height: 30px;"> Facebook</a></li>
                    <br>
                    <li><a href="https://www.instagram.com/" style="color: #fff;"><img src="https://elordenmundial.com/wp-content/uploads/2021/10/instagram-aplicacion-tecnologia-lanzamiento-sociedad-historia.jpg" alt="Instagram" style="max-width: 30px; max-height: 30px;"> Instagram</a></li>
                    <br>
                    <li><a href="https://web.whatsapp.com/" style="color: #fff;"><img src="https://img.freepik.com/vector-premium/concepto-icono-whatsapp_23-2147897840.jpg?size=626&ext=jpg&ga=GA1.1.379066732.1702397873&semt=ais" alt="WhatsApp" style="max-width: 50px; max-height: 30px;"> WhatsApp</a></li>
                </ul>


            </div>
            <div style="margin-right: 40px," class="col-md-3">
                <h4>Makeup Glam</h4>
                <p>"Makeup Glam ofrece una exclusiva selección de productos de belleza y maquillaje para realzar tu estilo y resaltar tu belleza única. Nuestros productos están diseñados para potenciar tu confianza y destacar tu mejor versión, ofreciendo calidad y estilo en cada aplicación."</p>
            </div>
            <div style="margin-right: 20px" class="col-md-4">
                <h4>Contactenos</h4>
                <form>
                    <div class="form-group">
                        <input type="text" class="form-control" placeholder="Nombre" required>
                    </div>
                    <div class="form-group">
                        <input type="email" class="form-control" placeholder="Correo electrónico" required>
                    </div>
                    <div class="form-group">
                        <textarea class="form-control" rows="3" placeholder="Mensaje"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block">Enviar</button>
                </form>
            </div>
        </div>
        <br>
        <p class="text-center">© 2023 Makeup Glam - Todos los derechos reservados</p>

    </footer>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>