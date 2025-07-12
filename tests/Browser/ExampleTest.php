<?php

use Laravel\Dusk\Browser;

test('can visit homepage', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->pause(3000) // Wait for page to load
                ->screenshot('homepage-test')
                ->assertPathIs('/');
    });
});

test('homepage has correct title', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->pause(2000)
                ->assertTitleContains('Gist Manager');
    });
});

test('homepage shows gist manager content', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
                ->pause(2000)
                ->assertSee('Gist Manager');
    });
});
