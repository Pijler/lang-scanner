<?php

test('true is true', function () {
    expect(true)->toBeTrue();

    // [$status, $output] = run('default', [
    //     '--check' => true,
    // ]);

    // dd($output);
});
