// Archivo: stress.js
const URL = 'https://subastas.ovismart.app/'; // Cambia esto por la URL que quieras probar
const TOTAL_REQUESTS = 10000;
const BATCH_SIZE = 500; // Peticiones simultáneas por bloque

async function hacerPeticion(id) {
    const inicio = Date.now();
    try {
        const respuesta = await fetch(URL, { method: 'GET' });
        const duracion = Date.now() - inicio;
        return { id, status: respuesta.status, duracion, error: null };
    } catch (error) {
        const duracion = Date.now() - inicio;
        return { id, status: 'FAIL', duracion, error: error.message };
    }
}

async function ejecutar() {
    console.log(`🚀 Iniciando ${TOTAL_REQUESTS} peticiones hacia: ${URL}\n`);
    let exitosas = 0;
    let fallidas = 0;
    const tiempoInicioGlobal = Date.now();

    for (let i = 0; i < TOTAL_REQUESTS; i += BATCH_SIZE) {
        const lote = [];
        const limite = Math.min(i + BATCH_SIZE, TOTAL_REQUESTS);

        for (let j = i; j < limite; j++) {
            lote.push(hacerPeticion(j + 1));
        }

        const resultados = await Promise.all(lote);

        resultados.forEach(res => {
            if (res.status === 200 || (typeof res.status === 'number' && res.status < 400)) {
                exitosas++;
            } else {
                fallidas++;
            }
        });

        console.log(`Procesadas: ${limite} / ${TOTAL_REQUESTS} peticiones...`);
    }

    const tiempoTotal = (Date.now() - tiempoInicioGlobal) / 1000;

    console.log('\n========================================');
    console.log('           RESULTADOS FINALES           ');
    console.log('========================================');
    console.log(`⏱️  Tiempo total: ${tiempoTotal} segundos`);
    console.log(`✅ Exitosas (2xx/3xx): ${exitosas}`);
    console.log(`❌ Fallidas / Bloqueadas: ${fallidas}`);
    console.log('========================================');
}

ejecutar();