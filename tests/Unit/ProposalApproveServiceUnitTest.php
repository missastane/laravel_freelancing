<?php

namespace Tests\Unit;

use App\Models\Market\Project;
use App\Models\Payment\Wallet;
use Tests\TestCase;
use App\Models\User\User;
use App\Models\Market\Proposal;
use App\Models\Market\Order;
use App\Models\Market\OrderItem;
use Illuminate\Support\Facades\Notification;
use App\Http\Services\Proposal\ProposalApprovalService;
use App\Http\Services\Chat\ChatService;
use App\Repositories\Contracts\Market\ProposalRepositoryInterface;
use App\Repositories\Contracts\Market\ProjectRepositoryInterface;
use App\Repositories\Contracts\Market\OrderRepositoryInterface;
use App\Repositories\Contracts\Market\OrderItemRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletRepositoryInterface;
use App\Repositories\Contracts\Payment\WalletTransactionRepositoryInterface;
use App\Repositories\Contracts\Market\ConversationRepositoryInterface;
use Mockery;

class ProposalApproveServiceUnitTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_approve_proposal_unit_with_mock_status()
    {
        // Fake Notification
        Notification::fake();

        // Arrange
        $client = User::factory()->employer()->create();
        $wallet = Wallet::factory()->create([
            'user_id' => $client->id,
            'balance' => 1000
        ]);
        $this->be($client); 
        $freelancer = User::factory()->freelancer()->create();

        $project = Project::factory()->create(['user_id' => $client->id]);
        // مدل Proposal بدون persist در DB
        $proposal = Proposal::factory()->create([
            'freelancer_id' => $freelancer->id,
            'project_id' => $project->id,
            'status' => 1,
            'total_amount' => 500,
            'description' => 'پیشنهاد تستی برای پروژه',
        ]);

        // ChatService mock
        $chatService = Mockery::mock(ChatService::class);
        $chatService->shouldReceive('createConversation')->once()->andReturn(true);

        // ConversationRepository mock
        $conversationRepo = Mockery::mock(ConversationRepositoryInterface::class);
        $conversationRepo->shouldReceive('getConversationIfExists')->once()
         ->with($freelancer->id, $client->id, Proposal::class, $proposal->id)->andReturnNull();

        // OrderRepository mock
        $orderRepo = Mockery::mock(OrderRepositoryInterface::class);
        $orderRepo->shouldReceive('create')->once()->andReturn(new Order());

        // OrderItemRepository mock
        $orderItemRepo = Mockery::mock(OrderItemRepositoryInterface::class);
        $orderItemRepo->shouldReceive('create')->andReturnTrue();
        $orderItemRepo->shouldReceive('getFirstPendingItem')->andReturn(new OrderItem());
        $orderItemRepo->shouldReceive('update')->andReturnTrue();

        // WalletRepository mock
        $walletRepo = Mockery::mock(WalletRepositoryInterface::class);
        $walletRepo->shouldReceive('hasEnoughBalance')->with($client->id, 500)->andReturn(true);
        $walletRepo->shouldReceive('update')->andReturnTrue();

        // WalletTransactionRepository mock
        $walletTransactionRepo = Mockery::mock(WalletTransactionRepositoryInterface::class);
        $walletTransactionRepo->shouldReceive('create')->once()->andReturnTrue();

        // ProposalRepository mock
        $proposalRepo = Mockery::mock(ProposalRepositoryInterface::class);

        // وقتی update صدا زده شد، status مدل رو تغییر بده
        $proposalRepo->shouldReceive('update')->with($proposal, ['status' => 2])
            ->once()
            ->andReturnUsing(function($model, $data) {
                $model->status = $data['status'];
                return true;
            });

        $proposalRepo->shouldReceive('updateWhere')->once()->andReturnTrue();

        // ProjectRepository mock
        $projectRepo = Mockery::mock(ProjectRepositoryInterface::class);
        $projectRepo->shouldReceive('update')->once()->andReturnTrue();

        // سرویس رو با mock ها بساز
        $service = new ProposalApprovalService(
            $chatService,
            $conversationRepo,
            $orderRepo,
            $orderItemRepo,
            $walletTransactionRepo,
            $walletRepo,
            $proposalRepo,
            $projectRepo,

        );

        // Act
        $result = $service->approveProposal($proposal);

        // Assert
        $this->assertEquals(2, $proposal->status, 'Status باید به 2 تغییر کرده باشد');

        $proposalRepo->shouldHaveReceived('update')->with($proposal, ['status' => 2]);
        $proposalRepo->shouldHaveReceived('updateWhere');
        $projectRepo->shouldHaveReceived('update');

        Notification::assertSentTo(
            [$freelancer],
            \App\Notifications\ApproveProposalNotification::class
        );
    }
}
