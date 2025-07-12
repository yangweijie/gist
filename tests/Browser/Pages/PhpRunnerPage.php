<?php

namespace Tests\Browser\Pages;

use Laravel\Dusk\Browser;
use Laravel\Dusk\Page;

class PhpRunnerPage extends Page
{
    /**
     * Get the URL for the page.
     */
    public function url(): string
    {
        return '/php-runner';
    }

    /**
     * Assert that the browser is on the page.
     */
    public function assert(Browser $browser): void
    {
        $browser->assertPathIs($this->url())
                ->assertSee('PHP 在线运行器');
    }

    /**
     * Get the element shortcuts for the page.
     */
    public function elements(): array
    {
        return [
            '@code-editor' => '#code-editor',
            '@output-panel' => '.output-panel',
            '@run-button' => '#run-btn',
            '@clear-button' => '#clear-btn',
            '@clear-output-button' => '#clear-output-btn',
            '@validate-button' => '#validate-btn',
            '@examples-button' => '#examples-btn',
            '@fullscreen-button' => '#fullscreen-btn',
            '@status-indicator' => '#php-status',
            '@status-text' => '#php-status-text',
            '@examples-modal' => '#examples-modal',
            '@examples-grid' => '#examples-grid',
            '@output-content' => '#output-content',
            '@validation-result' => '.validation-result',
            '@loading-spinner' => '#loading-spinner',
            '@execution-time' => '#execution-time',
        ];
    }

    /**
     * Wait for PHP runtime to be ready.
     */
    public function waitForPhpReady(Browser $browser, int $timeout = 15): self
    {
        $browser->waitFor('@status-indicator', 10)
                ->waitUntilMissing('@loading-spinner', $timeout);

        return $this;
    }

    /**
     * Clear and type code in the editor.
     */
    public function typeCode(Browser $browser, string $code): self
    {
        $browser->clear('@code-editor')
                ->type('@code-editor', $code);

        return $this;
    }

    /**
     * Run the code in the editor.
     */
    public function runCode(Browser $browser, int $timeout = 10): self
    {
        $browser->click('@run-button')
                ->waitFor('@output-content', $timeout);

        return $this;
    }

    /**
     * Validate the code in the editor.
     */
    public function validateCode(Browser $browser, int $timeout = 5): self
    {
        $browser->click('@validate-button')
                ->waitFor('@validation-result', $timeout);

        return $this;
    }

    /**
     * Clear the code editor.
     */
    public function clearCode(Browser $browser): self
    {
        $browser->click('@clear-button');

        return $this;
    }

    /**
     * Clear the output panel.
     */
    public function clearOutput(Browser $browser): self
    {
        $browser->click('@clear-output-button');

        return $this;
    }

    /**
     * Open examples modal.
     */
    public function openExamples(Browser $browser): self
    {
        $browser->click('@examples-button')
                ->waitFor('@examples-modal', 2);

        return $this;
    }

    /**
     * Select an example by index.
     */
    public function selectExample(Browser $browser, int $index): self
    {
        $browser->click(".example-card:nth-child({$index})")
                ->waitFor('@code-editor', 2);

        return $this;
    }

    /**
     * Toggle fullscreen mode.
     */
    public function toggleFullscreen(Browser $browser): self
    {
        $browser->click('@fullscreen-button')
                ->pause(1000);

        return $this;
    }

    /**
     * Assert that code execution was successful.
     */
    public function assertExecutionSuccess(Browser $browser, string $expectedOutput = null): self
    {
        $browser->assertPresent('@output-content')
                ->assertDontSeeIn('@output-panel', 'Error');

        if ($expectedOutput) {
            $browser->assertSeeIn('@output-panel', $expectedOutput);
        }

        return $this;
    }

    /**
     * Assert that code execution failed with error.
     */
    public function assertExecutionError(Browser $browser, string $expectedError = null): self
    {
        $browser->assertPresent('@output-content')
                ->assertSeeIn('@output-panel', 'Error');

        if ($expectedError) {
            $browser->assertSeeIn('@output-panel', $expectedError);
        }

        return $this;
    }

    /**
     * Assert that validation was successful.
     */
    public function assertValidationSuccess(Browser $browser): self
    {
        $browser->assertSeeIn('@validation-result', '代码语法正确');

        return $this;
    }

    /**
     * Assert that validation failed.
     */
    public function assertValidationError(Browser $browser, string $expectedError = null): self
    {
        $browser->assertPresent('@validation-result');

        if ($expectedError) {
            $browser->assertSeeIn('@validation-result', $expectedError);
        }

        return $this;
    }

    /**
     * Assert that execution time is displayed.
     */
    public function assertExecutionTimeDisplayed(Browser $browser): self
    {
        $browser->assertSeeIn('@output-panel', 'ms');

        return $this;
    }

    /**
     * Assert that the editor contains specific code.
     */
    public function assertCodeContains(Browser $browser, string $expectedCode): self
    {
        $browser->assertInputValue('@code-editor', function ($value) use ($expectedCode) {
            return str_contains($value, $expectedCode);
        });

        return $this;
    }

    /**
     * Assert that the output contains specific text.
     */
    public function assertOutputContains(Browser $browser, string $expectedText): self
    {
        $browser->assertSeeIn('@output-panel', $expectedText);

        return $this;
    }

    /**
     * Assert that the output does not contain specific text.
     */
    public function assertOutputNotContains(Browser $browser, string $unexpectedText): self
    {
        $browser->assertDontSeeIn('@output-panel', $unexpectedText);

        return $this;
    }

    /**
     * Use keyboard shortcut to run code (Ctrl+Enter).
     */
    public function runCodeWithShortcut(Browser $browser, int $timeout = 10): self
    {
        $browser->keys('@code-editor', ['{ctrl}', '{enter}'])
                ->waitFor('@output-content', $timeout);

        return $this;
    }

    /**
     * Assert that fullscreen mode is active.
     */
    public function assertFullscreenActive(Browser $browser): self
    {
        $browser->assertPresent('.fullscreen-mode');

        return $this;
    }

    /**
     * Assert that fullscreen mode is not active.
     */
    public function assertFullscreenInactive(Browser $browser): self
    {
        $browser->assertMissing('.fullscreen-mode');

        return $this;
    }

    /**
     * Assert that examples modal is visible.
     */
    public function assertExamplesVisible(Browser $browser): self
    {
        $browser->assertPresent('@examples-modal')
                ->assertSee('示例代码');

        return $this;
    }

    /**
     * Assert that the page is responsive on mobile.
     */
    public function assertMobileResponsive(Browser $browser): self
    {
        $browser->assertPresent('.grid-cols-1')
                ->assertMissing('.lg\\:grid-cols-2');

        return $this;
    }

    /**
     * Execute a complete test scenario: type code, run it, and verify output.
     */
    public function executeCodeScenario(Browser $browser, string $code, string $expectedOutput, int $timeout = 10): self
    {
        $this->typeCode($browser, $code)
             ->runCode($browser, $timeout)
             ->assertExecutionSuccess($browser, $expectedOutput)
             ->assertExecutionTimeDisplayed($browser);

        return $this;
    }

    /**
     * Execute a validation scenario: type code, validate it, and verify result.
     */
    public function validateCodeScenario(Browser $browser, string $code, bool $shouldPass = true, string $expectedMessage = null): self
    {
        $this->typeCode($browser, $code)
             ->validateCode($browser);

        if ($shouldPass) {
            $this->assertValidationSuccess($browser);
        } else {
            $this->assertValidationError($browser, $expectedMessage);
        }

        return $this;
    }
}
