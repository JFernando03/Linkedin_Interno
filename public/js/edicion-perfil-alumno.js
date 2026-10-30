    document.addEventListener('DOMContentLoaded', function () {
        cargarCarreras();
        cargarCatalogoHabilidades();

        document.getElementById('formEditarPerfil').addEventListener('submit', guardarDatosAlumno);
        document.getElementById('idCarrera').addEventListener('change', function () {
            const optionSeleccionada = this.options[this.selectedIndex];
            const totalSemestres = optionSeleccionada ? optionSeleccionada.getAttribute('data-semestres') : null;
            actualizarOpcionesSemestre(totalSemestres);
        });

        document.getElementById('btnAddHabilidad').addEventListener('click', agregarHabilidadAlumno);
    });

    function getToken() {
        return localStorage.getItem('token') || 
               localStorage.getItem('access_token') || 
               localStorage.getItem('auth_token') || 
               sessionStorage.getItem('token');
    }

    // 1. Cargar catálogo de carreras
    function cargarCarreras() {
        const token = getToken();

        fetch('/api/carreras', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => response.ok ? response.json() : Promise.reject('Error al cargar carreras'))
        .then(data => {
            const carreras = data.data || data.carreras || data;
            const selectCarrera = document.getElementById('idCarrera');
            selectCarrera.innerHTML = '<option value="">Selecciona tu carrera</option>';

            carreras.forEach(carrera => {
                const option = document.createElement('option');
                option.value = carrera.idCarrera || carrera.id_carrera || carrera.id;
                option.textContent = carrera.carrera || carrera.nombre;
                option.setAttribute('data-semestres', carrera.semestres || carrera.total_semestres || 10);
                selectCarrera.appendChild(option);
            });

            cargarDatosAlumno();
        })
        .catch(error => console.error(error));
    }

    // 2. Cargar catálogo general de habilidades
    function cargarCatalogoHabilidades() {
        const token = getToken();

        fetch('/api/habilidades', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(res => res.ok ? res.json() : Promise.reject('Error al cargar catálogo de habilidades'))
        .then(data => {
            const habilidades = data.habilidades || data.data || data;
            const select = document.getElementById('selectNuevaHabilidad');
            select.innerHTML = '<option value="">Selecciona habilidad...</option>';

            if (Array.isArray(habilidades)) {
                habilidades.forEach(h => {
                    const option = document.createElement('option');
                    option.value = h.idHabilidad || h.id;
                    option.textContent = h.nombre || h.habilidad;
                    select.appendChild(option);
                });
            }
        })
        .catch(err => console.error('Error catálogo habilidades:', err));
    }

    // 3. Actualizar semestres dinámicos
    function actualizarOpcionesSemestre(totalSemestres, semestreSeleccionado = null) {
        const selectSemestre = document.getElementById('semestre');
        if (!selectSemestre) return;

        selectSemestre.innerHTML = '<option value="">Selecciona tu semestre</option>';
        for (let i = 1; i <= parseInt(totalSemestres || 10); i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Semestre ${i}`;
            if (semestreSeleccionado && parseInt(semestreSeleccionado) === i) {
                option.selected = true;
            }
            selectSemestre.appendChild(option);
        }
    }

    // 4. Cargar datos actuales del alumno y sus habilidades asociadas
    function cargarDatosAlumno() {
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
            const alumno = baseData.alumno || baseData.persona || baseData;
            
            if (document.getElementById('nombre')) document.getElementById('nombre').value = alumno.nombre || baseData.nombre || '';
            if (document.getElementById('apellidoPaterno')) document.getElementById('apellidoPaterno').value = alumno.apellidoPaterno || alumno.apellido_paterno || baseData.apellidoPaterno || '';
            if (document.getElementById('apellidoMaterno')) document.getElementById('apellidoMaterno').value = alumno.apellidoMaterno || alumno.apellido_materno || baseData.apellidoMaterno || '';

            const selectCarrera = document.getElementById('idCarrera');
            const carreraVal = alumno.idCarrera || baseData.idCarrera || alumno.id_carrera;

            if (carreraVal && selectCarrera) {
                selectCarrera.value = String(carreraVal);
                const optionSeleccionada = selectCarrera.options[selectCarrera.selectedIndex];
                if (optionSeleccionada) {
                    const totalSemestres = optionSeleccionada.getAttribute('data-semestres');
                    actualizarOpcionesSemestre(totalSemestres, alumno.semestre || baseData.semestre);
                }
            }

            const habilidadesAlumno = alumno.habilidades || baseData.habilidades || alumno.alumno_habilidades || [];
            renderizarMisHabilidades(habilidadesAlumno);
        })
        .catch(error => {
            console.error('Error cargando perfil:', error.message);
            actualizarOpcionesSemestre(10);
        });
    }

    // Renderizar insignias de habilidades con nivel y botón de eliminación robusto
    function renderizarMisHabilidades(habilidades) {
        const container = document.getElementById('listaMisHabilidades');
        if (!habilidades || habilidades.length === 0) {
            container.innerHTML = '<span class="text-muted">No tienes habilidades registradas aún.</span>';
            return;
        }

        const nivelesMap = { 1: 'Básico', 2: 'Intermedio', 3: 'Avanzado' };

        container.innerHTML = '';
        habilidades.forEach(item => {
            const nombreH = item.habilidad ? (item.habilidad.nombre || item.habilidad.habilidad) : (item.nombre || item.habilidad);
            const idNivel = item.pivot?.idNivel || item.idNivel || 2;
            const textoNivel = nivelesMap[idNivel] || `Nivel ${idNivel}`;
            
            // Recolectamos de forma segura cualquier variante posible del identificador del registro
            const idRelacion = item.pivot?.id_alumno_habilidad || 
                              item.pivot?.id || 
                              item.idAlumnoHabilidad || 
                              item.idUsuarioHabilidad || 
                              item.idHabilidad || 
                              item.id;

            if (nombreH) {
                const badge = document.createElement('span');
                badge.className = 'badge bg-primary d-inline-flex align-items-center gap-2 p-2 fs-6';
                badge.innerHTML = `
                    <span>${nombreH} <small class="text-white-50">(${textoNivel})</small></span>
                    <button type="button" class="btn-close btn-close-white btn-sm" aria-label="Eliminar" onclick="eliminarHabilidad('${idRelacion}')"></button>
                `;
                container.appendChild(badge);
            }
        });
    }

    // 5. Agregar Habilidad (POST /api/alumnos/habilidades)
    function agregarHabilidadAlumno() {
        const token = getToken();
        const selectHabilidad = document.getElementById('selectNuevaHabilidad');
        const selectNivel = document.getElementById('selectNivelHabilidad');
        
        const idHabilidad = selectHabilidad.value;
        const idNivel = selectNivel.value;

        if (!idHabilidad) {
            alert('Selecciona una habilidad válida del catálogo.');
            return;
        }

        fetch('/api/alumnos/habilidades', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify({ 
                idHabilidad: idHabilidad,
                idNivel: idNivel 
            })
        })
        .then(async res => {
            const data = await res.json();
            if (res.ok) {
                selectHabilidad.value = '';
                selectNivel.value = '2';
                cargarDatosAlumno();
            } else {
                alert(data.message || 'Error al agregar la habilidad.');
            }
        })
        .catch(err => console.error('Error:', err));
    }

    // 6. Eliminar Habilidad (DELETE /api/alumnos/habilidades/{id})
    function eliminarHabilidad(idRelacion) {
        const token = getToken();
        if (!idRelacion || idRelacion === 'undefined' || idRelacion === 'null') {
            alert('Error: No se encontró el identificador de esta relación.');
            return;
        }

        if (!confirm('¿Estás seguro de eliminar esta habilidad de tu perfil?')) return;

        fetch(`/api/alumnos/habilidades/${idRelacion}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(async res => {
            if (res.ok) {
                cargarDatosAlumno(); // Recarga la lista al instante
            } else {
                const data = await res.json().catch(() => ({}));
                alert(data.message || 'Error al eliminar la habilidad.');
            }
        })
        .catch(err => console.error('Error de red al eliminar:', err));
    }

    // 7. Guardar cambios generales (PUT /api/perfil)
    function guardarDatosAlumno(e) {
        e.preventDefault();
        const token = getToken();
        const alertBox = document.getElementById('alertBox');
        const btnGuardar = document.getElementById('btnGuardar');

        alertBox.classList.add('d-none');
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        const formData = new FormData();
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellidoPaterno', document.getElementById('apellidoPaterno').value);
        formData.append('apellidoMaterno', document.getElementById('apellidoMaterno').value);
        formData.append('idCarrera', document.getElementById('idCarrera').value);
        formData.append('semestre', document.getElementById('semestre').value);

        const fileInput = document.getElementById('curriculum');
        if (fileInput.files.length > 0) {
            formData.append('curriculum', fileInput.files[0]);
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
                alertBox.textContent = data.message || '¡Perfil actualizado con éxito!';
                alertBox.classList.remove('d-none');
                setTimeout(() => { window.location.href = '/perfil'; }, 1500);
            } else {
                throw new Error(data.message || 'Error al actualizar el perfil.');
            }
        })
        .catch(error => {
            alertBox.className = 'alert alert-danger';
            alertBox.textContent = error.message;
            alertBox.classList.remove('d-none');
        })
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios Generales';
        });
    }