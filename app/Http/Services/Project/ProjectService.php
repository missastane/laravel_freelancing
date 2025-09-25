<?php

namespace App\Http\Services\Project;

use App\Exceptions\ProjectAddLimitException;
use App\Exceptions\ProjectViewLimitException;
use App\Http\Services\Favorite\FavoriteService;
use App\Http\Services\FileManagemant\FileManagementService;
use App\Http\Services\Public\MediaStorageService;
use App\Jobs\SendNotificationForNewProject;
use App\Jobs\SendNotificationForUpdatingProject;
use App\Models\Market\File;
use App\Models\Market\Project;
use App\Models\User\User;
use App\Notifications\RemoveProjectNotification;
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
        protected ProposalRepositoryInterface $proposalRepository,
        protected FavoriteService $favoriteService,
        protected FileManagementService $fileManagementService
    ) {
    }

    public function getProjects(array $data)
    {
        return $this->projectRepository->getProjects($data);
    }
    public function getUserPrjects(?User $user = null, array $data)
    {
        return $this->projectRepository->getUserProjects($user, $data);
    }

    public function options(): array
    {
        return [
            'categories' => $this->projectCategoryRepository->getOptions(),
            'skills' => $this->skillRepository->skillOption()
        ];
    }
    public function storeProject(array $data): Project
    {
        $user = auth()->user();

        $project = DB::transaction(function () use ($data, $user) {
            $data['user_id'] = $user->id;

            $project = $this->projectRepository->create($data);

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

        $freelancers = $this->userRepository->getFreelancerWithSkills($data['skills']);
        SendNotificationForNewProject::dispatch($freelancers);
        return $project;
    }

    public function viewProjectDetails(Project $project): array
    {
        $user = auth()->user();
        $usageService = app(SubscriptionUsageManagerService::class, ['user' => $user]);

        if (!$usageService->canUse('view_details')) {
            throw new ProjectViewLimitException();
        }
        $stats = $this->proposalRepository->getProjectProposalsStats($project);
        // مصرف کاربر یکی اضافه میشه
        $usageService->increamentUsage('view_details');
        return [
            'project' => $project,
            'stats' => $stats
        ];
    }

    public function showProject(Project $project)
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
        // $project->is_full_time = $project->is_full_time === 1 ? 2 : 1;
        // if ($project->save()) {
        //     $message = $project->is_full_time == 1 ?
        //         'پروژه به حالت تمام وقت درآمد' :
        //         'پروژه از حالت تمام وقت خارج شد';
        //     return $message;
        // }
        // return null;
    }

    public function addToFavorite(Project $project)
    {
        $inputs = [];
        $inputs['favoritable_type'] = Project::class;
        $inputs['favoritable_id'] = $project->id;
        return $this->favoriteService->addToFavorite($inputs);
    }

    public function removeFavorite(Project $project)
    {
        return $this->favoriteService->removeFavorite($project);
    }
    public function deleteProjectFile(File $file)
    {
        return $this->fileManagementService->deleteFile($file);
    }
    public function deleteProject(Project $project)
    {
        $this->projectRepository->delete($project);
        if(auth()->user()->active_role === 'admin'){
            $project->employer->notify(new RemoveProjectNotification('پروژه شما به دلیل نقص قوانین سایت توسط ادمین حذف شد'));
        }
        return true;
    }
}