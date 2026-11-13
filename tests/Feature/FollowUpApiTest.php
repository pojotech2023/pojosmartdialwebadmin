<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class FollowUpApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_api_returns_follow_up_history_across_months_excluding_nope_and_account_audits(): void
    {
        $admin = $this->createUser('admin', 'api-followup-admin@example.com');
        $telecaller = $this->createUser('telecaller', 'api-followup-telecaller@example.com');
        $account = $this->createUser('account', 'api-followup-account@example.com');
        $lead = $this->createLead($telecaller, 'January Customer');
        $otherLead = $this->createLead($telecaller, 'February Customer');

        $november = $this->createReminder($telecaller, $lead, '2026-11-30', 'Call in November', 'Follow Up');
        $december = $this->createReminder($telecaller, $otherLead, '2026-12-01', 'Call in December', null);
        $this->createReminder($telecaller, $lead, '2026-11-29', 'Nope reminder', 'NoPe');
        $this->createReminder($account, $lead, '2026-11-30', 'Account status audit', 'IN PROGRESS');

        $token = $this->loginToken($admin);

        $this->withToken($token)->getJson('/api/follow-ups?emp_id=' . $admin->id)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'id' => $november->id,
                'customer_name' => 'January Customer',
                'telecaller_name' => $telecaller->name,
            ])
            ->assertJsonFragment([
                'id' => $december->id,
                'customer_name' => 'February Customer',
            ])
            ->assertJsonPath('data.1.notification_status', '')
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 1)
            ->assertJsonPath('pagination.per_page', 20)
            ->assertJsonPath('pagination.total', 2)
            ->assertDontSee('Nope reminder')
            ->assertDontSee('Account status audit');
    }

    public function test_telecaller_api_returns_only_their_own_follow_up_reminders(): void
    {
        $telecaller = $this->createUser('telecaller', 'api-followup-owner@example.com');
        $otherTelecaller = $this->createUser('telecaller', 'api-followup-other@example.com');
        $lead = $this->createLead($telecaller, 'My Customer');
        $otherLead = $this->createLead($otherTelecaller, 'Other Customer');
        $ownReminder = $this->createReminder($telecaller, $lead, '2026-11-12', 'My reminder', 'New');
        $this->createReminder($otherTelecaller, $otherLead, '2026-11-12', 'Other reminder', 'New');

        $token = $this->loginToken($telecaller);

        $this->withToken($token)->getJson('/api/follow-ups?emp_id=' . $telecaller->id)
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $ownReminder->id)
            ->assertJsonPath('data.0.product_type', 'Website')
            ->assertJsonPath('data.0.customer_name', 'My Customer')
            ->assertJsonPath('pagination.total', 1)
            ->assertDontSee('Other reminder');

        $this->assertSame([
            'notification_date',
            'notification_time',
            'customer_name',
            'company_name',
            'mobile',
            'telecaller_name',
            'product_type',
            'notification_status',
            'remarks',
            'id',
            'assign_base_id',
            'is_sent',
        ], array_keys($this->getJson('/api/follow-ups?emp_id=' . $telecaller->id)->json('data.0')));
    }

    public function test_api_supports_search_and_pagination(): void
    {
        $admin = $this->createUser('admin', 'api-followup-search-admin@example.com');
        $telecaller = $this->createUser('telecaller', 'api-followup-search-telecaller@example.com');
        $firstLead = $this->createLead($telecaller, 'Search Customer One');
        $secondLead = $this->createLead($telecaller, 'Search Customer Two');
        $first = $this->createReminder($telecaller, $firstLead, '2026-11-12', 'Matching search phrase', 'New');
        $second = $this->createReminder($telecaller, $secondLead, '2026-11-13', 'Matching search phrase', 'New');

        $token = $this->loginToken($admin);

        $this->withToken($token)->getJson('/api/follow-ups?emp_id=' . $admin->id . '&search=Matching&per_page=1&page=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $second->id)
            ->assertJsonPath('pagination.current_page', 2)
            ->assertJsonPath('pagination.last_page', 2)
            ->assertJsonPath('pagination.per_page', 1)
            ->assertJsonPath('pagination.total', 2);
    }

    public function test_account_user_cannot_access_mobile_follow_up_api(): void
    {
        $account = $this->createUser('account', 'api-followup-denied@example.com');

        $accountToken = Crypt::encryptString(implode('|', [
            'mobile-follow-ups',
            $account->id,
            now()->addDay()->timestamp,
        ]));

        $this->withToken($accountToken)->getJson('/api/follow-ups?emp_id=' . $account->id)
            ->assertForbidden()
            ->assertJsonPath('status', false);
    }

    public function test_api_requires_token_and_rejects_an_emp_id_not_in_that_token(): void
    {
        $admin = $this->createUser('admin', 'api-followup-token-admin@example.com');
        $telecaller = $this->createUser('telecaller', 'api-followup-token-telecaller@example.com');

        $this->getJson('/api/follow-ups?emp_id=' . $admin->id)
            ->assertUnauthorized()
            ->assertJsonPath('status', false);

        $telecallerToken = $this->loginToken($telecaller);
        $this->withToken($telecallerToken)->getJson('/api/follow-ups?emp_id=' . $admin->id)
            ->assertForbidden()
            ->assertJsonPath('status', false);
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role) . ' API Follow Up',
            'email' => $email,
            'mobile' => '9' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function loginToken(User $user): string
    {
        $response = $this->postJson('/api/login', [
            'mobile' => $user->mobile,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('status', true);

        $token = $response->json('follow_up_token');
        $this->assertNotEmpty($token);

        return $token;
    }

    private function createLead(User $telecaller, string $customerName): AssignBase
    {
        return AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => $customerName,
            'company_name' => 'Example Company',
            'product_type' => 'Website',
            'status' => 'FOLLOW UP',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);
    }

    private function createReminder(
        User $creator,
        AssignBase $lead,
        string $date,
        string $remarks,
        ?string $status
    ): Notification {
        return Notification::create([
            'emp_id' => $creator->id,
            'user_id' => $creator->id,
            'assign_base_id' => $lead->id,
            'notification_date' => $date,
            'notification_time' => '10:00:00',
            'remarks' => $remarks,
            'status' => $status,
        ]);
    }
}
