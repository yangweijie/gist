<?php

use App\Models\User;
use App\Models\Gist;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Favorite;

test('user can be created with required fields', function () {
    $user = User::factory()->create([
        'name' => 'Test User',
        'email' => 'test@example.com',
    ]);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->name)->toBe('Test User')
        ->and($user->email)->toBe('test@example.com')
        ->and($user->is_active)->toBeTrue();
});

test('user can have github information', function () {
    $user = User::factory()->create([
        'github_id' => '12345',
        'github_username' => 'testuser',
        'github_token' => 'github_token_123',
        'avatar_url' => 'https://github.com/avatar.jpg',
    ]);

    expect($user->github_id)->toBe('12345')
        ->and($user->github_username)->toBe('testuser')
        ->and($user->avatar_url)->toBe('https://github.com/avatar.jpg');
});

test('user password is hidden in serialization', function () {
    $user = User::factory()->create(['password' => 'secret123']);
    
    $array = $user->toArray();
    
    expect($array)->not->toHaveKey('password')
        ->and($array)->not->toHaveKey('github_token')
        ->and($array)->not->toHaveKey('remember_token');
});

test('user has many gists relationship', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create(['user_id' => $user->id]);

    expect($user->gists)->toHaveCount(1)
        ->and($user->gists->first())->toBeInstanceOf(Gist::class)
        ->and($user->gists->first()->id)->toBe($gist->id);
});

test('user has many likes relationship', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    $like = Like::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($user->likes)->toHaveCount(1)
        ->and($user->likes->first())->toBeInstanceOf(Like::class);
});

test('user has many comments relationship', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    $comment = Comment::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($user->comments)->toHaveCount(1)
        ->and($user->comments->first())->toBeInstanceOf(Comment::class);
});

test('user has many favorites relationship', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($user->favorites)->toHaveCount(1)
        ->and($user->favorites->first())->toBeInstanceOf(Favorite::class);
});

test('active scope filters active users', function () {
    User::factory()->create(['is_active' => true]);
    User::factory()->create(['is_active' => false]);

    $activeUsers = User::active()->get();

    expect($activeUsers)->toHaveCount(1)
        ->and($activeUsers->first()->is_active)->toBeTrue();
});

test('with github scope filters users with github id', function () {
    User::factory()->create(['github_id' => '12345']);
    User::factory()->create(['github_id' => null]);

    $githubUsers = User::withGithub()->get();

    expect($githubUsers)->toHaveCount(1)
        ->and($githubUsers->first()->github_id)->toBe('12345');
});

test('user email is cast to datetime', function () {
    $user = User::factory()->create(['email_verified_at' => now()]);

    expect($user->email_verified_at)->toBeInstanceOf(\Carbon\Carbon::class);
});

test('user is_active is cast to boolean', function () {
    $user = User::factory()->create(['is_active' => 1]);

    expect($user->is_active)->toBeBool()
        ->and($user->is_active)->toBeTrue();
});
