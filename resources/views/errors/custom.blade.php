<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Algo salió mal! - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #f58634; /* Naranja CambialóRD */
            --secondary: #479bd5; /* Azul CambialóRD */
            --bg: #0f172a; /* Azul muy oscuro para contraste premium */
            --text: #ffffff;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Roboto', 'Outfit', sans-serif;
            background: var(--bg);
            color: var(--text);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            text-align: center;
            padding: 20px;
        }

        .container {
            max-width: 600px;
            position: relative;
            z-index: 10;
        }

        .error-card {
            background: rgba(30, 41, 59, 0.7);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 134, 52, 0.2); /* Borde con toque naranja */
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.8s ease-out;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(to right, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        p {
            font-size: 1.1rem;
            color: var(--text-muted);
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .error-id-container {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 2rem;
            border-left: 4px solid var(--primary);
        }

        .label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            display: block;
            margin-bottom: 5px;
        }

        .error-id {
            font-family: monospace;
            font-size: 1.2rem;
            color: #fff;
            word-break: break-all;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(to right, var(--primary), #e67520);
            color: white;
            text-decoration: none;
            padding: 12px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 10px 15px -3px rgba(245, 134, 52, 0.4);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 20px 25px -5px rgba(245, 134, 52, 0.5);
        }

        /* Abstract Background Elements */
        .circle {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            z-index: 1;
        }
        .circle-1 {
            width: 300px; height: 300px;
            background: var(--primary);
            top: -100px; left: -100px;
            opacity: 0.2;
        }
        .circle-2 {
            width: 250px; height: 250px;
            background: var(--secondary);
            bottom: -50px; right: -50px;
            opacity: 0.15;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="circle circle-1"></div>
    <div class="circle circle-2"></div>

    <div class="container">
        <div class="error-card">
            <h1>Ocurrió un error inesperado</h1>
            <p>Lo sentimos mucho, algo no salió como esperábamos en nuestros servidores. Hemos registrado el incidente para solucionarlo lo antes posible.</p>
            
            <div class="error-id-container">
                <span class="label">ID de Referencia del Error</span>
                <span class="error-id">{{ $error_reference ?? 'N/A' }}</span>
            </div>

            <p style="font-size: 0.9rem; margin-bottom: 2rem;">Si el problema persiste, por favor contacte a soporte técnico proporcionando el ID de referencia superior.</p>

            <a href="{{ url('/') }}" class="btn">Volver al Inicio</a>
        </div>
    </div>
</body>
</html>
