<?php

use App\Actions\Concerns\UpdateScanner;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

test('it should merge old and new translations', function () {
    console('default', []);

    $updateScanner = resolve(UpdateScanner::class);

    $new = [
        'email' => '',
        'address' => [
            'city' => '',
        ],
        'phones' => [
            'home' => '',
        ],
    ];

    $old = [
        'name' => 'Name',
        'address' => [
            'street' => 'Address Street',
            'zip' => 'Address Zip',
        ],
        'phones' => [
            'work' => 'Work Phone',
        ],
    ];

    [$merged, $diff] = $this->callMethod($updateScanner, 'mergeTranslations', [$old, $new]);

    expect($diff)->toBe([
        'email',
        'address.city',
        'phones.home',
    ]);
    expect($merged)->toBe([
        'email' => '',
        'address' => [
            'city' => '',
            'street' => 'Address Street',
            'zip' => 'Address Zip',
        ],
        'phones' => [
            'home' => '',
            'work' => 'Work Phone',
        ],
        'name' => 'Name',
    ]);
});

test('it should return files to scan', function () {
    console('default', []);

    $updateScanner = resolve(UpdateScanner::class);

    $this->setProperty($updateScanner, 'config', []);

    rescue(function () use ($updateScanner) {
        $this->callMethod($updateScanner, 'getFilesToScan');
    }, function (Exception $e) {
        expect($e->getCode())->toBe(0);
        expect($e->getMessage())->toBe('Extensions are not set.');
    });

    $this->setProperty($updateScanner, 'config', [
        'extensions' => ['.php'],
    ]);

    rescue(function () use ($updateScanner) {
        $this->callMethod($updateScanner, 'getFilesToScan');
    }, function (Exception $e) {
        expect($e->getCode())->toBe(0);
        expect($e->getMessage())->toBe('Config paths are not set.');
    });

    $this->setProperty($updateScanner, 'config', scannerConfig(
        base_path('tests/Fixtures/test-a/scanner.json'),
    ));

    $files = $this->callMethod($updateScanner, 'getFilesToScan');

    $filenames = $files->map(fn (SplFileInfo $file) => $file->getRealPath())->toArray();

    expect($filenames)->toBe([
        base_path('tests/Fixtures/test-a/resources/app/app.blade.php'),
        base_path('tests/Fixtures/test-a/resources/base/base.blade.php'),
    ]);
});

test('it should extract translation keys from file', function () {
    console('default', []);

    $updateScanner = resolve(UpdateScanner::class);

    $this->setProperty($updateScanner, 'config', []);

    [$fileApp, $fileBase] = File::allFiles(base_path('tests/Fixtures/test-a/resources'));

    rescue(function () use ($fileApp, $fileBase, $updateScanner) {
        $file = Arr::random([$fileApp, $fileBase]);

        $this->callMethod($updateScanner, 'extractTranslationKeysFromFile', [$file]);
    }, function (Exception $e) {
        expect($e->getCode())->toBe(0);
        expect($e->getMessage())->toBe('Methods are not set in config.');
    });

    $this->setProperty($updateScanner, 'config', scannerConfig(
        base_path('tests/Fixtures/test-a/scanner.json'),
    ));

    $keys = $this->callMethod($updateScanner, 'extractTranslationKeysFromFile', [$fileApp]);

    expect($keys)->toBe([
        0 => 'App Name',
        1 => 'app.name',
        2 => 'App. Name!',
        3 => 'App. Name.',
        4 => 'App Version',
        5 => 'app.version',
        6 => 'App. Version!',
        7 => 'App. Version.',
        8 => 'App Description',
        9 => 'app.description',
        10 => 'App. Description!',
        11 => 'App. Description.',
    ]);

    $keys = $this->callMethod($updateScanner, 'extractTranslationKeysFromFile', [$fileBase]);

    expect($keys)->toBe([
        0 => 'Base Name',
        1 => 'base.name',
        2 => 'Base. Name!',
        3 => 'Base. Name.',
        4 => 'Base Version',
        5 => 'base.version',
        6 => 'Base. Version!',
        7 => 'Base. Version.',
        8 => 'Base Description',
        9 => 'base.description',
        10 => 'Base. Description!',
        11 => 'Base. Description.',
    ]);
});

test('it should return new translations', function () {
    console('default', []);

    $updateScanner = resolve(UpdateScanner::class);

    $translations = [
        'name' => 'Name',
        'address' => [
            'street' => 'Address Street',
            'zip' => 'Address Zip',
        ],
        'phones' => [
            'work' => 'Work Phone',
        ],
    ];
    $collectedKeys = [
        'App Name',
        'app.name',
        'App. Name!',
        'App. Name.',
        'App Version',
        'app.version',
        'App. Version!',
        'App. Version.',
        'App Description',
        'app.description',
        'App. Description!',
        'App. Description.',
        'Base Name',
        'base.name',
        'Base. Name!',
        'Base. Name.',
        'Base Version',
        'base.version',
        'Base. Version!',
        'Base. Version.',
        'Base Description',
        'base.description',
        'Base. Description!',
        'Base. Description.',
    ];

    $keys = $this->callMethod($updateScanner, 'returnNewTranslations', [$translations, $collectedKeys]);

    expect($keys)->toBe([
        'App Description' => '',
        'App Name' => '',
        'App Version' => '',
        'App. Description!' => '',
        'App. Description.' => '',
        'App. Name!' => '',
        'App. Name.' => '',
        'App. Version!' => '',
        'App. Version.' => '',
        'Base Description' => '',
        'Base Name' => '',
        'Base Version' => '',
        'Base. Description!' => '',
        'Base. Description.' => '',
        'Base. Name!' => '',
        'Base. Name.' => '',
        'Base. Version!' => '',
        'Base. Version.' => '',
        'address' => [
            'street' => 'Address Street',
            'zip' => 'Address Zip',
        ],
        'app' => [
            'description' => '',
            'name' => '',
            'version' => '',
        ],
        'base' => [
            'description' => '',
            'name' => '',
            'version' => '',
        ],
        'name' => 'Name',
        'phones' => [
            'work' => 'Work Phone',
        ],
    ]);

    $updateScanner = resolve(UpdateScanner::class);

    $translations = [
        'An app? Yes, an app!' => '',
        'Click the ":action" button to open the app.' => 'Click the ":action" button to open the app.',
        "Don't forget to update your app." => '',
        "If you're enjoying this app, please leave a review!" => "If you're enjoying this app, please leave a review!",
        "The app's performance can't be beat." => '',
        'The app, as you know, is great.' => 'The app, as you know, is great.',
    ];
    $collectedKeys = [
        'The app, as you know, is great.',
        'Click the \":action\" button to open the app.',
        'An app? Yes, an app!',
        "The app's performance can't be beat.",
        "Don't forget to update your app.",
        "If you're enjoying this app, please leave a review!",
    ];

    $keys = $this->callMethod($updateScanner, 'returnNewTranslations', [$translations, $collectedKeys]);

    expect($keys)->toBe([
        'An app? Yes, an app!' => '',
        'Click the ":action" button to open the app.' => 'Click the ":action" button to open the app.',
        "Don't forget to update your app." => '',
        "If you're enjoying this app, please leave a review!" => "If you're enjoying this app, please leave a review!",
        "The app's performance can't be beat." => '',
        'The app, as you know, is great.' => 'The app, as you know, is great.',
    ]);
});
