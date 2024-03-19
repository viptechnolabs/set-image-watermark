<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessImage;
use Illuminate\Http\Request;

class IndexController extends Controller
{
    public function uploadFile(Request $request)
    {
        $image = $request->file('image');

        $path = $image->store('public/upload');

        // Dispatch a job to process the image
        ProcessImage::dispatch($path);

        return response()->json(['message' => 'Image uploaded successfully.']);
    }
}
