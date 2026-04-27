const tablero = document.getElementById('tablero');
const nivelDisplay = document.getElementById('nivel');
const puntosDisplay = document.getElementById('puntos');

let cantidadBotones = 0;
let dificultadActual = '2';
let nivel = 0;
let puntos = 0;
let secuenciaMaquina = [];
let secuenciaUsuario = [];
let bloqueado = true;
let timeoutsPendientes = [];

const colores = ['#e74c3c', '#2ecc71', '#3498db', '#f1c40f', '#9b59b6', '#e67e22', '#1abc9c', '#ff6b81', '#ecf0f1'];
let botonesActivos = [];

// Bloqueos de seguridad
document.addEventListener('contextmenu', event => event.preventDefault());
document.onkeydown = function (e) {
    if (e.keyCode == 123) return false;
    if (e.ctrlKey && e.shiftKey && [67, 75, 90, 69].includes(e.keyCode)) return false;
};

// Limpieza de temporizadores activos
function limpiarTimeouts() {
    timeoutsPendientes.forEach(clearTimeout);
    timeoutsPendientes = [];
}

// Función para ajustar la dificultad desde el menú y cambiar botones
function ajustarDificultad(dif) {
    dificultadActual = dif;
    const botones = document.querySelectorAll('#menu button:not(#btn-reiniciar)');
    botones.forEach(btn => btn.classList.remove('activo'));
    const botonActivo = document.querySelector(`#menu button[onclick="ajustarDificultad('${dif}')"]`);
    if (botonActivo) {
        botonActivo.classList.add('activo');
    }
    reiniciarJuego();
}

// Limpieza de anterior juego e inicialización de nuevo
function iniciarJuego() {
    limpiarTimeouts(); 

    tablero.innerHTML = '';
    nivel = 0;
    puntos = 0;
    secuenciaMaquina = [];
    secuenciaUsuario = [];
    botonesActivos = [];
    bloqueado = true;
    
    nivelDisplay.textContent = "0";
    puntosDisplay.textContent = "0";

    if (dificultadActual === '1') {
        cantidadBotones = 4;
        tablero.style.gridTemplateColumns = "repeat(2, 120px)"; 
    } else if (dificultadActual === '2') {
        cantidadBotones = 6;
        tablero.style.gridTemplateColumns = "repeat(3, 120px)";
    } else if (dificultadActual === '3') {
        cantidadBotones = 9;
        tablero.style.gridTemplateColumns = "repeat(3, 120px)";
    }

    // Generar botones en el tablero
    for (let i = 0; i < cantidadBotones; i++) {
        const btn = document.createElement('div');
        btn.classList.add('boton-simon');
        btn.style.backgroundColor = colores[i];
        btn.style.color = colores[i];
        btn.dataset.indice = i;
        
        btn.addEventListener('mousedown', () => manejarClickUsuario(i));
        
        tablero.appendChild(btn);
        botonesActivos.push(btn);
    }

    timeoutsPendientes.push(setTimeout(siguienteNivel, 1000));
}

// Generar el siguiente paso de la secuencia
function siguienteNivel() {
    nivel++;
    nivelDisplay.textContent = nivel;
    secuenciaUsuario = [];
    
    const colorAleatorio = Math.floor(Math.random() * cantidadBotones);
    secuenciaMaquina.push(colorAleatorio);
    
    reproducirSecuencia();
}

// Mostrar la secuencia visual de la máquina
function reproducirSecuencia() {
    bloqueado = true;
    tablero.classList.add('bloqueado');
    
    for (let i = 0; i < secuenciaMaquina.length; i++) {
        const indiceBoton = secuenciaMaquina[i];
        
        // Iluminar cada botón con retraso
        timeoutsPendientes.push(setTimeout(() => {
            iluminarBoton(indiceBoton);
        }, (i + 1) * 600)); 
    }

    // Desbloqueo tras terminar la secuencia
    const tiempoTotal = (secuenciaMaquina.length * 600) + 500;
    timeoutsPendientes.push(setTimeout(() => {
        bloqueado = false;
        tablero.classList.remove('bloqueado');
    }, tiempoTotal));
}

// Iluminar el botón visualmente
function iluminarBoton(indice) {
    const btn = botonesActivos[indice];
    btn.classList.add('iluminado');
    
    // Apagar iluminación
    timeoutsPendientes.push(setTimeout(() => {
        btn.classList.remove('iluminado');
    }, 300));
}

// Control de respuesta del usuario y comparación
function manejarClickUsuario(indice) {
    if (bloqueado) return;

    iluminarBoton(indice);
    secuenciaUsuario.push(indice);

    const pasoActual = secuenciaUsuario.length - 1;

    // Comprobar acierto o fallo
    if (secuenciaUsuario[pasoActual] !== secuenciaMaquina[pasoActual]) {
        terminarJuego();
        return;
    }

    // Sumar puntos por acierto
    let multiplicador = parseInt(dificultadActual);
    puntos += (10 * multiplicador);
    puntosDisplay.textContent = puntos;

    // Verificar si ha completado la secuencia
    if (secuenciaUsuario.length === secuenciaMaquina.length) {
        bloqueado = true; 
        tablero.classList.add('bloqueado');
        
        puntos += (50 * multiplicador);
        puntosDisplay.textContent = puntos;

        timeoutsPendientes.push(setTimeout(siguienteNivel, 1000));
    }
}

// Lógica de derrota y reinicio visual
function terminarJuego() {
    bloqueado = true;
    tablero.classList.add('bloqueado');
    
    // Feedback visual de error
    document.body.style.backgroundColor = '#c0392b';
    timeoutsPendientes.push(setTimeout(() => {
        document.body.style.backgroundColor = '#222f3e';
    }, 300));

    setTimeout(() => {
        alert(`¡Te equivocaste!\n\nNivel alcanzado: ${nivel}\nPuntuación Final: ${puntos}`);
    }, 500);
}

function reiniciarJuego() {
    iniciarJuego();
}

ajustarDificultad('2');