<?php
session_start(); // Iniciar sesión aquí
?>
<?php
// Incluir la conexión a la base de datos
require 'db.php';

// Obtener los filtros y la búsqueda
$startDate = $_GET['startDate'] ?? '';
$endDate = $_GET['endDate'] ?? '';
$searchName = $_GET['searchName'] ?? '';
$searchEmail = $_GET['searchEmail'] ?? '';
$orderBy = $_GET['orderBy'] ?? 'datetime';
$orderDir = $_GET['orderDir'] ?? 'DESC';

// Construir la consulta SQL
$sql = "SELECT u.id_user, u.nombres, u.email, r.datetime
        FROM reg_user r
        JOIN user u ON r.id_user = u.id_user
        WHERE 1=1";

if ($startDate && $endDate) {
    $sql .= " AND r.datetime BETWEEN :startDate AND :endDate";
}
if ($searchName) {
    $sql .= " AND u.nombres LIKE :searchName";
}
if ($searchEmail) {
    $sql .= " AND u.email LIKE :searchEmail";
}

$sql .= " ORDER BY $orderBy $orderDir";

// Preparar y ejecutar la consulta
$stmt = $pdo->prepare($sql);

if ($startDate && $endDate) {
    $stmt->bindParam(':startDate', $startDate);
    $stmt->bindParam(':endDate', $endDate);
}
if ($searchName) {
    $stmt->bindValue(':searchName', "%$searchName%");
}
if ($searchEmail) {
    $stmt->bindValue(':searchEmail', "%$searchEmail%");
}

$stmt->execute();
$logins = $stmt->fetchAll();
?>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Auditoría</title>
    <style>
 
        
        .container {
            padding: 20px;
            margin-top: 80px;
            flex: 1;
        }
        h1 {
            color: #007bff;
            font-size: 24px;
            margin-bottom: 20px;
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #007bff;
            color: #fff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
  
        /* Estilos para el modal */
        .modal {
            display: none; 
            position: fixed; 
            z-index: 1000; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            overflow: auto; 
            background-color: rgb(0,0,0); 
            background-color: rgba(0,0,0,0.4); 
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; 
            padding: 20px;
            border: 1px solid #888;
            width: 80%; 
            max-width: 600px;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

/* Estilos para el filtro y búsqueda */
.filter-form {
    margin-bottom: 20px;
    background-color: #f9f9f9; /* Cambié a un gris claro */
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Estilos para el botón de exportar PDF */
.filter-form button.export-pdf {
    padding: 10px 15px; /* Espaciado interno */
    background-color: #28a745; /* Color verde */
    color: white; /* Color del texto */
    border: none; /* Sin borde */
    border-radius: 4px; /* Bordes redondeados */
    cursor: pointer; /* Cambia el cursor al pasar el ratón */
    margin-left: 10px; /* Espaciado entre botones */
    font-size: 16px; /* Tamaño de la fuente */
    transition: background-color 0.3s ease; /* Transición suave para el cambio de color */
}

.filter-form button.export-pdf:hover {
    background-color: #218838; /* Color verde más oscuro al pasar el ratón */
}

.filter-form button.export-pdf:active {
    background-color: #1e7e34; /* Color aún más oscuro al hacer clic */
}

.filter-form button.export-pdf:focus {
    outline: none; /* Elimina el contorno de enfoque */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); /* Agrega sombra al enfoque */
}


        /* Color de la letra para los nombres de la tabla */
td a {
    color: black;
}

/* Estilos para el enlace como botón */
.export-pdf {
    display: inline-block; /* Hace que el enlace se comporte como un bloque */
    padding: 10px 15px; /* Espaciado interno */
    background-color: #28a745; /* Color verde */
    color: white; /* Color del texto */
    text-decoration: none; /* Elimina el subrayado del enlace */
    border-radius: 4px; /* Bordes redondeados */
    cursor: pointer; /* Cambia el cursor al pasar el ratón */
    font-size: 16px; /* Tamaño de la fuente */
    transition: background-color 0.3s ease; /* Transición suave para el cambio de color */
}

.export-pdf:hover {
    background-color: #218838; /* Color verde más oscuro al pasar el ratón */
}

.export-pdf:active {
    background-color: #1e7e34; /* Color aún más oscuro al hacer clic */
}

.export-pdf:focus {
    outline: none; /* Elimina el contorno de enfoque */
    box-shadow: 0 0 5px rgba(0, 0, 0, 0.2); /* Agrega sombra al enfoque */
}


    </style>
</head>
<body>
<?php
// index.php
include 'header.php';
include 'sidebar.php';
?>
    <section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>

    <!-- Filtros y búsqueda -->
    <form class="filter-form" method="GET" action="">
        <label for="startDate">Fecha de Inicio:</label>
        <input type="date" id="startDate" name="startDate" value="<?php echo htmlspecialchars($startDate); ?>">
        <label for="endDate">Fecha de Fin:</label>
        <input type="date" id="endDate" name="endDate" value="<?php echo htmlspecialchars($endDate); ?>">
        <label for="searchName">Nombre:</label>
        <input type="text" id="searchName" name="searchName" value="<?php echo htmlspecialchars($searchName); ?>">
        <label for="searchEmail">Email:</label>
        <input type="text" id="searchEmail" name="searchEmail" value="<?php echo htmlspecialchars($searchEmail); ?>">
        <button type="submit">Filtrar</button>
          <!-- Botón para exportar a PDF -->
       <!-- Botón para exportar a PDF -->
        <a href="generate_pdf.php?startDate=<?php echo htmlspecialchars($startDate); ?>&endDate=<?php echo htmlspecialchars($endDate); ?>&searchName=<?php echo htmlspecialchars($searchName); ?>&searchEmail=<?php echo htmlspecialchars($searchEmail); ?>" class="export-pdf">Exportar a PDF</a>


    </form>

    <!-- Tabla de resultados -->
    <table id="dataTable">
    <thead>
        <tr>
            <th><a href="?orderBy=nombres&orderDir=<?php echo $orderDir === 'ASC' ? 'DESC' : 'ASC'; ?>">Nombre</a></th>
            <th><a href="?orderBy=email&orderDir=<?php echo $orderDir === 'ASC' ? 'DESC' : 'ASC'; ?>">Email</a></th>
            <th><a href="?orderBy=datetime&orderDir=<?php echo $orderDir === 'ASC' ? 'DESC' : 'ASC'; ?>">Hora de Ingreso</a></th>
        </tr>
    </thead>
    <tbody>
        <?php if (count($logins) > 0): ?>
            <?php foreach ($logins as $login): ?>
                <tr>
                    <td><a href="#" onclick="showUserInfo(<?php echo htmlspecialchars(json_encode($login['id_user'])); ?>)"><?php echo htmlspecialchars($login['nombres']); ?></a></td>
                    <td><?php echo htmlspecialchars($login['email']); ?></td>
                    <td><?php echo htmlspecialchars($login['datetime']); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="3">No se encontraron datos de ingreso.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    </section>

    <!-- El Modal -->
    <div id="userInfoModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Detalles del Usuario</h2>
            <div id="userInfoContent">
                <!-- Información del usuario cargada aquí -->
            </div>
        </div>
    </div>

    <script>
        function showUserInfo(id_user) {
            var modal = document.getElementById("userInfoModal");
            var content = document.getElementById("userInfoContent");

            var xhr = new XMLHttpRequest();
            xhr.open("GET", "user_info.php?id_user=" + id_user, true);
            xhr.onload = function () {
                if (xhr.status >= 200 && xhr.status < 300) {
                    content.innerHTML = xhr.responseText;
                    modal.style.display = "block";
                }
            };
            xhr.send();
        }

        function closeModal() {
            var modal = document.getElementById("userInfoModal");
            modal.style.display = "none";
        }

        window.onclick = function(event) {
            var modal = document.getElementById("userInfoModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // Función para generar el PDF

    </script>
     <script>
     async function generatePDF() {
    try {
        // Capturar la tabla usando html2canvas
        const tableElement = document.getElementById('dataTable');

        if (!tableElement) {
            throw new Error('No se encontró la tabla para capturar.');
        }

        // Configuración de opciones para html2canvas
        const canvas = await html2canvas(tableElement, {
            scale: 2, // Mejora la calidad de la imagen
            scrollY: -window.scrollY, // Para capturar correctamente si la tabla es larga
        });

        // Convertir la tabla capturada en una imagen PNG
        const imgData = canvas.toDataURL('image/png');

        // Acceder a jsPDF correctamente
        const { jsPDF } = window.jspdf;
        const pdf = new jsPDF('p', 'mm', 'a4'); // 'p' = orientación vertical, 'mm' = unidad de medida, 'a4' = tamaño de página

        // Obtener las dimensiones de la imagen y del PDF
        const imgProps = pdf.getImageProperties(imgData);
        const pdfWidth = pdf.internal.pageSize.getWidth(); // Ancho del PDF
        const pdfHeight = pdf.internal.pageSize.getHeight(); // Alto del PDF
        const imgWidth = pdfWidth - 20; // Margen de 10mm en cada lado
        const imgHeight = (imgProps.height * imgWidth) / imgProps.width; // Mantener la proporción de la imagen

        // Calcular cuántas páginas se necesitarán para la tabla
        let positionY = 10;
        const totalPages = Math.ceil(imgHeight / (pdfHeight - 20)); // Altura total de la imagen dividido por la altura disponible en una página

        // Dividir la imagen en páginas
        for (let i = 0; i < totalPages; i++) {
            if (i > 0) pdf.addPage(); // Añadir una nueva página si no es la primera
            const srcY = i * (pdfHeight - 20); // La posición de la imagen desde la cual recortar
            pdf.addImage(imgData, 'PNG', 10, positionY, imgWidth, pdfHeight - 20, undefined, 'FAST', 0, srcY);
        }

        // Obtener la fecha y hora actuales
        const now = new Date();
        const date = now.toLocaleDateString('es-ES').replace(/\//g, '-'); // Formato: DD-MM-YYYY
        const time = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).replace(/:/g, '-'); // Formato: HH-MM-SS

        // Concatenar la fecha y hora al nombre del archivo PDF
        const fileName = `Informe_Auditoria_${date}_${time}.pdf`;

        // Descargar el archivo PDF con el nombre que incluye la fecha y hora
        pdf.save(fileName);
    } catch (error) {
        console.error('Error al capturar la tabla o generar el PDF:', error);
    }
}

    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
