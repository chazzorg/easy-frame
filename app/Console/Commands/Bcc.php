<?php

namespace App\Console\Commands;

use Chazz\Console\Commands;

class Bcc extends Commands{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name=true;

    /**
     * The console command opts.
     *
     * @var array
     */
    protected $opts=['user','id'];



    public function handle()
    {
        dd($this->arguments());
    }

}
