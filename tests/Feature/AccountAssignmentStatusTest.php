<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountAssignmentStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigning_a_lead_to_an_account_preserves_telecaller_status_and_starts_account_status_as_new(): void
    {
        [$telecaller, $account, $lead] = $this->createAssignmentScenario();

        $this->postJson('/api/assign-lead', [
            'assign_base_id' => $lead->id,
            'emp_id' => $telecaller->id,
            'assigned_to' => $account->id,
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'FOLLOW UP')
            ->assertJsonPath('data.account_status', 'NEW');

        $this->assertDatabaseHas('assign_bases', [
            'id' => $lead->id,
            'assigned_to' => $account->id,
            'status' => 'FOLLOW UP',
            'account_status' => 'NEW',
        ]);
        $this->assertDatabaseHas('lead_details', [
            'assign_base_id' => $lead->id,
            'assigned_to' => $account->id,
            'account_status' => 'NEW',
        ]);

    }

    public function test_saving_lead_details_with_account_assignment_preserves_telecaller_status(): void
    {
        [$telecaller, $account, $lead] = $this->createAssignmentScenario();

        $this->postJson('/api/save-lead-details', [
            'assign_base_id' => $lead->id,
            'emp_id' => $telecaller->id,
            'assigned_to' => $account->id,
            'project_type' => 'Website',
        ])
            ->assertOk()
            ->assertJsonPath('data.status', 'FOLLOW UP')
            ->assertJsonPath('data.account_status', 'NEW');

        $this->assertDatabaseHas('assign_bases', [
            'id' => $lead->id,
            'assigned_to' => $account->id,
            'status' => 'FOLLOW UP',
            'account_status' => 'NEW',
        ]);
        $this->assertDatabaseHas('lead_details', [
            'assign_base_id' => $lead->id,
            'assigned_to' => $account->id,
            'account_status' => 'NEW',
        ]);
    }

    private function createAssignmentScenario(): array
    {
        $telecaller = User::create([
            'name' => 'Assignment Telecaller',
            'email' => 'assignment-telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $account = User::create([
            'name' => 'Assignment Account',
            'email' => 'assignment-account@example.com',
            'password' => 'password',
            'role' => 'account',
        ]);
        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => 'Assignment Customer',
            'company_name' => 'Assignment Company',
            'status' => 'FOLLOW UP',
            'account_status' => 'IN PROGRESS',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);

        return [$telecaller, $account, $lead];
    }
}
