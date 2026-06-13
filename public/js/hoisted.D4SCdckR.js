import "./hoisted.YWnzczOx.js";
import KeenSlider from "https://cdn.jsdelivr.net/npm/keen-slider@6.8.6/+esm";

// Esperar a que el DOM esté listo
document.addEventListener("DOMContentLoaded", () => {
    // Mostrar modal al cargar
    const modal = document.getElementById("modal");
    if (modal) {
   
        modal.classList.remove("hidden");

        const closeModalBtn = document.getElementById("closeModal");
        if (closeModalBtn) {
            closeModalBtn.addEventListener("click", () => {
                modal.classList.add("hidden");
            });
        }

    }


    // Seleccionar el elemento del slider
    const sliderElement = document.querySelector("#keen-slider");
    if (!sliderElement) {
        //console.error("⚠️ No se encontró el elemento #keen-slider en el DOM.");
        return;
    }

    // Configuración del slider
    const slider = new KeenSlider(sliderElement, {
        loop: true,
        slides: {
            origin: "center",
            perView: 1.25,
            spacing: 16,
        },
        breakpoints: {
            "(min-width: 640px)": {
                slides: { perView: 1.5, spacing: 16 },
            },
            "(min-width: 768px)": {
                slides: { perView: 2, spacing: 16 },
            },
            "(min-width: 1024px)": {
                slides: { perView: 3.8, spacing: 24 },
            },
        },
    });

    // Controles del slider (móvil y escritorio)
    const prevBtn = document.getElementById("keen-slider-previous");
    const nextBtn = document.getElementById("keen-slider-next");
    const prevDesktopBtn = document.getElementById("keen-slider-previous-desktop");
    const nextDesktopBtn = document.getElementById("keen-slider-next-desktop");

    if (prevBtn) prevBtn.addEventListener("click", () => slider.prev());
    if (nextBtn) nextBtn.addEventListener("click", () => slider.next());
    if (prevDesktopBtn) prevDesktopBtn.addEventListener("click", () => slider.prev());
    if (nextDesktopBtn) nextDesktopBtn.addEventListener("click", () => slider.next());
   
   
});
