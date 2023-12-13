<?php
session_start();
include("../Conexion/conexion.php");

$host = "localhost";
$dbname = "Tienda";
$username = "root";
$password = "";



try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
    die();
}

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $NombreAdmin = $_POST['NombreAdmin'];
    $Clave = $_POST['Clave'];

    try {
        // Consulta SQL para verificar las credenciales.
        $consulta = "SELECT * FROM administradores WHERE NombreAdmin = :NombreAdmin AND Clave = :Clave";
        $stmt = $pdo->prepare($consulta);
        $stmt->bindParam(':NombreAdmin', $NombreAdmin, PDO::PARAM_STR);
        $stmt->bindParam(':Clave', $Clave, PDO::PARAM_STR);
        $stmt->execute();

       
        if ($stmt->rowCount() == 1) {
            $_SESSION['NombreAdmin'] = $NombreAdmin; 

            header("Location: admin.php"); 
        } else {
            $mensajeError = "Nombre de usuario o contraseña incorrectos.";
        }
        } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        die();
        }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Iniciar Sesión - Makeup Glam </title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 400px;
            margin: 100px auto;
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

        label {
            display: block;
            margin: 10px 0 5px;
            color: #495057;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        button {
            background-color: #007bff;
            color: #fff;
            padding: 10px 15px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            width: 100%;
        }

        button:hover {
            background-color: #0056b3;
        }

        p {
            color: #dc3545;
            margin-top: 10px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
<img src="../Imagenes/logo.jpg" alt="" width="100px" height="50px" style="margin-top: 10px;">
    <a style="margin-left: 20px" class="navbar-brand" href="#">Makeup Glam </a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav">
              
                <li style="margin-left: 20px" class="nav-item">
                    <a  class="nav-link" href="index.php">Inicio</a>
                </li>
                <li  style="margin-left: 20px"  class="nav-item">
                    <a class="nav-link" href="#">Promociones Navideñas</a>
                </li>
                <li  style="margin-left: 20px"  class="nav-item">
                    <a class="nav-link" href="#">Maquillajes</a>
                </li>
                <li   style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="#">Cuidado Facial</a>
                </li>
                <li   style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="#">Accesorios</a>
                </li>
                <li   style="margin-left: 20px" class="nav-item">
                    <a class="nav-link" href="#">Perfumes</a>
                </li>
            </ul>
           
    
        </div>
</nav>

<div class="container mt-5">
    <h1 class="mb-4">Iniciar Sesión</h1>
    <form method="post" action="">
        <label for="NombreAdmin">Nombre de Usuario:</label>
        <input type="text" name="NombreAdmin" class="form-control" required>
        <br>
        <label for="Clave">Contraseña:</label>
        <input type="password" name="Clave" class="form-control" required>
        <br>
        <button type="submit" class="btn btn-primary btn-block">Iniciar Sesión</button>
    </form>
    <?php if ($mensajeError) echo "<p class='text-danger'>$mensajeError</p>"; ?>
</div>
<br>
<footer style="background-color: #EBDEF0; color: black; padding: 20px 0;">

        <p class="text-center">© 2023 Makeup Glam - Todos los derechos reservados</p>
  
</footer>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.0.7/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>