<?php
// Conexión a la base de datos
$host = 'localhost';
$dbname = 'bd_helios';
$username = 'root';
$password = ''; // Cambia si tu MySQL tiene contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Lista de nombres y apellidos realistas
    $nombres = ['Carlos', 'María', 'Jorge', 'Ana', 'Luis', 'Lucía', 'Pedro', 'Gabriela', 'Miguel', 'Camila'];
    $apellidos = ['Gonzales', 'Pérez', 'Rodríguez', 'López', 'Martínez', 'Sánchez', 'Gutiérrez', 'Ramos', 'Flores', 'Cruz'];

    // Obtener los cargos existentes
    $stmt = $pdo->query("SELECT id_cargo, nom_cargo FROM cargo");
    $cargos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($cargos as $i => $cargo) {
        $id_cargo = $cargo['id_cargo'];

        // Generar datos realistas
        $nombre = $nombres[$i % count($nombres)];
        $apellido = $apellidos[$i % count($apellidos)];
        $email = strtolower($nombre) . "." . strtolower($apellido) . "@ejemplo.com";
        $ci = str_pad(10000000 + $i, 8, '0', STR_PAD_LEFT);
        $cel = '7' . str_pad(rand(1000000, 9999999), 7, '0', STR_PAD_LEFT);
        $direccion = "Calle " . $apellidos[$i % count($apellidos)] . " #" . rand(100, 999);
        $fechaNacimiento = date('Y-m-d', strtotime('-' . rand(20, 40) . ' years'));

        // Contraseña segura
        $passwordHash = password_hash('123456', PASSWORD_DEFAULT);

        // id_dis puede ser cualquier valor genérico
        $id_dis = 1;

        // Insertar usuario
        $sql = "INSERT INTO user (
                    id_dis, nom_user, apel_user, cel_user, dir_user,
                    fec_nac_user, email_user, CI_user, gen_user,
                    pass_user, status_user, id_cargo
                ) VALUES (
                    :id_dis, :nom_user, :apel_user, :cel_user, :dir_user,
                    :fec_nac_user, :email_user, :CI_user, :gen_user,
                    :pass_user, :status_user, :id_cargo
                )";

        $stmtInsert = $pdo->prepare($sql);
        $stmtInsert->execute([
            ':id_dis' => $id_dis,
            ':nom_user' => $nombre,
            ':apel_user' => $apellido,
            ':cel_user' => $cel,
            ':dir_user' => $direccion,
            ':fec_nac_user' => $fechaNacimiento,
            ':email_user' => $email,
            ':CI_user' => $ci,
            ':gen_user' => rand(0, 1), // 0 = femenino, 1 = masculino
            ':pass_user' => $passwordHash,
            ':status_user' => 1,
            ':id_cargo' => $id_cargo
        ]);
    }

    echo "Usuarios creados correctamente para cada cargo.";
} catch (PDOException $e) {
    echo "Error en la conexión o inserción: " . $e->getMessage();
}
?>
