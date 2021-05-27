<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use DB;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Dompdf\Dompdf;
class TamuCtrl extends Controller
{
    //



    public function toGateProvos($id,$slug,Request $request){

        $data=DB::table('tamu as t')
        ->selectRaw("t.id as id_tamu,t.*")->where('id',$id)->first();

        $data=(array) $data;
        $data['foto']=isset($data['foto'])?url($data['foto']):null;

        return redirect()->route('p.input')->withInput((array) $data);
    }
	public function daftarTamuList(Request $req){
		$where=[];
        // dd(explode('|',$req->q));
		foreach (explode('|',$req->q) as $key => $value) {
            $value=(trim($value));
            $where[]="replace(t.nomer_telpon,'-','') like '%".$value."%'";
            $where[]="t.nama like '%".$value."%'";
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
                Alert::error('','Anda tidak dapat meilihat data melebihi '.$last_date->format('d F Y'));
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

        if(count($request->tujuan??[])){
        	foreach ($request->tujuan??[] as $key => $value) {
        		$where_raw[]="log.tujuan like '%".$value."%'";
        	}
        }



        $log_tamu=[];
        switch($checkin){
            case 'PROVOS':
            	$status='PROVOS';
                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','=','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                          ->where('log.provos_checkin','>=',Carbon::parse($start))
                ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
                ->where('log.gate_checkin','=',null)
                ->whereRaw(implode(' and ', $where_raw))

                ->orderBy('log.provos_checkin','desc');
                
            break;
            case 'GATE_CHECKIN':
            	$status='CHECKIN';

                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                          ->where('log.provos_checkin','>=',Carbon::parse($start))
                ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
                ->where('log.gate_checkout','=',null)
                ->whereRaw(implode(' and ', $where_raw))

                ->orderBy('log.provos_checkin','desc');
               
            break;
            case 'GATE_CHECKOUT':
            	$status='CHECKOUT';

                $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                ->where('log.provos_checkin','>=',Carbon::parse($start))
                ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
                ->where('log.gate_checkout','!=',null)
                ->whereRaw(implode(' and ', $where_raw))

                ->orderBy('log.provos_checkin','desc');
                
            break;

            default:

             $log_tamu=DB::table('log_tamu as log')
                ->join('tamu as v','v.id','log.tamu_id')
                ->join('identity_tamu as ind',[['ind.tamu_id','=','log.tamu_id'],['ind.jenis_identity','log.jenis_id']])
                ->selectRaw("log.*,v.*,ind.*,log.id as id_log,log.created_at as log_created_at")
                ->where('log.provos_checkin','>=',Carbon::parse($start))
                ->where('log.provos_checkin','<=',Carbon::parse($end)->endOfDay())
                ->whereRaw(implode(' and ', $where_raw))
                
                ->orderBy('log.provos_checkin','desc');
                

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
                   $status="TELAH MENEYELESAIKAN KUNJUNGAN";    
                    break;
            
                default:
                   $status="TELAH MENEYELESAIKAN KUNJUNGAN";
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
                   $v->status="TELAH MENEYELESAIKAN KUNJUNGAN";    
                    break;
            
                default:
                  if($v->checkout_from_gate){
                     $v->status="TELAH MENEYELESAIKAN KUNJUNGAN";
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

			$sheet->setCellValue(static::nta(14).($start+$key), $v->provos_checkin);
			$sheet->setCellValue(static::nta(15).($start+$key), $v->gate_checkin);
			$sheet->setCellValue(static::nta(16).($start+$key), $v->gate_checkout);
			$sheet->setCellValue(static::nta(17).($start+$key), $v->status);
			$sheet->getStyle(static::nta(1).($start+$key).':'.static::nta(17).($start+$key))->applyFromArray($DATASTYLE);


		}

		$sheet->setAutoFilter(static::nta(1).($start-1).':'.static::nta(17).(($start+$key)));

		$writer = new Xlsx($spreadsheet);
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'. urlencode('export-tamu-('.$start_date.')-('.$end_date).').xlsx"');
		return $writer->save('php://output');


	}


	static function export_pdf($data,$start_date,$end_date,$status){
		

		$DOM=view('gate.export_pdf')->with(['data'=>$data,'start'=>$start_date,'end'=>$end_date,'status'=>$status])->render();

		

		$pdf = \App::make('dompdf.wrapper');
		$pdf->loadHtml($DOM );
		$pdf->setPaper('legal', 'landscape');
		header('Content-Type: application/pdf');
       
		return $pdf->stream(urlencode('export-tamu-('.$start_date.')-('.$end_date.')'.".pdf") );

	}

}
