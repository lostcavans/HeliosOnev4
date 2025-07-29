<?php
require 'db.php';

// Obtener usuarios con contraseñas sin hash (asumiendo que los hashes son > 50 chars)
$stmt = $pdo->query("SELECT id_user, pass_user FROM user WHERE LENGTH(pass_user) <= 50");
$users = $stmt->fetchAll();

foreach ($users as $user) {
    $hashed = password_hash($user['pass_user'], PASSWORD_DEFAULT);
    $update = $pdo->prepare("UPDATE user SET pass_user = ? WHERE id_user = ?");
    $update->execute([$hashed, $user['id_user']]);
    echo "Actualizado usuario {$user['id_user']}<br>";
}

echo "Migración completada. Todos los usuarios ahora tienen contraseñas hasheadas.";
?>