<?php

namespace Iconscout\ImageResizer\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Storage;
use Illuminate\Http\File;
use Intervention\Image\ImageManager as InterImage;
use Iconscout\ImageResizer\Util;
use Iconscout\ImageResizer\Config;
use Iconscout\ImageResizer\ImageFile;

class ImageResizer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $crop;
    private $rotate;
    private $imageFile;
    private $typeConfig;
    private $defaultDisk;
    private $defaultFilename;
    private $defaultExtension;
    private $uncroppedImage;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(ImageFile $imageFile, Config $typeConfig, string $defaultDisk, array $crop = null, array $rotate = null)
    {
        $this->crop = $crop;
        $this->rotate = $rotate;
        $this->imageFile = $imageFile;
        $this->typeConfig = $typeConfig;
        $this->defaultDisk = $defaultDisk;
        $this->defaultFilename = $this->imageFile->getBaseName();
        $this->defaultExtension = $this->imageFile->extension;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $interConfig = config('image');
        $this->interImage = new InterImage($interConfig);

        $sizes = $this->typeConfig->get('sizes');

        if ($this->rotate) { 
            $rotate = $this->rotate;
            $fullpath = $this->imageFile->fullpath;

            $img = $this->interImage->make($fullpath);
            $img->orientate();
            $img->rotate($rotate['angle']);

            $file = Util::createNewTempFile();
            $img->save($file);

            $this->uncroppedImage = $this->imageFile;
            $this->imageFile = new ImageFile($file);

            if ($this->typeConfig->has('uncropped')) {
                $uncropped = $this->typeConfig->get('uncropped');
                $disk = empty($uncropped['disk']) ? $this->defaultDisk : $uncropped['disk'];
                Storage::disk($disk)->putFileAs($uncropped['path'], new File($this->uncroppedImage->fullpath), $this->defaultFilename);
            }
        }

        if ($this->crop) { 
            $crop = $this->crop;
            $fullpath = $this->imageFile->fullpath;

            $img = $this->interImage->make($fullpath);
            $img->orientate();
            $img->crop($crop['width'], $crop['height'], $crop['x'], $crop['y']);

            $file = Util::createNewTempFile();
            $img->save($file);

            $this->uncroppedImage = $this->imageFile;
            $this->imageFile = new ImageFile($file);

            if ($this->typeConfig->has('uncropped')) {
                $uncropped = $this->typeConfig->get('uncropped');
                $disk = empty($uncropped['disk']) ? $this->defaultDisk : $uncropped['disk'];
                Storage::disk($disk)->putFileAs($uncropped['path'], new File($this->uncroppedImage->fullpath), $this->defaultFilename);
            }
        }

        $original = $this->typeConfig->get('original');
        $disk = empty($original['disk']) ? $this->defaultDisk : $original['disk'];
        Storage::disk($disk)->putFileAs($original['path'], new File($this->imageFile->fullpath), $this->defaultFilename);

        foreach ($sizes as $size => $dimensions) {
            $this->resizeImage($size, $dimensions);            
        }

        $interConfig = null;
        $sizes = null;
        $this->interImage = null;
    }

    /**
     * Resize Image according to the config and save it to the target
     * @param string $fullpath 
     * @param string $target 
     * @param array $size 
     * @param string|null $size_string 
     * @return void
     */
    protected function resizeImage(string $size, array $dimensions)
    {
        $fullpath = $this->imageFile->fullpath;
        $img = $this->interImage->make($fullpath);

        // Reset Image Rotation before doing any activity
        $img->orientate();

        // Check if height or width is set to auto then resize must be according to the aspect ratio
        $width = $dimensions['width'];
        $height = $dimensions['height'];
        $stretch = $dimensions['stretch'];
        $extension = $dimensions['extension'];
        $watermark = $dimensions['watermark'];

        if ($extension === null)
            $extension = $this->defaultExtension;

        if ($width == null || $height == null){
            $img->resize($width, $height, function ($constraint) {
                $constraint->aspectRatio();
            });
        } elseif ($stretch) {
            $img->resize($width, $height);
        } else {
            $img->fit($width, $height);
        }

        if ($watermark && file_exists($watermark['source'])) {
            $img->insert($watermark['source'], $watermark['position'], $watermark['x'], $watermark['y']);
        }

        $img = $img->encode($extension);
        $resource = $img->stream()->detach();

        $output = $this->typeConfig->get('base')['path'].'/'.$size.'/'.$this->defaultFilename;
        $file = Storage::disk($this->defaultDisk)->put($output, $resource);

        $img->destroy();
    }
}
