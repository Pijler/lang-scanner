<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class DefaultCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'default';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan files and update translations';

    /**
     * The configuration of the command.
     *
     * @return void
     */
    protected function configure()
    {
        parent::configure();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // dd($this);
    }
}
