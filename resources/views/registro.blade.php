<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Alumno</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-7">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white text-center">
                    <h4 class="mb-0">Registro de Alumno</h4>
                </div>
                <div class="card-body p-4">

                    <!-- Contenedor para mensajes de respuesta -->
                    <div id="alertBox" class="alert d-none" role="alert"></div>

                    <form id="formRegistro" action="/api/alumnos" method="POST" enctype="multipart/form-data">
                        @csrf

                        <input type="hidden" name="idRol" value="1">
                        <input type="hidden" name="idCarrera" value="1">

                        <!-- Nombre -->
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre(s)</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                            <div class="invalid-feedback" id="error-nombre"></div>
                        </div>

                        <!-- Apellidos -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="apellidoPaterno" class="form-label">Apellido Paterno</label>
                                <input type="text" name="apellidoPaterno" id="apellidoPaterno" class="form-control" required>
                                <div class="invalid-feedback" id="error-apellidoPaterno"></div>
                            </div>
                            <div class="col-md-6">
                                <label for="apellidoMaterno" class="form-label">Apellido Materno</label>
                                <input type="text" name="apellidoMaterno" id="apellidoMaterno" class="form-control" required>
                                <div class="invalid-feedback" id="error-apellidoMaterno"></div>
                            </div>
                        </div>

                        <!-- Correo Electrónico -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo Electrónico</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                            <div class="invalid-feedback" id="error-email"></div>
                        </div>

                        <!-- Contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control" required>
                            <div class="invalid-feedback" id="error-password"></div>
                        </div>

                        <!-- Teléfono (Fila individual o combinada) -->
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label for="telefono" class="form-label">Teléfono</label>
                                <input type="text" name="telefono" id="telefono" class="form-control" maxlength="10" required placeholder="Ej. 6671234567">
                                <div class="invalid-feedback" id="error-telefono"></div>
                            </div>
                        </div>

                        <!-- Carrera y Semestre (En una misma fila) -->
                        <div class="row mb-3">
                            <!-- Carrera (Cargada vía API) -->
                            <div class="col-md-6">
                                <label for="idCarrera" class="form-label">Carrera</label>
                                <select name="idCarrera" id="idCarrera" class="form-select" required>
                                    <option value="" selected disabled>Cargando carreras...</option>
                                </select>
                                <div class="invalid-feedback" id="error-idCarrera"></div>
                            </div>

                            <!-- Semestre (Dependiente de Carrera) -->
                            <div class="col-md-6">
                                <label for="semestre" class="form-label">Semestre</label>
                                <select name="semestre" id="semestre" class="form-select" required disabled>
                                    <option value="" selected disabled>Selecciona primero una carrera</option>
                                </select>
                                <div class="invalid-feedback" id="error-semestre"></div>
                            </div>
                        </div>

                        <!-- Sobre Mí -->
                        <div class="mb-3">
                            <label for="sobreMi" class="form-label">Sobre Mí (Opcional)</label>
                            <textarea name="sobreMi" id="sobreMi" rows="3" class="form-control"></textarea>
                            <div class="invalid-feedback" id="error-sobreMi"></div>
                        </div>

                        <!-- Currículum Vitae -->
                        <div class="mb-4">
                            <label for="curriculum" class="form-label">Currículum Vitae (PDF o Word)</label>
                            <input type="file" name="curriculum" id="curriculum" class="form-control" accept=".pdf,.doc,.docx" required>
                            <div class="form-text">Archivos permitidos: PDF, DOC, DOCX. Máximo 2MB.</div>
                            <div class="invalid-feedback" id="error-curriculum"></div>
                        </div>

                        <!-- Botón de Registro -->
                        <div class="d-grid mb-3">
                            <button type="submit" id="btnSubmit" class="btn btn-primary btn-lg">Registrar Alumno</button>
                        </div>

                        <!-- Enlace para regresar al Login -->
                        <div class="text-center">
                            <small class="text-muted">¿Ya tienes cuenta? <a href="/login" class="text-decoration-none fw-bold">Inicia sesión aquí</a></small>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Cargar las carreras desde la API al renderizar la vista
            cargarCarreras();

            // 2. Escuchar cambios en la carrera seleccionada para actualizar los semestres
            const selectCarrera = document.getElementById('idCarrera');
            if (selectCarrera) {
                selectCarrera.addEventListener('change', function () {
                    const optionSeleccionada = this.options[this.selectedIndex];
                    const totalSemestres = optionSeleccionada ? optionSeleccionada.getAttribute('data-semestres') : null;
                    actualizarOpcionesSemestre(totalSemestres);
                });
            }
        });

        // Cargar carreras dinámicamente desde el endpoint de la API
        function cargarCarreras() {
            fetch('/api/carreras', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) throw new Error('Error al obtener la lista de carreras.');
                return response.json();
            })
            .then(data => {
                const carreras = data.data || data.carreras || data;
                const selectCarrera = document.getElementById('idCarrera');

                if (!selectCarrera) return;

                selectCarrera.innerHTML = '<option value="" selected disabled>Selecciona tu carrera</option>';

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
            })
            .catch(error => {
                const alertBox = document.getElementById('alertBox');
                if (alertBox) {
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = 'No se pudieron cargar las carreras: ' + error.message;
                    alertBox.classList.remove('d-none');
                }
            });
        }

        // Generar dinámicamente las opciones del <select> de semestres según la carrera elegida
        function actualizarOpcionesSemestre(totalSemestres) {
            const selectSemestre = document.getElementById('semestre');
            if (!selectSemestre) return;

            selectSemestre.innerHTML = '';

            if (!totalSemestres) {
                selectSemestre.innerHTML = '<option value="" selected disabled>Selecciona primero una carrera</option>';
                selectSemestre.disabled = true;
                return;
            }

            selectSemestre.disabled = false;
            selectSemestre.innerHTML = '<option value="" selected disabled>Selecciona tu semestre</option>';

            for (let i = 1; i <= parseInt(totalSemestres); i++) {
                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Semestre ${i}`;
                selectSemestre.appendChild(option);
            }
        }

        // Evento de envío del formulario
        document.getElementById('formRegistro').addEventListener('submit', async function (e) {
            e.preventDefault();

            const alertBox = document.getElementById('alertBox');
            const btnSubmit = document.getElementById('btnSubmit');
            
            // Limpiar alertas y errores previos
            alertBox.classList.add('d-none');
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));

            btnSubmit.disabled = true;
            btnSubmit.textContent = 'Guardando...';

            const formData = new FormData(this);

            try {
                const response = await fetch('/api/alumnos', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (response.ok) {
                    alertBox.className = 'alert alert-success';
                    alertBox.textContent = (data.message || '¡Alumno registrado correctamente!') + ' Redirigiendo al inicio de sesión...';
                    alertBox.classList.remove('d-none');
                    this.reset();

                    // Deshabilitar el select de semestre nuevamente al limpiar el formulario
                    actualizarOpcionesSemestre(null);

                    // Redirigir al inicio de sesión tras 1.5 segundos
                    setTimeout(() => {
                        window.location.href = '/login';
                    }, 1500);

                } else if (response.status === 422) {
                    // Mostrar errores de validación devueltos por la API
                    alertBox.className = 'alert alert-danger';
                    alertBox.textContent = 'Por favor, corrige los errores del formulario.';
                    alertBox.classList.remove('d-none');

                    Object.keys(data.errors).forEach(field => {
                        const input = document.getElementById(field);
                        const errorDiv = document.getElementById(`error-${field}`);
                        if (input) {
                            input.classList.add('is-invalid');
                        }
                        if (errorDiv) {
                            errorDiv.textContent = data.errors[field][0];
                        }
                    });
                } else {
                    throw new Error(data.message || 'Error al procesar la solicitud.');
                }
            } catch (error) {
                alertBox.className = 'alert alert-danger';
                alertBox.textContent = error.message;
                alertBox.classList.remove('d-none');
            } finally {
                btnSubmit.disabled = false;
                btnSubmit.textContent = 'Registrar Alumno';
            }
        });
    </script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>