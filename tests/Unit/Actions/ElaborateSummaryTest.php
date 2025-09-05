<?php

use App\Actions\ElaborateSummary;

test('it should count issues correctly', function () {
    console('default', []);

    $summary = resolve(ElaborateSummary::class);

    expect($this->callMethod($summary, 'countIssues', [[]]))->toBe(0);

    $changes = [
        ['file' => 'file1.php', 'count' => 2],
        ['file' => 'file2.php', 'count' => 3],
        ['file' => 'file3.php', 'count' => 0],
    ];

    expect($this->callMethod($summary, 'countIssues', [$changes]))->toBe(5);
});
