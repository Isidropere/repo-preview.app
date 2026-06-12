<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirigiendo a AZUL - Cámbialo RD</title>
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
            padding: 3rem;
            border-radius: 1.5rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            max-width: 450px;
            width: 90%;
            border: 1px solid #f1f5f9;
        }
        .spinner {
            width: 4rem;
            height: 4rem;
            border: 4px solid #e2e8f0;
            border-top: 4px solid #2563eb;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 2rem;
        }
        h1 {
            font-size: 1.5rem;
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
        .security-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background-color: #f0fdf4;
            color: #15803d;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            border: 1px solid #dcfce7;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .hidden-btn {
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 0.5rem;
            cursor: pointer;
            display: none;
            margin: 1rem auto 0;
            transition: background-color 0.2s;
        }
        .hidden-btn:hover {
            background-color: #1d4ed8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="spinner"></div>
        <h1>Conectando con la pasarela de pagos</h1>
        <p>Por favor espera un momento mientras te redirigimos de forma segura al portal de <strong>AZUL</strong> para completar tu pago.</p>
        
        <div class="security-badge">
            <svg style="width: 1rem; height: 1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            Conexión Cifrada SSL / PCI-DSS
        </div>

        <form id="redirectForm" action="{{ $url }}" method="POST">
            @foreach($fields as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach
            <button type="submit" id="submitBtn" class="hidden-btn">Si no eres redirigido, haz clic aquí</button>
        </form>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Auto-submit del formulario tras 500ms para asegurar carga visual
            setTimeout(function() {
                document.getElementById('redirectForm').submit();
            }, 500);

            // Mostrar el botón de respaldo si la redirección tarda más de 5 segundos
            setTimeout(function() {
                document.getElementById('submitBtn').style.display = 'block';
            }, 5000);
        });
    </script>
</body>
</html>
