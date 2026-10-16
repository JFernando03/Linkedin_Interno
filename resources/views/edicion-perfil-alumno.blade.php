<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Alumno</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="bg-light">

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                        <h4 class="mb-0">Editar Perfil de Alumno</h4>
                        <a href="/perfil" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body p-4">
                        
                        <!-- Mensaje de Estado / Alerta -->
                        <div id="alertBox" class="alert d-none" role="alert"></div>

                        <!-- Formulario de Edición General -->
                        <form id="formEditarPerfil" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Nombre -->
                            <div class="mb-3">
                                <label for="nombre" class="form-label fw-bold">Nombre(s)</label>
                                <input type="text" class="form-control" id="nombre" name="nombre" required placeholder="Nombre(s)">
                                <div class="invalid-feedback" id="error-nombre"></div>
                            </div>

                            <!-- Apellido Paterno -->
                            <div class="mb-3">
                                <label for="apellidoPaterno" class="form-label fw-bold">Apellido Paterno</label>
                                <input type="text" class="form-control" id="apellidoPaterno" name="apellidoPaterno" required placeholder="Apellido Paterno">
                                <div class="invalid-feedback" id="error-apellidoPaterno"></div>
                            </div>

                            <!-- Apellido Materno -->
                            <div class="mb-3">
                                <label for="apellidoMaterno" class="form-label fw-bold">Apellido Materno</label>
                                <input type="text" class="form-control" id="apellidoMaterno" name="apellidoMaterno" required placeholder="Apellido Materno">
                                <div class="invalid-feedback" id="error-apellidoMaterno"></div>
                            </div>
                            
                            <!-- Carrera -->
                            <div class="mb-3">
                                <label for="idCarrera" class="form-label fw-bold">Carrera</label>
                                <select class="form-select" id="idCarrera" name="idCarrera" required>
                                    <option value="">Cargando carreras...</option>
                                </select>
                                <div class="invalid-feedback" id="error-idCarrera"></div>
                            </div>

                            <!-- Semestre -->
                            <div class="mb-3">
                                <label for="semestre" class="form-label fw-bold">Semestre</label>
                                <select class="form-select" id="semestre" name="semestre" required>
                                    <option value="">Seleccione carrera</option>
                                </select>
                                <div class="invalid-feedback" id="error-semestre"></div>
                            </div>

                            <!-- Currículum (PDF/Documento) -->
                            <div class="mb-3">
                                <label for="curriculum" class="form-label fw-bold">Currículum (CV)</label>
                                <input type="file" class="form-control" id="curriculum" name="curriculum" accept=".pdf,.doc,.docx">
                                <small class="text-muted">Deja este campo vacío si no deseas actualizar tu archivo de CV actual.</small>
                                <div class="invalid-feedback" id="error-curriculum"></div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="/perfil" class="btn btn-secondary">Cancelar</a>
                                <button type="submit" id="btnGuardar" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Guardar Cambios Generales
                                </button>
                            </div>

                        </form>

                        <hr class="my-4">

                        <!-- Sección de Gestión de Habilidades -->
                        <h5 class="card-title fs-6 text-uppercase text-primary fw-bold mb-3">
                            <i class="bi bi-code-slash me-1"></i> Mis Habilidades Tecnológicas
                        </h5>

                        <!-- Lista visual de habilidades actuales del alumno con opción de borrado -->
                        <div class="mb-3">
                            <div id="listaMisHabilidades" class="d-flex flex-wrap gap-2 mb-3">
                                <span class="text-muted">Cargando habilidades...</span>
                            </div>

                            <!-- Selector de Habilidad, Nivel y Botón de Agregar -->
                            <div class="row g-2">
                                <div class="col-md-7">
                                    <select class="form-select" id="selectNuevaHabilidad">
                                        <option value="">Cargando catálogo...</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select class="form-select" id="selectNivelHabilidad">
                                        <option value="1">Básico</option>
                                        <option value="2" selected>Intermedio</option>
                                        <option value="3">Avanzado</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button class="btn btn-outline-primary w-100" type="button" id="btnAddHabilidad">
                                        <i class="bi bi-plus-lg"></i> Agregar
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
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
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>