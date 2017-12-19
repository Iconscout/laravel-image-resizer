<?php

namespace Iconscout\ImageResizer;

class ImageResizer
{
    protected $config;

    public function __construct($config = null)
    {
        $this->config = $config;
    }

    public function type(string $type)
    {
        return new ImageType($type, $this->config);
    }
}