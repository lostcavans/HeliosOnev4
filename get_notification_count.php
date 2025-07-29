<?php
require 'db.php';

header('Content-Type: application/json');

try {
    $count = 0;
    
    if (isset($_SESSION['id_cargo'])) {
        $sql = "SELECT COUNT(*) as count FROM notification 
                WHERE target = :target 
                AND status_not = 1 
                AND date_end >= CURDATE()";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':target' => $_SESSION['id_cargo']]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $count = $result['count'];
    }
    
    echo json_encode(['count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['count' => 0]);
}
?>