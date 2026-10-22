<?php

use App\Validators\ConfigurationValidator;

test('it should accept a valid configuration', function () {
    ConfigurationValidator::validateFile([
        'extends' => ['/module/scanner.json'],
        'cache' => '/storage/app/scanner.cache',
        'scanner' => [
            [
                'check' => true,
                'sort' => false,
                'lang_path' => 'lang/',
                'extensions' => ['.php'],
                'paths' => ['resources/'],
                'methods' => ['__(*)', '$title = *'],
            ],
        ],
    ], 'scanner.json');

    ConfigurationValidator::validateMode(['lang_path' => 'lang/'], 'check');

    expect(true)->toBeTrue();
});

test('it should reject invalid configurations', function (array $content, string $message) {
    expect(fn () => ConfigurationValidator::validateFile($content, 'scanner.json'))
        ->toThrow(Exception::class, $message);
})->with([
    'unknown root key' => [['scaner' => []], 'unknown key [scaner]'],
    'extends not a list' => [['extends' => 'module'], '[extends] must be a list of strings.'],
    'scanner not a list' => [['scanner' => ['lang_path' => 'lang/']], '[scanner] must be a list of objects.'],
    'scanner entry not an object' => [['scanner' => ['lang/']], '[scanner[0]] must be an object.'],
    'unknown scanner key' => [['scanner' => [['lang-path' => 'lang/']]], 'unknown key [lang-path] in [scanner[0]]'],
    'empty lang path' => [['scanner' => [['lang_path' => '']]], '[scanner[0].lang_path] must be a non-empty string.'],
    'paths not a list' => [['scanner' => [['paths' => 'resources/']]], '[scanner[0].paths] must be a list of strings.'],
    'flag not a boolean' => [['scanner' => [['check' => 'yes']]], '[scanner[0].check] must be a boolean.'],
    'method without placeholder' => [['scanner' => [['methods' => ['__()']]]], 'must contain exactly one "*" placeholder.'],
]);

test('it should report the exact validation message', function () {
    expect(fn () => ConfigurationValidator::validateFile(['scanner' => [['check' => 'yes']]], 'scanner.json'))
        ->toThrow(Exception::class, 'Invalid configuration [scanner.json]: [scanner[0].check] must be a boolean.');
});

test('it should require the keys of each mode', function () {
    expect(fn () => ConfigurationValidator::validateMode(['base_path' => '/app'], 'update'))
        ->toThrow(Exception::class, 'the "update" mode requires the [lang_path, paths, extensions, methods] options.');

    expect(fn () => ConfigurationValidator::validateMode(['base_path' => '/app', 'lang_path' => 'lang/'], 'duplicate'))
        ->toThrow(Exception::class, 'Invalid configuration [/app]: the "duplicate" mode requires the [methods] option.');
});
