<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ConfigVueProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }



    static function build_from_options($code){
        return collect((array)$code)->pluck('code')->toArray();
    }

    static function build_options($key_conf){
        $tujuan=(array)config('web_config.'.$key_conf)??[];
        
        $vue=[];
        foreach ($tujuan as $key => $value) {
            $vue[]=[
                'label'=>$value['name'],
                'code'=>$value['tag']
            ];
        }
        return $vue;
    }

    
    static function show_name($key_conf,$code){
        $tujuan=config('web_config.'.$key_conf)??[];

         foreach ($tujuan as $key => $value) {

            if($value['tag']==$code){
                return $value['name'];
            }
            
        }
    }

    static function build_from_array($key_conf,$codes=[]){
        $tujuan=config('web_config.'.$key_conf)??[];
        $vue=[];
        foreach ($tujuan as $key => $value) {
            if(in_array($value['tag'],$codes)){
                $vue[]=[
                'label'=>$value['name'],
                'code'=>$value['tag']
                ];
            }
        }
        return $vue;
    }
}
