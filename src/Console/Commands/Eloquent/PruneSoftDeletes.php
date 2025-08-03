<?php

namespace Lucent\Console\Commands\Eloquent;

use Illuminate\Console\Command;

class PruneSoftDeletes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:prune-soft-deletes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prune outdated soft deleted model records.';

    /**
     * Execute the console command.
     */
    public function handle() {}
}
