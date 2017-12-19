<?php

namespace Iconscout\ImageResizer\Exceptions;

use Exception as BaseException;

class InvalidInputException extends BaseException
{
    /**
     * @var string
     */
    protected $url;

    /**
     * Constructor.
     *
     * @param string        $url
     * @param int           $code
     * @param BaseException $previous
     */
    public function __construct($url, $code = 0, BaseException $previous = null)
    {
        $this->url = $url;
        parent::__construct('File having invalid url: ' . $this->getUrl(), $code, $previous);
    }

    /**
     * Get the url which was found.
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }
}
