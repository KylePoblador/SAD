<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get('/login');

    $response->assertStatus(200);
});

test('users can authenticate using the login screen', function () {
    $user = User::factory()->create();

    $response = $this->post('/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();

    $expected = $user->role === 'staff'
        ? route('dashboard', absolute: false)
        : route('student.dashboard', absolute: false);

    $response->assertRedirect($expected);
});

test('admin users are redirected to admin dashboard after login', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
        'college' => null,
    ]);

    $response = $this->post('/login', [
        'email' => $admin->email,
        'password' => 'password',
        'role' => 'admin',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('admin.dashboard', absolute: false));
});

test('users can not authenticate with invalid password', function () {
    $user = User::factory()->create();

    $this->post('/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ]);

    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/logout');

    $this->assertGuest();
    $response->assertRedirect('/');
});
