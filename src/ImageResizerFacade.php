<?php

namespace Iconscout\ImageResizer;

use Illuminate\Support\Facades\Facade;

class ImageResizerFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'imageresizer';
    }
}