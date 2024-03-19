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

// Add watermark
        $originalImagePath = storage_path('app/' . $path);
        $watermarkPath = public_path('Group.png'); // Path to your watermark image
        $outputImagePath = storage_path('app/public/upload/' . $image->hashName());

// Get the image type
        $imageInfo = getimagesize($originalImagePath);
        $imageType = $imageInfo[2];

// Load the image based on its type
        if ($imageType == IMAGETYPE_JPEG) {
            $image = imagecreatefromjpeg($originalImagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $image = imagecreatefrompng($originalImagePath);
        } else {
            // Handle unsupported image types or other errors
            // You might want to log an error or throw an exception here
            // For simplicity, let's assume an error occurred and exit
            exit("Unsupported image type");
        }

// Open the watermark image
        $watermark = imagecreatefrompng($watermarkPath);

// Get dimensions of original image and watermark
        $imageWidth = imagesx($image);
        $imageHeight = imagesy($image);
        $watermarkWidth = imagesx($watermark);
        $watermarkHeight = imagesy($watermark);

// Calculate the position for placing the watermark at the top left corner
        $destX = 0;
        $destY = 0;

// Merge the images
        imagecopy($image, $watermark, $destX, $destY, 0, 0, $watermarkWidth, $watermarkHeight);

// Save the modified image
        if ($imageType == IMAGETYPE_JPEG) {
            imagejpeg($image, $outputImagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            imagepng($image, $outputImagePath);
        }

// Clean up resources
        imagedestroy($image);
        imagedestroy($watermark);

        // Return the URL to the watermarked image
        return Redirect::to(asset('storage/upload/' . basename($path)));
        // Return the path to the watermarked image
        /*return asset('storage/upload/' . basename($path));*/


        /* dd($image);*/
    }
}
