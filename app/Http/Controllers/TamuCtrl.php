<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
use Hash;
use Validator;
use CV;
use Storage;
use Alert;
class TamuCtrl extends Controller
{

    public function tambah(){
        return view('tamu.add');
    }

    public function store(Request $request){
        $valid=Validator::make($request->all(),[
            'nomer_telpon'=>'string|required|unique:tamu,nomer_telpon',
        ]);

        if($valid->fails()){
            Alert::error('Gagal',$valid->errors()->first());
            return back()->withInput();
        }

    }

    public function view($id,$slug){
        $tamu=DB::table('tamu as t')->where('id',$id)->first();

        if($tamu){
            $identity=DB::table('identity_tamu as idt')->where('tamu_id',$id)->get();

            foreach ($identity as $key => $value) {
                $identity[$key]->path_rendered=url($value->path_identity);
                $identity[$key]->path_def=url($value->path_identity);
                $identity[$key]->path_file=null;
                $identity[$key]->identity_number_k=$value->identity_number;

                $identity[$key]->berlaku_hingga=$value->berlaku_hingga?Carbon::parse($value->berlaku_hingga)->format('Y-m-d'):null;

                # code...
            }
            if($tamu->tamu_khusus){

            }else{

            }

            if(Auth::User()->can('is_admin')){
                return view('tamu.edit')->with(['data'=>$tamu,'data_id'=>$identity]);
            }else{
                 return view('tamu.view')->with(['data'=>$tamu,'data_id'=>$identity]);
            }
        }
    }

    public function simpan_data_tamu($id,Request $request){

        $tamu=DB::table('tamu')->where('id',$id)->first();

        if($tamu){


            $jenis_identity=collect(config('web_config.identity_list'))->pluck('tag');
             $data=[
                    'nama'=>$request->nama,
            ];


            $identity=DB::table('identity_tamu')->where([
                ['tamu_id','=',$id],
                ['jenis_identity','=',config('web_config.jenis_tamu_khusus.'.$request->jenis_tamu_khusus)],

            ])->first();
            if($request->tamu_khusus){
                $request['tujuan']=CV::build_from_options(json_decode($request->tujuan??'[]'));


                 $request['req_id_def']=config('web_config.jenis_tamu_khusus.'.$request->jenis_tamu_khusus)??'ya';


                $valid=Validator::make($request->all(),[
                    'jenis_kelamin'=>'required|numeric|in:0,1',
                    'nama'=>'required|string|min:3',
                    'nomer_telpon'=>'required|min:10',
                    'kategori_tamu'=>'required|string',
                    'keperluan'=>'required|string',
                    'instansi'=>'required|string',
                    'tujuan'=>'required|array',
                    'req_id_def'=>'required|string'

                ]);

                if($valid->fails()){
                    Alert::error('Gagal',$valid->errors()->first());
                    return back();
                }

                $no=DB::table('tamu')->where([
                    ['id','!=',$tamu->id],
                    ['nomer_telpon','=',$request->nomer_telpon],
                ])->first();

                if($no){
                    Alert::error('Gagal','Nomer telepon Telah Digunakan sebelumnya');
                    return back();
                }

                $data['def_keperluan']=$request->keperluan;
                $data['def_tujuan']=$request->tujuan;
                $data['def_instansi']=$request->instansi;
                $data['def_kategori_tamu']=$request->kategori_tamu;

                $data['tamu_khusus']=$request->tamu_khusus??0;
                $data['jenis_tamu_khusus']=$request->jenis_tamu_khusus;



            }else{
                $valid=Validator::make($request->all(),[
                    'jenis_kelamin'=>'required|numeric|in:0,1',
                    'nama'=>'required|string|min:3',
                    'nomer_telpon'=>'required|min:10',
                ]);

                 if($valid->fails()){
                    Alert::error('Gagal',$valid->errors()->first());
                    return back();
                }


            }
        }else{
            Alert::error('Gagal','DATA TAMU TIDAK TERSEDIA');
            return redirect()->route('g.daftar_tamu');
        }


                if($request->alamat){
                    $data['alamat']=$request->alamat;
                }

                $data['izin_akses_masuk']=$request->izin_akses_masuk??false;
                $data['tamu_khusus']=$request->tamu_khusus??false;


                $data['keterangan_tolak_izin_akses']=$request->keterangan_tolak_izin_akses??null;
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


                 if($request->instansi){
                    $data['def_instansi']=$request->instansi;
                }

                if($request->kategori_tamu){
                    $data['def_kategori_tamu']=$request->kategori_tamu;
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
                            ($tamu?$tamu->id:'cache').'/foto/def-cam-profile.png',$data_foto);

                        $path_foto='/storage/indentity/id-'.
                            ($tamu?$tamu->id:'cache').'/foto/def-cam-profile.png';


                    }
                }

                if($path_foto){
                    $data['foto']=$path_foto;
                }


                $data['updated_at']=Carbon::now();

               $up= DB::table('tamu')->where('id',$tamu->id)->update($data);
               if($up){
                $now=Carbon::now();
                $new_id=[];

                $col=collect($request->identity??[]);
                $col=$col->pluck('id')->toArray();

                DB::table('identity_tamu')
                ->where('tamu_id',$tamu->id)
                ->whereNotIn('id',$col)
                ->delete();

                foreach ($request->identity??[] as $key => $n) {
                    if(strpos($n['id'], 'new-')!==false){
                        if($n['jenis_identity'] and $n['identity_number']){
                            $new_id[]=$n;
                        }
                    }else{

                        $check=DB::table('identity_tamu')->where([
                        ['id','=',$n['id']],
                        ['jenis_identity','=',$n['jenis_identity']],
                        ['identity_number','=',$n['identity_number']],
                        ['tamu_id','=',$tamu->id],
                        ])->first();

                        if(!$check){
                            DB::table('identity_tamu')->where('id',$n['id'])
                            ->where('tamu_id',$tamu->id)->update([
                                'jenis_identity'=>$n['jenis_identity'],
                                'identity_number'=>$n['identity_number'],
                                'berlaku_hingga'=>$n['berlaku_hingga'],
                                'updated_at'=>$now,
                                'path_identity'=>isset($n['path_file_src'])?Storage::url(Storage::put('public/indentity/id-'.($tamu->id).'/'.$n['jenis_identity'],$n['path_file_src'])):DB::raw('path_identity')
                            ]);

                        }else{
                                DB::table('identity_tamu')->where('id',$n['id'])
                                ->where('tamu_id',$tamu->id)->update([
                                    'updated_at'=>$now,
                                    'berlaku_hingga'=>$n['berlaku_hingga'],
                                    'path_identity'=>isset($n['path_file_src'])?Storage::url(Storage::put('public/indentity/id-'.($tamu->id).'/'.$n['jenis_identity'],$n['path_file_src'])):DB::raw('path_identity')
                                ]);

                        }


                    }
                }

                foreach ($new_id as $key => $n) {
                    $check=DB::table('identity_tamu')->where([
                        ['jenis_identity','=',$n['jenis_identity']],
                        ['tamu_id','=',$tamu->id],

                    ])->first();

                    if(!$check){
                        DB::table('identity_tamu')->insertOrIgnore([
                            'tamu_id'=>$tamu->id,
                            'jenis_identity'=>$n['jenis_identity'],
                            'identity_number'=>$n['identity_number'],
                            'berlaku_hingga'=>$n['berlaku_hingga'],
                            'path_identity'=>isset($n['path_file_src'])?Storage::url(Storage::put('public/indentity/id-'.($tamu->id).'/'.$n['jenis_identity'],$n['path_file_src'])):null,
                            'created_at'=>$now,
                            'updated_at'=>$now,
                        ]);
                    }

                }

                if(!$request->identity){
                    DB::table('identity_tamu')->where('tamu_id',$tamu->id)->delete();
                }


                Alert::success('Berhasil','DATA BERHASIL DI PERBARUI');
               }

        return back();

    }

    public function identity_tamu_khusus($id,Request $request){

        $tamu=DB::table('tamu')->where('id',$id)->first();
        $hash_log=$request->hash_log??'';
        $re=[];
        $LIST_=['M|nor','Zz*&','Linq<>','uTes)','YarJ^U','hALLLall'];

        $chek=0;
        do {

            if(isset($LIST_[$chek])){
                $re=explode($LIST_[$chek],$hash_log);

            }else{
                $re=['1','2'];
            }

            $chek++;
        } while (count($re)<=1 OR count($LIST_)<$chek);

        if($tamu){
            if($tamu->tamu_khusus and $tamu->def_instansi ){

            }else{
                $tamu=false;
            }
        }


        $size=array(0,0,153.01417,242.6457);
        if($tamu and ((base64_encode($re[1])==date('Ymd')) and Hash::check('TAM'.$id.'SUS',$re[0])) ){
            if($tamu->foto){

                $path = public_path($tamu->foto);
                $type = pathinfo($path, PATHINFO_EXTENSION);
                $data = file_get_contents($path);
                $foto = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $dompdf = new Dompdf();
                $dompdf->setBasePath(public_path('/'));
                $view=view('tamu.id_khusus.print')->with(['code_id'=>$tamu->string_id,'tamu'=>$tamu,'foto'=>$foto])->render();


                // return $view;
                $dompdf->loadHtml($view);


                // if($hash_log=='render'){
                //     return view('tamu.id_khusus.print')->with(['code_id'=>$tamu->string_id,'tamu'=>$tamu,'foto'=>$foto])->render();
                // }

                // (Optional) Setup the paper size and orientation
                $dompdf->setPaper($size, 'potrait');

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                return $dompdf->stream('id-'.$tamu->string_id.'.pdf' ,array("Attachment" => false));
            }
        }else{
                $dompdf = new Dompdf();
                $dompdf->set_base_path(public_path('tparty'));
                $dompdf->loadHtml(view('tamu.id_khusus.error')->render()   );


                // if($hash_log=='render'){
                //     return view('tamu.id_khusus.print')->with(['code_id'=>$tamu->string_id,'tamu'=>$tamu,'foto'=>$foto])->render();
                // }

                // (Optional) Setup the paper size and orientation
                $dompdf->setPaper($size, 'potrait');

                // Render the HTML as PDF
                $dompdf->render();

                // Output the generated PDF to Browser
                return $dompdf->stream('id-'.'ERROR'.'.pdf' ,array("Attachment" => false));        }

    }

    public function edit($id,$slug){
        $tamu=DB::table('tamu as t')->where('id',$id)->first();

        if($tamu){
            $identity=DB::table('identity_tamu as idt')->where('tamu_id',$id)->get();

            foreach ($identity as $key => $value) {
                $identity[$key]->path_rendered=url($value->path_identity);
                $identity[$key]->path_def=url($value->path_identity);
                $identity[$key]->path_file=null;
                $identity[$key]->identity_number_k=$value->identity_number;

                $identity[$key]->berlaku_hingga=$value->berlaku_hingga?Carbon::parse($value->berlaku_hingga)->format('Y-m-d'):null;

                # code...
            }
            if($tamu->tamu_khusus){

            }else{

            }


            return view('tamu.edit')->with(['data'=>$tamu,'data_id'=>$identity]);
        }

    }


    public function toGateProvos($id,$slug,Request $request){

        $data=DB::table('tamu as t')
        ->selectRaw("t.id as id_tamu,t.*")->where('id',$id)->first();
        if(!$data){
            Alert::error('Gagal','');
            return back();
        }else{


        }
        $data=(array) $data;
        $data['foto']=isset($data['foto'])?url($data['foto']):null;

        return redirect()->route('p.input')->withInput(['nomer_telpon'=>$data['nomer_telpon'],'BUILD_IN_FORM'=>1]);
    }
	public function daftarTamuList(Request $req){
		$where=[];
        // dd(explode('|',$req->q));
		foreach (explode('|',$req->q) as $key => $value) {
            $value=(trim($value));
            $where[]="replace(t.nomer_telpon,'-','') like '%".trim(str_replace('-', '', $value))."%'";
            $where[]="t.nama like '%".$value."%'";
            $where[]="replace(idt.identity_number,'-','') like '%".trim(str_replace('-', '', $value))."%'";
            $where[]="idt.jenis_identity like '%".$value."%'";
            $where[]="t.alamat like '%".$value."%'";
            $where[]="t.pekerjaan like '%".$value."%'";
            $where[]="t.tempat_lahir like '%".$value."%'";
            $where[]="t.tanggal_lahir like '%".$value."%'";

		}

		$data=DB::table('tamu as t')
        ->selectRaw("t.id as id_tamu,t.*,group_concat(distinct(concat(idt.jenis_identity,' : ',idt.identity_number)) SEPARATOR ' || ') as idt_list")
		->leftJoin('identity_tamu as idt','idt.tamu_id','=','t.id')
		->leftJoin('log_tamu as lg','lg.tamu_id','=','t.id');
        if(count($where)){
            $data=$data->whereRaw('('.implode(') or (', $where).')');
        }

		$data=$data

        ->orderBy(DB::raw("(".'('.implode(') + (', $where).')'.")"),'DESC')
        ->limit(15)
        ->groupBy('t.id')
        ->get();

        return view('tamu.archive')->with(['data'=>$data,'req'=>$req]);


	}


	public function report(Request $request){
		$dateNow=Carbon::now()->format('Y-m-d');

		$start=$dateNow;
		$end=$dateNow;

		if($request->start){
			$start=$request->start;
		}


		if($request->end){
			$end=$request->end;
		}

		$day=Carbon::now()->startOfDay();
        $day_last=Carbon::now()->endOfDay();

        if($request->date){
            $last_date=Carbon::now()->addDays(-3)->endOfDay();
            $day=Carbon::parse($request->date)->startOfDay();

             $day_last=Carbon::parse($request->date)->endOfDay();
             if($last_date->gt($day_last)){
                Alert::error('','Anda tidak dapat melihat data melebihi '.$last_date->format('d F Y'));
                return redirect()->route('g.index');
             }

        }



        $checkin=null;
        if($request->status){
            $checkin=$request->status;
        }

        $status=NULL;

        $where_raw=[
        	'1=1'
        ];



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
                $where_raw[]="log.tujuan like '%".$value."%'";
            }
        }

        if($checkin==null){
            $checkin='GATE_CHECKIN';
        }


        $log_tamu=[];
        switch($checkin){
            // case 'PROVOS':
            // 	$status='PROVOS';
            //     $log_tamu=DB::table('log_tamu as log')
            //     ->join('tamu as v','v.id','=','log.tamu_id')
            //     ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
            //     ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,(select upin.name from users as upin where upin.id=log.provos_handle) as nama_provos_handle,
            //         (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
            //         (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle")
            //               ->where('log.provos_checkin','>=',Carbon::parse($start))
            //     ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
            //     ->where('log.gate_checkin','=',null)
            //     ->whereRaw(implode(' and ', $where_raw))

            //     ->orderBy('log.provos_checkin','desc');

            // break;
            case 'GATE_CHECKIN':
            	$status='CHECKIN';

                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,
                    (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                    (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle")
                ->where('log.gate_checkin','>=',Carbon::parse($start))
                ->where('log.gate_checkin','<=',Carbon::parse($end)->endOfDay())
                ->where('log.gate_checkout','=',null)
                ->whereRaw(implode(' and ', $where_raw))

                ->orderBy('log.gate_checkin','desc');

            break;
            case 'GATE_CHECKOUT':
            	$status='CHECKOUT';

                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,

                    (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
                    (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle")
                ->where('log.gate_checkin','>=',Carbon::parse($start))
                ->where('log.gate_checkin','<=',Carbon::parse($end)->endOfDay())
                ->where('log.gate_checkout','!=',null)
                ->whereRaw(implode(' and ', $where_raw))

                ->orderBy('log.gate_checkin','desc');

            break;

            default:

             // $log_tamu=DB::table('log_tamu as log')
             //    ->join('tamu as v','v.id','log.tamu_id')
             //    ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
             //    ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at,
             //        (select upin.name from users as upin where upin.id=log.provos_handle) as nama_provos_handle,
             //        (select ucin.name from users as ucin where ucin.id=log.gate_handle) as nama_gate_handle,
             //        (select ucout.name from users as ucout where ucout.id=log.gate_out_handle) as nama_gate_out_handle")
             //    ->where('log.provos_checkin','>=',Carbon::parse($start))
             //    ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
             //    ->whereRaw(implode(' and ', $where_raw))

             //    ->orderBy('log.provos_checkin','desc');


            break;
        }







        if($request->export){
        	$log_tamu=$log_tamu->get();

        	switch($status){
                case 'PROVOS':
                   $status="TAMU TERDAFTAR DI PROVOS";
                    break;
                case 'CHECKIN':
                   $status="TAMU TELAH MEMASUKI GATE";
                    break;

             	case 'CHECKOUT':
                   $status="TELAH MENYELESAIKAN KUNJUNGAN";
                    break;

                default:
                   $status="TELAH MENYELESAIKAN KUNJUNGAN";
            }

        	switch ($request->export) {
        		case 'EXCEL':

        			return static::export_excel($log_tamu,$start,$end,$status);

        			break;
        		case 'PDF':
        			return static::export_pdf($log_tamu,$start,$end,$status);



        			break;

        		default:
        			# code...
        			break;
        	}
        }else{
        	$log_tamu=$log_tamu->paginate(15);

	        return view('admin.tamu.report')->with([
	            'data_visitor'=>$log_tamu,
	            'req'=>$request,
	            'status'=>$checkin,
	            'start'=>$start,
	            'end'=>$end,
                'tujuan_json'=>$request->tujuan_json,
	            'tujuan'=>$request->tujuan??[]
	        ]);



        }







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


	static function export_excel($data,$start_date,$end_date,$status){
	   ini_set('memory_limit',-1);
        ini_set('max_execution_time', -1);
		// dd(public_path('them/export-tamu.xlsx'));


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

		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		$spreadsheet = $reader->load(public_path('them/export-tamu.xlsx'));
		$sheet = $spreadsheet->getActiveSheet();
		$start=6;
		$sheet->setCellValue(static::nta(1).(1), Carbon::now());
		$sheet->setCellValue(static::nta(1).(3), $start_date);
		$sheet->setCellValue(static::nta(3).(3), $end_date);
		$sheet->setCellValue(static::nta(5).(3), $status);



		foreach ($data as $key => $v) {
			// dd($v);
			$v->status=($v->gate_checkout?'CHECKOUT':($v->gate_checkin?'CHECKIN':($v->provos_checkin?'PROVOS':'')));
			switch($v->status){
                case 'PROVOS':
                   $v->status="TAMU TERDAFTAR DI PROVOS";
                    break;
                case 'CHECKIN':
                   $v->status="TAMU TELAH MEMASUKI GATE";
                    break;

             	case 'CHECKOUT':
                   $v->status="TELAH MENYELESAIKAN KUNJUNGAN";
                    break;

                default:
                  if($v->checkout_from_gate){
                     $v->status="TELAH MENYELESAIKAN KUNJUNGAN";
                  }else{
                     $v->status="MEMBATALKAN KUNJUNGAN";

                  }
            }



			$sheet->setCellValue(static::nta(1).($start+$key), $v->jenis_identity);
			$sheet->setCellValue(static::nta(2).($start+$key), $v->identity_number);
			$sheet->setCellValue(static::nta(3).($start+$key), $v->nomer_telpon);
			$sheet->setCellValue(static::nta(4).($start+$key), $v->nama);
			$sheet->setCellValue(static::nta(5).($start+$key), $v->jenis_kelamin?'LAKI-LAKI':'PEREMPUAN');
			$sheet->setCellValue(static::nta(6).($start+$key), $v->golongan_darah);
			$sheet->setCellValue(static::nta(7).($start+$key), $v->tempat_lahir);
			$sheet->setCellValue(static::nta(8).($start+$key), $v->tanggal_lahir);
			$sheet->setCellValue(static::nta(9).($start+$key), $v->alamat);
			$sheet->setCellValue(static::nta(10).($start+$key), $v->pekerjaan);

			$sheet->setCellValue(static::nta(11).($start+$key), $v->kategori_tamu);
			$sheet->setCellValue(static::nta(12).($start+$key), implode(', ',json_decode($v->tujuan??[]) ) );
			$sheet->setCellValue(static::nta(13).($start+$key), $v->keperluan);


			$sheet->setCellValue(static::nta(14).($start+$key), $v->gate_checkin);
			$sheet->setCellValue(static::nta(15).($start+$key), $v->gate_checkout);
			$sheet->setCellValue(static::nta(16).($start+$key), $v->status);
			$sheet->getStyle(static::nta(1).($start+$key).':'.static::nta(16).($start+$key))->applyFromArray($DATASTYLE);


		}

		$sheet->setAutoFilter(static::nta(1).($start-1).':'.static::nta(16).(($start+$key)));

		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode('export-tamu-('.$start_date.')-('.$end_date).').xlsx"');
		return $writer->save('php://output');


	}


	static function export_pdf($data,$start_date,$end_date,$status){
		ini_set('memory_limit',-1);
        ini_set('max_execution_time', -1);
		$DOM=view('gate.export_pdf')->with(['data'=>$data,'start'=>$start_date,'end'=>$end_date,'status'=>$status])->render();

		$pdf = \App::make('dompdf.wrapper');
		$pdf->loadHtml($DOM );
		$pdf->setPaper('legal', 'landscape');
		header('Content-Type: application/pdf');
		return $pdf->stream(urlencode('export-tamu-('.$start_date.')-('.$end_date.')'.".pdf"),array("Attachment" => false) );

	}

}
