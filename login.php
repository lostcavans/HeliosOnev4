<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Sistema de gerenciamiento de cuerpo de Bomberos - La Paz/Bolivia</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/login.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/material-design-iconic-font/2.2.0/css/material-design-iconic-font.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    

    <style>
        body.cover {
            background-size: cover;
            background-position: center;
            color: #ffffff;
            font-family: 'Arial', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }
        .container-login {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 12px;
            width: 90%;
            max-width: 350px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        .container-login i.zmdi {
            color: #a31900;
            margin-bottom: 10px;
        }
        .input-field {
            position: relative;
            margin-bottom: 25px;
        }
        .input-field input {
            background-color: #333;
            border: 1px solid #fff; /* Temporal */
            color: #ffffff;
            border-radius: 4px;
            padding: 10px;
            width: 100%;
            box-sizing: border-box;
        }
        .password-toggle {
            cursor: pointer;
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #a31900; /* Asegúrate de que el color sea visible */
        }
    </style>
</head>
<body class="cover" style="background-image: url(assets/img/bombero4.jpg);">
    <div class="container-login center-align">
        <div class="logo">
            <img src="assets\img\bomb-removebg-preview.png" alt="Logo de la empresa" width="150">
        </div>

        <form id="loginForm">
            <div class="input-field">
                <input id="Email" name="email" type="email" class="validate" required>
                <label for="Email"><i class="zmdi zmdi-email"></i>&nbsp; Email</label>
            </div>
            <div class="input-field col s12" style="position: relative;">
                <input id="Password" name="password" type="password" class="validate" required>
                <label for="Password"><i class="zmdi zmdi-lock"></i>&nbsp; Contraseña</label>
                <i class="fas fa-eye password-toggle" id="togglePassword"></i>

            </div>
            <button type="submit" class="waves-effect waves-teal btn-flat">Ingresar &nbsp; </button>
        </form>
        <div class="divider" style="margin: 20px 0;"></div>
        <a href="register_client.php">Crear cuenta</a>
    </div>
    <script src="./js/jquery-3.1.1.min.js"></script>
    <script src="./js/bootstrap.min.js"></script>
    <script src="./js/material.min.js"></script>
    <script src="./js/ripples.min.js"></script>
    <script src="./js/jquery.mCustomScrollbar.concat.min.js"></script>
    <script src="./js/main.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("Password");
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            this.classList.toggle("zmdi-eye-off"); // Cambiar a ícono de ojo cerrado
            this.classList.toggle("zmdi-eye"); // Cambiar a ícono de ojo abierto
        });

        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();
            const email = document.getElementById("Email").value;
            const password = document.getElementById("Password").value;

            fetch('loginCN.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'email': email,
                    'password': password
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    window.location.href = "map.php";
                } else if (data.status === "deactivated") {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cuenta desactivada',
                        text: 'Tu cuenta ha sido desactivada, contacta con soporte.',
                        confirmButtonColor: '#ff5722',
                        background: '#333',
                        color: '#fff'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message,
                        confirmButtonColor: '#a31900',
                        background: '#333',
                        color: '#fff',
                        footer: '¿Olvidaste tu contraseña?'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: "Hubo un problema al conectar con el servidor.",
                    confirmButtonColor: '#a31900',
                    background: '#333',
                    color: '#fff'
                });
            });
        });
    </script>
</body>
</html>
