<?php
// Conexión a la base de datos
$servername = "66.94.116.235";
$username = "Janco";
$password = "";
$dbname = "aptec";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Obtener los datos enviados
$table = $_POST['table'];
$id = $_POST['Id'];

switch ($table) {
    case 'Ub':
        $latitud = $_POST['Latitud'];
        $longitud = $_POST['Longitud'];
        $battV = $_POST['BattV'];
        $tempAmb = $_POST['TempAmb'];
        $sql = "INSERT INTO tabla2 (id, BattV, TempAmb) VALUES ('$id', '$battV', '$tempAmb')";
        break;

    case 'Min15':
        $battV = $_POST['BattV'];
        $tempAmb = $_POST['TempAmb'];
        $sql = "INSERT INTO min15 (id, columna_battv, columna_tempamb) VALUES ('$id', '$battV', '$tempAmb')";
        break;

    case 'Dia':
        $battV = $_POST['BattV'];
        $tempAmb = $_POST['TempAmb'];
        $sql = "INSERT INTO dia (id, columna_battv, columna_tempamb) VALUES ('$id', '$battV', '$tempAmb')";
        break;

    default:
        echo "Tabla no especificada o no válida";
        exit();
}

if ($conn->query($sql) === TRUE) {
    echo "Datos insertados correctamente en la tabla $table";
} else {
    echo "Error al insertar los datos: " . $conn->error;
}

$conn->close();
?>
