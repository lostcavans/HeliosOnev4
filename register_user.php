<?php
session_start(); // Iniciar sesión aquí
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="css/register_user.css"> <!-- Enlace al archivo CSS -->
    <style>
        .password-strength {
            height: 5px;
            width: 100%;
            background-color: #e0e0e0;
            margin-top: 5px;
            border-radius: 3px;
        }
        .strength-weak {
            background-color: red;
        }
        .strength-medium {
            background-color: yellow;
        }
        .strength-strong {
            background-color: green;
        }
    </style>
    <script>
        function validatePasswordStrength(password) {
            const minLength = 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumbers = /[0-9]/.test(password);
            const hasSpecialChar = /[!@#$%^&*()]/.test(password);
            
            return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
        }

        function updatePasswordStrength(password) {
            const strengthBar = document.getElementById('passwordStrength');
            const strengthText = document.getElementById('strengthText');
            let strength = 0;

            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*()]/.test(password)) strength++;

            strengthBar.className = 'password-strength';
            if (strength === 0) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Contraseña muy debil';
            } else if (strength === 1) {
                strengthBar.classList.add('strength-weak');
                strengthText.textContent = 'Contraseña Débil';
            } else if (strength === 2) {
                strengthBar.classList.add('strength-medium');
                strengthText.textContent = 'Contraseña Media';
            } else {
                strengthBar.classList.add('strength-strong');
                strengthText.textContent = 'Contraseña Fuerte';
            }
        }

        function validateForm() {
            const form = document.forms['userForm'];
            const fec_nac = new Date(form['fec_nac_user'].value);
            const today = new Date();
            const age = today.getFullYear() - fec_nac.getFullYear();
            const isValidAge = age >= 18 && age <= 98;

            if (!isValidAge) {
                alert('La edad debe estar entre 18 y 98 años.');
                return false;
            }

            const password = form['pass_user'].value;
            const confirmPassword = form['confirm_pass_user'].value;

            if (!validatePasswordStrength(password)) {
                alert('La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un carácter especial.');
                return false;
            }

            if (password !== confirmPassword) {
                alert('Las contraseñas no coinciden.');
                return false;
            }

            // Confirmar con el usuario antes de enviar el formulario
            return confirm('¿Está seguro de que desea registrar este usuario?');
        }

        async function submitForm(event) {
            event.preventDefault(); // Evitar el envío normal del formulario

            const form = document.forms['userForm'];
            const formData = new FormData(form);
            
            try {
                const response = await fetch('register_user_sol.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(result.message); // Mostrar mensaje de éxito
                    form.reset(); // Opcional: reiniciar el formulario
                    updatePasswordStrength(''); // Resetear la barra de fuerza de la contraseña
                } else {
                    alert('Error: ' + result.message); // Mostrar mensaje de error
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar el formulario. CI, Celular o email ya registrados.');
            }
        }

        function togglePasswordVisibility(inputId, toggleId) {
            const input = document.getElementById(inputId);
            const toggle = document.getElementById(toggleId);
            input.type = input.type === 'password' ? 'text' : 'password';
            toggle.classList.toggle('fa-eye');
            toggle.classList.toggle('fa-eye-slash');
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome para el ícono de ojo -->
</head>
<body>

<?php include 'header.php'; ?>
<?php include 'sidebar.php'; ?>

<!-- Content page -->
<section class="full-box dashboard-contentPage">
    <?php include 'navbar.php'; ?>
    <h2>Registrar Usuario</h2>
    <form name="userForm" onsubmit="return validateForm() && submitForm(event)">
        <!-- Nombre -->
        <label for="nom_user">Nombre:</label>
        <input type="text" id="nom_user" name="nom_user" required><br>

        <!-- Apellido -->
        <label for="apel_user">Apellido:</label>
        <input type="text" id="apel_user" name="apel_user" required><br>

        <!-- Celular -->
        <label for="cel_user">Celular:</label>
        <input type="text" id="cel_user" name="cel_user" required pattern="\d{8,8}"><br>

        <!-- Dirección -->
        <label for="dir_user">Dirección:</label>
        <input type="text" id="dir_user" name="dir_user" required><br>

        <!-- Fecha de Nacimiento -->
        <label for="fec_nac_user">Fecha de Nacimiento:</label>
        <input type="date" id="fec_nac_user" name="fec_nac_user" required><br>

        <!-- CI (Documento de identificación) -->
        <label for="CI_user">CI (Documento de identificación):</label>
        <input type="text" id="CI_user" name="CI_user" required><br>

        <!-- Género -->
        <label for="gen_user">Género:</label>
        <select id="gen_user" name="gen_user" required>
            <option value="1">Masculino</option>
            <option value="2">Femenino</option>
            <option value="3">Otro</option>
        </select><br>

        <!-- Contraseña -->
        <label for="pass_user">Contraseña:</label>
        <input type="password" id="pass_user" name="pass_user" required oninput="updatePasswordStrength(this.value)">
        <i class="fas fa-eye" id="togglePassword" onclick="togglePasswordVisibility('pass_user', 'togglePassword')"></i><br>
        <div class="password-strength" id="passwordStrength"></div>
        <div id="strengthText">Requisitos: Al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 número y 1 carácter especial.</div>

        <!-- Confirmar Contraseña -->
        <label for="confirm_pass_user">Confirmar Contraseña:</label>
        <input type="password" id="confirm_pass_user" name="confirm_pass_user" required>
        <i class="fas fa-eye" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm_pass_user', 'toggleConfirmPassword')"></i><br>

        <!-- Correo Electrónico -->
        <label for="email_user">Correo Electrónico:</label>
        <input type="email" id="email_user" name="email_user" required><br>

        <!-- ID del Dispositivo -->
        <label for="id_dis">ID del Dispositivo:</label>
        <input type="text" id="id_dis" name="id_dis" required><br>

        <!-- Cargo -->
        <label for="id_cargo">Cargo:</label>
        <select id="id_cargo" name="id_cargo" required>
            <?php
            include 'db.php'; // Usar PDO para obtener los cargos
            $stmt = $pdo->query("SELECT id_cargo, nom_cargo FROM cargo");
            while ($row = $stmt->fetch()) {
                echo "<option value='{$row['id_cargo']}'>{$row['nom_cargo']}</option>";
            }
            ?>
        </select><br>

        <!-- Estado (oculto) -->
        <label for="status_user" hidden>Estado:</label>
        <select id="status_user" name="status_user" hidden>
            <option value="1">Activo</option>
        </select><br>

        <!-- Botón de envío -->
        <button type="submit">Registrar</button>
    </form>
</section>

<?php include 'notifications.php'; ?>
<?php include 'footer.php'; ?>

</body>
</html>