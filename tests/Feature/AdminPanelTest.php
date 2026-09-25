<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_can_be_rendered(): void
    {
        $response = $this->get('/admin/login');

        $response->assertSuccessful();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertSuccessful();
    }

    public function test_admin_can_access_resources(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)->get('/admin/service-requests')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/service-types')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/permits')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/payments')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/tariffs')->assertSuccessful();
        $this->actingAs($admin)->get('/admin/users')->assertSuccessful();
    }
}
