<?php

use App\Enum\Status;
use App\Output\ProgressOutput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

test('it should not fail on very narrow terminals', function () {
    $columns = getenv('COLUMNS');

    putenv('COLUMNS=4');

    try {
        $output = new BufferedOutput;

        $progress = new ProgressOutput(new ArrayInput([]), $output);

        $progress->handle(Status::SKIPPED);
        $progress->handle(Status::SKIPPED);

        expect($output->fetch())->toContain('.');
    } finally {
        putenv($columns === false ? 'COLUMNS' : "COLUMNS={$columns}");
    }
});
