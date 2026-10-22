<?php

test('it should resolve a relative config path', function () {
    $cwd = getcwd();

    chdir(base_path('tests/Fixtures'));

    try {
        [$status, $output] = run('default', [
            '--config' => 'test-k/scanner.json',
        ]);
    } finally {
        chdir($cwd);
    }

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '1 file');

    expect(getContent(base_path('tests/Fixtures/test-k/lang/en-US.json')))->toHaveKey('Ok.');
});

test('it should fail when the config file does not exist', function () {
    run('default', [
        '--config' => base_path('tests/Fixtures/missing/scanner.json'),
    ]);
})->throws(Exception::class, 'The configuration file');

test('it should fail when an extended config does not exist (test-m)', function () {
    run('default', [
        '--config' => base_path('tests/Fixtures/test-m/scanner.json'),
    ]);
})->throws(Exception::class, 'The extended configuration file');

test('it should fail on circular extends (test-l)', function () {
    run('default', [
        '--config' => base_path('tests/Fixtures/test-l/scanner.json'),
    ]);
})->throws(Exception::class, 'Circular extends detected');

test('it should fail when a translation file is not valid JSON (test-n)', function () {
    run('default', [
        '--check' => true,
        '--config' => base_path('tests/Fixtures/test-n/scanner.json'),
    ]);
})->throws(Exception::class, 'is not valid JSON');
