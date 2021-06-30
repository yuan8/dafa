<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Auth;
use Alert;
use App\Models\User;
use Validator;
use Str;
use Hash;
use Carbon\Carbon;
class UserCtrl extends Controller
{
    //

    public function ubah_password($id,Request $request){
    	$data=DB::table('users')->where('id',$id)->first();

    	if($data){

    		$valid=Validator::make($request->all(),[
    		'password'=>'required|string|min:8|confirmed'
	    	]);

	    	if($valid->fails()){
	    		Alert::error('Gagal',$valid->errors()->first());
	    		return back();
	    	}

	    	if($data->id==Auth::User()->id){
	    		if(!Hash::check($request->old_password, $data->password)){
	    			Alert::error('Gagal','Pasword Lama Tidak Sesuai');
	    			return back();
	    		}
	    	}

	    	$uu=DB::table('users')->where('id',$id)->update([
	    		'password'=>Hash::make($request->password)
	    	]);

	    	if($uu){
				Alert::success('Berhasil','Berhasil Merubah Password');

	    	}else{
		    	Alert::error('Gagal','Data Tidak Tersedia');

	    	}

	    	return back();


    	}else{
	    	Alert::error('Gagal','Data Tidak Tersedia');

    		return redirect()->route('a.u.index');
    	}
    	



    }

    public function update($id,$slug,Request $request){
    	$re=$request->all()??[];
		$re['username']=str_replace('-', '_', Str::slug($request->username));
		$data=DB::table('users')->where('id',$id)->first();

		if($data){
			$chek_uname=DB::table('users')
			->where('id','!=',$data->id)
			->where('username',$re['username'])->first();
			$valide=Validator::make($re,[
				'username'=>"required|string"
			]);

			if($valide->fails()){
				Alert::error('Gagal',$valide->errors()->first());
				return back();
			}

			if($chek_uname){
				Alert::error('Gagal','Username Telah Digunakan Sebelumnya');
				return back();
			}

			$chek_nrp=DB::table('users')
			->where('id','!=',$data->id)
			->where('nrp',$re['nrp'])->first();

			if($chek_nrp){
				Alert::error('Gagal','NRP Telah Digunakan Sebelumnya');
				return back();
			}

			
			$data_up=[
				'username'=>$re['username'],
				'jabatan'=>$request->jabatan,
				'pangkat'=>$request->pangkat,
				'name'=>$request->name,
				'updated_at'=>Carbon::now()
			];

			if($id!=Auth::user()->id){
				if($request->email){

					$data_up['email']=$request->email;
					$valide=Validator::make($data_up,[
						'email'=>"required|email"
					]);

					if($valide->fails()){
						Alert::error('Gagal',$valide->errors()->first());
						return back();
					}
					$chek=DB::table('users')->where('email',$request->email)
					->where('id','!=',$id)->first();
					if($chek){
						Alert::error('Gagal','Email - telah digunakan sebelumnya');
						return back();
					}
				}
				if($request->role){
					if(in_array($request->role, [0,1,2,3])){
						$data_up['role']=$request->role;

					}
				}
			}



			if($request->status){
				if($request->status=='NULL_VALUE'){
					$data_up['deleted_at']=null;
					$data_up['is_active']=false;
					
				}else{
					$data_up['deleted_at']=Carbon::now();
					$data_up['is_active']=true;
				}
			}

			$uup=DB::table('users')->where('id',$id)->update($data_up);
			if($uup){
				Alert::success('Berhasil','Berhasil Merubah Data');
				if(isset($data_up['deleted_at']) and $data_up['deleted_at']!=null){
					DB::table('sessions')->where('user_id',$id)->delete();
				}

			}else{
				Alert::error('Gagal','Data Tidak Tersedia');

			}

			return back();

		}else{
			Alert::error('Gagal','Data Tidak Tersedia');

			return redirect()->route('a.u.index');
		}
    }

	public function store(Request $request){
		$re=$request->all()??[];
		$re['username']=Str::slug($request->username);

		$valid=Validator::make($re,[
			'name'=>'string|required',
			'nrp'=>'string|required||unique:users,nrp',
			'username'=>'string|required|unique:users,username',
			'email'=>'string|required|unique:users,username',
			'role'=>'numeric|required|in:0,1,2,3',
			'password'=>'string|min:8|confirmed',
		]);



		if($valid->fails()){
			Alert::error('',$valid->errors()->first());
			return back()->withInput();
		}

		$user=User::create([
			'name'=>$request->name,
			'email'=>$request->email,
			'username'=>Str::slug($request->username),
			'role'=>$request->role,
			'nrp'=>$request->nrp,
			'pangkat'=>$request->pangkat,
			'jabatan'=>$request->jabatan,
			'password'=>Hash::make($request->nrp),
			'api_token'=>Hash::make($request->email),

		]);

		if($user){
			Alert::success('Berhasil','Berhasil Menambahkan User');

		}else{
			Alert::error('Gagal','Gagal Menambahkan User');

		}

		return back();
	}

    public function tambah(){
    	return view('admin.user.create')->with(['role'=>$this->role]);
    }

	public function edit($id,$slug){
		$u=DB::table('users')->where('id',$id)->first();

		if($u){
			return view('admin.user.edit')->with(['data'=>$u,'role'=>$this->role]);
		}
	}

    protected $role=[
    	1=>'SUPERADMIN',
    	2=>'GATE',
    	3=>'PROVOS GATE'
    ];

    public function index(Request $request){
    	$where=[];
    	$wheredef=[
    		"id <> ".Auth::User()->id,
    	];

    	$whereRaw=[];

    	if($request->status=='USER_TIDAK_AKTIF'){
    		$wheredef[]='deleted_at is not null';
    	}else{
    		
    		$wheredef[]="deleted_at is null";
    	}

    	if($request->request){
    		$where[]="email like '%".$request->q."%'";
    		$where[]="name like '%".$request->q."%'";
    		$where[]="jabatan like '%".$request->q."%'";
    	}

    	if(count($where)){
    		foreach ($where as $key => $value) {
    			$m=$wheredef;
    			$m[]=$value;
    			$whereRaw[]='('.implode(' and ',$m).')';
    		}
    	}else{
    		$whereRaw=$wheredef;

    	}

    	$data=DB::table('users')->whereRaw(implode(' or ',$whereRaw))->paginate(10);

    	$data->appends($request->all());

    	return view('admin.user.index')->with(['data'=>$data,'request'=>$request,'role'=>$this->role]);


    }
}
