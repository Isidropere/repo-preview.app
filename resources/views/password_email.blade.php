@extends('layouts.app')

@section('title', 'Envio-password - Cambialord')

@section('content')
<main class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col items-center justify-center">
           
                    <!-- Logo -->
                   
                 
                     <br>
                    <div class="mb-8">
                        <img src="{{ asset('imgs/logoTypes/logoFooter.png') }}" class="h-24 object-contain" alt="Logo Cambialord">
                    </div>
                <div class="w-full max-w-md bg-white border border-gray-200 rounded-xl shadow-sm sm:w-11/12 lg:w-3/4 xl:w-2/3 2xl:w-1/2" data-astro-cid-zp6vblvr>
                    <div class="p-4 sm:p-7" data-astro-cid-zp6vblvr>
                        <div class="text-center" data-astro-cid-zp6vblvr>
                            <h1 class="block text-2xl font-bold text-gray-800" data-astro-cid-zp6vblvr>Cambiar Contraseña</h1>
                            <p class="mt-2 text-sm text-gray-600" data-astro-cid-zp6vblv>
                                Ingresa tu correo electrónico para recibir el enlace de restablecimiento.
                            </p>
                        </div>
                    <div class="mt-5" data-astro-cid-zp6vblvr>
                        <form id="resetForm" data-astro-cid-zp6vblvr method="POST" action="{{ route('password.email') }}">
                            @csrf
                            <div id="emailInputContainer" class="grid gap-y-4" data-astro-cid-zp6vblvr>
                                <div data-astro-cid-zp6vblvr>
                                    <div class="relative" data-astro-cid-zp6vblvr>
                                        <div class="relative z-0 w-full mb-4">
                                            {{-- <label for="email" class="block mb-1 text-gray-700 font-medium">Correo electrónico</label> --}}
                                            <input type="email" placeholder ="Correo electrónico"
                                                    required name="email"
                                                    value="{{ old('email') }}"
                                                    class="relative pt-3 pb-2 block w-full px-0 mt-0 bg-transparent border-0 border-b-2 appearance-none focus:outline-none focus:ring-0 focus:border-primary border-gray-200"
                                                    data-astro-cid-zp6vblvr> <label for="email"
                                                    class="absolute duration-300 top-3 -z-1 origin-0 text-gray-500"></label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div id="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg text-center hidden">
                                Se ha enviado un link a su correo para cambiar su contraseña.
                            </div>
                            <button id="submitButton" type="submit"
                                class="w-full py-3 px-4 inline-flex justify-center items-center gap-x-2 text-sm font-semibold rounded-lg border border-transparent bg-secondary text-white hover:bg-hoverSecondary disabled:opacity-50 disabled:pointer-events-none"
                                data-astro-cid-zp6vblvr>
                                Enviar enlace de restablecimiento
                            </button>
                        </form>
                    </div>
                </div>
            </div>
                            @if (session('status'))
                                <div class="mt-4 text-green-600 text-center">
                                    {{ session('status') }}
                                </div>
                            @endif
                                <div class="mt-4 text-center">
                                    <a href="{{ route('login') }}" class="text-primary hover:underline font-medium">Volver a inicio de sesión</a>
                                </div>


           

       </div>
           </div>     
</main>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetForm');
            const emailInputContainer = document.getElementById('emailInputContainer');
            const successMessage = document.getElementById('successMessage');
            const submitButton = document.getElementById('submitButton');

            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const formData = new FormData(form);

                // Disable the button during submission
                submitButton.disabled = true;

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Hide the email input container
                        emailInputContainer.classList.add('hidden');

                        // Show the success message
                        successMessage.classList.remove('hidden');

                        // Change the button text and action
                        submitButton.textContent = 'Regresar a inicio';
                        submitButton.classList.remove('bg-secondary', 'hover:bg-hoverSecondary');
                        submitButton.classList.add('bg-primary', 'hover:bg-blue-700');

                        // Change the button action to redirect to home
                        submitButton.type = 'button'; // Change from submit to button
                        submitButton.addEventListener('click', function() {
                            window.location.href = '{{ route("home") }}';
                        });
                    } else {
                        // Show error message
                        const errorMessage = document.createElement('div');
                        errorMessage.className = 'mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-center';
                        errorMessage.textContent = data.message || 'Ha ocurrido un error. Por favor, inténtalo de nuevo.';
                        form.appendChild(errorMessage);

                        // Re-enable the button
                        submitButton.disabled = false;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);

                    // Show error message
                    const errorMessage = document.createElement('div');
                    errorMessage.className = 'mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-center';
                    errorMessage.textContent = 'Ha ocurrido un error. Por favor, inténtalo de nuevo.';
                    form.appendChild(errorMessage);

                    // Re-enable the button
                    submitButton.disabled = false;
                });
            });

            // Check if there's an error message from a previous submission
            @if($errors->any())
                submitButton.disabled = false;
            @endif

            // Check if we should show the success state (e.g., after a page refresh)
            @if(session('status'))
                emailInputContainer.classList.add('hidden');
                successMessage.classList.remove('hidden');
                submitButton.textContent = 'Regresar a inicio';
                submitButton.classList.remove('bg-secondary', 'hover:bg-hoverSecondary');
                submitButton.classList.add('bg-primary', 'hover:bg-blue-700');
                submitButton.type = 'button'; // Change from submit to button
                submitButton.addEventListener('click', function() {
                    window.location.href = '{{ route("home") }}';
                });
            @endif
        });
    </script>
@endpush
