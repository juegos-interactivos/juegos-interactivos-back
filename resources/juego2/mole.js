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
let timeoutTopo = null;
let agujeroActual = null;
let jugando = false;
let primerGolpe = false;

// Bloqueos de seguridad
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function (e) {
    if (e.keyCode == 123) return false;
    if (e.ctrlKey && e.shiftKey && [67, 75, 90, 69].includes(e.keyCode)) return false;
};

// Función para ajustar la dificultad desde el menú y cambiar colores de los botones
function ajustarDificultad(dif) {
    dificultadActual = dif;
    const botones = document.querySelectorAll('#menu button');
    botones.forEach(btn => btn.classList.remove('activo'));
    const botonActivo = document.querySelector(`#menu button[onclick="ajustarDificultad('${dif}')"]`);
    if (botonActivo) {
        botonActivo.classList.add('activo');
    }
    reiniciarJuego();
}

//Limpieza de anterior juego e inicialización de nuevo
function iniciarJuego() {
    tablero.innerHTML = '';
    tiempo = 0;
    puntos = 0;
    escapados = 0;
    golpeados = 0;
    jugando = true;
    primerGolpe = false;
    velocidadActual = 1500;

    clearInterval(intervaloTiempo);
    clearTimeout(timeoutTopo);

    tiempoDisplay.textContent = "0";
    golpeadosDisplay.textContent = "0";
    escapadosDisplay.textContent = "0";
    puntosDisplay.textContent = "0";

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

    // Crear agujeros y topos
    for (let i = 0; i < cantidadAgujeros; i++) {
        const agujero = document.createElement('div');
        agujero.classList.add('agujero');

        const topo = document.createElement('div');
        topo.classList.add('topo');

        const hocico = document.createElement('div');
        hocico.classList.add('topo-hocico');

        topo.appendChild(hocico);
        topo.addEventListener('mousedown', golpearTopo);
        agujero.appendChild(topo);
        tablero.appendChild(agujero);
    }
    aparecerPrimerTopo();
}

// Función para mostrar el primer topo al iniciar el juego
function aparecerPrimerTopo() {
    const agujeros = document.querySelectorAll('.agujero');
    if (agujeros.length === 0) return;
    const azar = Math.floor(Math.random() * agujeros.length);
    agujeroActual = agujeros[azar];
    agujeroActual.classList.add('activo');
}

//Golpear al topo y lógica de puntos, tiempo y dificultad
function golpearTopo() {
    if (!jugando) return;
    const agujeroParent = this.parentElement;
    if (!agujeroParent.classList.contains('activo')) return;

    // Lógica de inicio al primer golpe
    if (!primerGolpe) {
        primerGolpe = true;
        iniciarCronometro();
    }

    // Acertar topo
    agujeroParent.classList.remove('activo');
    clearTimeout(timeoutTopo);
    golpeados++;
    golpeadosDisplay.textContent = golpeados;
    let multiplicador = parseInt(dificultadActual);
    puntos += (50 * multiplicador) + Math.floor(tiempo / 2);
    puntosDisplay.textContent = puntos;

    // Continuar el ciclo
    setTimeout(mostrarTopo, 300);
}

function iniciarCronometro() {
    intervaloTiempo = setInterval(() => {
        tiempo++;
        tiempoDisplay.textContent = tiempo;
        if (tiempo % 5 === 0 && velocidadActual > 400) {
            velocidadActual -= 100;
        }
    }, 1000);
}

// Mostrar topo en un agujero aleatorio
function mostrarTopo() {
    if (!jugando) return;

    const agujeros = document.querySelectorAll('.agujero');
    let indiceAleatorio;
    do {
        indiceAleatorio = Math.floor(Math.random() * agujeros.length);
    } while (agujeros[indiceAleatorio] === agujeroActual && cantidadAgujeros > 1);

    agujeroActual = agujeros[indiceAleatorio];
    agujeroActual.classList.add('activo');

    timeoutTopo = setTimeout(() => {
        if (agujeroActual.classList.contains('activo')) {
            agujeroActual.classList.remove('activo');
            topoEscapa();
        }
    }, velocidadActual);
}

// Lógica para topo que escapa y penalización de puntos
function topoEscapa() {
    if (!jugando) return;

    escapados++;
    escapadosDisplay.textContent = escapados;
    puntos = Math.max(0, puntos - (25 * parseInt(dificultadActual)));
    puntosDisplay.textContent = puntos;

    if (escapados >= 3) {
        terminarJuego();
    } else {
        setTimeout(mostrarTopo, 500);
    }
}

// Perder y mostrar estadísticas finales
function terminarJuego() {
    jugando = false;
    clearInterval(intervaloTiempo);
    clearTimeout(timeoutTopo);

    setTimeout(() => {
        alert(`¡Partida Terminada!\n\nTopos golpeados: ${golpeados}\nTiempo sobrevivido: ${tiempo}s\nPuntuación Final: ${puntos}`);
    }, 100);
}

function reiniciarJuego() {
    iniciarJuego();
}

ajustarDificultad('2');