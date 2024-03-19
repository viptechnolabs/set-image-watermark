<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImage;
use Illuminate\Http\Request;

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
}
