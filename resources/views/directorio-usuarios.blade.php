<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directorio de la Comunidad - linkedinFIC</title>
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
                <a href="/login" onclick="localStorage.clear(); sessionStorage.clear();" class="btn btn-gold btn-sm fw-bold rounded-pill px-3">Cerrar Sesión</a>
            </div>
        </div>
    </div>
</nav>

<div class="container my-5">
    <div class="row mb-4">
        <div class="col-md-8 mx-auto text-center">
            <h2 class="fw-bold">Directorio de la Comunidad</h2>
            <p class="text-muted">Conecta con los miembros activos de la plataforma.</p>
            
            <input type="text" id="inputBuscar" class="form-control form-control-lg shadow-sm" placeholder="Buscar por nombre...">
        </div>
    </div>

    <!-- Alerta de estado -->
    <div id="alertBox" class="alert d-none" role="alert"></div>

    <!-- Loader mientras carga -->
    <div id="loader" class="text-center py-5">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Cargando directorio...</span>
        </div>
        <p class="mt-2 text-muted">Cargando directorio de la comunidad...</p>
    </div>

    <!-- Contenedor de Tarjetas -->
    <div class="row g-4" id="contenedorUsuarios"></div>
</div>

<script src="{{ asset('js/directorio.js') }}?v={{ time() }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>