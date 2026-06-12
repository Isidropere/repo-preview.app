<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Resultado del Pago' }} - Cámbialo RD</title>
    <!-- Outfit Font -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Outfit', sans-serif;
            background-color: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            color: #1e293b;
        }
        .container {
            text-align: center;
            background: white;
            padding: 3rem 2rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 400px;
            width: 90%;
            border: 1px solid #f1f5f9;
        }
        .icon-container {
            width: 5rem;
            height: 5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }
        .success-bg {
            background-color: #f0fdf4;
            color: #15803d;
            border: 2px solid #dcfce7;
        }
        .error-bg {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 2px solid #fee2e2;
        }
        h1 {
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
            color: #0f172a;
        }
        p {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        .btn-action {
            display: block;
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 0.85rem 1.5rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
            box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);
        }
        .btn-action:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        @if($success)
            <div class="icon-container success-bg">
                <svg style="width: 3rem; height: 3rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1>¡Transacción Exitosa!</h1>
            <p>{{ $message }}</p>
        @else
            <div class="icon-container error-bg">
                <svg style="width: 3rem; height: 3rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </div>
            <h1>Pago no completado</h1>
            <p>{{ $message }}</p>
        @endif

        <a href="javascript:window.close();" class="btn-action" id="closeBtn">Volver a la Aplicación</a>
    </div>

    <script>
        // Si el esquema deep link está soportado, podemos auto-redirigir a la app después de 3 segundos
        @if($success)
        setTimeout(function() {
            window.location.href = "cambialo://payment/success";
        }, 2500);
        @else
        setTimeout(function() {
            window.location.href = "cambialo://payment/failure";
        }, 2500);
        @endif

        // Fallback: si window.close() no funciona porque no se abrió vía JS
        document.getElementById('closeBtn').addEventListener('click', function(e) {
            // Intentar deep link si window.close falla
            setTimeout(function() {
                window.location.href = "cambialo://home";
            }, 500);
        });
    </script>
</body>
</html>
