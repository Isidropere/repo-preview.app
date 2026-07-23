<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Cambialord - Panel de Administración">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('imgs/logoTypes/logoFooter.png') }}">
    <link rel="stylesheet" href="{{ asset('css/_astro/index.D-AOIgCY.css') }}">
    <link rel="stylesheet" href="{{ asset('css/_astro/index.BneVErea.css') }}">
    <title>@yield('title', 'Admin - Cambialord')</title>
    
    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    @stack('head_styles')

    <style>
        /* Custom scrollbar for sidebar */
        .admin-sidebar::-webkit-scrollbar { width: 4px; }
        .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
        .admin-sidebar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
        
        /* Layout CSS (Tailwind classes might not be compiled) */
        .admin-layout-wrapper { display: flex; height: 100vh; width: 100%; }
        .admin-sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid #e5e7eb;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow-y: auto;
            position: relative;
            z-index: 40;
        }
        @media (max-width: 768px) {
            .admin-sidebar {
                position: fixed;
                transform: translateX(-100%);
                transition: transform 0.3s ease-in-out;
            }
            .admin-sidebar.show {
                transform: translateX(0);
            }
        }
        .admin-main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            height: 100%;
            overflow-y: auto;
            position: relative;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 font-sans antialiased overflow-hidden">

    <div class="admin-layout-wrapper">
        
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="h-16 flex items-center px-6 border-b border-gray-100 flex-shrink-0 bg-white">
                <a href="{{ url('/') }}" title="Volver al inicio" class="flex items-center w-full h-full">
                    <img src="{{ asset('imgs/logoTypes/header-logo.png') }}" alt="Logo" class="h-12 object-contain transition-transform hover:scale-105" style="max-width: 100%;">
                </a>
            </div>

            <div class="p-4 flex-1">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-2">Principal</div>
                <nav class="space-y-1 mb-6">
                    <a href="{{ route('admin.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-primary/10 hover:text-primary transition-colors text-gray-700">
                        <i class="fas fa-home w-5 text-center"></i>
                        Dashboard
                    </a>
                    @if(auth()->user()->isAdmin || auth()->user()->isSuperAdminUser())
                    <a href="{{ route('admin.imagenes.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-primary/10 hover:text-primary transition-colors text-gray-700">
                        <i class="fas fa-images w-5 text-center"></i>
                        Aprobación de Fotos
                    </a>
                    @endif
                </nav>

                @if(auth()->user()->isSuperAdminUser() || auth()->user()->isContableUser())
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">ERP Empresarial</div>
                <nav class="space-y-1 mb-6">
                    <a href="{{ route('admin.erp.contabilidad') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-blue-50 hover:text-blue-600 transition-colors text-gray-700">
                        <i class="fas fa-file-invoice-dollar w-5 text-center text-blue-500"></i>
                        Contabilidad
                    </a>
                    <a href="{{ route('admin.erp.inventario') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-green-50 hover:text-green-600 transition-colors text-gray-700">
                        <i class="fas fa-boxes w-5 text-center text-green-500"></i>
                        Inventario
                    </a>
                    <a href="{{ route('admin.erp.caja') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-amber-50 hover:text-amber-600 transition-colors text-gray-700">
                        <i class="fas fa-cash-register w-5 text-center text-amber-500"></i>
                        Cuadre de Caja
                    </a>
                    <a href="{{ route('admin.erp.transporte.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-indigo-50 hover:text-indigo-600 transition-colors text-gray-700">
                        <i class="fas fa-truck w-5 text-center text-indigo-500"></i>
                        Transporte
                    </a>
                    <a href="{{ route('admin.erp.historial') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-emerald-50 hover:text-emerald-600 transition-colors text-gray-700">
                        <i class="fas fa-history w-5 text-center text-emerald-500"></i>
                        Ventas e Intercambios
                    </a>
                </nav>
                @endif

                @if(auth()->user()->isAdmin || auth()->user()->isSuperAdminUser())
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">Administración</div>
                <nav class="space-y-1 mb-6">
                    <a href="{{ route('admin.recursos-humanos.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors text-gray-700">
                        <i class="fas fa-users w-5 text-center text-gray-500"></i>
                        Recursos Humanos
                    </a>
                    <a href="{{ route('admin.mensajes.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-comment-dots w-5 text-center text-gray-500"></i>
                        Mensajes Predefinidos
                    </a>
                    <a href="{{ route('admin.ayuda.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-question-circle w-5 text-center text-gray-500"></i>
                        Páginas de Ayuda
                    </a>
                    <a href="{{ route('admin.notificaciones.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-bell w-5 text-center text-yellow-500"></i>
                        Notificaciones
                    </a>
                    <a href="{{ route('admin.notificaciones.categorias') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-list-ul w-5 text-center text-blue-400"></i>
                        Notificar Categorías
                    </a>
                </nav>
                @endif

                @if(auth()->user()->isSuperAdminUser())
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2 mt-4">Reportes y Configuración Avanzada</div>
                <nav class="space-y-1 mb-6">
                    <a href="{{ route('admin.stats.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-chart-line w-5 text-center text-purple-500"></i>
                        Estadísticas
                    </a>
                    <a href="{{ route('admin.delivery.zonas') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-map-marker-alt w-5 text-center text-red-500"></i>
                        Zonas de Envío
                    </a>
                    <a href="{{ route('admin.motivos_devolucion.index') }}" class="flex items-center gap-3 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 transition-colors text-gray-700">
                        <i class="fas fa-undo w-5 text-center text-gray-500"></i>
                        Motivos Devolución
                    </a>
                </nav>
                @endif
            </div>

            <div class="p-4 border-t border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center text-gray-600 font-bold">
                        {{ substr(auth()->user()->nombres ?? 'A', 0, 1) }}
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ auth()->user()->nombres ?? 'Admin' }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST" class="mt-3">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm font-medium text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                        <i class="fas fa-sign-out-alt w-5"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Overlay for mobile -->
        <div id="sidebarOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 z-30 hidden md:hidden"></div>

        <!-- Main Content -->
        <div class="admin-main-content">
            <!-- Header Móvil (Solo visible en móviles) -->
            <header class="md:hidden bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-20 flex-shrink-0 shadow-sm">
                <div class="flex items-center">
                    <button id="mobileMenuBtn" class="mr-4 text-gray-500 hover:text-primary focus:outline-none transition-colors">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <h1 class="text-lg font-bold text-gray-800">@yield('title', 'Panel')</h1>
                </div>
            </header>

            <!-- Contenedor scrolleable interior -->
            <div class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                @yield('content')
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('sidebarOverlay');

            function toggleSidebar() {
                const isOpen = sidebar.classList.contains('-translate-x-full');
                if (isOpen) {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('hidden');
                } else {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('hidden');
                }
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleSidebar);
            }
            if (overlay) {
                overlay.addEventListener('click', toggleSidebar);
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
