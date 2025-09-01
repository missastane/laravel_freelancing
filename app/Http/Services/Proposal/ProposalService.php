<?php

namespace App\Http\Services\Proposal;

use App\Http\Services\Chat\ChatService;
use App\Models\Market\Conversation;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use App\Notifications\AddNewProposal;
use App\Notifications\ProposalUpdatedNotification;
use App\Notifications\WithdrawProposalNotification;
use App\Repositories\Contracts\Market\ProposalMilstoneRepositoryInterface;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Traits\ApiResponseTrait;
use Exception;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Notification\SubscriptionUsageManagerService;
class ProposalService
{
    protected User $user;
    public function __construct(
        protected ProposalApprovalService $proposalApprovalService,
        protected ProposalRepositoryInterface $proposalRepository,
        protected ProposalMilstoneRepositoryInterface $proposalMilstoneRepository,
        protected ChatService $chatService
    ) {
        $this->user = auth()->user();
    }
    public function getProposals(Project $project = null, User $user = null, array $data): Paginator|Project
    {
        return $this->proposalRepository->getProposals($project, $user, $data);
    }

    public function storeProposal(Project $project, array $data)
    {
        $user = auth()->user();

        $usageService = app(SubscriptionUsageManagerService::class, ['user' => $user]);

        if (!$usageService->canUse('target_create')) {
            throw new Exception("شما به حداکثر تعداد مجاز ایجاد پیشنهاد رسیده‌اید.");
        }
        $proposal = DB::transaction(function ($project) use ($data) {
            $user = auth()->user();
            $proposal = $this->proposalRepository->create([
                'project_id' => $project->id,
                'freelancer_id' => $user->id,
                'description' => $data['description']
            ]);
            foreach ($data['milstones'] as $index => $milstone) {
                $this->proposalMilstoneRepository->create($proposal, [
                    'title' => $milstone['title'],
                    'description' => $milstone['description'],
                    'amount' => $milstone['amount'],
                    'duration_time' => $milstone['duration_time'],
                ]);
            }
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

    public function showProposal(Proposal $proposal): array
    {
        $conversation = $this->getConversation($proposal);
        return [
            'proposal' => $this->proposalRepository->showProposal($proposal),
            'conversation' => $conversation ? $conversation : null
        ];
    }

    public function updateProposal(Proposal $proposal, array $data): Proposal
    {
        $updatedProposal = DB::transaction(function ($proposal) use ($data) {
            $this->proposalRepository->update($proposal, [
                'description' => $data['description']
            ]);
            if ($data['milstones']) {
                $this->proposalMilstoneRepository->deleteProposlMilestones($proposal);
                foreach ($data['milstones'] as $index => $milstone) {
                    $this->proposalMilstoneRepository->create($proposal, [
                        'title' => $milstone['title'],
                        'description' => $milstone['description'],
                        'amount' => $milstone['amount'],
                        'duration_time' => $milstone['duration_time'],
                    ]);
                }
            }
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

    public function delete(Proposal $proposal)
    {
        return $this->proposalRepository->delete($proposal);
    }
}