<?php

namespace Tests\Feature;

use App\Models\Conversation;
use App\Models\FundingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminMissingUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // SQLite cannot disable foreign keys inside a transaction, so these
        // legacy-data fixtures need a fresh database without transaction wrapping.
        $this->artisan('migrate:fresh');
        $this->beforeApplicationDestroyed(function () {
            RefreshDatabaseState::$migrated = false;
        });
    }

    public function test_admin_login_renders_conversations_whose_user_no_longer_exists(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['is_admin' => true, 'password' => 'password123']);
        $deletedUser = User::factory()->create();
        $conversation = $deletedUser->conversations()->create(['status' => 'escalated']);
        $currentUser = User::factory()->create(['name' => 'Current conversation owner']);
        $currentUser->conversations()->create(['status' => 'automated']);
        $this->deleteUserWithoutCascading($deletedUser);

        $this->followingRedirects()->post('/login', [
            'email' => $admin->email,
            'password' => 'password123',
        ])->assertOk()
            ->assertSee('ADMIN CONTROL CENTER')
            ->assertSee('Deleted user')
            ->assertSee('Current conversation owner')
            ->assertSee(route('admin.conversations.show', $conversation), false);

        $this->assertAuthenticatedAs($admin);
        $this->assertDatabaseHas('conversations', ['id' => $conversation->id]);
        Mail::assertNothingSent();
    }

    public function test_admin_can_read_a_retained_conversation_with_a_missing_user(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $deletedUser = User::factory()->create();
        $conversation = $deletedUser->conversations()->create(['status' => 'escalated']);
        $conversation->messages()->create([
            'user_id' => null,
            'author_type' => 'human_admin',
            'body' => 'Retained support message.',
        ]);
        $this->deleteUserWithoutCascading($deletedUser);

        $this->actingAs($admin)->get(route('admin.conversations.show', $conversation))
            ->assertOk()
            ->assertSee('Deleted user')
            ->assertSee('Retained support message.')
            ->assertSee('HUMAN ADMIN · Support');
    }

    public function test_funding_queue_keeps_missing_user_records_without_review_actions(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $deletedUser = User::factory()->create();
        $orphanedFunding = $this->createFunding($deletedUser);
        $currentUser = User::factory()->create(['name' => 'Current funding owner']);
        $currentFunding = $this->createFunding($currentUser);
        $this->deleteUserWithoutCascading($deletedUser);

        $this->actingAs($admin)->get('/admin/compliance')
            ->assertOk()
            ->assertSee('Deleted user')
            ->assertSee('Review unavailable: this user no longer exists.')
            ->assertDontSee(route('admin.funding.approve', $orphanedFunding), false)
            ->assertDontSee(route('admin.funding.reject', $orphanedFunding), false)
            ->assertSee('Current funding owner')
            ->assertSee(route('admin.funding.approve', $currentFunding), false)
            ->assertSee(route('admin.funding.reject', $currentFunding), false);

        $this->assertDatabaseHas('funding_requests', [
            'id' => $orphanedFunding->id,
            'status' => 'awaiting_admin_review',
        ]);
    }

    public function test_missing_user_funding_cannot_be_approved_or_rejected_directly(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $deletedUser = User::factory()->create();
        $funding = $this->createFunding($deletedUser);
        $this->deleteUserWithoutCascading($deletedUser);

        $this->actingAs($admin)->post(route('admin.funding.approve', $funding), [
            'review_note' => 'Confirm this request.',
        ])->assertUnprocessable();
        $this->post(route('admin.funding.reject', $funding), [
            'review_note' => 'Reject this request.',
        ])->assertUnprocessable();

        $this->assertDatabaseHas('funding_requests', [
            'id' => $funding->id,
            'status' => 'awaiting_admin_review',
            'reviewed_by' => null,
        ]);
        $this->assertDatabaseCount('compliance_checks', 0);
        $this->assertDatabaseCount('ledger_entries', 0);
    }

    private function createFunding(User $user): FundingRequest
    {
        return FundingRequest::create([
            'user_id' => $user->id,
            'type' => 'deposit',
            'method' => 'wire',
            'asset' => 'USD',
            'network' => 'wire',
            'crypto_amount' => 0,
            'usd_amount' => 100,
            'status' => 'awaiting_admin_review',
        ]);
    }

    private function deleteUserWithoutCascading(User $user): void
    {
        // Legacy hosting databases may not enforce the declared foreign keys.
        Schema::withoutForeignKeyConstraints(function () use ($user) {
            DB::table('users')->where('id', $user->id)->delete();
        });

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
