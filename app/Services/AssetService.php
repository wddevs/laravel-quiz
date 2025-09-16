<?php

namespace App\Services;

use App\Models\Asset;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AssetService
{
    public function upload(string $input = 'files', string $disk = 'public', ?string $collection = null)
    {
        $files = request()->file($input);
        if (!$files) return [];

        if (!is_array($files)) $files = [$files];

        $assets = [];
        foreach ($files as $file) {
            /** @var UploadedFile $file */
            $dir = 'uploads/' . date('Y') . '/' . date('m');
            $name = Str::uuid()->toString() . '.' . strtolower($file->getClientOriginalExtension());
            Storage::disk($disk)->putFileAs($dir, $file, $name);

            $assets[] = Asset::create([
                'user_id'       => Auth::id(),
                'disk'          => $disk,
                'path'          => "$dir/$name",
                'original_name' => $file->getClientOriginalName(),
                'mime_type'     => $file->getClientMimeType(),
                'extension'     => strtolower($file->getClientOriginalExtension()),
                'file_size'     => $file->getSize(),
                'collection'    => $collection,
            ]);
        }

        return count($assets) === 1 ? $assets[0] : $assets;
    }

    public function fromUrl(string $url, string $disk = 'public', ?string $collection = null): Asset
    {
        $contents = file_get_contents($url);
        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';

        $dir = 'uploads/' . date('Y') . '/' . date('m');
        $name = Str::uuid()->toString() . '.' . strtolower($ext);
        $path = "$dir/$name";

        Storage::disk($disk)->put($path, $contents);

        return Asset::create([
            'user_id'       => Auth::id(),
            'disk'          => $disk,
            'path'          => $path,
            'original_name' => basename($url),
            'mime_type'     => mime_content_type(Storage::disk($disk)->path($path)) ?: null,
            'extension'     => strtolower($ext),
            'file_size'     => Storage::disk($disk)->size($path),
            'collection'    => $collection,
        ]);
    }
}
