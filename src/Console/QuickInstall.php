<?php

namespace YangJiSen\QuickPassport\Console;

use Illuminate\Console\Command;

class QuickInstall extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:quick-install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Quick Install And Config Laravel Passport';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $this->call('passport:install');
        $this->call('passport:env-client');
    }
}
