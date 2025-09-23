<?php

use App\Actions\Base\DuplicateScanner;

test('it should check if remove is enabled via option', function () {
    console('default', ['--remove' => true]);

    $duplicateScanner = resolve(DuplicateScanner::class);

    expect($this->callMethod($duplicateScanner, 'remove'))->toBeTrue();

    app()->forgetInstance(DuplicateScanner::class);

    console('default', ['--remove' => false]);

    $duplicateScanner = resolve(DuplicateScanner::class);

    expect($this->callMethod($duplicateScanner, 'remove'))->toBeFalse();
});
