<?php

use App\Actions\Concerns\RecursiveConfigs;
use App\Commands\DefaultCommand;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Runs the given console command.
 */
function run(string $command, array $arguments): array
{
    [$input, $output] = console($command, $arguments);

    $status = resolve(Kernel::class)->call($command, $arguments, $output);

    $display = $output->fetch();

    return [$status, $display];
}

/**
 * Prepares the console input and output for the given command.
 */
function console(string $command, array $arguments): array
{
    $commandInstance = match ($command) {
        'default' => resolve(DefaultCommand::class),
    };

    $output = new BufferedOutput(BufferedOutput::VERBOSITY_VERBOSE);

    $input = new ArrayInput($arguments, $commandInstance->getDefinition());

    app()->singleton(InputInterface::class, fn () => $input);
    app()->singleton(OutputInterface::class, fn () => $output);

    return [$input, $output];
}

/**
 * Get the scanner configuration from the given path.
 */
function scannerConfig(string $path): array
{
    $configs = (new RecursiveConfigs($path))->execute();

    return Arr::first($configs);
}
