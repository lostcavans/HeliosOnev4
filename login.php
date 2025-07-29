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
            border: 1px solid #fff;
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
            color: #a31900;
        }
        .btn-login {
            background-color: #a31900;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .btn-login:hover {
            background-color: #8a1500;
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
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
        <div class="divider" style="margin: 20px 0;"></div>
        <a href="register_client.php" style="color: #a31900;">Crear cuenta</a>
    </div>

    <script src="./js/jquery-3.1.1.min.js"></script>
    <script>
        document.getElementById("togglePassword").addEventListener("click", function() {
            const passwordInput = document.getElementById("Password");
            const type = passwordInput.getAttribute("type") === "password" ? "text" : "password";
            passwordInput.setAttribute("type", type);
            this.classList.toggle("fa-eye-slash");
        });

        document.getElementById("loginForm").addEventListener("submit", function(event) {
            event.preventDefault();
            const email = document.getElementById("Email").value;
            const password = document.getElementById("Password").value;

            // Mostrar loader
            const submitBtn = this.querySelector('button[type="submit"]');
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verificando...';
            submitBtn.disabled = true;

            fetch('loginCN.php', {
                method: 'POST',
                credentials: 'include', // Esto es esencial para manejar cookies
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'email': email,
                    'password': password
                })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Error en la red');
                }
                return response.json();
            })
            .then(data => {
                if (data.status === "success") {
                    // Redirigir después de mostrar mensaje
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Redirigiendo al sistema...',
                        showConfirmButton: false,
                        timer: 1500,
                        background: '#333',
                        color: '#fff'
                    }).then(() => {
                        // Forzar recarga completa con parámetro de cache
                        window.location.href = data.redirect + '?r=' + Date.now();
                    });
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
                        title: 'Error de autenticación',
                        text: data.message || 'Credenciales incorrectas',
                        confirmButtonColor: '#a31900',
                        background: '#333',
                        color: '#fff'
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
            })
            .finally(() => {
                submitBtn.innerHTML = originalBtnText;
                submitBtn.disabled = false;
            });
        });

        // Verificar si hay parámetros de error en la URL
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get('error');
            
            if (error === 'session_expired') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sesión expirada',
                    text: 'Por favor ingresa nuevamente',
                    confirmButtonColor: '#a31900',
                    background: '#333',
                    color: '#fff'
                });
            } else if (error === 'invalid_session') {
                Swal.fire({
                    icon: 'error',
                    title: 'Sesión inválida',
                    text: 'Debes iniciar sesión nuevamente',
                    confirmButtonColor: '#a31900',
                    background: '#333',
                    color: '#fff'
                });
            } else if (error === 'invalid_session_data') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de sesión',
                    text: 'Los datos de sesión son inválidos',
                    confirmButtonColor: '#a31900',
                    background: '#333',
                    color: '#fff'
                });
            }
        });
    </script>
</body>
</html>