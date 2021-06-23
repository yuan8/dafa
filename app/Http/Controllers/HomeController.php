<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use Validator;
use Carbon\Carbon;
use DB;
use Alert;
use Storage;
use CV;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
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

    static function generate_id($pre='T'){
        do {
            $token=uniqid($pre);
            $token=strtoupper($token);
            $tamu=DB::table('tamu')->where('string_id',$token)->first();
            # code...
        } while ($tamu!=null);

        return $token;

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
           $jenis_identity=collect(config('web_config.identity_list'))->pluck('tag');
            $request['tujuan']=CV::build_from_options(json_decode($request->tujuan??'[]'));

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
                Alert::error('',$valid->errors()->first());
                return back()->withInput();
            }

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
                }else if($request->file_foto_cam){

                    if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                        $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);

                        $data_foto = base64_decode($data_foto);
                        $path_foto=Storage::put('public/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png',$data_foto);

                        $path_foto='/storage/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png';


                    }
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
                   ->where('gate_checkin','>',$day)->first();
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
        ->where('log.gate_checkin','>=',$day)
        ->where('log.id','=',$id_log)
        ->where('log.gate_checkout','=',null)
        ->orderBy('log.gate_checkin','desc')
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

        $request['tujuan']=CV::build_from_options(json_decode($request->tujuan??'[]'));



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

        if($check_tamu){
            if(!$check_tamu->izin_akses_masuk){
                Alert::error('Gagal','Tamu Ini Tidak Di Izinkan Masuk');
                return back();
            }

            DB::table('tamu')->where('id',$check_tamu->id)->update($data);
            $check_tamu=
                DB::table('tamu as ind')
                ->where('id',$check_tamu->id)
                ->first();
        }



        if(!$check_tamu){
            // JSKJSKJSK
            $token=static::generate_id('T');
            $data['string_id']=$token;


            $id_tamu=DB::table('tamu')->insertGetId($data);
            $check_tamu=
                DB::table('tamu as ind')
                ->where('id',$id_tamu)
                ->first();

        }

          $path_foto=null;
         if($request->foto_file){
            $path_foto=Storage::put('public/indentity/id-'.($tamu?$tamu->tamu_id:'cache').'/foto',$request->foto_file);
            $path_foto=Storage::url($path_foto);
            }else if($request->file_foto_cam){

            if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);

                $data_foto = base64_decode($data_foto);
                $path_foto=Storage::put('public/indentity/id-'.
                    ($check_tamu?$check_tamu->id:'cache').'/foto/def-cam-profile.png',$data_foto);

                $path_foto='/storage/indentity/id-'.
                    ($check_tamu?$check_tamu->id:'cache').'/foto/def-cam-profile.png';


            }
        }

        if($path_foto){
            $data_up=['foto'=>$path_foto];
            DB::table('tamu')->where('id',$check_tamu->id)->update($data_up);
        }




        $check_id=DB::table('identity_tamu as ind')
        ->where('tamu_id',$check_tamu->id)
        ->where('jenis_identity',$request->jenis_identity)
        ->first();

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
           ->where('gate_checkin','<=',$day)
           ->first();


           if(!$log_tamu){
                    $log_tamu=DB::table('log_tamu')->insertGetId([
                        'gate_checkin'=>Carbon::now(),
                        'jenis_id'=>$request->jenis_identity,
                        'tamu_id'=>$check_tamu->id,
                        'gate_handle'=>$U->id,
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
             DB::table('tamu')->where('id',$check_tamu->id)->update([
                 'def_keperluan'=>$request->keperluan,
                 'def_instansi'=>$request->instansi,
               ]);
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

        if($request->status=='REKAP' OR $request->status==NULL){
            $day=Carbon::parse($request->start_date)->startOfDay();
            $day_last=Carbon::parse($request->end_date)->endOfDay();

        }else{

            if($request->date){
                $last_date=Carbon::now()->addDays(-3)->endOfDay();

                $day=Carbon::parse($request->date)->startOfDay();

                 $day_last=Carbon::parse($request->date)->endOfDay();
                 if($last_date->gt($day_last)){
                    Alert::error('','Anda tidak dapat melihat data melebihi '.$last_date->format('d F Y'));
                    return redirect()->route('g.index');
                 }
            }
        }

        $where=[];

        if($request->q){
                $where[]="(v.nama like '%".$request->q."%')";
                $where[]="(log.tujuan like '%".$request->q."%')";
                $where[]="(log.instansi like '%".$request->q."%')";
                $where[]="(log.keperluan like '%".$request->q."%')";
                $where[]="(v.alamat like '%".$request->q."%')";
                $where[]="(replace(ind.identity_number,'-','') like '%".str_replace('-', '', $request->q)."%')";
                $where[]="(replace(v.nomer_telpon,'-','') like '%".str_replace('-', '', $request->q)."%')";
        }

        $checkin='GATE_CHECKIN';
        if($request->status){
            $checkin=$request->status;
        }


        $log_tamu=[];
        switch($checkin){
            case 'GATE_CHECKIN':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,

                    (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                    (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle
                    ")
                ->where('log.gate_checkin','>=',$day)
                ->where('log.gate_checkin','<=',$day_last)
                ->where('log.gate_checkout','=',null)
                ->orderBy('log.gate_checkin','desc')
                ->groupBy('v.id','log.id');
            break;
            case 'GATE_CHECKOUT':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,

                    (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                    (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle
                    ")
                ->where('log.gate_checkin','>=',$day)
                ->where('log.gate_checkin','<=',$day_last)
                ->where('log.gate_checkout','!=',null)
                ->orderBy('log.gate_checkin','desc')
                ->groupBy('v.id','log.id');
            break;

            case 'REKAP':
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,

                    (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                    (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle
                    ")
                ->where('log.gate_checkin','>=',$day)
                ->where('log.gate_checkin','<=',$day_last)
                ->orderBy('log.gate_checkin','desc')
                ->groupBy('v.id','log.id');
            break;

        }


        if($log_tamu){
            if(count($where)){
                $log_tamu=$log_tamu->whereRaw('('.implode(" OR ",$where).")");
            }

            $log_tamu=$log_tamu->get();

            $fingerprint=$request->fingerprint();

            $rekap_tamu=DB::table('log_tamu as log')
            ->where('log.gate_checkin','>=',$day)
            ->where('log.gate_checkin','<=',$day_last)
            ->selectRaw("count(distinct(case when (gate_checkout is null) then log.id else null end )) as count_in,count(distinct(case when (gate_checkout is not null) then log.id else null end) ) as count_out ")->first();

            return view('gate.index')->with([
                'data_visitor'=>$log_tamu,
                'date_start'=>$day,
                'date_end'=>$day_last,
                'fingerprint'=>$fingerprint,
                'req'=>$request,
                'rekap_tamu'=>$rekap_tamu,
                'active_h'=>$day_last,
                'status'=>$checkin
            ]);


        }


    }


    public function rekap(Request $request){

        $day=Carbon::parse($request->start_date??date('Y-m-d'))->startOfDay();
        $day_last=Carbon::parse($request->end_date??date('Y-m-d'))->endOfDay();

        $date_2=Carbon::parse($request->start_date??date('Y-m-d'))->format('d F Y');

        $where=[];
        if($request->q){
                $where[]="(v.nama like '%".$request->q."%')";
                $where[]="(log.tujuan like '%".$request->q."%')";
                $where[]="(log.instansi like '%".$request->q."%')";
                $where[]="(log.keperluan like '%".$request->q."%')";
                $where[]="(v.alamat like '%".$request->q."%')";
                $where[]="(replace(ind.identity_number,'-','') like '%".str_replace('-', '', $request->q)."%')";
                $where[]="(replace(v.nomer_telpon,'-','') like '%".str_replace('-', '', $request->q)."%')";
        }

        $checkin='GATE_CHECKIN';
        if($request->status){
            $checkin=$request->status;
        }

        $where_def=[
            "log.gate_checkin >= '".$day."'" ,
            "log.gate_checkin <= '".$day_last."'" ,
        ];

        $where_def_rekap=[
            "log.gate_checkin >= '".$day."'" ,
            "log.gate_checkin <= '".$day_last."'" ,

         ];

       $where_def_rekap_out=[
            "log.gate_checkout >= '".$day."'" ,
            "log.gate_checkout <= '".$day_last."'" ,
        ];

       $where_def_rekap_in=[
            "log.gate_checkin >= '".$day."'" ,
            "log.gate_checkin <= '".$day_last."'" ,
            "log.gate_checkout is null" ,

        ];;

        $whereRaw=[];
        $whererawre_inher='';
        $whereRaw_rekap_in=[];
        $whereRaw_rekap_out=[];

        if($request->tujuan_json){
            $request->tujuan_json=json_decode($request->tujuan_json??'[]');
        }else{
            $request->tujuan_json=[];

        }

        if($request->tujuan_json){
            $request->tujuan=collect($request->tujuan_json)->pluck('code');

        }

        if(count($request->tujuan??[])){

            foreach ($request->tujuan??[] as $key => $value) {
                $where[]="log.tujuan like '%".$value."%'";
            }
        }



            if($checkin!='ALL'){
                switch ($checkin) {
                    case 'GATE_CHECKIN':
                        $where_def=[
                            "log.gate_checkin >= '".$day."'" ,
                            "log.gate_checkin <= '".$day_last."'" ,
                         ];

                        $where_def[]="log.gate_checkout is null ";

                        break;
                    case 'GATE_CHECKOUT':
                        $where_def=[
                            "log.gate_checkout >= '".$day."'" ,
                            "log.gate_checkout <= '".$day_last."'" ,
                         ];



                        break;

                    default:
                        # code...
                        break;
                }
            }

        if(count($where)){
                foreach ($where as $key => $w) {
                    $wr=$where_def;
                    $wr_r_in=$where_def_rekap_in;
                    $wr_r_out=$where_def_rekap_out;





                    $wr[]=$w;
                    $wr_r_in[]=$w;
                    $wr_r_out[]=$w;

                    $whereRaw[]='('.implode(') and (',$wr).')';
                    $whereRaw_rekap_in[]='('.implode(') and (',$wr_r_in).')';
                    $whereRaw_rekap_out[]='('.implode(') and (',$wr_r_out).')';



                    # code...
                }


        }else{
            $whereRaw[]=implode(' and ', $where_def);
            $whereRaw_rekap_in[]=implode(' and ', $where_def_rekap_in);
            $whereRaw_rekap_out[]=implode(' and ', $where_def_rekap_out);


        }

        $whereRaw_inher=$whereRaw;

        switch ($request->jenis_tamu) {
            case 'ALL':
                # code...
                break;

            case 'TAMU_KHUSUS':
                # code...
                foreach ($whereRaw as $key => $value) {
                    # code...
                    $whereRaw[$key].=' and (v.tamu_khusus =true)';
                }

                break;

            case 'TAMU':
                # code...
            foreach ($whereRaw as $key => $value) {
                    # code...
                    $whereRaw[$key].=' and (v.tamu_khusus =false)';
                }

                break;

            default:
                # code...
                break;
        }



          $log_tamu=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','log.tamu_id')
            ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
            ->selectRaw("
                v.id as id_tamu,
                v.nama,
                v.foto,
                v.tamu_khusus,
                v.jenis_tamu_khusus,
                ind.jenis_identity,
                ind.identity_number,
                log.id as id_log,
                log.created_at as log_created_at,
                log.gate_handle as id_gate_handle,
                log.gate_out_handle as id_gate_out_handle,
                log.keperluan as keperluan,
                log.tujuan as tujuan,
                log.kategori_tamu,
                log.instansi as instansi,

                log.gate_checkin as gate_checkin,
                log.gate_checkout as gate_checkout,
                (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle,
                (case when (log.gate_checkout is not null) then 1 else 0 end) as status_out
                ")
            ->whereRaw(implode(' or ', $whereRaw))
            ->orderBy('log.gate_checkin','desc')
            ->groupBy('v.id','log.id')
            ->get();


            $log_rekap_in=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','log.tamu_id')
            ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
              ->whereRaw(implode(' and ', $where_def_rekap))
            ->orderBy('log.gate_checkin','desc')
            ->groupBy('v.id','log.id')
            ->selectRaw('sum(case when ('.implode(' or ', $whereRaw_rekap_in).') then 1 else 0 end ) as count_data,sum(case when  (v.tamu_khusus=true and ('.implode(' or ', $whereRaw_rekap_in).')) then 1 else 0 end) as count_khusus,
                sum(case when (v.tamu_khusus=false and ('.implode(' or ', $whereRaw_rekap_in).')) then 1 else 0 end) as count_non_khusus
                ')
            ->first();

            if(!$log_rekap_in){
                $log_rekap_in=[
                    'count_data'=>0,
                    'count_khusus'=>0,
                    'count_non_khusus'=>0,

                ];
            }



            $log_rekap_out=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','log.tamu_id')
            ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
            ->whereRaw(implode(' and ', $where_def_rekap))
            ->orderBy('log.gate_checkin','desc')
            ->groupBy('log.id')
            ->selectRaw('sum(case when ('.implode(' or ', $whereRaw_rekap_out).') then 1 else 0 end ) as count_data,count(distinct(case when (v.tamu_khusus=true and  ('.implode(' or ', $whereRaw_rekap_out).')) then log.id else null end)) as count_khusus,
                sum(case when (v.tamu_khusus=false and ('.implode(' or ', $whereRaw_rekap_out).')) then 1 else 0 end) as count_non_khusus

                ')
            ->first();

            dd($whereRaw_rekap_out,$where_def_rekap);

             if(!$log_rekap_out){
                $log_rekap_out=[
                    'count_data'=>0,
                    'count_khusus'=>0,
                    'count_non_khusus'=>0,

                ];
            }




            if($request->v_export=='EXCEL'){

            }elseif($request->v_export=='PDF'){

                $dompdf = \App::make('dompdf.wrapper');
                // $dompdf->setBasePath(public_path('/'));
                $view=view('tamu.export_rekap')->with(
                    [
                    'data'=>$log_tamu,
                    'status'=>$checkin,
                    'day'=>$day,
                    'day_last'=>$day_last,
                    'req'=>$request,
                    'tujuan_json'=>is_array($request->tujuan_json)?$request->tujuan_json:json_decode($request->tujuan_json??'[]'),
                    'tujuan'=>$request->tujuan??[]

                ])->render();
                $dompdf->loadHtml($view);
                $dompdf->setPaper('A4', $request->jenis_table=='LENGKAP'?'landscape':'potrait');
                // $dompdf->render();
                return $dompdf->stream('REKAP-'.($checkin).'_'.($day).'-'.($day_last).'.pdf' ,array("Attachment" => false));

            }



        return view('gate.rekap')->with([
            'data'=>$log_tamu,
            'date_start'=>$day->format('Y-m-d'),
            'date_end'=>$day_last->format('Y-m-d'),
            'req'=>$request,
            'rekap'=>['count_in'=>$log_rekap_in,'count_out'=>$log_rekap_out],
            'status'=>$checkin,
            'date_2'=>$date_2,
            'tujuan_json'=>is_array($request->tujuan_json)?$request->tujuan_json:json_decode($request->tujuan_json??'[]'),
            'tujuan'=>$request->tujuan??[]
        ]);

    }


    static function rekap_export($data){

    }

    public function gate_check_in($id_log,$slug,Request $request){
           $jenis_identity=collect(config('web_config.identity_list'))->pluck('tag');
            $request['tujuan']=CV::build_from_options(json_decode($request->tujuan??'[]'));

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
                Alert::error('',$valid->errors()->first());
                return back()->withInput();
            }

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
                }else if($request->file_foto_cam){

                    if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                        $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);

                        $data_foto = base64_decode($data_foto);
                        $path_foto=Storage::put('public/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png',$data_foto);

                        $path_foto='/storage/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png';


                    }
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


        $fingerprint=$request->fingerprint();
        return view('home')->with(['fingerprint'=>$fingerprint]);
    }
