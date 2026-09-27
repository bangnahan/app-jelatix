<?php

namespace Tests\Feature;

use App\Models\Organizer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FilamentPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_visiting_admin(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_superadmin_can_access_admin_panel(): void
    {
        $superadmin = User::factory()->create([
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($superadmin)->get('/admin');

        $response->assertOk();
    }

    public function test_organizer_user_can_access_organizer_panel(): void
    {
        $organizer = Organizer::create([
            'name' => 'Test EO',
            'slug' => 'test-eo',
            'email' => 'test@eo.com',
            'phone' => '08123456789',
            'is_verified' => true,
            'is_active' => true,
        ]);
        $eoUser = User::factory()->create([
            'role' => 'organizer_owner',
            'organizer_id' => $organizer->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eoUser)->get('/organizer');

        $response->assertOk();
    }

    public function test_organizer_user_visiting_admin_panel_is_redirected_to_admin_login_without_403_trap(): void
    {
        $organizer = Organizer::create([
            'name' => 'Test EO 2',
            'slug' => 'test-eo-2',
            'email' => 'test2@eo.com',
            'phone' => '08123456780',
            'is_verified' => true,
            'is_active' => true,
        ]);
        $eoUser = User::factory()->create([
            'role' => 'organizer_owner',
            'organizer_id' => $organizer->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($eoUser)->get('/admin');

        // Harus diarahkan ke login admin, bukan error 403
        $response->assertRedirect('/admin/login');
        $this->assertGuest();
    }
}
