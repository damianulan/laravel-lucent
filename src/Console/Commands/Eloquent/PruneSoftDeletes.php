<?php

namespace Lucent\Console\Commands\Eloquent;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\SoftDeletes;
use Lucent\Support\Traits\SoftDeletesPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Eloquent\Collection;

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
    public function handle()
    {
        try {
            $this->line('Pruning outdated soft deleted model records...');
            DB::beginTransaction();
            $classMap = require base_path('vendor/composer/autoload_classmap.php');
            $classMap = array_filter($classMap, function ($key) {
                return strpos($key, 'App') !== false && strpos($key, 'Models') !== false
                    && is_subclass_of($key, Model::class)
                    && class_uses_trait(SoftDeletes::class, $key)
                    && class_uses_trait(SoftDeletesPrunable::class, $key);
            }, ARRAY_FILTER_USE_KEY);

            if (!empty($classMap)) {
                $this->line('Found ' . count($classMap) . ' models with soft deletes traits.');
                $this->newLine();
                foreach (array_keys($classMap) as $class) {
                    $instance = new $class;
                    $table = $instance->getTable();
                    if (Schema::hasTable($table)) {
                        $class::prunableSoftDeletes()->chunk(200, function (Collection $collection) {
                            $collection->each(function (Model $model) {
                                $this->warn('Deleting ' . $model->getKey() . ' from ' . $model->getTable());
                                $model->forceDelete();
                            });
                        });
                    }
                }
            }
            DB::commit();
            $this->newLine();
            $this->info('Pruning soft deleted records completed successfully.');
        } catch (\Throwable $th) {
            $this->error("An error occurred while pruning outdated soft deleted records. Rolling back...");
            DB::rollBack();
            $this->error($th->getMessage());
        }
    }
}
