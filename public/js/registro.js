document.addEventListener('DOMContentLoaded', () => {
    cargarCarreras();
    cargarIndustrias();

    const formAlumno = document.getElementById('formRegistroAlumno');
    if (formAlumno) {
        formAlumno.addEventListener('submit', registrarAlumno);
    }

    const formEmpresa = document.getElementById('formRegistroEmpresa');
    if (formEmpresa) {
        formEmpresa.addEventListener('submit', registrarEmpresa);
    }
});

let listaCarreras = []; // Guardará las carreras obtenidas del API

// GET /api/carreras
async function cargarCarreras() {
    const selectCarrera = document.getElementById('selectCarrera');
    if (!selectCarrera) return;

    try {
        const response = await fetch('/api/carreras');
        const data = await response.json();
        
        listaCarreras = data.carreras || data.data || data;
        selectCarrera.innerHTML = '<option value="">Selecciona tu carrera...</option>';
        
        listaCarreras.forEach(c => {
            const nombre = c.carrera || c.nombreCarrera || c.nombre;
            selectCarrera.innerHTML += `<option value="${c.idCarrera}">${nombre}</option>`;
        });

        // Evento: al seleccionar/cambiar de carrera, actualizamos los semestres
        selectCarrera.addEventListener('change', actualizarOpcionesSemestre);

    } catch (e) {
        selectCarrera.innerHTML = '<option value="">Error al cargar carreras</option>';
    }
}

// Función dinama que limita y genera las opciones de semestre
function actualizarOpcionesSemestre() {
    const selectCarrera = document.getElementById('selectCarrera');
    const selectSemestre = document.getElementById('selectSemestre');
    
    const idCarreraSeleccionada = selectCarrera.value;
    
    if (!idCarreraSeleccionada) {
        selectSemestre.innerHTML = '<option value="">Selecciona primero una carrera</option>';
        selectSemestre.disabled = true;
        return;
    }

    const carreraEncontrada = listaCarreras.find(c => c.idCarrera == idCarreraSeleccionada);
    const totalSemestres = carreraEncontrada ? (carreraEncontrada.semestres || 10) : 10;

    selectSemestre.innerHTML = '<option value="">Selecciona tu semestre...</option>';
    for (let i = 1; i <= totalSemestres; i++) {
        selectSemestre.innerHTML += `<option value="${i}">${i}.º Semestre</option>`;
    }
    
    selectSemestre.disabled = false;
}

// GET /api/industrias
async function cargarIndustrias() {
    const select = document.getElementById('selectIndustria');
    if (!select) return;

    try {
        const response = await fetch('/api/industrias');
        const data = await response.json();

        if (data.success && Array.isArray(data.industrias)) {
            select.innerHTML = '<option value="">Selecciona una industria...</option>';
            data.industrias.forEach(ind => {
                select.innerHTML += `<option value="${ind.idIndustria}">${ind.industria || ind.nombre}</option>`;
            });
        }
    } catch (e) {
        select.innerHTML = '<option value="">Error al cargar industrias</option>';
    }
}

async function registrarAlumno(e) {
    e.preventDefault();

    const btn = document.getElementById('btnRegistroAlumno');
    if (btn) btn.disabled = true;

    try {
        const formData = new FormData();

        // Datos requeridos
        formData.append('idRol', 1);
        formData.append('nombre', document.getElementById('nombreAlumno').value.trim());
        formData.append('apellidoPaterno', document.getElementById('apellidoPaternoAlumno').value.trim());
        formData.append('apellidoMaterno', document.getElementById('apellidoMaternoAlumno').value.trim());
        formData.append('idCarrera', document.getElementById('selectCarrera').value);
        formData.append('email', document.getElementById('emailAlumno').value.trim());
        formData.append('password', document.getElementById('passwordAlumno').value);

        // Semestre tomado directamente del selector dinámico generado por la API
        const semestreVal = document.getElementById('selectSemestre')?.value || "1";
        formData.append('semestre', semestreVal);

        // Campos opcionales (Solo se adjuntan si el usuario escribió algo)
        const tel = document.getElementById('telefonoAlumno')?.value.trim();
        if (tel) {
            formData.append('telefono', tel);
        }

        const avatarInput = document.getElementById('avatarAlumno');
        if (avatarInput?.files[0]) {
            formData.append('avatar', avatarInput.files[0]);
        }

        const cvInput = document.getElementById('curriculumAlumno');
        if (cvInput?.files[0]) {
            formData.append('curriculum', cvInput.files[0]);
        }

        const response = await fetch('/api/alumnos', {
            method: 'POST',
            body: formData,
            headers: {
                'Accept': 'application/json'
            }
        });

        const data = await response.json();

        if (response.ok) {
            alert('¡Registro de alumno exitoso!');
            window.location.href = '/login';
        } else {
            alert(data.message || 'Error en el registro');
        }

    } catch (error) {
        console.error('Error:', error);
        alert('Ocurrió un error al procesar la solicitud');
    } finally {
        if (btn) btn.disabled = false;
    }
}

// POST /api/empresas
async function registrarEmpresa(e) {
    e.preventDefault();
    const btn = document.getElementById('btnRegistroEmpresa');
    btn.disabled = true;

    const formData = new FormData();
    formData.append('idRol', 2); // Ajusta el ID según el rol correspondiente a Empresa en tu BD
    formData.append('nombreEmpresa', document.getElementById('nombreEmpresa').value.trim());
    formData.append('idIndustria', document.getElementById('selectIndustria').value);
    formData.append('email', document.getElementById('emailEmpresa').value.trim());
    formData.append('password', document.getElementById('passwordEmpresa').value);

    const web = document.getElementById('paginaWeb').value.trim();
    if (web) formData.append('paginaWeb', web);

    // Provisional para enviar un string vacío si no hay teléfono, ya que el backend espera un string
    const telEmpresa = document.getElementById('telefonoEmpresa')?.value.trim() || "";
    formData.append('telefono', telEmpresa); // Manda '' en lugar de omitirlo o mandar null

    const sobreMi = document.getElementById('sobreMiEmpresa').value.trim();
    if (sobreMi) formData.append('sobreMi', sobreMi);

    const avatarInput = document.getElementById('avatarEmpresa');
    if (avatarInput && avatarInput.files[0]) {
        formData.append('avatar', avatarInput.files[0]);
    }

    try {
        const response = await fetch('/api/empresas', {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: formData
        });

        const data = await response.json();
        if (response.ok && data.success) {
            alert('¡Empresa registrada exitosamente! Ya puedes iniciar sesión.');
            window.location.href = '/login';
        } else {
            alert(data.message || data.error || 'Error al registrar empresa');
        }
    } catch (error) {
        alert('Error de conexión: ' + error.message);
    } finally {
        btn.disabled = false;
    }
}