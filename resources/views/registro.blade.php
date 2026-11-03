<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro | linkedinFIC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-5" style="max-width: 600px;">
    <div class="card border-0 shadow-sm p-4">
        <h3 class="text-center fw-bold mb-4">Crear una Cuenta</h3>

        <!-- Pestañas para seleccionar tipo de usuario -->
        <ul class="nav nav-pills nav-justified mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="tab-alumno" data-bs-toggle="pill" data-bs-target="#pills-alumno" type="button" role="tab">Soy Alumno</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="tab-empresa" data-bs-toggle="pill" data-bs-target="#pills-empresa" type="button" role="tab">Soy Empresa</button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            
<!-- FORMULARIO ALUMNO -->
<div class="tab-pane fade show active" id="pills-alumno" role="tabpanel">
    <form id="formRegistroAlumno" enctype="multipart/form-data">
        <input type="hidden" name="idRol" value="1">

        <div class="mb-3">
            <label class="form-label fw-bold">Nombre(s)</label>
            <input type="text" name="nombre" id="nombreAlumno" class="form-control" required>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Apellido Paterno</label>
                <input type="text" name="apellidoPaterno" id="apellidoPaternoAlumno" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Apellido Materno</label>
                <input type="text" name="apellidoMaterno" id="apellidoMaternoAlumno" class="form-control" required>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Carrera</label>
                <select name="idCarrera" id="selectCarrera" class="form-select" required>
                    <option value="">Cargando carreras...</option>
                </select>
            </div>

            <div class="col-md-6 mb-3">
                <label class="form-label fw-bold">Semestre</label>
                <select name="semestre" id="selectSemestre" class="form-select" required disabled>
                    <option value="">Selecciona primero una carrera</option>
                </select>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Correo Electrónico</label>
            <input type="email" name="email" id="emailAlumno" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Contraseña</label>
            <input type="password" name="password" id="passwordAlumno" class="form-control" required>
        </div>

        <div class="mb-3">
            <label for="sobreMiAlumno" class="form-label fw-bold">Sobre Mí (Opcional)</label>
            <textarea name="sobreMi" id="sobreMiAlumno" class="form-control" rows="3" placeholder="Cuéntanos sobre ti..."></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Teléfono (Opcional)</label>
            <input type="tel" name="telefono" id="telefonoAlumno" class="form-control" 
                maxlength="10" pattern="[0-9]{10}" inputmode="numeric" 
                placeholder="10 dígitos (ej. 6671234567)"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <!-- Currículum Vitae (Recuperado) -->
        <div class="mb-3">
            <label class="form-label fw-bold">Currículum Vitae (PDF o Word)</label>
            <input type="file" name="curriculum" id="curriculumAlumno" class="form-control" accept=".pdf,.doc,.docx">
            <small class="text-muted">Archivos permitidos: PDF, DOC, DOCX. Máximo 2MB.</small>
        </div>

        <button type="submit" id="btnRegistroAlumno" class="btn btn-primary w-100 fw-bold">Registrarme como Alumno</button>
    </form>
</div>

            <!-- FORMULARIO EMPRESA -->
<div class="tab-pane fade" id="pills-empresa" role="tabpanel">
    <form id="formRegistroEmpresa" enctype="multipart/form-data">
        <input type="hidden" name="idRol" value="2">

        <div class="mb-3">
            <label class="form-label fw-bold">Nombre de la Empresa</label>
            <input type="text" name="nombreEmpresa" id="nombreEmpresa" class="form-control" placeholder="Ej. Checho Corp" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Industria / Sector</label>
            <select name="idIndustria" id="selectIndustria" class="form-select" required>
                <option value="">Selecciona una industria...</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Página Web (Opcional)</label>
            <input type="url" name="paginaWeb" id="paginaWeb" class="form-control" placeholder="https://ejemplo.com">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Correo Electrónico corporativo</label>
            <input type="email" name="email" id="emailEmpresa" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Contraseña</label>
            <input type="password" name="password" id="passwordEmpresa" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Teléfono de contacto (Opcional)</label>
            <input type="tel" name="telefono" id="telefonoEmpresa" class="form-control" 
                maxlength="10" pattern="[0-9]{10}" inputmode="numeric" 
                placeholder="10 dígitos (ej. 6671234567)"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Logo / Avatar (Opcional)</label>
            <input type="file" name="avatar" id="avatarEmpresa" class="form-control" accept="image/*">
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Sobre la empresa / Descripción (Opcional)</label>
            <textarea name="sobreMi" id="sobreMiEmpresa" class="form-control" rows="3" placeholder="Breve descripción de lo que realiza la empresa..."></textarea>
        </div>

        <button type="submit" id="btnRegistroEmpresa" class="btn btn-primary w-100 fw-bold">Registrar Empresa</button>
    </form>
</div>

<!-- Enlace discreto para regresar al Login -->
        <div class="text-center mt-4 pt-3 border-top">
            <span class="text-muted small">¿Ya tienes una cuenta?</span>
            <a href="/login" class="text-decoration-none fw-bold small ms-1 text-primary">Iniciar Sesión</a>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/registro.js') }}"></script>
</body>
</html>