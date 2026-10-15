<?php

use App\ValueObjects\Issue;

test('it should return the count', function () {
    $issue = new Issue(
        count: 5,
        check: false,
        path: '/path/to',
        file: '/path/to/file.php',
        changes: ['Change 1', 'Change 2'],
    );

    expect($issue->count())->toBe(5);
});

test('it should return the description', function () {
    $issue = new Issue(
        count: 5,
        check: false,
        path: '/path/to',
        file: '/path/to/file.php',
        changes: ['Change 1', 'Change 2'],
    );

    expect($issue->description())->toBe('Change 1, Change 2');
});

test('it should return the color and symbol for check mode', function () {
    $issue = new Issue(
        count: 5,
        check: true,
        path: '/path/to',
        file: '/path/to/file.php',
        changes: ['Change 1', 'Change 2'],
    );

    expect($issue->symbol())->toBe('⨯');
    expect($issue->color())->toBe('red');
});

test('it should return the color and symbol for non-check mode', function () {
    $issue = new Issue(
        count: 5,
        check: false,
        path: '/path/to',
        file: '/path/to/file.php',
        changes: ['Change 1', 'Change 2'],
    );

    expect($issue->symbol())->toBe('✓');
    expect($issue->color())->toBe('green');
});

test('it should return the file path', function () {
    $issue = new Issue(
        count: 5,
        check: false,
        path: '/path/to',
        file: '/path/to/file.php',
        changes: ['Change 1', 'Change 2'],
    );

    expect($issue->file())->toBe('file.php');
});

test('it should describe unused translations', function () {
    $issue = new Issue(
        count: 2,
        check: true,
        path: '/app',
        unused: true,
        changes: ['a', 'b'],
        file: '/app/lang/en.json',
    );

    expect($issue->failing())->toBeTrue();
    expect($issue->description())->toBe('Unused: a, b');

    $issue = new Issue(
        count: 2,
        check: false,
        path: '/app',
        unused: true,
        changes: ['a', 'b'],
        file: '/app/lang/en.json',
    );

    expect($issue->failing())->toBeFalse();
    expect($issue->description())->toBe('Removed: a, b');
});
