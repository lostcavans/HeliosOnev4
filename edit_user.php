<?php
include 'db.php';
session_start();
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
    <title>Modificar Usuario</title>
    <link rel="stylesheet" href="css/main.css"> <!-- Incluye el CSS para el estilo -->
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: #f4f7f6;
            color: #333;
        }
        h2 {
            text-align: center;
            padding: 20px;
            font-size: 28px;
            color: #333;
        }
        form {
            background: white;
            max-width: 600px;
            margin: 20px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            animation: fadeIn 0.8s ease;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 10px;
        }
        input[type="text"],
        input[type="date"],
        input[type="password"],
        input[type="email"],
        select {
            width: calc(100% - 22px);
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: border-color 0.3s ease;
        }
        input:focus, select:focus {
            border-color: #007bff;
        }
        button {
            background-color: #007bff;
            color: white;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #0056b3;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Modificar Usuario</h2>
    <form action="update_user.php" method="POST">
        <input type="hidden" name="id_user" value="<?php echo htmlspecialchars($user['id_user']); ?>">

        <label for="nom_user">Nombre:</label>
        <input type="text" name="nom_user" value="<?php echo htmlspecialchars($user['nom_user']); ?>" required>

        <label for="apel_user">Apellido Paterno:</label>
        <input type="text" name="apel_user" value="<?php echo htmlspecialchars($user['apel_user']); ?>">

        <label for="cel_user">Celular:</label>
        <input type="text" name="cel_user" value="<?php echo htmlspecialchars($user['cel_user']); ?>" required>

        <label for="dir_user">Dirección:</label>
        <input type="text" name="dir_user" value="<?php echo htmlspecialchars($user['dir_user']); ?>" required>

        <label for="fec_nac_user">Fecha de Nacimiento:</label>
        <input type="date" name="fec_nac_user" value="<?php echo htmlspecialchars($user['fec_nac_user']); ?>" required>

        <label for="email_user">Correo Electrónico:</label>
        <input type="email" name="email_user" value="<?php echo htmlspecialchars($user['email_user']); ?>" required>

        <label for="CI_user">CI (Documento de identificación):</label>
        <input type="text" name="CI_user" value="<?php echo htmlspecialchars($user['CI_user']); ?>" required>

        <label for="gen_user">Género:</label>
        <select name="gen_user" required>
            <option value="1" <?php echo $user['gen_user'] == 1 ? 'selected' : ''; ?>>Masculino</option>
            <option value="2" <?php echo $user['gen_user'] == 2 ? 'selected' : ''; ?>>Femenino</option>
        </select>

        <label for="status_user">Estado:</label>
        <select name="status_user" required>
            <option value="1" <?php echo $user['status_user'] == 1 ? 'selected' : ''; ?>>Activo</option>
            <option value="0" <?php echo $user['status_user'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
        </select>
        <!-- ID del Dispositivo -->
        <label for="id_dis">ID del Dispositivo:</label>
        <input type="text" name="id_dis" value="<?php echo htmlspecialchars($user['id_dis']); ?>" required>

        <label for="id_cargo">Cargo:</label>
        <select name="id_cargo" required>
            <?php
            $stmt = $pdo->query("SELECT id_cargo, nom_cargo FROM cargo");
            while ($row = $stmt->fetch()) {
                $selected = $row['id_cargo'] == $user['id_cargo'] ? 'selected' : '';
                echo "<option value='{$row['id_cargo']}' $selected>{$row['nom_cargo']}</option>";
            }
            ?>
        </select>

        <label for="pass_user">Contraseña:</label>
        <input type="password" name="pass_user" value="<?php echo htmlspecialchars($user['pass_user']); ?>">

        <button type="submit">Actualizar</button>
    </form>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>
<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.getElementById('editUserForm').addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar el envío normal del formulario

        const formData = new FormData(this);

        fetch('update_user.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Mostrar ventana emergente de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: data.message,
                    confirmButtonText: 'Aceptar',
                    willClose: () => {
                    // Redirigir a list_user.php después de cerrar la ventana emergente
                    window.location.href = data.redirect;
                }
            });
            } else {
                // Mostrar ventana emergente de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message,
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al enviar el formulario.',
                confirmButtonText: 'Aceptar'
            });
        });
    });
</script>
</body>
</html>
