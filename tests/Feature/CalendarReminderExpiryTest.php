<?php

namespace Tests\Feature;

use App\Models\AssignBase;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarReminderExpiryTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_web_calendar_hides_reminders_after_their_scheduled_time_without_deleting_them(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-12 20:31:00', 'Asia/Kolkata'));
        [$admin, $telecaller, $lead, $expired, $upcoming] = $this->createCalendarFixtures();

        $this->actingAs($admin)
            ->getJson(route('calendar.events', [
                'year' => 2026,
                'month' => 11,
                'employee_id' => $telecaller->id,
                'type' => 'reminder',
            ]))
            ->assertOk()
            ->assertJsonPath('counts.reminders', 1)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.raw_id', $upcoming->id);

        $this->assertDatabaseHas('notifications', ['id' => $expired->id]);
        $this->assertDatabaseHas('notifications', ['id' => $upcoming->id]);
    }

    public function test_mobile_calendar_hides_reminders_after_their_scheduled_time(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-11-12 20:31:00', 'Asia/Kolkata'));
        [, $telecaller, , $expired, $upcoming] = $this->createCalendarFixtures();

        $this->postJson('/api/calendar-events', [
            'emp_id' => $telecaller->id,
            'year' => 2026,
            'month' => 11,
            'type' => 'reminder',
        ])->assertOk()
            ->assertJsonPath('total_events', 1)
            ->assertJsonCount(1, 'events')
            ->assertJsonPath('events.0.raw_id', $upcoming->id);

        $this->assertDatabaseHas('notifications', ['id' => $expired->id]);
    }

    private function createCalendarFixtures(): array
    {
        $admin = User::create([
            'name' => 'Calendar Admin',
            'email' => 'calendar-admin@example.com',
            'password' => 'password',
            'role' => 'admin',
        ]);
        $telecaller = User::create([
            'name' => 'Calendar Telecaller',
            'email' => 'calendar-telecaller@example.com',
            'mobile' => '9876543210',
            'password' => 'password',
            'role' => 'telecaller',
        ]);
        $lead = AssignBase::create([
            'emp_id' => $telecaller->id,
            'customer_name' => 'Calendar Customer',
            'company_name' => 'Example Company',
            'status' => 'FOLLOW UP',
            'mobile' => '9876500000',
            'assign_date' => now()->toDateString(),
        ]);

        $expired = $this->createReminder($telecaller, $lead, '20:30:00', 'Expired reminder');
        $this->createReminder($telecaller, $lead, '20:31:00', 'Reminder at current time');
        $upcoming = $this->createReminder($telecaller, $lead, '20:32:00', 'Upcoming reminder');

        return [$admin, $telecaller, $lead, $expired, $upcoming];
    }

    private function createReminder(User $telecaller, AssignBase $lead, string $time, string $remarks): Notification
    {
        return Notification::create([
            'emp_id' => $telecaller->id,
            'user_id' => $telecaller->id,
            'assign_base_id' => $lead->id,
            'notification_date' => '2026-11-12',
            'notification_time' => $time,
            'remarks' => $remarks,
            'is_sent' => false,
        ]);
    }
}
