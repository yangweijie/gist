<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('homepage contains expected content', function () {
    $response = $this->get('/');

    $response->assertStatus(200)
        ->assertSee('Gist Manager')
        ->assertSee(__('common.messages.app_description'))
        ->assertViewIs('welcome');
});

test('unauthenticated user can access public pages', function () {
    $this->get('/')->assertStatus(200);
    $this->get('/login')->assertStatus(200);
});

test('authenticated user can access dashboard', function () {
    $user = createTestUser();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertStatus(200);
});
