<?php

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
error_log("Acceso  - Datos de sesión: " . print_r($_SESSION, true));

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
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Credencial - Helios</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/credencial.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <?php include 'sidebar.php'; ?>
    
    <section class="full-box dashboard-contentPage">
        <?php include 'navbar.php'; ?>
        <div class="credential-container">
            
            
            <form id="credentialForm" name="credentialForm" enctype="multipart/form-data" class="credential-card">
                <!-- Encabezado de la credencial -->
                <div class="credential-header">
                    <div class="credential-title">REGISTRO DE CREDENCIAL</div>
                    <img src="assets/img/bomb-removebg-preview.png" alt="Logo Helios" class="credential-logo">
                </div>
                
                <!-- Cuerpo de la credencial -->
                <div class="credential-body">
                    <!-- Sección de foto -->
                    <div class="credential-photo-section">
                        <div class="credential-photo-preview" id="photoPreview">
                            <div class="photo-placeholder">
                                <i class="fas fa-camera"></i>
                                <span>Subir foto</span>
                            </div>
                        </div>
                        <input type="file" id="user_photo" name="user_photo" accept="image/*" class="photo-input">
                        <div class="error-message" id="photoError"></div>
                    </div>
                    
                    <!-- Campos de datos en la credencial -->
                    <div class="credential-fields">
                        <div class="credential-field">
                            <label for="nom_user" class="credential-label">Nombre:</label>
                            <input type="text" id="nom_user" name="nom_user" required class="credential-input" placeholder="Ingrese nombre">
                        </div>
                        
                        <div class="credential-field">
                            <label for="apel_user" class="credential-label">Apellido:</label>
                            <input type="text" id="apel_user" name="apel_user" required class="credential-input" placeholder="Ingrese apellido">
                        </div>
                        
                        <div class="credential-field">
                            <label for="CI_user" class="credential-label">Cédula:</label>
                            <input type="text" id="CI_user" name="CI_user" required pattern="[0-9]{6,10}" class="credential-input" placeholder="Número de cédula">
                            <div class="error-message" id="ciError"></div>
                        </div>
                        
                        <div class="credential-field">
                            <label for="fec_nac_user" class="credential-label">Nacimiento:</label>
                            <input type="date" id="fec_nac_user" name="fec_nac_user" required class="credential-input">
                            <div class="error-message" id="ageError"></div>
                        </div>
                        
                        <div class="credential-field">
                            <label for="gen_user" class="credential-label">Género:</label>
                            <select id="gen_user" name="gen_user" required class="credential-input">
                                <option value="">Seleccione...</option>
                                <option value="1">Masculino</option>
                                <option value="2">Femenino</option>
                                <option value="3">Otro</option>
                            </select>
                        </div>
                        
                        <div class="credential-field">
                            <label for="dir_user" class="credential-label">Dirección:</label>
                            <input type="text" id="dir_user" name="dir_user" required class="credential-input" placeholder="Dirección completa">
                        </div>
                        
                        <div class="credential-field">
                            <label for="cel_user" class="credential-label">Teléfono:</label>
                            <input type="tel" id="cel_user" name="cel_user" required pattern="[0-9]{8,10}" class="credential-input" placeholder="Número de teléfono">
                            <div class="error-message" id="celError"></div>
                        </div>
                        
                        <div class="credential-field">
                            <label for="email_user" class="credential-label">Email:</label>
                            <input type="email" id="email_user" name="email_user" required class="credential-input" placeholder="correo@ejemplo.com">
                            <div class="error-message" id="emailError"></div>
                        </div>
                        
                        <div class="credential-field">
                            <label for="id_cargo" class="credential-label">Cargo:</label>
                            <select id="id_cargo" name="id_cargo" required class="credential-input">
                                <option value="">Seleccione cargo...</option>
                                <?php
                                require_once 'db.php';
                                $stmt = $pdo->query("SELECT id_cargo, nom_cargo FROM cargo WHERE stat_cargo = 1");
                                while ($row = $stmt->fetch()) {
                                    echo "<option value='{$row['id_cargo']}'>{$row['nom_cargo']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="credential-field">
                            <label for="id_dis" class="credential-label">ID Credencial:</label>
                            <input type="text" id="id_dis" name="id_dis" required class="credential-input" placeholder="Número de credencial">
                        </div>
                        
                        <div class="credential-field password-field">
                            <label for="pass_user" class="credential-label">Contraseña:</label>
                            <div class="password-container">
                                <input type="password" id="pass_user" name="pass_user" required class="credential-input" placeholder="Cree una contraseña">
                                <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                            </div>
                            <div class="password-strength" id="passwordStrength"></div>
                            <div class="strength-text" id="strengthText">Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos</div>
                        </div>
                    </div>
                    
                    <!-- Sección QR -->
                    <div class="credential-qr-section">
                        <div class="qr-placeholder">
                            <i class="fas fa-qrcode"></i>
                            <span>Código generado automáticamente</span>
                        </div>
                    </div>
                </div>
                
                <!-- Pie de la credencial -->
                <div class="credential-footer">
                    <button type="submit" class="submit-btn">
                        <i class="fas fa-id-card"></i> Generar Credencial
                    </button>
                    <div class="credential-validity">
                        Helios Security Systems © <?= date('Y') ?> | Validez: <?= date('m/Y', strtotime('+2 years')) ?>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script src="js/credencial.js"></script>
    <?php include 'notifications.php'; ?>
    <?php include 'footer.php'; ?>
</body>
</html>