<?php

if (!function_exists('display_item_image')) {
    function display_item_image(
        $item,
        $containerClass = 'relative group w-100 h-100 border border-gray-300 rounded-lg overflow-hidden',
        $imageClass = 'w-full h-full object-cover rounded-lg',
        $imageId = 'imagen_principal_preview',
        $defaultImage = 'images/default-article.jpg',
        $showFilename = true,
        $filenameClass = 'file-name text-xs text-gray-700 absolute bottom-1 left-1 bg-white bg-opacity-80 px-1 rounded max-w-[90%] truncate'
    ) {
        // Verificamos si hay imagen
        $hasImage = $item->imagenes && $item->imagenes->isNotEmpty();
        $imageName = $hasImage ? $item->imagenes->first()->nombre : '';

        // Ruta de la imagen
        $imagePath = $hasImage
            ? asset('storage/imgs/articulos/items/' . $imageName)
            : asset($defaultImage);

        // HTML de la imagen
        $imageHtml = '<img id="' . $imageId . '" src="' . $imagePath . '" class="' . $imageClass . '" />';

        // Nombre del archivo (solo si se muestra)
        $filenameHtml = '';
        if ($showFilename) {
            $filenameVisibility = $hasImage ? '' : 'hidden';
            $filenameValue = $hasImage ? $imageName : '';
            $filenameHtml = '<span id="imagen_principal_filename" class="' . $filenameClass . ' ' . $filenameVisibility . '">'
                . $filenameValue . '</span>';
        }

        // Contenedor final
        return '
            <div id="image-upload-container" class="' . $containerClass . '">
                ' . $imageHtml . '
                ' . $filenameHtml . '
            </div>';
    }
}
