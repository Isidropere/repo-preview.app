{{-- Global Legal Policy Modal --}}
<div id="globalLegalModal" class="fixed inset-0 bg-black/60 hidden z-[99999] flex items-center justify-center p-4 backdrop-blur-sm transition-all duration-300" style="opacity: 0;">
    <div class="bg-white rounded-2xl w-full max-w-4xl h-[85vh] flex flex-col shadow-2xl border border-gray-200 overflow-hidden transform scale-95 transition-all duration-300 modal-card">
        
        <!-- Modal Header -->
        <div class="px-6 pt-5 pb-3 border-b border-gray-100 bg-gray-50 flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-50 text-blue-600 border border-blue-100/50">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 text-base md:text-lg">Políticas de Cámbialo RD</h3>
                        <p class="text-xs text-gray-400">Información legal y de seguridad</p>
                    </div>
                </div>
                <button id="closeGlobalLegalModal" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-xl p-2 transition-all focus:outline-none">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <!-- Tabs Switcher -->
            <div class="flex border-b border-gray-200 overflow-x-auto scrollbar-none" id="legalTabs">
                <button data-tab="terminos" class="flex-1 min-w-[150px] py-3 px-2 text-center border-b-2 border-transparent font-semibold text-xs md:text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all focus:outline-none whitespace-nowrap">
                    Términos y Condiciones
                </button>
                <button data-tab="privacidad" class="flex-1 min-w-[150px] py-3 px-2 text-center border-b-2 border-transparent font-semibold text-xs md:text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all focus:outline-none whitespace-nowrap">
                    Política de Privacidad
                </button>
                <button data-tab="devoluciones" class="flex-1 min-w-[150px] py-3 px-2 text-center border-b-2 border-transparent font-semibold text-xs md:text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all focus:outline-none whitespace-nowrap">
                    Devoluciones y Reembolsos
                </button>
            </div>
        </div>

        <!-- Modal Body (Scrollable content) -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 space-y-6 text-gray-700 leading-relaxed text-sm bg-white legal-content" id="legalModalBody">
            <div id="tab-content-terminos" class="hidden space-y-4">
                @include('legal.partials.terminos')
            </div>
            <div id="tab-content-privacidad" class="hidden space-y-4">
                @include('legal.partials.privacidad')
            </div>
            <div id="tab-content-devoluciones" class="hidden space-y-4">
                @include('legal.partials.devoluciones')
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex justify-end">
            <button id="closeGlobalLegalModalBtn" class="px-5 py-2.5 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-sm font-semibold transition-all focus:outline-none">
                Cerrar
            </button>
        </div>
    </div>
</div>

<style>
    #globalLegalModal.show-modal {
        opacity: 1 !important;
    }
    #globalLegalModal.show-modal .modal-card {
        transform: scale(1) !important;
    }
    .scrollbar-none::-webkit-scrollbar {
        display: none;
    }
    .scrollbar-none {
        -ms-overflow-style: none;
        scrollbar-width: none;
    }
    .legal-content h2 {
        font-size: 1.15rem;
        font-weight: 700;
        color: #1e3a8a; /* Indigo-900 / Dark blue */
        margin-top: 1.75rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 0.35rem;
    }
    .legal-content p {
        margin-bottom: 1rem;
        line-height: 1.625;
        color: #4b5563;
    }
    .legal-content ul, .legal-content ol {
        margin-bottom: 1.25rem;
        padding-left: 1.5rem;
    }
    .legal-content li {
        margin-bottom: 0.5rem;
        color: #4b5563;
        line-height: 1.5;
    }
    .legal-content strong {
        color: #111827;
        font-weight: 600;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('globalLegalModal');
    const closeBtns = [
        document.getElementById('closeGlobalLegalModal'),
        document.getElementById('closeGlobalLegalModalBtn')
    ];
    const tabButtons = document.querySelectorAll('#legalTabs button');
    const tabContents = {
        terminos: document.getElementById('tab-content-terminos'),
        privacidad: document.getElementById('tab-content-privacidad'),
        devoluciones: document.getElementById('tab-content-devoluciones')
    };

    function openModal(activeTab) {
        // Hide all tab contents
        Object.values(tabContents).forEach(content => {
            if (content) content.classList.add('hidden');
        });
        
        // Show active content
        if (tabContents[activeTab]) {
            tabContents[activeTab].classList.remove('hidden');
        }

        // Toggle button states
        tabButtons.forEach(btn => {
            if (btn.dataset.tab === activeTab) {
                btn.classList.add('border-blue-600', 'text-blue-600');
                btn.classList.remove('border-transparent', 'text-gray-500');
            } else {
                btn.classList.remove('border-blue-600', 'text-blue-600');
                btn.classList.add('border-transparent', 'text-gray-500');
            }
        });

        // Show modal wrapper
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Trigger transition
        setTimeout(() => {
            modal.classList.add('show-modal');
        }, 10);

        // Prevent background scrolling
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        modal.classList.remove('show-modal');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = '';
        }, 300);
    }

    // Tab switcher events
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.dataset.tab;
            Object.keys(tabContents).forEach(key => {
                if (key === tabName) {
                    tabContents[key].classList.remove('hidden');
                } else {
                    tabContents[key].classList.add('hidden');
                }
            });

            tabButtons.forEach(b => {
                if (b === btn) {
                    b.classList.add('border-blue-600', 'text-blue-600');
                    b.classList.remove('border-transparent', 'text-gray-500');
                } else {
                    b.classList.remove('border-blue-600', 'text-blue-600');
                    b.classList.add('border-transparent', 'text-gray-500');
                }
            });
        });
    });

    // Close listeners
    closeBtns.forEach(btn => {
        btn?.addEventListener('click', closeModal);
    });

    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
            closeModal();
        }
    });

    // Global Interceptor for Legal Links
    const legalPaths = [
        { path: 'terminos-condiciones', tab: 'terminos' },
        { path: 'politica-privacidad', tab: 'privacidad' },
        { path: 'politica-devoluciones', tab: 'devoluciones' }
    ];

    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        if (!link) return;

        const href = link.getAttribute('href');
        if (!href) return;

        // Do not intercept if on a legal page itself (allows standard direct page viewing)
        const currentPath = window.location.pathname;
        const isOnLegalPage = legalPaths.some(lp => currentPath.includes(lp.path));
        if (isOnLegalPage) return;

        for (const lp of legalPaths) {
            if (href.includes(lp.path)) {
                e.preventDefault();
                openModal(lp.tab);
                break;
            }
        }
    });
});
</script>
