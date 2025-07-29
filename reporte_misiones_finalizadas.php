<?php
session_start();
require_once 'db.php';

// Verificar sesión
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';
$grupo_id = isset($_GET['grupo_id']) ? (int)$_GET['grupo_id'] : 0;

// Construir consulta con filtros
$query = "SELECT m.*, g.nom_grup 
          FROM mision m
          JOIN grupo g ON m.id_grupo = g.id_grupo
          WHERE m.stat_mis = 1";

$params = [];

if (!empty($fecha_inicio)) {
    $query .= " AND m.fin_mis >= ?";
    $params[] = $fecha_inicio;
}

if (!empty($fecha_fin)) {
    $query .= " AND m.fin_mis <= ?";
    $params[] = $fecha_fin;
}

if ($grupo_id > 0) {
    $query .= " AND m.id_grupo = ?";
    $params[] = $grupo_id;
}

$query .= " ORDER BY m.fin_mis DESC";

// Obtener misiones con PDO
try {
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $misiones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener misiones: " . $e->getMessage());
}

// Obtener grupos para el filtro
$grupos = $pdo->query("SELECT id_grupo, nom_grup FROM grupo WHERE stat_grupo = 1")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Misiones Finalizadas</title>
    <link rel="stylesheet" href="css/main.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .filter-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .table-responsive {
            overflow-x: auto;
        }
        .no-results {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
        .action-buttons {
            white-space: nowrap;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    
    <div class="container-fluid">
        <h2 class="mb-4">Reporte de Misiones Finalizadas</h2>
        
        <!-- Filtros -->
        <div class="filter-container mb-4">
            <form id="filter-form" method="GET">
                <div class="row">
                    <div class="col-md-3">
                        <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                               value="<?= htmlspecialchars($fecha_inicio) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="fecha_fin" class="form-label">Fecha Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                               value="<?= htmlspecialchars($fecha_fin) ?>">
                    </div>
                    <div class="col-md-3">
                        <label for="grupo_id" class="form-label">Grupo</label>
                        <select class="form-select" id="grupo_id" name="grupo_id">
                            <option value="0">Todos los grupos</option>
                            <?php foreach ($grupos as $grupo): ?>
                                <option value="<?= $grupo['id_grupo'] ?>" 
                                    <?= $grupo_id == $grupo['id_grupo'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($grupo['nom_grup']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                        <button type="button" class="btn btn-secondary" onclick="resetFilters()">Limpiar</button>
                    </div>
                </div>
            </form>
        </div>
        
        <!-- Botones de exportación -->
        <div class="mb-3 d-flex justify-content-end">
            <a href="generar_pdf_misiones.php?<?= http_build_query($_GET) ?>" class="btn btn-success me-2">
                <i class="fas fa-file-pdf"></i> Exportar a PDF
            </a>
            <a href="exportar_excel_misiones.php?<?= http_build_query($_GET) ?>" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar a Excel
            </a>
        </div>
        
        <!-- Tabla de resultados -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Descripción</th>
                        <th>Grupo</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Duración</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($misiones)): ?>
                        <tr>
                            <td colspan="7" class="no-results">No se encontraron misiones finalizadas con los filtros aplicados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($misiones as $mision): 
                            $inicio = new DateTime($mision['fec_mis']);
                            $fin = new DateTime($mision['fin_mis']);
                            $duracion = $inicio->diff($fin);
                        ?>
                            <tr>
                                <td><?= htmlspecialchars($mision['nom_mis']) ?></td>
                                <td><?= htmlspecialchars($mision['des_mis']) ?></td>
                                <td><?= htmlspecialchars($mision['nom_grup']) ?></td>
                                <td><?= $inicio->format('d/m/Y H:i') ?></td>
                                <td><?= $fin->format('d/m/Y H:i') ?></td>
                                <td><?= $duracion->format('%d días %H horas %I minutos') ?></td>
                                <td class="action-buttons">
                                    <a href="detalle_mision.php?id=<?= $mision['id_mis'] ?>" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // Inicializar datepickers
    flatpickr("#fecha_inicio", {
        dateFormat: "Y-m-d",
        allowInput: true
    });
    
    flatpickr("#fecha_fin", {
        dateFormat: "Y-m-d",
        allowInput: true
    });

    // Limpiar filtros
    function resetFilters() {
        document.getElementById('fecha_inicio').value = '';
        document.getElementById('fecha_fin').value = '';
        document.getElementById('grupo_id').value = '0';
        document.getElementById('filter-form').submit();
    }
</script>
</body>
</html>