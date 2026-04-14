<?php

use App\Models\User;

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test.student@usm.edu.ph',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'student',
        'college' => 'ceit',
        'terms_accepted' => '1',
        'phone' => '09123456789',
        'student_id' => '2024-REG-0001',
    ]);

    $response->assertRedirect('/');
    $response->assertSessionHas('status');
    $this->assertGuest();

    $this->assertDatabaseHas('users', [
        'email' => 'test.student@usm.edu.ph',
        'role' => 'student',
    ]);
});

test('second staff cannot register for the same college', function () {
    User::factory()->staff()->create([
        'college' => 'ceit',
        'email' => 'first.staff@example.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'Second Staff',
        'email' => 'second.staff@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'staff',
        'college' => 'ceit',
        'terms_accepted' => '1',
        'canteen_name' => 'Another Canteen',
    ]);

    $response->assertSessionHasErrors('college');
    $this->assertDatabaseMissing('users', ['email' => 'second.staff@example.com']);
});

test('staff can register when another college already has staff', function () {
    User::factory()->staff()->create([
        'college' => 'ceit',
        'email' => 'ceit.staff@example.com',
    ]);

    $response = $this->post('/register', [
        'name' => 'CASS Staff',
        'email' => 'cass.staff@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'role' => 'staff',
        'college' => 'cass',
        'terms_accepted' => '1',
        'canteen_name' => 'CASS Food Hub',
    ]);

    $response->assertRedirect('/');
    $this->assertDatabaseHas('users', [
        'email' => 'cass.staff@example.com',
        'role' => 'staff',
        'college' => 'cass',
    ]);
});
