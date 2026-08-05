<?php
// ============================================================================
// Endpoint API: /auth/delete_account
// Este código debe integrarse en el controlador de Auth de tu API
// (Ej. AuthController.php en Laravel, o tu archivo de rutas).
// ============================================================================

// Lógica sugerida para el Controlador:
/*
public function deleteAccount(Request $request)
{
    $user = auth()->user(); // Obtener usuario autenticado por token JWT

    if (!$user) {
        return response()->json(['message' => 'No autenticado'], 401);
    }

    // Si el usuario no es de Google, requerimos contraseña para mayor seguridad
    if (empty($user->google_id)) {
        $request->validate([
            'password' => 'required|string'
        ]);

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Contraseña incorrecta'], 400);
        }
    }

    try {
        // Eliminar los artículos y talentos del usuario (Hard Delete)
        \App\Models\Articulo::where('user_id', $user->id)->delete();
        \App\Models\Talento::where('user_id', $user->id)->delete();
        
        // Eliminar el usuario
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cuenta y datos eliminados permanentemente.'
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al eliminar la cuenta.'
        ], 500);
    }
}
*/
?>
