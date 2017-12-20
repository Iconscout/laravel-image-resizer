<?php

namespace Iconscout\ImageResizer;

trait ConfigAwareTrait
{
    protected $config;

    protected function setConfig($config)
    {
        $this->config = $config ? Util::ensureConfig($config) : new Config;
    }

    public function getConfig()
    {
        return $this->config;
    }

    protected function prepareConfig(array $config)
    {
        $config = new Config($config);

        $config->setFallback($this->getConfig());
        
        return $config;
    }
}