<?php

namespace Iconscout\ImageResizer;

use Storage;
use Exception;
use Carbon\Carbon;
use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\File;
use Illuminate\Http\UploadedFile;
use Iconscout\ImageResizer\Jobs\ImageResizer as ImageResizerJob;
use Iconscout\ImageResizer\Exceptions\InvalidTypeException;
use Iconscout\ImageResizer\Exceptions\InvalidInputException;
use Iconscout\ImageResizer\Exceptions\TooLongFileNameException;

class ImageType
{
    use ConfigAwareTrait;

    protected $type;
    protected $crop;
    protected $rotate;
    protected $filename;
    protected $typeConfig;
    protected $destination;
    protected $expirationTime;
    protected $baseFileDisk;
    protected $baseDiskConfig;
    protected $originalFileDisk;
    protected $originalDiskConfig;
    protected $defaultSizeConfig = [
        'width' => null,
        'height' => null,
        'stretch' => false,
        'extension' => null,
        'watermark' => false
    ];

    public function __construct($type, $config = null)
    {
        $this->type = $type;
        $this->setConfig($config);
        $this->typeConfig = $this->getTypeConfig($type);
        $this->expirationTime = $this->getConfig()->get('expiration-time');

        $this->originalFileDisk = $this->getDiskName('original');
        $this->originalDiskConfig = $this->getDiskConfig($this->originalFileDisk);

        $this->baseFileDisk = $this->getDiskName('base');
        $this->baseDiskConfig = $this->getDiskConfig($this->baseFileDisk);
    }

    public function getTypeConfig($key)
    {
        $types = $this->getConfig()->get('types');

        if (! array_key_exists($key, $types) ) {
            throw new InvalidTypeException($key);
        }

        $config = $types[$key];
        if (isset($config['sizes'])) {
            foreach ($config['sizes'] as $key => &$configSize) {
                $configSize = array_merge($this->defaultSizeConfig, $configSize);
            }
        }

        return new Config($config);
    }

    public function getDiskName(string $type)
    {
        if (! empty($this->typeConfig->get($type)) && array_key_exists('disk', $this->typeConfig->get($type))) {
            return $this->typeConfig->get($type)['disk'];
        }

        return $this->getConfig()->get('disk');
    }

    public function getDiskConfig(string $fileDisk)
    {
        return config("filesystems.disks.{$fileDisk}");
    }

    protected function transferHTTPFile(string $url)
    {
        $guzzleHttp = new GuzzleClient([
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36',
                'Accept'     => 'image/png,image/gif,image/jpeg,image/pjpeg;q=0.9,text/html,application/xhtml+xml,application/xml;q=0.8,*.*;q=0.5'
            ]
        ]);

        $destination = Util::createNewTempFile();

        try {
            $response = $guzzleHttp->get($url, [
                'sink' => $destination
            ]);
        } catch (Exception $e) {
            throw new InvalidInputException($url);
        }

        return $this->putOriginalFile($destination);
    }

    protected function getFileName($filename, $extension)
    {
        $name = "{$filename}.{$extension}";

        if (strlen($name) > 255)
            throw new TooLongFileNameException($name);

        return $name;
    }

    protected function putOriginalFile($source)
    {
        $imageFile = new ImageFile($source);

        if ($imageFile->isValid()) {
            $destination = Util::createNewTempFile($this->filename);
            rename($source, $destination);
            return new ImageFile($destination);
        } else {
            throw new InvalidInputException($source);
        }
    }

    public function putFile($file, string $filename = null)
    {
        if ($file instanceof UploadedFile) {
            $this->filename = $this->getFileName($filename, $file->getClientOriginalExtension());
            $originalImageFile = $this->putOriginalFile($file->getRealPath());
        } elseif (file_exists($file)) {
            $this->filename = $this->getFileName($filename, pathinfo($file, PATHINFO_EXTENSION));
            $originalImageFile = $this->putOriginalFile($file);
        } elseif (filter_var($file, FILTER_VALIDATE_URL)) {
            $this->filename = $this->getFileName($filename, pathinfo($file, PATHINFO_EXTENSION));
            $originalImageFile = $this->transferHTTPFile($file);
        } else {
            throw new InvalidInputException($file);
        }

        $job = new ImageResizerJob($originalImageFile, $this->typeConfig, $this->baseFileDisk, $this->crop, $this->rotate);
        $job->handle();

        return $originalImageFile;
    }

    public function crop($width, $height, $x = 0, $y = 0)
    {
        $this->crop = [
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y
        ];

        return $this;
    }

    public function rotate(float $angle)
    {
        $this->rotate = [
            'angle' => $angle
        ];

        return $this;
    }

    public function url(string $filename = null, $sizes = [], $expirationTime = null)
    {
        if (empty($filename)) {
            return $this->defaultUrl($sizes);
        }
        
        $urls = [];
        $this->expirationTime = empty($expirationTime) ? $this->expirationTime : $expirationTime;

        if (empty($sizes) || in_array('original', $sizes)) {
            $output = $this->typeConfig->get('original')['path'] . '/' . $filename;

            $storage = Storage::disk($this->originalFileDisk);
            if ($this->originalDiskConfig['driver'] === 'local' || empty($this->originalDiskConfig['private'])) {
                $urls['original'] = $storage->url($output);
            } else {
                $urls['original'] = $storage->temporaryUrl($output, Carbon::now()->addMinutes($this->expirationTime));
            }
        }

        $filename = pathinfo($filename);
        $configSizes = $this->typeConfig->get('sizes');

        if ($configSizes) {
            foreach ($configSizes as $size => $dimensions) {
                if (empty($sizes) || in_array($size, $sizes)) {
                    $urls[$size] = $this->imageUrl($filename, $size, $dimensions);
                }
            }
        }

        return $urls;
    }

    public function temporaryUrl(string $filename = null, $sizes = [], $expirationTime = null)
    {
        if (empty($filename)) {
            return $this->defaultUrl($sizes);
        }

        $urls = [];
        $this->expirationTime = empty($expirationTime) ? $this->expirationTime : $expirationTime;

        if (empty($sizes) || in_array('original', $sizes)) {
            $output = $this->typeConfig->get('original')['path'] . '/' . $filename;
            $urls['original'] = Storage::disk($this->originalFileDisk)->temporaryUrl($output, Carbon::now()->addMinutes($this->expirationTime));
        }

        $filename = pathinfo($filename);
        $configSizes = $this->typeConfig->get('sizes');

        if ($configSizes) {
            foreach ($configSizes as $size => $dimensions) {
                if (empty($sizes) || in_array($size, $sizes)) {
                    $urls[$size] = $this->temporaryImageUrl($filename, $size, $dimensions);
                }
            }
        }

        return $urls;
    }

    public function defaultUrl($sizes = [])
    {
        $urls = [];
        $output = $this->typeConfig->get('default');
        $defaultUrl = Storage::disk('local')->url($output);

        if (empty($sizes) || in_array('original', $sizes)) {
            $urls['original'] = $defaultUrl;
        }

        $configSizes = $this->typeConfig->get('sizes');

        foreach ($configSizes as $size => $dimensions) {
            if (empty($sizes) || in_array($size, $sizes)) {
                $urls[$size] = $defaultUrl;
            }
        }

        return $urls;
    }

    protected function imageUrl($filename, $size, $dimensions)
    {
        if ($dimensions['extension'] === null) $dimensions['extension'] = $filename['extension'];

        $output = $this->typeConfig->get('base')['path'] . '/' . $size . '/' . $filename['filename'] . '.' . $dimensions['extension'];

        $storage = Storage::disk($this->baseFileDisk);
        if ($this->baseDiskConfig['driver'] === 'local' || empty($this->baseDiskConfig['private'])) {
            return $storage->url($output);
        } else {
            return $storage->temporaryUrl($output, Carbon::now()->addMinutes($this->expirationTime));
        }
    }

    protected function temporaryImageUrl($filename, $size, $dimensions)
    {
        if ($dimensions['extension'] === null) $dimensions['extension'] = $filename['extension'];

        $output = $this->typeConfig->get('base')['path'] . '/' . $size . '/' . $filename['filename'] . '.' . $dimensions['extension'];
        return Storage::disk($this->baseFileDisk)->temporaryUrl($output, Carbon::now()->addMinutes($this->expirationTime));
    }
}