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
        });
    }
});

// 3. Validar que solo se ingresen números en el campo DNI mientras se escribe
const dniInput = document.getElementById("dni");
if (dniInput) {
    dniInput.addEventListener("input", function(e) {
        // Reemplaza cualquier caracter que no sea número por vacío
        this.value = this.value.replace(/[^0-9]/g, '');
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

            /*
            // TODO: Integrar con el endpoint real del servicio
            fetch('/api/circuitoquirurgico/usuarios', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // 'Authorization': 'Bearer ' + token // si usan tokens
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(result => {
                if(result.success) {
                    alert('Usuario creado correctamente');
                    // Aquí pueden redirigir con window.location.href o limpiar el form
                } else {
                    alert('Error desde la API: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error al contactar el servicio:', error);
            });
            */
        }, false)
    })
})()
