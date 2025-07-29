<?php
// notifications.php
require 'db.php';

// Inicializar variables
$notification_count = 0;
$notifications = [];

try {
    // Verificar si la tabla existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'notification'")->rowCount() > 0;
    
    if ($tableExists && isset($_SESSION['id_cargo'])) {
        $sql = "SELECT COUNT(*) as count FROM notification 
                WHERE target = :target 
                AND status_not = 1 
                AND date_end >= CURDATE()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':target' => $_SESSION['id_cargo']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $notification_count = $result['count'];
        
        // Obtener las notificaciones completas si es necesario
        $sql = "SELECT n.*, c.nom_cargo 
                FROM notification n
                JOIN cargo c ON n.target = c.id_cargo
                WHERE n.target = :target 
                AND n.status_not = 1 
                AND n.date_end >= CURDATE()
                ORDER BY n.date_create DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':target' => $_SESSION['id_cargo']]);
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    error_log("Error en notificaciones: " . $e->getMessage());
}
?>
<!-- Pasa el contador a JavaScript -->
<script>
    window.notificationCount = <?php echo $notification_count; ?>;
</script>
<?php
// notifications.php
require 'db.php';

// Verificación segura para evitar errores si la tabla no existe
$notifications = [];
$tableExists = false;

try {
    // Verificar si la tabla existe
    $tableExists = $pdo->query("SHOW TABLES LIKE 'notification'")->rowCount() > 0;
    
    if ($tableExists) {
        // Obtener notificaciones para el usuario actual o según su cargo
        $target = $_SESSION['id_cargo'] ?? null;
        
        if ($target) {
            $sql = "SELECT n.*, c.nom_cargo 
                    FROM notification n
                    JOIN cargo c ON n.target = c.id_cargo
                    WHERE n.target = :target 
                    AND n.status_not = 1 
                    AND n.date_end >= CURDATE()
                    ORDER BY n.date_create DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([':target' => $target]);
            $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
} catch (PDOException $e) {
    error_log("Error en notificaciones: " . $e->getMessage());
    $notifications = [];
}
?>

<!-- Notifications area - Manteniendo la estructura original -->
<section class="full-box Notifications-area">
    <div class="full-box Notifications-bg btn-Notifications-area"></div>
    <div class="full-box Notifications-body">
        <div class="Notifications-body-title text-titles text-center">
            Notificaciones <i class="zmdi zmdi-close btn-Notifications-area"></i>
            <?php if (!$tableExists): ?>
                <small style="display: block; color: #e74c3c; font-size: 12px;">(Sistema no configurado)</small>
            <?php endif; ?>
        </div>
        <div class="list-group">
            <?php if (empty($notifications)): ?>
                <div class="list-group-item">
                    <div class="list-group-item-text">
                        No hay notificaciones pendientes
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($notifications as $notification): ?>
                    <div class="list-group-item">
                        <div class="list-group-item-header">
                            <span class="list-group-item-date">
                                <?= date('d/m/Y', strtotime($notification['date_create'])) ?>
                            </span>
                            <span class="list-group-item-target">
                                <?= htmlspecialchars($notification['nom_cargo']) ?>
                            </span>
                        </div>
                        <div class="list-group-item-text">
                            <?= htmlspecialchars($notification['msg']) ?>
                        </div>
                        <div class="list-group-item-footer">
                            Válida hasta: <?= date('d/m/Y', strtotime($notification['date_end'])) ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Script para manejar la visualización de notificaciones
document.addEventListener('DOMContentLoaded', function() {
    const btnNotifications = document.querySelector('.btn-Notifications-area');
    const notificationsArea = document.querySelector('.Notifications-area');
    const notificationsBg = document.querySelector('.Notifications-bg');
    
    function toggleNotifications() {
        notificationsArea.classList.toggle('show');
        notificationsBg.classList.toggle('show');
    }
    
    notificationsBg.addEventListener('click', toggleNotifications);
    btnNotifications.addEventListener('click', toggleNotifications);
    
    // Opcional: Actualizar notificaciones periódicamente
    setInterval(() => {
        fetch('get_notifications.php')
            .then(response => response.text())
            .then(data => {
                document.querySelector('.list-group').innerHTML = data;
            });
    }, 300000); // Cada 5 minutos
});

// Actualizar notificaciones cada 60 segundos
function updateNotificationCount() {
    fetch('get_notification_count.php')
        .then(response => response.json())
        .then(data => {
            const badge = document.querySelector('.notification-badge');
            
            if (data.count > 0) {
                if (!badge) {
                    // Crear badge si no existe
                    const newBadge = document.createElement('span');
                    newBadge.className = 'badge notification-badge';
                    document.querySelector('.btn-Notifications-area').appendChild(newBadge);
                }
                document.querySelector('.notification-badge').textContent = data.count;
            } else if (badge) {
                // Eliminar badge si no hay notificaciones
                badge.remove();
            }
        })
        .catch(error => console.error('Error:', error));
}

// Actualizar inmediatamente al cargar y luego cada minuto
document.addEventListener('DOMContentLoaded', function() {
    updateNotificationCount();
    setInterval(updateNotificationCount, 60000);
});
</script>