document.addEventListener("DOMContentLoaded", function() {
    cargarUsuariosAPI();
});

function cargarUsuariosAPI() {
    const tbody = document.getElementById("tablaUsuariosBody");
    if (!tbody) return;

    console.log("Iniciando petición GET al endpoint de usuarios...");

    // Hacer la petición al endpoint real
    fetch('app/cir-api/endpoints/listar_users-e.php', {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        // Limpiar el estado de carga
        tbody.innerHTML = '';
        
        if(data && data.usuarios && data.usuarios.length > 0) {
            data.usuarios.forEach(usuario => {
                const tr = document.createElement('tr');
                const rolNombre = usuario.rol_nombre || 'Sin rol';
                tr.innerHTML = `
                    <td class="ps-4 py-3 fw-medium">${usuario.nombre} ${usuario.apellido}</td>
                    <td class="py-3">${usuario.email || 'Sin email'}</td>
                    <td class="py-3">
                        <span class="badge bg-light-blue blue rounded-pill px-2 py-1 fw-normal">${rolNombre}</span>
                    </td>
                    <td class="py-3">
                        <span class="badge bg-light-success success rounded-pill px-2 py-1 fw-normal">
                            <i class="fa-solid fa-circle" style="font-size: 0.5rem; vertical-align: middle;"></i> Registrado
                        </span>
                    </td>
                    <td class="py-3 text-secondary">Hoy</td>
                    <td class="py-3 pe-4 text-center">
                        <button class="btn btn-sm btn-link blue me-2" onclick="editarUsuario(${usuario.id_persona})"><i class="bi bi-pencil-square"></i></button>
                        <button class="btn btn-sm btn-link danger" onclick="eliminarUsuario(${usuario.id_persona})"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-secondary">No hay usuarios registrados todavía.</td></tr>';
        }
    })
    .catch(error => {
        console.error("Error al obtener usuarios de la API:", error);
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center py-4 text-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i> Error al conectar con el servicio.
                </td>
            </tr>`;
    });
}
