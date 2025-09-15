<?php

test('it should run default command with special characters (text-g)', function () {
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

test('it should run default command with special characters (text-h)', function () {
    [$status, $output] = run('default', [
        '--config' => base_path('tests/Fixtures/test-h/scanner.json'),
    ]);

    expect($status)->toBe(0);
    expect($output)->toContain('NO ISSUES', '2 files');

    expect(getContent(base_path('tests/Fixtures/test-h/lang/en-US.json')))->toBe([
        'An app? Yes, an app!' => '',
        'Click the ":action" button to open the app.' => 'Click the ":action" button to open the app.',
        "Don't forget to update your app." => '',
        "If you're enjoying this app, please leave a review!" => "If you're enjoying this app, please leave a review!",
        "The app's performance can't be beat." => '',
        'The app, as you know, is great.' => 'The app, as you know, is great.',
    ]);

    expect(getContent(base_path('tests/Fixtures/test-h/lang/pt-BR.json')))->toBe([
        'An app? Yes, an app!' => 'An app? Yes, an app!',
        'Click the ":action" button to open the app.' => '',
        "Don't forget to update your app." => "Don't forget to update your app.",
        "If you're enjoying this app, please leave a review!" => '',
        "The app's performance can't be beat." => "The app's performance can't be beat.",
        'The app, as you know, is great.' => '',
    ]);
});
