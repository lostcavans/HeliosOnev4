<?php
// map.php - Versión segura con depuración

// Habilitar todos los errores para desarrollo
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión de manera segura
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => $_SERVER['HTTP_HOST'],
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Registrar datos de sesión para depuración
error_log("Acceso a map.php - Datos de sesión: " . print_r($_SESSION, true));

// Verificar autenticación
require_once 'auth_check.php';
try {
    check_auth();
} catch (Exception $e) {
    error_log("Error en autenticación: " . $e->getMessage());
    die("Error de autenticación. Por favor inicie sesión nuevamente.");
}

// Configuración de seguridad
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Cache-Control: no-cache, no-store, must-revalidate");
?>
<?php
include 'db.php'; // Incluye la conexión a la base de datos

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Grupos</title>
    <link rel="stylesheet" href="css/main.css">
    <style>
        /* Estilos específicos para la tabla de grupos */
        .grupos-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .grupos-table, .grupos-table th, .grupos-table td {
            border: 1px solid #ddd;
        }

        .grupos-table th, .grupos-table td {
            padding: 10px;
            text-align: center;
        }

        .grupos-table th {
            background-color: #f4f4f4;
        }

        /* Estilos específicos para los botones */
        .grupos-table button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 6px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            margin-right: 5px;
        }

        .grupos-table button:hover {
            background-color: #0056b3;
        }

        .grupos-table .status-button {
            background-color: #28a745;
            color: #fff;
        }

        .grupos-table .status-button.inactive {
            background-color: #dc3545;
        }

        /* Estilos para los modales (pueden permanecer igual) */
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 80%;
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

        input[type="text"] {
            padding: 8px;
            margin: 5px 0;
            width: 100%;
            box-sizing: border-box;
        }

        .modal-button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            font-size: 16px;
            cursor: pointer;
        }
        /* Estilos para la lista de integrantes y el select */
#integrantesList {
    list-style-type: none;
    padding: 0;
}

#integrantesList li {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 5px;
    border-bottom: 1px solid #ddd;
}

#integrantesList li button {
    background-color: #dc3545;
    color: #fff;
    border: none;
    padding: 3px 8px;
    border-radius: 3px;
    cursor: pointer;
}

#personasSinGrupo {
    width: 100%;
    padding: 8px;
    margin-top: 10px;
}
    </style>
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Lista de Grupos</h2>

    <?php
    // Consulta para obtener los grupos y sus integrantes
    $query = "SELECT g.id_grupo, g.nom_grup, u.nom_user, u.apel_user 
              FROM grupo g
              LEFT JOIN user_grup ug ON g.id_grupo = ug.id_grupo
              LEFT JOIN user u ON ug.id_user = u.id_user
              ORDER BY g.id_grupo";
    $stmt = $pdo->query($query);
    $groups = $stmt->fetchAll();
    ?>

    <div class="grupos-container">
        <table class="grupos-table">
            <thead>
                <tr>
                    <th>Grupo</th>
                    <th>Integrantes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $currentGroup = null;
                foreach ($groups as $group):
                    if ($currentGroup !== $group['id_grupo']) {
                        if ($currentGroup !== null) {
                            // Cerrar la fila anterior
                            echo '</td>';
                            echo '<td>
                                    <button onclick="openModal(' . $currentGroup . ')">Modificar</button>
                                    <button class="status-button" onclick="confirmDelete(' . $currentGroup . ')">Eliminar</button>
                                  </td>';
                            echo '</tr>';
                        }
                        // Abrir una nueva fila
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($group['nom_grup']) . '</td>';
                        echo '<td>';
                        $currentGroup = $group['id_grupo'];
                    }
                    if ($group['nom_user']) {
                        echo htmlspecialchars($group['nom_user'] . ' ' . $group['apel_user']) . '<br>';
                    }
                endforeach;

                // Cerrar la última fila
                if ($currentGroup !== null) {
                    echo '</td>';
                    echo '<td>
                            <button onclick="openModal(' . $currentGroup . ')">Modificar</button>
                            <button class="status-button" onclick="confirmDelete(' . $currentGroup . ')">Eliminar</button>
                          </td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

<!-- Modal Eliminar Grupo -->
<div id="deleteGroupModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeDeleteModal()">&times;</span>
        <h2>¿Estás seguro que quieres eliminar este grupo?</h2>
        <p>Esta acción no se puede deshacer.</p>
        <button id="confirmDeleteBtn" class="modal-button">Sí, eliminar</button>
        <button class="modal-button" onclick="closeDeleteModal()">Cancelar</button>
    </div>
</div>

<!-- Modal Editar Grupo -->
<div id="editGroupModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2>Editar Grupo</h2>
        <form id="editGroupForm" method="POST">
            <label for="nom_grup">Nombre del Grupo:</label>
            <input type="text" id="nom_grup" name="nom_grup" required>

            <!-- Lista de integrantes del grupo -->
            <h3>Integrantes del Grupo</h3>
            <ul id="integrantesList">
                <!-- Los integrantes se cargarán dinámicamente aquí -->
            </ul>

            <!-- Lista de personas sin grupo -->
            <h3>Agregar Personas al Grupo</h3>
            <select id="personasSinGrupo">
                <!-- Las personas sin grupo se cargarán dinámicamente aquí -->
            </select>
            <button type="button" onclick="agregarPersona()">Agregar</button>

            <button type="submit" class="modal-button">Guardar Cambios</button>
        </form>
    </div>
</div>

<script>
    // Mostrar el modal de confirmación de eliminación
    function confirmDelete(groupId) {
        document.getElementById('deleteGroupModal').style.display = "block";

        // Al hacer clic en el botón de confirmación, proceder a eliminar
        document.getElementById('confirmDeleteBtn').onclick = function() {
            window.location.href = "delete_group.php?id=" + groupId;  // Redirigir a la eliminación del grupo
        };
    }

    // Cerrar el modal de eliminación
    function closeDeleteModal() {
        document.getElementById('deleteGroupModal').style.display = "none";
    }
    
    // Mostrar el modal de edición
    function openModal(groupId) {
        document.getElementById('editGroupModal').style.display = "block";
        
        // Cargar los datos del grupo seleccionado en el formulario
        fetch('get_group_data.php?id=' + groupId)
            .then(response => response.json())
            .then(data => {
                document.getElementById('nom_grup').value = data.nom_grup;
                document.getElementById('editGroupForm').action = 'edit_group.php?id=' + groupId;
            })
            .catch(error => console.error('Error:', error));
    }

    // Cerrar el modal de edición
    function closeModal() {
        document.getElementById('editGroupModal').style.display = "none";
    }

    // Función para abrir el modal y cargar los datos del grupo
function openModal(groupId) {
    document.getElementById('editGroupModal').style.display = "block";

    // Cargar los datos del grupo seleccionado en el formulario
    fetch('get_group_data.php?id=' + groupId)
        .then(response => response.json())
        .then(data => {
            document.getElementById('nom_grup').value = data.nom_grup;
            document.getElementById('editGroupForm').action = 'edit_group.php?id=' + groupId;

            // Cargar los integrantes del grupo
            cargarIntegrantes(groupId);

            // Cargar las personas sin grupo
            cargarPersonasSinGrupo();
        })
        .catch(error => console.error('Error:', error));
}

// Función para cargar los integrantes del grupo
function cargarIntegrantes(groupId) {
    fetch('get_integrantes.php?id=' + groupId)
        .then(response => response.json())
        .then(data => {
            const integrantesList = document.getElementById('integrantesList');
            integrantesList.innerHTML = ''; // Limpiar la lista

            data.forEach(integrante => {
                const li = document.createElement('li');
                li.innerHTML = `
                    ${integrante.nom_user} ${integrante.apel_user}
                    <button onclick="eliminarPersona(${groupId}, ${integrante.id_user})">x</button>
                `;
                integrantesList.appendChild(li);
            });
        })
        .catch(error => console.error('Error:', error));
}

// Función para cargar las personas sin grupo
function cargarPersonasSinGrupo() {
    fetch('get_personas_sin_grupo.php')
        .then(response => response.json())
        .then(data => {
            const select = document.getElementById('personasSinGrupo');
            select.innerHTML = '<option value="">Seleccione una persona</option>'; // Limpiar el select

            data.forEach(persona => {
                const option = document.createElement('option');
                option.value = persona.id_user;
                option.textContent = `${persona.nom_user} ${persona.apel_user}`;
                select.appendChild(option);
            });
        })
        .catch(error => console.error('Error:', error));
}

// Función para eliminar una persona del grupo
function eliminarPersona(groupId, userId) {
    if (confirm('¿Estás seguro de eliminar a esta persona del grupo?')) {
        fetch('eliminar_persona_grupo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_grupo: groupId, id_user: userId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarIntegrantes(groupId); // Recargar la lista de integrantes
                cargarPersonasSinGrupo(); // Recargar la lista de personas sin grupo
            } else {
                alert('Error al eliminar la persona del grupo.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Función para agregar una persona al grupo
function agregarPersona() {
    const select = document.getElementById('personasSinGrupo');
    const userId = select.value;
    const groupId = document.getElementById('editGroupForm').action.split('id=')[1];

    if (userId) {
        fetch('agregar_persona_grupo.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id_grupo: groupId, id_user: userId }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                cargarIntegrantes(groupId); // Recargar la lista de integrantes
                cargarPersonasSinGrupo(); // Recargar la lista de personas sin grupo
            } else {
                alert('Error al agregar la persona al grupo.');
            }
        })
        .catch(error => console.error('Error:', error));
    } else {
        alert('Seleccione una persona para agregar.');
    }
}
</script>

</body>
</html>