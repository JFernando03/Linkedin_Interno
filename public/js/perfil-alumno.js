// Diccionario para traducir idCarrera al nombre real de la carrera
const carrerasMap = {
    1: 'Ingeniería en Software',
    2: 'Ingeniería Civil',
    3: 'Ingeniería en Procesos Industriales',
    4: 'Licenciatura en Astronomía',
};

// Función unificada y segura para obtener el token sin importar la llave
function getToken() {
    return localStorage.getItem('auth_token') || 
           localStorage.getItem('token') || 
           localStorage.getItem('access_token') || 
           sessionStorage.getItem('token');
}

document.addEventListener('DOMContentLoaded', async () => {
    const token = getToken();
    const alertBox = document.getElementById('alertBox');
    const loader = document.getElementById('loader');
    const cardPerfil = document.getElementById('cardPerfil');

    if (!token) {
        window.location.replace('/login');
        return;
    }

    try {
        const response = await fetch('/api/perfil', {
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

        if (!response.ok) {
            throw new Error('No se pudo obtener la información del perfil.');
        }

        const res = await response.json();
        
        const baseData = res.data || res.usuario || res.user || res;

        // --- NOMBRES Y APELLIDOS ---
        const alumno = res.usuario.alumno || {};

        const nombre = alumno.nombre || '';
        const paterno = alumno.apellidoPaterno || '';
        const materno = alumno.apellidoMaterno || '';

        const nombreCompleto = `${nombre} ${paterno} ${materno}`.trim();
        const nombreNav = `${nombre} ${paterno}`.trim();

        document.getElementById('profileNombreCompleto').textContent = nombreCompleto || 'Alumno FIC';
        const navUser = document.getElementById('navUserName');
        if (navUser) {
            navUser.textContent = `${nombreNav || 'Alumno'}`;
        }
        document.getElementById('profileAvatar').textContent = nombre ? nombre.charAt(0).toUpperCase() : 'A';

        // --- INFORMACIÓN GENERAL Y CONTACTO ---
        document.getElementById('profileEmail').textContent = baseData.email || alumno.email || 'No especificado';
        document.getElementById('profileTelefono').textContent = alumno.telefono || baseData.telefono || 'No especificado';
        document.getElementById('profileSobreMi').textContent = alumno.sobreMi || alumno.sobre_mi || baseData.sobreMi || 'Sin información disponible.';

        // --- HABILIDADES TECNOLÓGICAS (Renderizado dinámico) ---
        renderizarHabilidadesPerfil(alumno.habilidades || baseData.habilidades || alumno.alumno_habilidades);

        // --- OBTENCIÓN DINÁMICA DEL NOMBRE DE LA CARRERA DESDE LA BASE DE DATOS ---
        let nombreCarrera = 'No especificada';

        if (alumno.carrera) {
            // Si la relación 'carrera' viene cargada como objeto (ej. alumno.carrera.nombre o alumno.carrera.carrera)
            nombreCarrera = alumno.carrera.nombre || alumno.carrera.carrera || alumno.carrera.descripcion || 'Carrera sin nombre';
        }

        document.getElementById('profileCarrera').textContent = nombreCarrera;
        document.getElementById('profileSemestre').textContent = alumno.semestre || baseData.semestre || 'No especificado';

        // --- ROL ---
        if (baseData.rol && baseData.rol.nombre) {
            document.getElementById('profileRol').textContent = baseData.rol.nombre;
        } else {
            document.getElementById('profileRol').textContent = 'Alumno';
        }

        // --- CURRÍCULUM ---
        const curriculumPath = alumno.curriculum || 
                               baseData.curriculum || 
                               alumno.curriculum_vitae || 
                               alumno.ruta_cv;

        if (curriculumPath) {
            document.getElementById('profileCurriculumContainer').innerHTML = `
                <button id="btnVerCv" class="btn btn-outline-primary btn-sm fw-semibold">
                    <i class="bi bi-file-earmark-pdf"></i> Ver / Descargar CV
                </button>
            `;

            document.getElementById('btnVerCv').addEventListener('click', async () => {
                try {
                    const resPdf = await fetch('/api/alumnos/curriculum', {
                        method: 'GET',
                        headers: {
                            'Authorization': `Bearer ${token}`
                        }
                    });

                    if (!resPdf.ok) {
                        throw new Error('No se pudo cargar el archivo PDF.');
                    }

                    const blob = await resPdf.blob();
                    const blobUrl = window.URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');

                } catch (err) {
                    alert(err.message);
                }
            });
        } else {
            document.getElementById('profileCurriculumContainer').innerHTML = `
                <span class="text-muted"><i class="bi bi-exclamation-circle"></i> Sin documento adjunto</span>
            `;
        }

        loader.classList.add('d-none');
        cardPerfil.classList.remove('d-none');

    } catch (error) {
        loader.classList.add('d-none');
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
    }
});

// Función para renderizar dinámicamente las habilidades del alumno con nivel
function renderizarHabilidadesPerfil(habilidades) {
    const container = document.getElementById('profileHabilidades');
    
    if (!habilidades || habilidades.length === 0) {
        container.innerHTML = '<span class="text-muted">Sin habilidades registradas</span>';
        return;
    }

    const nivelesMap = { 1: 'Básico', 2: 'Intermedio', 3: 'Avanzado' };

    container.innerHTML = '';
    habilidades.forEach(item => {
        const nombreH = item.habilidad ? (item.habilidad.nombre || item.habilidad.habilidad) : (item.nombre || item.habilidad);
        const idNivel = item.pivot?.idNivel || item.idNivel || 2;
        const textoNivel = nivelesMap[idNivel] || `Nivel ${idNivel}`;

        if (nombreH) {
            const badge = document.createElement('span');
            badge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary-subtle px-2 py-1 fs-6 d-inline-flex align-items-center gap-1';
            badge.innerHTML = `<span>${nombreH}</span> <small class="text-muted" style="font-size: 0.75rem;">(${textoNivel})</small>`;
            container.appendChild(badge);
        }
    });
}

// Cerrar sesión
document.getElementById('btnLogout').addEventListener('click', async (e) => {
    e.preventDefault();
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
        } catch (e) {
            console.error('Error enviando petición de logout:', e);
        }
    }

    localStorage.clear();
    sessionStorage.clear();
    window.location.replace('/login');
});