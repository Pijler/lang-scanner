<?php

use App\Actions\Concerns\RecursiveConfigs;
use App\Commands\DefaultCommand;
use App\Contracts\PathsRepository;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Tests\TestCase;

$fixturesPath = __DIR__.'/Fixtures';

$tempBackupPath = __DIR__.'/../storage/app/temp-backup';

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

uses()->beforeEach(function () use ($fixturesPath, $tempBackupPath) {
    File::ensureDirectoryExists($tempBackupPath);

    File::copyDirectory($fixturesPath, $tempBackupPath);
})->in('Unit', 'Feature');

uses()->afterEach(function () use ($fixturesPath, $tempBackupPath) {
    File::ensureDirectoryExists($tempBackupPath);

    File::copyDirectory($tempBackupPath, $fixturesPath);
})->in('Unit', 'Feature');

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
 * Mocks the PathsRepository to return the given paths for the diff method.
 */
function mockDiff(array $paths): void
{
    $mock = mock(PathsRepository::class);

    $mock->shouldReceive('diff')->twice()->andReturn($paths);

    app()->instance(PathsRepository::class, $mock);
}

/**
 * Mocks the PathsRepository to return the given paths for the dirty method.
 */
function mockDirty(array $paths): void
{
    $mock = mock(PathsRepository::class);

    $mock->shouldReceive('dirty')->twice()->andReturn($paths);

    app()->instance(PathsRepository::class, $mock);
}

/**
 * Get the JSON content from the given file path.
 */
function getContent(string $filePath): mixed
{
    $content = File::get($filePath);

    return json_decode($content, true);
}

/**
 * Get the scanner configuration from the given path.
 */
function scannerConfig(string $path): array
{
    $configs = (new RecursiveConfigs($path))->execute();

    return Arr::first($configs);
}
