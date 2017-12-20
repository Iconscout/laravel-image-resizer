<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Default ImageResizer Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default imageresizer disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'disk' => env('IMAGERESIZER_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Expiration time
    |--------------------------------------------------------------------------
    |
    | By default, if this value is set to null, cached items will never expire.
    | If you are afraid of dead data or if you care about disk space, it may
    | be a good idea to set this value to something like 180 (3 hours).
    |
    */

    'expiration-time' => env('IMAGERESIZER_EXPIRATION_TIME', 180),

    /*
    |--------------------------------------------------------------------------
    | Queue
    |--------------------------------------------------------------------------
    |
    | Whether the image resizing process should be queued
    |
    | Default: false
    |
    */

    'queue' => false,

    /*
    |--------------------------------------------------------------------------
    | Queue Name
    |--------------------------------------------------------------------------
    |
    | If queue is set to true, queue_name will be used to push the jobs in queue
    |
    | Default: 'imageresizer'
    |
    */

    'queue_name' => 'imageresizer',
    

    /*
    |--------------------------------------------------------------------------
    | Dynamic Generate
    |--------------------------------------------------------------------------
    |
    | Provides the support to generate the images dynamically
    | when the request is called by user.
    | Generally used when we are using queue to resize images,
    | it provides the support to generate and show resized images on the go,
    | despite of the job is executed or not.
    | Also can be useful when we want all our images to be re-generated on the go.
    |
    | Default: 'false'
    |
    */

    'dynamic_generate' => false,

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | Provides the support of base url, this url will be used to serve the images.
    | You can use pull-cdn with the Laravel Image Resizer as a base url for production env.
    |
    | Default: ''
    |
    */

    'base_url' => '',

    /*
    |--------------------------------------------------------------------------
    | Ignore Environments
    |--------------------------------------------------------------------------
    |
    | The environments for which base_url will be ignored.
    | Better for local environment testing.
    |
    | Default: 'local'
    |
    */

    'ignore_environments' => array(
        'local',
    ),

    /*
    |--------------------------------------------------------------------------
    | Types of sizes
    |--------------------------------------------------------------------------
    |
    | Defines the types of sizes in which image has to be resized and stored.
    |
    | Appendix:
    | 'profile' => type of the image (you can give any name)
    | 'crop' => 'enabled' => whether the image has to be cropped or not,
    |         'uncropped_image' => path (where the uncropped image has to be saved)
    |                              not required, (if we do not want to save original image)
    | 'compiled' => path where the compiled (resized) images will be saved, by deafult this path will be used to retrive the files
    | 'public' => used when we are storing all the images in Storage folder or in cloud, this path will be used to retrive the images instead of compiled path
    | 'default' => default image file (that has to be retured in case of image not found)
    |                                 (can be useful for setting default profile picture)
    | 'sizes' => sizes of the images with key that has to be resized
    |          'small' (any name can be used)
    |          format: [width, height, ['fit|stretch', ['file type: original, jpg, png, gif', ['animated: if gif image has to be animated in resizing format'] ] ] ]
    |
    |
    | Don't use public_path() function in case of image has to be stored in public folder.
    | (see, 'base', 'default' type values)
    |
    |
    */

    'types' => [
        'profile' => [
            'default' => 'images/profile-default.jpg',
            'original' => [
                'path' => 'profile'
            ],
            'uncropped' => [
                'path' => 'profile/uncropped',
                'disk' => 'spaces'
            ],
            'base' => [
                'path' => 'images/profile',
                'disk' => 'azure'
            ],
            'sizes' => [
                'small' => [
                    'width' => 100,         // Give value as null, if you want to keep aspect ratio based on height
                    'height' => 100,        // Give value as null, if you want to keep aspect ratio based on width
                    'stretch' => false,     // If stretch is false, then image will be fit into the dimensions
                    'extension' => 'jpg',    // If extension is true, then original extension will be used, else defined extension will be given
                    'watermark' => [
                        'source' => 'watermarks/watermark.png',
                        'position' => 'bottom-right',
                        'x' => 10,
                        'y' => 10
                    ]
                ],
                'normal' => [
                    'width' => 300,         // Give value as null, if you want to keep aspect ratio based on height
                    'height' => 300,        // Give value as null, if you want to keep aspect ratio based on width
                    'stretch' => false,     // If stretch is false, then image will be fit into the dimensions
                    'extension' => null     // If extension is null, then original extension will be used, else defined extension will be given
                ],
                'normal' => [
                    'width' => 400,         // Give value as null, if you want to keep aspect ratio based on height
                    'height' => 400,        // Give value as null, if you want to keep aspect ratio based on width
                    'stretch' => true,      // If stretch is false, then image will be fit into the dimensions
                    'extension' => null      // If extension is true, then original extension will be used, else defined extension will be given
                ]
            ]
        ],
    ],


    'valid_extensions' => ['gif', 'jpeg', 'png', 'bmp', 'jpg'],

    /*
    |--------------------------------------------------------------------------
    | Append Random Characters at the end of filename
    |--------------------------------------------------------------------------
    |
    | Whether to append a random 7 characters at the end of file name generated
    | Useful to have unique file names for all the files saved
    |
    | Default: 'true'
    |
    */

    'append_random_characters' => true,

    /*
    |--------------------------------------------------------------------------
    | Clear Invalid Uploaded Files
    |--------------------------------------------------------------------------
    |
    | In case of Upload Image from URL, it may happen that someone tries to upload invalid image,
    | setting this true will delete the file to free the space.
    |
    | Default: 'true'
    |
    */

    'clear_invalid_uploads' => true,

);
