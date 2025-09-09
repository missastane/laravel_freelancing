<?php

namespace App\Providers;

use App\Repositories\Contracts\Content\PostCategoryRepositoryInterface;
use App\Repositories\Contracts\Content\PostRepositoryInterface;
use App\Repositories\Contracts\Content\TagRepositoryInterface;
use App\Repositories\Contracts\Locale\CityRepositoryInterface;
use App\Repositories\Contracts\Locale\ProvinceRepositoryInterface;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use App\Repositories\Contracts\Market\FeatureTypeRepositoryInterface;
use App\Repositories\Contracts\Market\FileRepositoryInterface;
use App\Repositories\Contracts\Market\MessageRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectCategoryRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalMilestoneRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionDefaultFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionDefaultUsageRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionFeatureRepositoryInterface;
use App\Repositories\Contracts\Market\SubscriptionRepositoryInterface;
use App\Repositories\Contracts\Market\UserSubscriptionRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Contracts\Setting\SettingRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketDepartmentRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketMessageRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketPriorityRepositoryInterface;
use App\Repositories\Contracts\Ticket\TicketRepositoryInterface;
use App\Repositories\Contracts\User\FavoriteRepositoryInterface;
use App\Repositories\Contracts\User\NotificationRepositoryInterface;
use App\Repositories\Contracts\User\OTPRepositoryInterface;
use App\Repositories\Contracts\User\PermissionRepositoryInterface;
use App\Repositories\Contracts\User\RoleRepositoryInterface;
use App\Repositories\Contracts\User\UserEducationRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use App\Repositories\Contracts\User\WorkExperienceRepositoryInterface;
use App\Repositories\Eloquent\Content\FileRepository;
use App\Repositories\Eloquent\Content\PostCategoryRepository;
use App\Repositories\Eloquent\Content\PostRepository;
use App\Repositories\Eloquent\Content\TagRepository;
use App\Repositories\Eloquent\Locale\CityRepository;
use App\Repositories\Eloquent\Locale\ProvinceRepository;
use App\Repositories\Eloquent\Market\ConversationRepository;
use App\Repositories\Eloquent\Market\FeatureTypeRepository;
use App\Repositories\Eloquent\Market\MessageRepository;
use App\Repositories\Eloquent\Market\OrderItemRepository;
use App\Repositories\Eloquent\Market\OrderRepository;
use App\Repositories\Eloquent\Market\ProjectCategoryRepository;
use App\Repositories\Eloquent\Market\ProjectRepository;
use App\Repositories\Eloquent\Market\ProposalMilestoneRepository;
use App\Repositories\Eloquent\Market\ProposalRepository;
use App\Repositories\Eloquent\Market\SkillRepository;
use App\Repositories\Eloquent\Market\SubscriptionDefaultFeatureRepository;
use App\Repositories\Eloquent\Market\SubscriptionDefaultUsageRepository;
use App\Repositories\Eloquent\Market\SubscriptionFeatureRepository;
use App\Repositories\Eloquent\Market\SubscriptionRepository;
use App\Repositories\Eloquent\Market\UserSubscriptionRepository;
use App\Repositories\Eloquent\Payment\WalletRepository;
use App\Repositories\Eloquent\Payment\WalletTransactionRepository;
use App\Repositories\Eloquent\Setting\SettingRepository;
use App\Repositories\Eloquent\Ticket\TicketDepartmentRepository;
use App\Repositories\Eloquent\Ticket\TicketMessageRepository;
use App\Repositories\Eloquent\Ticket\TicketPriorityRepository;
use App\Repositories\Eloquent\Ticket\TicketRepository;
use App\Repositories\Eloquent\User\FavoriteRepository;
use App\Repositories\Eloquent\User\NotificationRepository;
use App\Repositories\Eloquent\User\OTPRepository;
use App\Repositories\Eloquent\User\PermissionRepository;
use App\Repositories\Eloquent\User\RoleRepository;
use App\Repositories\Eloquent\User\UserEducationRepository;
use App\Repositories\Eloquent\User\UserRepository;
use App\Repositories\Eloquent\User\WorkExperienceRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(UserEducationRepositoryInterface::class, UserEducationRepository::class);
        $this->app->bind(WorkExperienceRepositoryInterface::class, WorkExperienceRepository::class);
        $this->app->bind(OTPRepositoryInterface::class, OTPRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(ProvinceRepositoryInterface::class, ProvinceRepository::class);
        $this->app->bind(CityRepositoryInterface::class, CityRepository::class);
        $this->app->bind(PostCategoryRepositoryInterface::class, PostCategoryRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
        $this->app->bind(WalletRepositoryInterface::class, WalletRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(OrderItemRepositoryInterface::class, OrderItemRepository::class);
        $this->app->bind(TagRepositoryInterface::class, TagRepository::class);
        $this->app->bind(ProjectCategoryRepositoryInterface::class, ProjectCategoryRepository::class);
        $this->app->bind(ProjectRepositoryInterface::class, ProjectRepository::class);
        $this->app->bind(ProposalRepositoryInterface::class, ProposalRepository::class);
        $this->app->bind(ProposalMilestoneRepositoryInterface::class, ProposalMilestoneRepository::class);
        $this->app->bind(FavoriteRepositoryInterface::class, FavoriteRepository::class);
        $this->app->bind(SkillRepositoryInterface::class, SkillRepository::class);
        $this->app->bind(SubscriptionRepositoryInterface::class, SubscriptionRepository::class);
        $this->app->bind(SubscriptionDefaultUsageRepositoryInterface::class, SubscriptionDefaultUsageRepository::class);
        $this->app->bind(SubscriptionFeatureRepositoryInterface::class, SubscriptionFeatureRepository::class);
        $this->app->bind(WalletTransactionRepositoryInterface::class, WalletTransactionRepository::class);
        $this->app->bind(UserSubscriptionRepositoryInterface::class, UserSubscriptionRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(TicketRepositoryInterface::class, TicketRepository::class);
        $this->app->bind(TicketDepartmentRepositoryInterface::class, TicketDepartmentRepository::class);
        $this->app->bind(TicketPriorityRepositoryInterface::class, TicketPriorityRepository::class);
        $this->app->bind(TicketMessageRepositoryInterface::class, TicketMessageRepository::class);
        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
        $this->app->bind(ConversationRepositoryInterface::class, ConversationRepository::class);
        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
        $this->app->bind(FeatureTypeRepositoryInterface::class, FeatureTypeRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
