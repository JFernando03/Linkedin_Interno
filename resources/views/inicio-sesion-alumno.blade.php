<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Iniciar Sesión</h4>
                </div>
                <div class="card-body p-4">

                    <!-- Contenedor para mensajes de alerta -->
                    <div id="alertBox" class="alert d-none" role="alert"></div>

                    <form id="formLogin" action="/api/login" method="POST">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="correo@ejemplo.com">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="******">
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <!-- Botón de Ingreso -->
                        <div class="d-grid mb-3">
                            <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg">Ingresar</button>
                        </div>

                        <!-- Enlace al Registro -->
                        <div class="text-center">
                            <small class="text-muted">¿No tienes cuenta? <a href="/registro-alumno" class="text-decoration-none fw-bold">Regístrate aquí</a></small>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>