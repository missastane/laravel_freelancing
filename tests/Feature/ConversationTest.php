<?php

namespace Tests\Feature;

use App\Models\Market\Conversation;
use App\Models\Market\Message;
use App\Models\Market\Order;
use App\Models\Market\Project;
use App\Models\Market\Proposal;
use App\Models\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ConversationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_is_not_member_cannot_access_to_conversation()
    {
        $employer = User::factory()->employer()->create();

        $project = Project::factory()->create(['user_id' => $employer->id]);

        $freelancer = User::factory()->freelancer()->create();

        $proposal = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'description' => 'پیشنهاد تستی برای پروژه',
            'status' => 2,
            'total_amount' => 5000
        ]);

        $order = Order::factory()->create([
            'proposal_id' => $proposal->id,
            'project_id' => $project->id,
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'total_price' => 5000
        ]);

        $conversation = Conversation::factory()->create([
            'employee_id' => $freelancer->id,
            'employer_id' => $employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id
        ]);

        $user = User::factory()->create();
        $this->actingAs($user, 'api')
            ->getJson("/api/message/{$conversation->id}")
            ->assertStatus(403)
            ->assertJson(['status' => false]);
    }

    public function test_conversation_members_can_access_conversation_and_messages()
   {
    $employer = User::factory()->employer()->create();

        $project = Project::factory()->create(['user_id' => $employer->id]);

        $freelancer = User::factory()->freelancer()->create();

        $proposal = Proposal::factory()->create([
            'project_id' => $project->id,
            'freelancer_id' => $freelancer->id,
            'description' => 'پیشنهاد تستی برای پروژه',
            'status' => 2,
            'total_amount' => 5000
        ]);

        $order = Order::factory()->create([
            'proposal_id' => $proposal->id,
            'project_id' => $project->id,
            'employer_id' => $employer->id,
            'freelancer_id' => $freelancer->id,
            'total_price' => 5000
        ]);

        $conversation = Conversation::factory()->create([
            'employee_id' => $freelancer->id,
            'employer_id' => $employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => $order->id
        ]);

        $this->actingAs($freelancer, 'api')
            ->getJson("/api/message/{$conversation->id}")
            ->assertStatus(200)
            ->assertJson(['status' => true]);
   }

    public function test_member_user_can_send_message_to_conversation()
    {
        $user = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();
        $conversation = Conversation::factory()->create([
            'employee_id' => $user->id,
            'employer_id' => $employer->id,
            'conversation_context' => Order::class,
            'conversation_context_id' => Order::factory()->create([
                'freelancer_id' => $user->id,
                'employer_id' => $employer->id,
            ])
        ]);
        $response = $this->actingAs($user,'api')->postJson("/api/message/send/{$conversation->id}",[
              'message' => 'سلام آشنا 👋',
            'files' => [],
        ])
        ->assertStatus(201)->assertJson([
            'status' => true,
            'message' => 'پیام با موفقیت ارسال شد',
        ]);
        $this->assertDatabaseHas('messages',[
            'message' =>  'سلام آشنا 👋',
            'message_context' => Order::class,
            'sender_id' => $user->id
        ]);
    }


     public function test_member_user_can_reply_to_message()
    {
        $user = User::factory()->freelancer()->create();
        $employer = User::factory()->employer()->create();
        $conversation = Conversation::factory()->create([
            'employee_id' => $user->id,
            'employer_id' => $employer->id,
        ]);
        $message = Message::factory()->create([
            'sender_id' => $employer->id,
            'conversation_id' => $conversation->id,
            'message' => 'hi baby'
        ]);
        $response = $this->actingAs($employer, 'api')->postJson("/api/message/reply/{$message->id}", [
            'message' => 'سلام آشنا 👋',
            'files' => [],
        ])
            ->assertStatus(201)->assertJson([
                    'status' => true,
                    'message' => 'پیام با موفقیت پاسخ داده شد',
                ]);
        $this->assertDatabaseHas('messages', [
            'message' => 'سلام آشنا 👋',
            'message_context' => Order::class,
            'sender_id' => $employer->id,
            'parent_id' => $message->id
        ]);
    }
}
