<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario - linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-4">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/feed">linkedinFIC</a>
        
        <div class="d-flex align-items-center gap-3">
            <!-- Nombre del usuario logueado en la Navbar -->
            <span class="text-white fw-semibold d-none d-sm-inline" id="navUserName">Cargando...</span>
            
            <!-- Botones de Navegación -->
            <a href="/feed" class="btn btn-outline-light btn-sm fw-semibold">
                <i class="bi bi-house-door-fill me-1"></i> Inicio (Feed)
            </a>

            <a href="/perfil" class="btn btn-light btn-sm text-primary fw-bold">
                <i class="bi bi-person-fill me-1"></i> Mi Perfil
            </a>

            <!-- Botón de Cerrar Sesión -->
            <button class="btn btn-outline-light btn-sm" id="btnLogout">Cerrar Sesión</button>
        </div>
    </div>
</nav>

<div class="container my-4">
    <div class="row justify-content-center">
        <div class="col-md-9 col-lg-8">

            <!-- Alerta de estado/error -->
            <div id="alertBox" class="alert d-none" role="alert"></div>

            <!-- Loader mientras carga la info -->
            <div id="loader" class="text-center py-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando perfil...</span>
                </div>
                <p class="mt-2 text-muted">Cargando información del perfil...</p>
            </div>

            <!-- Tarjeta con la información del perfil -->
            <div id="cardPerfil" class="card shadow-sm d-none">
                <div class="card-header bg-white py-4 border-0 text-center border-bottom position-relative">
                    
                    <!-- Botón para Editar Perfil -->
                    <a href="/perfil/editar" class="btn btn-outline-primary btn-sm position-absolute top-0 end-0 m-3">
                        <i class="bi bi-pencil-square"></i> Editar Perfil
                    </a>

                    <div class="avatar-circle mx-auto bg-primary text-white d-flex align-items-center justify-content-center rounded-circle fs-2 fw-bold mb-2 shadow-sm" style="width: 80px; height: 80px;" id="profileAvatar">
                        --
                    </div>
                    <h3 class="mb-0 fw-bold" id="profileNombreCompleto">Cargando...</h3>
                    <span class="badge bg-secondary mt-2 fs-6" id="profileRol">Rol</span>
                </div>

                <div class="card-body p-4">
                    <!-- Sección Información Académica -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-mortarboard-fill me-1"></i> Información Académica
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Carrera / Programa</div>
                        <div class="col-sm-8 fw-semibold" id="profileCarrera">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Semestre / Año</div>
                        <div class="col-sm-8 fw-semibold" id="profileSemestre">--</div>
                    </div>

                    <hr class="my-4">

                    <!-- Sección Información General y de Contacto -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-person-lines-fill me-1"></i> Información General
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Correo Electrónico</div>
                        <div class="col-sm-8 fw-semibold" id="profileEmail">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Teléfono</div>
                        <div class="col-sm-8 fw-semibold" id="profileTelefono">--</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Sobre Mí</div>
                        <div class="col-sm-8" id="profileSobreMi">--</div>
                    </div>

                    <hr class="my-4">

                    <!-- SECCIÓN HABILIDADES TECNOLÓGICAS -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-code-slash me-1"></i> Habilidades Tecnológicas
                    </h5>

                    <div class="row mb-3">
                        <div class="col-sm-4 text-muted">Mis Habilidades</div>
                        <div class="col-sm-8">
                            <div id="profileHabilidades" class="d-flex flex-wrap gap-1">
                                <span class="text-muted">Cargando habilidades...</span>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Sección Documentación -->
                    <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Documentación
                    </h5>

                    <div class="row align-items-center">
                        <div class="col-sm-4 text-muted">Currículum Vitae</div>
                        <div class="col-sm-8" id="profileCurriculumContainer">
                            <span class="text-muted">Sin documento adjunto</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
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
        const alumno = baseData.alumno || baseData.persona || baseData;

        // --- NOMBRES Y APELLIDOS ---
        const nombre = alumno.nombre || baseData.nombre || '';
        const paterno = alumno.apellidoPaterno || alumno.apellido_paterno || baseData.apellidoPaterno || '';
        const materno = alumno.apellidoMaterno || alumno.apellido_materno || baseData.apellidoMaterno || '';
        const nombreCompleto = `${nombre} ${paterno} ${materno}`.trim();

        document.getElementById('profileNombreCompleto').textContent = nombreCompleto || 'Alumno FIC';
        document.getElementById('navUserName').textContent = nombre || 'Alumno';
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
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>