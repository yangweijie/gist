<?php

namespace Tests;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Collection;
use Laravel\Dusk\TestCase as BaseTestCase;
use PHPUnit\Framework\Attributes\BeforeClass;
use App\Models\User;

abstract class DuskTestCase extends BaseTestCase
{
    use DatabaseMigrations;
    /**
     * Prepare for Dusk test execution.
     */
    #[BeforeClass]
    public static function prepare(): void
    {
        if (! static::runningInSail()) {
            static::startChromeDriver(['--port=9515']);
        }
    }

    /**
     * Create the RemoteWebDriver instance.
     */
    protected function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions)->addArguments(collect([
            $this->shouldStartMaximized() ? '--start-maximized' : '--window-size=1920,1080',
            '--disable-search-engine-choice-screen',
        ])->unless($this->hasHeadlessDisabled(), function (Collection $items) {
            return $items->merge([
                '--disable-gpu',
                '--headless=new',
            ]);
        })->all());

        return RemoteWebDriver::create(
            $_ENV['DUSK_DRIVER_URL'] ?? env('DUSK_DRIVER_URL') ?? 'http://localhost:9515',
            DesiredCapabilities::chrome()->setCapability(
                ChromeOptions::CAPABILITY, $options
            )
        );
    }

    /**
     * Create a test user for authentication.
     */
    protected function createTestUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Create a test user with GitHub information.
     */
    protected function createGitHubUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'name' => 'GitHub User',
            'email' => 'github@example.com',
            'github_id' => '12345',
            'github_username' => 'testuser',
            'github_token' => 'github_token_123',
            'avatar_url' => 'https://github.com/avatar.jpg',
            'is_active' => true,
        ], $attributes));
    }

    /**
     * Wait for HTMX requests to complete.
     */
    protected function waitForHtmx($browser, $timeout = 5)
    {
        $browser->waitUntil('typeof htmx !== "undefined" && !htmx.config.requestClass', $timeout);
    }

    /**
     * Wait for element to be visible and clickable.
     */
    protected function waitForClickable($browser, $selector, $timeout = 5)
    {
        return $browser->waitFor($selector, $timeout)
                      ->waitUntil("document.querySelector('$selector') && !document.querySelector('$selector').disabled", $timeout);
    }
}
