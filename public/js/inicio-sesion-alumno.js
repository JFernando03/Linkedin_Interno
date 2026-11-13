document.addEventListener('DOMContentLoaded', function () {
    const radios = document.querySelectorAll('input[name="tipoPerfil"]');
    const tituloLogin = document.getElementById('tituloLogin');
    const inputTipo = document.getElementById('inputTipo');
    const enlaceRegistro = document.getElementById('enlaceRegistro');

    if (radios.length > 0) {
        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'empresa') {
                    if (tituloLogin) tituloLogin.textContent = 'Iniciar Sesión - Empresa';
                    if (inputTipo) inputTipo.value = 'empresa';
                    if (enlaceRegistro) {
                        enlaceRegistro.href = '/registro-empresa';
                        enlaceRegistro.textContent = '¿No tienes cuenta de empresa? Regístrate aquí';
                    }
                } else {
                    if (tituloLogin) tituloLogin.textContent = 'Iniciar Sesión - Alumno';
                    if (inputTipo) inputTipo.value = 'alumno';
                    if (enlaceRegistro) {
                        enlaceRegistro.href = '/registro-alumno';
                        enlaceRegistro.textContent = '¿No tienes cuenta? Regístrate aquí';
                    }
                }
            });
        });
    }
});

document.getElementById('formLogin').addEventListener('submit', async function (e) {
    e.preventDefault();

    const alertBox = document.getElementById('alertBox');
    const btnSubmit = document.getElementById('btnSubmit');

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

            const token = data.access_token || data.token;
            localStorage.setItem('auth_token', token);
            localStorage.setItem('token', token);

            const usuario = data.usuario || data.user || {};
            localStorage.setItem('usuario', JSON.stringify(usuario));

            const esEmpresa = usuario.idRol == 2 || Boolean(usuario.empresa);
            const rutaDestino = esEmpresa ? '/perfil/empresa' : '/perfil';

            setTimeout(() => {
                window.location.href = rutaDestino;
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