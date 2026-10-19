<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --brand-blue: #0a66c2;
            --brand-blue-hover: #004182;
            --brand-gold: #c09018;
        }

        .bg-brand {
            background-color: var(--brand-blue) !important;
        }

        .btn-brand {
            background-color: var(--brand-blue);
            border-color: var(--brand-blue);
            color: #ffffff;
        }

        .btn-brand:hover, .btn-brand:focus {
            background-color: var(--brand-blue-hover);
            border-color: var(--brand-blue-hover);
            color: #ffffff;
        }

        .text-brand-gold {
            color: var(--brand-gold) !important;
        }

        .form-control:focus {
            border-color: var(--brand-blue);
            box-shadow: 0 0 0 0.25rem rgba(10, 102, 194, 0.25);
        }
    </style>
    
</head>
<body class="bg-light d-flex align-items-center min-vh-100 py-4">

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            
            <!-- 1. CONTENEDOR DEL LOGO DE LA MARCA -->
            <div class="text-center mb-4">
                <img src="/images/logofic.webp" alt="LinkedFIC Logo" class="img-fluid" style="max-width: 240px; height: auto;">
            </div>

            <!-- Tarjeta de Login -->
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-brand text-white text-center py-3 rounded-top-3">
                    <h5 class="mb-0 fw-bold">Iniciar Sesión</h5>
                </div>
                <div class="card-body p-4">

                    <!-- Contenedor para mensajes de alerta -->
                    <div id="alertBox" class="alert d-none" role="alert"></div>

                    <form id="formLogin" action="/api/login" method="POST">
                        @csrf

                        <!-- Correo Electrónico -->
                        <div class="mb-3">
                            <label for="email" class="form-label fw-semibold">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" required placeholder="correo@ejemplo.com">
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-4">
                            <label for="password" class="form-label fw-semibold">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required placeholder="******">
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <!-- Botón de Ingreso -->
                        <div class="d-grid mb-3">
                            <button type="submit" id="btnSubmit" class="btn btn-brand btn-lg fw-bold rounded-pill">Ingresar</button>
                        </div>

                        <!-- Enlace al Registro -->
                        <div class="text-center">
                            <a href="/registro-alumno" class="text-brand-gold text-decoration-none fw-bold">Regístrate aquí</a>
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