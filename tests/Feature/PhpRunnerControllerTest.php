<?php

use App\Models\User;
use App\Models\Gist;

test('php runner index page can be accessed', function () {
    $response = $this->get('/php-runner');
    
    $response->assertStatus(200);
    $response->assertSee('PHP 在线运行器');
});

test('php runner validate endpoint works', function () {
    $response = $this->post('/php-runner/validate', [
        'code' => '<?php echo "Hello World"; ?>'
    ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => '代码语法正确'
    ]);
});

test('php runner validate endpoint rejects invalid code', function () {
    $response = $this->post('/php-runner/validate', [
        'code' => '<?php echo "test" { invalid syntax'
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'success' => false
    ]);
});

test('php runner validate endpoint rejects dangerous code', function () {
    $response = $this->post('/php-runner/validate', [
        'code' => '<?php system("ls"); ?>'
    ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => false,
        'type' => 'security'
    ]);
});

test('php runner can load public gist', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Test PHP Gist',
        'content' => '<?php echo "Hello from gist"; ?>',
        'language' => 'php',
        'is_public' => true,
    ]);

    $response = $this->get("/php-runner/gist/{$gist->id}");
    
    $response->assertStatus(200);
    $response->assertSee('Hello from gist');
});

test('php runner rejects non-php gist', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'JavaScript Gist',
        'content' => 'console.log("Hello");',
        'language' => 'javascript',
        'is_public' => true,
    ]);

    $response = $this->get("/php-runner/gist/{$gist->id}");
    
    $response->assertRedirect("/gists/{$gist->id}");
    $response->assertSessionHas('error', '只支持运行 PHP 代码');
});

test('php runner rejects private gist for unauthenticated user', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Private PHP Gist',
        'content' => '<?php echo "Private content"; ?>',
        'language' => 'php',
        'is_public' => false,
    ]);

    $response = $this->get("/php-runner/gist/{$gist->id}");
    
    $response->assertStatus(403);
});

test('php runner allows private gist for owner', function () {
    $user = User::factory()->create();
    $gist = Gist::factory()->create([
        'user_id' => $user->id,
        'title' => 'Private PHP Gist',
        'content' => '<?php echo "Private content"; ?>',
        'language' => 'php',
        'is_public' => false,
    ]);

    $response = $this->actingAs($user)->get("/php-runner/gist/{$gist->id}");
    
    $response->assertStatus(200);
    $response->assertSee('Private content');
});

test('php runner log endpoint works for authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/php-runner/log', [
        'code' => '<?php echo "Test"; ?>',
        'output' => 'Test',
        'execution_time' => 50,
        'memory_usage' => '1MB'
    ]);
    
    $response->assertStatus(200);
    $response->assertJson([
        'success' => true,
        'message' => '执行日志已记录'
    ]);
});

test('php runner log endpoint requires authentication', function () {
    $response = $this->post('/php-runner/log', [
        'code' => '<?php echo "Test"; ?>',
        'output' => 'Test',
        'execution_time' => 50,
        'memory_usage' => '1MB'
    ]);
    
    $response->assertStatus(302); // Redirect to login
});
