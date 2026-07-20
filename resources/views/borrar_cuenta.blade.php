<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambialord - Eliminar Cuenta y Datos</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #F9FAFB;
            margin: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            background-color: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
            max-width: 450px;
            width: 100%;
            text-align: center;
            border: 1px solid #E5E7EB;
        }
        h2 {
            color: #EF4444;
            margin-bottom: 10px;
        }
        p {
            color: #6B7280;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 25px;
        }
        .input-group {
            margin-bottom: 15px;
            text-align: left;
        }
        label {
            display: block;
            font-size: 13px;
            color: #374151;
            margin-bottom: 5px;
            font-weight: 600;
        }
        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #D1D5DB;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 14px;
        }
        input:focus {
            outline: none;
            border-color: #EF4444;
            box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
        }
        button {
            width: 100%;
            padding: 12px;
            background-color: #EF4444;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
            transition: background-color 0.2s;
            margin-top: 10px;
        }
        button:hover {
            background-color: #DC2626;
        }
        .alert {
            background-color: #FEF2F2;
            color: #991B1B;
            padding: 12px;
            border-radius: 6px;
            border: 1px solid #F87171;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }
        .message-success { background-color: #ECFDF5; color: #065F46; padding: 12px; border-radius: 6px; border: 1px solid #10B981; margin-bottom: 20px; text-align: left; }
        .message-error { background-color: #FEF2F2; color: #991B1B; padding: 12px; border-radius: 6px; border: 1px solid #EF4444; margin-bottom: 20px; text-align: left; }
    </style>
</head>
<body>

    <div class="container">
        <h2>Solicitud de Eliminación de Cuenta</h2>
        <div class="alert">
            <strong>Atención:</strong> Esta acción es irreversible. Se borrarán permanentemente tus datos personales, historial de intercambios, artículos publicados y talentos.
        </div>

        @if(session('success'))
            <div class="message-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="message-error">{{ session('error') }}</div>
        @endif
        
        <p>Para solicitar la eliminación de tu cuenta y todos los datos asociados, por favor ingresa tus credenciales. La eliminación se procesará de forma inmediata.</p>

        <form action="{{ route('borrar_cuenta.process') }}" method="POST">
            @csrf
            <div class="input-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" required placeholder="correo@ejemplo.com">
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="Tu contraseña">
            </div>

            <button type="submit">Eliminar mi cuenta definitivamente</button>
        </form>

        <p style="margin-top: 20px; font-size: 12px;">
            Si iniciaste sesión con Google, contáctanos a soporte@cambialord.com indicando tu correo electrónico para procesar tu solicitud manualmente.
        </p>
    </div>

</body>
</html>
