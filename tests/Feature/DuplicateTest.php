<?php

test('it should run default command (test-j)', function () {
    [$status, $output] = run('default', [
        '--duplicate' => true,
        '--config' => base_path('tests/Fixtures/test-j/scanner.json'),
    ]);

    // expect($status)->toBe(0);
    // expect($output)->toContain('NO ISSUES', '2 files');

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

    // expect($status)->toBe(0);
    // expect($output)->toContain('NO ISSUES', '2 files');

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/en-US.json')))->toBe([
    //     //
    // ]);

    // expect(getContent(base_path('tests/Fixtures/test-j/lang/pt-BR.json')))->toBe([
    //     //
    // ]);
});
