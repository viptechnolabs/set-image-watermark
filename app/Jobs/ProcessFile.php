<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $imagePath;

    public function __construct($imageUrl)
    {
        // Remove the base URL and decode the URL
        $this->imagePath = urldecode(str_replace('/storage/', '', parse_url($imageUrl, PHP_URL_PATH)));
        // Ensure the path separator is correct for the operating system
        $this->imagePath = str_replace('/', DIRECTORY_SEPARATOR, $this->imagePath);
    }

    public function handle()
    {
        $originalImagePath = storage_path('app/' . $this->imagePath);
        $watermarkPath = public_path('logo.png'); // Path to your watermark image
        $outputImagePath = storage_path('app/public/upload/' . basename($this->imagePath));

        // Verify original image exists
        if (!file_exists($originalImagePath)) {
            Log::error('Original image not found: ' . $originalImagePath);
            return;
        }

        // Verify watermark image exists
        if (!file_exists($watermarkPath)) {
            Log::error('Watermark image not found: ' . $watermarkPath);
            return;
        }

        // Get image type
        $imageInfo = getimagesize($originalImagePath);
        if (!$imageInfo) {
            Log::error('Failed to get image info: ' . $originalImagePath);
            return;
        }
        $imageType = $imageInfo[2];

        // Load the original image
        $image = match ($imageType) {
            IMAGETYPE_JPEG => imagecreatefromjpeg($originalImagePath),
            IMAGETYPE_PNG => imagecreatefrompng($originalImagePath),
            default => null,
        };
        if (!$image) {
            Log::error('Failed to load image: ' . $originalImagePath);
            return;
        }

        // Load the watermark image
        $watermark = imagecreatefrompng($watermarkPath);
        if (!$watermark) {
            Log::error('Failed to load watermark: ' . $watermarkPath);
            imagedestroy($image);
            return;
        }

        // Merge the images
        $result = imagecopy($image, $watermark, 0, 0, 0, 0, imagesx($watermark), imagesy($watermark));
        if (!$result) {
            Log::error('Failed to merge images');
            imagedestroy($image);
            imagedestroy($watermark);
            return;
        }

        // Ensure the output directory exists
        $outputDirectory = storage_path('app/public/upload/');
        if (!file_exists($outputDirectory)) {
            mkdir($outputDirectory, 0755, true); // Create directory recursively
        }

        // Save the modified image
        if ($imageType == IMAGETYPE_JPEG) {
            $success = imagejpeg($image, $outputImagePath);
        } elseif ($imageType == IMAGETYPE_PNG) {
            $success = imagepng($image, $outputImagePath);
        }

        // Check if saving was successful
        if (!$success) {
            Log::error('Failed to save image: ' . $outputImagePath);
        } else {
            Log::info('Watermarked image saved: ' . $outputImagePath);
        }

        // Clean up resources
        imagedestroy($image);
        imagedestroy($watermark);

        if ($success) {
            Log::info('Watermarked image saved: ' . $outputImagePath);
        }
    }
}

