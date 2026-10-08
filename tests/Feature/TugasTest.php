<?php

namespace Tests\Feature;

use App\Models\Tugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TugasTest extends TestCase
{
    use RefreshDatabase;

    public function test_unauthenticated_request_to_tugas_returns_401(): void
    {
        $response = $this->getJson('/api/tugas');
        $response->assertStatus(401);

        $responsePost = $this->postJson('/api/tugas', ['title' => 'Sample']);
        $responsePost->assertStatus(401);
    }

    public function test_authenticated_user_can_list_their_tugas_returns_200(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        Tugas::factory()->count(3)->create(['user_id' => $user->id]);
        Tugas::factory()->count(2)->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tugas');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    public function test_user_can_create_tugas_returns_201(): void
    {
        $user = User::factory()->create();

        $payload = [
            'title' => 'Belajar Docker Multi-Stage',
            'description' => 'Mengerjakan soal nomor 5 UTS',
            'status' => 'in_progress',
            'due_date' => '2026-10-15',
        ];

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tugas', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'message' => 'Tugas created successfully',
                'data' => [
                    'title' => 'Belajar Docker Multi-Stage',
                    'description' => 'Mengerjakan soal nomor 5 UTS',
                    'status' => 'in_progress',
                    'due_date' => '2026-10-15',
                    'user_id' => $user->id,
                ],
            ]);

        $this->assertDatabaseHas('tugas', [
            'user_id' => $user->id,
            'title' => 'Belajar Docker Multi-Stage',
        ]);
    }

    public function test_create_tugas_fails_with_422_when_validation_fails(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->postJson('/api/tugas', [
            'title' => '',
            'status' => 'invalid-status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    public function test_user_can_show_their_own_tugas_returns_200(): void
    {
        $user = User::factory()->create();
        $tugas = Tugas::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tugas/'.$tugas->id);

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $tugas->id,
                    'title' => $tugas->title,
                ],
            ]);
    }

    public function test_user_cannot_show_other_users_tugas_returns_404(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTugas = Tugas::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->getJson('/api/tugas/'.$otherTugas->id);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Tugas not found']);
    }

    public function test_user_can_update_tugas_returns_200(): void
    {
        $user = User::factory()->create();
        $tugas = Tugas::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old Title',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/tugas/'.$tugas->id, [
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Tugas updated successfully',
                'data' => [
                    'id' => $tugas->id,
                    'title' => 'Updated Title',
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('tugas', [
            'id' => $tugas->id,
            'title' => 'Updated Title',
            'status' => 'completed',
        ]);
    }

    public function test_update_tugas_fails_with_422_on_invalid_data(): void
    {
        $user = User::factory()->create();
        $tugas = Tugas::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/tugas/'.$tugas->id, [
            'status' => 'unknown-status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_user_cannot_update_other_users_tugas_returns_404(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherTugas = Tugas::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($user, 'sanctum')->putJson('/api/tugas/'.$otherTugas->id, [
            'title' => 'Malicious Update',
        ]);

        $response->assertStatus(404)
            ->assertJson(['message' => 'Tugas not found']);
    }

    public function test_user_can_delete_tugas_returns_200(): void
    {
        $user = User::factory()->create();
        $tugas = Tugas::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/tugas/'.$tugas->id);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Tugas deleted successfully']);

        $this->assertDatabaseMissing('tugas', ['id' => $tugas->id]);
    }

    public function test_delete_non_existent_or_unauthorized_tugas_returns_404(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'sanctum')->deleteJson('/api/tugas/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Tugas not found']);
    }
}
