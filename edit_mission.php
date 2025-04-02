<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();
// Verificar si se ha recibido el id de la misión para editar
if (isset($_GET['id'])) {
    $missionId = $_GET['id'];

    // Obtener los datos de la misión a editar
    $query = "SELECT * FROM mision WHERE id_mis = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$missionId]);
    $mission = $stmt->fetch();

    // Si no se encuentra la misión, redirigir de vuelta
    if (!$mission) {
        header("Location: list_mision.php");
        exit();
    }
} else {
    // Si no se recibe el id, redirigir de vuelta
    header("Location: list_mision.php");
    exit();
}

// Procesar el formulario cuando se envíe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_mis = $_POST['nom_mis'];
    $des_mis = $_POST['des_mis'];
    $id_grupo = $_POST['id_grupo'];
    $fec_mis = $_POST['fec_mis'];
    $stat_mis = $_POST['stat_mis'];

    // Actualizar los datos de la misión
    $updateQuery = "UPDATE mision SET nom_mis = ?, des_mis = ?, id_grupo = ?, stat_mis = ? WHERE id_mis = ?";
    $updateStmt = $pdo->prepare($updateQuery);
    $updateStmt->execute([$nom_mis, $des_mis, $id_grupo, $stat_mis, $missionId]);

    // Redirigir a la lista de misiones después de guardar los cambios
    header("Location: list_mision.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Misión</title>
    <link rel="stylesheet" href="css/main.css">
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>

    <h2>Editar Misión</h2>

    <form method="POST" action="">
        <label for="nom_mis">Nombre de la Misión:</label>
        <input type="text" id="nom_mis" name="nom_mis" value="<?php echo htmlspecialchars($mission['nom_mis']); ?>" required>

        <label for="des_mis">Descripción de la Misión:</label>
        <textarea id="des_mis" name="des_mis" required><?php echo htmlspecialchars($mission['des_mis']); ?></textarea>

        <label for="id_grupo">Grupo:</label>
        <select id="id_grupo" name="id_grupo" required>
            <!-- Aquí deberías cargar los grupos desde la base de datos -->
            <?php
            $groupQuery = "SELECT * FROM grupo";
            $groupStmt = $pdo->query($groupQuery);
            while ($group = $groupStmt->fetch()) {
                $selected = ($group['id_grupo'] == $mission['id_grupo']) ? 'selected' : '';
                echo "<option value='{$group['id_grupo']}' $selected>{$group['nom_grup']}</option>";
            }
            ?>
        </select>


        <label for="stat_mis">Estado:</label>
        <select id="stat_mis" name="stat_mis" required>
            <option value="1" <?php echo ($mission['stat_mis'] == 1) ? 'selected' : ''; ?>>Activa</option>
            <option value="0" <?php echo ($mission['stat_mis'] == 0) ? 'selected' : ''; ?>>Concluida</option>
        </select>

        <button type="submit">Guardar Cambios</button>
    </form>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>
