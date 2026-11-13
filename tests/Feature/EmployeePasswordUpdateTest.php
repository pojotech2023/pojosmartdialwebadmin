<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeePasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_change_employee_password_and_employee_can_log_in_with_it(): void
    {
        $admin = User::create([
            'name' => 'Password Test Admin',
            'email' => 'password-admin@example.com',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $employee = User::create([
            'name' => 'Password Test Employee',
            'email' => 'password-employee@example.com',
            'mobile' => '9876543210',
            'password' => 'old-password',
            'role' => 'Telecaller',
        ]);

        $this->actingAs($admin)
            ->put(route('employees.update', $employee), [
                'name' => $employee->name,
                'mobile' => $employee->mobile,
                'email' => $employee->email,
                'role' => $employee->role,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('employees.index'))
            ->assertSessionHas('success', 'Employee updated successfully');

        $updatedEmployee = $employee->fresh();
        $this->assertTrue(Hash::check('new-password-123', $updatedEmployee->password));
        $this->assertFalse(Hash::check('old-password', $updatedEmployee->password));

        $this->postJson('/api/login', [
            'mobile' => $updatedEmployee->mobile,
            'password' => 'new-password-123',
        ])->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.emp_id', $updatedEmployee->id);
    }

    public function test_leaving_password_fields_blank_keeps_the_existing_password(): void
    {
        $admin = User::create([
            'name' => 'Password Test Admin',
            'email' => 'password-admin-blank@example.com',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $employee = User::create([
            'name' => 'Password Test Employee',
            'email' => 'password-employee-blank@example.com',
            'mobile' => '9876543211',
            'password' => 'old-password',
            'role' => 'Account',
        ]);
        $oldHash = $employee->password;

        $this->actingAs($admin)
            ->put(route('employees.update', $employee), [
                'name' => $employee->name,
                'mobile' => $employee->mobile,
                'email' => $employee->email,
                'role' => $employee->role,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertRedirect(route('employees.index'));

        $this->assertSame($oldHash, $employee->fresh()->password);
        $this->assertTrue(Hash::check('old-password', $employee->fresh()->password));
    }

    public function test_mismatched_password_confirmation_is_rejected(): void
    {
        $admin = User::create([
            'name' => 'Password Test Admin',
            'email' => 'password-admin-mismatch@example.com',
            'password' => 'admin-password',
            'role' => 'admin',
        ]);
        $employee = User::create([
            'name' => 'Password Test Employee',
            'email' => 'password-employee-mismatch@example.com',
            'mobile' => '9876543212',
            'password' => 'old-password',
            'role' => 'Account',
        ]);

        $this->actingAs($admin)
            ->from(route('employees.edit', $employee))
            ->put(route('employees.update', $employee), [
                'name' => $employee->name,
                'mobile' => $employee->mobile,
                'email' => $employee->email,
                'role' => $employee->role,
                'password' => 'new-password-123',
                'password_confirmation' => 'different-password',
            ])
            ->assertRedirect(route('employees.edit', $employee))
            ->assertSessionHasErrors('password');

        $this->assertTrue(Hash::check('old-password', $employee->fresh()->password));
    }
}
