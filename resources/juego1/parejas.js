const tablero = document.getElementById('tablero');
let cantidadTarjetas = 0;
let cartasGiradas = [];
let bloqueado = false;
let parejasEncontradas = 0;


//Bloquear herramientas para hacer trampa
// Bloquear click derecho
document.addEventListener('contextmenu', event => event.preventDefault());

// Bloquear F12 y otros atajos
document.onkeydown = function(e) {
    if (e.keyCode == 123) { // F12
        return false;
    }
    if (e.ctrlKey && e.shiftKey && e.keyCode == 'C'.charCodeAt(0)) { // Ctrl+Shift+C
        return false;
    }
    if (e.ctrlKey && e.shiftKey && e.keyCode == 'K'.charCodeAt(0)) { // Ctrl+Shift+K
        return false;
    }
    if (e.ctrlKey && e.shiftKey && e.keyCode == 'Z'.charCodeAt(0)) { // Ctrl+Shift+Z
        return false;
    }
    if (e.ctrlKey && e.shiftKey && e.keyCode == 'E'.charCodeAt(0)) { // Ctrl+Shift+E
        return false;
    }
};

// Ajustamos cantidad y el ancho del tablero para forzar las líneas
let dificultad = prompt('Selecciona: 1(6 cartas), 2(12 cartas), 3(24 cartas)');

while (dificultad !== '1' && dificultad !== '2' && dificultad !== '3') {
    dificultad = prompt('Opción no válida. Selecciona: 1(6 cartas), 2(12 cartas), 3(24 cartas)');
}
if (dificultad === '1') {
    cantidadTarjetas = 6;
    tablero.style.maxWidth = "500px"; // Caben 3 por línea (3x2)
} else if (dificultad === '2') {
    cantidadTarjetas = 12;
    tablero.style.maxWidth = "680px"; // Caben 4 por línea (4x3)
} else if (dificultad === '3') {
    cantidadTarjetas = 24;
    tablero.style.maxWidth = "1000px"; // Caben 6 por línea (6x4)
}

// Lógica de creación de cartas y asignar aleatoriamente
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
        <div class="trasera">${numero}</div>
    `;

    tarjeta.addEventListener('click', girarTarjeta);
    tablero.appendChild(tarjeta);
});

// Lógica para girar tarjetas y verificar que coincidan
function girarTarjeta() {
    if (bloqueado || this.classList.contains('clicked') || this === cartasGiradas[0]) return;

    this.classList.add('clicked');
    cartasGiradas.push(this);

    if (cartasGiradas.length === 2) {
        bloqueado = true;
        let [t1, t2] = cartasGiradas;

        if (t1.dataset.valor === t2.dataset.valor) {
            cartasGiradas = [];
            bloqueado = false;
        } else {
            setTimeout(() => {
                t1.classList.remove('clicked');
                t2.classList.remove('clicked');
                cartasGiradas = [];
                bloqueado = false;
            }, 1000);
        }
    }
}