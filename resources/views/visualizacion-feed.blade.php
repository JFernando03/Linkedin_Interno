<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feed | linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="/feed">linkedinFIC</a>
            
            <div class="d-flex align-items-center gap-3">
                <span class="text-white fw-semibold d-none d-sm-inline" id="navUserName">Cargando...</span>
                
                <a href="/feed" class="btn btn-light btn-sm text-primary fw-bold">
                    <i class="bi bi-house-door-fill me-1"></i> Inicio (Feed)
                </a>

                <a href="/perfil" class="btn btn-outline-light btn-sm fw-semibold">
                    <i class="bi bi-person-fill me-1"></i> Mi Perfil
                </a>

                <button class="btn btn-outline-light btn-sm" id="btnLogout">Cerrar Sesión</button>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <div class="container py-4" style="max-width: 680px;">
        
        <!-- Formulario de Publicación -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form id="formCrearPublicacion">
                    <!-- Campo Título -->
                    <div class="mb-2">
                        <input type="text" id="tituloPublicacion" class="form-control border-0 bg-light fw-bold" placeholder="Título de la publicación..." required>
                    </div>
                    
                    <!-- Campo Contenido -->
                    <div class="d-flex align-items-start mb-3">
                        <textarea id="contenidoPublicacion" class="form-control border-0 bg-light" rows="3" placeholder="¿Qué quieres compartir hoy?" style="resize: none;" required></textarea>
                    </div>
                    
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <small class="text-muted">En que estas pensando...</small>
                        <button type="submit" id="btnPublicar" class="btn btn-primary btn-sm px-4 rounded-pill">Publicar</button>
                    </div>
                </form>
            </div>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    obtenerUsuarioActual();
    cargarPublicaciones();

    const form = document.getElementById('formCrearPublicacion');
    if (form) {
        form.addEventListener('submit', crearPublicacion);
    }
});

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

// Cargar Nombre de Usuario en el Navbar desde localStorage
function obtenerUsuarioActual() {
    try {
        const usuarioStorage = localStorage.getItem('usuario');
        const navUser = document.getElementById('navUserName');

        if (usuarioStorage && navUser) {
            const usuario = JSON.parse(usuarioStorage);

            // 1. Buscar nombre en el objeto alumno si existe
            let nombreCompleto = '';
            if (usuario.alumno) {
                nombreCompleto = `${usuario.alumno.nombre || ''} ${usuario.alumno.apellidoPaterno || usuario.alumno.primerApellido || ''}`.trim();
            }

            // 2. Si no viene en alumno, buscar directo en el objeto usuario
            if (!nombreCompleto) {
                nombreCompleto = `${usuario.nombre || usuario.name || ''} ${usuario.apellidoPaterno || usuario.apellido || ''}`.trim();
            }

            // 3. Asignar el nombre o un fallback si está vacío
            navUser.textContent = nombreCompleto || 'Alumno';
        }
    } catch (e) {
        console.warn('Error leyendo usuario de localStorage', e);
    }
}

// GET /api/publicaciones
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

        // Extraer el arreglo real dentro de la paginación de Laravel (publicaciones.data)
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
            contenedor.innerHTML = '<div class="card border-0 shadow-sm p-4 text-center text-muted">Aún no hay publicaciones en la plataforma. ¡Sé el primero en compartir algo!</div>';
            return;
        }

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

    // Obtener Nombre del Usuario / Alumno desde la relación del controlador (usuario.alumno)
    let nombreUsuario = 'Usuario';
    const usuarioObj = post.usuario || post.user;

    if (usuarioObj) {
        if (usuarioObj.alumno) {
            nombreUsuario = `${usuarioObj.alumno.nombre || ''} ${usuarioObj.alumno.apellidoPaterno || usuarioObj.alumno.primerApellido || ''}`.trim();
        } 
        if (!nombreUsuario || nombreUsuario === 'Usuario') {
            nombreUsuario = usuarioObj.nombre || usuarioObj.name || usuarioObj.email || 'Usuario';
        }
    } else {
        try {
            const localUser = JSON.parse(localStorage.getItem('usuario'));
            if (localUser) nombreUsuario = localUser.nombre || localUser.name || localUser.email || 'Usuario';
        } catch(e) {}
    }

    const inicial = nombreUsuario.charAt(0).toUpperCase();

    // Extraer campos de la BD
    const titulo = post.titulo || post.title || '';
    const contenido = post.contenido || post.content || '';
    const fechaOrigen = post.published_at || post.created_at;
    const fecha = fechaOrigen ? new Date(fechaOrigen).toLocaleDateString() : 'Reciente';

    card.innerHTML = `
        <div class="card-body">
            <div class="d-flex align-items-center mb-3">
                <div class="avatar-circle me-3 bg-primary text-white fw-bold d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: 50%;">
                    ${inicial}
                </div>
                <div>
                    <h6 class="mb-0 fw-bold">${nombreUsuario}</h6>
                    <small class="text-muted">${fecha}</small>
                </div>
            </div>
            
            ${titulo ? `<h5 class="fw-bold mb-2">${titulo}</h5>` : ''}
            <p class="card-text text-dark" style="white-space: pre-line;">${contenido}</p>
            ${post.multimedia ? `<img src="/storage/${post.multimedia}" class="img-fluid rounded mt-2 mb-2" alt="Imagen del post">` : ''}
        </div>
    `;

    return card;
}

// POST /api/publicaciones -> Crear publicación
async function crearPublicacion(e) {
    e.preventDefault();

    const inputTitulo = document.getElementById('tituloPublicacion');
    const textareaContenido = document.getElementById('contenidoPublicacion');
    const btn = document.getElementById('btnPublicar');

    const titulo = inputTitulo.value.trim();
    const contenido = textareaContenido.value.trim();

    if (!titulo || !contenido) return;

    let idUsuario = null;
    try {
        const usuarioStorage = localStorage.getItem('usuario');
        if (usuarioStorage) {
            const userObj = JSON.parse(usuarioStorage);
            idUsuario = userObj.idUsuario || userObj.id;
        }
    } catch (e) {}

    btn.disabled = true;
    btn.textContent = 'Publicando...';

    const payload = { 
        titulo: titulo,
        contenido: contenido
    };

    if (idUsuario) {
        payload.idUsuario = idUsuario;
    }

    try {
        const response = await fetch('/api/publicaciones', getFetchOptions('POST', payload));
        const responseData = await response.json();

        if (response.ok) {
            inputTitulo.value = '';
            textareaContenido.value = '';
            
            const contenedor = document.getElementById('contenedorFeed');
            if (contenedor.querySelector('.text-muted') || contenedor.querySelector('.alert')) {
                contenedor.innerHTML = '';
            }

            const postCreado = responseData.publicacion || responseData.data || responseData;
            
            if (!postCreado.contenido) postCreado.contenido = contenido;
            if (!postCreado.titulo) postCreado.titulo = titulo;

            const nuevoPostCard = crearCardPublicacion(postCreado);
            contenedor.insertBefore(nuevoPostCard, contenedor.firstChild);
        } else {
            alert(responseData.message || 'Error al realizar la publicación');
        }
    } catch (error) {
        alert('Error de conexión con la API: ' + error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = 'Publicar';
    }
}
</script>

</body>
</html>