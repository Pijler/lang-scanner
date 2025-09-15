<?php

test('it should run default command with special characters', function () {
    [$status, $output] = run('default', [
        '--config' => base_path('tests/Fixtures/test-g/scanner.json'),
    ]);

    expect($status)->toBe(1);
    expect($output)->toContain('SCANNED', '2 files, New 12 translations scanned');

    expect(getContent(base_path('tests/Fixtures/test-g/lang/en-US.json')))->toBe([
        'An app? Yes, an app!' => '',
        'Click the ":action" button to open the app.' => '',
        "Don't forget to update your app." => '',
        "If you're enjoying this app, please leave a review!" => '',
        "The app's performance can't be beat." => '',
        'The app, as you know, is great.' => '',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-g/lang/pt-BR.json')))->toBe([
        'An app? Yes, an app!' => '',
        'Click the ":action" button to open the app.' => '',
        "Don't forget to update your app." => '',
        "If you're enjoying this app, please leave a review!" => '',
        "The app's performance can't be beat." => '',
        'The app, as you know, is great.' => '',
    ]);
});
