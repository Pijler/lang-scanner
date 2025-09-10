<?php

namespace App\Providers;

use App\Actions\Concerns\CheckScanner;
use App\Actions\Concerns\UpdateScanner;
use App\Actions\ElaborateSummary;
use App\Actions\Scanner;
use App\Commands\DefaultCommand;
use App\Contracts\PathsRepository;
use App\Output\ProgressOutput;
use App\Output\SummaryOutput;
use App\Project;
use App\Repositories\GitPathsRepository;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(PathsRepository::class, function () {
            return new GitPathsRepository(
                Project::path(),
            );
        });

        $this->app->bindMethod([DefaultCommand::class, 'handle'], function ($command) {
            return $command->handle(
                resolve(Scanner::class),
                resolve(ElaborateSummary::class)
            );
        });

        $this->app->singleton(Scanner::class, function () {
            return new Scanner(
                resolve(CheckScanner::class),
                resolve(UpdateScanner::class),
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
                new ProgressOutput(
                    resolve(InputInterface::class),
                    resolve(OutputInterface::class),
                ),
            );
        });

        $this->app->singleton(ElaborateSummary::class, function () {
            return new ElaborateSummary(
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
                new SummaryOutput(
                    resolve(InputInterface::class),
                    resolve(OutputInterface::class),
                ),
            );
        });

        $this->app->singleton(CheckScanner::class, function () {
            $input = resolve(InputInterface::class);

            return new CheckScanner(
                Project::paths($input),
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
                new ProgressOutput(
                    resolve(InputInterface::class),
                    resolve(OutputInterface::class),
                ),
            );
        });

        $this->app->singleton(UpdateScanner::class, function () {
            $input = resolve(InputInterface::class);

            return new UpdateScanner(
                Project::paths($input),
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
                new ProgressOutput(
                    resolve(InputInterface::class),
                    resolve(OutputInterface::class),
                ),
            );
        });
    }
}
