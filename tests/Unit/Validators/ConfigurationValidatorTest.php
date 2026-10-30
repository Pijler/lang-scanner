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
    expect(function () use ($content) {
        ConfigurationValidator::validateFile(
            content: $content,
            path: 'scanner.json',
        );
    })->toThrow(Exception::class, $message);
})->with([
    'unknown root key' => [['scaner' => []], 'unknown key [scaner]'],
    'extends not a list' => [['extends' => 'module'], '[extends] must be a list of strings.'],
    'scanner entry not an object' => [['scanner' => ['lang/']], '[scanner[0]] must be an object.'],
    'flag not a boolean' => [['scanner' => [['check' => 'yes']]], '[scanner[0].check] must be a boolean.'],
    'merge in scanner entry' => [['scanner' => [['merge' => true]]], 'unknown key [merge] in [scanner[0]]'],
    'scanner not a list' => [['scanner' => ['lang_path' => 'lang/']], '[scanner] must be a list of objects.'],
    'unknown scanner key' => [['scanner' => [['lang-path' => 'lang/']]], 'unknown key [lang-path] in [scanner[0]]'],
    'empty lang path' => [['scanner' => [['lang_path' => '']]], '[scanner[0].lang_path] must be a non-empty string.'],
    'paths not a list' => [['scanner' => [['paths' => 'resources/']]], '[scanner[0].paths] must be a list of strings.'],
    'duplicate in scanner entry' => [['scanner' => [['duplicate' => true]]], 'unknown key [duplicate] in [scanner[0]]'],
    'method without placeholder' => [['scanner' => [['methods' => ['__()']]]], 'must contain exactly one "*" placeholder.'],
]);

test('it should report the exact validation message', function () {
    expect(function () {
        ConfigurationValidator::validateFile(
            path: 'scanner.json',
            content: ['scanner' => [['check' => 'yes']]],
        );
    })->toThrow(Exception::class, 'Invalid configuration [scanner.json]: [scanner[0].check] must be a boolean.');
});

test('it should require the keys of each mode', function () {
    expect(function () {
        ConfigurationValidator::validateMode(
            mode: 'update',
            config: ['base_path' => '/app'],
        );
    })->toThrow(Exception::class, 'the "update" mode requires the [lang_path, paths, extensions, methods] options.');

    expect(function () {
        ConfigurationValidator::validateMode(
            mode: 'duplicate',
            config: ['base_path' => '/app', 'lang_path' => 'lang/'],
        );
    })->toThrow(Exception::class, 'Invalid configuration [/app]: the "duplicate" mode requires the [methods] option.');
});
