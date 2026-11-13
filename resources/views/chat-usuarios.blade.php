<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - LinkedFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/diseño_principal.css') }}">
    <style>
        .chat-wrapper {
            height: calc(100vh - 110px);
            min-height: 500px;
        }
        .conversaciones-list {
            overflow-y: auto;
            height: calc(100% - 115px);
        }
        .conversacion-item {
            cursor: pointer;
            transition: background-color 0.15s ease;
        }
        .conversacion-item:hover, .conversacion-item.active {
            background-color: #eef4ff;
        }
        .mensajes-body {
            overflow-y: auto;
            flex: 1;
            background-color: #f8f9fa;
        }
        .burbuja-propia {
            background-color: #0d6efd;
            color: #fff;
            border-radius: 16px 16px 4px 16px;
            max-width: 72%;
        }
        .burbuja-ajena {
            background-color: #ffffff;
            color: #212529;
            border: 1px solid #dee2e6;
            border-radius: 16px 16px 16px 4px;
            max-width: 72%;
        }
    </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-brand shadow-sm sticky-top py-2">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center py-0" href="/feed">
            <div class="bg-white px-2 py-1 rounded-3 shadow-sm d-flex align-items-center" style="height: 42px;">
                <img src="/images/logofic.webp" alt="LinkedFIC Logo" style="height: 34px; width: auto; object-fit: contain;">
            </div>
        </a>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <div class="ms-auto d-flex align-items-center gap-2 gap-lg-3 mt-3 mt-lg-0">
                <a href="/feed" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Inicio (Feed)</a>
                <a href="/directorio" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Directorio</a>
                <a href="/chat" class="btn btn-light btn-sm fw-bold rounded-pill px-3 text-primary">
                    <i class="bi bi-chat-dots-fill me-1"></i> Mensajes
                </a>
                <a href="/perfil" id="navBtnPerfil" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">Mi Perfil</a>
                <button type="button" id="btnLogout" class="btn btn-gold btn-sm fw-bold rounded-pill px-3">Cerrar Sesión</button>
            </div>
        </div>
    </div>
</nav>

<div class="container my-3">
    <div id="alertBox" class="alert d-none" role="alert"></div>

    <div class="card shadow-sm border-0 rounded-3 overflow-hidden chat-wrapper">
        <div class="row g-0 h-100">
            <!-- Panel Izquierdo: Lista de Conversaciones -->
            <div class="col-md-4 border-end bg-white h-100 d-flex flex-column">
                <div class="p-3 border-bottom">
                    <h5 class="fw-bold mb-2 text-primary">
                        <i class="bi bi-chat-left-text me-1"></i> Conversaciones
                    </h5>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="buscarConversacion" class="form-control bg-light border-start-0" placeholder="Buscar chat...">
                    </div>
                </div>

                <div id="listaConversaciones" class="conversaciones-list list-group list-group-flush">
                    <div class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary" role="status"></div>
                        <p class="small mt-2 mb-0">Cargando conversaciones...</p>
                    </div>
                </div>
            </div>

            <!-- Panel Derecho: Hilo de Mensajes -->
            <div class="col-md-8 h-100 d-flex flex-column bg-white">
                <!-- Cabecera del chat activo -->
                <div id="chatHeader" class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between d-none">
                    <div class="d-flex align-items-center gap-2">
                        <div id="chatHeaderAvatar" class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 42px; height: 42px; overflow: hidden;">
                            ?
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold" id="chatHeaderNombre">Selecciona un chat</h6>
                            <small class="text-muted" id="chatHeaderRol"></small>
                        </div>
                    </div>
                    <a id="btnVerPerfilChat" href="#" class="btn btn-outline-secondary btn-sm rounded-pill">
                        <i class="bi bi-person"></i> Ver Perfil
                    </a>
                </div>

                <!-- Estado vacío inicial -->
                <div id="chatEmptyState" class="flex-grow-1 d-flex flex-column align-items-center justify-content-center text-center p-4 text-muted">
                    <i class="bi bi-chat-square-dots display-3 text-primary opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-dark">Tus Mensajes</h5>
                    <p class="mb-3" style="max-width: 360px;">No tienes mensajes aún, inicia una conversación con algún usuario del directorio.</p>
                    <a href="/directorio" class="btn btn-primary btn-sm rounded-pill px-4">
                        <i class="bi bi-people me-1"></i> Ir al Directorio
                    </a>
                </div>

                <!-- Contenedor de mensajes -->
                <div id="mensajesContainer" class="mensajes-body p-3 d-flex flex-column gap-2 d-none"></div>

                <!-- Formulario de envío -->
                <form id="formEnviarMensaje" class="p-3 border-top bg-white d-none">
                    <div class="input-group">
                        <input type="text" id="inputMensaje" class="form-control" placeholder="Escribe un mensaje..." maxlength="1000" autocomplete="off" required>
                        <button type="submit" id="btnEnviar" class="btn btn-primary px-4 fw-bold">
                            <i class="bi bi-send-fill me-1"></i> Enviar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('js/chat-usuarios.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>