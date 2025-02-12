<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ScormContentController extends Controller
{
    public function serve($path)
    {
        Log::info('Serving SCORM content', ['path' => $path]);
        
        $fullPath = Storage::disk('scorm')->path($path);
        Log::info('Full path', ['fullPath' => $fullPath]);
        
        if (!file_exists($fullPath)) {
            Log::error('SCORM content file not found', [
                'path' => $path,
                'fullPath' => $fullPath,
                'storage_path' => storage_path('app/scorm'),
                'files_in_dir' => is_dir(dirname($fullPath)) ? scandir(dirname($fullPath)) : 'directory not found'
            ]);
            abort(404);
        }
        
        $mimeType = File::mimeType($fullPath);
        
        return response()->file($fullPath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline',
            'X-Content-Type-Options' => 'nosniff'
        ]);
    }
}
