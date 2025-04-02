<?php
include 'db.php';
session_start();

    
// Obtener los datos del formulario
$id_user = $_POST['id_user'] ?? 0;
$nom_user = $_POST['nom_user'] ?? '';
$apel_user = $_POST['apel_user'] ?? '';
$cel_user = $_POST['cel_user'] ?? '';
$dir_user = $_POST['dir_user'] ?? '';
$fec_nac_user = $_POST['fec_nac_user'] ?? '';
$email_user = $_POST['email_user'] ?? '';
$CI_user = $_POST['CI_user'] ?? '';
$gen_user = $_POST['gen_user'] ?? '';
$status_user = $_POST['status_user'] ?? '';
$id_cargo = $_POST['id_cargo'] ?? '';
$id_dis = $_POST['id_dis'] ?? '';
$pass_user = $_POST['pass_user'] ?? '';

// Validar que el ID del usuario sea válido
if (!$id_user) {
    echo json_encode(['success' => false, 'message' => 'ID de usuario no válido.']);
    exit;
}

try {
    // Preparar la consulta SQL para actualizar el usuario
    $sql = "UPDATE user SET 
            nom_user = :nom_user, 
            apel_user = :apel_user, 
            cel_user = :cel_user, 
            dir_user = :dir_user, 
            fec_nac_user = :fec_nac_user, 
            email_user = :email_user, 
            CI_user = :CI_user, 
            gen_user = :gen_user, 
            status_user = :status_user, 
            id_cargo = :id_cargo, 
            id_dis = :id_dis";

    // Si se proporciona una nueva contraseña, actualizarla
    if (!empty($pass_user)) {
        $sql .= ", pass_user = :pass_user";
    }

    $sql .= " WHERE id_user = :id_user";

    // Preparar la consulta
    $stmt = $pdo->prepare($sql);

    // Bind de los parámetros
    $params = [
        ':nom_user' => $nom_user,
        ':apel_user' => $apel_user,
        ':cel_user' => $cel_user,
        ':dir_user' => $dir_user,
        ':fec_nac_user' => $fec_nac_user,
        ':email_user' => $email_user,
        ':CI_user' => $CI_user,
        ':gen_user' => $gen_user,
        ':status_user' => $status_user,
        ':id_cargo' => $id_cargo,
        ':id_dis' => $id_dis,
        ':id_user' => $id_user,
    ];

    // Si se proporciona una nueva contraseña, agregarla a los parámetros
    if (!empty($pass_user)) {
        $params[':pass_user'] = $pass_user; // Guardar la contraseña sin hash
    }

    // Ejecutar la consulta
    $stmt->execute($params);

    // Respuesta JSON en caso de éxito
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado con éxito.',
        'redirect' => 'list_user.php' // URL de redirección
    ]);
} catch (PDOException $e) {
    // Manejar errores de la base de datos
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario: ' . $e->getMessage()]);
}