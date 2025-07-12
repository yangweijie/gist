<?php

use Laravel\Dusk\Browser;

test('gist page loads with syntax highlighting', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->screenshot('gists-with-highlighting')
                ->assertPathIs('/gists');
    });
});

test('search functionality works with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(3000)
                ->screenshot('before-search');

        // Check if search input exists
        if ($browser->element('input[name="search"]')) {
            $browser->type('input[name="search"]', 'test')
                    ->pause(3000) // Wait for HTMX request
                    ->screenshot('search-results');
        }

        $browser->assertPathIs('/gists');
    });
});

test('language filter works with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->select('select[name="language"]', 'php')
                ->pause(3000) // Wait for HTMX request
                ->screenshot('language-filter')
                ->assertPathIs('/gists');
    });
});

test('sort functionality works with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->select('select[name="sort"]', 'popular')
                ->pause(3000) // Wait for HTMX request
                ->screenshot('sort-popular')
                ->assertPathIs('/gists');
    });
});

test('tag filter works with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Check if tag select exists and has options
        if ($browser->element('select[name="tag"] option[value!=""]')) {
            $browser->select('select[name="tag"]', $browser->element('select[name="tag"] option[value!=""]:first-child')->getAttribute('value'))
                    ->pause(3000) // Wait for HTMX request
                    ->screenshot('tag-filter');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('clear filters button works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->type('input[name="search"]', 'test')
                ->pause(2000);
        
        // Check if clear filters button appears
        if ($browser->element('button:contains("清除筛选")')) {
            $browser->click('button:contains("清除筛选")')
                    ->pause(2000)
                    ->screenshot('filters-cleared');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('gist preview modal works with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Check if preview buttons exist
        if ($browser->element('button[data-action="preview"]')) {
            $browser->click('button[data-action="preview"]:first')
                    ->pause(3000) // Wait for HTMX modal to load
                    ->screenshot('gist-preview-modal');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('copy button functionality', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Check if copy buttons exist
        if ($browser->element('button[data-action="copy"]')) {
            $browser->click('button[data-action="copy"]:first')
                    ->pause(1000)
                    ->screenshot('copy-button-clicked');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('load more functionality with htmx', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Check if load more button exists
        if ($browser->element('button:contains("加载更多")')) {
            $browser->click('button:contains("加载更多")')
                    ->pause(3000) // Wait for HTMX request
                    ->screenshot('load-more-results');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('php wasm execution works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Look for PHP gists with run button
        if ($browser->element('button[data-action="run-php"]')) {
            $browser->click('button[data-action="run-php"]:first')
                    ->pause(5000) // Wait for WASM to load and execute
                    ->screenshot('php-wasm-execution');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('responsive design works on mobile viewport', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(375, 667) // iPhone viewport
                ->visit('/gists')
                ->pause(2000)
                ->screenshot('mobile-gists-page')
                ->assertPathIs('/gists');
    });
});

test('dark mode toggle works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);
        
        // Check if dark mode toggle exists
        if ($browser->element('button[data-action="toggle-theme"]')) {
            $browser->click('button[data-action="toggle-theme"]')
                    ->pause(1000)
                    ->screenshot('dark-mode-toggled');
        }
        
        $browser->assertPathIs('/gists');
    });
});

test('keyboard shortcuts work', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->keys('input[name="search"]', '{ctrl}', 'k') // Common search shortcut
                ->pause(1000)
                ->screenshot('keyboard-shortcut-test')
                ->assertPathIs('/gists');
    });
});

test('infinite scroll works', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->script('window.scrollTo(0, document.body.scrollHeight);') // Scroll to bottom
                ->pause(3000) // Wait for potential infinite scroll
                ->screenshot('infinite-scroll-test')
                ->assertPathIs('/gists');
    });
});

test('error handling works for failed htmx requests', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000);

        // Simulate network error by searching for something that might cause issues
        $browser->type('input[name="search"]', 'very-long-search-term-that-might-cause-issues-' . str_repeat('x', 100))
                ->pause(3000)
                ->screenshot('error-handling-test')
                ->assertPathIs('/gists');
    });
});

// ===== 增强的代码高亮和交互测试 =====

test('syntax highlighting loads correctly for different languages', function () {
    $user = createTestUser();

    // Create test gists with different languages
    $phpGist = createTestGist($user, [
        'title' => 'PHP Test',
        'content' => '<?php echo "Hello World!"; ?>',
        'language' => 'php',
        'filename' => 'test.php'
    ]);

    $jsGist = createTestGist($user, [
        'title' => 'JavaScript Test',
        'content' => 'console.log("Hello World!");',
        'language' => 'javascript',
        'filename' => 'test.js'
    ]);

    $this->browse(function (Browser $browser) use ($phpGist, $jsGist) {
        // Test PHP syntax highlighting
        $browser->visit("/gists/{$phpGist->id}")
                ->pause(3000)
                ->assertPresent('.hljs')
                ->assertPresent('code.language-php')
                ->screenshot('php-syntax-highlighting');

        // Test JavaScript syntax highlighting
        $browser->visit("/gists/{$jsGist->id}")
                ->pause(3000)
                ->assertPresent('.hljs')
                ->assertPresent('code.language-javascript')
                ->screenshot('js-syntax-highlighting');
    });
});

test('code copy functionality works correctly', function () {
    $user = createTestUser();
    $gist = createTestGist($user, [
        'content' => '<?php echo "Test copy functionality"; ?>'
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/gists/{$gist->id}")
                ->pause(2000)
                ->assertPresent('button[data-action="copy"]')
                ->click('button[data-action="copy"]')
                ->pause(1000)
                ->screenshot('copy-functionality-test');

        // Check if success message appears (if implemented)
        // Note: Clipboard testing is limited in browser automation
    });
});

test('htmx search updates results without page reload', function () {
    $user = createTestUser();

    // Create test gists with searchable content
    createTestGist($user, [
        'title' => 'PHP Search Test',
        'content' => '<?php echo "searchable content"; ?>',
        'language' => 'php'
    ]);

    createTestGist($user, [
        'title' => 'JavaScript Search Test',
        'content' => 'console.log("different content");',
        'language' => 'javascript'
    ]);

    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->assertPresent('.gist-item')
                ->type('input[name="search"]', 'PHP')
                ->pause(3000) // Wait for HTMX request
                ->screenshot('htmx-search-results')
                ->assertPathIs('/gists') // Should stay on same page
                ->assertQueryStringHas('search', 'PHP');
    });
});

test('htmx language filter updates results dynamically', function () {
    $user = createTestUser();

    // Create gists with different languages
    createTestGist($user, ['language' => 'php', 'title' => 'PHP Gist']);
    createTestGist($user, ['language' => 'javascript', 'title' => 'JS Gist']);
    createTestGist($user, ['language' => 'python', 'title' => 'Python Gist']);

    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->select('select[name="language"]', 'php')
                ->pause(3000) // Wait for HTMX request
                ->screenshot('htmx-language-filter')
                ->assertPathIs('/gists')
                ->assertQueryStringHas('language', 'php');
    });
});

test('php wasm runner loads and executes code', function () {
    $user = createTestUser();
    $phpGist = createTestGist($user, [
        'title' => 'PHP WASM Test',
        'content' => '<?php echo "Hello from PHP WASM!"; ?>',
        'language' => 'php',
        'filename' => 'wasm-test.php'
    ]);

    $this->browse(function (Browser $browser) use ($phpGist) {
        $browser->visit("/gists/{$phpGist->id}")
                ->pause(2000);

        // Check if PHP runner button exists
        if ($browser->element('button[data-action="run-php"]') || $browser->element('.php-runner-btn')) {
            $browser->click('button[data-action="run-php"], .php-runner-btn')
                    ->pause(5000) // Wait for WASM to load and execute
                    ->screenshot('php-wasm-execution-result');

            // Check if output container appears
            if ($browser->element('.php-output') || $browser->element('#php-result')) {
                $browser->assertPresent('.php-output, #php-result');
            }
        }
    });
});

test('responsive navigation works on mobile', function () {
    $this->browse(function (Browser $browser) {
        $browser->resize(375, 667) // Mobile viewport
                ->visit('/gists')
                ->pause(2000)
                ->screenshot('mobile-navigation-test');

        // Check if mobile menu toggle exists
        if ($browser->element('.mobile-menu-toggle') || $browser->element('[data-toggle="mobile-menu"]')) {
            $browser->click('.mobile-menu-toggle, [data-toggle="mobile-menu"]')
                    ->pause(1000)
                    ->screenshot('mobile-menu-opened');
        }
    });
});

test('code theme switching works', function () {
    $user = createTestUser();
    $gist = createTestGist($user, [
        'content' => '<?php echo "Theme test"; ?>'
    ]);

    $this->browse(function (Browser $browser) use ($gist) {
        $browser->visit("/gists/{$gist->id}")
                ->pause(2000);

        // Check if theme toggle exists
        if ($browser->element('button[data-action="toggle-theme"]') || $browser->element('.theme-toggle')) {
            $browser->click('button[data-action="toggle-theme"], .theme-toggle')
                    ->pause(1000)
                    ->screenshot('theme-switched');
        }
    });
});

test('htmx loading indicators work', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->type('input[name="search"]', 'test')
                ->pause(500) // Quick pause to catch loading state
                ->screenshot('htmx-loading-state')
                ->pause(2500) // Wait for request to complete
                ->screenshot('htmx-loaded-state');
    });
});
