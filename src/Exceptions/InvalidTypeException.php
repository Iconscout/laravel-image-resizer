<?php

namespace Iconscout\ImageResizer\Exceptions;

use Exception as BaseException;

class InvalidTypeException extends BaseException
{
    /**
     * @var string
     */
    protected $type;
    
    /**
     * Constructor.
     *
     * @param string        $type
     * @param int           $code
     * @param BaseException $previous
     */
    public function __construct($type, $code = 0, BaseException $previous = null)
    {
        $this->type = $type;
        parent::__construct('Invalid imageresizer type: ' . $this->getType(), $code, $previous);
    }

    /**
     * Get the type which was found.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }
}
