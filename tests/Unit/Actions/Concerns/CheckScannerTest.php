<?php

use App\Actions\Concerns\CheckScanner;

test('true is true', function () {
    console('default', [
        '--sort' => true,
        // '--config' => base_path('tests/Fixtures/config/scanner.php'),
    ]);

    $checkScanner = resolve(CheckScanner::class);

    dd($checkScanner);

    // $test = run('default', [
    //     '--check' => true,
    //     '--config' => base_path('tests/Fixtures/config/scanner.php'),
    // ]);

    // dd($test);
});
