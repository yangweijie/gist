<?php

use App\Models\Favorite;
use App\Models\User;
use App\Models\Gist;

test('favorite can be created with required fields', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    
    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($favorite)->toBeInstanceOf(Favorite::class)
        ->and($favorite->user_id)->toBe($user->id)
        ->and($favorite->gist_id)->toBe($gist->id);
});

test('favorite belongs to user', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($favorite->user)->toBeInstanceOf(User::class)
        ->and($favorite->user->id)->toBe($user->id);
});

test('favorite belongs to gist', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    $favorite = Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    expect($favorite->gist)->toBeInstanceOf(Gist::class)
        ->and($favorite->gist->id)->toBe($gist->id);
});

test('favorite toggle creates favorite when not exists', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();

    $result = Favorite::toggle($user->id, $gist->id);

    expect($result)->toBeTrue()
        ->and(Favorite::where('user_id', $user->id)->where('gist_id', $gist->id)->exists())->toBeTrue();
});

test('favorite toggle removes favorite when exists', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    
    // 先创建收藏
    Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    $result = Favorite::toggle($user->id, $gist->id);

    expect($result)->toBeFalse()
        ->and(Favorite::where('user_id', $user->id)->where('gist_id', $gist->id)->exists())->toBeFalse();
});

test('is favorited returns true when favorite exists', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();
    
    Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist->id,
    ]);

    $result = Favorite::isFavorited($user->id, $gist->id);

    expect($result)->toBeTrue();
});

test('is favorited returns false when favorite does not exist', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create();

    $result = Favorite::isFavorited($user->id, $gist->id);

    expect($result)->toBeFalse();
});

test('multiple users can favorite same gist', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $gist = Gist::factory()->create();

    Favorite::factory()->create([
        'user_id' => $user1->id,
        'gist_id' => $gist->id,
    ]);
    
    Favorite::factory()->create([
        'user_id' => $user2->id,
        'gist_id' => $gist->id,
    ]);

    expect(Favorite::where('gist_id', $gist->id)->count())->toBe(2)
        ->and(Favorite::isFavorited($user1->id, $gist->id))->toBeTrue()
        ->and(Favorite::isFavorited($user2->id, $gist->id))->toBeTrue();
});

test('user can favorite multiple gists', function () {
    $user = User::factory()->create();
    $gist1 = Gist::factory()->create();
    $gist2 = Gist::factory()->create();

    Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist1->id,
    ]);
    
    Favorite::factory()->create([
        'user_id' => $user->id,
        'gist_id' => $gist2->id,
    ]);

    expect(Favorite::where('user_id', $user->id)->count())->toBe(2)
        ->and(Favorite::isFavorited($user->id, $gist1->id))->toBeTrue()
        ->and(Favorite::isFavorited($user->id, $gist2->id))->toBeTrue();
});
