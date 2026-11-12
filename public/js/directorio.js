function getToken() {
    return localStorage.getItem('auth_token') || 
           localStorage.getItem('token') || 
           localStorage.getItem('access_token') || 
           sessionStorage.getItem('token');
}

let listaUsuariosGlobal = [];

document.addEventListener('DOMContentLoaded', async () => {
    const token = getToken();
    const alertBox = document.getElementById('alertBox');
    const loader = document.getElementById('loader');

    if (!token) {
        window.location.replace('/login');
        return;
    }

    const inputBuscar = document.getElementById('inputBuscar');
    if (inputBuscar) {
        inputBuscar.addEventListener('input', function () {
            filtrarUsuarios(this.value.toLowerCase());
        });
    }

    try {
        let response = null;
        let res = null;

        // Intentamos primero con el endpoint general de usuarios si tu compañero lo llega a crear
        try {
            response = await fetch('/api/usuarios', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
            if (response.ok) {
                res = await response.json();
            }
        } catch (e) {
            // Si falla la red o no existe, pasamos al respaldo del feed
        }

        // Si la ruta general no respondió, usamos el feed como respaldo seguro
        if (!res || !response.ok) {
            response = await fetch('/api/feed', {
                method: 'GET',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });

            if (response.status === 401) {
                localStorage.clear();
                sessionStorage.clear();
                window.location.replace('/login');
                return;
            }

            if (response.ok) {
                res = await response.json();
            } else {
                throw new Error('No se pudo obtener la información de la comunidad.');
            }
        }

        // Procesamos los datos sin importar de qué endpoint provengan
        let items = [];
        if (Array.isArray(res)) {
            items = res;
        } else {
            const feedContainer = res.feed || res;
            items = feedContainer.data || res.data || res.usuarios || res.publicaciones || [];
        }

        const usuariosMap = new Map();
        if (Array.isArray(items)) {
            items.forEach(item => {
                const user = item.usuario || item.user || (item.idUsuario || item.id ? item : null);
                
                if (user) {
                    const userId = user.idUsuario || user.id || user.id_usuario;
                    if (userId && !usuariosMap.has(userId)) {
                        usuariosMap.set(userId, user);
                    }
                }
            });
        }
        listaUsuariosGlobal = Array.from(usuariosMap.values());

    } catch (error) {
        console.error("Error al cargar directorio:", error);
        if (alertBox) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = 'No se pudieron cargar los usuarios. Intenta más tarde.';
            alertBox.classList.remove('d-none');
        }
    } finally {
        if (loader) loader.classList.add('d-none');
        renderizarTarjetas(listaUsuariosGlobal);
    }
});

function renderizarTarjetas(usuarios) {
    const contenedor = document.getElementById('contenedorUsuarios');
    if (!contenedor) return;
    
    contenedor.innerHTML = '';

    if (!Array.isArray(usuarios) || usuarios.length === 0) {
        contenedor.innerHTML = `
            <div class="col-12 text-center text-muted py-4">
                <p>Aún no hay perfiles disponibles en el directorio.</p>
                <small class="text-secondary">Los usuarios aparecerán aquí conforme participen en la plataforma.</small>
            </div>`;
        return;
    }

    usuarios.forEach(user => {
        const alumno = user.alumno || {};
        const empresa = user.empresa || {};
        
        const nombre = alumno.nombre || user.nombre || empresa.nombreEmpresa || 'Usuario';
        const apellido = alumno.apellidoPaterno || user.apellidoPaterno || '';
        
        const userId = user.idUsuario || user.id || user.id_usuario || alumno.idUsuario || alumno.id || empresa.idEmpresa || empresa.id;

        let rolTexto = 'Miembro';
        if (user.alumno || alumno.nombre) rolTexto = 'Alumno';
        if (user.empresa || empresa.nombreEmpresa) rolTexto = 'Empresa';

        const col = document.createElement('div');
        col.className = 'col-md-4 col-sm-6';
        
        col.innerHTML = `
            <div class="card shadow-sm border-0 h-100 rounded-3 text-center p-3">
                <div class="avatar-circle mx-auto bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fs-4 fw-bold mb-3 shadow-sm" style="width: 60px; height: 60px;">
                    ${nombre.charAt(0).toUpperCase()}
                </div>
                <h5 class="fw-bold mb-1">${nombre} ${apellido}</h5>
                <span class="badge bg-secondary mb-3 mx-auto">${rolTexto}</span>
                <a href="/usuarios/${userId}/perfil" class="btn btn-outline-primary btn-sm mt-auto rounded-pill">Ver Perfil</a>
            </div>
        `;
        contenedor.appendChild(col);
    });
}

function filtrarUsuarios(texto) {
    const filtrados = listaUsuariosGlobal.filter(user => {
        const alumno = user.alumno || {};
        const empresa = user.empresa || {};
        const nombreCompleto = `${alumno.nombre || user.nombre || empresa.nombreEmpresa || ''} ${alumno.apellidoPaterno || ''}`.toLowerCase();
        return nombreCompleto.includes(texto);
    });
    renderizarTarjetas(filtrados);
}