<?php
session_start(); // Iniciar sesión aquí
?>  
<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Obtener todos los usuarios y sus detalles desde la tabla `user` junto con el nombre del cargo
$sql = "SELECT u.id_user, u.nombres, u.apel_mat, u.apel_pat, u.cel, u.fec_nac, u.CI, u.pais, u.departamento, u.provincia, u.ciud, u.zona, u.comp, u.id_cargo, u.email, u.inst, u.stat, c.nom_cargo                    
FROM user u
LEFT JOIN cargo c ON u.id_cargo = c.id_cargo
ORDER BY u.nombres ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$users = $stmt->fetchAll();
?>




<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.13/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
    body {
        font-family: 'Helvetica Neue', Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f9;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }
    header {
        background-color: #007bff;
        color: #fff;
        padding: 15px 20px;
        text-align: center;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }
    .navbar {
        margin-top: 60px;
        padding: 10px;
        background-color: #f1f1f1;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }
    footer {
        background-color: #007bff;
        color: #fff;
        padding: 15px 20px;
        text-align: center;
        margin-top: auto;
    }
    .container {
        padding: 20px;
        margin-top: 80px;
        flex: 1;
        max-width: 1200px;
        margin: 80px auto;
    }
    h1 {
        color: #007bff;
        font-size: 28px; /* Título más grande */
        margin-bottom: 20px;
        text-align: center;
    }
    .search-container {
        margin-bottom: 20px;
        display: flex;
        justify-content: space-between; /* Distribuir espacio entre el input y el botón */
        align-items: center;
    }
    .search-container input {
        width: 70%; /* Ajustar el ancho */
        padding: 12px;
        border-radius: 5px;
        border: 1px solid #ccc;
        transition: border-color 0.3s;
    }
    .search-container input:focus {
        border-color: #007bff; /* Cambiar el borde al foco */
        outline: none;
    }
    .download-btn {
        padding: 12px 20px;
        background-color: #007bff;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s, transform 0.3s;
    }
    .download-btn:hover {
        background-color: #0056b3;
        transform: translateY(-2px); /* Efecto de elevación */
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    th, td {
    padding: 20px; /* Aumentar el padding para más espacio */
    text-align: left;
    border-bottom: 1px solid #ddd;
    white-space: nowrap; /* Evita el ajuste de línea en las celdas */
    }
    th {
        background-color: #007bff;
        color: #fff;
        cursor: pointer;
        position: relative; /* Necesario para el indicador de ordenamiento */
    }
    th.sort-asc::after {
        content: " ▲";
    }
    th.sort-desc::after {
        content: " ▼";
    }
    tr {
    transition: background-color 0.3s; /* Añadir transición para el cambio de fondo al pasar el mouse */
    }

    tr:hover {
        background-color: #f1f1f1;
    }
    /* Agregar separación entre las filas */
    tbody tr {
        margin-bottom: 10px; /* Separar un poco las filas */
    }
    a {
        color: #007bff;
        text-decoration: none;
    }
    a:hover {
        text-decoration: underline;
    }
    .table-container {
        overflow-x: auto; /* Permite el desplazamiento horizontal si es necesario */
    }
</style>

</head>
<body>
<?php
// list_users.php
include 'header.php';
include 'sidebar.php';
?>
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>

        

        <!-- Área de búsqueda -->
        <div class="search-container">
            <input type="text" id="searchInput" placeholder="Buscar...">
            <!-- Botón para descargar PDF -->
            <button class="download-btn" id="downloadBtn">Descargar PDF</button>
        </div>

        <!-- Tabla de usuarios -->
        <div class="table-container">
            <table id="userTable">
                <thead>
                    <tr>
                        <th data-sort="nombres">Nombres</th>
                        <th data-sort="apel_pat">Apellido Paterno</th>
                        <th data-sort="apel_mat">Apellido Materno</th>
                        <th data-sort="cel">Celular</th>
                        <th data-sort="fec_nac">Fecha de Nacimiento</th>
                        <th data-sort="CI">CI</th>
                        <th data-sort="pais">País</th>
                        <th data-sort="departamento">Departamento</th>
                        <th data-sort="provincia">Provincia</th>
                        <th data-sort="ciud">Ciudad</th>
                        <th data-sort="zona">Zona</th>
                        <th data-sort="comp">Complemento</th>
                        <th data-sort="nom_cargo">Cargo</th>
                        <th data-sort="email">Email</th>
                        <th data-sort="inst">Institución</th>
                        <th data-sort="stat">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['nombres']); ?></td>
                                <td><?php echo htmlspecialchars($user['apel_pat']); ?></td>
                                <td><?php echo htmlspecialchars($user['apel_mat']); ?></td>
                                <td><?php echo htmlspecialchars($user['cel']); ?></td>
                                <td><?php echo htmlspecialchars($user['fec_nac']); ?></td>
                                <td><?php echo htmlspecialchars($user['CI']); ?></td>
                                <td><?php echo htmlspecialchars($user['pais']); ?></td>
                                <td><?php echo htmlspecialchars($user['departamento']); ?></td>
                                <td><?php echo htmlspecialchars($user['provincia']); ?></td>
                                <td><?php echo htmlspecialchars($user['ciud']); ?></td>
                                <td><?php echo htmlspecialchars($user['zona']); ?></td>
                                <td><?php echo htmlspecialchars($user['comp']); ?></td>
                                <td><?php echo htmlspecialchars($user['nom_cargo']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo htmlspecialchars($user['inst']); ?></td>
                                <td><?php echo $user['stat'] == 1 ? 'Activo' : 'Inactivo'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="15">No se encontraron usuarios.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const table = document.querySelector('#userTable');
            const headers = table.querySelectorAll('th');
            const tbody = table.querySelector('tbody');
            const searchInput = document.getElementById('searchInput');

            // Filtro de búsqueda
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                const rows = tbody.querySelectorAll('tr');

                rows.forEach(row => {
                    const cells = row.querySelectorAll('td');
                    const rowContainsQuery = Array.from(cells).some(cell => 
                        cell.textContent.toLowerCase().includes(query)
                    );
                    row.style.display = rowContainsQuery ? '' : 'none';
                });
            });

            headers.forEach(header => {
                header.addEventListener('click', () => {
                    const isAsc = header.classList.contains('sort-asc');
                    const column = header.dataset.sort;

                    // Remove existing sort classes
                    headers.forEach(h => h.classList.remove('sort-asc', 'sort-desc'));

                    // Toggle sort direction
                    header.classList.add(isAsc ? 'sort-desc' : 'sort-asc');

                    // Sort table rows
                    sortTable(column, isAsc ? 'desc' : 'asc');
                });
            });

            function sortTable(column, order) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const index = Array.from(headers).findIndex(header => header.dataset.sort === column);
                
                rows.sort((a, b) => {
                    const cellA = a.cells[index].textContent.trim();
                    const cellB = b.cells[index].textContent.trim();

                    if (cellA < cellB) return order === 'asc' ? -1 : 1;
                    if (cellA > cellB) return order === 'asc' ? 1 : -1;
                    return 0;
                });

                // Reinsert sorted rows
                rows.forEach(row => tbody.appendChild(row));
            }
// Generar PDF
document.getElementById('downloadBtn').addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'landscape', // Cambiar a horizontal si es necesario
        unit: 'mm',
        format: 'a4',
        putOnlyUsedFonts: true,
        floatPrecision: 16 // Para mayor precisión en números
    });

    doc.setFontSize(6); // Reducir el tamaño de fuente
    doc.text("Lista de Usuarios", 10, 10);
    doc.setFontSize(5); // Tamaño de fuente para el contenido

    // Obtener encabezados de tabla
    const headers = Array.from(table.querySelectorAll('th')).map(th => th.innerText);

    // Obtener solo las filas visibles
    const rows = Array.from(table.querySelectorAll('tbody tr')).filter(row => {
        return row.style.display !== 'none'; // Solo incluir filas que no están ocultas
    }).map(row => 
        Array.from(row.querySelectorAll('td')).map(td => td.innerText)
    );

    // Generar la tabla en el PDF
    doc.autoTable({
        head: [headers],
        body: rows,
        startY: 20,
        margin: { horizontal: 5, vertical: 5 }, // Márgenes horizontal y vertical
        styles: {
            overflow: 'linebreak', // Ajustar el contenido si es muy largo
            cellWidth: 'auto', // Ajustar automáticamente el ancho de las celdas
            fontSize: 5, // Reducir tamaño de fuente para celdas
            halign: 'center', // Alinear horizontalmente al centro
            valign: 'middle', // Alinear verticalmente al medio
            cellPadding: 1, // Espaciado interno de las celdas
        },
        theme: 'striped', // Estilo de la tabla
        didDrawCell: (data) => {
            // Personalizar el dibujo de las celdas si es necesario
        }
    });

    // Guardar el PDF
    doc.save('usuarios.pdf');
});


            });
    </script>
</body>
</html>
<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>