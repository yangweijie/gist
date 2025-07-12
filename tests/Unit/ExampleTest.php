<?php

test('that true is true', function () {
    expect(true)->toBeTrue();
});

test('basic math operations work correctly', function () {
    expect(2 + 2)->toBe(4);
    expect(10 - 5)->toBe(5);
    expect(3 * 4)->toBe(12);
    expect(8 / 2)->toBe(4);
});

test('string operations work correctly', function () {
    expect('hello world')->toContain('world');
    expect('Laravel')->toStartWith('Lar');
    expect('Framework')->toEndWith('work');
    expect(strlen('test'))->toBe(4);
});
