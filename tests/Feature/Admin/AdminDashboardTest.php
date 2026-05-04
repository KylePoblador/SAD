<?php

use App\Models\User;

test('admin dashboard renders for admin user', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'college' => null,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.dashboard'));

    $response->assertOk();
    $response->assertSee('CoinMeal Administrator Console');
});

test('admin can delete a student account', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'college' => null,
    ]);
    $student = User::factory()->create([
        'role' => 'student',
    ]);

    $response = $this->actingAs($admin)->delete(route('admin.users.destroy', $student));

    $response->assertRedirect();
    $this->assertDatabaseMissing('users', ['id' => $student->id]);
});

test('admin can edit a staff account', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'college' => null,
    ]);
    $staff = User::factory()->create([
        'role' => 'staff',
        'college' => 'cass',
        'canteen_name' => 'Old Name',
    ]);

    $response = $this->actingAs($admin)->patch(route('admin.users.update', $staff), [
        '_form' => 'admin-user-edit',
        'name' => 'Updated Staff',
        'email' => 'updated.staff@example.com',
        'role' => 'staff',
        'college' => 'cass',
        'canteen_name' => 'Updated Canteen',
    ]);

    $response->assertRedirect(route('admin.dashboard'));
    $this->assertDatabaseHas('users', [
        'id' => $staff->id,
        'name' => 'Updated Staff',
        'email' => 'updated.staff@example.com',
        'role' => 'staff',
        'canteen_name' => 'Updated Canteen',
    ]);
});
