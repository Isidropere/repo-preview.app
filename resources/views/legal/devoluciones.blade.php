@extends('layouts.app')

@section('title', 'Políticas de Devoluciones y Reembolsos - Cambialo RD')

@section('content')
    <main class="min-h-screen py-10 bg-gray-50">
        <section class="max-w-4xl mx-auto px-4 bg-white p-8 rounded-2xl shadow-sm border border-gray-200">
            @include('components.btn-volver', ['backUrl' => route('home')])
            <header class="mb-8 border-b border-gray-100 pb-4">
                <h1 class="font-bold text-primary text-3xl">Políticas de Devoluciones, Reembolsos y Cancelaciones</h1>
                <p class="text-sm text-gray-500 mt-1">Última actualización: {{ date('d/m/Y') }}</p>
            </header>
            
            <article class="space-y-6 text-gray-700 leading-relaxed text-base">
                <p>
                    En <strong>Cámbialo RD</strong>, nuestra meta es garantizar la satisfacción total de nuestros usuarios. A continuación, detallamos los términos y condiciones relativos a las devoluciones, reembolsos y cancelaciones de transacciones comerciales en nuestra plataforma.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">1. Devoluciones de Productos Físicos</h2>
                <p>
                    Debido a la naturaleza de nuestra plataforma de intercambio y venta directa entre usuarios:
                </p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>El cliente dispone de un plazo de <strong>48 horas</strong> contadas a partir de la recepción física del artículo para notificar cualquier disconformidad, defecto de fábrica o discrepancia significativa entre el artículo recibido y la descripción provista en el sitio web.</li>
                    <li>Para proceder con una devolución, el artículo debe encontrarse en el mismo estado en que fue entregado, con sus etiquetas y accesorios completos.</li>
                    <li>Para iniciar el proceso de reclamación, el usuario debe abrir una solicitud de soporte o contactarnos directamente vía correo electrónico o teléfono.</li>
                </ul>

                <h2 class="text-xl font-semibold text-secondary mt-6">2. Políticas de Reembolso</h2>
                <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded-r-lg my-4">
                    <p class="font-semibold text-orange-900 mb-1">Reembolsos antes de realizar la compra:</p>
                    <p class="text-orange-800 text-sm">
                        <strong>Cámbialo RD no realiza reembolsos ni devoluciones de dinero en compras de artículos físicos por cambios de opinión del cliente</strong> una vez el producto ha sido enviado y recibido conforme a la descripción.
                    </p>
                    <p class="text-orange-800 text-sm mt-2">
                        En caso de que se apruebe una devolución debido a un error del vendedor o artículo defectuoso, el reembolso se procesará directamente al método de pago original (la tarjeta de crédito o débito utilizada) dentro de un plazo de 5 a 10 días hábiles, sujeto a los tiempos de procesamiento de la pasarela de pagos <strong>AZUL</strong> y del banco emisor.
                    </p>
                </div>

                <h2 class="text-xl font-semibold text-secondary mt-6">3. Cancelación de Pedidos</h2>
                <p>
                    Un cliente puede solicitar la cancelación de un pedido de producto físico sin costo alguno siempre y cuando el artículo <strong>no haya sido despachado</strong> por el vendedor o el socio logístico. Una vez el artículo esté en tránsito, no se aceptarán cancelaciones.
                </p>

                <h2 class="text-xl font-semibold text-secondary mt-6">4. Servicios y Talentos</h2>
                <p>
                    Para los servicios profesionales o talentos adquiridos a través del portal:
                </p>
                <ul class="list-disc pl-5 space-y-2">
                    <li>Si el proveedor del servicio cancela la cita o no se presenta en el día y hora acordados, el cliente tendrá derecho a un reembolso del 100% de los fondos abonados.</li>
                    <li>Las cancelaciones por parte del cliente deben realizarse con al menos 24 horas de anticipación para optar por un reembolso completo o reagendación. Cancelaciones posteriores a este límite pueden estar sujetas a cargos administrativos.</li>
                </ul>

                <h2 class="text-xl font-semibold text-secondary mt-6">5. Soporte de Reclamaciones</h2>
                <p>
                    Para cualquier aclaración, cancelación o gestión de reembolsos, póngase en contacto con nuestro equipo de atención:
                </p>
                <ul class="list-disc pl-5 space-y-1">
                    <li><strong>Correo electrónico:</strong> cambialord.com@gmail.com</li>
                    <li><strong>Teléfono de Soporte:</strong> (829) 963-4839</li>
                </ul>
            </article>
        </section>
    </main>
@endsection
