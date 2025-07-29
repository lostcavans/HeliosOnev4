<?php
// Incluir la conexión a la base de datos
include 'db.php';
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_user'])) {
    echo json_encode(["success" => false, "message" => "Acceso denegado: por favor inicie sesión."]);
    exit;
}

// Parámetros de paginación
$limit = 10; // Número de registros por página
$page = isset($_GET['page']) ? intval($_GET['page']) : 1; // Página actual
$offset = ($page - 1) * $limit; // Cálculo del offset

// Parámetros de búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Consulta base con JOIN para obtener los registros de reg_user y user
$query = "
    SELECT r.*, u.nom_user, u.email_user 
    FROM reg_user r
    JOIN user u ON r.id_user = u.id_user
";

// Aplicar filtro de búsqueda si se proporciona
if (!empty($search)) {
    $query .= " WHERE u.nom_user LIKE :search OR u.email_user LIKE :search OR r.mac LIKE :search OR r.ip LIKE :search";
}

// Ordenar por fecha y hora descendente
$query .= " ORDER BY r.datetime DESC";

// Consulta para contar el total de registros (para la paginación)
$countQuery = str_replace("SELECT r.*, u.nom_user, u.email_user", "SELECT COUNT(*) as total", $query);
$stmtCount = $pdo->prepare($countQuery);
if (!empty($search)) {
    $stmtCount->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmtCount->execute();
$totalRecords = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'];
$totalPages = ceil($totalRecords / $limit); // Calcular el total de páginas

// Consulta para obtener los registros paginados
$query .= " LIMIT :limit OFFSET :offset";
$stmt = $pdo->prepare($query);
if (!empty($search)) {
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
}
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de aceso (Login/Logout)</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 10px;
            text-align: center;
        }

        th {
            background-color: #f4f4f4;
        }

        .status-circle {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            display: inline-block;
        }

        .login {
            background-color: green;
        }

        .logout {
            background-color: red;
        }

        .btn {
            background-color: #007bff;
            color: #fff;
            padding: 6px 12px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .pagination {
            margin-top: 20px;
            text-align: center;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            margin: 0 4px;
            border: 1px solid #ddd;
            text-decoration: none;
            color: #007bff;
        }

        .pagination a:hover {
            background-color: #f4f4f4;
        }

        .pagination .current {
            background-color: #007bff;
            color: #fff;
            border-color: #007bff;
        }

        .search-box {
            margin-bottom: 20px;
            text-align: right;
        }

        .search-box input[type="text"] {
            padding: 8px;
            width: 300px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-box button {
            padding: 8px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-box button:hover {
            background-color: #0056b3;
        }
        .download-box {
    margin-bottom: 20px;
    text-align: right;
}

.download-box button {
    padding: 8px 16px;
    background-color: #28a745;
    color: #fff;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.download-box button:hover {
    background-color: #218838;
}
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Reporte de aceso (Login/Logout)</h2>
    <!-- Botón para descargar el PDF -->
<div class="download-box">
    <form method="GET" action="generate_pdf.php">
        <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
        <button type="submit" class="btn">Descargar PDF</button>
    </form>
</div>

    <!-- Barra de búsqueda -->
    <div class="search-box">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Buscar por nombre, correo, MAC o IP" value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Buscar</button>
        </form>
    </div>

    <!-- Tabla de logs -->
    <table>
        <thead>
            <tr>
                <th>Nombre del Usuario</th>
                <th>Correo del Usuario</th>
                <th>Acción</th>
                <th>MAC</th>
                <th>IP</th>
                <th>Fecha y Hora</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6">No se encontraron registros.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($log['nom_user']); ?></td>
                        <td><?php echo htmlspecialchars($log['email_user']); ?></td>
                        <td>
                            <?php if ($log['log'] == 1): ?>
                                <span class="status-circle login"></span> Login
                            <?php else: ?>
                                <span class="status-circle logout"></span> Logout
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($log['mac']); ?></td>
                        <td><?php echo htmlspecialchars($log['ip']); ?></td>
                        <td><?php echo htmlspecialchars($log['datetime']); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- Paginación -->
    <div class="pagination">
        <?php if ($page > 1): ?>
            <a href="?page=<?php echo ($page - 1); ?>&search=<?php echo urlencode($search); ?>">Anterior</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <span class="current"><?php echo $i; ?></span>
            <?php else: ?>
                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>"><?php echo $i; ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo ($page + 1); ?>&search=<?php echo urlencode($search); ?>">Siguiente</a>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>