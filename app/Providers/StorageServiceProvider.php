<?php

namespace App\Providers;

use App\Services\StorageService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;

class StorageServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(StorageService::class, function ($app) {
            return new StorageService(
                Storage::disk(config('services.storage.disk', 'local')),
                config('services.storage.totalUserSpace', 10 * 1024 * 1024),
                config('services.storage.maxFileSize', 20 * 1024 * 1024),
            );
        });
    }
}
