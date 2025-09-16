<?php

// app/Models/Asset.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;


class Asset extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $appends = ['url', 'filesystem_path'];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            $m->uuid ??= (string) Str::uuid();
        });

        // Автовидалення файла з диска
        static::deleting(function (Asset $asset) {
            if ($asset->path && Storage::disk($asset->disk)->exists($asset->path)) {
                Storage::disk($asset->disk)->delete($asset->path);
            }
        });
    }

    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function assetable() { return $this->morphTo(); }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFilesystemPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }
}
