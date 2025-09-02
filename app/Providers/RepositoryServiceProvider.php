<?php

namespace App\Providers;

use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\Content\PostRepositoryInterface;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectCategoryRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionDefaultFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionDefaultUsageRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\Market\UserSubscriptionRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
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
use App\Repositories\Eloquent\Market\ProjectCategoryRepository;
use App\Repositories\Eloquent\Market\ProjectRepository;
use App\Repositories\Eloquent\Market\SkillRepository;
use App\Repositories\Eloquent\Market\SubscriptionDefaultFeatureRepository;
use App\Repositories\Eloquent\Market\SubscriptionDefaultUsageRepository;
use App\Repositories\Eloquent\Market\SubscriptionFeatureRepository;
use App\Repositories\Eloquent\Market\SubscriptionRepository;
use App\Repositories\Eloquent\Market\UserSubscriptionRepository;
use App\Repositories\Eloquent\Payment\WalletRepository;
use App\Repositories\Eloquent\Payment\WalletTransactionRepository;
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
        $this->app->bind(ProjectCategoryRepositoryInterface::class, ProjectCategoryRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(SubscriptionDefaultUsageRepositoryInterface::class, SubscriptionDefaultUsageRepository::class);
        $this->app->bind(SubscriptionFeatureRepositoryInterface::class, SubscriptionFeatureRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
        $this->app->bind(UserSubscriptionRepositoryInterface::class, UserSubscriptionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
