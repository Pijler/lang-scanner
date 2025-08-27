<?php

namespace App\Providers;

use App\Actions\ElaborateSummary;
use App\Actions\Scanner;
use App\Commands\DefaultCommand;
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
        $this->app->bindMethod([DefaultCommand::class, 'handle'], function ($command) {
            return $command->handle(
                resolve(Scanner::class),
                resolve(ElaborateSummary::class)
            );
        });

        $this->app->singleton(Scanner::class, function () {
            return new Scanner(
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
            );
        });

        $this->app->singleton(ElaborateSummary::class, function () {
            return new ElaborateSummary(
                resolve(InputInterface::class),
                resolve(OutputInterface::class),
            );
        });
    }
}
