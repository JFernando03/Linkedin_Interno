<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Empresa</title>
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
                        <h4 class="mb-0">Editar Perfil de Empresa</h4>
                        <a href="/perfil/empresa" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Volver
                        </a>
                    </div>

                    <div class="card-body p-4">
                        
                        <!-- Mensaje de Estado / Alerta -->
                        <div id="alertBox" class="alert d-none" role="alert"></div>

                        <!-- Formulario de Edición de Empresa -->
                        <form id="formEditarPerfilEmpresa" enctype="multipart/form-data">
                            @csrf
                            
                            <!-- Nombre de la Empresa -->
                            <div class="mb-3">
                                <label for="nombreEmpresa" class="form-label fw-bold">Nombre de la Empresa</label>
                                <input type="text" class="form-control" id="nombreEmpresa" name="nombreEmpresa" required placeholder="Nombre de la empresa">
                                <div class="invalid-feedback" id="error-nombreEmpresa"></div>
                            </div>

                            <!-- Industria -->
                            <div class="mb-3">
                                <label for="idIndustria" class="form-label fw-bold">Industria</label>
                                <select class="form-select" id="idIndustria" name="idIndustria" required>
                                    <option value="">Cargando industrias...</option>
                                </select>
                                <div class="invalid-feedback" id="error-idIndustria"></div>
                            </div>

                            <!-- Página Web -->
                            <div class="mb-3">
                                <label for="paginaWeb" class="form-label fw-bold">Página Web</label>
                                <input type="url" class="form-control" id="paginaWeb" name="paginaWeb" placeholder="https://tuempresa.com">
                                <div class="invalid-feedback" id="error-paginaWeb"></div>
                            </div>

                            <!-- Teléfono -->
                            <div class="mb-3">
                                <label for="telefono" class="form-label fw-bold">Teléfono de Contacto</label>
                                <input type="text" class="form-control" id="telefono" name="telefono" maxlength="10" pattern="[0-9]{10}" inputmode="numeric" placeholder="10 dígitos (opcional)">
                                <div class="invalid-feedback" id="error-telefono"></div>
                            </div>

                            <!-- Sobre Mí / Descripción de la Empresa -->
                            <div class="mb-3">
                                <label for="sobreMi" class="form-label fw-bold">Acerca de la Empresa</label>
                                <textarea class="form-control" id="sobreMi" name="sobreMi" rows="3" placeholder="Breve descripción de la empresa..."></textarea>
                                <div class="invalid-feedback" id="error-sobreMi"></div>
                            </div>

                            <!-- Avatar / Logotipo de la Empresa -->
                            <div class="mb-3">
                                <label for="avatar" class="form-label fw-bold">Logotipo / Avatar</label>
                                <input type="file" class="form-control" id="avatar" name="avatar" accept="image/*">
                                <small class="text-muted">Deja este campo vacío si no deseas actualizar el logotipo actual.</small>
                                <div class="invalid-feedback" id="error-avatar"></div>
                            </div>

                            <!-- Botones de Acción -->
                            <div class="d-flex justify-content-end gap-2 mt-4">
                                <a href="/perfil/empresa" class="btn btn-secondary">Cancelar</a>
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

    <!-- Script de lógica específico para empresa -->
    <script src="{{ asset('js/edicion-perfil-empresa.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>