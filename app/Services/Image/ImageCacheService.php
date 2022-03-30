<?php

namespace App\Services\Image;

use Illuminate\Support\Facades\Config;
use Intervention\Image\Facades\Image;

class ImageCacheService {

    public function cache($imagePath, $size = '')
    {

        $imageSizes = Config::get('image.cache-image-sizes');
        if (!isset($imageSizes[$size])) {
            $size = Config::get('image.default-current-cache-image');
        }

        $width = $imageSizes[$size]['width'];
        $height = $imageSizes[$size]['height'];
        $lifetime =  Config::get('image.image-cache-life-time');
        //cache image

        if (file_exists($imagePath)) {
            
            $img = Image::cache(function($image) use($imagePath, $width, $height) {
                return $image->make($imagePath)->fit($width, $height);
            }, $lifetime, true);
            return $img->response();
        } else {
            $img = Image::canvas($width, $height, '#777')->text('image not found!', $width/2, $height/2, function($font){
                $font->color('#000');
                $font->align('center');
                $font->valign('center');
            });
            return $img->response();
        }


    }

}