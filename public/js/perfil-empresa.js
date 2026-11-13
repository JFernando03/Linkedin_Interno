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

        // Si el usuario autenticado es alumno, redirigir a su vista correspondiente
        if (baseData.idRol == 1 || baseData.alumno) {
            window.location.replace('/perfil');
            return;
        }

        const empresa = baseData.empresa || res.empresa || baseData;

        // --- NOMBRE DE LA EMPRESA ---
        const nombreEmpresa = empresa.nombreEmpresa || baseData.nombreEmpresa || 'Empresa FIC';

        document.getElementById('profileNombreEmpresa').textContent = nombreEmpresa;
        const navUser = document.getElementById('navUserName');
        if (navUser) {
            navUser.textContent = nombreEmpresa;
        }

        // --- AVATAR / LOGOTIPO ---
        const avatarEl = document.getElementById('profileAvatar');
        const avatarPath = baseData.avatar || empresa.avatar;

        if (avatarPath) {
            const imgUrl = avatarPath.startsWith('http') || avatarPath.startsWith('/storage/') 
                ? avatarPath 
                : `/storage/${avatarPath}`;

            avatarEl.innerHTML = `<img src="${imgUrl}" alt="${nombreEmpresa}" class="rounded-circle w-100 h-100" style="object-fit: cover;">`;
            avatarEl.classList.remove('bg-primary');
        } else {
            avatarEl.textContent = nombreEmpresa ? nombreEmpresa.charAt(0).toUpperCase() : 'E';
        }

        // --- INFORMACIÓN GENERAL Y CONTACTO ---
        document.getElementById('profileEmail').textContent = baseData.email || empresa.email || 'No especificado';
        document.getElementById('profileTelefono').textContent = empresa.telefono || baseData.telefono || 'No especificado';
        document.getElementById('profileSobreMi').textContent = empresa.sobreMi || empresa.sobre_mi || baseData.sobreMi || 'Sin información disponible.';

        // --- INDUSTRIA ---
        let nombreIndustria = 'No especificada';
        if (empresa.industria && empresa.industria.industria) {
            nombreIndustria = empresa.industria.industria;
        } else if (empresa.industria && empresa.industria.nombre) {
            nombreIndustria = empresa.industria.nombre;
        }
        document.getElementById('profileIndustria').textContent = nombreIndustria;

        // --- PÁGINA WEB ---
        const paginaWeb = empresa.paginaWeb || empresa.pagina_web;
        const webContainer = document.getElementById('profilePaginaWebContainer');
        if (paginaWeb) {
            webContainer.innerHTML = `<a href="${paginaWeb}" target="_blank" class="text-decoration-none">${paginaWeb} <i class="bi bi-box-arrow-up-right small"></i></a>`;
        } else {
            webContainer.textContent = 'No especificada';
        }

        // --- ROL ---
        if (baseData.rol && baseData.rol.nombre) {
            document.getElementById('profileRol').textContent = baseData.rol.nombre;
        } else {
            document.getElementById('profileRol').textContent = 'Empresa';
        }

        loader.classList.add('d-none');
        cardPerfil.classList.remove('d-none');

    } catch (error) {
        console.error("Error al cargar perfil de empresa:", error);
        loader.classList.add('d-none');
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
    }
});

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