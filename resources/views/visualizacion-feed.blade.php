<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed | linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                
                <button type="button" onclick="cerrarSesion()" class="btn btn-gold btn-sm fw-bold rounded-pill px-3">Cerrar Sesión</button>
            </div>
        </div>
    </div>
</nav>

    <!-- Contenido Principal -->
    <div class="container py-4" style="max-width: 680px;">
        
        <!-- Formulario de Publicación -->
        <form id="formCrearPublicacion" enctype="multipart/form-data">
            <div class="mb-3">
                <input type="text" id="tituloPublicacion" class="form-control" placeholder="Título de la publicación..." required>
            </div>

            <div class="mb-3">
                <textarea id="contenidoPublicacion" class="form-control" rows="3" placeholder="¿Qué quieres compartir hoy?" required></textarea>
            </div>
            <!-- Imagen y fecha programada -->
            <div class="row mb-3">
                <div class="col-md-6 mb-2">
                    <label for="multimediaPublicacion" class="form-label small fw-bold text-muted">Imagen/Multimedia (Opcional)</label>
                    <input type="file" id="multimediaPublicacion" class="form-control form-control-sm" accept="image/*,video/*">
                </div>

                <div class="col-md-6 mb-2">
                    <label for="fechaProgramada" class="form-label small fw-bold text-muted">Programar publicación (Opcional)</label>
                    <input type="datetime-local" id="fechaProgramada" class="form-control form-control-sm">
                </div>
            </div>

            <button type="submit" id="btnPublicar" class="btn btn-primary w-100 fw-bold">Publicar</button>
        </form>
            <!-- Opción dinámica para eliminar imagen al editar -->
            <div id="contenedorEliminarImagen" class="form-check mt-2 d-none">
                <input class="form-check-input" type="checkbox" id="checkEliminarImagen">
                <label class="form-check-label text-danger small fw-bold" for="checkEliminarImagen">
                    Eliminar imagen actual
                </label>
            </div>
        <!-- Muro / Lista de Publicaciones -->
        <div id="contenedorFeed">
            <div class="text-center py-4 text-muted" id="loadingFeed">
                <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                <span class="ms-2">Cargando publicaciones...</span>
            </div>
        </div>

    </div>

<script src="{{ asset('js/feed.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>