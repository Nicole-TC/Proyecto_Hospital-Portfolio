const BASE_URL = document.querySelector('meta[name="base-url"]')?.content ?? '';

const campos = {
    'pacientes-circuito': { key: 'pacientes_circuito', fallback: '—' },
    'aptos-operar': { key: 'aptos_operar', fallback: '—' },
    'alertas-demora': { key: 'alertas_demora', fallback: '—' },
};

// Estado de carga
Object.keys(campos).forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = '...';
});

fetch(`${BASE_URL}/admin/app/cir-api/endpoints/dashboard-e.php?action=metricas`)
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (!data.ok) {
            throw new Error(data.error ?? 'Error desconocido del servidor');
        }
        Object.entries(campos).forEach(([id, { key, fallback }]) => {
            const el = document.getElementById(id);
            if (!el) return;
            const valor = data.data[key];
            el.textContent = (valor !== null && valor !== undefined) ? valor : fallback;
        });
    })
    .catch(error => {
        console.error('Error al cargar métricas:', error);
        Object.keys(campos).forEach(id => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = 'Error';
                el.title = error.message;
            }
        });
    });