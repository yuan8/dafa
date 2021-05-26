<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Validator;
use Str;
use Alert;
class BagianCtrl extends Controller
{
    //


    public function index(Request $request){
    	$data_seting=config('web_config.tujuan_tamu')??[];
    	return view('admin.bagian.index')->with(['data'=>$data_seting]);
    }

     public function create(Request $request){
    	return view('admin.bagian.create');
    }

   

    public function edit($tag,$slug,Request $request){
    	$val=config('web_config.tujuan_tamu')??[];
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
    		return view('admin.bagian.update')->with(['data'=>$data]);

    	}else{
    		return abort(404);
    	}
    }


     public function update($tag,$slug,Request $request){

     	$valid=Validator::make($request->all(),[
    		'tag'=>'string|required',
    		'name'=>'string|required'
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
    			$val[$key]=[
    				'tag'=>$tag,
    				'name'=>$request->name
    			];
    			$del=true;

    		}
    	}

    	config(['web_config.tujuan_tamu'=>$val]);
        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';
        
        $myfile = fopen(app_path('../config/web_config.php'), "w") or die("Unable to open file!");
        fwrite($myfile, $set);
        fclose($myfile);
        if($del){
        	Alert::success('Berhasil','Berhasil Menghapus Tujuan Bagian Tamu');
        }else{
       		 Alert::success('Gagal','Gagal Menghapus Tujuan Bagian Tamu');

        }

        return back();


    }

    public function delete($tag,$slug,Request $request){
    	$valid=Validator::make($request->all(),[
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

    	config(['web_config.tujuan_tamu'=>$val]);
        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';
        
        $myfile = fopen(app_path('../config/web_config.php'), "w") or die("Unable to open file!");
        fwrite($myfile, $set);
        fclose($myfile);
        if($del){
        	Alert::success('Berhasil','Berhasil Menghapus Tujuan Bagian Tamu');
        }else{
       		 Alert::success('Gagal','Gagal Menghapus Tujuan Bagian Tamu');

        }

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

    	config(['web_config.tujuan_tamu'=>$val]);
        $set=config('web_config');
        $set='<?php
        
		return '.(var_export($set,true));
        $set=(trim($set)).';';
        
        $myfile = fopen(app_path('../config/web_config.php'), "w") or die("Unable to open file!");
        fwrite($myfile, $set);
        fclose($myfile);

        Alert::success('Berhasil','Berhasil Menambahkan Tujuan Bagian Tamu');
        return back();

    }
}
