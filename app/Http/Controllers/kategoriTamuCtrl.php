<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Validator;
use Alert;
use Carbon\Carbon;
class kategoriTamuCtrl extends Controller
{
    //

    public $user_handle;

    public function __construct(){
            $this->middleware(function($req,$next){
                $this->user_handle=Auth::User();
                return $next($req);
            });

                
    }

    public function index(Request $request){

    	$data=DB::table('master_ketegori as k')
    	->where([
    		['k.nama','like',('%'.$request->q.'%')],
    		
    		['k.deleted_at','=',null]

    	])
        ->orWhere([
            ['k.deskripsi','like',('%'.$request->q.'%')],
            ['k.deleted_at','=',null]
            
        ])
        ->orderBy('id','desc')->paginate(10);
    	$data->appends(['q'=>$request->q]);

    	return view('admin.kategori.index')->with(['data'=>$data,'req'=>$request]);
    }


    public function tambah(){
    	return view('admin.kategori.tambah');
    }

    public function store(Request $request){

        $valid=Validator::make($request->all(),[
            'nama'=>'string|required|unique:master_ketegori,nama',
            'deskripsi'=>'string|nullable'
        ]);

        if($valid->fails()){
            Alert::error('Gagal',$valid->errors()->first());
            return back()->withInput();
        }

        $data=DB::table('master_ketegori')->insert([
            'nama'=>$request->nama,
            'deskripsi'=>$request->deskripsi,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now(),
            'user_id_created'=>$this->user_handle->id,
            'user_id_updated'=>$this->user_handle->id,
        ]);

        if($data){
            Alert::success('Berhasil','Kategori Tamu ditambahkan');
        }

        return back();
    }
}
