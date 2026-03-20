<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixImageNames extends Command
{
    protected $signature = 'fix:image-names';
    protected $description = 'Limpia nombres de imágenes con saltos de línea o sin extensión';

    public function handle()
    {
        // Limpiar saltos de línea \n y \r
        $updated = DB::update("UPDATE imagenes_item SET nombre = REPLACE(REPLACE(TRIM(nombre), '\r', ''), '\n', '') WHERE nombre LIKE '%\n%' OR nombre LIKE '%\r%' OR nombre != TRIM(nombre)");
        $this->info("Nombres limpiados: {$updated}");

        // Mostrar los que no tienen extensión conocida
        $sinExt = DB::select("SELECT id_imagen, nombre FROM imagenes_item WHERE nombre NOT REGEXP '\\.(jpg|jpeg|png|webp|gif)$'");
        if (count($sinExt)) {
            $this->warn("Registros sin extensión válida (pueden ser datos legacy):");
            foreach ($sinExt as $r) {
                $this->line("  id={$r->id_imagen} nombre={$r->nombre}");
            }
        } else {
            $this->info("Todos los nombres tienen extensión válida.");
        }
    }
}
