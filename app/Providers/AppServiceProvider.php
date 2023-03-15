<?php

namespace App\Providers;

use App\Models\Folder;
use App\Models\Post;
use App\Observers\FolderObserver;
use App\Observers\PostObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Post::observe(PostObserver::class);
        Folder::observe(FolderObserver::class);
    }
}
