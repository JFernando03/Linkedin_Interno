<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión - Alumnos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/inicio-sesion-alumno.css') }}" rel="stylesheet">
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

<script src="{{ asset('js/inicio-sesion-alumno.js') }}"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>