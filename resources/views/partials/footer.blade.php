<footer class="mt-auto w-full shadow-2xl bg-white border-t border-gray-100">
    <div class="mt-auto w-full max-w-[85rem] py-8 px-4 sm:px-6 lg:px-8 mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6">

            {{-- Logo --}}
            <div class="col-span-full lg:col-span-1 flex items-center py-2">
                <a class="flex-none text-xl font-semibold" href="/" aria-label="Brand">
                    <img src="/imgs/logoTypes/logoFooter.png"
                         style="height:48px;object-fit:contain;transition:transform .3s ease, filter .3s ease;cursor:pointer;"
                         onmouseover="this.style.transform='scale(1.1)';this.style.filter='brightness(1.15)'"
                         onmouseout="this.style.transform='scale(1)';this.style.filter='brightness(1)'"
                         alt="Cambialord" loading="lazy">
                </a>
            </div>

            {{-- Secciones --}}
            <div class="col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Secciones</h4>
                <div class="grid space-y-2 text-sm">
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="/">Inicio</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('about') }}">Sobre Nosotros</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('cont') }}">Contáctanos</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('envios') }}">Información de envíos</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('empleos') }}">Empleos</a></p>
                </div>
            </div>

            {{-- Ayuda e información --}}
            <div class="col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Ayuda</h4>
                <div class="grid space-y-2 text-sm">
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('responsabilidad') }}">Responsabilidad social</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('realizar-intercambio') }}">¿Cómo realizar un intercambio?</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('como-vender') }}">¿Cómo vender?</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('realizar-compra') }}">¿Cómo realizar una compra?</a></p>
                </div>
            </div>

            {{-- Legal --}}
            <div class="col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Políticas y Legal</h4>
                <div class="grid space-y-2 text-sm">
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('legal.terminos') }}">Términos y Condiciones</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('legal.privacidad') }}">Política de Privacidad</a></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('legal.devoluciones') }}">Devoluciones y Cancelaciones</a></p>
                </div>
            </div>

            {{-- Buscador --}}
            <div class="col-span-2 lg:col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Buscar</h4>
                <form method="GET" action="{{ route('items.search_header') }}">
                    <div class="flex flex-col gap-2 sm:flex-row bg-white rounded-lg border border-gray-200 p-1">
                        <input type="search" name="q" class="py-1.5 px-3 block w-full border-0 rounded-lg text-xs focus:ring-0 focus:outline-none" placeholder="Buscar productos...">
                        <button type="submit" class="py-1.5 px-3 inline-flex justify-center items-center gap-x-2 text-xs font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary transition-colors">
                            Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Información de contacto, dirección y tarjetas aceptadas (Requisitos AZUL) --}}
        <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-between items-center gap-6 text-xs text-gray-500">
            <div class="space-y-1.5 text-center md:text-left">
                <p class="font-semibold text-gray-800 text-sm">Cámbialo RD</p>
                <p>Dirección permanente: Napoleón Bonaparte, Manzana T, Edificio 21, Res. Pablo Mella Morales II, Santo Domingo, República Dominicana</p>
                <p>Soporte al Cliente: Teléfono: <a href="tel:8299634839" class="hover:text-primary font-semibold transition-colors">(829) 963-4839</a> | Correo: <a href="mailto:cambialord.com@gmail.com" class="hover:text-primary font-semibold transition-colors">cambialord.com@gmail.com</a></p>
            </div>
            <div class="flex flex-col items-center md:items-end gap-2">
                <span class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Tarjetas y Pagos Aceptados</span>
                <div class="flex items-center gap-4 flex-wrap justify-center md:justify-end">
                    <img src="/imgs/Visa_Brandmark_Blue_RGB_2021.png" alt="Visa" class="h-10 object-contain">
                    <img src="/imgs/mastercard-logo.png" alt="Mastercard" class="h-10 object-contain">
                    <img src="/imgs/visa-secure_blu_2021_dkbg.png" alt="Visa Secure" class="h-10 object-contain">
                    <img src="/imgs/mastercardidentitycheck.png" alt="Mastercard Identity Check" class="h-10 object-contain">
                    <div class="flex items-center bg-blue-600 text-white font-black px-3 py-2 rounded-lg text-xs tracking-widest uppercase shadow-sm select-none h-10">
                        AZUL
                    </div>
                </div>
            </div>
        </div>

        {{-- Copyright + Redes sociales --}}
        <div class="mt-6 pt-4 border-t border-gray-50 grid gap-y-2 sm:gap-y-0 sm:flex sm:justify-between sm:items-center">
            <div class="flex justify-between items-center">
                <p class="text-xs text-gray-400">© {{ date('Y') }} Cámbialo RD. Todos los derechos reservados.</p>
            </div>
            <div class="flex items-center gap-1">
                <a class="size-8 inline-flex justify-center items-center text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-gray-100 transition-colors" href="https://www.instagram.com/cambialordo/" target="_blank" rel="noopener noreferrer" title="Instagram">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-primary hover:fill-hoverPrimary" viewBox="0 0 24 24"><path d="M11.999 7.377a4.623 4.623 0 1 0 0 9.248 4.623 4.623 0 0 0 0-9.248zm0 7.627a3.004 3.004 0 1 1 0-6.008 3.004 3.004 0 0 1 0 6.008z"/><circle cx="16.806" cy="7.207" r="1.078"/><path d="M20.533 6.111A4.605 4.605 0 0 0 17.9 3.479a6.606 6.606 0 0 0-2.186-.42c-.963-.042-1.268-.054-3.71-.054s-2.755 0-3.71.054a6.554 6.554 0 0 0-2.184.42 4.6 4.6 0 0 0-2.633 2.632 6.585 6.585 0 0 0-.419 2.186c-.043.962-.056 1.267-.056 3.71 0 2.442 0 2.753.056 3.71.015.748.156 1.486.419 2.187a4.61 4.61 0 0 0 2.634 2.632 6.584 6.584 0 0 0 2.185.45c.963.042 1.268.055 3.71.055s2.755 0 3.71-.055a6.615 6.615 0 0 0 2.186-.419 4.613 4.613 0 0 0 2.633-2.633c.263-.7.404-1.438.419-2.186.043-.962.056-1.267.056-3.71s0-2.753-.056-3.71a6.581 6.581 0 0 0-.421-2.217zm-1.218 9.532a5.043 5.043 0 0 1-.311 1.688 2.987 2.987 0 0 1-1.712 1.711 4.985 4.985 0 0 1-1.67.311c-.95.044-1.218.055-3.654.055-2.438 0-2.687 0-3.655-.055a4.96 4.96 0 0 1-1.669-.311 2.985 2.985 0 0 1-1.719-1.711 5.08 5.08 0 0 1-.311-1.669c-.043-.95-.053-1.218-.053-3.654 0-2.437 0-2.686.053-3.655a5.038 5.038 0 0 1 .311-1.687c.305-.789.93-1.41 1.719-1.712a5.01 5.01 0 0 1 1.669-.311c.951-.043 1.218-.055 3.655-.055s2.687 0 3.654.055a4.96 4.96 0 0 1 1.67.311 2.991 2.991 0 0 1 1.712 1.712 5.08 5.08 0 0 1 .311 1.669c.043.951.054 1.218.054 3.655 0 2.436 0 2.698-.043 3.654h-.011z"/></svg>
                </a>
                <a class="size-8 inline-flex justify-center items-center text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-gray-100 transition-colors" href="https://www.facebook.com/cambialord" target="_blank" rel="noopener noreferrer" title="Facebook">
                    <svg class="h-5 w-5 fill-primary hover:fill-hoverPrimary" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 4.99 3.656 9.126 8.437 9.879v-6.988h-2.54v-2.891h2.54V9.798c0-2.508 1.493-3.891 3.776-3.891 1.094 0 2.24.195 2.24.195v2.459h-1.264c-1.24 0-1.628.772-1.628 1.563v1.875h2.771l-.443 2.891h-2.328v6.988C18.344 21.129 22 16.992 22 12.001c0-5.522-4.477-9.999-9.999-9.999z"/></svg>
                </a>
                <a class="size-8 inline-flex justify-center items-center text-sm font-semibold rounded-lg border border-transparent text-primary hover:bg-gray-100 transition-colors" href="mailto:cambialord.com@gmail.com?subject=Contacto%20a%20Cambialo%20RD" title="Correo">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-primary hover:fill-hoverPrimary" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
                </a>
            </div>
        </div>
    </div>
</footer>
