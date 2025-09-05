<?php

use App\Actions\Scanner;

test('it should check default checking as false', function () {
    console('default', []);

    $scanner = resolve(Scanner::class);

    expect($this->callMethod($scanner, 'checked', [[]]))->toBeFalse();
});

test('it should check if checking is enabled via option', function () {
    console('default', []);

    $scanner = resolve(Scanner::class);

    $config = ['check' => true];

    expect($this->callMethod($scanner, 'checked', [$config]))->toBeTrue();

    $config = ['check' => false];

    expect($this->callMethod($scanner, 'checked', [$config]))->toBeFalse();
});

test('it should check if checking is enabled via config', function () {
    console('default', ['--check' => true]);

    $scanner = resolve(Scanner::class);

    expect($this->callMethod($scanner, 'checked', [[]]))->toBeTrue();

    app()->forgetInstance(Scanner::class);

    console('default', ['--check' => false]);

    $scanner = resolve(Scanner::class);

    expect($this->callMethod($scanner, 'checked', [[]]))->toBeFalse();
});
