document.addEventListener('DOMContentLoaded', async () => {
    const token = localStorage.getItem('token') || localStorage.getItem('auth_token');
    if (!token) {
        window.location.href = '/login';
        return;
    }

    const usuarioActual = JSON.parse(localStorage.getItem('usuario') || '{}');
    const miId = String(usuarioActual.idUsuario || usuarioActual.id || '');

    // Ajustar botón "Mi Perfil" según rol
    const btnPerfil = document.getElementById('navBtnPerfil');
    if (btnPerfil && (usuarioActual.idRol == 2 || Boolean(usuarioActual.empresa))) {
        btnPerfil.href = '/perfil/empresa';
    }

    // Cerrar sesión
    const btnLogout = document.getElementById('btnLogout');
    if (btnLogout) {
        btnLogout.addEventListener('click', () => {
            localStorage.removeItem('token');
            localStorage.removeItem('auth_token');
            localStorage.removeItem('usuario');
            window.location.href = '/login';
        });
    }

    let conversaciones = [];
    let destinatarioActivoId = null;
    let pollingInterval = null;

    const listaEl = document.getElementById('listaConversaciones');
    const buscarInput = document.getElementById('buscarConversacion');
    const chatHeader = document.getElementById('chatHeader');
    const chatEmptyState = document.getElementById('chatEmptyState');
    const mensajesContainer = document.getElementById('mensajesContainer');
    const formEnviar = document.getElementById('formEnviarMensaje');
    const inputMensaje = document.getElementById('inputMensaje');

    const headersAuth = {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json',
        'Content-Type': 'application/json'
    };

    function formatearHora(fechaIso) {
        if (!fechaIso) return '';
        const fecha = new Date(fechaIso);
        return fecha.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }

    function renderAvatar(avatarPath, nombre, size = 42) {
        if (avatarPath) {
            const url = avatarPath.startsWith('http') || avatarPath.startsWith('/storage/')
                ? avatarPath
                : `/storage/${avatarPath}`;
            return `<img src="${url}" alt="${nombre}" class="rounded-circle w-100 h-100" style="object-fit: cover;">`;
        }
        return (nombre ? nombre.charAt(0).toUpperCase() : '?');
    }

    function renderListaConversaciones(filtro = '') {
        const filtradas = conversaciones.filter(c =>
            (c.nombre || '').toLowerCase().includes(filtro.toLowerCase())
        );

        if (filtradas.length === 0) {
            listaEl.innerHTML = `
                <div class="text-center py-5 px-3 text-muted">
                    <p class="small mb-0">No hay conversaciones disponibles.</p>
                </div>`;
            return;
        }

        listaEl.innerHTML = filtradas.map(conv => {
            const activa = String(conv.interlocutor_id) === String(destinatarioActivoId) ? 'active' : '';
            const badge = conv.no_leidos > 0
                ? `<span class="badge bg-primary rounded-pill ms-1">${conv.no_leidos}</span>`
                : '';

            return `
                <div class="list-group-item list-group-item-action border-0 border-bottom py-3 px-3 conversacion-item ${activa}"
                     data-id="${conv.interlocutor_id}">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold flex-shrink-0"
                             style="width: 44px; height: 44px; overflow: hidden;">
                            ${renderAvatar(conv.avatar, conv.nombre, 44)}
                        </div>
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-bold text-truncate">${conv.nombre}</h6>
                                <small class="text-muted ms-2">${formatearHora(conv.fecha)}</small>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted text-truncate">${conv.ultimo_mensaje || ''}</small>
                                ${badge}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        listaEl.querySelectorAll('.conversacion-item').forEach(item => {
            item.addEventListener('click', () => {
                seleccionarChat(item.dataset.id);
            });
        });
    }

const cacheUsuarios = {};

    async function cargarConversaciones() {
        try {
            const res = await fetch('/api/conversaciones', { headers: headersAuth });
            const data = await res.json();
            if (res.ok && data.success) {
                const listaCruda = data.conversaciones || [];

                conversaciones = await Promise.all(listaCruda.map(async (conv) => {
                    const id = String(conv.interlocutor_id);
                    if (!cacheUsuarios[id]) {
                        cacheUsuarios[id] = await cargarInfoDestinatarioNuevo(id);
                    }
                    return {
                        ...cacheUsuarios[id],
                        ultimo_mensaje: conv.ultimo_mensaje,
                        fecha: conv.fecha,
                        no_leidos: conv.no_leidos
                    };
                }));

                renderListaConversaciones(buscarInput.value);
            }
        } catch (error) {
            console.error('Error al cargar conversaciones:', error);
        }
    }

    async function cargarInfoDestinatarioNuevo(id) {
        try {
            const res = await fetch(`/api/usuarios/${id}/perfil`, { headers: headersAuth });
            const data = await res.json();
            if (res.ok && data.usuario) {
                const u = data.usuario;
                let nombre = u.email;
                let rol = '';
                if (u.alumno) {
                    nombre = `${u.alumno.nombre} ${u.alumno.apellidoPaterno}`.trim();
                    rol = 'Alumno';
                } else if (u.empresa) {
                    nombre = u.empresa.nombreEmpresa;
                    rol = 'Empresa';
                }
                return {
                    interlocutor_id: String(id),
                    nombre,
                    rol,
                    avatar: u.avatar,
                    ultimo_mensaje: '',
                    fecha: null,
                    no_leidos: 0
                };
            }
        } catch (e) {
            console.error(e);
        }
        return {
            interlocutor_id: String(id),
            nombre: `Usuario #${id}`,
            rol: '',
            avatar: null,
            ultimo_mensaje: '',
            fecha: null,
            no_leidos: 0
        };
    }

    async function seleccionarChat(id, silencioso = false) {
        destinatarioActivoId = String(id);

        let info = conversaciones.find(c => String(c.interlocutor_id) === destinatarioActivoId);
        if (!info) {
            info = await cargarInfoDestinatarioNuevo(destinatarioActivoId);
            conversaciones.unshift(info);
        }

        // Mostrar paneles del chat
        chatEmptyState.classList.add('d-none');
        chatHeader.classList.remove('d-none');
        mensajesContainer.classList.remove('d-none');
        formEnviar.classList.remove('d-none');

        document.getElementById('chatHeaderNombre').textContent = info.nombre;
        document.getElementById('chatHeaderRol').textContent = info.rol || 'Usuario';
        document.getElementById('chatHeaderAvatar').innerHTML = renderAvatar(info.avatar, info.nombre, 42);
        document.getElementById('btnVerPerfilChat').href = `/usuarios/${destinatarioActivoId}/perfil`;

        renderListaConversaciones(buscarInput.value);

        try {
            const res = await fetch(`/api/mensajes/${destinatarioActivoId}`, { headers: headersAuth });
            const data = await res.json();

            if (res.ok && data.success) {
                const estabaAbajo = mensajesContainer.scrollHeight - mensajesContainer.scrollTop <= mensajesContainer.clientHeight + 80;

                if (data.mensajes.length === 0) {
                    mensajesContainer.innerHTML = `
                        <div class="text-center text-muted my-auto py-4">
                            <small>Aún no hay mensajes. ¡Envía el primero!</small>
                        </div>`;
                } else {
                    mensajesContainer.innerHTML = data.mensajes.map(m => {
                        const esMio = String(m.remitente_id) === miId;
                        return `
                            <div class="d-flex flex-column ${esMio ? 'align-items-end' : 'align-items-start'}">
                                <div class="px-3 py-2 shadow-sm ${esMio ? 'burbuja-propia' : 'burbuja-ajena'}">
                                    <div>${escapeHtml(m.mensaje)}</div>
                                    <div class="text-end mt-1" style="font-size: 0.7rem; opacity: 0.8;">
                                        ${formatearHora(m.created_at)}
                                    </div>
                                </div>
                            </div>
                        `;
                    }).join('');
                }

                if (!silencioso || estabaAbajo) {
                    mensajesContainer.scrollTop = mensajesContainer.scrollHeight;
                }

                // Actualizar contador de no leídos en la lista lateral
                info.no_leidos = 0;
                renderListaConversaciones(buscarInput.value);
            }
        } catch (error) {
            console.error('Error al obtener mensajes:', error);
        }
    }

    function escapeHtml(texto) {
        const div = document.createElement('div');
        div.textContent = texto;
        return div.innerHTML;
    }

    // Enviar mensaje
    formEnviar.addEventListener('submit', async (e) => {
        e.preventDefault();
        const texto = inputMensaje.value.trim();
        if (!texto || !destinatarioActivoId) return;

        inputMensaje.value = '';
        inputMensaje.focus();

        try {
            const res = await fetch('/api/mensajes', {
                method: 'POST',
                headers: headersAuth,
                body: JSON.stringify({
                    destinatario_id: destinatarioActivoId,
                    mensaje: texto
                })
            });

            const data = await res.json();
            if (res.ok && data.success) {
                await seleccionarChat(destinatarioActivoId);
                await cargarConversaciones();
            } else {
                alert(data.message || 'No se pudo enviar el mensaje.');
            }
        } catch (error) {
            console.error('Error al enviar mensaje:', error);
        }
    });

    // Filtro de búsqueda en conversaciones
    buscarInput.addEventListener('input', (e) => {
        renderListaConversaciones(e.target.value);
    });

    // Inicialización
    await cargarConversaciones();

    // Si viene ?destinatario=ID en la URL (desde Directorio o Perfil Público), abrir ese chat directo
    const params = new URLSearchParams(window.location.search);
    const destinatarioParam = params.get('destinatario');
    if (destinatarioParam && destinatarioParam !== miId) {
        await seleccionarChat(destinatarioParam);
    }

    // Refresco automático cada 4 segundos para mensajes entrantes
    pollingInterval = setInterval(async () => {
        await cargarConversaciones();
        if (destinatarioActivoId) {
            await seleccionarChat(destinatarioActivoId, true);
        }
    }, 4000);
});