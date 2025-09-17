<?php

use App\Actions\Concerns\CheckScanner;

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
