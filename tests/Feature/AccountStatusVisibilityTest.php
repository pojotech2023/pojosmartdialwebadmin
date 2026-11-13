<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\LeadDetail;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountStatusVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_follow_up_page_lists_notifications_except_nope_and_scopes_account_users(): void
    {
        $telecaller = User::create([
            'name' => 'Follow Up Telecaller',
            'email' => 'follow-up-telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $account = User::create([
            'name' => 'Suresh Kumar',
            'email' => 'follow-up-account@example.com',
            'password' => 'password',
            'role' => 'account',
        ]);
        $otherAccount = User::create([
            'name' => 'Other Account',
            'email' => 'other-account@example.com',
            'password' => 'password',
            'role' => 'account',
        ]);
        $admin = User::create([
            'name' => 'Follow Up Admin',
            'email' => 'follow-up-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $assignedLead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'assigned_to' => $account->id,
            'customer_name' => 'Follow Up Customer',
            'company_name' => 'Follow Up Company',
            'status' => 'CONFIRMED',
            'account_status' => 'NEW',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);
        $otherLead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'assigned_to' => $otherAccount->id,
            'customer_name' => 'Other Customer',
            'company_name' => 'Other Company',
            'status' => 'CONFIRMED',
            'account_status' => 'NEW',
            'mobile' => '9876543211',
            'assign_date' => now()->toDateString(),
        ]);

        foreach ([
            [$assignedLead, null, 'No mobile status'],
            [$assignedLead, 'Quotation Shared', 'Needs follow up'],
            [$assignedLead, 'NoPe', 'Should be excluded'],
            [$otherLead, 'Appointment-Online', 'Other account follow up'],
        ] as [$lead, $status, $remarks]) {
            Notification::create([
                'emp_id' => $telecaller->id,
                'user_id' => $telecaller->id,
                'assign_base_id' => $lead->id,
                'notification_date' => now()->addDay()->toDateString(),
                'notification_time' => '12:00:00',
                'remarks' => $remarks,
                'status' => $status,
            ]);
        }

        Notification::create([
            'emp_id' => $account->id,
            'user_id' => $account->id,
            'assign_base_id' => $assignedLead->id,
            'notification_date' => now()->toDateString(),
            'notification_time' => '13:00:00',
            'remarks' => 'Status changed to IN PROGRESS by Suresh Kumar',
            'status' => 'IN PROGRESS',
        ]);

        $this->actingAs($account)
            ->getJson(route('follow-ups.data'))
            ->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'customer_name' => 'Follow Up Customer',
                'telecaller_name' => 'Follow Up Telecaller',
            ])
            ->assertJsonFragment(['notification_status' => 'Quotation Shared'])
            ->assertJsonPath('meta.total', 2);

        $this->actingAs($admin)
            ->getJson(route('follow-ups.data', ['search' => 'Other Customer', 'per_page' => 10]))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.customer_name', 'Other Customer')
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 1);

        $this->actingAs($account)
            ->get(route('follow-ups.index'))
            ->assertOk()
            ->assertSee('All Follow Up List')
            ->assertSee('Follow Up Customer')
            ->assertSee('Not selected')
            ->assertSee('Quotation Shared')
            ->assertDontSee('Status changed to IN PROGRESS by Suresh Kumar')
            ->assertDontSee('Should be excluded')
            ->assertDontSee('Other Customer');

        $this->actingAs($admin)
            ->get(route('follow-ups.index'))
            ->assertOk()
            ->assertSee('All Follow Up List')
            ->assertSee('Follow Up Customer')
            ->assertSee('Other Customer')
            ->assertDontSee('Status changed to IN PROGRESS by Suresh Kumar')
            ->assertDontSee('Should be excluded');
    }

    public function test_notification_list_includes_mobile_notification_status(): void
    {
        $telecaller = User::create([
            'name' => 'Notification Telecaller',
            'email' => 'notification-telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $admin = User::create([
            'name' => 'Notification Admin',
            'email' => 'notification-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => 'Notification Lead',
            'company_name' => 'Test Company',
            'status' => 'FOLLOW UP',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);
        Notification::create([
            'emp_id' => $telecaller->id,
            'user_id' => $telecaller->id,
            'assign_base_id' => $lead->id,
            'notification_date' => now()->addDay()->toDateString(),
            'notification_time' => '12:00:00',
            'remarks' => 'Call customer',
            'status' => 'Quotation Shared',
        ]);

        $this->actingAs($admin)
            ->getJson(route('dashboard.notifications.data', ['assign_base_id' => $lead->id]))
            ->assertOk()
            ->assertJsonPath('upcoming.0.notification_status', 'Quotation Shared');

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Notification Status:', false);
    }

    public function test_unassigned_lead_details_show_telecaller_status(): void
    {
        $telecaller = User::create([
            'name' => 'Telecaller',
            'email' => 'unassigned-telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'unassigned-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'assigned_to' => null,
            'customer_name' => 'Unassigned Lead',
            'company_name' => 'Test Company',
            'status' => 'FOLLOW UP',
            'account_status' => 'NEW',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);

        $this->actingAs($admin)
            ->getJson(route('dashboard.lead-details.data', $lead->id))
            ->assertOk()
            ->assertJsonPath('data.status', 'FOLLOW UP')
            ->assertJsonPath('data.assigned_to.id', null);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('const hasAssignedAccount = Boolean(d.assigned_to?.id);', false)
            ->assertSee("const displayStatusLabel = hasAssignedAccount ? 'Account Status' : 'Telecaller Status';", false)
            ->assertSee("hasAssignedAccount ? (p.account_status || 'NEW') : (d.status || 'NEW')", false);
    }

    public function test_account_status_updates_are_visible_on_the_admin_accounts_page(): void
    {
        $telecaller = User::create([
            'name' => 'Telecaller',
            'email' => 'telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $account = User::create([
            'name' => 'Account User',
            'email' => 'account@example.com',
            'password' => 'password',
            'role' => 'account',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'assigned_to' => $account->id,
            'customer_name' => 'Test Customer',
            'company_name' => 'Test Company',
            'status' => 'CONFIRMED',
            'account_status' => 'NEW',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);

        $this->actingAs($account)
            ->postJson(route('dashboard.update-lead-status'), [
                'assign_base_id' => $lead->id,
                'account_status' => 'QUOTATION SHARED',
                'account_remarks' => 'Quotation sent',
            ])
            ->assertOk()
            ->assertJsonPath('data.account_status', 'QUOTATION SHARED');

        $this->assertDatabaseHas('assign_bases', [
            'id' => $lead->id,
            'account_status' => 'QUOTATION SHARED',
        ]);
        $this->assertDatabaseHas('lead_details', [
            'assign_base_id' => $lead->id,
            'account_status' => 'QUOTATION SHARED',
        ]);

        $this->actingAs($account)
            ->postJson(route('dashboard.update-lead-status'), [
                'assign_base_id' => $lead->id,
                'account_status' => 'IN PROGRESS',
            ])
            ->assertOk()
            ->assertJsonPath('data.account_status', 'IN PROGRESS')
            ->assertJsonPath('data.account_remarks', 'Quotation sent');

        $this->actingAs($admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSeeInOrder(['#' . $lead->id, 'IN PROGRESS']);

        $this->actingAs($account)
            ->postJson(route('dashboard.update-lead-status'), [
                'assign_base_id' => $lead->id,
                'account_status' => 'CONFIRMED',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('account_status');

        $this->actingAs($account)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('<option value="CONFIRMED"', false);

        $this->actingAs($account)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertDontSee('<option value="CONFIRMED"', false);

        $this->actingAs($admin)
            ->postJson(route('dashboard.update-lead-status'), [
                'assign_base_id' => $lead->id,
                'account_status' => 'NOPE',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('assign_bases', [
            'id' => $lead->id,
            'status' => 'CONFIRMED',
            'account_status' => 'IN PROGRESS',
        ]);

        $this->actingAs($account)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Accounts Team')
            ->assertSee('Account Status')
            ->assertSee("hasAssignedAccount ? 'Account Status' : 'Telecaller Status'", false)
            ->assertSee('name="account_status"', false)
            ->assertDontSee('Account Status & Note')
            ->assertSeeInOrder(['#' . $lead->id, 'IN PROGRESS']);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Accounts Team')
            ->assertSee('Telecaller Status')
            ->assertDontSee('name="account_status"', false)
            ->assertDontSee('<th>Account Status</th>')
            ->assertSeeInOrder(['#' . $lead->id, 'CONFIRMED']);

        $this->actingAs($account)
            ->get(route('dashboard', ['account_status' => 'IN PROGRESS']))
            ->assertOk()
            ->assertSeeInOrder(['#' . $lead->id, 'IN PROGRESS']);

        $this->actingAs($admin)
            ->get(route('dashboard', ['status' => 'CONFIRMED']))
            ->assertOk()
            ->assertSeeInOrder(['#' . $lead->id, 'CONFIRMED']);

        $this->actingAs($admin)
            ->get(route('dashboard', ['account_status' => 'NEW']))
            ->assertOk()
            ->assertSeeInOrder(['#' . $lead->id, 'CONFIRMED']);
    }

    public function test_admin_accounts_page_uses_the_current_assign_base_status_when_details_are_stale(): void
    {
        $telecaller = User::create([
            'name' => 'Telecaller',
            'email' => 'telecaller@example.com',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $account = User::create([
            'name' => 'Account User',
            'email' => 'account@example.com',
            'password' => 'password',
            'role' => 'account',
        ]);
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);

        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'assigned_to' => $account->id,
            'customer_name' => 'Test Customer',
            'company_name' => 'Test Company',
            'status' => 'CONFIRMED',
            'account_status' => 'IN PROGRESS',
            'mobile' => '9876543210',
            'assign_date' => now()->toDateString(),
        ]);
        LeadDetail::create([
            'assign_base_id' => $lead->id,
            'assigned_to' => $account->id,
            'account_status' => 'NEW',
        ]);

        $this->actingAs($admin)
            ->get(route('accounts.index'))
            ->assertOk()
            ->assertSeeInOrder(['#' . $lead->id, 'IN PROGRESS']);
    }
}
