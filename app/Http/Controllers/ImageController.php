<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreImageRequest;
use App\Jobs\ProcessImageJob;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    /**
     * Store an image and dispatch processing job.
     */
    public function store(StoreImageRequest $request): JsonResponse
    {
        // Guardar archivo temporalmente en el disco "public" bajo carpeta tmp
        $tempPath = $request->file('image')->store('tmp', 'public');

        // Encolar el job (sync driver será ejecutado inmediatamente)
        ProcessImageJob::dispatch($tempPath, $request->user()->id);

        return response()->json([
            'message' => 'Imagen recibida y en proceso de optimización.',
        ]);
    }
}
