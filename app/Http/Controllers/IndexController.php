<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class IndexController extends Controller
{
    public function uploadFile(Request $request)
    {
        $image = $request->file('image');

        $path = $image->store('public/upload');

        $originalImagePath = storage_path('app/' . $path);
        $watermarkPath = public_path('logo.png'); // Path to your watermark image
        $outputImagePath = storage_path('app/public/upload/' . $image->hashName());

        $imageInfo = getimagesize($originalImagePath);
        $imageType = $imageInfo[2];

        if ($imageType == IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($originalImagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $image = imagecreatefrompng($originalImagePath);
        } else {
            exit("Unsupported image type");
        }

        $watermark = imagecreatefrompng($watermarkPath);

        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

        $destX = 0;
        $destY = 0;

        imagecopy($image, $watermark, $destX, $destY, 0, 0, $watermarkWidth, $watermarkHeight);

        if ($imageType == IMAGETYPE_JPEG) {
            imagejpeg($image, $outputImagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            imagepng($image, $outputImagePath);
        }

        imagedestroy($image);
        imagedestroy($watermark);

        return Redirect::to(asset('storage/upload/' . basename($path)));
    }
}
