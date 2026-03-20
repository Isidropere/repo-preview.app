<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ImageDisplay extends Component
{
    public $item;
    public $classes;
    public $id;
    public $defaultImage;

    public function __construct(
        $item,
        $classes = 'absolute inset-0 w-full h-full object-cover rounded-lg',
        $id = 'imagen_principal_preview',
        $defaultImage = null
    ) {
        $this->item = $item;
        $this->classes = $classes;
        $this->id = $id;
        $this->defaultImage = $defaultImage ?? asset('images/default-item-image.jpg');
    }

    public function render()
    {
        return view('components.image-display');
    }
}
