<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Str;
use Alert;
use Storage;
use Session;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Session\SessionManager;
class BagianCtrl extends Controller
{
    //
    static function getData(){
        $load=json_decode(file_get_contents(app_path('SqlLite/tujuan_tamu.json')),true);
        $data_seting=$load??[];
        return $data_seting;
    }

    static function storeData($data){
        file_put_contents(app_path('SqlLite/tujuan_tamu.json'), json_encode($data??[],JSON_PRETTY_PRINT));
        $load=json_decode(file_get_contents(app_path('SqlLite/tujuan_tamu.json')),true);
        $data_seting=$load??[];
        return $data_seting;
    }

    static function sortData($data){
        $data_key=collect($data)->pluck('name')->toArray();
        foreach ($data_key as $key => $value) {
            $data_key[$key]=($value.'|________|'.$key);
        }
        array_multisort($data_key);
        $data_sort=[];
        foreach ($data_key as $key => $value) {
            $id=explode('|________|',$value)[1];
            $data_sort[]=$data[$id];
        }

        return $data_sort;

    }

    public static function index(Request $request){ 
    	$data_seting=static::getData();

        $data_seting=static::sortData($data_seting);
        


    	return view('admin.bagian.index')->with(['data'=>$data_seting,'url'=>$request->url()]);
    }


     public function create(Request $request){
    	return view('admin.bagian.create');
    }

   

    public function edit($tag,$slug,Request $request){
        Artisan::call('config:clear');
    	$path_render=(app_path('../config/web_config.php'));

	    $val = include($path_render);
	    $val=$val['tujuan_tamu']??[];
        static::storeData($val);

    	$valid=Validator::make(['tag'=>$tag],[
    		'tag'=>'string|required'
    	]);

    	if($valid->fails()){
    		Alert::error('Gagal',$valid->errors()->first());

    		return back()->withInput();
    	}


    	$tag=strtoupper(Str::slug($tag));

    	$data=[];
    
    	foreach ($val as $key => $v) {
    		if($v['tag']==$tag){
    			$data=$v;

    		}
    	}

    	if(count($data)){
    		if(Str::slug($data['name'])!=$slug){
    			if(Session::has('alert.config')){
	    			$json=json_decode(session('alert.config'));
	    			if($json->title=='Berhasil'){

	    				Alert::success($json->title,$json->text);
	    				return redirect()->route('a.b.ubah',['id'=>$tag,'slug'=>Str::slug($slug)]);
	    			}
	    		}

    		}
    		return view('admin.bagian.update')->with(['data'=>$data]);
    	}else{
    		if(Session::has('alert.config')){
    			$json=json_decode(session('alert.config'));
    			if($json->title=='Berhasil'){
    				Alert::success($json->title,$json->text);
    				return redirect()->route('a.b.ubah',['id'=>$tag,'slug'=>Str::slug($slug)]);
    			}
    		}
    		// $json=

    		return abort(404);
    	}
    }


     public function update($tag,$slug,Request $request){
        Artisan::call('config:clear');

     	$valid=Validator::make($request->all(),[
    		'tag'=>'string|required',
    		'name'=>'string|required'
    	]);

    	if($valid->fails()){
    		Alert::error('Gagal',$valid->errors()->first());

    		return back()->withInput();
    	}

    	$del=false;
    	$tag_up=strtoupper(Str::slug($request->tag));
    	$val=config('web_config.tujuan_tamu')??[];


    	foreach ($val as $key => $v) {
    		if($v['tag']==$tag){
    			$val[$key]=[
    				'tag'=>$tag_up,
    				'name'=>$request->name
    			];
    			$del=true;

    		}
    	}

        $val=static::sortData($val);

    	config(['web_config.tujuan_tamu'=>$val]);

        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';

        Storage::disk('config')->put('web_config.php',$set);
        // $art=Artisan::call("myweb:update tujuan_tamu ".$tag." ".$tag_up." ".str_replace(' ', '0*0space', $request->name));

        static::storeData($val);

        if($del){
        	Alert::success('Berhasil','Berhasil Mengubah Tujuan Bagian Tamu');
        }else{
       		Alert::error('Gagal','Gagal Mengubah Tujuan Bagian Tamu');

        }

        return redirect()->route('a.b.ubah',['id'=>$tag_up,'slug'=>Str::slug($request->name)]);

    }

    public function delete($tag,$slug,Request $request){
    	$valid=Validator::make(['tag'=>$tag],[
    		'tag'=>'string|required'
    	]);

    	if($valid->fails()){
    		Alert::error('Gagal',$valid->errors()->first());

    		return back()->withInput();
    	}

    	$del=false;
    	$tag=strtoupper(Str::slug($tag));

    	$val=config('web_config.tujuan_tamu')??[];
    	foreach ($val as $key => $v) {
    		if($v['tag']==$tag){
    			unset($val[$key]);
    			$del=true;

    		}
    	}

        $val=static::sortData($val);

    	config(['web_config.tujuan_tamu'=>$val]);
        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';
        
        $myfile = fopen(app_path('../config/web_config.php'), "w") or die("Unable to open file!");
        fwrite($myfile, $set);
        fclose($myfile);
        Artisan::call('config:clear');


        if($del){
            static::storeData($val);

        	Alert::success('Berhasil','Berhasil Menghapus Tujuan Bagian Tamu');
        }else{
       		Alert::error('Gagal','Gagal Menghapus Tujuan Bagian Tamu');
        }

        config(['web_config.tujuan_tamu'=>$val]);


        $r=$request;


        return back();


    }

    public function store(Request $request){
    	$valid=Validator::make($request->all(),[
    		'tag'=>'string|required',
    		'name'=>'string|required'
    	]);


    	if($valid->fails()){
    		Alert::error('Gagal',$valid->errors()->first());

    		return back()->withInput();
    	}


    	$val=config('web_config.tujuan_tamu')??[];
    	$col_tag=collect($val)->pluck('tag');
    	

    	$tag=strtoupper(Str::slug($request->tag));

    	if(in_array($tag,$col_tag->toArray())){
    		Alert::error('Gagal','Data Telah Ada Sebelumnya');
    		return back()->withInput();
    	}

    	$val[]=[
    		'tag'=>$tag,
    		'name'=>$request->name
    	];

        $val=static::sortData($val);

    	config(['web_config.tujuan_tamu'=>$val]);
        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';
        
        $myfile = fopen(app_path('../config/web_config.php'), "w") or die("Unable to open file!");
        fwrite($myfile, $set);
        fclose($myfile);

        static::storeData($val);

        Alert::success('Berhasil','Berhasil Menambahkan Tujuan Bagian Tamu');
        return redirect()->route('a.b.index');

    }
}
