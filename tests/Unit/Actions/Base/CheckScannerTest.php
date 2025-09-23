<?php

use App\Actions\Base\CheckScanner;

test('it should check default no-updating as false', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'noUpdate'))->toBeFalse();
});

test('it should check if no-updating is enabled via option', function () {
    console('default', []);

    $checkScanner = resolve(CheckScanner::class);

    $this->setProperty($checkScanner, 'config', [
        'no-update' => true,
    ]);

    expect($this->callMethod($checkScanner, 'noUpdate'))->toBeTrue();

    $this->setProperty($checkScanner, 'config', [
        'no-update' => false,
    ]);

    expect($this->callMethod($checkScanner, 'noUpdate'))->toBeFalse();
});

test('it should check if no-updating is enabled via config', function () {
    console('default', ['--no-update' => true]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'noUpdate'))->toBeTrue();

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--no-update' => false]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'noUpdate'))->toBeFalse();
});

test('it should check if no-empty is enabled via option', function () {
    console('default', ['--no-empty' => true]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'noEmpty'))->toBeTrue();

    app()->forgetInstance(CheckScanner::class);

    console('default', ['--no-empty' => false]);

    $checkScanner = resolve(CheckScanner::class);

    expect($this->callMethod($checkScanner, 'noEmpty'))->toBeFalse();
});
