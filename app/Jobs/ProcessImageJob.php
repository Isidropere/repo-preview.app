<?php

namespace App\Jobs;

use App\Models\ImageFile;
use App\Services\ImageProcessorInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class ProcessImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $tempPath;
    protected int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(string $tempPath, int $userId)
    {
        $this->tempPath = $tempPath;
        $this->userId    = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(ImageProcessorInterface $processor): void
    {
        // Resolve absolute path of the temporary file
        $absolute = Storage::disk(config('image.disk'))->path($this->tempPath);

        // Procesar la imagen (redimensionar, crear variantes, guardar)
        $imageRecord = $processor->process($absolute, $this->userId);

        // Eliminar el archivo temporal
        Storage::disk(config('image.disk'))->delete($this->tempPath);
    }
}
