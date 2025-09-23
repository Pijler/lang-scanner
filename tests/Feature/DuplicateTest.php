<?php

test('it should run default command (test-j)', function () {
    [$status, $output] = run('default', [
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-j/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '6 files, New 62 translations scanned');

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/en-US.json')))->toBe([
    //     //
    // ]);

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/pt-BR.json')))->toBe([
    //     //
    // ]);
});

test('it should run default command (test-j) removing duplicates', function () {
    [$status, $output] = run('default', [
        '--remove' => true,
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-j/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '6 files, New 71 translations scanned');

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/en-US.json')))->toBe([
    //     //
    // ]);

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/pt-BR.json')))->toBe([
    //     //
    // ]);
});
