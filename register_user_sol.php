<?php
session_start();
include 'db.php'; // Incluye la conexión PDO

// Obtener los datos del formulario
$nom_user = $_POST['nom_user'] ?? '';
$apel_user = $_POST['apel_user'] ?? '';
$cel_user = $_POST['cel_user'] ?? '';
$dir_user = $_POST['dir_user'] ?? '';
$fec_nac_user = $_POST['fec_nac_user'] ?? '';
$email_user = $_POST['email_user'] ?? '';
$CI_user = $_POST['CI_user'] ?? '';
$gen_user = $_POST['gen_user'] ?? '';
$pass_user = $_POST['pass_user'] ?? '';
$id_cargo = $_POST['id_cargo'] ?? '';
$id_dis = $_POST['id_dis'] ?? '';
$status_user = 1; // Estado activo por defecto

// Validar que todos los campos obligatorios estén presentes
if (empty($nom_user) || empty($apel_user) || empty($cel_user) || empty($dir_user) || empty($fec_nac_user) || empty($email_user) || empty($CI_user) || empty($gen_user) || empty($pass_user) || empty($id_cargo) || empty($id_dis)) {
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

try {
    // Insertar el nuevo usuario en la base de datos
    $sql = "INSERT INTO user (nom_user, apel_user, cel_user, dir_user, fec_nac_user, email_user, CI_user, gen_user, pass_user, id_cargo, id_dis, status_user) 
            VALUES (:nom_user, :apel_user, :cel_user, :dir_user, :fec_nac_user, :email_user, :CI_user, :gen_user, :pass_user, :id_cargo, :id_dis, :status_user)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':nom_user' => $nom_user,
        ':apel_user' => $apel_user,
        ':cel_user' => $cel_user,
        ':dir_user' => $dir_user,
        ':fec_nac_user' => $fec_nac_user,
        ':email_user' => $email_user,
        ':CI_user' => $CI_user,
        ':gen_user' => $gen_user,
        ':pass_user' => $pass_user, // Contraseña sin hash
        ':id_cargo' => $id_cargo,
        ':id_dis' => $id_dis,
        ':status_user' => $status_user
    ]);

    echo json_encode(['success' => true, 'message' => 'Usuario registrado con éxito.']);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario: ' . $e->getMessage()]);
}
?>