// api-simulada.js - Simula las respuestas de la API sin PHP

// Datos iniciales simulados
const datosSimulados = {
    equipo: [
        {
            id_equipo: 1,
            nombre: "Scuderia Alpha Racing",
            pais: "Italia",
            fundacion: 2020,
            presupuesto_anual: 150000000,
            director_tecnico: "James Wilson",
            sede_principal: "Marranello, Italia"
        }
    ],
    pilotos: [
        {
            id_piloto: 1,
            id_equipo: 1,
            nombre: "Carlos Rodríguez",
            nacionalidad: "España",
            fecha_nacimiento: "1995-06-15",
            licencia_fia: "FIA-001",
            tipo_piloto: "Titular",
            numero_competition: 14,
            experiencia_anos: 8,
            sueldo_base: 5000000,
            fecha_contrato_inicio: "2023-01-01",
            fecha_contrato_fin: "2025-12-31"
        }
    ],
    autos: [
        {
            chasis_auto: "CH-001",
            id_equipo: 1,
            modelo: "Alpha-RS25",
            motor: "V6 Turbo Híbrido 1.6L",
            año: 2024,
            tipo_auto: "Competencia",
            numero_competition: 14,
            estado_actual: "Disponible",
            fecha_fabricacion: "2024-01-15",
            especificaciones_tecnicas: "Alerón trasero ajustable, suspensión push-rod"
        }
    ]
};

// Simulación de fetch
function simularFetch(url, options) {
    return new Promise((resolve) => {
        setTimeout(() => {
            const urlObj = new URL(url, window.location.origin);
            const params = urlObj.searchParams;
            const action = params.get('action');
            const id = params.get('id');
            const chasis = params.get('chasis');
            
            let response = { success: false, message: 'Acción no válida' };
            
            if (options.method === 'GET') {
                switch(action) {
                    case 'get_equipo':
                        response = { success: true, data: datosSimulados.equipo };
                        break;
                    case 'get_pilotos':
                        response = { success: true, data: datosSimulados.pilotos };
                        break;
                    case 'get_piloto':
                        const piloto = datosSimulados.pilotos.find(p => p.id_piloto === parseInt(id));
                        response = piloto ? 
                            { success: true, data: piloto } : 
                            { success: false, message: 'Piloto no encontrado' };
                        break;
                    case 'get_autos':
                        response = { success: true, data: datosSimulados.autos };
                        break;
                    case 'get_auto':
                        const auto = datosSimulados.autos.find(a => a.chasis_auto === chasis);
                        response = auto ? 
                            { success: true, data: auto } : 
                            { success: false, message: 'Auto no encontrado' };
                        break;
                }
            } else if (options.method === 'POST') {
                // Simular procesamiento de formularios
                const formData = options.body ? 
                    Object.fromEntries(new URLSearchParams(options.body)) : {};
                
                switch(action) {
                    case 'insert_piloto':
                        const nuevoIdPiloto = Math.max(...datosSimulados.pilotos.map(p => p.id_piloto)) + 1;
                        const nuevoPiloto = {
                            id_piloto: nuevoIdPiloto,
                            id_equipo: parseInt(formData.id_equipo || 1),
                            nombre: formData.nombre,
                            nacionalidad: formData.nacionalidad,
                            fecha_nacimiento: formData.fecha_nacimiento,
                            licencia_fia: formData.licencia_fia,
                            tipo_piloto: formData.tipo_piloto,
                            numero_competition: formData.numero_competition ? parseInt(formData.numero_competition) : null,
                            experiencia_anos: parseInt(formData.experiencia_anos || 0),
                            sueldo_base: parseFloat(formData.sueldo_base || 0),
                            fecha_contrato_inicio: formData.fecha_contrato_inicio || null,
                            fecha_contrato_fin: formData.fecha_contrato_fin || null
                        };
                        datosSimulados.pilotos.push(nuevoPiloto);
                        response = { success: true, message: 'Piloto insertado correctamente' };
                        break;
                        
                    case 'update_piloto':
                        const indexPiloto = datosSimulados.pilotos.findIndex(p => p.id_piloto === parseInt(id));
                        if (indexPiloto !== -1) {
                            datosSimulados.pilotos[indexPiloto] = {
                                ...datosSimulados.pilotos[indexPiloto],
                                nombre: formData.nombre,
                                nacionalidad: formData.nacionalidad,
                                fecha_nacimiento: formData.fecha_nacimiento,
                                licencia_fia: formData.licencia_fia,
                                tipo_piloto: formData.tipo_piloto,
                                numero_competition: formData.numero_competition ? parseInt(formData.numero_competition) : null,
                                experiencia_anos: parseInt(formData.experiencia_anos || 0),
                                sueldo_base: parseFloat(formData.sueldo_base || 0),
                                fecha_contrato_inicio: formData.fecha_contrato_inicio || null,
                                fecha_contrato_fin: formData.fecha_contrato_fin || null
                            };
                            response = { success: true, message: 'Piloto actualizado correctamente' };
                        } else {
                            response = { success: false, message: 'Piloto no encontrado' };
                        }
                        break;
                }
            }
            
            resolve({
                json: () => Promise.resolve(response),
                ok: response.success
            });
        }, 500); // Simular latencia de red
    });
}

// Reemplazar fetch global para simulación
window.fetch = function(url, options = {}) {
    // Si es una URL de nuestra API simulada
    if (url.includes('api.php')) {
        return simularFetch(url, options);
    }
    // Para otras URLs, usar fetch normal si existe
    return originalFetch ? originalFetch(url, options) : Promise.reject('Fetch no disponible');
};

// Guardar fetch original si existe
const originalFetch = window.fetch;