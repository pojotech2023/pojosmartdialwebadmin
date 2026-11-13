<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\LeadDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class MonthlyTargetTest extends TestCase
{
    use RefreshDatabase;

    public function test_telecaller_login_and_monthly_target_api_return_only_their_current_month_summary(): void
    {
        $this->travelTo(Carbon::parse('2026-11-15 12:00:00'));

        $telecaller = $this->createUser('Telecaller', 'monthly-target-owner@example.com', 1000);
        $otherTelecaller = $this->createUser('Telecaller', 'monthly-target-other@example.com', 9000);

        $currentConfirmedLead = $this->createLead($telecaller, '2026-11-02');
        $this->createLeadDetail($currentConfirmedLead, 'confirmed', '2026-11-10 09:00:00', '₹250.00');

        $thisMonthLead = $this->createLead($telecaller, '2026-11-05');

        $previousMonthLead = $this->createLead($telecaller, '2026-10-25');
        $this->createLeadDetail($previousMonthLead, 'CONFIRMED', '2026-10-29 09:00:00', '400');

        $otherTelecallerLead = $this->createLead($otherTelecaller, '2026-11-03');
        $this->createLeadDetail($otherTelecallerLead, 'CONFIRMED', '2026-11-11 09:00:00', '900');

        $login = $this->postJson('/api/login', [
            'mobile' => $telecaller->mobile,
            'password' => 'password',
        ])->assertOk()
            ->assertJsonPath('data.monthly_target.month', '2026-11')
            ->assertJsonPath('data.monthly_target.monthly_target', 1000)
            ->assertJsonPath('data.monthly_target.archived_amount', 250)
            ->assertJsonPath('data.monthly_target.balance_to_achieve', 750)
            ->assertJsonPath('data.monthly_target.assigned_customer_count', 2);

        $this->withToken($login->json('follow_up_token'))
            ->getJson('/api/telecaller/monthly-target?emp_id=' . $otherTelecaller->id)
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.emp_id', $telecaller->id)
            ->assertJsonPath('data.employee_name', $telecaller->name)
            ->assertJsonPath('data.monthly_target', 1000)
            ->assertJsonPath('data.archived_amount', 250)
            ->assertJsonPath('data.balance_to_achieve', 750)
            ->assertJsonPath('data.assigned_customer_count', 2)
            ->assertDontSee($otherTelecaller->name);
    }

    public function test_monthly_target_api_requires_a_telecaller_login_token(): void
    {
        $this->getJson('/api/telecaller/monthly-target')
            ->assertUnauthorized()
            ->assertJsonPath('status', false);

        $account = $this->createUser('Account', 'monthly-target-account@example.com', 0);
        $accountToken = Crypt::encryptString(implode('|', [
            'mobile-follow-ups',
            $account->id,
            now()->addDay()->timestamp,
        ]));

        $this->withToken($accountToken)
            ->getJson('/api/telecaller/monthly-target')
            ->assertForbidden()
            ->assertJsonPath('status', false);
    }

    private function createUser(string $role, string $email, float $monthlyTarget): User
    {
        return User::create([
            'name' => ucfirst(strstr($email, '@', true)) . ' Monthly Target',
            'email' => $email,
            'mobile' => '9' . str_pad((string) random_int(0, 999999999), 9, '0', STR_PAD_LEFT),
            'password' => 'password',
            'role' => $role,
            'monthly_target' => $monthlyTarget,
        ]);
    }

    private function createLead(User $telecaller, string $assignDate): AssignBase
    {
        return AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => 'Monthly Target Customer',
            'company_name' => 'Example Company',
            'status' => 'FOLLOW UP',
            'mobile' => '9876543210',
            'assign_date' => $assignDate,
        ]);
    }

    private function createLeadDetail(
        AssignBase $lead,
        string $accountStatus,
        string $updatedAt,
        string $price
    ): LeadDetail {
        return LeadDetail::create([
            'assign_base_id' => $lead->id,
            'price' => $price,
            'account_status' => $accountStatus,
            'account_updated_at' => $updatedAt,
        ]);
    }
}
