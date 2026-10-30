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

<script src="{{ asset('js/edicion-perfil-alumno.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>