<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'file' => ['required','image','max:5120'], // 5MB
        ]);

        $path = $request->file('file')->store('quiz', 'public');
        return response()->json([
            'path' => $path,
            'url'  => Storage::disk('public')->url($path),
        ]);
    }
}
