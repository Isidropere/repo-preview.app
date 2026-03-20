// Mostrar loader
window.mostrarLoader = function() {
    const loader = document.getElementById("globalLoader");
    if (loader) loader.classList.remove("hidden");
};

// Ocultar loader
window.ocultarLoader = function() {
    const loader = document.getElementById("globalLoader");
    if (loader) loader.classList.add("hidden");
};

// Wrapper para procesos asíncronos
window.conProcesando = async function(asyncFn) {
    try {
        mostrarLoader();
        await asyncFn();
    } catch (err) {
        console.error(err);
        alert("❌ Ocurrió un error en el proceso");
    } finally {
        ocultarLoader();
    }
};