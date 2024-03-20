<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessFile;
use App\Jobs\ProcessImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function uploadFile(Request $request)
    {
        if ($request->hasFile('images')) {
            // Loop through each uploaded file
            foreach ($request->file('images') as $image) {
                // Store each image
                $path = $image->store('public/upload');

                // Dispatch a job to process each image
                ProcessImage::dispatch($path);
            }

            return response()->json(['message' => 'Images uploaded successfully.']);
        } else {
            return response()->json(['message' => 'No images uploaded.'], 400);
        }
    }

    public function getFiles()
    {
        // Define the folder path
        $folderPath = 'public/images';

        // Get all files within the folder
        $files = Storage::files($folderPath);

        // Filter out non-image files (if necessary)
        $imageFiles = array_filter($files, function ($file) {
            return Str::startsWith(mime_content_type(storage_path('app/' . $file)), 'image');
        });

        // Map the file paths to URLs
        $imageUrls = array_map(function ($file) {
            return asset('storage/' . $file);
        }, $imageFiles);

        // Dispatch a ProcessImage job for each image URL
        foreach ($imageUrls as $imageUrl) {
            ProcessFile::dispatch($imageUrl);
        }

        return response()->json(['message' => 'ProcessImage jobs dispatched successfully.']);

    }
}
