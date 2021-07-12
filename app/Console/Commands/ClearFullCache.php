<?php namespace App\Console\Commands;

use Illuminate\Cache\Console\ClearCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class ClearFullCache extends ClearCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'c:c';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Clear full cache - config - data - route - view';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear full cache - config - data - route - view';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $commands = [
                'cache:clear',
                'config:clear',
                'config:cache',
                'route:clear',
                'view:clear',
            ];
            foreach ($commands as $command) {
                Artisan::call($command);
            }
            File::deleteDirectory(storage_path() . '\framework\cache\data');
            $this->info('Cache all data, config, route, view successfully.');
        } catch (\Exception $exception) {

        }
    }
}