const tablero = document.getElementById('tablero');
const tiempoDisplay = document.getElementById('tiempo');
const golpeadosDisplay = document.getElementById('golpeados');
const escapadosDisplay = document.getElementById('escapados');
const puntosDisplay = document.getElementById('puntos');

let cantidadAgujeros = 0;
let dificultadActual = '2';
let tiempo = 0;
let puntos = 0;
let escapados = 0;
let golpeados = 0;
let velocidadActual = 1500;
let intervaloTiempo = null;
let jugando = false;
let primerGolpe = false;
let gatoIniciado = false;
let segundoTopoIniciado = false;

// Bloqueos de seguridad
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function (e) {
    if (e.keyCode == 123) return false;
    if (e.ctrlKey && e.shiftKey && [67, 75, 90, 69].includes(e.keyCode)) return false;
};

function ajustarDificultad(dif) {
    dificultadActual = dif;
    const botones = document.querySelectorAll('#menu button:not(#btn-reiniciar)');
    botones.forEach(btn => btn.classList.remove('activo'));
    const botonActivo = document.querySelector(`#menu button[onclick="ajustarDificultad('${dif}')"]`);
    if (botonActivo) botonActivo.classList.add('activo');
    reiniciarJuego();
}

function iniciarJuego() {
    tablero.innerHTML = '';
    tiempo = 0; puntos = 0; escapados = 0; golpeados = 0;
    jugando = true; primerGolpe = false;
    velocidadActual = 1500;
    gatoIniciado = false; segundoTopoIniciado = false;

    clearInterval(intervaloTiempo);
    tiempoDisplay.textContent = "0"; golpeadosDisplay.textContent = "0";
    escapadosDisplay.textContent = "0"; puntosDisplay.textContent = "0";

    // Configuración de Grid
    if (dificultadActual === '1') {
        cantidadAgujeros = 4;
        tablero.style.gridTemplateColumns = "repeat(2, 100px)";
    } else if (dificultadActual === '2') {
        cantidadAgujeros = 9;
        tablero.style.gridTemplateColumns = "repeat(3, 100px)";
    } else if (dificultadActual === '3') {
        cantidadAgujeros = 16;
        tablero.style.gridTemplateColumns = "repeat(4, 100px)";
    }

    for (let i = 0; i < cantidadAgujeros; i++) {
        const agujero = document.createElement('div');
        agujero.classList.add('agujero');

        // Crear Topo
        const topo = document.createElement('div');
        topo.classList.add('topo');
        const hocico = document.createElement('div');
        hocico.classList.add('topo-hocico');
        topo.appendChild(hocico);
        topo.addEventListener('mousedown', golpearTopo);

        // Crear Gato
        const gato = document.createElement('div');
        gato.classList.add('gato');
        const caraGato = document.createElement('div');
        caraGato.classList.add('gato-cara');
        const ojosGato = document.createElement('div');
        ojosGato.classList.add('gato-ojos');
        gato.appendChild(caraGato);
        gato.appendChild(ojosGato);
        gato.addEventListener('mousedown', golpearGato);

        // Colocar ambos en el agujero
        agujero.appendChild(topo);
        agujero.appendChild(gato);
        tablero.appendChild(agujero);
    }
    aparecerPrimerTopo();
}

function iniciarCronometro() {
    intervaloTiempo = setInterval(() => {
        tiempo++;
        tiempoDisplay.textContent = tiempo;

        // Acelerar juego
        if (tiempo % 5 === 0 && velocidadActual > 400) velocidadActual -= 50;

        // A los 10 segundos aparece el Gato
        if (tiempo === 10 && !gatoIniciado) {
            gatoIniciado = true;
            mostrarEntidad('gato', 'gato1');
        }

        // A los 20 segundos aparece el 2º topo (Solo Normal y Difícil)
        if (tiempo === 20 && !segundoTopoIniciado && (dificultadActual === '2' || dificultadActual === '3')) {
            segundoTopoIniciado = true;
            mostrarEntidad('topo', 'topo2');
        }
    }, 1000);
}

// Función para buscar casillas vacías
function obtenerAgujeroLibre() {
    const agujeros = Array.from(document.querySelectorAll('.agujero'));
    const libres = agujeros.filter(a => !a.classList.contains('activo-topo') && !a.classList.contains('activo-gato'));
    if (libres.length === 0) return null;
    return libres[Math.floor(Math.random() * libres.length)];
}

function aparecerPrimerTopo() {
    const libre = obtenerAgujeroLibre();
    if (!libre) return;
    libre.classList.add('activo-topo');
    libre.dataset.identificador = 'topo1';
}

// Controlador de aparición de topos y gatos
function mostrarEntidad(tipo, identificador) {
    if (!jugando) return;

    const libre = obtenerAgujeroLibre();
    if (!libre) {
        setTimeout(() => mostrarEntidad(tipo, identificador), 300);
        return;
    }

    const claseActiva = `activo-${tipo}`;
    libre.classList.add(claseActiva);
    libre.dataset.identificador = identificador;

    // Timeout para que se esconda solo
    const tId = setTimeout(() => {
        if (libre.classList.contains(claseActiva)) {
            libre.classList.remove(claseActiva);

            if (tipo === 'topo') {
                topoEscapa(libre, identificador);
            } else {
                const desincronizacion = 1000 + (Math.random() * 1000);
                setTimeout(() => mostrarEntidad('gato', identificador), desincronizacion);
            }
        }
    }, velocidadActual);
    libre.dataset.timeoutId = tId;
}

// Controladores de golpeo
function golpearTopo() {
    if (!jugando) return;
    const agujero = this.parentElement;
    if (!agujero.classList.contains('activo-topo')) return;
    if (!primerGolpe) { primerGolpe = true; iniciarCronometro(); }
    clearTimeout(agujero.dataset.timeoutId);
    agujero.classList.remove('activo-topo');

    aplicarFeedback(agujero, 'acierto');

    golpeados++;
    golpeadosDisplay.textContent = golpeados;
    puntos += (50 * parseInt(dificultadActual)) + Math.floor(tiempo / 2);
    puntosDisplay.textContent = puntos;

    // Volver a sacarlo con una ligera desincronización aleatoria (300-600ms)
    const ident = agujero.dataset.identificador;
    const desincronizacion = 300 + (Math.random() * 300);
    setTimeout(() => mostrarEntidad('topo', ident), desincronizacion);
}

function golpearGato() {
    if (!jugando) return;
    const agujero = this.parentElement;
    if (!agujero.classList.contains('activo-gato')) return;
    clearTimeout(agujero.dataset.timeoutId);
    agujero.classList.remove('activo-gato');
    aplicarFeedback(agujero, 'fallo');

    // Penalización por golpear al gato
    puntos -= (100 * parseInt(dificultadActual));
    if (puntos < 0) puntos = 0;
    puntosDisplay.textContent = puntos;

    const ident = agujero.dataset.identificador;
    const desincronizacion = 1000 + (Math.random() * 1000);
    setTimeout(() => mostrarEntidad('gato', ident), desincronizacion);
}

function topoEscapa(agujero, identificador) {
    if (!jugando) return;
    escapados++;
    escapadosDisplay.textContent = escapados;
    aplicarFeedback(agujero, 'fallo');
    puntos = Math.max(0, puntos - (25 * parseInt(dificultadActual)));
    puntosDisplay.textContent = puntos;

    //Terminar el juego si se escapan 3 topos
    if (escapados >= 3) {
        terminarJuego();
    } else {
        const desincronizacion = 500 + (Math.random() * 400);
        setTimeout(() => mostrarEntidad('topo', identificador), desincronizacion);
    }
}

// Aplicar feedback visual de acierto o fallo
function aplicarFeedback(agujero, tipo) {
    agujero.classList.add(tipo);
    setTimeout(() => {
        agujero.classList.remove(tipo);
    }, 200);
}

// Función para terminar el juego
function terminarJuego() {
    jugando = false;
    clearInterval(intervaloTiempo);

    // Limpiamos la mesa para que no haya topos tras perder
    document.querySelectorAll('.agujero').forEach(a => {
        a.classList.remove('activo-topo', 'activo-gato');
        clearTimeout(a.dataset.timeoutId);
    });

    // Mostrar resumen final
    setTimeout(() => {
        alert(`¡Partida Terminada!\n\nTopos golpeados: ${golpeados}\nTiempo sobrevivido: ${tiempo}s\nPuntuación Final: ${puntos}`);
    }, 100);
}

function reiniciarJuego() {
    iniciarJuego();
}

ajustarDificultad('2');