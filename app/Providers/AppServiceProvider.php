<?php

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensurePublicStorageLink();
    }

    protected function ensurePublicStorageLink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (file_exists($link)) {
            return;
        }

        if (! is_dir($target)) {
            File::makeDirectory($target, 0755, true);
        }

        try {
            File::link($target, $link);
        } catch (\Throwable) {
            // Hosting không cho tạo symlink, route /storage/* sẽ phục vụ file.
        }
    }
}
