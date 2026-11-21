// Helpers reutilizables para resolver imágenes de perfil y multimedia desde BD / storage
function resolverUrlAvatar(rawPath) {
    if (!rawPath || typeof rawPath !== 'string') return '';
    const limpio = rawPath.trim();
    if (!limpio) return '';
    if (/^https?:\/\//i.test(limpio) || limpio.startsWith('/')) {
        return limpio;
    }
    return `/storage/${limpio}`;
}

function renderizarAvatarElemento(contenedor, rawPath, textoFallback, altTexto) {
    if (!contenedor) return;

    const mostrarInicial = () => {
        contenedor.innerHTML = '';
        contenedor.classList.add('bg-primary', 'text-white');
        contenedor.textContent = (textoFallback || 'U').trim().charAt(0).toUpperCase() || 'U';
    };

    const url = resolverUrlAvatar(rawPath);
    if (!url) {
        mostrarInicial();
        return;
    }

    contenedor.classList.remove('bg-primary');
    contenedor.innerHTML = `<img src="${url}" alt="${altTexto || 'Avatar'}" class="w-100 h-100" style="object-fit: cover; border-radius: 50%;">`;

    const img = contenedor.querySelector('img');
    if (img) {
        img.addEventListener('error', mostrarInicial);
    }
}

function obtenerNombreMostrable(usuarioObj) {
    if (!usuarioObj) return 'Usuario';

    if (usuarioObj.empresa) {
        const emp = usuarioObj.empresa;
        const nomEmp = emp.nombreEmpresa || emp.nombre_empresa || emp.razonSocial || emp.nombre;
        if (nomEmp) return nomEmp.trim();
    }

    if (usuarioObj.alumno) {
        const alu = usuarioObj.alumno;
        const nomAlu = `${alu.nombre || ''} ${alu.apellidoPaterno || alu.primerApellido || ''}`.trim();
        if (nomAlu) return nomAlu;
    }

    return (
        usuarioObj.nombreEmpresa ||
        usuarioObj.nombre ||
        usuarioObj.name ||
        usuarioObj.email ||
        'Usuario'
    ).trim();
}

function obtenerRutaAvatarUsuario(usuarioObj) {
    if (!usuarioObj) return '';
    return (
        usuarioObj.avatar ||
        usuarioObj.foto ||
        usuarioObj.foto_perfil ||
        usuarioObj.empresa?.avatar ||
        usuarioObj.empresa?.logo ||
        usuarioObj.alumno?.avatar ||
        usuarioObj.alumno?.foto ||
        usuarioObj.alumno?.foto_perfil ||
        ''
    );
}

document.addEventListener('DOMContentLoaded', () => {
    const usuarioGuardado = JSON.parse(localStorage.getItem('usuario') || '{}');
    const esEmpresa = usuarioGuardado.idRol == 2 || Boolean(usuarioGuardado.empresa) || Boolean(usuarioGuardado.nombreEmpresa);
    const btnPerfil = document.getElementById('navBtnPerfil');
    if (btnPerfil && esEmpresa) {
        btnPerfil.href = '/perfil/empresa';
    }
});

document.addEventListener('DOMContentLoaded', async function () {
    const formCrear = document.getElementById('formCrearPublicacion');
    if (formCrear) {
        formCrear.addEventListener('submit', crearPublicacion);
    }

    obtenerUsuarioActual();
    await actualizarUsuarioStorage();
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
            localStorage.setItem('usuario', JSON.stringify(resJson.usuario));
            obtenerUsuarioActual();
        }
    } catch (e) {
        console.warn('Error al sincronizar perfil en Feed:', e);
    }
}

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

function obtenerUsuarioActual() {
    const usuarioStorage = localStorage.getItem('usuario');
    const navUser = document.getElementById('navUserName');

    if (!usuarioStorage || !navUser) return;

    const usuario = JSON.parse(usuarioStorage);
    navUser.textContent = obtenerNombreMostrable(usuario);
}

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

        try {
            const userLocal = JSON.parse(localStorage.getItem('usuario') || '{}');
            const miPost = publicaciones.find(p => (p.idUsuario || p.usuario?.idUsuario) == (userLocal.idUsuario || userLocal.id));
            if (miPost && miPost.usuario) {
                const navUser = document.getElementById('navUserName');
                const miNombre = obtenerNombreMostrable(miPost.usuario);
                if (miNombre && navUser) navUser.textContent = miNombre;
            }
        } catch (e) {}

        publicaciones.forEach(post => {
            contenedor.appendChild(crearCardPublicacion(post));
        });

    } catch (error) {
        contenedor.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

function crearCardPublicacion(post) {
    if (!post) return document.createElement('div');

    const card = document.createElement('div');
    card.className = 'card border-0 shadow-sm mb-3';

    let userLocalId = null;
    try {
        const localUser = JSON.parse(localStorage.getItem('usuario'));
        if (localUser) userLocalId = localUser.idUsuario || localUser.id;
    } catch (e) {}

    const usuarioObj = post.usuario || post.user || {};
    const autorId = post.idUsuario || post.id_usuario || usuarioObj.idUsuario || usuarioObj.id || null;
    const nombreUsuario = obtenerNombreMostrable(usuarioObj);
    const avatarAutor = obtenerRutaAvatarUsuario(usuarioObj) || post.avatar || '';
    const inicial = nombreUsuario.charAt(0).toUpperCase();

    const titulo = post.titulo || post.title || '';
    const contenido = post.contenido || post.content || '';
    const fechaOrigen = post.published_at || post.created_at;
    const fecha = fechaOrigen ? new Date(fechaOrigen).toLocaleDateString() : 'Reciente';

    const esMio = post.esMia === true || post.esMia === 1 ||
        (autorId && userLocalId && autorId == userLocalId);

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

    const tituloHtml = titulo ? `<h5 class="fw-bold mb-2 text-dark">${titulo}</h5>` : '';
    const multimediaUrl = resolverUrlAvatar(post.multimedia);
    const multimediaHtml = multimediaUrl
        ? `<div class="mt-3 text-center">
             <img src="${multimediaUrl}" class="img-fluid rounded shadow-sm w-100" style="max-height: 400px; object-fit: cover;" alt="Imagen del post">
           </div>`
        : '';

    let autorHtml = `<h6 class="mb-0 fw-bold text-dark">${nombreUsuario}</h6>`;
    if (autorId) {
        autorHtml = `<a href="/usuarios/${autorId}/perfil" class="mb-0 fw-bold text-dark text-decoration-none stretched-link-autor">${nombreUsuario}</a>`;
    }

    card.innerHTML = `
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3 bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden" style="width: 42px; height: 42px; border-radius: 50%;">
                        ${inicial}
                    </div>
                    <div>
                        ${autorHtml}
                        <small class="text-muted">${fecha}</small>
                    </div>
                </div>
                ${menuHtml}
            </div>
            
            <div class="w-100 mb-2">
                ${tituloHtml}
                <p class="card-text text-dark mb-0" style="white-space: pre-line; word-break: break-word;">${contenido}</p>
            </div>

            ${multimediaHtml}
        </div>
    `;

    const avatarContenedor = card.querySelector('.avatar-circle');
    renderizarAvatarElemento(avatarContenedor, avatarAutor, nombreUsuario, nombreUsuario);

    const linkAutor = card.querySelector('a');
    if (linkAutor) {
        linkAutor.style.position = 'relative';
        linkAutor.style.zIndex = '2';
        linkAutor.addEventListener('mouseenter', () => linkAutor.classList.add('text-primary'));
        linkAutor.addEventListener('mouseleave', () => linkAutor.classList.remove('text-primary'));
    }

    return card;
}

async function crearPublicacion(e) {
    e.preventDefault();

    const form = document.getElementById('formCrearPublicacion');
    const inputTitulo = document.getElementById('tituloPublicacion');
    const textareaContenido = document.getElementById('contenidoPublicacion');
    const inputMultimedia = document.getElementById('multimediaPublicacion');
    const inputFecha = document.getElementById('fechaProgramada');
    
    const checkEliminar = document.getElementById('checkEliminarImagen');
    const contenedorEliminar = document.getElementById('contenedorEliminarImagen');
    const btn = document.getElementById('btnPublicar');

    const titulo = inputTitulo ? inputTitulo.value.trim() : '';
    const contenido = textareaContenido ? textareaContenido.value.trim() : '';

    if (!titulo || !contenido) {
        alert('El título y el contenido son obligatorios.');
        return;
    }

    const editId = form ? form.dataset.editId : null;
    const url = editId ? `/api/publicaciones/${editId}` : '/api/publicaciones';
    
    const formData = new FormData();
    formData.append('titulo', titulo);
    formData.append('contenido', contenido);

    if (editId) {
        formData.append('_method', 'PUT');

        if (checkEliminar && checkEliminar.checked) {
            formData.append('eliminar_multimedia', '1');
        }
    }

    if (inputMultimedia && inputMultimedia.files[0]) {
        formData.append('multimedia', inputMultimedia.files[0]);
    }

    if (inputFecha && inputFecha.value) {
        formData.append('published_at', inputFecha.value);
    }

    try {
        const usuarioStorage = localStorage.getItem('usuario');
        if (usuarioStorage) {
            const userObj = JSON.parse(usuarioStorage);
            const idUser = userObj.idUsuario || userObj.id;
            if (idUser) formData.append('idUsuario', idUser);
        }
    } catch (e) {}

    if (btn) {
        btn.disabled = true;
        btn.textContent = editId ? 'Guardando...' : 'Publicando...';
    }

    try {
        const token = localStorage.getItem('auth_token') || localStorage.getItem('token') || '';

        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            },
            body: formData
        });

        const responseData = await response.json();

        if (response.ok && (responseData.success || responseData.idPublicacion || responseData.id || responseData.data)) {
            if (inputTitulo) inputTitulo.value = '';
            if (textareaContenido) textareaContenido.value = '';
            if (inputMultimedia) inputMultimedia.value = '';
            if (inputFecha) inputFecha.value = '';

            if (checkEliminar) checkEliminar.checked = false;
            if (contenedorEliminar) contenedorEliminar.classList.add('d-none');

            if (form) delete form.dataset.editId;

            alert(editId ? 'Publicación actualizada correctamente' : 'Publicación creada con éxito');

            cargarPublicaciones();
        } else {
            console.error('Error del servidor:', responseData);
            alert(responseData.message || responseData.error || 'Error al procesar la publicación');
        }
    } catch (error) {
        console.error('Error en fetch:', error);
        alert('Error de conexión con el servidor: ' + error.message);
    } finally {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Publicar';
        }
    }
}

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

function prepararEdicion(idPublicacion, tituloEnc, contenidoEnc, tieneMultimedia = 0, fechaProgramada = '') {
    const titulo = decodeURIComponent(tituloEnc);
    const contenido = decodeURIComponent(contenidoEnc);
    
    const checkEliminar = document.getElementById('checkEliminarImagen');
    const contenedorEliminar = document.getElementById('contenedorEliminarImagen');

    if (checkEliminar) checkEliminar.checked = false;

    if (tieneMultimedia === 1 || tieneMultimedia === true) {
        if (contenedorEliminar) contenedorEliminar.classList.remove('d-none');
    } else {
        if (contenedorEliminar) contenedorEliminar.classList.add('d-none');
    }
    
    const inputTitulo = document.getElementById('tituloPublicacion');
    const inputContenido = document.getElementById('contenidoPublicacion');
    const inputFecha = document.getElementById('fechaProgramada');
    
    if (inputTitulo) inputTitulo.value = titulo;
    if (inputContenido) inputContenido.value = contenido;
    
    if (inputFecha && fechaProgramada && fechaProgramada !== 'null') {
        inputFecha.value = fechaProgramada.slice(0, 16);
    }

    const btn = document.getElementById('btnPublicar');
    if (btn) btn.textContent = 'Guardar Cambios';
    
    const form = document.getElementById('formCrearPublicacion');
    if (form) {
        form.dataset.editId = idPublicacion;
    }
    
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