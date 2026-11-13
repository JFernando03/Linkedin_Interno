document.addEventListener('DOMContentLoaded', async function () {
    await cargarIndustrias();
    await cargarDatosEmpresa();

    document.getElementById('formEditarPerfilEmpresa').addEventListener('submit', guardarDatosEmpresa);
    
    const inputTelefono = document.getElementById('telefono');
    if (inputTelefono) {
        inputTelefono.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '').slice(0, 10);
        });
    }
});

function getToken() {
    return localStorage.getItem('token') || 
           localStorage.getItem('access_token') || 
           localStorage.getItem('auth_token') || 
           sessionStorage.getItem('token');
}

// 1. Cargar catálogo de industrias (asíncrono para esperar antes de preseleccionar)
async function cargarIndustrias() {
    const token = getToken();
    try {
        const response = await fetch('/api/industrias', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });
        if (!response.ok) throw new Error('Error al cargar industrias');
        
        const data = await response.json();
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
    } catch (error) {
        console.error('Error catálogo industrias:', error);
    }
}

// 2. Cargar datos actuales de la empresa
async function cargarDatosEmpresa() {
    const token = getToken();
    if (!token) {
        window.location.replace('/login');
        return;
    }

    try {
        const res = await fetch('/api/perfil', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        });

        if (!res.ok) throw new Error('No se pudo obtener la información del perfil.');
        const data = await res.json();

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
        const industriaVal = empresa.idIndustria || empresa.id_industria || (empresa.industria ? empresa.industria.idIndustria : '');
        if (industriaVal && selectIndustria) {
            selectIndustria.value = String(industriaVal);
        }
    } catch (error) {
        console.error('Error cargando perfil de empresa:', error.message);
    }
}

// 3. Guardar cambios generales de la empresa
async function guardarDatosEmpresa(e) {
    e.preventDefault();
    const token = getToken();
    const alertBox = document.getElementById('alertBox');
    const btnGuardar = document.getElementById('btnGuardar');

    alertBox.classList.add('d-none');
    document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

    btnGuardar.disabled = true;
    btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

    const formData = new FormData();
    formData.append('nombreEmpresa', document.getElementById('nombreEmpresa').value);
    formData.append('idIndustria', document.getElementById('idIndustria').value);
    formData.append('paginaWeb', document.getElementById('paginaWeb').value);
    formData.append('telefono', document.getElementById('telefono').value);
    formData.append('sobreMi', document.getElementById('sobreMi').value);

    const fileInput = document.getElementById('avatar');
    if (fileInput && fileInput.files.length > 0) {
        formData.append('avatar', fileInput.files[0]);
    }

    formData.append('_method', 'PUT');

    try {
        const response = await fetch('/api/perfil', {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            // Actualizar el objeto usuario en localStorage para mantener sincronizado el nombre
            if (data.usuario) {
                localStorage.setItem('usuario', JSON.stringify(data.usuario));
            }
            alertBox.className = 'alert alert-success';
            alertBox.textContent = data.message || '¡Perfil de empresa actualizado con éxito!';
            alertBox.classList.remove('d-none');
            setTimeout(() => { window.location.href = '/perfil/empresa'; }, 1500);
        } else if (response.status === 422 && data.errors) {
            Object.keys(data.errors).forEach(field => {
                const input = document.getElementById(field);
                const errorDiv = document.getElementById(`error-${field}`);
                if (input) input.classList.add('is-invalid');
                if (errorDiv) errorDiv.textContent = data.errors[field][0];
            });
            throw new Error(data.message || 'Revisa los campos marcados en rojo.');
        } else {
            throw new Error(data.message || 'Error al actualizar el perfil de empresa.');
        }
    } catch (error) {
        alertBox.className = 'alert alert-danger';
        alertBox.textContent = error.message;
        alertBox.classList.remove('d-none');
    } finally {
        btnGuardar.disabled = false;
        btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
    }
}