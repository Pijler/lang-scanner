<?php

use App\Actions\Concerns\CheckScanner;

test('it should return current translations', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    $translations = $this->callMethod($checkScanner, 'currentTranslations', [
        [
            'name' => 'Name',
            'email' => '',
            'address' => [
                'street' => 'Address Street',
                'city' => '',
                'zip' => 'Address Zip',
            ],
            'phones' => [
                'home' => '',
                'work' => 'Work Phone',
            ],
        ],
    ]);

    expect($translations)->toBeArray();
    expect($translations)->toBe([
        0 => 'name',
        1 => 'email',
        2 => 'address.street',
        3 => 'address.city',
        4 => 'address.zip',
        5 => 'phones.home',
        6 => 'phones.work',
    ]);

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--no-empty' => true]);

    $checkScanner = resolve(CheckScanner::class);

    $translations = $this->callMethod($checkScanner, 'currentTranslations', [
        [
            'name' => 'Name',
            'email' => '',
            'address' => [
                'street' => 'Address Street',
                'city' => '',
                'zip' => 'Address Zip',
            ],
            'phones' => [
                'home' => '',
                'work' => 'Work Phone',
            ],
        ],
    ]);

    expect($translations)->toBeArray();
    expect($translations)->toBe([
        0 => 'name',
        1 => 'address.street',
        2 => 'address.zip',
        3 => 'phones.work',
    ]);
});
