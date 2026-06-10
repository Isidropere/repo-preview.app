@extends('layouts.app')

@section('title', 'Política de Privacidad - Cambialo RD')

@section('content')
    <main class="min-h-screen py-10 bg-gray-50">
        <section class="max-w-4xl mx-auto px-4 bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8 border-b border-gray-100 pb-4">
                <h1 class="font-bold text-primary text-3xl">Política de Privacidad y Seguridad</h1>
                <p class="text-sm text-gray-500 mt-1">Última actualización: {{ date('d/m/Y') }}</p>
            </header>
            
            <article class="space-y-6 text-gray-700 leading-relaxed text-base">
                <p>
                    En <strong>Cámbialo RD</strong> (en adelante, "el Comercio"), con nombre comercial registrado <strong>Cámbialo RD</strong>, nos comprometemos a proteger la privacidad y la seguridad de la información personal de nuestros usuarios. Esta política describe cómo recopilamos, utilizamos y salvaguardamos sus datos.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">1. Recopilación de Información</h2>
                <p>
                    Recopilamos información de identificación personal, como nombres, apellidos, direcciones de correo electrónico, números telefónicos y direcciones de envío, únicamente cuando los usuarios la proporcionan de forma voluntaria para utilizar nuestros servicios de trueque, compra o venta.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">2. Política de Seguridad para la Transmisión de Datos de Tarjetas</h2>
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-r-lg my-4">
                    <p class="font-semibold text-blue-900 mb-1">Protección de Datos de Pago:</p>
                    <p class="text-blue-800 text-sm">
                        En Cámbialo RD, priorizamos la seguridad de sus datos financieros. <strong>No almacenamos, guardamos ni compartimos los números completos de sus tarjetas de crédito o débito, ni sus códigos de seguridad (CVV)</strong>.
                    </p>
                    <p class="text-blue-800 text-sm mt-2">
                        Toda transmisión de datos de tarjetas de pago se realiza de forma cifrada mediante el protocolo seguro <strong>TLS 1.2</strong> directamente hacia los servidores de la pasarela de pagos <strong>AZUL</strong> (Banco Popular Dominicano). Cámbialo RD no tiene acceso ni control sobre los datos confidenciales de la tarjeta en ningún momento de la transacción.
                    </p>
                </div>

                <h2 class="text-xl font-semibold text-secondary mt-6">3. Uso de la Información</h2>
                <p>
                    La información personal recopilada se utiliza para procesar transacciones, coordinar la logística de envío a través de nuestros socios de transporte, gestionar las solicitudes de soporte técnico y enviar notificaciones relacionadas con su cuenta o sus transacciones.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">4. Cookies y Tecnologías de Seguimiento</h2>
                <p>
                    Utilizamos cookies para mantener su sesión activa y mejorar su experiencia de navegación en nuestra plataforma. Puede deshabilitar las cookies en su navegador si lo prefiere, aunque esto podría afectar el funcionamiento de algunas secciones de la página web.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">5. Contacto</h2>
                <p>
                    Si tiene alguna pregunta o inquietud respecto a esta Política de Privacidad y Seguridad, puede contactarnos a través de:
                </p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Correo electrónico:</strong> cambialord.com@gmail.com</li>
                    <li><strong>Teléfono de Soporte:</strong> (829) 963-4839</li>
                </ul>
            </article>
        </section>
    </main>
@endsection
