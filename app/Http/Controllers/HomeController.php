<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;
use DB;
use Alert;
use Storage;
class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function gate_receiver($fingerprint,Request $request){
        return view('gate.receiver')->with(['fingerprint'=>$fingerprint]);


    }


    public function batalkan($tamu_id){
        $data=DB::table('log_tamu as log')
        ->join('tamu as v','v.id','=','log.tamu_id')
        ->where('log.id',$tamu_id)
        ->selectRaw("log.*,v.nama")
        ->whereNull('log.gate_checkin')
        ->whereNull('log.gate_checkout')
        ->first();

        if($data){
            $U=Auth::User();

            $data=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','=','log.tamu_id')
            ->where('log.id',$tamu_id)
            ->selectRaw("log.*,v.nama")
            ->whereNull('log.gate_checkin')
            ->whereNull('log.gate_checkout')->update([
                'gate_out_handle'=>$U->id,
                'gate_checkout'=>Carbon::now(),
                'gate_checkin'=>Carbon::now(),
                'gate_handle'=>$U->id,
                'checkout_from_gate'=>false
            ]);
            if($data){
            Alert::success('Berhasil','Berhasil Membatalkan kujungan');

            }


        }else{
            Alert::error('Gagal','Gagal Membatalkan kujungan');
        }

        return back();


    }



    public function daftar_tamu(Request $request){

    }


    public function gate_check_out($id_log,$slug,Request $request){

        $U=Auth::User();
            $tamu=DB::table('log_tamu')
            ->whereRaw("(id=".$id_log." and  gate_checkout is null)")
            ->first();
            if($tamu){
                $data=[
                'nama'=>$request->nama,
                ];
                if($request->alamat){
                    $data['alamat']=$request->alamat;
                }
            
                if($request->nomer_telpon){
                    $data['nomer_telpon']=$request->nomer_telpon;
                }
                if($request->jenis_kelamin!=null){
                    $data['jenis_kelamin']=$request->jenis_kelamin??false;
                }
                if($request->tempat_lahir){
                    $data['tempat_lahir']=$request->tempat_lahir;
                }
                 if($request->tanggal_lahir){
                    $data['tanggal_lahir']=$request->tanggal_lahir;
                }
                
                if($request->agama){
                    $data['agama']=$request->agama;
                }
                
                if($request->pekerjaan){
                    $data['pekerjaan']=$request->pekerjaan;
                }

                if($request->golongan_darah){
                    $data['golongan_darah']=$request->golongan_darah;
                }

                $path_foto=null;
                 if($request->foto_file){
                    $path_foto=Storage::put('public/indentity/id-'.($tamu?$tamu->tamu_id:'cache').'/foto',$request->foto_file);
                    $path_foto=Storage::url($path_foto);
                }

                if($path_foto){
                    $data['foto']=$path_foto;
                }
                $data['updated_at']=Carbon::now();

                DB::table('tamu')->where('id',$tamu->tamu_id)->update($data);

                $tamu=DB::table('tamu')->where('id',$tamu->tamu_id)->first();

                $check_id=DB::table('identity_tamu as ind')
                ->where('tamu_id',$tamu->id)
                ->where('jenis_identity',$request->jenis_identity)
                ->first();


                $path_identity=null;
               if($check_id){
                    if($check_id->tamu_id!=$tamu->id){
                        $check_id=null;
                    }else{
                        $path_identity=$check_id->path_identity;
                    }
                }

                if($request->file){
                    $path_identity=Storage::put('public/indentity/id-'.($tamu?$tamu->id:'cache').'/'.$request->jenis_identity,$request->file);
                    $path_identity=Storage::url($path_identity);
                }

                if(!$check_id){
                    $check_id=DB::table('identity_tamu')->insertGetId([
                        'tamu_id'=>$tamu->id,
                        'identity_number'=>$request->no_identity,
                        'jenis_identity'=>$request->jenis_identity,
                        'path_identity'=>$path_identity,
                        'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                    ]);
                }else{

                    DB::table('identity_tamu')->where('id',$check_id->id)->update([
                        'identity_number'=>$request->no_identity,
                        'jenis_identity'=>$request->jenis_identity,
                        'path_identity'=>$path_identity,

                        'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                    ]);



                }

                $day=Carbon::now()->addDays(-3)->startOfDay();


                $log_tamu_record=DB::table('log_tamu as log')->where([
                    'tamu_id'=>$tamu->id,
                    ])
                   ->whereNull('gate_checkout')
                   ->where('id',$id_log)
                   ->where('provos_checkin','>',$day)->first();
                   $log_tamu=null;

                if($log_tamu_record){


                        $log_tamu=DB::table('log_tamu')->where('id',$log_tamu_record->id)->update([
                            'gate_checkout'=>Carbon::now(),
                            'jenis_id'=>$request->jenis_identity,
                            'gate_out_handle'=>$U->id,
                            'checkout_from_gate'=>true,
                            'keperluan'=>$request->keperluan,
                            'instansi'=>$request->instansi,
                            'kategori_tamu'=>$request->kategori_tamu,
                            'tujuan'=>json_encode($request->tujuan??[]),
                        ]);

                        if($log_tamu){
                            if(is_numeric($log_tamu)){
                                if(config('web_config.broadcast_network')??false)
                                    broadcast(new \App\Events\GateCheckin([
                                        'tamu_id'=>$log_tamu_record->tamu_id,
                                        'log_id'=>$log_tamu_record->id,
                                    ]));
                                }
                       }
               }else{
                 Alert::error('Gagal','Tamu Telah Berkunjung Hari Ini dan Belum Menyelesaikan Kunjunganya');
                 return back()->withInput();

               }

               if($log_tamu){
                 Alert::success('Berhasil','Berhasil Mengubah Status Tamu - Keluar');
                  return redirect()->route('g.index');

               }else{
                Alert::error('Gagal','Data Tidak Tersedia');

                return back()->withInput();


               }

            }else{
                Alert::error('Gagal','Data Tidak Tersedia');
                return redirect()->route('g.index');
            }

            

    }


    public function gate_out($id_log,$slug,Request $request){
        $fingerprint=$request->fingerprint;
        $day=Carbon::now()->addDays(-3)->startOfDay();
        $data_record=DB::table('log_tamu as log')
        ->join('tamu as v','v.id','=','log.tamu_id')
        ->join('identity_tamu as ind',
        [
            ['ind.tamu_id','=','log.tamu_id'],
            ['ind.jenis_identity','=','log.jenis_id']
        ])
        ->selectRaw("
            ind.jenis_identity,
            ind.identity_number as no_identity,
            ind.path_identity,
            ind.berlaku_hingga,
            v.id as id_tamu,
            v.nama,
            v.alamat,
            v.foto,
            v.agama,
            v.golongan_darah,
            v.pekerjaan,
            v.nomer_telpon,
            v.tempat_lahir,
            v.tanggal_lahir,
            v.jenis_kelamin,
            log.created_at,
            log.id,
            log.updated_at,
            log.keperluan,
            log.tujuan,
            log.kategori_tamu,
            log.instansi


        ")
        ->where('log.provos_checkin','>=',$day)
        ->where('log.id','=',$id_log)
        ->where('log.gate_checkout','=',null)
        ->orderBy('log.provos_checkin','desc')
        ->first();






        if($data_record){
            return view('gate.out')->with([
                'data'=>$data_record,
                'fingerprint'=>$fingerprint,
                'req'=>$request,
            ]);

        }else{
            return abort(404);
        }


    }



    public function provos_submit(Request $request){
        $jenis_identity=collect(config('web_config.identity_list'))->pluck('tag');
        $valid=Validator::make($request->all(),[
            'no_identity'=>'required',
            'jenis_kelamin'=>'required|numeric',
            'nama'=>'required',
            'nomer_telpon'=>'required|min:10',
            'kategori_tamu'=>'required|string',
            'keperluan'=>'required|string',
            'tujuan'=>'required|array',
            'jenis_identity'=>'required|in:'.implode(',', $jenis_identity->toArray()),

        ]);


        if($valid->fails()){
            Alert::error('Error',$valid->errors()->first());
            return back()->withInput();
        }


        $U=Auth::User();
        $day=Carbon::now()->startOfDay();

        $check_tamu=
        DB::table('tamu as ind')
        ->where('nomer_telpon','like',"%".$request->nomer_telpon.'%')
        ->first();

        


        $check_id=DB::table('identity_tamu as ind')
        ->where('identity_number',$request->no_identity)
        ->where('jenis_identity',$request->jenis_identity)
        ->first();










        if(!$check_tamu){
            $data=[
                'nama'=>$request->nama,
            ];
            if($request->alamat){
                $data['alamat']=$request->alamat;
            }
        
            if($request->nomer_telpon){
                $data['nomer_telpon']=$request->nomer_telpon;
            }
            if($request->jenis_kelamin){
                $data['jenis_kelamin']=$request->jenis_kelamin;
            }
            if($request->tempat_lahir){
                $data['tempat_lahir']=$request->tempat_lahir;
            }
             if($request->tanggal_lahir){
                $data['tanggal_lahir']=$request->tanggal_lahir;
            }
            
            if($request->agama){
                $data['agama']=$request->agama;
            }
            
            if($request->pekerjaan){
                $data['pekerjaan']=$request->pekerjaan;
            }

            if($request->golongan_darah){
                $data['golongan_darah']=$request->golongan_darah;
            }




            $id_tamu=DB::table('tamu')->insertGetId($data);

            $check_tamu=
                DB::table('tamu as ind')
                ->where('id',$id_tamu)
                ->first();
        }

        $path_identity=null;

        if($check_id){
            if($check_id->tamu_id!=$check_tamu->id){
                $check_id=null;
                Alert::error('Gagal','Data Identitas Tamu Yang Telah Terekam Sebelumya Tidak dapat Digunakan Kembali untuk Tamu Berbeda');
                return back()->withInput();
            }else{
                $path_identity=$check_id->path_identity;
            }
        }

        if($request->file){
            $path_identity=Storage::put('public/indentity/id-'.($check_tamu?$check_tamu->id:'cache').'/'.$request->jenis_identity,$request->file);
            $path_identity=Storage::url($path_identity);
        }


        if(!$check_id){


                $check_id=DB::table('identity_tamu')->insertGetId([
                    'tamu_id'=>$check_tamu->id,
                    'identity_number'=>$request->no_identity,
                    'jenis_identity'=>$request->jenis_identity,
                    'path_identity'=>$path_identity,
                    'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                ]);

        }else{

                DB::table('identity_tamu')->where('id',$check_id->id)->update([
                    'identity_number'=>$request->no_identity,
                    'jenis_identity'=>$request->jenis_identity,
                    'path_identity'=>$path_identity,
                    'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                ]);

        }

       

       if($check_tamu && $check_id){
           $log_tamu=DB::table('log_tamu as log')->where([
            'tamu_id'=>$check_tamu->id,
           ])
           ->whereNull('gate_checkout')
           ->where('provos_checkin','>',$day)->first();

           if(!$log_tamu){
                    $log_tamu=DB::table('log_tamu')->insertGetId([
                        'provos_checkin'=>Carbon::now(),
                        'jenis_id'=>$request->jenis_identity,
                        'tamu_id'=>$check_tamu->id,
                        'provos_handle'=>$U->id,
                        'keperluan'=>$request->keperluan,
                        'instansi'=>$request->instansi,
                        'kategori_tamu'=>$request->kategori_tamu,
                        'tujuan'=>json_encode($request->tujuan??[]),

                    
                ]);

                if($log_tamu){
                    if(is_numeric($log_tamu)){
                        if(config('web_config.broadcast_network')??false)
                            broadcast(new \App\Events\ProvosCheckin([
                                'tamu_id'=>$check_tamu->id,
                                'log_id'=>$log_tamu,
                            ]));
                        }
               }
           }else{
             Alert::error('Gagal','Tamu Telah Berkunjung Hari Ini dan Belum Menyelesaikan Kunjunganya');

             return back()->withInput();

           }

           
           if($log_tamu){
             Alert::success('Berhasil','Berhasil Menambahkan Tamu');
           }


           return redirect()->route('g.index');
       }
        
        
        // $valid=Validator::make($request->all(),[
        //     ''=

        // ]);
    }


    public function gate_index(Request $request){
        $day=Carbon::now()->startOfDay();
        $day_last=Carbon::now()->endOfDay();

        if($request->date){
            $last_date=Carbon::now()->addDays(-3)->endOfDay();
            
            $day=Carbon::parse($request->date)->startOfDay();

             $day_last=Carbon::parse($request->date)->endOfDay();
             if($last_date->gt($day_last)){
                Alert::error('','Anda tidak dapat meilihat data melebihi '.$last_date->format('d F Y'));
                return redirect()->route('g.index');
             }

        }



        $checkin='PROVOS';
        if($request->status){
            $checkin=$request->status;

        }

        $log_tamu=[];
        switch($checkin){
            case 'PROVOS':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','=','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                ->where('log.provos_checkin','>=',$day)
                ->where('log.provos_checkin','<=',$day_last)
                ->where('log.gate_checkin','=',null)
                ->where('log.gate_checkout','=',null)

                ->orderBy('log.provos_checkin','desc')
                ->get();
            break;
            case 'GATE_CHECKIN':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                ->where('log.provos_checkin','>=',$day)
                ->where('log.provos_checkin','<=',$day_last)
                ->where('log.gate_checkout','=',null)
                ->orderBy('log.provos_checkin','desc')
                ->get();
            break;
            case 'GATE_CHECKOUT':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                ->where('log.provos_checkin','>=',$day)
                ->where('log.provos_checkin','<=',$day_last)
                ->orderBy('log.provos_checkin','desc')
                ->get();
            break;
        }
        
        $fingerprint=$request->fingerprint();
        return view('gate.index')->with([
            'data_visitor'=>$log_tamu,
            'fingerprint'=>$fingerprint,
            'req'=>$request,
            'active_h'=>$day_last,
            'status'=>$checkin
        ]);
    }

    public function gate_check_in($id_log,$slug,Request $request){
            $U=Auth::User();
            $tamu=DB::table('log_tamu')
            ->whereRaw("(id=".$id_log." and  gate_checkout is null and gate_checkin is null)")
            ->first();
            if($tamu){
                $data=[
                'nama'=>$request->nama,
                ];
                if($request->alamat){
                    $data['alamat']=$request->alamat;
                }
            
                if($request->nomer_telpon){
                    $data['nomer_telpon']=$request->nomer_telpon;
                }
                if($request->jenis_kelamin){
                    $data['jenis_kelamin']=$request->jenis_kelamin;
                }
                if($request->tempat_lahir){
                    $data['tempat_lahir']=$request->tempat_lahir;
                }
                 if($request->tanggal_lahir){
                    $data['tanggal_lahir']=$request->tanggal_lahir;
                }
                
                if($request->agama){
                    $data['agama']=$request->agama;
                }
                
                if($request->pekerjaan){
                    $data['pekerjaan']=$request->pekerjaan;
                }

                if($request->golongan_darah){
                    $data['golongan_darah']=$request->golongan_darah;
                }

                $path_foto=null;
                 if($request->foto_file){
                    $path_foto=Storage::put('public/indentity/id-'.($tamu?$tamu->tamu_id:'cache').'/foto',$request->foto_file);
                    $path_foto=Storage::url($path_foto);
                }

                if($path_foto){
                    $data['foto']=$path_foto;
                }


                $data['updated_at']=Carbon::now();

                DB::table('tamu')->where('id',$tamu->tamu_id)->update($data);

                $tamu=DB::table('tamu')->where('id',$tamu->tamu_id)->first();

                $check_id=DB::table('identity_tamu as ind')
                ->where('tamu_id',$tamu->id)
                ->where('jenis_identity',$request->jenis_identity)
                ->first();


                $path_identity=null;
               if($check_id){
                    if($check_id->tamu_id!=$tamu->id){
                        $check_id=null;
                    }else{
                        $path_identity=$check_id->path_identity;
                    }
                }

                if($request->file){
                    $path_identity=Storage::put('public/indentity/id-'.($tamu?$tamu->id:'cache').'/'.$request->jenis_identity,$request->file);
                    $path_identity=Storage::url($path_identity);
                }




                if(!$check_id){
                    $check_id=DB::table('identity_tamu')->insertGetId([
                        'tamu_id'=>$tamu->id,
                        'identity_number'=>$request->no_identity,
                        'jenis_identity'=>$request->jenis_identity,
                        'path_identity'=>$path_identity,
                        'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                    ]);
                }else{

                    DB::table('identity_tamu')->where('id',$check_id->id)->update([
                        'identity_number'=>$request->no_identity,
                        'jenis_identity'=>$request->jenis_identity,
                        'path_identity'=>$path_identity,
                        'berlaku_hingga'=>isset($request->berlaku_hingga)?$request->berlaku_hingga:null,
                    ]);



                }

                $day=Carbon::now()->addDays(-3)->startOfDay();


                $log_tamu_record=DB::table('log_tamu as log')->where([
                    'tamu_id'=>$tamu->id,
                    ])
                   ->whereNull('gate_checkin')
                   ->where('id',$id_log)
                   ->where('provos_checkin','>',$day)->first();
                   $log_tamu=null;

                if($log_tamu_record){


                        $log_tamu=DB::table('log_tamu')->where('id',$log_tamu_record->id)->update([
                            'gate_checkin'=>Carbon::now(),
                            'jenis_id'=>$request->jenis_identity,
                            'gate_handle'=>$U->id,
                            'keperluan'=>$request->keperluan,
                            'instansi'=>$request->instansi,
                            'kategori_tamu'=>$request->kategori_tamu,
                            'tujuan'=>json_encode($request->tujuan??[]),
                        ]);

                        if($log_tamu){
                            if(is_numeric($log_tamu)){
                                if(config('web_config.broadcast_network')??false)
                                    broadcast(new \App\Events\GateCheckin([
                                        'tamu_id'=>$log_tamu_record->tamu_id,
                                        'log_id'=>$log_tamu_record->id,
                                    ]));
                                }
                       }
               }else{
                 Alert::error('Gagal','Tamu Telah Berkunjung Hari Ini dan Belum Menyelesaikan Kunjunganya');
                 return back()->withInput();

               }

               if($log_tamu){
                 Alert::success('Berhasil','Berhasil Mengubah Status Tamu - Masuk');
                  return redirect()->route('g.index');

               }else{
                Alert::error('Gagal','Data Tidak Tersedia');

                return back()->withInput();


               }

            }else{
                Alert::error('Gagal','Data Tidak Tersedia');
                return redirect()->route('g.index');
            }

            


    }

    public function gate_input($id_log,$slug,Request $request){
        $fingerprint=$request->fingerprint;
        $day=Carbon::now()->addDays(-3)->startOfDay();
        $data_record=DB::table('log_tamu as log')
        ->join('tamu as v','v.id','=','log.tamu_id')
        ->join('identity_tamu as ind',
        [
            ['ind.tamu_id','=','log.tamu_id'],
            ['ind.jenis_identity','=','log.jenis_id']
        ])
        ->selectRaw("
            ind.jenis_identity,
            ind.identity_number as no_identity,
            ind.path_identity,
            ind.berlaku_hingga,
            v.id as id_tamu,
            v.nama,
            v.alamat,
            v.foto,
            v.agama,
            v.golongan_darah,
            v.pekerjaan,
            v.nomer_telpon,
            v.tempat_lahir,
            v.tanggal_lahir,
            v.jenis_kelamin,
            log.created_at,
            log.id,
            log.updated_at,
            log.keperluan,
            log.tujuan,
            log.kategori_tamu,
            log.instansi


        ")
        ->where('log.provos_checkin','>=',$day)
        ->where('log.id','=',$id_log)
        ->where('log.gate_checkin','=',null)
        ->orderBy('log.provos_checkin','desc')
        ->first();
        // dd($data_record);



        if($data_record){
            return view('gate.input')->with([
                'data'=>$data_record,
                'fingerprint'=>$fingerprint,
                'req'=>$request,
            ]);

        }else{
            return abort(404);
        }

    }

    public function provos_index(Request $request){
        $fingerprint=$request->fingerprint();
        
        return view('provos.index')->with(['fingerprint'=>$fingerprint]);
    }

    public function provos_input(Request $request){
        $fingerprint=$request->fingerprint();
        
        return view('provos.input')->with(['fingerprint'=>$fingerprint]);
    }

    public function provos_receiver($fingerprint,Request $request){
        return view('provos.receiver')->with(['fingerprint'=>$fingerprint]);
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $U=Auth::User();
        if($U->role==2){
            return redirect()->route('p.input');
        }

        $fingerprint=$request->fingerprint();
        return view('home')->with(['fingerprint'=>$fingerprint]);
    }
}
