<?php

use Laravel\Dusk\Browser;

test('debug login page content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
                ->pause(2000)
                ->screenshot('login-page-content')
                ->assertPathIs('/login');
        
        // Get page source for debugging
        $source = $browser->driver->getPageSource();
        echo "Page source: " . substr($source, 0, 1000) . "...\n";
    });
});

test('debug register page content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
                ->pause(2000)
                ->screenshot('register-page-content')
                ->assertPathIs('/register');
        
        // Get page source for debugging
        $source = $browser->driver->getPageSource();
        echo "Page source: " . substr($source, 0, 1000) . "...\n";
    });
});

test('debug homepage content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->pause(2000)
                ->screenshot('homepage-content')
                ->assertPathIs('/');

        // Get page source for debugging
        $source = $browser->driver->getPageSource();
        echo "Page source: " . substr($source, 0, 1000) . "...\n";
    });
});

test('debug gists page content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/gists')
                ->pause(2000)
                ->screenshot('gists-page-content')
                ->assertPathIs('/gists');

        // Get page source for debugging
        $source = $browser->driver->getPageSource();
        echo "Gists page source: " . substr($source, 0, 1000) . "...\n";
    });
});
