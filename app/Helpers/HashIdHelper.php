<?php

namespace App\Helpers;

use Hashids\Hashids;

class HashIdHelper
{
    private static function hashids(): Hashids
    {
        return new Hashids(config('app.key'), 6);
    }

    public static function encode(int $id): string
    {
        return self::hashids()->encode($id);
    }

    public static function decode(string $hash): ?int
    {
        $decoded = self::hashids()->decode($hash);
        return $decoded[0] ?? null;
    }
}
