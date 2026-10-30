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