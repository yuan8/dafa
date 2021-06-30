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
                    $old_foto='/^data:image\/(\w+);base64,/'.base64_decode($request->foto_file);

                }else if($request->file_foto_cam){

                    if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                        $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);

                        $data_foto = base64_decode($data_foto);
                        $path_foto=Storage::put('public/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png',$data_foto);

                        $path_foto='/storage/indentity/id-'.
                            ($tamu?$tamu->tamu_id:'cache').'/foto/def-cam-profile.png';

                        $old_foto='/^data:image\/(\w+);base64,/'.base64_decode($request->file_foto_cam);

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
                 Alert::error('Gagal','Tamu Masih Di Dalam Gedung dan Belum Menyelesaikan Kunjunganya');
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
        ->leftJoin('identity_tamu as ind',
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
            log.instansi,
            log.nomer_kartu


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
        $old_foto=null;
        $data=[];



        $U=Auth::User();
        $day_start=Carbon::now()->startOfDay();

        $next_proccess=false;

        $path_foto=null;
        if($request->foto_file){
                $path_foto=Storage::put('public/foto-cache/'.Carbon::now()->format('d-m-y'),$request->foto_file);
                $path_foto=Storage::url($path_foto);
                $old_foto=asset($path_foto);

        }else if($request->file_foto_cam!='false'){
            if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                    $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);
                    $data_foto =base64_decode($data_foto);
                    $path_con='foto-cache/'.Carbon::now()->format('d-m-y').'/'.Carbon::now()->format('d-m-y-h-i-s-a').'_'.$U->id.'_'.rand(0,1000).'.png';

                    $path_foto=Storage::put('public/'.$path_con,$data_foto);
                    $path_foto='/storage/'.$path_con;
                    $old_foto=asset($path_foto);
            }

        }else if($request->foto){
                $path_foto=str_replace(url('storage'), '/storage', $request->foto);
                $old_foto=asset($path_foto);
        }

        $insert_identity=false;




        $valid=Validator::make($request->all(),[
            'no_identity'=>'required',
            // 'jenis_kelamin'=>'required|numeric',
            'nama'=>'required',
            // 'instansi'=>'required|string',
            'nomer_telpon'=>'required|min:10',
            // 'kategori_tamu'=>'required|string',
            // 'keperluan'=>'required|string',
            'tujuan'=>'required|array',
            'jenis_identity'=>'required|in:'.implode(',', $jenis_identity->toArray()),
        ]);


        if($valid->fails()){
            $next_proccess=false;

        }else{
            $next_proccess=true;
        }




        $data_log=[
            'gate_checkin'=>$day_start,
            'jenis_id'=>$request->jenis_identity,
            'jenis_identity'=>$request->jenis_identity,

            'no_identity'=>$request->no_identity,
            'gate_handle'=>$U->id,
            'tamu_id'=>null,
            'keperluan'=>$request->keperluan,
            'instansi'=>$request->instansi,
            'kategori_tamu'=>$request->kategori_tamu,
            'tujuan'=>json_encode($request->tujuan??[]),
        ];



        $data['nama']=$request->nama;
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



        if($next_proccess){

        }else{
            $data['old_foto']=$old_foto;
            $data['nomer_kartu']=$request->nomer_kartu;
            $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
            Alert::error('Gagal',$valid->errors()->first());
            $mergeInput=array_merge($data,$data_log);
            return back()->withInput($mergeInput);
        }


        $check_tamu=DB::table('tamu as ind')
            ->where('nomer_telpon','like',"%".$request->nomer_telpon.'%')
            ->first();


        $check_identity=DB::table('identity_tamu as idt')
        ->leftJoin('tamu as t','t.nomer_telpon','=',DB::raw("'".$data['nomer_telpon']."'"))
            ->where([
                ['idt.jenis_identity','=',$data_log['jenis_id']],
                ['idt.identity_number','=',$data_log['no_identity']]
        ])->selectRaw('t.*,idt.jenis_identity,idt.identity_number')->first();

        if($check_identity){
            if($check_identity->nomer_telpon!=$data['nomer_telpon']){
                $data['old_foto']=$old_foto;
                $data['nomer_kartu']=$request->nomer_kartu;
                $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
                Alert::error('Gagal','Identitas Tamu telah digunakan untuk tamu '.$check_identity->nama);
                $mergeInput=array_merge($data,$data_log);
                return back()->withInput($mergeInput);
            }



        }else{


            $insert_identity=true;
        }

        if($check_tamu){
            if(strtoupper(trim($check_tamu->nama))!=strtoupper(trim($data['nama']))){
                Alert::error('Gagal','Nomor Telpon digunakan untuk tamu '.$check_tamu->nama.' , Jika tamu ini sama silahkan melakukan editing terlebih dahulu pada menu master data tamu untuk Nomor Telepon / Nama');

                $data['old_foto']=$old_foto;
                $data['nomer_kartu']=$request->nomer_kartu;
                $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
                $mergeInput=array_merge($data,$data_log);
                return back()->withInput($mergeInput);
            }

            if(!$check_tamu->izin_akses_masuk){
                 Alert::error('Tamu Tidak Diperbolehkan Masuk',$check_tamu->keterangan_tolak_izin_akses);
                $data['old_foto']=$old_foto;
                $data['nomer_kartu']=$request->nomer_kartu;
                $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
                $mergeInput=array_merge($data,$data_log);
                return back()->withInput($mergeInput);


            }

            if(!$check_identity){
                $chek_jenis_id_tamu=DB::table('identity_tamu as idt')->where('tamu_id',$check_tamu->id)->where('jenis_identity',$data_log['jenis_identity'])->first();
                if($chek_jenis_id_tamu){
                    if($chek_jenis_id_tamu->identity_number!=$data_log['no_identity']){
                        Alert::error('Gagal','Nomor Pada Jenis Identitas '.$data_log['jenis_identity'].' tamu '.$check_tamu->nama.'  Tidak Sesuai, Mohon melakukan editing pada menu master data tamu jika memang ada perubahan nomor identitas');

                        $data['old_foto']=$old_foto;
                        $data['nomer_kartu']=$request->nomer_kartu;
                        $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
                        $mergeInput=array_merge($data,$data_log);
                        return back()->withInput($mergeInput);


                    }
                }else{
                    $insert_identity=true;
                }
            }


            $check_log=DB::table('log_tamu')
            ->where('tamu_id',$check_tamu->id)
            ->where('gate_checkout',null)
            ->where('gate_checkin','<=',$day_start->endOfDay())
            ->first();

            if($check_log){
                 Alert::error('Gagal',$check_tamu->nama.' Belum meyesaikan kunjungan pada '.$check_log->gate_checkin);
                $data['old_foto']=$old_foto;
                $data['nomer_kartu']=$request->nomer_kartu;

                $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
                $mergeInput=array_merge($data,$data_log);
                return back()->withInput($mergeInput);
            }

        }

        if(!$check_tamu){
            $data['def_keperluan']=$data_log['keperluan'];
            $data['def_instansi']=$data_log['instansi'];
            $data['def_tujuan']=$data_log['tujuan'];

            $id_tamu=DB::table('tamu')->insertGetId($data);
            $check_tamu=(object)['id'=>$id_tamu,'nama'=>$data['nama']];
        }


        if($path_foto){
            if(strpos($path_foto, '/foto-cache/')===true){
                $path_foto=str_replace('/storage', '/',$path_foto);
                $path_save='identity/id-'.$check_tamu->id.'/foto/def-cam-profile.png';
                Storage::move('public/'.$path_foto,'public/'.$path_save);
                $path_foto='/storage/'.$path_save;
            }

            $data['foto']=$path_foto;
            DB::table('tamu')->where('id',$check_tamu->id)->update($data);
        }

        $id_in=[
            'tamu_id'=>$check_tamu->id,
            'jenis_identity'=>$request->jenis_identity,
            'identity_number'=>$data_log['no_identity'],
        ];
        if($request->berlaku_hingga){
            $id_in['berlaku_hingga']=$request->berlaku_hingga;
        }

        if($request->file){
            $path_identity=Storage::put('public/indentity/id-'.($check_tamu?$check_tamu->id:'cache').'/'.$request->jenis_identity,$request->file);
            $path_identity=Storage::url($path_identity);
            $id_in['path_identity']=$path_identity;
        }else{
            $check_list_id=DB::table('identity_tamu')->where('tamu_id',$check_tamu->id)->count();
            // if(!$check_list_id){
            //     Alert::error('Gagal',$check_tamu->nama.' File Identitas Tidak tersedia');
            //     $data['old_foto']=$old_foto;
            //     $data['nomer_kartu']=$request->nomer_kartu;
            //     $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
            //     $mergeInput=array_merge($data,$data_log);
            //     return back()->withInput($mergeInput);
            // }
        }

        if($insert_identity){
            DB::table('identity_tamu')->insert($id_in);
        }

        DB::table('identity_tamu')->where([
            ['tamu_id','=',$check_tamu->id],
            ['jenis_identity','=',$request->jenis_identity],

        ])->update($id_in);


        $chek_nomer_kartu=DB::table('log_tamu')->where('gate_checkout',null)
        ->where('nomer_kartu',$request->nomer_kartu)->first();

        if($chek_nomer_kartu){
             $data['old_foto']=$old_foto;
            $data['nomer_kartu']=$request->nomer_kartu;
            $data_log['tujuan']=json_decode($data_log['tujuan']??'[]');
            Alert::error('Gagal','Nomer Kartu '.$request->nomer_kartu.' Terlah Digunakan Sebelumnya');
            $mergeInput=array_merge($data,$data_log);
            return back()->withInput($mergeInput);

        }

         $insert_log= DB::table('log_tamu')->insert([
            'gate_checkin'=>$day_start,
            'jenis_id'=>$request->jenis_identity,
            'gate_handle'=>$U->id,
            'gate_checkin'=>$day_start,
            'nomer_kartu'=>$request->nomer_kartu,
            'tamu_id'=>$check_tamu->id,
            'keperluan'=>$request->keperluan,
            'instansi'=>$request->instansi,
            'kategori_tamu'=>$request->kategori_tamu,
            'tujuan'=>json_encode($request->tujuan??[]),
        ]);

         if($insert_log){
            Alert::success('Berhasil','Tamu '.$check_tamu->nama.' berhasil diinput');
            return back();
         }



    }



    public function provos_submit_a(Request $request){
        $jenis_identity=collect(config('web_config.identity_list'))->pluck('tag');

        $request['tujuan']=CV::build_from_options(json_decode($request->tujuan??'[]'));

        $old_foto=null;

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

            $old_foto=url(($check_tamu->foto));

            DB::table('tamu')->where('id',$check_tamu->id)->update($data);
            $check_tamu=
                DB::table('tamu as ind')
                ->where('id',$check_tamu->id)
                ->first();
        }

          $path_foto=null;
         if($request->foto_file){
            $path_foto=Storage::put('public/foto-cache/'.Carbon::now()->format('d-m-y'),$request->foto_file);
            // $path_foto=Storage::put('public/indentity/id-'.($tamu?$tamu->tamu_id:'cache').'/foto',$request->foto_file);

            $path_foto=Storage::url($path_foto);
            $old_foto=asset($path_foto);

        }else if($request->file_foto_cam){

            if (preg_match('/^data:image\/(\w+);base64,/', $request->file_foto_cam)) {
                $data_foto = substr($request->file_foto_cam, strpos($request->file_foto_cam, ',') + 1);

                $data_foto =base64_decode($data_foto);
                $path_foto=Storage::put('public/foto-cache/'.Carbon::now()->format('d-m-y').'/'.
                    Carbon::now()->format('d-m-y-h-i-s-a').'.png',$data_foto);

                 $path_foto=Storage::url($path_foto);
                $old_foto=asset($path_foto);


                // $path_foto=Storage::put('public/indentity/id-'.
                //     ($check_tamu?$check_tamu->id:'cache').'/foto/def-cam-profile.png',$data_foto);

                // $path_foto='/storage/indentity/id-'.
                //     ($check_tamu?$check_tamu->id:'cache').'/foto/def-cam-profile.png';

                // $old_foto='/^data:image\/(\w+);base64,/'.base64_decode($request->file_foto_cam);

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
                return back()->withInput(['old_foto'=>$old_foto]);
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
           ->where('gate_checkin','<=',$day->endOfDay())
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
             $old=$request->all();
             $old['old_foto']=$old_foto;
             return back()->withInput($old);

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


        // if($request->status=='REKAP' OR $request->status==NULL){
        //     $day=Carbon::parse($request->start_date)->startOfDay();
        //     $day_last=Carbon::parse($request->end_date)->endOfDay();

        // }else{

        //     if($request->date){
        //         $last_date=Carbon::now()->addDays(-3)->endOfDay();

        //         $day=Carbon::parse($request->date)->startOfDay();

        //          $day_last=Carbon::parse($request->date)->endOfDay();
        //          if($last_date->gt($day_last)){
        //             Alert::error('','Anda tidak dapat melihat data melebihi '.$last_date->format('d F Y'));
        //             return redirect()->route('g.index');
        //          }
        //     }
        // }


        if($request->start_date){
            $day=Carbon::parse($request->start_date)->startOfDay();
            $day_last=Carbon::parse($request->end_date)->endOfDay();
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
                ->leftJoin('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
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
                ->leftJoin('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
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
                ->leftJoin('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
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
                $log_tamu=$log_tamu->whereRaw($where?'('.implode(" OR ",$where).")":'(1=1)');
            }


            $log_tamu=$log_tamu->get();


            $fingerprint=$request->fingerprint();

            $rekap_tamu=DB::table('log_tamu as log')
            ->where('log.gate_checkin','>=',$day)
            ->where('log.gate_checkin','<=',$day_last)
            ->selectRaw("count(distinct(case when (gate_checkout is null) then log.id else null end )) as count_in,count(distinct(case when (gate_checkout is not null) then log.id else null end) ) as count_out ")->first();

            return view('gate.index')->with([
                'data_visitor'=>$log_tamu,
                'date_start'=>$day->format('Y-m-d'),
                'date_end'=>$day_last->format('Y-m-d'),
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
            "log.gate_checkout is not null" ,

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


        $order='log.gate_checkin';
            if($checkin!='ALL'){
                switch ($checkin) {
                    case 'GATE_CHECKIN':
                        $where_def=[
                            "log.gate_checkin >= '".$day."'" ,
                            "log.gate_checkin <= '".$day_last."'" ,
                         ];

                        $where_def[]="log.gate_checkout is null ";
                        $order='log.gate_checkin';


                        break;
                    case 'GATE_CHECKOUT':
                        $where_def=[
                            "log.gate_checkout >= '".$day."'" ,
                            "log.gate_checkout <= '".$day_last."'" ,
                         ];
                        $where_def[]="log.gate_checkout is not null ";
                         $order='log.gate_checkout';





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
            ->orderBy($order,'desc')
            ->groupBy('v.id','log.id')
            ->get();


            $log_rekap_in=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','log.tamu_id')
            ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
              ->whereRaw(implode(' and ', $where_def_rekap))
            ->orderBy('log.gate_checkin','desc')
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
            ->selectRaw('sum(case when (('.implode(') or (', $whereRaw_rekap_out).')) then 1 else 0 end) as count_data,count(distinct(case when (v.tamu_khusus=true and  ('.implode(' or ', $whereRaw_rekap_out).')) then log.id else null end)) as count_khusus,
                sum(case when (v.tamu_khusus=false and ('.implode(' or ', $whereRaw_rekap_out).')) then 1 else 0 end) as count_non_khusus

           ')
            ->first();



             if(!$log_rekap_out){
                $log_rekap_out=[
                    'count_data'=>0,
                    'count_khusus'=>0,
                    'count_non_khusus'=>0,

                ];
            }




            if($request->v_export=='EXCEL'){
                return static::rekap_export_excel([
                    'data'=>$log_tamu,
                    'status'=>$checkin,
                    'day'=>$day,
                    'day_last'=>$day_last,
                    'req'=>$request,
                    'tujuan_json'=>is_array($request->tujuan_json)?$request->tujuan_json:json_decode($request->tujuan_json??'[]'),
                    'tujuan'=>$request->tujuan??[]

                ]);

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
                 Alert::error('Gagal','Tamu Masih Di Dalam Gedung dan Belum Menyelesaikan Kunjunganya');
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


    static public function rekap_export_excel($dt){

        ini_set('memory_limit',-1);
        ini_set('max_execution_time', -1);
        if($dt['req']->jenis_table=='LENGKAP'){
            $HEAD=[
                'NO'=>1,
                'JENIS IDENTITAS'=>2,
                'NOMER IDENTITAS'=>3,
                'NAMA'=>4,
                'KATEGORI & JENIS TAMU'=>5,
                'INSTANSI'=>6,
                'TUJUAN'=>7,
                'KEPERLUAN'=>8,
                'TANGGAL & JAM MASUK'=>9,
                'OPERATOR MASUK'=>10,
                'TANGGAL & JAM KELUAR'=>11,
                'OPERATOR KELUAR'=>12
            ];

        }else{

            $HEAD=[
                'NO'=>1,
                'NAMA'=>2,
                'KATEGORI & JENIS TAMU'=>3,
                'TUJUAN'=>4,
                'KEPERLUAN'=>5,
                'TANGGAL & JAM MASUK'=>6,
                'TANGGAL & JAM KELUAR'=>7,
            ];
        }

        $DATASTYLE=[
            'font' => [
                'bold' => true,

            ],
            'alignment'=>[
                'wrapText'=>true,

            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                ],
            ],

        ];

        $KT=[

            'font' => [
                'bold' => true,


            ],
            'alignment'=>[
                'center'=>true,
                'wrapText'=>true,

            ],


        ];


        $TITLE=[
            'font' => [
                'bold' => true,
                'size'=>18,


            ],
            'alignment'=>[
                'center'=>true,
                'horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ],

        ];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $start=7;
        $this_day=$dt['day'];
        $title='REKAP '.


        (($dt['req']->jenis_tamu=='KHUSUS')
            ?'TAMU KHUSUS ':
            ($dt['req']->jenis_tamu=='ALL'?
                'TAMU ':
                'TAMU NON KHUSUS ')).
        (($dt['status']=='GATE_CHECKIN')?
            'MASUK ':
            (($dt['status']==='GATE_CHECKOUT')?'KELUAR ':'')
        );

        $d1=$this_day->format('Y-m-d');
        $d2=$dt['day_last']->format('Y-m-d');

        $sheet->setCellValue(static::nta(1).(1),$title);
        $tujuan=collect($dt['tujuan_json']??[])->pluck('label')->toArray();

        $sheet->getStyle(static::nta(1).(1))->applyFromArray($TITLE);
        $sheet->setCellValue(static::nta(1).(2), implode(', ',$tujuan??[]));
        $sheet->setCellValue(static::nta(1).(3), 'TANGGAL EXPORT');
        $sheet->setCellValue(static::nta(2).(3), Carbon::now()->format('Y-m-d H:I'));
        $sheet->setCellValue(static::nta(1).(4), 'KUNJUNGAN');
        $sheet->setCellValue(static::nta(2).(4), $this_day->format('Y-m-d'));
        $sheet->setCellValue(static::nta(3).(4), '-');
        $sheet->setCellValue(static::nta(4).(4), $dt['day_last']->format('Y-m-d'));
        $sheet->mergeCells((static::nta(1).(1)).':'.static::nta(count($HEAD)).(1));
        $sheet->mergeCells((static::nta(1).(2)).':'.static::nta(count($HEAD)).(2));


        foreach (array_keys($HEAD) as $key => $value) {
            # code...
            $sheet->setCellValue(static::nta($key+1).(5),$value);
            $sheet->setCellValue(static::nta($key+1).(6),$key+1);
            if($key==0){
                $sheet->getColumnDimension(static::nta($key+1))->setWidth(8);

            }else{
                $sheet->getColumnDimension(static::nta($key+1))->setWidth(13);
            }

        }
        $sheet->getStyle(static::nta(1).(2).':'.static::nta(count($HEAD)).(6))->applyFromArray($KT);
        $sheet->getStyle(static::nta(1).(5).':'.static::nta(count($HEAD)).(6))->applyFromArray($DATASTYLE);

        $key_last=$start;

        foreach ($dt['data'] as $key => $v) {
            foreach ($HEAD as $h => $c) {
                switch ($h) {
                    case 'NO':
                         $sheet->setCellValue(static::nta($c).($key+$start),$key+1);
                        # code...
                        break;
                    case 'JENIS IDENTITAS':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->jenis_identity);
                        # code...
                        break;
                    case 'NOMER IDENTITAS':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->identity_number);
                        # code...
                        break;
                    case 'NAMA':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->nama);
                        # code...
                        break;
                    case 'KATEGORI & JENIS TAMU':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->tamu_khusus?''.($v->jenis_tamu_khusus):$v->kategori_tamu);
                        # code...
                        break;
                     case 'TUJUAN':
                            $tujuan=collect((CV::build_from_array('tujuan_tamu',json_decode($v->tujuan??'[]'))))->pluck('label')->toArray();

                         $sheet->setCellValue(static::nta($c).($key+$start),implode(', ',$tujuan??[]));
                        # code...
                        break;
                    case 'INSTANSI':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->instansi);
                        # code...
                        break;
                    case 'KEPERLUAN':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->keperluan);
                        # code...
                        break;
                    case 'TANGGAL & JAM MASUK':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->gate_checkin?Carbon::parse($v->gate_checkin)->format('Y-m-d H:i'):'-');
                        # code...
                        break;
                    case 'TANGGAL & JAM KELUAR':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->gate_checkout?Carbon::parse($v->gate_checkout)->format('Y-m-d H:i'):'-');
                        # code...
                        break;
                     case 'OPERATOR MASUK':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->nama_gate_handle??'-');
                        # code...
                        break;
                     case 'OPERATOR KELUAR':
                         $sheet->setCellValue(static::nta($c).($key+$start),$v->nama_gate_out_handle??'-');
                        # code...
                        break;

                    default:
                        # code...
                        break;
                }

            }
            $key_last=(($key)+$start);
        }

         $sheet->getStyle(static::nta(1).($start).':'.static::nta(count($HEAD)).($key_last))->applyFromArray($DATASTYLE);

        $sheet->setAutoFilter(static::nta(1).($start-1).':'.static::nta(count($HEAD)).($key_last));

        $writer = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode($title).'_'.($d1==$d2?$d1:$d1.'-'.$d2).'.xlsx"');
        return $writer->save('php://output');


    }



    public function index(Request $request)
    {


        $fingerprint=$request->fingerprint();
        return view('home')->with(['fingerprint'=>$fingerprint]);
    }

    static  public function nta($number) {
        $number = intval($number);
        if ($number <= 0) {
            return '';
        }
        $alphabet = '';
        while($number != 0) {
            $p = ($number - 1) % 26;
            $number = intval(($number - $p) / 26);
            $alphabet = chr(65 + $p) . $alphabet;
        }
        return $alphabet;
    }
}
