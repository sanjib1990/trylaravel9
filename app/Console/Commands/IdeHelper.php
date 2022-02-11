<?php

namespace App\Console\Commands;

use Illuminate\Support\Str;
use Illuminate\Console\Command;

class IdeHelper extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'run:helper';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Runs All IDE Helper Commands';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->call("ide-helper:generate");
        $this->call("ide-helper:models", ["--nowrite" => true]);
        $this->call("ide-helper:meta");

        $this->output->text("!! 🚀 [DONE] 🚀 !!");
        return 0;
    }
}
