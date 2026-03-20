
@extends('layouts.app')

@section('title', 'Cambialord - Tu cuenta')

@section('content')
    <main class="min-h-screen">
        <div>
            <section class="section undefined  mx-auto lg:max-w-[1250px] md:max-w-[750px] max-w-[325px] ">
                @auth
                @include('components.btn-volver', ['backUrl' => route('home')])
                <h1 class="text-4xl text-primary font-semibold">¡Hola!, {{ ucfirst(Auth::user()->nombres) }} {{ ucfirst(Auth::user()->apellidos) }} </h1>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 my-4">
                      <a href="{{ route('items.talento_create') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/agregartalentos.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Agregar un nuevo talento</h2>
                            <p> talentos</p>
                        </div>
                    </a>
                    @else
                        <a href="{{ route('login') }}">Iniciar sesión</a>
                    @endauth
                        
                           <a href="{{ route('items.admintalento') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/talentos.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Administrar tus talentos</h2>
                            <p>talentos</p>
                        </div>
                    </a>
                <a  href="{{ route('items.create') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs//icons/addProduct.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Agregar productos</h2>
                            <p>Publica tus artículos</p>
                        </div>
                    </a>
                <!--href="/tu-cuenta/updateProducts"-->
                <a  href="{{ route('items.user') }}"  
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/editProduct.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Gestionar productos</h2>
                            <p>Elimina tus artículos</p>
                        </div>
                    </a>

                <a  href="{{ route('direcciones.index') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/EditLocation.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Dirección</h2>
                            <p>Actualiza y guarda tu dirección preferida</p>
                        </div>
                    </a>
                 <!--href="/tu-cuenta/contraseña"-->
                <a  href="{{ route('password.update') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/ShieldPlus.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Modificación de contraseña</h2>
                            <p>Cambia tu contraseña de manera segura</p>
                        </div>
                    </a>
                 <!--href="/historial"-->
                <a  href="{{ route('historial') }}"
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/History.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Historial general</h2>
                            <p>Revisa tus intercambios o compras pasadas</p>
                        </div>
                    </a>

                <!--href="/premium-upgrade"-->
                <a href="{{ route('usuario.tipo.update') }}" 
                        class="flex gap-x-2 border border-gray-200 rounded-lg bg-white hover:bg-gray-100 transition-all duration-300 p-4 shadow-md">
                        <img src="/imgs/icons/premium.svg" alt="">
                        <div>
                            <h2 class="text-xl font-medium">Cambiar cuenta a premium</h2>
                            <p>Descubre los beneficios de ser direcciones premium</p>
                        </div>
                    </a>
                       

                </div>



            </section>
        </div>
    </main> 
  @endsection

