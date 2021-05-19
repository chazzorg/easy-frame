<?php

namespace App\Console;

use Symfony\Component\Console\Application;

class Kernel
{
    /**
     * 命令白名单,为空时默认加载全部
     *
     * @var array
     */
    protected $commands = [
        //'Test',
    ];

    /**
     * 命令黑名单
     *
     * @var array
     */
    protected $dont_alias = [
        //'Test',
    ];

    

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    public function run(Type $var = null)
    {
        $this->load(__DIR__.'/Commands');
        $this->application->run();
    }

    protected function load($dir='')
    {
        if ($handle = opendir($dir)){
            while (($files = readdir($handle)) !== false){
                $fileName=pathinfo($files);
                if ($files != "." && isset($fileName['extension']) && isset($fileName['filename'])){
                    if($fileName['extension'] == 'php'){
                        $className="App\Console\Commands\\".$fileName['filename'];
                        if(in_array($fileName['filename'],$this->dont_alias) || ($this->commands && !in_array($fileName['filename'],$this->commands)) ){
                            continue;
                        }
                        $this->application->add(new $className());
                    }
                }
            }
            closedir($handle);
        }
    }
}
