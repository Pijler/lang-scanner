<?php

test('it should report unused translations without changing files (test-r)', function () {
    $before = getContent(base_path('tests/Fixtures/test-r/lang/en-US.json'));

    [$status, $output] = run('default', [
        '--unused' => true,
        '--config' => base_path('tests/Fixtures/test-r/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('FAIL', '2 files, 6 unused translations');
    expect($output)->toContain('Unused: Old key.');

    expect(getContent(base_path('tests/Fixtures/test-r/lang/en-US.json')))->toBe($before);
});

test('it should prune unused translations (test-r)', function () {
    [$status, $output] = run('default', [
        '--prune' => true,
        '--config' => base_path('tests/Fixtures/test-r/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('SCANNED', '2 files, Removed 6 unused translations');

    expect(getContent(base_path('tests/Fixtures/test-r/lang/en-US.json')))->toBe([
        'group' => [
            'used' => 'Group Used',
        ],
        'js' => [
            'used' => 'JS Used',
        ],
        'menu' => [
            'home' => 'Home',
        ],
        'used' => 'Used',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-r/lang/pt-BR.json')))->toBe([
        'group' => [
            'used' => 'Grupo Usado',
        ],
        'js' => [
            'used' => 'JS Usado',
        ],
        'menu' => [
            'home' => 'Início',
        ],
        'used' => 'Usado',
    ]);
});

test('it should not prune unused translations on dry run (test-r)', function () {
    $before = getContent(base_path('tests/Fixtures/test-r/lang/en-US.json'));

    [$status, $output] = run('default', [
        '--prune' => true,
        '--dry-run' => true,
        '--config' => base_path('tests/Fixtures/test-r/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('Removed 6 unused translations', 'Dry run: no files were changed.');

    expect(getContent(base_path('tests/Fixtures/test-r/lang/en-US.json')))->toBe($before);
});

test('it should not allow unused detection on a partial scan (test-r)', function () {
    mockDirty([base_path('tests/Fixtures/test-r/resources/php/app.blade.php')]);

    run('default', [
        '--prune' => true,
        '--dirty' => true,
        '--config' => base_path('tests/Fixtures/test-r/scanner.json'),
    ]);
})->throws(Exception::class, 'cannot be combined with [--dirty]');
