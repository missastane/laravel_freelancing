<?php

namespace App\Http\Services\Project;

use App\Http\Services\Public\MediaStorageService;
use App\Jobs\SendNotificationForNewProject;
use App\Jobs\SendNotificationForUpdatingProject;
use App\Models\Market\Project;
use App\Models\User\User;
use App\Repositories\Contracts\Market\ProjectCategoryRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Repositories\Contracts\Market\SkillRepositoryInterface;
use App\Repositories\Contracts\User\UserRepositoryInterface;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Notification\SubscriptionUsageManagerService;
class ProjectService
{
    public function __construct(
        private ProjectRepositoryInterface $projectRepository,
        protected ProjectCategoryRepositoryInterface $projectCategoryRepository,
        protected MediaStorageService $mediaStorageService,
        protected SkillRepositoryInterface $skillRepository,
        protected UserRepositoryInterface $userRepository,
        protected ProposalRepositoryInterface $proposalRepository
    ) {
    }

    public function getProjects(array $data): array
    {
        return [
            'categories' => $this->projectRepository->getProjects($data),
            'skills' => $this->skillRepository->skillOption()
        ];
    }
    public function getUserPrjects(?User $user = null, array $data): Paginator
    {
        return $this->projectRepository->getUserProjects($user, $data);
    }

    public function options()
    {
        return $this->projectCategoryRepository->getOptions();
    }
    public function storeProject(array $data): Project
    {
        $user = auth()->user();

        // اینجا از سرویس محدودیت استفاده می‌کنیم
        $usageService = app(SubscriptionUsageManagerService::class, ['user' => $user]);

        if (!$usageService->canUse('target_create')) {
            throw new Exception("شما به حداکثر تعداد مجاز ایجاد پروژه رسیده‌اید.");
        }

        $project = DB::transaction(function () use ($data, $user, $usageService) {
            $data['user_id'] = $user->id;
            $data['slug'] = 'slug';

            $project = $this->projectRepository->create($data);

            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Project::class,
                $project->id,
                "files/projects/{$project->id}",
                'public'
            );

            $this->projectRepository->syncSkills($project, $data['skills']);

            $usageService->increamentUsage('target_create');

            return $project;
        });

        $freelancers = $this->userRepository->getFreelancerWithSkills($data['skills']);
        SendNotificationForNewProject::dispatch($freelancers);
        return $project;
    }

    public function viewProjectDetails(Project $project): array
    {
        $user = auth()->user();
        $usageService = app(SubscriptionUsageManagerService::class, ['user' => $user]);

        if (!$usageService->canUse('view_details')) {
            throw new Exception("شما به حداکثر تعداد مجاز مشاهده جزئیات پروژه‌ها رسیده‌اید.");
        }
        $stats = $this->proposalRepository->getProjectProposalsStats($project->id);
        // مصرف کاربر یکی اضافه میشه
        $usageService->increamentUsage('view_details');
        return [
            'project' => $project,
            'stats' => $stats
        ];
    }

    public function showProject(Project $project): Project
    {
        return $this->projectRepository->showProject($project);
    }

    public function updateProject(Project $project, array $data)
    {
        $updatedProject = DB::transaction(function () use ($data, $project) {
            $this->projectRepository->update($project, $data);
            $this->mediaStorageService->storeMultipleFiles(
                $data['files'],
                Project::class,
                $project->id,
                "files/projects/{$project->id}",
                'public'
            );
            $this->projectRepository->syncSkills($project, $data['skills']);
            return $project;
        });
        $proposedFreelancers = $this->userRepository->getproposedFreelancers($updatedProject);
        SendNotificationForUpdatingProject::dispatch($proposedFreelancers, $updatedProject);
        return $updatedProject;
    }

    public function toggleFullTime(Project $project)
    {
        $project->is_full_time = $project->is_full_time === 1 ? 2 : 1;
        if ($project->save()) {
            $message = $project->is_full_time == 1 ?
                'پروژه به حالت تمام وقت درآمد' :
                'پروژه از حالت تمام وقت خارج شد';
            return $message;
        }
        return null;
    }

    public function deleteProject(Project $project)
    {
        return $this->projectRepository->delete($project);
    }
}