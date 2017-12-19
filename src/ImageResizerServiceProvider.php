<?php

namespace Iconscout\ImageResizer;

use Illuminate\Support\ServiceProvider;

class ImageResizerServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('imageresizer.php'),
        ], 'imageresizer');
    }
    
    /**
     * Register the application services.ImageResizer
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/config.php', 'imageresizer'
        );

        $this->app->singleton(ImageResizer::class, function () {
            return new ImageResizer($this->app['config']->get('imageresizer'));
        });
        
        $this->app->alias(ImageResizer::class, 'imageresizer');
    }
}