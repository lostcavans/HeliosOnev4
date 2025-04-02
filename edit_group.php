<?php
include 'db.php'; // Incluir la conexión a la base de datos

// Verificar si se ha pasado el ID del grupo
if (isset($_GET['id'])) {
    $id_grupo = $_GET['id'];

    // Obtener los datos actuales del grupo
    $query = "SELECT * FROM grupo WHERE id_grupo = :id_grupo";
    $stmt = $pdo->prepare($query);
    $stmt->execute(['id_grupo' => $id_grupo]);
    $group = $stmt->fetch();

    if (!$group) {
        echo "Grupo no encontrado.";
        exit;
    }

    // Verificar si se ha enviado el formulario
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nom_grup = $_POST['nom_grup'];

        // Actualizar el grupo en la base de datos
        $updateQuery = "UPDATE grupo SET nom_grup = :nom_grup WHERE id_grupo = :id_grupo";
        $updateStmt = $pdo->prepare($updateQuery);
        $updateStmt->execute([
            'nom_grup' => $nom_grup,
            'id_grupo' => $id_grupo
        ]);

        // Redirigir a la lista de grupos después de la actualización
        header("Location: list_group.php");
        exit;
    }
} else {
    echo "ID de grupo no especificado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Grupo</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* Estilos para el formulario */
        .form-container {
            max-width: 500px;
            margin: auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Editar Grupo</h2>

    <!-- Formulario de edición del grupo -->
    <div class="form-container">
        <form action="edit_group.php?id=<?php echo $id_grupo; ?>" method="POST">
            <label for="nom_grup">Nombre del Grupo</label>
            <input type="text" id="nom_grup" name="nom_grup" value="<?php echo htmlspecialchars($group['nom_grup']); ?>" required>

            <button type="submit">Guardar Cambios</button>
        </form>
    </div>

</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
