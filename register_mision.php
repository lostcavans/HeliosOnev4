<?php
// Iniciar la sesión
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Conectar a la base de datos
include 'db.php';

// Validar si los datos han sido enviados mediante POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los valores del formulario
    $nom_mis = trim($_POST['nom_mis'] ?? '');
    $des_mis = trim($_POST['des_mis'] ?? '');
    $id_grupo = intval($_POST['id_grupo'] ?? 0);
    $stat_mis = intval($_POST['stat_mis'] ?? 0);
    $fec_mis = date('Y-m-d H:i:s'); // Fecha de creación automática
    $fin_mis = NULL; // La misión inicia sin fecha de finalización

    // Validar que los campos obligatorios no estén vacíos
    if (empty($nom_mis) || empty($des_mis) || $id_grupo <= 0) {
        echo json_encode(["success" => false, "message" => "Todos los campos obligatorios deben ser completados."]);
        exit;
    }

    try {
        // Preparar la consulta para insertar la misión
        $stmt = $pdo->prepare("INSERT INTO mision (nom_mis, des_mis, id_grupo, fec_mis, stat_mis, fin_mis) 
                               VALUES (:nom_mis, :des_mis, :id_grupo, :fec_mis, :stat_mis, :fin_mis)");
        
        // Asignar valores a los parámetros
        $stmt->bindParam(':nom_mis', $nom_mis, PDO::PARAM_STR);
        $stmt->bindParam(':des_mis', $des_mis, PDO::PARAM_STR);
        $stmt->bindParam(':id_grupo', $id_grupo, PDO::PARAM_INT);
        $stmt->bindParam(':fec_mis', $fec_mis, PDO::PARAM_STR);
        $stmt->bindParam(':stat_mis', $stat_mis, PDO::PARAM_INT);
        $stmt->bindParam(':fin_mis', $fin_mis, PDO::PARAM_NULL);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            // Guardar mensaje en sesión para mostrar el popup
            $_SESSION['registro_exitoso'] = "Misión registrada exitosamente.";
            header('Location: reg_mis.php'); // Redirigir a reg_mis.php
            exit;
        } else {
            echo json_encode(["success" => false, "message" => "Error al registrar la misión."]);
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error en la base de datos: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Método no permitido."]);
}
?>
