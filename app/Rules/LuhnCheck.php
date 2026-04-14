<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida un número de tarjeta usando el algoritmo de Luhn.
 */
class LuhnCheck implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $number = preg_replace('/\D/', '', $value);

        if (strlen($number) < 13 || strlen($number) > 19) {
            $fail('El número de tarjeta debe tener entre 13 y 19 dígitos.');
            return;
        }

        $sum = 0;
        $alt = false;

        for ($i = strlen($number) - 1; $i >= 0; $i--) {
            $n = (int) $number[$i];
            if ($alt) {
                $n *= 2;
                if ($n > 9) $n -= 9;
            }
            $sum += $n;
            $alt = !$alt;
        }

        if ($sum % 10 !== 0) {
            $fail('El número de tarjeta no es válido.');
        }
    }
}
