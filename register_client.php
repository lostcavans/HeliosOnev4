<?php session_start(); ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="stylesheet" href="css/register_user.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>/* Estilos generales */
body {
    font-family: Arial, sans-serif;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f5f5f5;
}

form {
    background-color: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    padding: 20px;
    max-width: 100%;
    width: 90%;
    box-sizing: border-box;
    overflow: auto;
}

form label {
    display: block;
    margin-top: 10px;
    font-weight: bold;
}

form input[type="text"],
form input[type="tel"],
form input[type="date"],
form input[type="password"],
form input[type="email"],
form select {
    width: 100%;
    padding: 8px;
    margin-top: 5px;
    border-radius: 4px;
    border: 1px solid #ccc;
    box-sizing: border-box;
}

form button {
    margin-top: 15px;
    padding: 10px;
    width: 100%;
    background-color: #4CAF50;
    color: #ffffff;
    font-size: 16px;
    font-weight: bold;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

form button:hover {
    background-color: #45a049;
}

/* Diseño responsivo para pantallas pequeñas */
@media (max-width: 768px) {
    form {
        width: 100%;
        margin: 0 10px;
    }
}

/* Para pantallas muy pequeñas */
@media (max-width: 480px) {
    form label, form input, form button {
        font-size: 14px;
    }

    form button {
        padding: 8px;
        font-size: 14px;
    }
}
 .password-strength {
            height: 5px;
            width: 100%;
            background-color: #ccc;
            margin-top: 5px;
            border-radius: 3px;
        }
        /* Diseño responsivo para pantallas pequeñas */
        @media (max-width: 768px) {
            form {
                width: 100%;
                margin: 0 10px;
            }
        }
        /* Para pantallas muy pequeñas */
        @media (max-width: 480px) {
            form label, form input, form button {
                font-size: 14px;
            }
            form button {
                padding: 8px;
                font-size: 14px;
            }
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
            if (strength <= 2) {
                strengthBar.style.backgroundColor = 'red';
                strengthText.textContent = 'Débil';
            } else if (strength === 3) {
                strengthBar.style.backgroundColor = 'yellow';
                strengthText.textContent = 'Media';
            } else {
                strengthBar.style.backgroundColor = 'green';
                strengthText.textContent = 'Fuerte';
            }
        }
        function validateForm(event) {
            event.preventDefault();
            const form = document.forms['userForm'];
            const birthDate = new Date(form['fec_nac'].value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            if (age < 18 || age > 98) {
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
            submitForm();
        }
        async function submitForm() {
            const form = document.forms['userForm'];
            const formData = new FormData(form);
            try {
                const response = await fetch('register_user_sol.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                if (result.success) {
                    alert(result.message);
                    window.location.href = 'login.php';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al enviar el formulario.');
            }
        }
    </script>
</head>
<body>
<section class="full-box dashboard-contentPage">
    <h2>Registrar Usuario</h2>
  <form name="userForm" onsubmit="validateForm(event)">
    <label for="nombres">Nombres:</label>
    <input type="text" name="nombres" required>

    <label for="apel_mat">Apellido Materno:</label>
    <input type="text" name="apel_mat" required>

    <label for="apel_pat">Apellido Paterno:</label>
    <input type="text" name="apel_pat" required>

    <label for="cel">Celular:</label>
    <input type="tel" name="cel" required>

    <label for="fec_nac">Fecha de Nacimiento:</label>
    <input type="date" name="fec_nac" required>

    <label for="CI">CI:</label>
    <input type="text" name="CI" required>

    <label for="pais">País:</label>
<select name="pais" required>
    <option value="">Seleccione un país</option>
    <option value="Argentina">Argentina</option>
    <option value="Bahamas">Bahamas</option>
    <option value="Barbados">Barbados</option>
    <option value="Belice">Belice</option>
    <option value="Bolivia">Bolivia</option>
    <option value="Brasil">Brasil</option>
    <option value="Canadá">Canadá</option>
    <option value="Chile">Chile</option>
    <option value="Colombia">Colombia</option>
    <option value="Costa Rica">Costa Rica</option>
    <option value="Cuba">Cuba</option>
    <option value="Dominica">Dominica</option>
    <option value="Ecuador">Ecuador</option>
    <option value="El Salvador">El Salvador</option>
    <option value="Estados Unidos">Estados Unidos</option>
    <option value="Granada">Granada</option>
    <option value="Guatemala">Guatemala</option>
    <option value="Guyana">Guyana</option>
    <option value="Haití">Haití</option>
    <option value="Honduras">Honduras</option>
    <option value="Jamaica">Jamaica</option>
    <option value="México">México</option>
    <option value="Nicaragua">Nicaragua</option>
    <option value="Panamá">Panamá</option>
    <option value="Paraguay">Paraguay</option>
    <option value="Perú">Perú</option>
    <option value="República Dominicana">República Dominicana</option>
    <option value="San Cristóbal y Nieves">San Cristóbal y Nieves</option>
    <option value="San Vicente y las Granadinas">San Vicente y las Granadinas</option>
    <option value="Santa Lucía">Santa Lucía</option>
    <option value="Surinam">Surinam</option>
    <option value="Trinidad y Tobago">Trinidad y Tobago</option>
    <option value="Uruguay">Uruguay</option>
    <option value="Venezuela">Venezuela</option>
</select>


    <label for="ciud">Ciudad:</label>
    <input type="text" name="ciud" required>

     <label for="pass_user">Contraseña:</label>
        <input type="password" name="pass_user" id="pass_user" required oninput="updatePasswordStrength(this.value)">
        <div class="password-strength" id="passwordStrength"></div>
        <p id="strengthText"></p>

        <label for="confirm_pass_user">Confirmar Contraseña:</label>
        <input type="password" name="confirm_pass_user" id="confirm_pass_user" required>


    <label for="email">Correo Electrónico:</label>
    <input type="email" name="email" required>

    <label for="inst">Institución:</label>
    <input type="text" name="inst">

    <!-- Campos ocultos -->
    <input type="hidden" name="id_cargo" value="2">
    <input type="hidden" name="stat" value="1">

    <button type="submit">Registrar</button>
</form>

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
            if (strength <= 2) {
                strengthBar.style.backgroundColor = 'red';
                strengthText.textContent = 'Débil';
            } else if (strength === 3) {
                strengthBar.style.backgroundColor = 'yellow';
                strengthText.textContent = 'Media';
            } else {
                strengthBar.style.backgroundColor = 'green';
                strengthText.textContent = 'Fuerte';
            }
        }
                  
    function validateForm(event) {
        event.preventDefault();
        const form = document.forms['userForm'];
        const birthDate = new Date(form['fec_nac'].value);
        const today = new Date();
        const age = today.getFullYear() - birthDate.getFullYear();

        if (age < 18 || age > 98) {
            alert('La edad debe estar entre 18 y 98 años.');
            return false;
        }

        const password = form['pass_user'].value;
        const confirmPassword = form['confirm_pass_user'] ? form['confirm_pass_user'].value : '';

        if (!validatePasswordStrength(password)) {
            alert('La contraseña debe tener al menos 8 caracteres, incluir una letra mayúscula, una letra minúscula, un número y un carácter especial.');
            return false;
        }

        if (confirmPassword && password !== confirmPassword) {
            alert('Las contraseñas no coinciden.');
            return false;
        }

        submitForm();
    }

    async function submitForm() {
        const form = document.forms['userForm'];
        const formData = new FormData(form);

        try {
            const response = await fetch('register_user_sol.php', {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            if (result.success) {
                alert(result.message);
                window.location.href = 'login.php';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al enviar el formulario.');
        }
    }
</script>

</section>
</body>
</html>
