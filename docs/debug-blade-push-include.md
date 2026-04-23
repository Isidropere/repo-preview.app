# Por qué @include dentro de @push causa error 500

## El problema

Cuando se usa `@include` dentro de `@push('scripts')` en Laravel Blade, ocurre un error 500 silencioso que mata el proceso PHP sin loguear nada.

## Flujo que recorre hasta el error

```
1. Browser solicita GET /intercambio (usuario autenticado)
2. Laravel enruta a ItemController::showItemsTipo2y3()
3. El controlador consulta la BD → OK
4. Llama a view('blank-intercambiar.intercambio', compact('items'))
5. Blade compila la vista:
   - Procesa @section('content') → OK
   - Procesa @push('scripts') → AQUÍ FALLA
     - Dentro del @push hay: @include('components.modal-intercambio')
     - El archivo modal-intercambio.blade.php contiene HTML con atributos
       que tienen comillas dobles y simples mezcladas
     - Blade intenta compilar el @include en el contexto del @push
     - El compilador de Blade tiene un bug conocido: cuando un @include
       dentro de @push contiene ciertos patrones de HTML (especialmente
       atributos con onmouseover/onmouseout con comillas anidadas),
       el compilador genera PHP inválido
     - PHP lanza un ParseError/FatalError al ejecutar el archivo compilado
     - El proceso muere ANTES de que el handler de excepciones de Laravel
       pueda capturarlo
     - El servidor devuelve 500 sin log
6. El browser recibe 500
```

## Por qué no aparece en el log

El error ocurre en la fase de evaluación del PHP compilado por Blade.
En ese punto, el `register_shutdown_function` de Laravel aún no está
registrado completamente, o el error es tan fatal que PHP termina
antes de ejecutar los handlers.

## La solución aplicada

Separar el HTML del modal del script:

1. HTML del modal → dentro del `@section('content')` (antes del `</main>`)
   - Blade compila HTML puro sin problemas
   
2. JS del modal → archivo estático `public/js/modal-intercambio.js`
   - Sin directivas Blade, sin `{{ }}`, sin `@guest`
   - Las URLs se pasan como variables globales `window._urlXxx`
   
3. Variables de URL → en `@push('scripts')` como `<script>` simple
   - Solo asignaciones de variables, sin HTML, sin directivas complejas

## Regla general

**Nunca usar `@include` dentro de `@push` si el archivo incluido
contiene HTML con atributos que tienen comillas anidadas o
directivas Blade complejas.**

En su lugar:
- Poner el HTML directamente en la vista dentro del `@section`
- Poner el JS en archivos `.js` estáticos en `public/js/`
