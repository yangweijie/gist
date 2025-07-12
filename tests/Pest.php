<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

// Feature tests use Laravel TestCase with database refresh
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

// Unit tests also use Laravel TestCase for database access
pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

// Browser tests use DuskTestCase
pest()->extend(Tests\DuskTestCase::class)
    ->in('Browser');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

// Custom expectations for our GitHub Gist application
expect()->extend('toBeValidGist', function () {
    return $this->toHaveKeys(['id', 'description', 'public', 'files'])
        ->and($this->value['files'])->toBeArray()
        ->and($this->value['id'])->toBeString()
        ->and($this->value['public'])->toBeBool();
});

expect()->extend('toBeValidUser', function () {
    return $this->toHaveKeys(['id', 'name', 'email', 'github_id'])
        ->and($this->value['id'])->toBeInt()
        ->and($this->value['github_id'])->toBeString();
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function createTestUser(array $attributes = []): \App\Models\User
{
    return \App\Models\User::factory()->create($attributes);
}

function createTestGist(\App\Models\User $user = null, array $attributes = []): \App\Models\Gist
{
    if (!$user) {
        $user = createTestUser();
    }

    return \App\Models\Gist::factory()->create(array_merge([
        'user_id' => $user->id,
        'title' => 'Test Gist',
        'description' => 'Test Gist Description',
        'content' => '<?php echo "Hello World!"; ?>',
        'language' => 'php',
        'filename' => 'test.php',
        'is_public' => true,
    ], $attributes));
}
