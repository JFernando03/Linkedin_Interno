document.addEventListener('DOMContentLoaded', function () {
    cargarIndustrias();
    cargarDatosEmpresa();

    document.getElementById('formEditarPerfilEmpresa').addEventListener('submit', guardarDatosEmpresa);
});

function getToken() {
    return localStorage.getItem('token') || 
           localStorage.getItem('access_token') || 
           localStorage.getItem('auth_token') || 
           sessionStorage.getItem('token');
}

// 1. Cargar catálogo de industrias
function cargarIndustrias() {
    const token = getToken();

    fetch('/api/industrias', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
    .then(response => response.ok ? response.json() : Promise.reject('Error al cargar industrias'))
    .then(data => {
        const industrias = data.data || data.industrias || data;
        const selectIndustria = document.getElementById('idIndustria');
        selectIndustria.innerHTML = '<option value="">Selecciona una industria</option>';

        if (Array.isArray(industrias)) {
            industrias.forEach(ind => {
                const option = document.createElement('option');
                option.value = ind.idIndustria || ind.id_industria || ind.id;
                option.textContent = ind.industria || ind.nombre;
                selectIndustria.appendChild(option);
            });
        }
    })
    .catch(error => console.error('Error catálogo industrias:', error));
}

// 2. Cargar datos actuales de la empresa
function cargarDatosEmpresa() {
    const token = getToken();
    if (!token) return;

    fetch('/api/perfil', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        }
    })
    .then(res => {
        if (!res.ok) throw new Error('No se pudo obtener la información del perfil.');
        return res.json();
    })
    .then(data => {
        const baseData = data.data || data.usuario || data.user || data;
        const empresa = baseData.empresa || baseData;
        
        if (document.getElementById('nombreEmpresa')) {
            document.getElementById('nombreEmpresa').value = empresa.nombreEmpresa || empresa.nombre_empresa || '';
        }
        if (document.getElementById('paginaWeb')) {
            document.getElementById('paginaWeb').value = empresa.paginaWeb || empresa.pagina_web || '';
        }
        if (document.getElementById('telefono')) {
            document.getElementById('telefono').value = baseData.telefono || empresa.telefono || '';
        }
        if (document.getElementById('sobreMi')) {
            document.getElementById('sobreMi').value = baseData.sobreMi || baseData.sobre_mi || empresa.sobreMi || '';
        }

        const selectIndustria = document.getElementById('idIndustria');
        const industriaVal = empresa.idIndustria || empresa.id_industria;
        if (industriaVal && selectIndustria) {
            selectIndustria.value = String(industriaVal);
        }
    })
    .catch(error => {
        console.error('Error cargando perfil de empresa:', error.message);
    });
}

// 3. Guardar cambios generales de la empresa
function guardarDatosEmpresa(e) {
    e.preventDefault();
    const token = getToken();
    const alertBox = document.getElementById('alertBox');
    const btnGuardar = document.getElementById('btnGuardar');

    alertBox.classList.add('d-none');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

    const formData = new FormData();
    formData.append('nombreEmpresa', document.getElementById('nombreEmpresa').value);
    formData.append('idIndustria', document.getElementById('idIndustria').value);
    formData.append('paginaWeb', document.getElementById('paginaWeb').value);
    formData.append('telefono', document.getElementById('telefono').value);
    formData.append('sobreMi', document.getElementById('sobreMi').value);

    const fileInput = document.getElementById('avatar');
    if (fileInput.files.length > 0) {
        formData.append('avatar', fileInput.files[0]);
    }

    formData.append('_method', 'PUT');

    fetch('/api/perfil', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'Authorization': `Bearer ${token}`
        },
        body: formData
    })
    .then(async response => {
        const data = await response.json();
        if (response.ok) {
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message || '¡Perfil de empresa actualizado con éxito!';
            alertBox.classList.remove('d-none');
            setTimeout(() => { window.location.href = '/perfil/empresa'; }, 1500);
        } else {
            throw new Error(data.message || 'Error al actualizar el perfil de empresa.');
        }
    })
    .catch(error => {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
    })
    .finally(() => {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    });
}