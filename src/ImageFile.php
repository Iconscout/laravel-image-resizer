<?php

namespace Iconscout\ImageResizer;

class ImageFile
{
    /**
     * Mime type
     *
     * @var string
     */
    public $mime;
    
    /**
     * Original Name given by user of current file
     *
     * @var string
     */
    public $originalname;

    /**
     * Filename of current file (without extension)
     *
     * @var string
     */
    public $filename;

    /**
     * File extension of current file
     *
     * @var string
     */
    public $extension;
    
    /**
     * File dimensions of current file
     *
     * @var string
     */
    public $dimensions;

    /**
     * Full Location of current file
     *
     * @var string
     */
    public $fullpath;


    public function __construct(string $path = null)
    {
        if (! empty($path)) {
            $this->setFileInfoFromPath($path);
        }
    }

    /**
     * File name of current file
     *
     * @var string
     */
    
    public function getBaseName()
    {
        return "{$this->filename}.{$this->extension}";
    }

    /**
     * Sets all instance properties from given path
     *
     * @param string $path
     */
    public function setFileInfoFromPath($path)
    {
        $info = pathinfo($path);
        $this->fullpath = $path;
        $this->dirname = array_key_exists('dirname', $info) ? $info['dirname'] : null;
        $this->originalname = array_key_exists('basename', $info) ? $info['basename'] : null;
        $this->filename = array_key_exists('filename', $info) ? $info['filename'] : null;
        $this->extension = array_key_exists('extension', $info) ? $info['extension'] : null;

        if (file_exists($path) && is_file($path)) {
            $this->dimensions = $this->dimensions($path);
            $this->mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $path);
        }

        return $this;
    }

    /**
     * Get file size
     * 
     * @return mixed
     */
    public function filesize()
    {
        $path = $this->basePath();

        if (file_exists($path) && is_file($path)) {
            return filesize($path);
        }
        
        return false;
    }

    /**
     * Get image file size
     * 
     * @return mixed
     */
    public function dimensions()
    {
        if (empty($this->dimensions)){
            $path = $this->fullpath;

            if (file_exists($path) && is_file($path)) {

                $sizes = getimagesize($path);
                $dimensions['width'] = $sizes[0];
                $dimensions['height'] = $sizes[1];

                // We need to use exif data as getimagesize provides invalid width, height if image is rotated
                $exif = @exif_read_data($path);

                if (! empty($exif['Orientation'])) {
                    if ($exif['Orientation'] === 8 || $exif['Orientation'] === 6) {
                        // 8 = CW Rotate Image to get original
                        // 6 = CCW Rotate Image to get original

                        // Store width as height & height as width
                        $height = $dimensions['width'];
                        $width = $dimensions['height'];

                        $dimensions['width'] = $width;
                        $dimensions['height'] = $height;
                    }
                }

                $this->dimensions = $dimensions;
            }
        }

        return $this->dimensions;
    }

    /**
     * Check image file is valid or not
     * 
     * @return string
     */
    public function isValid()
    {
        return exif_imagetype($this->fullpath);
    }

    /**
     * Get file path by string
     * 
     * @return string
     */
    public function __toString ()
    {
        return $this->getBaseName();
    }
}