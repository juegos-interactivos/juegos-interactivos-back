const tablero = document.getElementById('tablero');
const tiempoDisplay = document.getElementById('tiempo');
const intentosDisplay = document.getElementById('intentos');
const puntosDisplay = document.getElementById('puntos');

let cantidadTarjetas = 0;
let cartasGiradas = [];
let bloqueado = false;
let parejasEncontradas = 0;
let tiempo = 0; 
let intentos = 0;
let puntos = 0; // Esta variable ahora sí bajará de 0 internamente
let intervaloTiempo = null;
let primerclick = false;
let dificultadActual = '2';

// Bloqueos de seguridad
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function (e) {
    if (e.keyCode == 123) return false;
    if (e.ctrlKey && e.shiftKey && [67, 75, 90, 69].includes(e.keyCode)) return false;
};

// Función para ajustar la dificultad desde el menú
function ajustarDificultad(dif) {
    dificultadActual = dif;
    reiniciarJuego();
}
function iniciarJuego() {
    tablero.innerHTML = '';
    cartasGiradas = [];
    bloqueado = false;
    parejasEncontradas = 0;
    tiempo = 0;
    intentos = 0;
    puntos = 0; 
    primerclick = false;
    clearInterval(intervaloTiempo);

    tiempoDisplay.textContent = "0";
    intentosDisplay.textContent = "0";
    puntosDisplay.textContent = "0";

    if (dificultadActual === '1') {
        cantidadTarjetas = 6;
        tablero.style.maxWidth = "500px";
    } else if (dificultadActual === '2') {
        cantidadTarjetas = 12;
        tablero.style.maxWidth = "680px";
    } else if (dificultadActual === '3') {
        cantidadTarjetas = 24;
        tablero.style.maxWidth = "1000px";
    }

    // Generar números para las tarjetas
    let numeros = [];
    for (let i = 1; i <= cantidadTarjetas / 2; i++) {
        numeros.push(i, i);
    }
    numeros.sort(() => Math.random() - 0.5);

    numeros.forEach(numero => {
        const tarjeta = document.createElement('div');
        tarjeta.classList.add('tarjeta');
        tarjeta.dataset.valor = numero;
        tarjeta.innerHTML = `
            <div class="frontal"></div>
            <div class="trasera"><img src="imagenes/imagen${numero}.jpg" alt="Pareja ${numero}"></div>
        `;
        tarjeta.addEventListener('click', girarTarjeta);
        tablero.appendChild(tarjeta);
    });
}

// Marcador de puntos
function actualizarDisplayPuntos() {
    puntosDisplay.textContent = puntos > 0 ? Math.round(puntos) : 0;
}

// Girar tarjeta y lógica de comparación
function girarTarjeta() {
    if (bloqueado || this.classList.contains('clicked') || this === cartasGiradas[0]) return;

    // Iniciar el temporizador en el primer click
    if (!primerclick) {
        primerclick = true;
        intervaloTiempo = setInterval(() => {
            tiempo++;
            tiempoDisplay.textContent = tiempo;
            puntos -= parseInt(dificultadActual);
            actualizarDisplayPuntos();
        }, 1000);
    }

    // Mostrar la tarjeta
    this.classList.add('clicked');
    cartasGiradas.push(this);

    // Si ya hay dos tarjetas giradas, comparar
    if (cartasGiradas.length === 2) {
        bloqueado = true;
        intentos++; 
        intentosDisplay.textContent = intentos;
        
        let [t1, t2] = cartasGiradas;
        let multiplicador = parseInt(dificultadActual);

        if (t1.dataset.valor === t2.dataset.valor) {
            // ACIERTO: Suma puntos sobre el valor real (aunque sea negativo)
            puntos += (150 * multiplicador);
            actualizarDisplayPuntos();

            cartasGiradas = [];
            bloqueado = false;
            parejasEncontradas++;

            // Verificar si se han encontrado todas las parejas
            if (parejasEncontradas === cantidadTarjetas / 2) {
                clearInterval(intervaloTiempo);
                setTimeout(() => {
                    let puntuacionFinal = puntos > 0 ? Math.round(puntos) : 0;
                    alert(`¡Felicidades!\n\nTiempo: ${tiempo}s\nIntentos: ${intentos}\nPuntos Finales: ${puntuacionFinal}`);
                }, 500);
            }
        } else {
            // FALLO: Resta puntos (Dificultad 1=25, 2=50, 3=75)
            puntos -= (25 * multiplicador);
            actualizarDisplayPuntos();
            setTimeout(() => {
                t1.classList.remove('clicked');
                t2.classList.remove('clicked');
                cartasGiradas = [];
                bloqueado = false;
            }, 1000);
        }
    }
}

function reiniciarJuego() {
    iniciarJuego();
}
iniciarJuego();