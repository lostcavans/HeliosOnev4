// Variables globales
let photoFile = null;

// Cargar foto - Evento click corregido
document.getElementById('photoPreview').addEventListener('click', function() {
    document.getElementById('user_photo').click();
});

document.getElementById('user_photo').addEventListener('change', function(e) {
    const preview = document.getElementById('photoPreview');
    const errorElement = document.getElementById('photoError');
    
    if (e.target.files && e.target.files[0]) {
        const file = e.target.files[0];
        const fileType = file.type;
        const fileSize = file.size / 1024 / 1024; // en MB
        
        // Validar tipo de archivo
        if (!fileType.match('image.*')) {
            errorElement.textContent = 'Solo se permiten imágenes (JPG, PNG)';
            e.target.value = '';
            return;
        }
        
        // Validar tamaño de archivo
        if (fileSize > 2) {
            errorElement.textContent = 'La imagen no debe superar 2MB';
            e.target.value = '';
            return;
        }
        
        photoFile = file;
        errorElement.textContent = '';
        
        const reader = new FileReader();
        
        reader.onload = function(e) {
            preview.innerHTML = '';
            const img = document.createElement('img');
            img.src = e.target.result;
            preview.appendChild(img);
        }
        
        reader.readAsDataURL(file);
    }
});

// Alternar visibilidad de contraseña
document.getElementById('togglePassword').addEventListener('click', function() {
    const input = document.getElementById('pass_user');
    const icon = this;
    input.type = input.type === 'password' ? 'text' : 'password';
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
});

// Validar fortaleza de contraseña
document.getElementById('pass_user').addEventListener('input', function(e) {
    const password = e.target.value;
    const strengthBar = document.getElementById('passwordStrength');
    const strengthText = document.getElementById('strengthText');
    let strength = 0;

    if (password.length >= 8) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) strength++;

    strengthBar.className = 'password-strength';
    if (strength <= 1) {
        strengthBar.classList.add('strength-weak');
        strengthText.textContent = 'Contraseña Débil';
    } else if (strength <= 3) {
        strengthBar.classList.add('strength-medium');
        strengthText.textContent = 'Contraseña Media';
    } else {
        strengthBar.classList.add('strength-strong');
        strengthText.textContent = 'Contraseña Fuerte';
    }
});

// Validar edad
document.getElementById('fec_nac_user').addEventListener('change', function() {
    const birthDate = new Date(this.value);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    
    const isValid = age >= 18 && age <= 98;
    document.getElementById('ageError').textContent = isValid ? '' : 'La edad debe estar entre 18 y 98 años.';
});

// Validar formulario
document.getElementById('credentialForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    // Validar foto
    if (!photoFile) {
        document.getElementById('photoError').textContent = 'Debe subir una foto para la credencial';
        return;
    }
    
    // Validar edad
    const ageValid = !document.getElementById('ageError').textContent;
    if (!ageValid) return;
    
    // Validar contraseña
    const password = document.getElementById('pass_user').value;
    if (!validatePasswordStrength(password)) {
        alert('La contraseña no cumple con los requisitos de seguridad');
        return;
    }
    
    // Verificar duplicados
    if (!await checkDuplicate('CI_user', 'ciError', 'La cédula ya está registrada')) return;
    if (!await checkDuplicate('email_user', 'emailError', 'El email ya está registrado')) return;
    
    // Confirmación
    if (!confirm('¿Confirmar el registro de esta credencial?')) return;
    
    // Enviar formulario
    submitForm();
});

// Función para validar la fortaleza de la contraseña
function validatePasswordStrength(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumbers = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password);
    
    return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar;
}

// Verificar duplicados
async function checkDuplicate(fieldId, errorId, errorMsg) {
    const field = document.getElementById(fieldId);
    const errorElement = document.getElementById(errorId);
    const value = field.value.trim();
    
    if (!value) return true;
    
    try {
        const response = await fetch('check_duplicate.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `field=${fieldId}&value=${encodeURIComponent(value)}`
        });
        
        const result = await response.json();
        
        if (result.exists) {
            errorElement.textContent = errorMsg;
            return false;
        } else {
            errorElement.textContent = '';
            return true;
        }
    } catch (error) {
        console.error('Error:', error);
        return true;
    }
}

// Enviar formulario
async function submitForm() {
    const form = document.getElementById('credentialForm');
    const formData = new FormData(form);
    
    try {
        const response = await fetch('register_user_sol.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert(result.message);
            form.reset();
            document.getElementById('photoPreview').innerHTML = `
                <div class="photo-placeholder">
                    <i class="fas fa-camera"></i>
                    <span>Subir foto</span>
                </div>`;
            document.getElementById('passwordStrength').className = 'password-strength';
            document.getElementById('strengthText').textContent = 'Mínimo 8 caracteres con mayúsculas, minúsculas, números y símbolos';
        } else {
            alert('Error: ' + result.message);
        }
    } catch (error) {
        console.error('Error:', error);
        alert('Error al enviar el formulario. Por favor intente nuevamente.');
    }
}