<?php
// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
require_once 'auth_check.php';
try {
    check_auth();
    
    // Verificar permisos
    if (!in_array($_SESSION['id_cargo'], [51, 2])) {
        throw new Exception('No tienes permisos para esta acción');
    }
} catch (Exception $e) {
    error_log("Error de autenticación: " . $e->getMessage());
    die("Error de autenticación. Por favor inicie sesión nuevamente.");
}

include 'db.php';

$id = $_GET['id'] ?? 0;

if ($id) {
    $query = "SELECT * FROM user WHERE id_user = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id' => $id]);
    $user = $stmt->fetch();
}

if (!$user) {
    echo "Usuario no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Usuario - Helios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .edit-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .edit-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, 
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .form-actions {
            grid-column: span 2;
            text-align: center;
            margin-top: 20px;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
        .full-width {
            grid-column: span 2;
        }
        .photo-section {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .photo-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            overflow: hidden;
            margin-bottom: 10px;
            background-color: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    
    <div class="edit-container">
        <h2><i class="fas fa-user-edit"></i> Modificar Usuario</h2>
        
        <form id="formEditarUsuario" method="POST" enctype="multipart/form-data" class="edit-form">
            <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($user['id_user']); ?>">

            <div class="form-group">
                <label for="nom_user">Nombre:</label>
                <input type="text" id="nom_user" name="nom_user" value="<?php echo htmlspecialchars($user['nom_user']); ?>" required>
            </div>

            <div class="form-group">
                <label for="apel_user">Apellido:</label>
                <input type="text" id="apel_user" name="apel_user" value="<?php echo htmlspecialchars($user['apel_user']); ?>" required>
            </div>

            <div class="form-group">
                <label for="CI_user">Cédula:</label>
                <input type="text" id="CI_user" name="CI_user" value="<?php echo htmlspecialchars($user['CI_user']); ?>" required maxlength="8">
                <small class="text-muted">Máximo 8 dígitos</small>
            </div>

            <div class="form-group">
                <label for="fec_nac_user">Fecha de Nacimiento:</label>
                <input type="date" id="fec_nac_user" name="fec_nac_user" value="<?php echo htmlspecialchars($user['fec_nac_user']); ?>" required>
            </div>

            <div class="form-group">
                <label for="gen_user">Género:</label>
                <select id="gen_user" name="gen_user" required>
                    <option value="1" <?php echo $user['gen_user'] == 1 ? 'selected' : ''; ?>>Masculino</option>
                    <option value="2" <?php echo $user['gen_user'] == 2 ? 'selected' : ''; ?>>Femenino</option>
                    <option value="3" <?php echo $user['gen_user'] == 3 ? 'selected' : ''; ?>>Otro</option>
                </select>
            </div>

            <div class="form-group">
                <label for="dir_user">Dirección:</label>
                <input type="text" id="dir_user" name="dir_user" value="<?php echo htmlspecialchars($user['dir_user']); ?>" required>
            </div>

            <div class="form-group">
                <label for="cel_user">Teléfono:</label>
                <input type="tel" id="cel_user" name="cel_user" value="<?php echo htmlspecialchars($user['cel_user']); ?>" required maxlength="10">
                <small class="text-muted">Máximo 10 dígitos</small>
            </div>

            <div class="form-group">
                <label for="email_user">Email:</label>
                <input type="email" id="email_user" name="email_user" value="<?php echo htmlspecialchars($user['email_user']); ?>" required>
            </div>

            <div class="form-group">
                <label for="id_cargo">Cargo:</label>
                <select id="id_cargo" name="id_cargo" required>
                    <?php
                    $stmt = $pdo->query("SELECT id_cargo, nom_cargo FROM cargo WHERE stat_cargo = 1");
                    while ($row = $stmt->fetch()) {
                        $selected = $row['id_cargo'] == $user['id_cargo'] ? 'selected' : '';
                        echo "<option value='{$row['id_cargo']}' $selected>{$row['nom_cargo']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="id_dis">ID Credencial:</label>
                <input type="text" id="id_dis" name="id_dis" value="<?php echo htmlspecialchars($user['id_dis']); ?>" required>
            </div>

            <div class="form-group full-width">
                <label for="pass_user">Contraseña:</label>
                <input type="password" id="pass_user" name="pass_user" placeholder="Dejar en blanco para no cambiar">
                <small class="text-muted">Mínimo 8 caracteres con mayúsculas, minúsculas y números</small>
            </div>

            <div class="form-group photo-section full-width">
                <label>Foto de perfil:</label>
                <div class="photo-preview">
                    <?php if (!empty($user['foto_user'])): ?>
                        <img src="<?php echo htmlspecialchars($user['foto_user']); ?>" alt="Foto de perfil">
                    <?php else: ?>
                        <i class="fas fa-user" style="font-size: 50px; color: #666;"></i>
                    <?php endif; ?>
                </div>
                <input type="file" id="user_photo" name="user_photo" accept="image/*">
                <small class="text-muted">Formatos aceptados: JPG, PNG (Máx. 2MB)</small>
            </div>

            <div class="form-group">
                <label for="status_user">Estado:</label>
                <select id="status_user" name="status_user" required>
                    <option value="1" <?php echo $user['status_user'] == 1 ? 'selected' : ''; ?>>Activo</option>
                    <option value="0" <?php echo $user['status_user'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary" id="btnGuardar">
                    <i class="fas fa-save"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

<script>
document.getElementById('formEditarUsuario').addEventListener('submit', function (event) {
    event.preventDefault();
    
    const form = this;
    const btn = document.getElementById('btnGuardar');
    const formData = new FormData(form);
    
    // Validar contraseña si se cambia
    const newPassword = formData.get('pass_user');
    if (newPassword && newPassword.length < 8) {
        alert('La contraseña debe tener al menos 8 caracteres');
        return;
    }
    
    // Validar foto si se sube
    const photo = document.getElementById('user_photo').files[0];
    if (photo && photo.size > 2 * 1024 * 1024) {
        alert('La imagen no debe superar 2MB');
        return;
    }
    
    if (confirm('¿Estás seguro de guardar los cambios?')) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Guardando...';
        
        fetch('update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al guardar los cambios');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save"></i> Guardar Cambios';
        });
    }
});

// Mostrar vista previa de la foto
document.getElementById('user_photo').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            const preview = document.querySelector('.photo-preview');
            preview.innerHTML = `<img src="${event.target.result}" alt="Vista previa">`;
        }
        reader.readAsDataURL(file);
    }
});
</script>