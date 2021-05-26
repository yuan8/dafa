<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Storage;
use Str;

class StoreConfig extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myweb:update {key} {tag} {tag_up} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
    public function handle()
    {

        $key=$this->argument('key');
        $tag=$this->argument('tag');
        $tag=strtoupper(Str::slug($tag));
        $tag_up=strtoupper(Str::slug($this->argument('tag_up')));
        $name=$this->argument('name');
        $name=str_replace('0*0space', ' ', $name);
        $val=config('web_config.'.$key)??[];
        foreach ($val as $key => $v) {
            if($v['tag']==$tag){
                $val[$key]=[
                    'tag'=>$tag_up,
                    'name'=>$name
                ];
                $del=true;

            }
        }

        config(['web_config.'.$key=>$val]);

        $set=config('web_config');
        $set='<?php
        
        return '.(var_export($set,true));
        $set=(trim($set)).';';

        Storage::disk('config')->put('web_config.php',$set);

        return 1;
    }
}
