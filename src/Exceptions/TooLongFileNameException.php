<?php

namespace Iconscout\ImageResizer\Exceptions;

use Exception as BaseException;

class TooLongFileNameException extends BaseException
{
    /**
     * @var string
     */
    protected $filename;
    
    /**
     * Constructor.
     *
     * @param string        $filename
     * @param int           $code
     * @param BaseException $previous
     */
    public function __construct($filename, $code = 0, BaseException $previous = null)
    {
        $this->filename = $filename;
        parent::__construct('File having too long filename: ' . $this->getFileName(), $code, $previous);
    }

    /**
     * Get the filename which was found.
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->filename;
    }
}