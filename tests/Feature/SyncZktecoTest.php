<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SyncZktecoTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    // public function test_example(): void
    // {
    //     $response = $this->get('/');

    //     $response->assertStatus(200);
    // }


public function test_sync_zkteco_works()
{
    // Ensure the user exists with a zk_user_id
    $user = User::findOrFail(231);



    $response = $this->postJson('/api/v1/syncZkteco', [
        'records' => [
            ['user_id' => $user->zk_user_id, 'name' => 'Raven Dudley', 'time' => '2025-05-22 08:00:00'],
            ['user_id' => $user->zk_user_id, 'name' => 'Raven Dudley', 'time' => '2025-05-22 17:00:00'],
        ]
    ]);

    $response->assertStatus(200)->assertJson([
        'message' => 'ZKTeco records synced'
    ]);

    $this->assertDatabaseHas('attendances', [
        'user_id' => $user->id,
        'attendance_date' => '2025-05-22',
        'clock_in_time' => '08:00:00',
        'clock_out_time' => '17:00:00',
    ]);
}

}
