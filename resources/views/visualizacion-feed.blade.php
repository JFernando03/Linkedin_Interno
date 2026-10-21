<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed | linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    :root {
        --brand-blue: #0a66c2;
        --brand-blue-hover: #004182;
        --brand-gold: #c09018;
        --brand-gold-hover: #a37a13;
    }

    /* Fondo azul corporativo para la barra */
    .bg-brand {
        background-color: var(--brand-blue) !important;
    }

    /* Botón Publicar */
    .btn-brand, #btnPublicar {
        background-color: var(--brand-blue) !important;
        border-color: var(--brand-blue) !important;
        color: #ffffff !important;
    }

    .btn-brand:hover, #btnPublicar:hover {
        background-color: var(--brand-blue-hover) !important;
        border-color: var(--brand-blue-hover) !important;
    }

    /* Botón Dorado (Cerrar sesión) */
    .btn-gold {
        background-color: var(--brand-gold) !important;
        border-color: var(--brand-gold) !important;
        color: #ffffff !important;
    }

    .btn-gold:hover {
        background-color: var(--brand-gold-hover) !important;
        border-color: var(--brand-gold-hover) !important;
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

<!-- Feed -->
<!-- Scripts de Lógica del Feed con CRUD completo -->
<script>
document.addEventListener('DOMContentLoaded', async function () {
    const formCrear = document.getElementById('formCrearPublicacion');
    if (formCrear) {
        formCrear.addEventListener('submit', crearPublicacion);
    }

    obtenerUsuarioActual(); // Muestra el respaldo rápido primero
    await actualizarUsuarioStorage(); // Trae los datos reales de la API
    cargarPublicaciones();
});

async function actualizarUsuarioStorage() {
    const token = localStorage.getItem('auth_token');
    if (!token) return;

    try {
        const res = await fetch('/api/perfil', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });

        if (res.ok) {
            const resJson = await res.json();
            // Guarda exactamente el objeto de usuario que retorna PerfilController.php
            localStorage.setItem('usuario', JSON.stringify(resJson.usuario));
            obtenerUsuarioActual();
        }
    } catch (e) {
        console.warn('Error al sincronizar perfil en Feed:', e);
    }
}

// Helper centralizado para preparar peticiones fetch
function getFetchOptions(method = 'GET', body = null) {
    const token = localStorage.getItem('auth_token');

    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (token && token !== 'null' && token !== 'undefined') {
        headers['Authorization'] = 'Bearer ' + token;
    }

    const options = {
        method: method,
        headers: headers
    };

    if (body) {
        options.body = JSON.stringify(body);
    }

    return options;
}

// Cargar Nombre de Usuario en el Navbar
function obtenerUsuarioActual() {
    const usuarioStorage = localStorage.getItem('usuario');
    const navUser = document.getElementById('navUserName');

    if (!usuarioStorage || !navUser) return;

    const usuario = JSON.parse(usuarioStorage);
    
    // Extracción directa según la relación cargada en el controller
    if (usuario.alumno && usuario.alumno.nombre) {
        const nombreCompleto = `${usuario.alumno.nombre} ${usuario.alumno.apellidoPaterno || ''}`.trim();
        navUser.textContent = `${nombreCompleto}`;
    } else {
        // En caso de que el usuario no tenga aún un registro en la tabla alumnos
        navUser.textContent = `${usuario.email}`;
    }
}

// GET /api/publicaciones -> Cargar Feed
async function cargarPublicaciones() {
    const contenedor = document.getElementById('contenedorFeed');

    try {
        const response = await fetch('/api/publicaciones', getFetchOptions('GET'));

        if (response.status === 401) {
            contenedor.innerHTML = `
                <div class="alert alert-warning text-center shadow-sm">
                    <strong>Sesión expirada.</strong><br>
                    Por favor, vuelve a ingresar desde la pantalla de Login.
                </div>`;
            return;
        }

        if (!response.ok) throw new Error('No se pudieron obtener las publicaciones.');

        const responseData = await response.json();

        // Extraer lista paginada (publicaciones.data)
        let publicaciones = [];
        if (responseData.publicaciones) {
            if (Array.isArray(responseData.publicaciones)) {
                publicaciones = responseData.publicaciones;
            } else if (responseData.publicaciones.data && Array.isArray(responseData.publicaciones.data)) {
                publicaciones = responseData.publicaciones.data;
            }
        } else if (Array.isArray(responseData)) {
            publicaciones = responseData;
        } else if (responseData.data && Array.isArray(responseData.data)) {
            publicaciones = responseData.data;
        }

        contenedor.innerHTML = '';

        if (!publicaciones || publicaciones.length === 0) {
            contenedor.innerHTML = '<div class="card border-0 shadow-sm p-4 text-center text-muted">No existen publicaciones actualmente. ¡Comparte algo!</div>';
            return;
        }

        // Actualizar el nombre en el navbar con el perfil de alumno si existe
        try {
            const userLocal = JSON.parse(localStorage.getItem('usuario'));
            const miPost = publicaciones.find(p => p.idUsuario == userLocal.idUsuario);
            if (miPost && miPost.usuario) {
                const navUser = document.getElementById('navUserName');
                let miNombre = miPost.usuario.nombre || miPost.usuario.name;
                if (miPost.usuario.alumno) {
                    miNombre = `${miPost.usuario.alumno.nombre || ''} ${miPost.usuario.alumno.apellidoPaterno || ''}`.trim();
                }
                if (miNombre && navUser) navUser.textContent = miNombre;
            }
        } catch(e) {}

        publicaciones.forEach(post => {
            contenedor.appendChild(crearCardPublicacion(post));
        });

    } catch (error) {
        contenedor.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

// Renderizar Tarjeta de Publicación
function crearCardPublicacion(post) {
    if (!post) return document.createElement('div');

    const card = document.createElement('div');
    card.className = 'card border-0 shadow-sm mb-3';

    // 1. Obtener ID del usuario en localStorage
    let userLocalId = null;
    try {
        const localUser = JSON.parse(localStorage.getItem('usuario'));
        if (localUser) userLocalId = localUser.idUsuario || localUser.id;
    } catch(e) {}

    // 2. Extraer datos del Autor
    let nombreUsuario = 'Usuario';
    const usuarioObj = post.usuario || post.user;

    if (usuarioObj) {
        if (usuarioObj.alumno) {
            nombreUsuario = `${usuarioObj.alumno.nombre || ''} ${usuarioObj.alumno.apellidoPaterno || usuarioObj.alumno.primerApellido || ''}`.trim();
        } 
        if (!nombreUsuario || nombreUsuario === 'Usuario') {
            nombreUsuario = usuarioObj.nombre || usuarioObj.name || usuarioObj.email || 'Usuario';
        }
    }

    const inicial = nombreUsuario.charAt(0).toUpperCase();
    const titulo = post.titulo || post.title || '';
    const contenido = post.contenido || post.content || '';
    const fechaOrigen = post.published_at || post.created_at;
    const fecha = fechaOrigen ? new Date(fechaOrigen).toLocaleDateString() : 'Reciente';

    // 3. Verificar si es dueño del post
    const esMio = post.esMia === true || post.esMia === 1 ||
        (post.idUsuario && userLocalId && post.idUsuario == userLocalId);

    // 4. Preparar menú HTML de opciones
    let menuHtml = '';
    if (esMio) {
        const idPost = post.idPublicacion || post.id;
        const titEnc = encodeURIComponent(titulo);
        const contEnc = encodeURIComponent(contenido);
        const tieneMedia = post.multimedia ? 1 : 0;
        const fechaProg = post.published_at || '';

        menuHtml = `
            <div class="dropdown">
                <button class="btn btn-link text-muted p-0 text-decoration-none fw-bold fs-5" type="button" data-bs-toggle="dropdown" aria-expanded="false">⋮</button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item text-warning" href="#" onclick="prepararEdicion(${idPost}, '${titEnc}', '${contEnc}', ${tieneMedia}, '${fechaProg}')">✏️ Editar</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarPublicacion(${idPost}, this)">🗑️ Eliminar</a></li>
                </ul>
            </div>
        `;
    }

    // 5. Preparar título e imagen opcionales
    const tituloHtml = titulo ? `<h5 class="fw-bold mb-2 text-dark">${titulo}</h5>` : '';
    const multimediaHtml = post.multimedia 
        ? `<div class="mt-3 text-center">
             <img src="/storage/${post.multimedia}" class="img-fluid rounded shadow-sm w-100" style="max-height: 400px; object-fit: cover;" alt="Imagen del post">
           </div>` 
        : '';

    // 6. Ensamblar HTML garantizando estructura vertical limpia
    card.innerHTML = `
        <div class="card-body p-3">
            <!-- Encabezado: Avatar + Nombre + Fecha a la izquierda | Menú a la derecha -->
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3 bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px; border-radius: 50%;">
                        ${inicial}
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark">${nombreUsuario}</h6>
                        <small class="text-muted">${fecha}</small>
                    </div>
                </div>
                ${menuHtml}
            </div>
            
            <!-- Contenido Principal: Título y Cuerpo del post -->
            <div class="w-100 mb-2">
                ${tituloHtml}
                <p class="card-text text-dark mb-0" style="white-space: pre-line; word-break: break-word;">${contenido}</p>
            </div>

            <!-- Imagen (si existe) ocupando el ancho completo -->
            ${multimediaHtml}
        </div>
    `;

    return card;
}
// POST /api/publicaciones (Crear) o PUT /api/publicaciones/{id} (Editar)
async function crearPublicacion(e) {
    e.preventDefault();

    const form = document.getElementById('formCrearPublicacion');
    const inputTitulo = document.getElementById('tituloPublicacion');
    const textareaContenido = document.getElementById('contenidoPublicacion');
    const inputMultimedia = document.getElementById('multimediaPublicacion');
    const inputFecha = document.getElementById('fechaProgramada');
    
    // Referencias al checkbox y contenedor de eliminación
    const checkEliminar = document.getElementById('checkEliminarImagen');
    const contenedorEliminar = document.getElementById('contenedorEliminarImagen');
    const btn = document.getElementById('btnPublicar');

    const titulo = inputTitulo.value.trim();
    const contenido = textareaContenido.value.trim();

    if (!titulo || !contenido) return;

    const editId = form.dataset.editId;
    const url = editId ? `/api/publicaciones/${editId}` : '/api/publicaciones';
    
    // Crear objeto FormData para enviar datos + archivo
    const formData = new FormData();
    formData.append('titulo', titulo);
    formData.append('contenido', contenido);

    // Si editamos, enviamos el metodo PUT simulado
    if (editId) {
        formData.append('_method', 'PUT');

        if (checkEliminar && checkEliminar.checked) {
            formData.append('eliminar_multimedia', '1');
        }
    }

    // Adjuntar archivo multimedia si fue seleccionado
    if (inputMultimedia && inputMultimedia.files[0]) {
        formData.append('multimedia', inputMultimedia.files[0]);
    }

    // Adjuntar fecha programada si fue seleccionada
    if (inputFecha && inputFecha.value) {
        formData.append('published_at', inputFecha.value);
    }

    // Adjuntar ID del usuario logueado
    try {
        const usuarioStorage = localStorage.getItem('usuario');
        if (usuarioStorage) {
            const userObj = JSON.parse(usuarioStorage);
            const idUser = userObj.idUsuario || userObj.id;
            if (idUser) formData.append('idUsuario', idUser);
        }
    } catch (e) {}

    btn.disabled = true;
    btn.textContent = editId ? 'Guardando...' : 'Publicando...';

    try {
        const token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
                // NUNCA colocar 'Content-Type' aquí cuando se usa FormData
            },
            body: formData
        });
        const responseData = await response.json();

        if (response.ok && responseData.success) {
            const fechaProgramada = inputFecha ? inputFecha.value : null;

            // Limpiar campos del formulario
            inputTitulo.value = '';
            textareaContenido.value = '';
            if (inputMultimedia) inputMultimedia.value = '';
            if (inputFecha) inputFecha.value = '';

            // Limpiar y ocultar el checkbox de eliminar imagen
            if (checkEliminar) checkEliminar.checked = false;
            if (contenedorEliminar) contenedorEliminar.classList.add('d-none');

            delete form.dataset.editId;
            btn.textContent = 'Publicar';

            // Notificar si fue programada para el futuro
            if (fechaProgramada && new Date(fechaProgramada) > new Date()) {
                alert('Publicación programada con éxito. Se mostrará en el feed en la fecha seleccionada.');
            }

            // Recargar el feed
            cargarPublicaciones();
        } else {
            alert(responseData.message || responseData.error || 'Error al procesar la publicación');
        }
    } catch (error) {
        alert('Error de conexión con el servidor: ' + error.message);
    } finally {
        btn.disabled = false;
        if (!form.dataset.editId) {
            btn.textContent = 'Publicar';
        }
    }
}

// DELETE /api/publicaciones/{id}
async function eliminarPublicacion(idPublicacion, elementoBoton) {
    if (!confirm('¿Estás seguro de que deseas eliminar esta publicación?')) return;

    try {
        const response = await fetch(`/api/publicaciones/${idPublicacion}`, getFetchOptions('DELETE'));
        const data = await response.json();

        if (response.ok) {
            const card = elementoBoton.closest('.card');
            if (card) card.remove();
        } else {
            alert(data.message || 'No se pudo eliminar la publicación.');
        }
    } catch (error) {
        alert('Error al conectar con el servidor: ' + error.message);
    }
}

// Cargar datos en el formulario para editar
function prepararEdicion(idPublicacion, tituloEnc, contenidoEnc, tieneMultimedia = 0, fechaProgramada = '') {
    const titEnc = encodeURIComponent(titulo).replace(/'/g, "%27");
    const contEnc = encodeURIComponent(contenido).replace(/'/g, "%27");
    const checkEliminar = document.getElementById('checkEliminarImagen');
    const contenedorEliminar = document.getElementById('contenedorEliminarImagen');

    if (checkEliminar) checkEliminar.checked = false;

    // Si el post tiene imagen, mostramos la opción de eliminarla
    if (tieneMultimedia === 1 || tieneMultimedia === true) {
        if (contenedorEliminar) contenedorEliminar.classList.remove('d-none');
    } else {
        if (contenedorEliminar) contenedorEliminar.classList.add('d-none');
    }
    
    document.getElementById('tituloPublicacion').value = titulo;
    document.getElementById('contenidoPublicacion').value = contenido;
    
    const inputFecha = document.getElementById('fechaProgramada');
    if (inputFecha && fechaProgramada) {
        // Convertir formato si viene en ISO string
        inputFecha.value = fechaProgramada.slice(0, 16);
    }

    const btn = document.getElementById('btnPublicar');
    btn.textContent = 'Guardar Cambios';
    
    const form = document.getElementById('formCrearPublicacion');
    form.dataset.editId = idPublicacion;
    
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function cerrarSesion() {
    const token = localStorage.getItem('auth_token');

    if (token) {
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
        } catch (e) {
            console.warn('Error al notificar logout al servidor:', e);
        }
    }

    localStorage.clear();
    sessionStorage.clear();
    window.location.replace('/login');
}

    // Helper de Fetch para enviar FormData con archivos e imagen
    function getFetchFormDataOptions(method = 'POST', formData = null) {
        const token = localStorage.getItem('auth_token');

        const headers = {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        };

        if (token && token !== 'null' && token !== 'undefined') {
            headers['Authorization'] = 'Bearer ' + token;
        }

        return {
            method: method,
            headers: headers,
            body: formData
    };
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>