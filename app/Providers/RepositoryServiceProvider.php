<?php

namespace App\Providers;

use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\Content\PostRepositoryInterface;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\User\PermissionRepositoryInterface;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Repositories\Eloquent\Content\FileRepository;
use App\Repositories\Eloquent\Content\PostCategoryRepository;
use App\Repositories\Eloquent\Content\PostRepository;
use App\Repositories\Eloquent\Content\TagRepository;
use App\Repositories\Eloquent\Locale\CityRepository;
use App\Repositories\Eloquent\Locale\ProvinceRepository;
use App\Repositories\Eloquent\Market\OrderRepository;
use App\Repositories\Eloquent\Payment\WalletRepository;
use App\Repositories\Eloquent\User\PermissionRepository;
use App\Repositories\Eloquent\User\RoleRepository;
use App\Repositories\Eloquent\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ProvinceRepositoryInterface::class, ProvinceRepository::class);
        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(PostCategoryRepositoryInterface::class, PostCategoryRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
