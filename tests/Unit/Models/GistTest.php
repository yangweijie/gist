<?php

use App\Models\Gist;
use App\Models\User;
use App\Models\Tag;
use App\Models\Like;
use App\Models\Comment;
use App\Models\Favorite;

test('gist can be created with required fields', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test Gist',
        'description' => 'A test gist',
        'content' => '<?php echo "Hello World";',
        'language' => 'php',
        'filename' => 'test.php',
        'is_public' => true,
        'is_synced' => false,
    ]);

    expect($gist)->toBeInstanceOf(Gist::class)
        ->and($gist->title)->toBe('Test Gist')
        ->and($gist->description)->toBe('A test gist')
        ->and($gist->content)->toBe('<?php echo "Hello World";')
        ->and($gist->language)->toBe('php')
        ->and($gist->filename)->toBe('test.php')
        ->and($gist->is_public)->toBeTrue()
        ->and($gist->is_synced)->toBeFalse();
});

test('gist belongs to user', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create(['user_id' => $user->id]);

    expect($gist->user)->toBeInstanceOf(User::class)
        ->and($gist->user->id)->toBe($user->id);
});

test('gist has many tags relationship', function () {
    $gist = Gist::factory()->create();
    $tag = Tag::factory()->create();
    $gist->tags()->attach($tag);

    expect($gist->tags)->toHaveCount(1)
        ->and($gist->tags->first())->toBeInstanceOf(Tag::class)
        ->and($gist->tags->first()->id)->toBe($tag->id);
});

test('gist has many likes relationship', function () {
    $gist = Gist::factory()->create();
    $user = User::factory()->create();
    $like = Like::factory()->create([
        'gist_id' => $gist->id,
        'user_id' => $user->id,
    ]);

    expect($gist->likes)->toHaveCount(1)
        ->and($gist->likes->first())->toBeInstanceOf(Like::class);
});

test('gist has many comments relationship', function () {
    $gist = Gist::factory()->create();
    $user = User::factory()->create();
    $comment = Comment::factory()->create([
        'gist_id' => $gist->id,
        'user_id' => $user->id,
    ]);

    expect($gist->comments)->toHaveCount(1)
        ->and($gist->comments->first())->toBeInstanceOf(Comment::class);
});

test('gist has many favorites relationship', function () {
    $gist = Gist::factory()->create();
    $user = User::factory()->create();
    $favorite = Favorite::factory()->create([
        'gist_id' => $gist->id,
        'user_id' => $user->id,
    ]);

    expect($gist->favorites)->toHaveCount(1)
        ->and($gist->favorites->first())->toBeInstanceOf(Favorite::class);
});

test('public scope filters public gists', function () {
    Gist::factory()->create(['is_public' => true]);
    Gist::factory()->create(['is_public' => false]);

    $publicGists = Gist::public()->get();

    expect($publicGists)->toHaveCount(1)
        ->and($publicGists->first()->is_public)->toBeTrue();
});

test('by language scope filters gists by language', function () {
    Gist::factory()->create(['language' => 'php']);
    Gist::factory()->create(['language' => 'javascript']);

    $phpGists = Gist::byLanguage('php')->get();

    expect($phpGists)->toHaveCount(1)
        ->and($phpGists->first()->language)->toBe('php');
});

test('popular scope orders by likes count', function () {
    $gist1 = Gist::factory()->create(['likes_count' => 5]);
    $gist2 = Gist::factory()->create(['likes_count' => 10]);
    $gist3 = Gist::factory()->create(['likes_count' => 2]);

    $popularGists = Gist::popular()->get();

    expect($popularGists->first()->id)->toBe($gist2->id)
        ->and($popularGists->last()->id)->toBe($gist3->id);
});

test('recent scope orders by created at', function () {
    $gist1 = Gist::factory()->create(['created_at' => now()->subDays(2)]);
    $gist2 = Gist::factory()->create(['created_at' => now()->subDay()]);
    $gist3 = Gist::factory()->create(['created_at' => now()]);

    $recentGists = Gist::recent()->get();

    expect($recentGists->first()->id)->toBe($gist3->id)
        ->and($recentGists->last()->id)->toBe($gist1->id);
});

test('gist casts boolean fields correctly', function () {
    $gist = Gist::factory()->create([
        'is_public' => 1,
        'is_synced' => 0,
    ]);

    expect($gist->is_public)->toBeBool()->toBeTrue()
        ->and($gist->is_synced)->toBeBool()->toBeFalse();
});

test('gist casts integer fields correctly', function () {
    $gist = Gist::factory()->create([
        'views_count' => '10',
        'likes_count' => '5',
        'comments_count' => '3',
        'favorites_count' => '2',
    ]);

    expect($gist->views_count)->toBeInt()->toBe(10)
        ->and($gist->likes_count)->toBeInt()->toBe(5)
        ->and($gist->comments_count)->toBeInt()->toBe(3)
        ->and($gist->favorites_count)->toBeInt()->toBe(2);
});

test('gist casts datetime fields correctly', function () {
    $gist = Gist::factory()->create([
        'github_created_at' => '2023-01-01 12:00:00',
        'github_updated_at' => '2023-01-02 12:00:00',
    ]);

    expect($gist->github_created_at)->toBeInstanceOf(\Carbon\Carbon::class)
        ->and($gist->github_updated_at)->toBeInstanceOf(\Carbon\Carbon::class);
});
