document.addEventListener('DOMContentLoaded', function () {
    // 1. Cargar catálogos dinámicos
    cargarCarreras();
    cargarIndustrias();

    // 2. Escuchar cambios en la carrera para actualizar semestres dinámicamente
    const selectCarrera = document.getElementById('selectCarrera');
    if (selectCarrera) {
        selectCarrera.addEventListener('change', function () {
            const optionSeleccionada = this.options[this.selectedIndex];
            const totalSemestres = optionSeleccionada ? optionSeleccionada.getAttribute('data-semestres') : null;
            actualizarOpcionesSemestre(totalSemestres);
        });
    }

    // 3. Listeners de envío
    const formAlumno = document.getElementById('formRegistroAlumno');
    if (formAlumno) formAlumno.addEventListener('submit', registrarAlumno);

    const formEmpresa = document.getElementById('formRegistroEmpresa');
    if (formEmpresa) formEmpresa.addEventListener('submit', registrarEmpresa);
});

// Cargar carreras dinámicamente desde el endpoint de la API
function cargarCarreras() {
    fetch('/api/carreras', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => {
        if (!response.ok) throw new Error('Error al obtener las carreras.');
        return response.json();
    })
    .then(data => {
        const carreras = data.data || data.carreras || data;
        const selectCarrera = document.getElementById('selectCarrera');
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
    .catch(error => console.error('Error cargando carreras:', error));
}

// Cargar industrias para el formulario de empresa
function cargarIndustrias() {
    fetch('/api/industrias', {
        headers: { 'Accept': 'application/json' }
    })
    .then(response => response.json())
    .then(data => {
        const industrias = data.data || data.industrias || data;
        const selectIndustria = document.getElementById('selectIndustria');
        if (!selectIndustria) return;

        selectIndustria.innerHTML = '<option value="" selected disabled>Selecciona tu industria</option>';
        industrias.forEach(ind => {
            const option = document.createElement('option');
            option.value = ind.idIndustria || ind.id;
            option.textContent = ind.nombreIndustria || ind.industria || ind.nombre;
            selectIndustria.appendChild(option);
        });
    })
    .catch(error => console.error('Error cargando industrias:', error));
}

// Generar dinámicamente las opciones del select de semestres
function actualizarOpcionesSemestre(totalSemestres) {
    const selectSemestre = document.getElementById('selectSemestre');
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

// Registro de Alumnos
async function registrarAlumno(e) {
    e.preventDefault();

    const btnSubmit = document.getElementById('btnRegistroAlumno');
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';
    }

    // Al usar FormData(this) enviamos directamente todos los inputs con sus nombres HTML (nombre, sobreMi, curriculum, etc.)
    const formData = new FormData(this);
    formData.append('idRol', 1);

    try {
        const response = await fetch('/api/alumnos', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message || '¡Alumno registrado correctamente! Redirigiendo al inicio de sesión...');
            this.reset();
            actualizarOpcionesSemestre(null);
            setTimeout(() => { window.location.href = '/login'; }, 1500);
        } else {
            alert(data.message || 'Ocurrió un error al registrar al alumno.');
        }
    } catch (error) {
        alert('Error en el servidor: ' + error.message);
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Registrar Alumno';
        }
    }
}

// Registro de Empresas
async function registrarEmpresa(e) {
    e.preventDefault();

    const btnSubmit = document.getElementById('btnRegistroEmpresa');
    if (btnSubmit) {
        btnSubmit.disabled = true;
        btnSubmit.textContent = 'Guardando...';
    }

    const formData = new FormData(this);
    formData.append('idRol', 2);

    try {
        const response = await fetch('/api/empresas', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });

        const data = await response.json();

        if (response.ok) {
            alert(data.message || '¡Empresa registrada correctamente! Redirigiendo al inicio de sesión...');
            this.reset();
            setTimeout(() => { window.location.href = '/login'; }, 1500);
        } else {
            alert(data.message || 'Ocurrió un error al registrar la empresa.');
        }
    } catch (error) {
        alert('Error en el servidor: ' + error.message);
    } finally {
        if (btnSubmit) {
            btnSubmit.disabled = false;
            btnSubmit.textContent = 'Registrar Empresa';
        }
    }
}