<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacantes | linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
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
                <a href="/vacantes" class="btn btn-light btn-sm fw-bold rounded-pill px-3 text-primary">
                    <i class="bi bi-briefcase-fill me-1"></i> Vacantes
                </a>
                <a href="/chat" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">
                    <i class="bi bi-chat-dots me-1"></i> Mensajes
                </a>
                <a href="/perfil" id="navBtnPerfil" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Mi Perfil</a>
                <button type="button" onclick="cerrarSesion()" class="btn btn-gold btn-sm fw-bold rounded-pill px-3">Cerrar Sesión</button>
            </div>
        </div>
    </div>
</nav>

<div class="container py-4" style="max-width: 680px;">

    <!-- Formulario para Agregar / Editar Vacante (solo visible para Empresa) -->
    <div id="cardFormVacante" class="card border-0 shadow-sm mb-4 d-none">
        <div class="card-body p-4">
            <h5 id="tituloFormVacante" class="fw-bold mb-3 text-dark">
                <i class="bi bi-plus-circle-fill text-primary me-1"></i> Agregar Nueva Vacante
            </h5>

            <form id="formCrearVacante" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="puestoVacante" class="form-label small fw-bold text-muted">Puesto *</label>
                    <input type="text" id="puestoVacante" name="puesto" maxlength="255" class="form-control" placeholder="Ej. Desarrollador Backend Laravel" required>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label for="idModalidadVacante" class="form-label small fw-bold text-muted">Modalidad *</label>
                        <select id="idModalidadVacante" name="idModalidad" class="form-select form-select-sm" required>
                            <option value="">Cargando modalidades...</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="sueldoMinVacante" class="form-label small fw-bold text-muted">Sueldo mínimo (Opcional)</label>
                        <input type="number" id="sueldoMinVacante" name="sueldo_min" min="0" step="any" class="form-control form-control-sm" placeholder="Ej. 6000">
                    </div>

                    <div class="col-md-4">
                        <label for="sueldoMaxVacante" class="form-label small fw-bold text-muted">Sueldo máximo (Opcional)</label>
                        <input type="number" id="sueldoMaxVacante" name="sueldo_max" min="0" step="any" class="form-control form-control-sm" placeholder="Ej. 10000">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="contenidoVacante" class="form-label small fw-bold text-muted">Descripción / Contenido *</label>
                    <textarea id="contenidoVacante" name="contenido" class="form-control" rows="4" placeholder="Describe las actividades, requisitos y detalles de la vacante..." required></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                        <label for="multimediaVacante" class="form-label small fw-bold text-muted">Imagen / Multimedia (Máx. 10 MB)</label>
                        <input type="file" id="multimediaVacante" name="multimedia" class="form-control form-control-sm" accept="image/*,video/*">
                    </div>

                    <div class="col-md-6">
                        <label for="publishedAtVacante" class="form-label small fw-bold text-muted">Programar publicación (Opcional)</label>
                        <input type="datetime-local" id="publishedAtVacante" name="published_at" class="form-control form-control-sm">
                    </div>
                </div>

                <div id="contenedorEliminarMultimediaVacante" class="form-check mb-3 d-none">
                    <input class="form-check-input" type="checkbox" id="checkEliminarMultimediaVacante">
                    <label class="form-check-label text-danger small fw-bold" for="checkEliminarMultimediaVacante">
                        Eliminar multimedia actual
                    </label>
                </div>

                <div class="d-flex gap-2">
                    <button type="button" id="btnCancelarEdicion" class="btn btn-outline-secondary fw-bold d-none">
                        Cancelar
                    </button>
                    <button type="submit" id="btnPublicarVacante" class="btn btn-primary w-100 fw-bold">
                        Publicar Vacante
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Vacantes publicadas desde la BD -->
    <h6 class="fw-bold text-muted mb-3">Vacantes publicadas</h6>
    <div id="contenedorVacantes">
        <div class="text-center py-4 text-muted">
            <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
            <span class="ms-2">Cargando vacantes...</span>
        </div>
    </div>

</div>

<script src="{{ asset('js/vacantes.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>