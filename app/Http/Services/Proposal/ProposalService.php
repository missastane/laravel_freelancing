<?php

namespace App\Http\Services\Proposal;

use App\Http\Resources\Market\ShowProposalResource;
use App\Http\Services\Chat\ChatService;
use App\Http\Services\Favorite\FavoriteService;
use App\Models\Market\Conversation;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Notifications\AddNewProposal;
use App\Notifications\ProposalUpdatedNotification;
use App\Notifications\WithdrawProposalNotification;
use App\Repositories\Contracts\Market\ProposalMilestoneRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use Exception;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Notification\SubscriptionUsageManagerService;
class ProposalService
{
    protected User $user;
    public function __construct(
        protected ProposalApprovalService $proposalApprovalService,
        protected ProposalRepositoryInterface $proposalRepository,
        protected ProposalMilestoneRepositoryInterface $proposalMilstoneRepository,
        protected ChatService $chatService,
        protected FavoriteService $favoriteService
    ) {
        $this->user = auth()->user();
    }
    public function getProposals(string $status)
    {
        return $this->proposalRepository->getProposals($status);
    }

    public function getProjectProposals(Project $project, string $status)
    {
        return $this->proposalRepository->getProjectProposals($project, $status);
    }
    public function storeProposal(Project $project, array $data)
    {
        $user = auth()->user();
        $exists = $this->proposalRepository->existsForProjectAndFreelancer($project->id, $user->id);
        if ($exists) {
            throw new Exception("شما قبلاً برای این پروژه پیشنهاد ارسال کرده‌اید.");
        }
        $usageService = app(SubscriptionUsageManagerService::class, ['user' => $user]);

        if (!$usageService->canUse('target_create')) {
            throw new Exception("شما به حداکثر تعداد مجاز ایجاد پیشنهاد رسیده‌اید.", 429);
        }
        $proposal = DB::transaction(function () use ($project, $data, $usageService) {
            $user = auth()->user();
            $proposal = $this->proposalRepository->create([
                'project_id' => $project->id,
                'freelancer_id' => $user->id,
                'description' => $data['description']
            ]);
            $totalAmount = 0;
            $totalDuration = 0;
            $currentDate = now();

            foreach ($data['milestones'] as $index => $milestone) {
                $dueDate = $currentDate->copy()->addDays((int) $milestone['duration_time']);
                $this->proposalMilstoneRepository->create([
                    'proposal_id' => $proposal->id,
                    'title' => $milestone['title'],
                    'description' => $milestone['description'],
                    'amount' => $milestone['amount'],
                    'duration_time' => $milestone['duration_time'],
                    'due_date' => $dueDate,
                ]);
                $totalAmount += (int) $milestone['amount'];
                $totalDuration += (int) $milestone['duration_time'];
                $currentDate = $dueDate;
            }
            $this->proposalRepository->update($proposal, [
                'total_amount' => $totalAmount,
                'due_date' => now()->addDays($totalDuration),
            ]);
            $usageService->increamentUsage('target_create');
            return $proposal;
        });
        $employer = $project->employer;
        $employer->notify(new AddNewProposal("پیشنهاد جدید برای پروژه شما ثبت شد", $project));
        return $proposal;
    }

    protected function getConversation(Proposal $proposal): Conversation|null
    {
        $user = auth()->user();
        if (!in_array($user->active_role, ['freelancer', 'employer'])) {
            return null;
        }
        return $this->chatService->getOrCreateConversationForProposal($proposal);
    }

    public function showProposal(Proposal $proposal)
    {
        $conversation = $this->getConversation($proposal);
        $result = [
            'proposal' => $this->proposalRepository->showProposal($proposal),
            'conversation' => $conversation ? $conversation : null
        ];
        return new ShowProposalResource($result);
    }

    public function updateProposal(Proposal $proposal, array $data): Proposal
    {
        $updatedProposal = DB::transaction(function () use ($proposal, $data) {
            $this->proposalRepository->update($proposal, [
                'description' => $data['description']
            ]);
            $totalAmount = 0;
            $totalDuration = 0;
            $currentDate = now();
            if ($data['milestones']) {
                $this->proposalMilstoneRepository->deleteProposlMilestones($proposal);
                foreach ($data['milestones'] as $index => $milestone) {
                    $dueDate = $currentDate->copy()->addDays((int) $milestone['duration_time']);
                    $this->proposalMilstoneRepository->create([
                        'proposal_id' => $proposal->id,
                        'title' => $milestone['title'],
                        'description' => $milestone['description'],
                        'amount' => $milestone['amount'],
                        'duration_time' => $milestone['duration_time'],
                        'due_date' => $dueDate,
                    ]);
                    $totalAmount += (int) $milestone['amount'];
                    $totalDuration += (int) $milestone['duration_time'];
                    $currentDate = $dueDate;
                }
            }
            $this->proposalRepository->update($proposal, [
                'total_amount' => $totalAmount,
                'due_date' => now()->addDays($totalDuration),
            ]);
            return $proposal;
        });
        $employer = $proposal->project->employer;
        $employer->notify(new ProposalUpdatedNotification($updatedProposal));
        return $updatedProposal;
    }

    public function approveProposal(Proposal $proposal)
    {
        return $this->proposalApprovalService->approveProposal($proposal);
    }
    public function withdrawProposal(Proposal $proposal)
    {
        $proposal->status = 4;
        $proposal->save();
        $freelancer = $proposal->freelancer;
        $freelancer->notify(new WithdrawProposalNotification($proposal));
        return $proposal;
    }

    public function addProposalToFavorite(Proposal $proposal)
    {
        $inputs = [];
        $inputs['favoritable_type'] = Proposal::class;
        $inputs['favoritable_id'] = $proposal->id;
        return $this->favoriteService->addToFavorite($inputs);
    }
    
    public function removeFavorite(Proposal $proposal)
    {
        return $this->favoriteService->removeFavorite($proposal);
    }
    public function delete(Proposal $proposal)
    {
        return $this->proposalRepository->delete($proposal);
    }
}