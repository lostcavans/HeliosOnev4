<?php
// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
require_once 'auth_check.php';
try {
    check_auth();
    
    // Verificar permisos
    if (!in_array($_SESSION['id_cargo'], [51, 46])) {
        throw new Exception('No tienes permisos para esta acción');
    }
} catch (Exception $e) {
    error_log("Error de autenticación: " . $e->getMessage());
    http_response_code(403);
    die(json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'redirect' => 'login.php'
    ]));
}

require 'db.php';

// Directorio para subir fotos
$uploadDir = 'uploads/users/';
if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0777, true)) {
        die(json_encode(['success' => false, 'message' => 'No se pudo crear el directorio para las fotos']));
    }
}

// Validar que la solicitud es POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'message' => 'Método no permitido']));
}

// Validar campos obligatorios
$requiredFields = [
    'nom_user', 'apel_user', 'CI_user', 'fec_nac_user', 
    'id_cargo', 'id_dis', 'email_user', 'pass_user',
    'cel_user', 'dir_user', 'gen_user'
];

$missingFields = [];
foreach ($requiredFields as $field) {
    if (empty($_POST[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    die(json_encode([
        'success' => false, 
        'message' => 'Campos obligatorios faltantes: ' . implode(', ', $missingFields)
    ]));
}

// Validar foto
if (!isset($_FILES['user_photo']) || $_FILES['user_photo']['error'] !== UPLOAD_ERR_OK) {
    die(json_encode([
        'success' => false, 
        'message' => 'Error en la foto: ' . getUploadError($_FILES['user_photo']['error'] ?? null)
    ]));
}

$photo = $_FILES['user_photo'];
$allowedTypes = ['image/jpeg' => 'jpg', 'image/png' => 'png'];
$maxSize = 2 * 1024 * 1024; // 2MB

if (!array_key_exists($photo['type'], $allowedTypes)) {
    die(json_encode([
        'success' => false, 
        'message' => 'Formato de imagen no válido. Solo se permiten JPG o PNG'
    ]));
}

if ($photo['size'] > $maxSize) {
    die(json_encode([
        'success' => false, 
        'message' => 'La imagen es demasiado grande (máximo 2MB)'
    ]));
}

// Procesar datos
$nom_user = trim($_POST['nom_user']);
$apel_user = trim($_POST['apel_user']);
$CI_user = preg_replace('/[^0-9]/', '', $_POST['CI_user']);
$fec_nac_user = $_POST['fec_nac_user'];
$id_cargo = (int)$_POST['id_cargo'];
$id_dis = trim($_POST['id_dis']);
$email_user = filter_var(trim($_POST['email_user']), FILTER_SANITIZE_EMAIL);
$pass_user = $_POST['pass_user'];
$cel_user = preg_replace('/[^0-9]/', '', $_POST['cel_user']);
$dir_user = trim($_POST['dir_user']);
$gen_user = (int)$_POST['gen_user'];

// Validaciones adicionales
if (!filter_var($email_user, FILTER_VALIDATE_EMAIL)) {
    die(json_encode(['success' => false, 'message' => 'El email no es válido']));
}

if (strlen($CI_user) < 6 || strlen($CI_user) > 10) {
    die(json_encode(['success' => false, 'message' => 'La cédula debe tener entre 6 y 10 dígitos']));
}

if (strlen($cel_user) < 8 || strlen($cel_user) > 10) {
    die(json_encode(['success' => false, 'message' => 'El teléfono debe tener entre 8 y 10 dígitos']));
}

// Validar edad
$birthDate = new DateTime($fec_nac_user);
$today = new DateTime();
$age = $today->diff($birthDate)->y;

if ($age < 18 || $age > 98) {
    die(json_encode(['success' => false, 'message' => 'La edad debe estar entre 18 y 98 años']));
}

// Validar fortaleza de contraseña
if (strlen($pass_user) < 8 || 
    !preg_match('/[A-Z]/', $pass_user) || 
    !preg_match('/[a-z]/', $pass_user) || 
    !preg_match('/[0-9]/', $pass_user)) {
    die(json_encode(['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres, incluyendo mayúsculas, minúsculas y números']));
}

try {
    $pdo->beginTransaction();

    // Verificar duplicados
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE CI_user = ? OR email_user = ? OR id_dis = ?");
    $stmt->execute([$CI_user, $email_user, $id_dis]);
    
    if ($stmt->rowCount() > 0) {
        throw new Exception('Cédula, email o ID de credencial ya registrados');
    }

    // Insertar usuario
    $hashed_password = password_hash($pass_user, PASSWORD_BCRYPT);
    
    $sql = "INSERT INTO user (
        nom_user, apel_user, CI_user, fec_nac_user, id_cargo, 
        id_dis, email_user, pass_user, status_user, cel_user, 
        dir_user, gen_user
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $nom_user, $apel_user, $CI_user, $fec_nac_user, $id_cargo, 
        $id_dis, $email_user, $hashed_password, $cel_user, 
        $dir_user, $gen_user
    ]);
    
    // Obtener el ID del usuario insertado
    $user_id = $pdo->lastInsertId();
    
    // Generar nombre único para la foto
    $fileExt = $allowedTypes[$photo['type']];
    $fileName = 'user_' . $user_id . '.' . $fileExt;
    $filePath = $uploadDir . $fileName;
    $relativePath = 'uploads/users/' . $fileName;

    // Mover archivo subido
    if (!move_uploaded_file($photo['tmp_name'], $filePath)) {
        throw new Exception("No se pudo guardar la imagen en el servidor");
    }

    // Actualizar el usuario con la ruta de la foto
    $updateSql = "UPDATE user SET foto_user = ? WHERE id_user = ?";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([$relativePath, $user_id]);
    
    $pdo->commit();

    echo json_encode([
        'success' => true, 
        'message' => 'Credencial registrada exitosamente',
        'user_id' => $user_id
    ]);
} catch (PDOException $e) {
    $pdo->rollBack();
    
    // Eliminar foto si se subió pero falló la transacción
    if (isset($filePath) && file_exists($filePath)) {
        @unlink($filePath);
    }
    
    error_log("Error en base de datos: " . $e->getMessage());
    
    if ($e->getCode() == 23000) {
        die(json_encode(['success' => false, 'message' => 'Error: Datos duplicados en la base de datos']));
    } else {
        die(json_encode([
            'success' => false, 
            'message' => 'Error al registrar en la base de datos',
            'error_details' => $e->getMessage() // Solo para desarrollo, quitar en producción
        ]));
    }
} catch (Exception $e) {
    $pdo->rollBack();
    if (isset($filePath) && file_exists($filePath)) {
        @unlink($filePath);
    }
    
    die(json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]));
}

// Función para traducir códigos de error de subida
function getUploadError($errorCode) {
    $errors = [
        UPLOAD_ERR_OK => 'No hay error',
        UPLOAD_ERR_INI_SIZE => 'El archivo excede el tamaño permitido',
        UPLOAD_ERR_FORM_SIZE => 'El archivo excede el tamaño permitido por el formulario',
        UPLOAD_ERR_PARTIAL => 'El archivo solo se subió parcialmente',
        UPLOAD_ERR_NO_FILE => 'No se subió ningún archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'Falta la carpeta temporal',
        UPLOAD_ERR_CANT_WRITE => 'No se pudo escribir el archivo en el disco',
        UPLOAD_ERR_EXTENSION => 'Una extensión de PHP detuvo la subida del archivo',
    ];
    
    return $errors[$errorCode] ?? 'Error desconocido al subir el archivo';
}