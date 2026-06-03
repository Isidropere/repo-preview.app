<?php

namespace App\Services;

use App\Contracts\PaymentProviderInterface;
use App\Services\Payments\AzulProvider;
use Illuminate\Support\Facades\Log;

class PagoService
{
    private PaymentProviderInterface $driver;

    private array $drivers = [
        'azul' => AzulProvider::class,
    ];

    public function __construct()
    {
        $this->driver = app(AzulProvider::class);
    }

    public function cobrarTarjeta(float $monto, string $currency, array $datosTarjeta, array $opciones = []): array
    {
        return $this->driver->cobrar($monto, $currency, $datosTarjeta, $opciones);
    }

    public function anularTransaccion(string $transactionId, float $monto, array $opciones = []): array
    {
        return $this->driver->anular($transactionId, $monto, $opciones);
    }

    public function reembolsar(string $transactionId, float $monto, array $opciones = []): array
    {
        return $this->driver->reembolsar($transactionId, $monto, $opciones);
    }

    public function proveedorActivo(): string
    {
        return $this->driver->nombre();
    }

    private function resolverDriver(string $nombre): PaymentProviderInterface
    {
        if (!isset($this->drivers[$nombre])) {
            $disponibles = implode(', ', array_keys($this->drivers));
            throw new \InvalidArgumentException("Driver '{$nombre}' no encontrado. Disponibles: {$disponibles}");
        }
        return app($this->drivers[$nombre]);
    }
}
