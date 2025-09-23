<?php

use App\Actions\Base\CheckScanner;
use App\Actions\Base\UpdateScanner;

test('it should check default dotting as false', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'dotted'))->toBeFalse();
});

test('it should check if dotting is enabled via option', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    $this->setProperty($checkScanner, 'config', [
        'dot' => true,
    ]);

    expect($this->callMethod($checkScanner, 'dotted'))->toBeTrue();

    $this->setProperty($checkScanner, 'config', [
        'dot' => false,
    ]);

    expect($this->callMethod($checkScanner, 'dotted'))->toBeFalse();
});

test('it should check if dotting is enabled via config', function () {
    console('default', ['--dot' => true]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'dotted'))->toBeTrue();

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--dot' => false]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'dotted'))->toBeFalse();
});

test('it should check default sorting as true', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'sorted'))->toBeTrue();
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

test('it should return merged and diff translations', function () {
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

    [$merged, $diff] = $this->callMethod($updateScanner, 'diffTranslations', [$old, $new]);

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

test('it should return merged and diff translations with no-empty enabled', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

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
        'email' => '',
        'address' => [
            'city' => '',
        ],
        'phones' => [
            'home' => '',
        ],
    ];

    [$merged, $diff] = $this->callMethod($checkScanner, 'diffTranslations', [$old, $new]);

    expect($diff)->toBe([]);
    expect($merged)->toBe([
        'email' => '',
        'address' => [
            'city' => '',
        ],
        'phones' => [
            'home' => '',
        ],
    ]);

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--no-empty' => true]);

    $checkScanner = resolve(CheckScanner::class);

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
        'email' => '',
        'address' => [
            'city' => '',
        ],
        'phones' => [
            'home' => '',
        ],
    ];

    [$merged, $diff] = $this->callMethod($checkScanner, 'diffTranslations', [$old, $new]);

    expect($diff)->toBe([
        'email',
        'address.city',
        'phones.home',
    ]);
    expect($merged)->toBe([
        'email' => '',
        'address' => [
            'city' => '',
        ],
        'phones' => [
            'home' => '',
        ],
    ]);
});
