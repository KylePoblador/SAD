<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('reset password link screen can be rendered', function () {
    $response = $this->get('/forgot-password');

    $response->assertStatus(200);
});

test('reset password link can be requested', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('password.reset', ['email' => $user->email]));
});

test('reset password screen can be rendered', function () {
    $response = $this->get('/reset-password');
    $response->assertStatus(200);
});

test('password can be reset after email verification', function () {
    $user = User::factory()->create();

    $this->post('/forgot-password', ['email' => $user->email])
        ->assertRedirect(route('password.reset', ['email' => $user->email]));

    $this->get('/reset-password?email='.$user->email)->assertStatus(200);

    $response = $this->post('/reset-password', [
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect(route('login'));

    expect(Hash::check('password123', $user->fresh()->password))->toBeTrue();
});

test('password reset fails when email is not verified in flow', function () {
    $user = User::factory()->create();

    $this->post('/reset-password', [
        'email' => $user->email,
        'password' => 'password123',
        'password_confirmation' => 'password123',
    ])->assertSessionHasErrors('email');
});
