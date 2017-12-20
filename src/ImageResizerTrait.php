<?php

namespace Iconscout\ImageResizer;

trait ImageResizerTrait
{
    protected function getImageResizerFields()
    {
        return array_keys($this->imageresizer_fields);
    }

    protected function getImageResizerType($field)
    {
        return $this->imageresizer_fields[$field];
    }

    public function getAttributeValue($key)
    {
        $value = parent::getAttributeValue($key);

        if (in_array($key, $this->getImageResizerFields())) {
            $value = $this->retrievePhotoFieldValue($this->getImageResizerType($key), $key, $value);
        }

        return $value;
    }

    public function retrievePhotoFieldValue($type, $key, $value)
    {
        return ImageResizer::type($type)->url($value);
    }

    public function toArray()
    {
        $array = parent::toArray();

        foreach ($array as $key => $value) {
            if (in_array($key, $this->getImageResizerFields())) {
                if (! empty($value))
                    $array[$key] = json_decode($value, true);
            }
        }

        return $array;
    }
}
