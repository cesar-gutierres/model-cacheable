<?php

namespace Leve\Cacheable\Commands;

use Illuminate\Console\Command;
use Leve\Cacheable\Facades\Cacheable;
use Leve\Cacheable\Index;
use Leve\Cacheable\Models\Group;
use Leve\Cacheable\Models\Model;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cacheable:install {--flush : resetar base}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'instalar models';

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

        if ($this->option('flush')) {
            $this->call('cacheable:flush --drop-groups');
        }

        $models = Cacheable::getModels();

        $bar = $this->output->createProgressBar(count($models));

        /**
         * @var string $class
         * @var  Index $index
         */
        foreach ($models as $class => $index) {
            $chunk = $index->getOption('chunck', 10);

            // registrar grupo global
            $class::crape(true);

            app($class)
                ->query()
                ->chunk($chunk, fn($row) => $row->each->cached());

            $bar->advance();
        }
    }
}
