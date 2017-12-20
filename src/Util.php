<?php

namespace Iconscout\ImageResizer;

class Util
{
    public static function ensureConfig($config)
    {
        if ($config === null) {
            return new Config();
        } elseif ($config instanceof Config) {
            return $config;
        } elseif (is_array($config)) {
            return new Config($config);
        }

        throw new LogicException('A config should either be an array or a ImageResizer\Config object.');
    }

    public static function createNewTempFile($filename = null)
    {
        if ($filename) {
            $folder = sys_get_temp_dir() . '/' . uniqid();
            mkdir($folder, 0777, true);
            return $folder . '/' . $filename;
        }

        return tempnam(sys_get_temp_dir(), 'imageresizer_');
    }
}