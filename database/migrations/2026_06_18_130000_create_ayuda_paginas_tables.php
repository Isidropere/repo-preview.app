<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ayuda_paginas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('titulo');
            $table->text('descripcion');
            $table->timestamps();
        });

        Schema::create('ayuda_pasos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ayuda_pagina_id');
            $table->integer('orden')->default(1);
            $table->string('titulo');
            $table->text('descripcion');
            $table->string('imagen')->nullable();
            $table->timestamps();

            $table->foreign('ayuda_pagina_id')->references('id')->on('ayuda_paginas')->onDelete('cascade');
        });

        // Seed initial data
        $this->seedInitialData();
    }

    public function down(): void
    {
        Schema::dropIfExists('ayuda_pasos');
        Schema::dropIfExists('ayuda_paginas');
    }

    private function seedInitialData(): void
    {
        // 1. ¿Cómo realizar un intercambio?
        $paginaIntercambioId = DB::table('ayuda_paginas')->insertGetId([
            'slug' => 'realizar-intercambio',
            'titulo' => '¿Cómo realizar un intercambio?',
            'descripcion' => 'Realizar un intercambio en Cámbialo RD es muy fácil y rápido. Sigue estos simples pasos:',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ayuda_pasos')->insert([
            [
                'ayuda_pagina_id' => $paginaIntercambioId,
                'orden' => 1,
                'titulo' => '1. Publica tu artículo',
                'descripcion' => 'Crea una cuenta y sube fotos junto con una descripción del objeto que deseas intercambiar.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaIntercambioId,
                'orden' => 2,
                'titulo' => '2. Busca un intercambio',
                'descripcion' => 'Explora los artículos disponibles en nuestra plataforma y encuentra uno que te interese.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaIntercambioId,
                'orden' => 3,
                'titulo' => '3. Propuesta de intercambio',
                'descripcion' => 'Contacta al usuario que ofrece el artículo que deseas y propón el intercambio.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaIntercambioId,
                'orden' => 4,
                'titulo' => '4. Aceptación y envío',
                'descripcion' => 'Una vez que ambos acuerden el intercambio, coordinaremos los detalles de envío. Recuerda que cada usuario cubrirá el costo de enviar su respectivo artículo.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaIntercambioId,
                'orden' => 5,
                'titulo' => '5. Confirmación',
                'descripcion' => 'Confirma que recibiste el artículo en buen estado y completa el intercambio.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 2. ¿Cómo vender?
        $paginaVenderId = DB::table('ayuda_paginas')->insertGetId([
            'slug' => 'como-vender',
            'titulo' => '¿Cómo vender?',
            'descripcion' => 'Vender en Cámbialo RD es simple y seguro. Aquí te mostramos cómo hacerlo:',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ayuda_pasos')->insert([
            [
                'ayuda_pagina_id' => $paginaVenderId,
                'orden' => 1,
                'titulo' => '1. Regístrate',
                'descripcion' => 'Crea una cuenta en nuestra plataforma para comenzar a vender.',
                'imagen' => '/imgs/tutorial/comovender/01.png',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaVenderId,
                'orden' => 2,
                'titulo' => '2. Publica tu artículo',
                'descripcion' => 'Sube fotos claras y detalladas del objeto que deseas vender, junto con una descripción precisa y el precio.',
                'imagen' => '/imgs/tutorial/comovender/02.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaVenderId,
                'orden' => 3,
                'titulo' => '3. Vende de forma directa',
                'descripcion' => 'Los compradores añadirán tu artículo a su carrito y realizarán el pago de forma segura a través de la plataforma sin complicaciones.',
                'imagen' => '/imgs/tutorial/comovender/03.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaVenderId,
                'orden' => 4,
                'titulo' => '4. Envío',
                'descripcion' => 'Una vez que se concrete la venta, coordinaremos el envío del producto al comprador.',
                'imagen' => '/imgs/tutorial/comovender/04.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaVenderId,
                'orden' => 5,
                'titulo' => '5. Recibe tu pago',
                'descripcion' => 'Tras la confirmación de recepción por parte del comprador, recibirás tu pago a través de la plataforma.',
                'imagen' => '/imgs/tutorial/comovender/05.jpeg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. ¿Cómo realizar una compra?
        $paginaCompraId = DB::table('ayuda_paginas')->insertGetId([
            'slug' => 'realizar-compra',
            'titulo' => '¿Cómo realizar una compra?',
            'descripcion' => 'Comprar en Cámbialo RD es seguro y sencillo. Sigue estos pasos:',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('ayuda_pasos')->insert([
            [
                'ayuda_pagina_id' => $paginaCompraId,
                'orden' => 1,
                'titulo' => '1. Registrate',
                'descripcion' => 'Crea una cuenta en nuestra plataforma.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaCompraId,
                'orden' => 2,
                'titulo' => '2. Busca lo que necesitas: ',
                'descripcion' => 'Explora las categorías o utiliza la barra de búsqueda para encontrar lo que estás buscando.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaCompraId,
                'orden' => 3,
                'titulo' => '3. Compra',
                'descripcion' => 'Una vez que encuentres un producto de tu interés, revisa la descripción y fotos proporcionadas. Si estás satisfecho, realiza la compra a través de la plataforma.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaCompraId,
                'orden' => 4,
                'titulo' => '4. Pago',
                'descripcion' => 'Elige tu método de pago preferido y completa la transacción. El costo de envío se sumará al precio del producto.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'ayuda_pagina_id' => $paginaCompraId,
                'orden' => 5,
                'titulo' => '5. Recibe tu compra',
                'descripcion' => 'Coordinaremos la entrega recibirás tu producto en la comodidad de tu hogar.',
                'imagen' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
};
