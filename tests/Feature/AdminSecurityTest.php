<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_predictable_admin_paths_return_404(): void
    {
        $this->get('/admin')->assertStatus(404);
        $this->get('/admin/artikel')->assertStatus(404);
        $this->get('/admin/program')->assertStatus(404);
        $this->get('/admin/keuangan')->assertStatus(404);
    }

    public function test_unauthenticated_user_cannot_access_obfuscated_admin_routes(): void
    {
        $response = $this->get(route('admin.artikel.index'));
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_access_obfuscated_admin_routes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.artikel.index'));
        $response->assertStatus(200);

        $financeResponse = $this->actingAs($user)->get(route('admin.keuangan.index'));
        $financeResponse->assertStatus(200);
    }
}
