<?php

require 'db.php';

try {
    // Datos del administrador
    $adminData = [
        'id_dis' => 2, // ID de dispositivo (ajustar según tu estructura)
        'nom_user' => 'GABRIEL MATHEUS',
        'apel_user' => 'JANCO DE FREITAS',
        'cel_user' => '69916082',
        'dir_user' => 'Oficina Central',
        'fec_nac_user' => '1999-12-31',
        'email_user' => 'japinhanaruto@hotmail.com',
        'CI_user' => '15600200',
        'gen_user' => 1,
        'pass_user' => password_hash('admin', PASSWORD_DEFAULT),
        'status_user' => 1,
        'id_cargo' => 51 // Asegúrate que este ID corresponde al cargo de administrador
    ];

    // Verificar si el admin ya existe
    $stmt = $pdo->prepare("SELECT id_user FROM user WHERE email_user = ?");
    $stmt->execute([$adminData['email_user']]);
    
    if ($stmt->rowCount() > 0) {
        die("El usuario administrador ya existe");
    }

    // Insertar el administrador
    $sql = "INSERT INTO user (
        id_dis, nom_user, apel_user, cel_user, dir_user, 
        fec_nac_user, email_user, CI_user, gen_user, 
        pass_user, status_user, id_cargo
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_values($adminData));

    echo "Usuario administrador creado exitosamente<br>";
    echo "Email: admin@bomberoslapaz.bo<br>";
    echo "Contraseña: Admin123<br>";
    echo "<strong>IMPORTANTE:</strong> Cambia esta contraseña inmediatamente después del primer inicio de sesión";

} catch (PDOException $e) {
    die("Error al crear usuario administrador: " . $e->getMessage());
}