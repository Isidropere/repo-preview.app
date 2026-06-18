<footer class="mt-auto w-full shadow-2xl bg-white border-t border-gray-100">
    <div class="mt-auto w-full max-w-[85rem] py-8 px-4 sm:px-6 lg:px-8 mx-auto">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-8 lg:gap-12">

            {{-- Logo --}}
            <div class="col-span-full lg:col-span-1 flex items-center py-2">
                <a class="flex-none text-xl font-semibold" href="/" aria-label="Brand">
                    <img src="/imgs/logoTypes/header-logo.png"
                         style="height:80px;object-fit:contain;transition:transform .3s ease, filter .3s ease;cursor:pointer;"
                         onmouseover="this.style.transform='scale(1.1)';this.style.filter='brightness(1.15)'"
                         onmouseout="this.style.transform='scale(1)';this.style.filter='brightness(1)'"
                         alt="Cambialord" loading="lazy">
                </a>
            </div>

            {{-- Secciones --}}
            <div class="col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Secciones</h4>
                <div class="grid space-y-2 text-sm">
                    <p><button type="button" id="btn-about-modal" class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors text-left focus:outline-none">Sobre Nosotros</button></p>
                    <p><button type="button" id="btn-contact-modal" class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors text-left focus:outline-none">Contáctanos</button></p>
                    <p><button type="button" id="btn-shipping-modal" class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors text-left focus:outline-none">Información de envíos</button></p>
                    <p><a class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors" href="{{ route('empleos') }}">Empleos</a></p>
                </div>
            </div>

            {{-- Ayuda e información --}}
            <div class="col-span-1">
                <h4 class="font-bold text-secondary text-base mb-3">Ayuda</h4>
                <div class="grid space-y-2 text-sm">
                    <p><button type="button" id="btn-responsibility-modal" class="inline-flex gap-x-2 text-gray-600 hover:text-primary transition-colors text-left focus:outline-none">Responsabilidad social</button></p>
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
                    <img src="/imgs/Visa_Brandmark_Blue_RGB_2021.png" alt="Visa" class="h-14 object-contain">
                    <img src="/imgs/mastercard-logo.png" alt="Mastercard" class="h-14 object-contain">
                    <img src="/imgs/visa-secure_blu_2021_dkbg.png" alt="Visa Secure" class="h-16 object-contain">
                    <img src="/imgs/mastercardidentitycheck.png" alt="Mastercard Identity Check" class="h-16 object-contain">
                    <div class="flex items-center bg-blue-600 text-white font-black px-4 py-2 rounded-lg text-sm tracking-widest uppercase shadow-sm select-none h-14">
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

    {{-- Modal Sobre Nosotros --}}
    <div id="about-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="about-modal-close-bg"></div>

            <!-- Trick browser to center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6 relative">
                    <!-- Close Button -->
                    <button type="button" id="btn-about-modal-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 w-full">
                            <h3 class="text-3xl font-bold text-primary mb-6" id="modal-title">
                                Sobre nosotros
                            </h3>
                            <div class="mt-2 text-gray-700 text-sm leading-relaxed space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                                <p>
                                    Cámbialo RD nace con la visión de ofrecer una solución innovadora y sostenible en la República Dominicana. Somos una plataforma en línea dedicada a facilitar el intercambio, compra y venta de objetos nuevos o usados en buen estado. Nuestra misión es promover un estilo de vida más ecológico y consciente, brindando a nuestros usuarios la posibilidad de darle una segunda vida a esos artículos que ya no utilizan. Con nuestro eslogan: “Si no puedes venderlo, ¡cámbialo!", queremos incentivar el reciclaje y el ahorro, proporcionando una alternativa práctica para quienes desean obtener nuevos artículos sin necesidad de comprarlos, o simplemente desean vender lo que ya no usan.
                                </p>
                                
                                <div>
                                    <h4 class="font-bold text-primary text-lg mb-2">Misión</h4>
                                    <p>
                                        En Cámbialo RD, nuestra misión es transformar la forma en que las personas en la República Dominicana intercambian, compran y venden artículos, promoviendo un consumo consciente y sostenible. Nos dedicamos a ofrecer una plataforma en línea segura y accesible que facilite el aprovechamiento de recursos, reduciendo el desperdicio y fomentando un estilo de vida más ecológico. Buscamos conectar a las personas, dándoles la oportunidad de encontrar nuevas utilidades para los objetos que ya no usan, contribuyendo así a un mundo más responsable y sostenible.
                                    </p>
                                </div>

                                <div>
                                    <h4 class="font-bold text-primary text-lg mb-2">Visión</h4>
                                    <p>
                                        Nuestra visión es ser la plataforma líder en la República Dominicana para el intercambio, compra y venta de artículos, posicionándonos como un referente en el consumo sostenible y consciente. Aspiramos a expandir nuestra comunidad, creando un impacto positivo tanto en el medio ambiente como en la economía local. Queremos ser reconocidos por nuestra capacidad de conectar a las personas, ofreciendo soluciones innovadoras que faciliten una vida más equilibrada y respetuosa con el entorno.
                                    </p>
                                </div>

                                <div>
                                    <h4 class="font-bold text-primary text-lg mb-4">Valores</h4>
                                    <ul class="list-disc pl-5 space-y-2.5">
                                        <li><span class="font-bold text-gray-900">Sostenibilidad:</span> Fomentamos prácticas que contribuyen a la reducción de desechos y al cuidado del medio ambiente, promoviendo el intercambio y la reutilización de objetos.</li>
                                        <li><span class="font-bold text-gray-900">Responsabilidad:</span> Operamos de manera ética y transparente, garantizando que nuestras acciones beneficien a la comunidad y respeten el entorno.</li>
                                        <li><span class="font-bold text-gray-900">Innovación:</span> Nos esforzamos por ofrecer soluciones tecnológicas que faciliten la vida de nuestros usuarios, mejorando constantemente nuestra plataforma para adaptarnos a sus necesidades.</li>
                                        <li><span class="font-bold text-gray-900">Confianza:</span> Brindamos un entorno seguro y confiable donde nuestros usuarios pueden realizar intercambios, compras y ventas con total tranquilidad.</li>
                                        <li><span class="font-bold text-gray-900">Comunidad:</span> Valoramos y fortalecemos las conexiones entre nuestros usuarios, creando un espacio donde todos pueden beneficiarse mutuamente y contribuir al bien común.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:py-4 flex justify-end">
                    <button type="button" id="btn-about-modal-close-footer" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Contáctanos --}}
    <div id="contact-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="contact-modal-close-bg"></div>

            <!-- Trick browser to center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6 relative">
                    <!-- Close Button -->
                    <button type="button" id="btn-contact-modal-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 w-full">
                            <h3 class="text-3xl font-bold text-primary mb-6" id="modal-title">
                                Contáctanos
                            </h3>
                            <div class="mt-2 text-gray-700 text-sm leading-relaxed space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                                <p class="text-gray-500">
                                    ¿Tienes alguna duda o inconveniente? Ponte en contacto con nosotros a través de cualquiera de nuestros canales oficiales y te responderemos lo antes posible.
                                </p>
                                
                                <div class="space-y-4">
                                    <!-- Correo -->
                                    <div class="flex items-center gap-x-4 p-4 rounded-2xl border border-gray-100 hover:border-primary/20 hover:bg-orange-50/10 transition-colors">
                                        <div class="flex-shrink-0 bg-orange-50 p-3 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" class="fill-primary">
                                                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Correo Electrónico</p>
                                            <a href="mailto:cambialord.com@gmail.com?subject=Contacto%20a%20Cambialo%20RD" class="text-base font-bold text-gray-800 hover:text-primary transition-colors">cambialord.com@gmail.com</a>
                                        </div>
                                    </div>

                                    <!-- Instagram -->
                                    <div class="flex items-center gap-x-4 p-4 rounded-2xl border border-gray-100 hover:border-primary/20 hover:bg-orange-50/10 transition-colors">
                                        <div class="flex-shrink-0 bg-orange-50 p-3 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" class="fill-primary">
                                                <path d="M11.999 7.377a4.623 4.623 0 1 0 0 9.248 4.623 4.623 0 0 0 0-9.248zm0 7.627a3.004 3.004 0 1 1 0-6.008 3.004 3.004 0 0 1 0 6.008z"></path>
                                                <circle cx="16.806" cy="7.207" r="1.078"></circle>
                                                <path d="M20.533 6.111A4.605 4.605 0 0 0 17.9 3.479a6.606 6.606 0 0 0-2.186-.42c-.963-.042-1.268-.054-3.71-.054s-2.755 0-3.71.054a6.554 6.554 0 0 0-2.184.42 4.6 4.6 0 0 0-2.633 2.632 6.585 6.585 0 0 0-.419 2.186c-.043.962-.056 1.267-.056 3.71 0 2.442 0 2.753.056 3.71.015.748.156 1.486.419 2.187a4.61 4.61 0 0 0 2.634 2.632 6.584 6.584 0 0 0 2.185.45c.963.042 1.268.055 3.71.055s2.755 0 3.71-.055a6.615 6.615 0 0 0 2.186-.419 4.613 4.613 0 0 0 2.633-2.633c.263-.7.404-1.438.419-2.186.043-.962.056-1.267.056-3.71s0-2.753-.056-3.71a6.581 6.581 0 0 0-.421-2.217zm-1.218 9.532a5.043 5.043 0 0 1-.311 1.688 2.987 2.987 0 0 1-1.712 1.711 4.985 4.985 0 0 1-1.67.311c-.95.044-1.218.055-3.654.055-2.438 0-2.687 0-3.655-.055a4.96 4.96 0 0 1-1.669-.311 2.985 2.985 0 0 1-1.719-1.711 5.08 5.08 0 0 1-.311-1.669c-.043-.95-.053-1.218-.053-3.654 0-2.437 0-2.686.053-3.655a5.038 5.038 0 0 1 .311-1.687c.305-.789.93-1.41 1.719-1.712a5.01 5.01 0 0 1 1.669-.311c.951-.043 1.218-.055 3.655-.055s2.687 0 3.654.055a4.96 4.96 0 0 1 1.67.311 2.991 2.991 0 0 1 1.712 1.712 5.08 5.08 0 0 1 .311 1.669c.043.951.054 1.218.054 3.655 0 2.436 0 2.698-.043 3.654h-.011z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Instagram</p>
                                            <a href="https://instagram.com/cambialorddominicana" target="_blank" rel="noopener noreferrer" class="text-base font-bold text-gray-800 hover:text-primary transition-colors">Cambialorddominicana.com.do</a>
                                        </div>
                                    </div>

                                    <!-- Facebook -->
                                    <div class="flex items-center gap-x-4 p-4 rounded-2xl border border-gray-100 hover:border-primary/20 hover:bg-orange-50/10 transition-colors">
                                        <div class="flex-shrink-0 bg-orange-50 p-3 rounded-xl">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" class="fill-primary">
                                                <path d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 4.99 3.656 9.126 8.437 9.879v-6.988h-2.54v-2.891h2.54V9.798c0-2.508 1.493-3.891 3.776-3.891 1.094 0 2.24.195 2.24.195v2.459h-1.264c-1.24 0-1.628.772-1.628 1.563v1.875h2.771l-.443 2.891h-2.328v6.988C18.344 21.129 22 16.992 22 12.001c0-5.522-4.477-9.999-9.999-9.999z"></path>
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">Facebook</p>
                                            <a href="https://facebook.com/cambialord" target="_blank" rel="noopener noreferrer" class="text-base font-bold text-gray-800 hover:text-primary transition-colors">Cámbialo RD</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:py-4 flex justify-end">
                    <button type="button" id="btn-contact-modal-close-footer" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Información de Envíos --}}
    <div id="shipping-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="shipping-modal-close-bg"></div>

            <!-- Trick browser to center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6 relative">
                    <!-- Close Button -->
                    <button type="button" id="btn-shipping-modal-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 w-full">
                            <h3 class="text-3xl font-bold text-primary mb-6" id="modal-title">
                                Información de envíos
                            </h3>
                            <div class="mt-2 text-gray-700 text-sm leading-relaxed space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                                <div class="bg-blue-50 border border-blue-100 rounded-2xl p-5 flex gap-x-4">
                                    <div class="flex-shrink-0">
                                        <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="text-blue-800 text-sm leading-normal">
                                        En Cámbialo RD, facilitamos el proceso de intercambio, compra y venta de manera rápida y sencilla. Los envíos de productos se realizan a través de nuestros partners logísticos de confianza, que podrás elegir al momento de realizar el pago.
                                    </div>
                                </div>
                                
                                <div class="space-y-4">
                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3">
                                        <h4 class="font-bold text-primary text-base flex items-center gap-x-2">
                                            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                                            </svg>
                                            Intercambios (Trueques)
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            En el caso de intercambios, los usuarios solo deberán cubrir el costo del envío. No hay cargos adicionales por transacción.
                                        </p>
                                    </div>

                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3">
                                        <h4 class="font-bold text-primary text-base flex items-center gap-x-2">
                                            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Compras directas
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            El cliente pagará tanto el precio del objeto como el costo de envío, el cual será especificado detalladamente al momento de la transacción.
                                        </p>
                                    </div>

                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3">
                                        <h4 class="font-bold text-primary text-base flex items-center gap-x-2">
                                            <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                            </svg>
                                            Garantía para el Vendedor
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            Si estás vendiendo, recibirás el pago por tu producto una vez que el comprador haya confirmado la recepción de este en perfectas condiciones.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:py-4 flex justify-end">
                    <button type="button" id="btn-shipping-modal-close-footer" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Responsabilidad Social --}}
    <div id="responsibility-modal" class="fixed inset-0 z-[100] hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        {{-- Backdrop --}}
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" id="responsibility-modal-close-bg"></div>

            <!-- Trick browser to center modal -->
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            {{-- Modal Panel --}}
            <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-3xl sm:w-full border border-gray-100">
                <div class="bg-white px-6 pt-6 pb-4 sm:p-8 sm:pb-6 relative">
                    <!-- Close Button -->
                    <button type="button" id="btn-responsibility-modal-close" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                    
                    <div class="sm:flex sm:items-start">
                        <div class="mt-3 text-left sm:mt-0 w-full">
                            <h3 class="text-3xl font-bold text-primary mb-6" id="modal-title">
                                Responsabilidad Social
                            </h3>
                            <div class="mt-2 text-gray-700 text-sm leading-relaxed space-y-6 max-h-[60vh] overflow-y-auto pr-2">
                                <p class="text-gray-650 text-base leading-relaxed">
                                    En <strong>Cámbialo RD</strong>, la responsabilidad social no es solo un compromiso corporativo; es nuestra razón de ser. Nuestra plataforma fue creada con el firme propósito de mitigar el impacto ambiental y fortalecer la economía familiar en la República Dominicana a través de un modelo de <strong>economía circular</strong> y consumo consciente.
                                </p>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Tarjeta 1: Reducción de Huella Ecológica -->
                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3 bg-green-50/10">
                                        <h4 class="font-bold text-green-700 text-base flex items-center gap-x-2">
                                            <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-11.314l.707.707m11.314 11.314l.707-.707M4 11a8 8 0 1116 0 8 8 0 01-16 0z"/>
                                            </svg>
                                            Reducción de Desechos
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            Promovemos la reutilización y el reciclaje práctico de objetos. Al darle una segunda vida a los artículos en buen estado, evitamos que terminen prematuramente en vertederos, disminuyendo la acumulación de residuos en nuestro país.
                                        </p>
                                    </div>

                                    <!-- Tarjeta 2: Economía Circular -->
                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3 bg-blue-50/10">
                                        <h4 class="font-bold text-blue-700 text-base flex items-center gap-x-2">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 1121.21 7.89M9 11l3 3L22 4"/>
                                            </svg>
                                            Trueque Sostenible
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            Bajo el lema <em>"Si no puedes venderlo, ¡cámbialo!"</em>, incentivamos el intercambio directo de bienes sin necesidad de usar dinero, promoviendo el ahorro, la solidaridad y un comercio más inclusivo y humano.
                                        </p>
                                    </div>

                                    <!-- Tarjeta 3: Consumo Consciente -->
                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3 bg-orange-50/10">
                                        <h4 class="font-bold text-orange-600 text-base flex items-center gap-x-2">
                                            <svg class="h-6 w-6 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                            </svg>
                                            Educación Ecológica
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            Operamos de manera transparente y ética, impulsando iniciativas y creando conciencia sobre los beneficios ecológicos de preferir el mercado de segunda mano frente al consumismo masivo tradicional.
                                        </p>
                                    </div>

                                    <!-- Tarjeta 4: Impacto Comunitario -->
                                    <div class="border border-gray-100 rounded-2xl p-5 space-y-3 bg-purple-50/10">
                                        <h4 class="font-bold text-purple-700 text-base flex items-center gap-x-2">
                                            <svg class="h-6 w-6 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                            </svg>
                                            Unión de Comunidades
                                        </h4>
                                        <p class="text-gray-600 text-sm leading-relaxed">
                                            Facilitamos que personas de diferentes provincias de la República Dominicana conecten de forma segura, construyendo una red colaborativa que promueve la equidad social y el acceso a productos de calidad.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-6 py-4 sm:px-8 sm:py-4 flex justify-end">
                    <button type="button" id="btn-responsibility-modal-close-footer" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-xs font-semibold rounded-xl transition">
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to setup a modal
            function setupModal(triggerId, modalId, closeId, closeFooterId, closeBgId) {
                const openBtn = document.getElementById(triggerId);
                const modal = document.getElementById(modalId);
                const closeBtn = document.getElementById(closeId);
                const closeFooterBtn = document.getElementById(closeFooterId);
                const closeBg = document.getElementById(closeBgId);

                if (!openBtn || !modal) return;

                function openModal() {
                    modal.classList.remove('hidden');
                    document.body.classList.add('overflow-hidden');
                }

                function closeModal() {
                    modal.classList.add('hidden');
                    document.body.classList.remove('overflow-hidden');
                }

                openBtn.addEventListener('click', openModal);
                if (closeBtn) closeBtn.addEventListener('click', closeModal);
                if (closeFooterBtn) closeFooterBtn.addEventListener('click', closeModal);
                if (closeBg) closeBg.addEventListener('click', closeModal);
            }

            // Setup the four modals
            setupModal('btn-about-modal', 'about-modal', 'btn-about-modal-close', 'btn-about-modal-close-footer', 'about-modal-close-bg');
            setupModal('btn-contact-modal', 'contact-modal', 'btn-contact-modal-close', 'btn-contact-modal-close-footer', 'contact-modal-close-bg');
            setupModal('btn-shipping-modal', 'shipping-modal', 'btn-shipping-modal-close', 'btn-shipping-modal-close-footer', 'shipping-modal-close-bg');
            setupModal('btn-responsibility-modal', 'responsibility-modal', 'btn-responsibility-modal-close', 'btn-responsibility-modal-close-footer', 'responsibility-modal-close-bg');
        });
    </script>
</footer>
