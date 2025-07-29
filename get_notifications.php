<?php
// get_notifications.php
require 'db.php';

header('Content-Type: text/html');

try {
    $target = $_SESSION['id_cargo'] ?? null;
    $notifications = [];
    
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

    if (empty($notifications)) {
        echo '<div class="list-group-item"><div class="list-group-item-text">No hay notificaciones pendientes</div></div>';
    } else {
        foreach ($notifications as $notification) {
            echo '<div class="list-group-item">
                    <div class="list-group-item-header">
                        <span class="list-group-item-date">'.date('d/m/Y', strtotime($notification['date_create'])).'</span>
                        <span class="list-group-item-target">'.htmlspecialchars($notification['nom_cargo']).'</span>
                    </div>
                    <div class="list-group-item-text">'.htmlspecialchars($notification['msg']).'</div>
                    <div class="list-group-item-footer">
                        Válida hasta: '.date('d/m/Y', strtotime($notification['date_end'])).'
                    </div>
                </div>';
        }
    }
} catch (PDOException $e) {
    echo '<div class="list-group-item"><div class="list-group-item-text">Error al cargar notificaciones</div></div>';
}
?>