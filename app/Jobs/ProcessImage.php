<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Add watermark
        $originalImagePath = storage_path('app/' . $this->path);
        $watermarkPath = public_path('logo.png'); // Path to your watermark image
        $outputImagePath = storage_path('app/public/upload/' . basename($this->path));

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

        // Dispatch an event with the URL to the watermarked image
        event(new ImageProcessed(asset('storage/upload/' . basename($outputImagePath))));

    }
}
