<?php
// update_user.php
require_once 'db.php';
require_once 'auth_check.php';

check_auth();

header('Content-Type: application/json');

try {
    // Validar datos
    $required = ['id_user', 'nom_user', 'apel_user', 'CI_user', 'fec_nac_user', 
                'id_cargo', 'id_dis', 'email_user', 'cel_user', 'dir_user', 'gen_user'];
    
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es obligatorio");
        }
    }
    
    $id_user = (int)$_POST['id_user'];
    
    // Verificar si el usuario existe
    $stmt = $pdo->prepare("SELECT * FROM user WHERE id_user = ?");
    $stmt->execute([$id_user]);
    $user = $stmt->fetch();
    
    if (!$user) {
        throw new Exception("Usuario no encontrado");
    }
    
    // Procesar foto si se subió
    $foto_user = $user['foto_user'];
    if (!empty($_FILES['user_photo']['name'])) {
        $uploadDir = 'uploads/users/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        
        $photo = $_FILES['user_photo'];
        $allowedTypes = ['image/jpeg', 'image/png'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($photo['type'], $allowedTypes)) {
            throw new Exception("Solo se permiten imágenes JPG o PNG");
        }
        
        if ($photo['size'] > $maxSize) {
            throw new Exception("La imagen no debe superar 2MB");
        }
        
        // Eliminar foto anterior si existe
        if (!empty($foto_user) && file_exists($foto_user)) {
            unlink($foto_user);
        }
        
        // Generar nuevo nombre
        $fileExt = pathinfo($photo['name'], PATHINFO_EXTENSION);
        $fileName = 'user_' . $id_user . '.' . $fileExt;
        $filePath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
            throw new Exception("Error al guardar la imagen");
        }
        
        $foto_user = 'uploads/users/' . $fileName;
    }
    
    // Preparar datos para actualización
    $data = [
        'nom_user' => trim($_POST['nom_user']),
        'apel_user' => trim($_POST['apel_user']),
        'CI_user' => preg_replace('/[^0-9]/', '', $_POST['CI_user']),
        'fec_nac_user' => $_POST['fec_nac_user'],
        'id_cargo' => (int)$_POST['id_cargo'],
        'id_dis' => trim($_POST['id_dis']),
        'email_user' => filter_var(trim($_POST['email_user']), FILTER_SANITIZE_EMAIL),
        'cel_user' => preg_replace('/[^0-9]/', '', $_POST['cel_user']),
        'dir_user' => trim($_POST['dir_user']),
        'gen_user' => (int)$_POST['gen_user'],
        'status_user' => (int)$_POST['status_user'],
        'foto_user' => $foto_user,
        'id_user' => $id_user
    ];
    
    // Actualizar contraseña si se proporcionó
    $passwordUpdate = '';
    if (!empty($_POST['pass_user'])) {
        if (strlen($_POST['pass_user']) < 8) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres");
        }
        $data['pass_user'] = password_hash($_POST['pass_user'], PASSWORD_BCRYPT);
        $passwordUpdate = ', pass_user = :pass_user';
    }
    
    // Consulta SQL
    $sql = "UPDATE user SET 
            nom_user = :nom_user,
            apel_user = :apel_user,
            CI_user = :CI_user,
            fec_nac_user = :fec_nac_user,
            id_cargo = :id_cargo,
            id_dis = :id_dis,
            email_user = :email_user,
            cel_user = :cel_user,
            dir_user = :dir_user,
            gen_user = :gen_user,
            status_user = :status_user,
            foto_user = :foto_user
            $passwordUpdate
            WHERE id_user = :id_user";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($data);
    
    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado correctamente',
        'redirect' => 'list_users.php' // Cambia esto por tu página de listado
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}