<?php

namespace Leve\Cacheable\Commands;

use Illuminate\Console\Command;
use Leve\Cacheable\Facades\Cacheable;
use Leve\Cacheable\Index;
use Leve\Cacheable\Models\Group;
use Leve\Cacheable\Models\Model;

class FlushCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cacheable:flush {--drop-groups : remover grupos} {--drop-models : remover models}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'flush caches';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        $models = Cacheable::getModels();

        $bar = $this->output->createProgressBar(count($models));

        /**
         * @var string $class
         * @var  Index $index
         */
        foreach ($models as $class => $index) {
            $index->flush();

            $bar->advance();
        }

        $this->option('drop-groups') && Group::query()->delete();

        $this->option('drop-models') && Model::query()->delete();
    }
}
