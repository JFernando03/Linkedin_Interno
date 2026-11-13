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



    // 1. Detectar automáticamente si estamos viendo un perfil ajeno desde el directorio (ej: /usuarios/5/perfil)

    const pathSegments = window.location.pathname.split('/');

    const perfilIndex = pathSegments.indexOf('perfil');

    let userId = null;



    if (perfilIndex > 0) {

        userId = pathSegments[perfilIndex - 1];

    }



    // 2. Definir el endpoint dinámico: si hay ID en la URL usa la ruta pública, si no, usa el perfil propio

    const endpoint = userId && !isNaN(userId) ? `/api/usuarios/${userId}/perfil` : '/api/perfil';



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

       

        // Extraer los datos reales devueltos por la API de Laravel

        const baseData = res.usuario || res.data || res;

        const alumno = baseData.alumno || {};

        const empresa = baseData.empresa || {};



        // 3. Determinar si el perfil es de una empresa o de un alumno (basado en tu lógica de login)

        const esEmpresa = baseData.idRol == 2 || baseData.id_rol == 2 || Boolean(baseData.empresa) || Object.keys(empresa).length > 0;



        // Si el usuario autenticado es empresa y está en su propio perfil raíz, redirigir a su vista

        if (!userId && esEmpresa) {

            window.location.replace('/perfil/empresa');

            return;

        }



        // --- NOMBRES Y DATOS GENERALES ---

        let nombre = '';

        let paterno = '';

        let materno = '';

        let rolTexto = '';



        if (esEmpresa) {

            nombre = empresa.nombreEmpresa || empresa.nombre || baseData.nombre || 'Empresa';

            rolTexto = 'Empresa';

        } else {

            nombre = alumno.nombre || baseData.nombre || 'Alumno';

            paterno = alumno.apellidoPaterno || baseData.apellidoPaterno || '';

            materno = alumno.apellidoMaterno || baseData.apellidoMaterno || '';

            rolTexto = baseData.rol?.nombre || 'Alumno';

        }



        const nombreCompleto = `${nombre} ${paterno} ${materno}`.trim();

        const nombreNav = `${nombre} ${paterno}`.trim();



        document.getElementById('profileNombreCompleto').textContent = nombreCompleto || 'Miembro FIC';

        const navUser = document.getElementById('navUserName');

        if (navUser) {

            navUser.textContent = nombreNav || 'Usuario';

        }

        document.getElementById('profileAvatar').textContent = nombre ? nombre.charAt(0).toUpperCase() : 'U';



        // --- INFORMACIÓN GENERAL Y CONTACTO (Directo de la BD) ---

        document.getElementById('profileEmail').textContent = baseData.email || baseData.correo || alumno.correo || empresa.correo || 'No especificado';

        document.getElementById('profileTelefono').textContent = alumno.telefono || empresa.telefono || baseData.telefono || 'No especificado';

        document.getElementById('profileSobreMi').textContent = alumno.sobreMi || alumno.sobre_mi || empresa.descripcion || baseData.sobreMi || 'Sin información disponible.';



        // --- ROL ---

        const badgeRol = document.getElementById('profileRol');

        if (badgeRol) {

            badgeRol.textContent = rolTexto;

        }



        // --- INFORMACIÓN ACADÉMICA O EMPRESARIAL SEGÚN CORRESPONDA ---

        const carreraEl = document.getElementById('profileCarrera');

        const semestreEl = document.getElementById('profileSemestre');



        if (esEmpresa) {

            if (carreraEl) carreraEl.textContent = empresa.industria?.nombre || empresa.industria || 'Sector Empresarial';

            if (semestreEl) semestreEl.textContent = 'N/A';

        } else {

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

        }



        // --- HABILIDADES TECNOLÓGICAS (Solo para alumnos) ---

        renderizarHabilidadesPerfil(alumno.habilidades || baseData.habilidades || alumno.alumno_habilidades);



        // --- CURRÍCULUM (Solo para alumnos) ---

        const curriculumPath = alumno.curriculum ||

                               baseData.curriculum ||

                               alumno.curriculum_vitae ||

                               alumno.ruta_cv;



        const curriculumContainer = document.getElementById('profileCurriculumContainer');

        if (curriculumContainer) {

            if (!esEmpresa && curriculumPath) {

                curriculumContainer.innerHTML = `

                    <button id="btnVerCv" class="btn btn-outline-primary btn-sm fw-semibold">

                        <i class="bi bi-file-earmark-pdf"></i> Ver / Descargar CV

                    </button>

                `;



                document.getElementById('btnVerCv').addEventListener('click', async () => {

                    try {

                        const resPdf = await fetch(userId ? `/api/usuarios/${userId}/curriculum` : '/api/alumnos/curriculum', {

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

                        alert(err.message || 'No hay acceso al documento de este usuario.');

                    }

                });

            } else {

                curriculumContainer.innerHTML = `

                    <span class="text-muted"><i class="bi bi-exclamation-circle"></i> ${esEmpresa ? 'No aplica para cuentas de empresa' : 'Sin documento adjunto'}</span>

                `;

            }

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