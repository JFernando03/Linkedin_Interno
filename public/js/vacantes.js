// Catálogo poblado dinámicamente desde la base de datos
let modalidadesCache = {};

function resolverUrlArchivo(rawPath) {
    if (!rawPath || typeof rawPath !== 'string') return '';
    const limpio = rawPath.trim();
    if (!limpio) return '';
    if (/^https?:\/\//i.test(limpio) || limpio.startsWith('/')) return limpio;
    return `/storage/${limpio}`;
}

function renderizarAvatarElemento(contenedor, rawPath, textoFallback, altTexto) {
    if (!contenedor) return;

    const mostrarInicial = () => {
        contenedor.innerHTML = '';
        contenedor.classList.add('bg-primary', 'text-white');
        contenedor.textContent = (textoFallback || 'U').trim().charAt(0).toUpperCase() || 'U';
    };

    const url = resolverUrlArchivo(rawPath);
    if (!url) {
        mostrarInicial();
        return;
    }

    contenedor.classList.remove('bg-primary');
    contenedor.innerHTML = `<img src="${url}" alt="${altTexto || 'Avatar'}" class="w-100 h-100" style="object-fit: cover; border-radius: 50%;">`;

    const img = contenedor.querySelector('img');
    if (img) img.addEventListener('error', mostrarInicial);
}

function tieneDatosObjeto(obj) {
    return Boolean(obj && typeof obj === 'object' && !Array.isArray(obj) && Object.keys(obj).length > 0);
}

function normalizarUsuario(raw) {
    if (!raw || typeof raw !== 'object') return {};
    return raw.usuario || raw.user || raw.data || raw;
}

// Determina explícitamente si la cuenta es de empresa
function esCuentaEmpresa(usuarioObj) {
    const u = normalizarUsuario(usuarioObj);
    if (!u || typeof u !== 'object') return false;

    if (tieneDatosObjeto(u.empresa)) return true;
    if (Number(u.idRol) === 2 || Number(u.id_rol) === 2) return true;

    const rolNombre = String(
        u.rol?.nombre ||
        u.rol?.nombreRol ||
        u.nombreRol ||
        u.tipo ||
        u.role ||
        ''
    ).toLowerCase().trim();

    return (
        rolNombre.includes('empresa') ||
        Boolean(u.nombreEmpresa || u.nombre_empresa || u.razonSocial || u.idEmpresa || u.id_empresa)
    );
}

function obtenerNombreMostrable(usuarioObj, vacante = {}) {
    const u = normalizarUsuario(usuarioObj);
    const emp = tieneDatosObjeto(vacante.empresa)
        ? vacante.empresa
        : (tieneDatosObjeto(u?.empresa) ? u.empresa : {});

    if (tieneDatosObjeto(emp)) {
        const nomEmp = emp.nombreEmpresa || emp.nombre_empresa || emp.razonSocial || emp.nombre;
        if (nomEmp) return nomEmp.trim();
    }

    if (tieneDatosObjeto(u?.alumno)) {
        const alu = u.alumno;
        const nomAlu = `${alu.nombre || ''} ${alu.apellidoPaterno || ''}`.trim();
        if (nomAlu) return nomAlu;
    }

    return (
        u?.nombreEmpresa ||
        u?.nombre ||
        u?.name ||
        u?.email ||
        'Usuario'
    ).trim();
}

function obtenerLogoEmpresa(usuarioObj, vacante = {}) {
    const u = normalizarUsuario(usuarioObj);
    return (
        vacante.empresa?.avatar ||
        vacante.empresa?.logo ||
        u?.empresa?.avatar ||
        u?.empresa?.logo ||
        u?.avatar ||
        u?.alumno?.avatar ||
        ''
    );
}

function getToken() {
    return localStorage.getItem('auth_token') ||
           localStorage.getItem('token') ||
           sessionStorage.getItem('token');
}

function getFetchOptions(method = 'GET', body = null) {
    const token = getToken();
    const headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
    };

    if (token && token !== 'null' && token !== 'undefined') {
        headers['Authorization'] = `Bearer ${token}`;
    }

    const options = { method, headers };
    if (body) options.body = JSON.stringify(body);
    return options;
}

document.addEventListener('DOMContentLoaded', async () => {
    const token = getToken();
    if (!token) {
        window.location.replace('/login');
        return;
    }

    aplicarVistaSegunTipoUsuario();
    await sincronizarUsuario();
    await cargarModalidadesDesdeBD();

    const form = document.getElementById('formCrearVacante');
    if (form) form.addEventListener('submit', guardarVacante);

    const btnCancelar = document.getElementById('btnCancelarEdicion');
    if (btnCancelar) btnCancelar.addEventListener('click', cancelarEdicion);

    cargarVacantes();
});

// Obtiene las modalidades directamente desde la base de datos vía API
async function cargarModalidadesDesdeBD() {
    const select = document.getElementById('idModalidadVacante');
    if (!select) return;

    try {
        const response = await fetch('/api/modalidades', getFetchOptions('GET'));
        if (!response.ok) throw new Error('No se pudieron cargar las modalidades');

        const data = await response.json();
        const lista = Array.isArray(data)
            ? data
            : (data.modalidades || data.data || []);

        select.innerHTML = '<option value="">Selecciona una modalidad...</option>';
        modalidadesCache = {};

        lista.forEach(mod => {
            const id = mod.idModalidad ?? mod.id;
            const nombre = mod.nombre || mod.modalidad || mod.descripcion || `Modalidad ${id}`;
            if (id !== undefined && id !== null) {
                modalidadesCache[String(id)] = nombre;
                const option = document.createElement('option');
                option.value = String(id);
                option.textContent = nombre;
                select.appendChild(option);
            }
        });
    } catch (error) {
        select.innerHTML = '<option value="">Error al cargar modalidades</option>';
        console.warn('Error cargando modalidades desde BD:', error);
    }
}

// Muestra el formulario de agregar vacante solo si es empresa; si es alumno, solo ve vacantes
function aplicarVistaSegunTipoUsuario() {
    const usuario = normalizarUsuario(JSON.parse(localStorage.getItem('usuario') || '{}'));
    const esEmpresa = esCuentaEmpresa(usuario);

    const btnPerfil = document.getElementById('navBtnPerfil');
    if (btnPerfil && esEmpresa) {
        btnPerfil.href = '/perfil/empresa';
    }

    const navUser = document.getElementById('navUserName');
    if (navUser) {
        navUser.textContent = obtenerNombreMostrable(usuario);
    }

    const cardForm = document.getElementById('cardFormVacante');
    if (cardForm) {
        if (esEmpresa) {
            cardForm.classList.remove('d-none');
        } else {
            cardForm.classList.add('d-none');
        }
    }
}

async function sincronizarUsuario() {
    const token = getToken();
    if (!token) return;

    try {
        const res = await fetch('/api/perfil', {
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json'
            }
        });
        if (res.ok) {
            const data = await res.json();
            const usuario = normalizarUsuario(data);
            localStorage.setItem('usuario', JSON.stringify(usuario));
            aplicarVistaSegunTipoUsuario();
        }
    } catch (e) {}
}

async function cargarVacantes() {
    const contenedor = document.getElementById('contenedorVacantes');
    if (!contenedor) return;

    try {
        const response = await fetch('/api/vacantes', getFetchOptions('GET'));

        if (response.status === 401) {
            window.location.replace('/login');
            return;
        }

        if (!response.ok) throw new Error('No se pudieron obtener las vacantes.');

        const data = await response.json();

        const modsEnRespuesta = data.modalidades;
        if (Array.isArray(modsEnRespuesta) && modsEnRespuesta.length > 0 && Object.keys(modalidadesCache).length === 0) {
            const select = document.getElementById('idModalidadVacante');
            if (select) select.innerHTML = '<option value="">Selecciona una modalidad...</option>';
            modsEnRespuesta.forEach(mod => {
                const id = mod.idModalidad ?? mod.id;
                const nombre = mod.nombre || mod.modalidad || `Modalidad ${id}`;
                modalidadesCache[String(id)] = nombre;
                if (select) {
                    const option = document.createElement('option');
                    option.value = String(id);
                    option.textContent = nombre;
                    select.appendChild(option);
                }
            });
        }

        const vacantes = Array.isArray(data)
            ? data
            : (data.vacantes?.data || data.vacantes || data.data || []);

        contenedor.innerHTML = '';

        if (!vacantes.length) {
            contenedor.innerHTML = '<div class="card border-0 shadow-sm p-4 text-center text-muted">No hay vacantes registradas en la base de datos.</div>';
            return;
        }

        vacantes.forEach(vacante => {
            contenedor.appendChild(crearCardVacante(vacante));
        });
    } catch (error) {
        contenedor.innerHTML = `<div class="alert alert-danger">${error.message}</div>`;
    }
}

function crearCardVacante(vacante) {
    const card = document.createElement('div');
    card.className = 'card border-0 shadow-sm mb-3';

    const localUser = normalizarUsuario(JSON.parse(localStorage.getItem('usuario') || '{}'));
    const miId = localUser.idUsuario || localUser.id;
    const soyEmpresa = esCuentaEmpresa(localUser);

    const usuarioObj = vacante.usuario || vacante.empresa?.usuario || {};
    const autorId = vacante.idUsuario ?? usuarioObj.idUsuario ?? usuarioObj.id ?? null;
    const nombreEmpresa = obtenerNombreMostrable(usuarioObj, vacante);
    const logoUrl = obtenerLogoEmpresa(usuarioObj, vacante);
    const inicial = nombreEmpresa.charAt(0).toUpperCase() || 'E';

    const puesto = vacante.puesto || 'Vacante';
    const contenido = vacante.contenido || '';
    const idMod = vacante.idModalidad ?? vacante.modalidad?.idModalidad ?? '';
    const nombreModalidad =
        vacante.modalidad?.nombre ||
        vacante.modalidad?.modalidad ||
        modalidadesCache[String(idMod)] ||
        (idMod ? `Modalidad #${idMod}` : '');

    const sueldoMin = vacante.sueldo_min;
    const sueldoMax = vacante.sueldo_max;
    let textoSueldo = '';
    if (sueldoMin !== null && sueldoMin !== undefined && sueldoMin !== '' && sueldoMax !== null && sueldoMax !== undefined && sueldoMax !== '') {
        textoSueldo = `$${Number(sueldoMin).toLocaleString()} - $${Number(sueldoMax).toLocaleString()}`;
    } else if (sueldoMin !== null && sueldoMin !== undefined && sueldoMin !== '') {
        textoSueldo = `Desde $${Number(sueldoMin).toLocaleString()}`;
    } else if (sueldoMax !== null && sueldoMax !== undefined && sueldoMax !== '') {
        textoSueldo = `Hasta $${Number(sueldoMax).toLocaleString()}`;
    }

    const fechaOrigen = vacante.published_at || vacante.created_at;
    const fecha = fechaOrigen ? new Date(fechaOrigen).toLocaleDateString() : '';
    const idVacante = vacante.idVacante;

    const esMia = soyEmpresa && Boolean(autorId && miId && String(autorId) === String(miId));

    const menuHtml = esMia
        ? `
            <div class="dropdown">
                <button class="btn btn-link text-muted p-0 text-decoration-none fw-bold fs-5" type="button" data-bs-toggle="dropdown">⋮</button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    <li><a class="dropdown-item text-warning" href="#" onclick='editarVacante(${JSON.stringify(vacante)})'>✏️ Editar</a></li>
                    <li><a class="dropdown-item text-danger" href="#" onclick="eliminarVacante(${idVacante}, this)">🗑️ Eliminar</a></li>
                </ul>
            </div>
        `
        : '';

    const badges = [
        `<span class="badge bg-secondary"><i class="bi bi-building me-1"></i>Empresa</span>`,
        nombreModalidad ? `<span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle">${nombreModalidad}</span>` : '',
        textoSueldo ? `<span class="badge bg-success bg-opacity-10 text-success border border-success-subtle"><i class="bi bi-cash-stack me-1"></i>${textoSueldo}</span>` : ''
    ].filter(Boolean).join(' ');

    const autorLink = autorId
        ? `<a href="/usuarios/${autorId}/perfil" class="fw-bold text-dark text-decoration-none">${nombreEmpresa}</a>`
        : `<span class="fw-bold text-dark">${nombreEmpresa}</span>`;

    const multimediaUrl = resolverUrlArchivo(vacante.multimedia);
    const multimediaHtml = multimediaUrl
        ? `<div class="mt-3 text-center">
             <img src="${multimediaUrl}" class="img-fluid rounded shadow-sm w-100" style="max-height: 400px; object-fit: cover;" alt="Imagen de la vacante">
           </div>`
        : '';

    card.innerHTML = `
        <div class="card-body p-3">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div class="d-flex align-items-center">
                    <div class="avatar-circle me-3 bg-primary text-white fw-bold d-flex align-items-center justify-content-center flex-shrink-0 overflow-hidden" style="width: 42px; height: 42px; border-radius: 50%;">
                        ${inicial}
                    </div>
                    <div>
                        <div>${autorLink}</div>
                        <small class="text-muted">${fecha}</small>
                    </div>
                </div>
                ${menuHtml}
            </div>

            <h5 class="fw-bold mb-2 text-dark">${puesto}</h5>
            <div class="d-flex flex-wrap gap-1 mb-2">${badges}</div>
            <p class="card-text text-dark mb-0" style="white-space: pre-line; word-break: break-word;">${contenido}</p>
            ${multimediaHtml}
        </div>
    `;

    renderizarAvatarElemento(card.querySelector('.avatar-circle'), logoUrl, nombreEmpresa, nombreEmpresa);
    return card;
}

async function guardarVacante(e) {
    e.preventDefault();

    const usuario = normalizarUsuario(JSON.parse(localStorage.getItem('usuario') || '{}'));
    if (!esCuentaEmpresa(usuario)) {
        alert('Solo las cuentas de empresa pueden publicar vacantes.');
        return;
    }

    const form = document.getElementById('formCrearVacante');
    const btn = document.getElementById('btnPublicarVacante');
    const editId = form?.dataset.editId;

    const puesto = document.getElementById('puestoVacante').value.trim();
    const contenido = document.getElementById('contenidoVacante').value.trim();
    const idModalidad = parseInt(document.getElementById('idModalidadVacante').value, 10);
    const sueldoMin = document.getElementById('sueldoMinVacante').value;
    const sueldoMax = document.getElementById('sueldoMaxVacante').value;
    const multimediaInput = document.getElementById('multimediaVacante');
    const publishedAt = document.getElementById('publishedAtVacante').value;
    const checkEliminar = document.getElementById('checkEliminarMultimediaVacante');

    if (!puesto || !contenido || !idModalidad) {
        alert('El puesto, el contenido y la modalidad son obligatorios.');
        return;
    }

    const formData = new FormData();
    formData.append('puesto', puesto);
    formData.append('contenido', contenido);
    formData.append('idModalidad', String(idModalidad));

    if (sueldoMin !== '') formData.append('sueldo_min', String(sueldoMin));
    if (sueldoMax !== '') formData.append('sueldo_max', String(sueldoMax));
    if (publishedAt) formData.append('published_at', publishedAt);

    if (multimediaInput && multimediaInput.files[0]) {
        formData.append('multimedia', multimediaInput.files[0]);
    }

    if (editId) {
        formData.append('_method', 'PUT');
        if (checkEliminar && checkEliminar.checked) {
            formData.append('eliminar_multimedia', '1');
        }
    }

    const url = editId ? `/api/vacantes/${editId}` : '/api/vacantes';
    const token = getToken();

    btn.disabled = true;
    btn.textContent = editId ? 'Guardando...' : 'Publicando...';

    try {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${token}`,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Error al guardar la vacante.');
        }

        alert(editId ? 'Vacante actualizada correctamente.' : 'Vacante publicada con éxito.');
        cancelarEdicion();
        cargarVacantes();
    } catch (error) {
        alert(error.message);
    } finally {
        btn.disabled = false;
        btn.textContent = editId ? 'Guardar Cambios' : 'Publicar Vacante';
    }
}

function editarVacante(vacante) {
    const form = document.getElementById('formCrearVacante');
    document.getElementById('puestoVacante').value = vacante.puesto || '';
    document.getElementById('contenidoVacante').value = vacante.contenido || '';
    document.getElementById('idModalidadVacante').value = String(vacante.idModalidad ?? vacante.modalidad?.idModalidad ?? '');
    document.getElementById('sueldoMinVacante').value = vacante.sueldo_min ?? '';
    document.getElementById('sueldoMaxVacante').value = vacante.sueldo_max ?? '';

    const fechaInput = document.getElementById('publishedAtVacante');
    const fechaProg = vacante.published_at || '';
    if (fechaInput && fechaProg) {
        fechaInput.value = String(fechaProg).slice(0, 16);
    }

    const contEliminar = document.getElementById('contenedorEliminarMultimediaVacante');
    const checkEliminar = document.getElementById('checkEliminarMultimediaVacante');
    if (checkEliminar) checkEliminar.checked = false;
    if (contEliminar) contEliminar.classList.toggle('d-none', !vacante.multimedia);

    form.dataset.editId = vacante.idVacante;
    document.getElementById('tituloFormVacante').innerHTML = '<i class="bi bi-pencil-square text-warning me-1"></i> Editar Vacante';
    document.getElementById('btnPublicarVacante').textContent = 'Guardar Cambios';
    document.getElementById('btnCancelarEdicion').classList.remove('d-none');

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function cancelarEdicion() {
    const form = document.getElementById('formCrearVacante');
    form.reset();
    delete form.dataset.editId;
    document.getElementById('contenedorEliminarMultimediaVacante')?.classList.add('d-none');
    document.getElementById('tituloFormVacante').innerHTML = '<i class="bi bi-plus-circle-fill text-primary me-1"></i> Agregar Nueva Vacante';
    document.getElementById('btnPublicarVacante').textContent = 'Publicar Vacante';
    document.getElementById('btnCancelarEdicion').classList.add('d-none');
}

async function eliminarVacante(idVacante, el) {
    if (!confirm('¿Estás seguro de eliminar esta vacante?')) return;

    try {
        const res = await fetch(`/api/vacantes/${idVacante}`, getFetchOptions('DELETE'));
        if (!res.ok) throw new Error('No se pudo eliminar la vacante.');
        el.closest('.card')?.remove();
    } catch (error) {
        alert(error.message);
    }
}

async function cerrarSesion() {
    const token = getToken();
    if (token) {
        try {
            await fetch('/api/logout', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Accept': 'application/json'
                }
            });
        } catch (e) {}
    }
    localStorage.clear();
    sessionStorage.clear();
    window.location.replace('/login');
}