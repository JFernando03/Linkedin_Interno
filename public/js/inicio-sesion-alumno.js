document.getElementById('formLogin').addEventListener('submit', async function (e) {
    e.preventDefault();

    const alertBox = document.getElementById('alertBox');
    const btnSubmit = document.getElementById('btnSubmit');

    // Limpiar alertas e indicaciones de validación previas
    alertBox.classList.add('d-none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Verificando...';

    const formData = new FormData(this);

    try {
        const response = await fetch('/api/login', {
            method: 'POST',
            headers: {
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message || '¡Inicio de sesión exitoso!';
            alertBox.classList.remove('d-none');

            // Guardar el token de acceso que retorna el AuthController ('access_token')
            localStorage.setItem('auth_token', data.access_token);
            localStorage.setItem('usuario', JSON.stringify(data.usuario));

            // Redirigir a la vista de perfil tras 1 segundo
            setTimeout(() => {
                window.location.href = '/perfil';
            }, 1000);

        } else if (response.status === 422) {
            alertBox.className = 'alert alert-danger';
            
            if (data.errors && data.errors.email) {
                alertBox.textContent = data.errors.email[0];
            } else {
                alertBox.textContent = data.message || 'Credenciales incorrectas.';
            }
            
            alertBox.classList.remove('d-none');

            if (data.errors) {
                Object.keys(data.errors).forEach(field => {
                    const input = document.getElementById(field);
                    const errorDiv = document.getElementById(`error-${field}`);
                    if (input) input.classList.add('is-invalid');
                    if (errorDiv) errorDiv.textContent = data.errors[field][0];
                });
            }
        } else {
            throw new Error(data.message || 'Ocurrió un error inesperado al iniciar sesión.');
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = 'Ingresar';
    }
});