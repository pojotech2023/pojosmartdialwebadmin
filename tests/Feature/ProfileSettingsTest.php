<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_user_can_open_profile_settings_from_top_right_menu(): void
    {
        $user = $this->createAccount();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Profile Settings')
            ->assertSee(route('profile.edit'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Full name')
            ->assertSee('Change password');
    }

    public function test_user_can_update_own_profile_without_changing_role(): void
    {
        $user = $this->createAccount();

        $this->actingAs($user)
            ->put(route('profile.update'), [
                'name' => 'Updated Account Name',
                'mobile' => '9876543210',
                'email' => 'updated-account@example.com',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Account Name',
            'mobile' => '9876543210',
            'email' => 'updated-account@example.com',
            'role' => 'account',
        ]);
    }

    public function test_user_must_provide_current_password_to_change_password(): void
    {
        $user = $this->createAccount();

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->put(route('profile.update'), [
                'name' => $user->name,
                'mobile' => $user->mobile,
                'email' => $user->email,
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    private function createAccount(): User
    {
        return User::create([
            'name' => 'Profile Account',
            'email' => 'profile-account@example.com',
            'mobile' => '9876500000',
            'password' => 'old-password',
            'role' => 'account',
        ]);
    }
}
