<?php

test('it should not change files on dry run (test-a)', function () {
    [$status, $output] = run('default', [
        '--dry-run' => true,
        '--config' => base_path('tests/Fixtures/test-a/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '2 files, New 48 translations scanned', 'Dry run: no files were changed.');

    expect(getContent(base_path('tests/Fixtures/test-a/lang/en-US.json')))->toBe([]);
    expect(getContent(base_path('tests/Fixtures/test-a/lang/pt-BR.json')))->toBe([]);
});

test('it should not change files on dry run in merge mode (test-b)', function () {
    $before = getContent(base_path('tests/Fixtures/test-b/lang/en-US.json'));

    [$status, $output] = run('default', [
        '--merge' => true,
        '--dry-run' => true,
        '--config' => base_path('tests/Fixtures/test-b/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('Dry run: no files were changed.');

    expect(getContent(base_path('tests/Fixtures/test-b/lang/en-US.json')))->toBe($before);
});
