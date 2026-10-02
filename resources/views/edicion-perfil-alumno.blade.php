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

                        <!-- Formulario de Edición -->
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
                            
                            <!-- Atualizacion: Se carga desde el PostgreSQL -->
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
                                    <i class="bi bi-save"></i> Guardar Cambios
                                </button>
                            </div>

                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cargar carrera desde la API
        cargarCarreras();

        document.getElementById('formEditarPerfil').addEventListener('submit', guardarDatosAlumno);
        document.getElementById('idCarrera').addEventListener('change', function () {
            const optionSeleccionada = this.options[this.selectedIndex];
            const totalSemestres = optionSeleccionada ? optionSeleccionada.getAttribute('data-semestres') : null;
            actualizarOpcionesSemestre(totalSemestres);
        });
    });

    function getToken() {
        return localStorage.getItem('token') || 
               localStorage.getItem('access_token') || 
               localStorage.getItem('auth_token') || 
               sessionStorage.getItem('token');
    }

    function cargarCarreras() {
        const token = getToken();

        fetch('/api/carreras', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al cargar la lista de carreras.');
            return response.json();
        })
        .then(data => {
            const carreras = data.data || data.carreras || data;
            const selectCarrera = document.getElementById('idCarrera');

            selectCarrera.innerHTML = '<option value="">Selecciona tu carrera</option>';

            carreras.forEach(carrera => {
                const option = document.createElement('option');
                const id = carrera.idCarrera || carrera.id_carrera || carrera.id;
                const nombre = carrera.carrera || carrera.nombre;
                const semestres = carrera.semestres || carrera.total_semestres || 10;

                option.value = id;
                option.textContent = nombre;
                option.setAttribute('data-semestres', semestres);

                selectCarrera.appendChild(option);
            });

            cargarDatosAlumno();
        })
        .catch(error => mostrarAlerta(error.message, 'danger'));
    }

    function actualizarOpcionesSemestre(totalSemestres, semestreSeleccionado = null) {
        const selectSemestre = document.getElementById('semestre');
        if (!selectSemestre) return;

        selectSemestre.innerHTML = '';

        if (!totalSemestres) {
            selectSemestre.innerHTML = '<option value="">Selecciona primero una carrera</option>';
            return;
        }

        selectSemestre.innerHTML = '<option value="">Selecciona tu semestre</option>';
        for (let i = 1; i <= parseInt(totalSemestres); i++) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `Semestre ${i}`;
            if (semestreSeleccionado && parseInt(semestreSeleccionado) === i) {
                option.selected = true;
            }
            selectSemestre.appendChild(option);
        }
    }

    function cargarDatosAlumno() {
        const token = getToken();

        if (!token) {
            mostrarAlerta('No se encontró un token de sesión. Por favor inicia sesión nuevamente.', 'danger');
            return;
        }

        fetch('/api/perfil', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Authorization': `Bearer ${token}`
            }
        })
        .then(response => {
            if (response.status === 401) throw new Error('Sesión vencida. Inicia sesión de nuevo.');
            if (!response.ok) throw new Error('Error al obtener la información del perfil.');
            return response.json();
        })
        .then(data => {
            const alumno = data.data || data.user || data.alumno || data;
            
            if (document.getElementById('nombre')) document.getElementById('nombre').value = alumno.nombre || '';
            if (document.getElementById('apellidoPaterno')) document.getElementById('apellidoPaterno').value = alumno.apellidoPaterno || '';
            if (document.getElementById('apellidoMaterno')) document.getElementById('apellidoMaterno').value = alumno.apellidoMaterno || '';

            const selectCarrera = document.getElementById('idCarrera');
            const carreraVal = alumno.idCarrera || alumno.id_carrera || (alumno.carrera ? (alumno.carrera.idCarrera || alumno.carrera.id || alumno.carrera.nombre) : null);

            if (carreraVal && selectCarrera) {
                selectCarrera.value = String(carreraVal);

                if (!selectCarrera.value) {
                    for (let option of selectCarrera.options) {
                        if (option.text.toLowerCase().trim() === String(carreraVal).toLowerCase().trim()) {
                            option.selected = true;
                            break;
                        }
                    }
                }

                const optionSeleccionada = selectCarrera.options[selectCarrera.selectedIndex];
                if (optionSeleccionada) {
                    const totalSemestres = optionSeleccionada.getAttribute('data-semestres');
                    actualizarOpcionesSemestre(totalSemestres, alumno.semestre);
                }
            }
        })
        .catch(error => mostrarAlerta(error.message, 'danger'));
    }

    // Guardar cambios
    function guardarDatosAlumno(e) {
        e.preventDefault();

        const token = getToken();
        if (!token) {
            mostrarAlerta('No estás autenticado. Inicia sesión de nuevo.', 'danger');
            return;
        }

        const alertBox = document.getElementById('alertBox');
        const btnGuardar = document.getElementById('btnGuardar');

        alertBox.classList.add('d-none');
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.invalid-feedback').forEach(el => el.textContent = '');

        btnGuardar.disabled = true;
        btnGuardar.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Guardando...';

        const formData = new FormData();
        formData.append('nombre', document.getElementById('nombre').value);
        formData.append('apellidoPaterno', document.getElementById('apellidoPaterno').value);
        formData.append('apellidoMaterno', document.getElementById('apellidoMaterno').value);
        
        const selectCarrera = document.getElementById('idCarrera');
        formData.append('idCarrera', selectCarrera.value);
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
                mostrarAlerta(data.message || '¡Perfil actualizado con éxito!', 'success');
                setTimeout(() => {
                    window.location.href = '/perfil';
                }, 1500);
            } else if (response.status === 401) {
                throw new Error('El token expiró o no es válido. Por favor inicia sesión de nuevo.');
            } else if (response.status === 422) {
                let mensajeGeneral = 'Por favor revisa la información ingresada:';
                
                if (data.errors) {
                    let lista = [];
                    Object.keys(data.errors).forEach(field => {
                        const input = document.getElementById(field);
                        const errorDiv = document.getElementById(`error-${field}`);
                        
                        if (input) input.classList.add('is-invalid');
                        if (errorDiv) errorDiv.textContent = data.errors[field][0];
                        
                        lista.push(`- ${data.errors[field][0]}`);
                    });
                    mensajeGeneral += '<br>' + lista.join('<br>');
                } else if (data.message) {
                    mensajeGeneral = data.message;
                }
                
                mostrarAlertaHtml(mensajeGeneral, 'danger');
            } else {
                throw new Error(data.message || 'Error al actualizar el perfil.');
            }
        })
        .catch(error => mostrarAlerta(error.message, 'danger'))
        .finally(() => {
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = '<i class="bi bi-save"></i> Guardar Cambios';
        });
    }

    function mostrarAlerta(mensaje, tipo) {
        const alertBox = document.getElementById('alertBox');
        alertBox.className = `alert alert-${tipo}`;
        alertBox.textContent = mensaje;
        alertBox.classList.remove('d-none');
    }

    function mostrarAlertaHtml(htmlContent, tipo) {
        const alertBox = document.getElementById('alertBox');
        alertBox.className = `alert alert-${tipo}`;
        alertBox.innerHTML = htmlContent;
        alertBox.classList.remove('d-none');
    }
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>