<?php
use Illuminate\Support\Str;

if (!function_exists('ucfirst_safe')) {
    /**
     * Convierte a minúsculas y luego el primer carácter a mayúscula de forma segura
     * 
     * @param string|null $value
     * @return string
     */
    function ucfirst_safe($value)
    {
        if (is_null($value)) {
            return '';
        }

        // Elimina espacios en blanco al inicio y final
        $trimmed = trim($value);

        if ($trimmed === '') {
            return '';
        }

        // Convierte todo a minúsculas primero
        $lowercased = mb_strtolower($trimmed, 'UTF-8');

        // Convierte el primer carácter a mayúscula
        return mb_strtoupper(mb_substr($lowercased, 0, 1, 'UTF-8'), 'UTF-8') .
            mb_substr($lowercased, 1, null, 'UTF-8');
    }
}
