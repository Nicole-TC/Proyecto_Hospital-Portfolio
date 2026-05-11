document.addEventListener("DOMContentLoaded", function() {
    // 1. Configurar la fecha máxima de nacimiento a hoy (no permitir fechas futuras)
    const fechaNacimientoInput = document.getElementById("fecha_nacimiento");
    if (fechaNacimientoInput) {
        const today = new Date().toISOString().split("T")[0];
        fechaNacimientoInput.setAttribute("max", today);
    }

    // 2. Limpiar validaciones previas al hacer click en el botón de Limpiar
    const btnReset = document.querySelector('button[type="reset"]');
    const formUsuario = document.getElementById("formUsuario");
    if(btnReset && formUsuario) {
        btnReset.addEventListener("click", function() {
            formUsuario.classList.remove('was-validated');
            const datosCirujano = document.getElementById('datos_cirujano');
            if (datosCirujano) datosCirujano.style.display = 'none';
        });
    }

    // 2.5 Mostrar campos de cirujano si el rol es cirujano
    const rolSelect = document.getElementById('rol');
    const datosCirujano = document.getElementById('datos_cirujano');
    if (rolSelect && datosCirujano) {
        rolSelect.addEventListener('change', function() {
            const selectedText = this.options[this.selectedIndex].text.toLowerCase();
            if (selectedText.includes('cirujano')) {
                datosCirujano.style.display = 'block';
                // Hacer campos obligatorios si es cirujano
                document.getElementById('especialidad').setAttribute('required', 'true');
                document.getElementById('numero_matricula').setAttribute('required', 'true');
                document.getElementById('tipo_matricula').setAttribute('required', 'true');
            } else {
                datosCirujano.style.display = 'none';
                // Quitar obligatoriedad
                document.getElementById('especialidad').removeAttribute('required');
                document.getElementById('numero_matricula').removeAttribute('required');
                document.getElementById('tipo_matricula').removeAttribute('required');
                // Limpiar campos al ocultar
                document.getElementById('especialidad').value = "";
                document.getElementById('numero_matricula').value = "";
                document.getElementById('tipo_matricula').value = "";
            }
        });
    }

    // 2.6 Cargar opciones de roles y especialidades desde la API
    async function cargarSelects() {
        try {
            const response = await fetch('app/cir-api/endpoints/obtener_roles_especialidades-e.php');
            const result = await response.json();

            console.log("Respuesta de la API (Selects):", result);

            if (result.status === 'success') {
                const rolSelect = document.getElementById('rol');
                const espSelect = document.getElementById('especialidad');

                if (!rolSelect || !espSelect) {
                    console.error("No se encontraron los elementos select en el DOM.");
                    return;
                }

                // Cargar Roles
                result.roles.forEach(rol => {
                    const option = document.createElement('option');
                    option.value = rol.id_rol; 
                    option.text = rol.nombre;  
                    rolSelect.add(option);
                });

                // Cargar Especialidades
                result.especialidades.forEach(esp => {
                    const option = document.createElement('option');
                    option.value = esp.id_especialidad; 
                    option.text = esp.nombre;  
                    espSelect.add(option);
                });
            } else {
                // Si la API devuelve un error, lo mostramos para saber qué falla
                alert("Error de la API: " + result.message);
            }
        } catch (error) {
            console.error("Error al cargar datos iniciales:", error);
            alert("Error crítico al conectar con la API de selects. Revisa la consola.");
        }
    }

    cargarSelects();
});

// 3. Validar DNI en vivo y bloquear formulario si ya existe
const dniInput = document.getElementById("dni");
if (dniInput) {
    dniInput.addEventListener("input", function(e) {
        // Reemplaza cualquier caracter que no sea número por vacío
        this.value = this.value.replace(/[^0-9]/g, '');
    });

    const otrosCampos = ["nombre", "apellido", "fecha_nacimiento", "email", "telefono", "direccion"];
    const btnGuardar = document.querySelector('button[type="submit"]');

    // Evento blur: cuando el usuario termina de escribir y cambia de campo
    dniInput.addEventListener("blur", async function(e) {
        const dniVal = this.value;
        if (dniVal.length >= 7) {
            try {
                const response = await fetch(`app/cir-api/endpoints/verificar_dni-e.php?dni=${dniVal}`);
                const result = await response.json();
                
                if (result.status === 'success' && result.existe === true) {
                    alert("¡Atención! Este DNI ya está registrado en la base de datos.");
                    
                    // Deshabilitar el resto de los campos para evitar que pierda tiempo
                    otrosCampos.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.disabled = true;
                    });
                    if (btnGuardar) btnGuardar.disabled = true;
                    
                } else if (result.status === 'success' && result.existe === false) {
                    // Si el DNI está libre, habilitar todo nuevamente
                    otrosCampos.forEach(id => {
                        const el = document.getElementById(id);
                        if (el) el.disabled = false;
                    });
                    if (btnGuardar) btnGuardar.disabled = false;
                }
            } catch (error) {
                console.error("Error al verificar el DNI:", error);
            }
        }
    });
}

// 4. Validación del formulario y preparación para envío a la API
(() => {
    'use strict'
    const forms = document.querySelectorAll('.needs-validation')
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            // Como es un servicio API, SIEMPRE evitamos el comportamiento por defecto (recarga de página)
            event.preventDefault()
            
            if (!form.checkValidity()) {
                event.stopPropagation()
                form.classList.add('was-validated')
                return; // Si no es válido, cortamos acá
            }
            
            form.classList.add('was-validated')

            // Preparar los datos para el endpoint
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Mostramos por consola lo que se enviará (para pruebas del equipo)
            console.log("Datos listos para enviar al endpoint de Circuito Quirúrgico:", data);

            // Enviar los datos al endpoint que acabamos de crear
            fetch('app/cir-api/endpoints/crear_users-e.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if(result.status === 'success') {
                    // Mostrar alerta de éxito
                    alert(result.message);
                    
                    // Limpiar el formulario y quitar las clases de validación
                    form.reset();
                    form.classList.remove('was-validated');
                    
                    const datosCirujano = document.getElementById('datos_cirujano');
                    if (datosCirujano) datosCirujano.style.display = 'none';
                } else {
                    // Mostrar error que devuelve el endpoint
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error al contactar el servicio:', error);
                alert('Ocurrió un error al intentar conectarse con el servidor.');
            });
        }, false)
    })
})()
