<?php

use App\Services\GitHubService;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('github.api_url', 'https://api.github.com');
    Config::set('github.cache.prefix', 'github_');
    Config::set('github.cache.ttl', 3600);
    
    Cache::flush();
});

test('github service can be instantiated', function () {
    $service = new GitHubService();
    
    expect($service)->toBeInstanceOf(GitHubService::class);
});

test('get user gists throws exception when user has no github token', function () {
    $user = User::factory()->create(['github_token' => null]);
    $service = new GitHubService();

    expect(fn() => $service->getUserGists($user))
        ->toThrow(\Exception::class, 'User does not have GitHub token');
});

test('get user gists returns cached data when available', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    
    $mockData = [
        ['id' => 'gist1', 'description' => 'Test Gist 1'],
        ['id' => 'gist2', 'description' => 'Test Gist 2'],
    ];
    
    // 设置缓存
    Cache::put("github_user_gists_{$user->id}_1_30", $mockData, 3600);
    
    $result = $service->getUserGists($user);
    
    expect($result)->toBe($mockData);
});

test('get user gists makes api call when cache is empty', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    
    $mockResponse = [
        ['id' => 'gist1', 'description' => 'Test Gist 1'],
        ['id' => 'gist2', 'description' => 'Test Gist 2'],
    ];
    
    Http::fake([
        'api.github.com/gists*' => Http::response($mockResponse, 200),
    ]);
    
    $result = $service->getUserGists($user);
    
    expect($result)->toBe($mockResponse);
    
    Http::assertSent(function ($request) use ($user) {
        return $request->url() === 'https://api.github.com/gists?page=1&per_page=30' &&
               $request->hasHeader('Authorization', 'Bearer ' . $user->github_token);
    });
});

test('get user gists handles api error gracefully', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    
    Http::fake([
        'api.github.com/gists*' => Http::response('Unauthorized', 401),
    ]);
    
    expect(fn() => $service->getUserGists($user))
        ->toThrow(\Exception::class, 'Failed to fetch gists from GitHub: 401');
});

test('get gist throws exception when user has no github token', function () {
    $user = User::factory()->create(['github_token' => null]);
    $service = new GitHubService();

    expect(fn() => $service->getGist($user, 'gist123'))
        ->toThrow(\Exception::class, 'User does not have GitHub token');
});

test('get gist returns cached data when available', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    $gistId = 'gist123';
    
    $mockData = ['id' => $gistId, 'description' => 'Test Gist'];
    
    // 设置缓存
    Cache::put("github_gist_{$gistId}", $mockData, 3600);
    
    $result = $service->getGist($user, $gistId);
    
    expect($result)->toBe($mockData);
});

test('get gist makes api call when cache is empty', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    $gistId = 'gist123';
    
    $mockResponse = ['id' => $gistId, 'description' => 'Test Gist'];
    
    Http::fake([
        "api.github.com/gists/{$gistId}" => Http::response($mockResponse, 200),
    ]);
    
    $result = $service->getGist($user, $gistId);
    
    expect($result)->toBe($mockResponse);
    
    Http::assertSent(function ($request) use ($user, $gistId) {
        return $request->url() === "https://api.github.com/gists/{$gistId}" &&
               $request->hasHeader('Authorization', 'Bearer ' . $user->github_token);
    });
});

test('get gist throws specific exception for 404 error', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    $gistId = 'nonexistent';
    
    Http::fake([
        "api.github.com/gists/{$gistId}" => Http::response('Not Found', 404),
    ]);
    
    expect(fn() => $service->getGist($user, $gistId))
        ->toThrow(\Exception::class, 'Gist not found');
});

test('create gist throws exception when user has no github token', function () {
    $user = User::factory()->create(['github_token' => null]);
    $service = new GitHubService();

    expect(fn() => $service->createGist($user, []))
        ->toThrow(\Exception::class, 'User does not have GitHub token');
});

test('create gist makes api call and returns response', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    
    $gistData = [
        'description' => 'Test Gist',
        'public' => true,
        'files' => [
            'test.php' => ['content' => '<?php echo "Hello";']
        ]
    ];
    
    $mockResponse = ['id' => 'new_gist_123', 'description' => 'Test Gist'];
    
    Http::fake([
        'api.github.com/gists' => Http::response($mockResponse, 201),
    ]);
    
    $result = $service->createGist($user, $gistData);
    
    expect($result)->toBe($mockResponse);
    
    Http::assertSent(function ($request) use ($user, $gistData) {
        return $request->url() === 'https://api.github.com/gists' &&
               $request->hasHeader('Authorization', 'Bearer ' . $user->github_token) &&
               $request->method() === 'POST' &&
               $request->data() === $gistData;
    });
});

test('update gist throws exception when user has no github token', function () {
    $user = User::factory()->create(['github_token' => null]);
    $service = new GitHubService();

    expect(fn() => $service->updateGist($user, 'gist123', []))
        ->toThrow(\Exception::class, 'User does not have GitHub token');
});

test('update gist makes api call and returns response', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    $gistId = 'gist123';
    
    $gistData = [
        'description' => 'Updated Gist',
        'files' => [
            'test.php' => ['content' => '<?php echo "Updated";']
        ]
    ];
    
    $mockResponse = ['id' => $gistId, 'description' => 'Updated Gist'];
    
    Http::fake([
        "api.github.com/gists/{$gistId}" => Http::response($mockResponse, 200),
    ]);
    
    $result = $service->updateGist($user, $gistId, $gistData);
    
    expect($result)->toBe($mockResponse);
    
    Http::assertSent(function ($request) use ($user, $gistId, $gistData) {
        return $request->url() === "https://api.github.com/gists/{$gistId}" &&
               $request->hasHeader('Authorization', 'Bearer ' . $user->github_token) &&
               $request->method() === 'PATCH' &&
               $request->data() === $gistData;
    });
});

test('update gist throws specific exception for 404 error', function () {
    $user = User::factory()->create(['github_token' => 'test_token']);
    $service = new GitHubService();
    $gistId = 'nonexistent';
    
    Http::fake([
        "api.github.com/gists/{$gistId}" => Http::response('Not Found', 404),
    ]);
    
    expect(fn() => $service->updateGist($user, $gistId, []))
        ->toThrow(\Exception::class, 'Gist not found');
});
