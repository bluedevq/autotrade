<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class Base extends Command
{
    protected $module = '';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'base';
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'Base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Base command';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        try {
            $this->_handle();
        } catch (\Exception $exception) {
            Log::error($exception);
        }
    }

    protected function _handle()
    {
    }
}
