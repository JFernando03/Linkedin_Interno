<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/diseño_principal.css') }}">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-brand shadow-sm sticky-top py-2">
    <div class="container">

        <a class="navbar-brand d-flex align-items-center py-0" href="/feed">
            <div class="bg-white px-2 py-1 rounded-3 shadow-sm d-flex align-items-center" style="height: 42px;">
                <img src="/images/logofic.webp" alt="LinkedFIC Logo" style="height: 34px; width: auto; object-fit: contain;">
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="ms-auto d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <span id="navUserName" class="text-white fw-bold d-none d-lg-inline"></span>
                
                <a href="/feed" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Inicio (Feed)</a>
                <a href="/perfil" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Mi Perfil</a>
                
                <button type="button" id="btnLogout" class="btn btn-gold btn-sm fw-bold rounded-pill px-3">Cerrar Sesión</button>
            </div>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">

            <!-- Alerta de estado/error -->
            <div id="alertBox" class="alert d-none" role="alert"></div>

            <!-- Loader mientras carga la info -->
            <div id="loader" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando perfil...</span>
                </div>
                <p class="mt-2 text-muted">Cargando información del perfil...</p>
            </div>

            <!-- Tarjeta con la información del perfil -->
            <div id="cardPerfil" class="card shadow-sm d-none">
                <div class="card-header bg-white py-4 border-0 text-center border-bottom position-relative">
                    
                    <!-- Botón para Editar Perfil -->
                    <a href="/perfil/editar" class="btn btn-outline-primary btn-sm position-absolute top-0 end-0 m-3">
                        <i class="bi bi-pencil-square"></i> Editar Perfil
                    </a>

                    <div class="avatar-circle mx-auto bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fs-2 fw-bold mb-2 shadow-sm" style="width: 80px; height: 80px;" id="profileAvatar">
                        --
                    </div>
                    <h3 class="mb-0 fw-bold" id="profileNombreCompleto">Cargando...</h3>
                    <span class="badge bg-secondary mt-2 fs-6" id="profileRol">Rol</span>
                </div>

                <div class="card-body p-4">
                    <!-- Sección Información Académica -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-mortarboard-fill me-1"></i> Información Académica
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Carrera / Programa</div>
                        <div class="col-sm-8 fw-semibold" id="profileCarrera">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Semestre / Año</div>
                        <div class="col-sm-8 fw-semibold" id="profileSemestre">--</div>
                    </div>

                    <hr class="my-4">

                    <!-- Sección Información General y de Contacto -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-person-lines-fill me-1"></i> Información General
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Correo Electrónico</div>
                        <div class="col-sm-8 fw-semibold" id="profileEmail">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Teléfono</div>
                        <div class="col-sm-8 fw-semibold" id="profileTelefono">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Sobre Mí</div>
                        <div class="col-sm-8" id="profileSobreMi">--</div>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN HABILIDADES TECNOLÓGICAS -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-code-slash me-1"></i> Habilidades Tecnológicas
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Mis Habilidades</div>
                        <div class="col-sm-8">
                            <div id="profileHabilidades" class="d-flex flex-wrap gap-1">
                                <span class="text-muted">Cargando habilidades...</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Sección Documentación -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Documentación
                    </h5>

                    <div class="row align-items-center">
                        <div class="col-sm-4 text-muted">Currículum Vitae</div>
                        <div class="col-sm-8" id="profileCurriculumContainer">
                            <span class="text-muted">Sin documento adjunto</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script src="{{ asset('/js/perfil-alumno.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>