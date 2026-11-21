// Función unificada y segura para obtener el token sin importar la llave
function getToken() {
    return localStorage.getItem('auth_token') || 
           localStorage.getItem('token') || 
           localStorage.getItem('access_token') || 
           sessionStorage.getItem('token');
}

// Helper para verificar si un objeto o arreglo tiene datos reales desde la BD
function tieneDatos(obj) {
    if (!obj) return false;
    if (Array.isArray(obj)) return obj.length > 0;
    if (typeof obj === 'object') return Object.keys(obj).length > 0;
    return Boolean(obj);
}

// Helper para resolver la URL de la imagen desde BD o storage
function resolverUrlAvatar(rawPath) {
    if (!rawPath || typeof rawPath !== 'string') return '';
    const limpio = rawPath.trim();
    if (!limpio) return '';
    if (/^https?:\/\//i.test(limpio) || limpio.startsWith('/')) {
        return limpio;
    }
    return `/storage/${limpio}`;
}

// Helper para renderizar el avatar con fallback automático a la inicial
function renderizarAvatarPerfil(avatarEl, rawPath, textoFallback, altTexto) {
    if (!avatarEl) return;

    const mostrarInicial = () => {
        avatarEl.innerHTML = '';
        avatarEl.classList.add('bg-primary', 'text-white');
        avatarEl.textContent = (textoFallback || 'U').trim().charAt(0).toUpperCase() || 'U';
    };

    const url = resolverUrlAvatar(rawPath);
    if (!url) {
        mostrarInicial();
        return;
    }

    avatarEl.classList.remove('bg-primary');
    avatarEl.innerHTML = `<img src="${url}" alt="${altTexto || 'Avatar'}" class="rounded-circle w-100 h-100" style="object-fit: cover;">`;

    const img = avatarEl.querySelector('img');
    if (img) {
        img.addEventListener('error', mostrarInicial);
    }
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

    // 1. Detectar ID de la URL si se visita un perfil ajeno (ej: /usuarios/5/perfil)
    const pathSegments = window.location.pathname.split('/').filter(Boolean);
    const perfilIndex = pathSegments.indexOf('perfil');
    let userIdUrl = null;

    if (perfilIndex > 0) {
        userIdUrl = pathSegments[perfilIndex - 1];
    }

    // 2. Endpoint dinámico respaldado por la consulta a la BD en Laravel
    const endpoint = userIdUrl && !isNaN(userIdUrl)
        ? `/api/usuarios/${userIdUrl}/perfil`
        : '/api/perfil';

    try {
        const response = await fetch(endpoint, {
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
            throw new Error('No se pudo obtener la información de este perfil desde la base de datos.');
        }

        const res = await response.json();

        // Extraer registro principal y relaciones devueltas por Laravel
        const baseData = res.usuario || res.data || res;
        const alumno = tieneDatos(baseData.alumno)
            ? baseData.alumno
            : (tieneDatos(res.alumno) ? res.alumno : {});
        const empresa = tieneDatos(baseData.empresa)
            ? baseData.empresa
            : (tieneDatos(res.empresa) ? res.empresa : {});

        // ID real obtenido directamente del registro de la BD (con respaldo al parámetro de URL)
        const idPerfilBD =
            baseData.idUsuario ??
            baseData.id_usuario ??
            baseData.id ??
            empresa.idUsuario ??
            empresa.id_usuario ??
            alumno.idUsuario ??
            alumno.id_usuario ??
            userIdUrl ??
            '';

        // Rol obtenido directamente de la relación/campos del usuario en la BD
        const rolDesdeBD =
            baseData.rol?.nombre ||
            baseData.rol?.nombreRol ||
            baseData.rol?.descripcion ||
            baseData.nombreRol ||
            baseData.nombre_rol ||
            baseData.tipo ||
            baseData.tipo_usuario ||
            '';

        const rolNormalizado = String(rolDesdeBD).toLowerCase().trim();

        const tieneCamposEmpresa = Boolean(
            empresa.nombreEmpresa ||
            empresa.nombre_empresa ||
            empresa.razonSocial ||
            empresa.razon_social ||
            empresa.rfc ||
            empresa.paginaWeb ||
            empresa.sitioWeb ||
            baseData.nombreEmpresa ||
            baseData.nombre_empresa
        );

        const tieneDatosAlumno = Boolean(
            alumno.nombre ||
            alumno.apellidoPaterno ||
            alumno.matricula ||
            alumno.carrera ||
            alumno.semestre
        );

        // Determinar si es empresa según relación/rol/campos desde BD
        const esEmpresa =
            rolNormalizado.includes('empresa') ||
            tieneDatos(empresa) ||
            tieneCamposEmpresa ||
            (!tieneDatosAlumno && Boolean(baseData.empresa));

        // Si el usuario autenticado es empresa y entra a /perfil sin ID, redirigir a su vista de empresa
        if (!userIdUrl && esEmpresa) {
            window.location.replace('/perfil/empresa');
            return;
        }

        // --- NOMBRES Y ROL DESDE LA BD ---
        let nombre = '';
        let paterno = '';
        let materno = '';

        if (esEmpresa) {
            nombre =
                empresa.nombreEmpresa ||
                empresa.nombre_empresa ||
                empresa.razonSocial ||
                empresa.razon_social ||
                empresa.nombre ||
                baseData.nombreEmpresa ||
                baseData.nombre_empresa ||
                baseData.nombre ||
                '';
        } else {
            nombre = alumno.nombre || baseData.nombre || '';
            paterno = alumno.apellidoPaterno || alumno.apellido_paterno || baseData.apellidoPaterno || '';
            materno = alumno.apellidoMaterno || alumno.apellido_materno || baseData.apellidoMaterno || '';
        }

        const rolTexto = rolDesdeBD || (esEmpresa ? 'Empresa' : 'Alumno');
        const nombreCompleto = esEmpresa
            ? nombre.trim()
            : `${nombre} ${paterno} ${materno}`.trim();
        const nombreNav = esEmpresa
            ? nombre.trim()
            : `${nombre} ${paterno}`.trim();

        document.getElementById('profileNombreCompleto').textContent = nombreCompleto || 'Sin nombre registrado';
        const navUser = document.getElementById('navUserName');
        if (navUser && nombreNav) {
            navUser.textContent = nombreNav;
        }

        // --- AVATAR / FOTO DE PERFIL DESDE LA BD ---
        const avatarPath = esEmpresa
            ? (empresa.avatar || empresa.logo || baseData.avatar)
            : (alumno.avatar || alumno.foto || alumno.foto_perfil || baseData.avatar || res.avatar);

        renderizarAvatarPerfil(
            document.getElementById('profileAvatar'),
            avatarPath,
            nombre || rolTexto || 'U',
            nombreCompleto || 'Perfil de usuario'
        );

        // --- INFORMACIÓN GENERAL Y CONTACTO DESDE LA BD ---
        document.getElementById('profileEmail').textContent =
            baseData.email || baseData.correo || alumno.correo || empresa.correo || empresa.email || 'No especificado';

        document.getElementById('profileTelefono').textContent =
            empresa.telefono || alumno.telefono || baseData.telefono || 'No especificado';

        document.getElementById('profileSobreMi').textContent = esEmpresa
            ? (empresa.descripcion || empresa.sobreMi || empresa.sobre_mi || baseData.descripcion || 'Sin información disponible.')
            : (alumno.sobreMi || alumno.sobre_mi || baseData.sobreMi || baseData.sobre_mi || 'Sin información disponible.');

        // --- ETIQUETA DE ROL ---
        const badgeRol = document.getElementById('profileRol');
        if (badgeRol) {
            badgeRol.textContent = rolTexto;
            badgeRol.className = 'badge bg-secondary mt-2 fs-6';
        }

        // --- SECCIONES EXCLUSIVAS DE ALUMNO VS EMPRESA ---
        const seccionHabilidades = document.getElementById('seccionHabilidades');
        const seccionDocumentacion = document.getElementById('seccionDocumentacion');
        const tituloSeccionPrincipal = document.getElementById('tituloSeccionPrincipal');
        const labelCampo1 = document.getElementById('labelCampo1');
        const labelCampo2 = document.getElementById('labelCampo2');
        const labelSobreMi = document.getElementById('labelSobreMi');
        const carreraEl = document.getElementById('profileCarrera');
        const semestreEl = document.getElementById('profileSemestre');

        if (esEmpresa) {
            if (seccionHabilidades) seccionHabilidades.classList.add('d-none');
            if (seccionDocumentacion) seccionDocumentacion.classList.add('d-none');

            if (tituloSeccionPrincipal) {
                tituloSeccionPrincipal.innerHTML = '<i class="bi bi-building me-1"></i> Información Corporativa';
            }
            if (labelCampo1) labelCampo1.textContent = 'Industria / Sector';
            if (labelCampo2) labelCampo2.textContent = 'Página Web';
            if (labelSobreMi) labelSobreMi.innerHTML = 'Sobre Mí /<br>Descripción';

            if (carreraEl) {
                let nombreIndustria = 'No especificada';
                const industriaData = empresa.industria || empresa.sector || baseData.industria;
                if (industriaData) {
                    nombreIndustria = typeof industriaData === 'object'
                        ? (industriaData.nombre || industriaData.industria || industriaData.descripcion || 'No especificada')
                        : industriaData;
                }
                carreraEl.textContent = nombreIndustria;
            }

            if (semestreEl) {
                const web = empresa.paginaWeb || empresa.pagina_web || empresa.sitioWeb || empresa.sitio_web || empresa.web || '';
                if (web) {
                    const urlSegura = /^https?:\/\//i.test(web) ? web : `https://${web}`;
                    semestreEl.innerHTML = `<a href="${urlSegura}" target="_blank" rel="noopener noreferrer">${web}</a>`;
                } else {
                    semestreEl.textContent = 'No especificada';
                }
            }
        } else {
            if (seccionHabilidades) seccionHabilidades.classList.remove('d-none');
            if (seccionDocumentacion) seccionDocumentacion.classList.remove('d-none');

            if (tituloSeccionPrincipal) {
                tituloSeccionPrincipal.innerHTML = '<i class="bi bi-mortarboard-fill me-1"></i> Información Académica';
            }
            if (labelCampo1) labelCampo1.textContent = 'Carrera / Programa';
            if (labelCampo2) labelCampo2.textContent = 'Semestre / Año';
            if (labelSobreMi) labelSobreMi.textContent = 'Sobre Mí';

            let nombreCarrera = 'No especificada';
            if (alumno.carrera) {
                if (typeof alumno.carrera === 'object') {
                    nombreCarrera = alumno.carrera.nombre || alumno.carrera.carrera || alumno.carrera.descripcion || 'Sin nombre de carrera';
                } else if (typeof alumno.carrera === 'string') {
                    nombreCarrera = alumno.carrera;
                }
            }
            if (carreraEl) carreraEl.textContent = nombreCarrera;
            if (semestreEl) semestreEl.textContent = alumno.semestre ? `${alumno.semestre}° Semestre` : 'No especificado';

            // Habilidades
            renderizarHabilidadesPerfil(alumno.habilidades || baseData.habilidades || alumno.alumno_habilidades);

            // Currículum
            const curriculumPath = alumno.curriculum || 
                                   baseData.curriculum || 
                                   alumno.curriculum_vitae || 
                                   alumno.ruta_cv;

            const curriculumContainer = document.getElementById('profileCurriculumContainer');
            if (curriculumContainer) {
                if (curriculumPath) {
                    curriculumContainer.innerHTML = `
                        <button id="btnVerCv" class="btn btn-outline-primary btn-sm fw-semibold">
                            <i class="bi bi-file-earmark-pdf"></i> Ver / Descargar CV
                        </button>
                    `;

                    document.getElementById('btnVerCv').addEventListener('click', async () => {
                        try {
                            const resPdf = await fetch(
                                idPerfilBD ? `/api/usuarios/${idPerfilBD}/curriculum` : '/api/alumnos/curriculum',
                                {
                                    method: 'GET',
                                    headers: {
                                        'Authorization': `Bearer ${token}`
                                    }
                                }
                            );

                            if (!resPdf.ok) {
                                throw new Error('No se pudo cargar el archivo PDF.');
                            }

                            const blob = await resPdf.blob();
                            const blobUrl = window.URL.createObjectURL(blob);
                            window.open(blobUrl, '_blank');

                        } catch (err) {
                            alert(err.message || 'No hay acceso al documento de este usuario.');
                        }
                    });
                } else {
                    curriculumContainer.innerHTML = `
                        <span class="text-muted"><i class="bi bi-exclamation-circle"></i> Sin documento adjunto</span>
                    `;
                }
            }
        }

        // --- BOTÓN DE MENSAJE DIRECTO USANDO EL ID OBTENIDO DE LA BD ---
        const containerBtnMensaje = document.getElementById('containerBtnMensaje');
        const btnEnviarMensaje = document.getElementById('btnEnviarMensaje');

        let miId = null;
        try {
            const localUser = JSON.parse(localStorage.getItem('usuario') || '{}');
            miId = String(localUser.idUsuario || localUser.id_usuario || localUser.id || '');
        } catch (e) {}

        const esPerfilAjeno = Boolean(idPerfilBD) && String(idPerfilBD) !== miId;

        if (esPerfilAjeno && containerBtnMensaje && btnEnviarMensaje) {
            btnEnviarMensaje.href = `/chat?destinatario=${encodeURIComponent(idPerfilBD)}`;
            containerBtnMensaje.classList.remove('d-none');
        } else if (containerBtnMensaje) {
            containerBtnMensaje.classList.add('d-none');
        }

        if (loader) loader.classList.add('d-none');
        if (cardPerfil) cardPerfil.classList.remove('d-none');

    } catch (error) {
        console.error("Error al cargar perfil:", error);
        if (loader) loader.classList.add('d-none');
        if (alertBox) {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = error.message || 'No se pudo obtener la información de este perfil.';
            alertBox.classList.remove('d-none');
        }
    }
});

// Función para renderizar dinámicamente las habilidades obtenidas de la base de datos
function renderizarHabilidadesPerfil(habilidades) {
    const container = document.getElementById('profileHabilidades');
    if (!container) return;
    
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