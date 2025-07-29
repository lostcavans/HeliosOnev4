<?php
// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
require_once 'auth_check.php';
try {
    check_auth();
    
    // Verificar permisos (solo cargos 1 y 2 pueden ver la lista completa)
    if (!in_array($_SESSION['id_cargo'], [51, 2])) {
        throw new Exception('No tienes permisos para ver esta página');
    }
} catch (Exception $e) {
    error_log("Error de autenticación: " . $e->getMessage());
    die("Error de autenticación. Por favor inicie sesión nuevamente.");
}

include 'db.php';

// Obtener todos los usuarios con información de cargo
$query = "SELECT u.*, c.nom_cargo 
          FROM user u 
          JOIN cargo c ON u.id_cargo = c.id_cargo
          ORDER BY u.status_user DESC, u.nom_user ASC";
$stmt = $pdo->query($query);
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Helios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/main.css">
    <style>
        .users-container {
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            margin: 20px auto;
            max-width: 1200px;
        }
        .users-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        .users-title {
            font-size: 24px;
            color: #333;
        }
        .add-user-btn {
            background-color: #28a745;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .add-user-btn i {
            margin-right: 8px;
        }
        .users-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .users-table th, 
        .users-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .users-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #495057;
        }
        .users-table tr:hover {
            background-color: #f8f9fa;
        }
        .user-status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 14px;
            margin-right: 5px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }
        .action-btn i {
            margin-right: 5px;
        }
        .edit-btn {
            background-color: #17a2b8;
            color: white;
        }
        .status-btn {
            background-color: #ffc107;
            color: #212529;
        }
        .delete-btn {
            background-color: #dc3545;
            color: white;
        }
        .user-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .no-photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e9ecef;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6c757d;
        }
        .search-container {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
        }
        .search-input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .search-btn {
            padding: 10px 15px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    
    <div class="users-container">
        <div class="users-header">
            <h1 class="users-title"><i class="fas fa-users"></i> Lista de Usuarios</h1>
            <a href="register_user.php" class="add-user-btn">
                <i class="fas fa-user-plus"></i> Nuevo Usuario
            </a>
        </div>

        <div class="search-container">
            <input type="text" id="searchInput" class="search-input" placeholder="Buscar usuarios...">
            <button class="search-btn" id="searchBtn"><i class="fas fa-search"></i> Buscar</button>
        </div>

        <table class="users-table" id="usersTable">
            <thead>
                <tr>
                    <th>Foto</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Cargo</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td>
                            <?php if (!empty($user['foto_user'])): ?>
                                <img src="<?php echo htmlspecialchars($user['foto_user']); ?>" alt="Foto" class="user-photo">
                            <?php else: ?>
                                <div class="no-photo">
                                    <i class="fas fa-user"></i>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($user['nom_user']); ?></td>
                        <td><?php echo htmlspecialchars($user['apel_user']); ?></td>
                        <td><?php echo htmlspecialchars($user['CI_user']); ?></td>
                        <td><?php echo htmlspecialchars($user['cel_user']); ?></td>
                        <td><?php echo htmlspecialchars($user['email_user']); ?></td>
                        <td><?php echo htmlspecialchars($user['nom_cargo']); ?></td>
                        <td>
                            <span class="user-status <?php echo $user['status_user'] == 1 ? 'status-active' : 'status-inactive'; ?>">
                                <?php echo $user['status_user'] == 1 ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id_user']; ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="change_status_user.php?id=<?php echo $user['id_user']; ?>&status=<?php echo $user['status_user'] == 1 ? 0 : 1; ?>" class="action-btn status-btn">
                                <i class="fas fa-power-off"></i> <?php echo $user['status_user'] == 1 ? 'Desactivar' : 'Activar'; ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

<script>
// Función de búsqueda
document.getElementById('searchBtn').addEventListener('click', function() {
    const input = document.getElementById('searchInput');
    const filter = input.value.toUpperCase();
    const table = document.getElementById('usersTable');
    const tr = table.getElementsByTagName('tr');

    for (let i = 1; i < tr.length; i++) {
        let found = false;
        const td = tr[i].getElementsByTagName('td');
        
        for (let j = 1; j < td.length - 1; j++) { // Excluye la columna de acciones
            if (td[j]) {
                const txtValue = td[j].textContent || td[j].innerText;
                if (txtValue.toUpperCase().indexOf(filter) > -1) {
                    found = true;
                    break;
                }
            }
        }
        
        tr[i].style.display = found ? '' : 'none';
    }
});

// Permitir búsqueda al presionar Enter
document.getElementById('searchInput').addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
        document.getElementById('searchBtn').click();
    }
});
</script>

</body>
</html>