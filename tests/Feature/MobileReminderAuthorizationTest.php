<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class MobileReminderAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_telecaller_login_can_create_a_reminder_for_their_own_lead(): void
    {
        Queue::fake();

        $telecaller = $this->createUser('telecaller', 'reminder-telecaller@example.com');
        $lead = $this->createLead($telecaller);

        $login = $this->postJson('/api/login', [
            'mobile' => $telecaller->mobile,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('is_telecaller', true);

        $token = $login->json('access_token');
        $this->assertNotEmpty($token);

        $this->withToken($token)->postJson('/api/save-notification', [
            'emp_id' => $telecaller->id,
            'assign_base_id' => $lead->id,
            'notification_date' => now()->addDay()->format('d/m/Y'),
            'notification_time' => '09:30',
            'remarks' => 'Call Raj',
            'status' => 'Follow Up',
        ])->assertOk()
            ->assertJsonPath('status', true);

        $this->assertDatabaseHas('notifications', [
            'emp_id' => $telecaller->id,
            'user_id' => $telecaller->id,
            'assign_base_id' => $lead->id,
            'remarks' => 'Call Raj',
        ]);
    }

    public function test_admin_and_account_tokens_cannot_create_mobile_reminders(): void
    {
        foreach (['admin', 'account'] as $role) {
            $user = $this->createUser($role, "reminder-{$role}@example.com");
            $token = Crypt::encryptString(implode('|', [
                'mobile-reminders',
                $user->id,
                now()->addDay()->timestamp,
            ]));

            foreach ([
                '/api/create-notification',
                '/api/save-notification',
                '/api/send-push-notification',
                '/api/schedule-notification',
            ] as $endpoint) {
                $this->withToken($token)->postJson($endpoint, [])
                    ->assertForbidden()
                    ->assertJsonPath('message', 'Only Telecallers can create mobile reminders');
            }
        }

        $this->assertSame(0, Notification::count());
    }

    public function test_telecaller_cannot_create_a_reminder_for_another_telecallers_lead(): void
    {
        $telecaller = $this->createUser('telecaller', 'reminder-owner@example.com');
        $otherTelecaller = $this->createUser('telecaller', 'reminder-other@example.com');
        $otherLead = $this->createLead($otherTelecaller);
        $token = Crypt::encryptString(implode('|', [
            'mobile-reminders',
            $telecaller->id,
            now()->addDay()->timestamp,
        ]));

        $this->withToken($token)->postJson('/api/save-notification', [
            'emp_id' => $telecaller->id,
            'assign_base_id' => $otherLead->id,
            'notification_date' => now()->addDay()->format('d/m/Y'),
            'notification_time' => '09:30',
            'remarks' => 'Unauthorized reminder',
        ])->assertForbidden()
            ->assertJsonPath('message', 'This customer is not assigned to your employee account');

        $this->assertSame(0, Notification::count());
    }

    public function test_mobile_reminder_creation_requires_a_login_token(): void
    {
        $this->postJson('/api/save-notification', [])
            ->assertUnauthorized()
            ->assertJsonPath('message', 'A Telecaller login token is required');
    }

    private function createUser(string $role, string $email): User
    {
        return User::create([
            'name' => ucfirst($role) . ' Reminder User',
            'email' => $email,
            'mobile' => '9' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role' => $role,
        ]);
    }

    private function createLead(User $telecaller): AssignBase
    {
        return AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => 'Raj Customer',
            'company_name' => 'Example Company',
            'status' => 'FOLLOW UP',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);
    }
}
