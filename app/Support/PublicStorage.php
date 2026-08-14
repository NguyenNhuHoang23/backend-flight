<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicStorage
{
    public static function store(UploadedFile $file, string $directory): string
    {
        return $file->store($directory, 'public');
    }

    public static function normalizePath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = trim($path);

        if (str_contains($path, '/storage/')) {
            $path = substr($path, strpos($path, '/storage/') + 9);
        } elseif (str_starts_with($path, 'storage/')) {
            $path = substr($path, 8);
        }

        return ltrim($path, '/');
    }

    public static function url(?string $path): ?string
    {
        $normalizedPath = self::normalizePath($path);

        if ($normalizedPath === null) {
            return null;
        }

        return Storage::disk('public')->url($normalizedPath);
    }

    public static function delete(?string $path): void
    {
        $normalizedPath = self::normalizePath($path);

        if ($normalizedPath === null) {
            return;
        }

        if (Storage::disk('public')->exists($normalizedPath)) {
            Storage::disk('public')->delete($normalizedPath);
        }
    }
}
