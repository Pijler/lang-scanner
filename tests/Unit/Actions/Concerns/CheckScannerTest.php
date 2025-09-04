<?php

use App\Actions\Concerns\CheckScanner;

test('it should check default sorting as false', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeFalse();
});

test('it should check if sorting is enabled via option', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    $this->setProperty($checkScanner, 'config', [
        'sort' => true,
    ]);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeTrue();

    $this->setProperty($checkScanner, 'config', [
        'sort' => false,
    ]);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeFalse();
});

test('it should check if sorting is enabled via config', function () {
    console('default', ['--sort' => true]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeTrue();

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--sort' => false]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeFalse();
});

test('it should return current translations', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    $translations = $this->callMethod($checkScanner, 'currentTranslations', [
        [
            'name' => 'John',
            'email' => '',
            'address' => [
                'street' => '123 Main St',
                'city' => null,
                'zip' => '12345',
            ],
            'phones' => [
                'home' => '',
                'work' => '555-1234',
            ],
        ],
    ]);

    expect($translations)->toBeArray();
    expect($translations)->toContain(
        'name',
        'email',
        'phones.home',
        'phones.work',
        'address.zip',
        'address.city',
        'address.street',
    );

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--no-empty' => true]);

    $checkScanner = resolve(CheckScanner::class);

    $translations = $this->callMethod($checkScanner, 'currentTranslations', [
        [
            'name' => 'John',
            'email' => '',
            'address' => [
                'street' => '123 Main St',
                'city' => null,
                'zip' => '12345',
            ],
            'phones' => [
                'home' => '',
                'work' => '555-1234',
            ],
        ],
    ]);

    expect($translations)->toBeArray();
    expect($translations)->toContain(
        'name',
        'phones.work',
        'address.zip',
        'address.street',
    );
});
