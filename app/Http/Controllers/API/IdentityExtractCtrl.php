<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Storage;
use Carbon\Carbon;
use DB;
use Validator;
class IdentityExtractCtrl extends Controller
{
    //

    public function get_identity(Request $request){
        $valid=Validator::make($request->all(),[
            'nomer_telpon'=>'required|string|min:8',
            'jenis_identity'=>'required|min:3|string',
            'no_identity'=>'required|string|min:3'
        ]);

        if($valid->fails()){
            return array('code'=>500,'data'=>[]);
        }else{
            $data=DB::table('tamu as t')
            ->leftJoin('identity_tamu as idt','idt.tamu_id','=','t.id')
            ->selectRaw('idt.*,t.nama as tamu_nama,t.foto as tamu_foto')
            ->whereRaw("
                (t.nomer_telpon like '%".$request->nomer_telpon."%') or ((idt.jenis_identity like '%".$request->jenis_identity."%') and (
                idt.identity_number like '%".$request->no_identity."%')
                )
            ")->groupBy('idt.id')->first();

            if($data){
                $data->path_identity=isset($data->path_identity)?url($data->path_identity):null;
                $data->tamu_foto=isset($data->tamu_foto)?url($data->tamu_foto):null;
                
                return array('code'=>200,'data'=>$data);
            }else{
                return array('code'=>500,'data'=>[]);

            }


            
        }

    }

    public function generate_qr_phone_cal(Request $request){
        return view('gate.phone_call')->with('request',$request);
    }

    public function batalkan_kunjungan($tamu_id,Request $request){
            $data=DB::table('log_tamu as log')
            ->join('tamu as v','v.id','=','log.tamu_id')
            ->where('log.id',$tamu_id)
            ->selectRaw("log.*,v.nama")
            ->whereNull('log.gate_checkin')
            ->first();

            if($data){
                return view('gate.batalkan')->with('data',$data);

            }

            return view('gate.batalkan_i')->with('data',$data);
    }

    public function extract(Request $request){
        if (preg_match('/^data:image\/(\w+);base64,/', $request->pic_data, $type)) {
            $data = substr($request->pic_data, strpos($request->pic_data, ',') + 1);

            $type = strtolower($type[1]); // jpg, png, gif
        
            if (!in_array($type, [ 'jpg', 'jpeg', 'gif', 'png' ])) {
                throw new \Exception('invalid image type');
            }
            $data = str_replace( ' ', '+', $data );
            $data = base64_decode($data);
        
            if ($data === false) {
                throw new \Exception('base64_decode failed');
            }
        } else {
            throw new \Exception('did not match data URI with image data');
        }
        $fingerprint=$request->fingerprint();
        $path='tmp/'.$fingerprint.'.'.$type;
        Storage::put($path,$data);
        // $path='/public/VISITOR/1/KTP_G.png';

        $data_ex=(new TesseractOCR(storage_path('app/'.$path)))
            ->run();
        $data_ex=strtoupper($data_ex);
        $data_ex=str_replace(' : ', '', $data_ex);
        $data_ex=str_replace('/', '', $data_ex);
        $data_ex=str_replace(['<','>','_','+','!'], '', $data_ex);
        $data_ex=explode("\n", $data_ex);
        $data=[];

        foreach (['NIK','NAMA','TEMPATTGL LAHIR','JENIS KELAMIN','ALAMAT','RTRW','KELDESA','KECEMATAN','AGAMA','STATUS PERKAWINAN','PEKERJAAN','KEWARGANEGARAAN','BERLAKU HINGGA','GOL. DARAH','PROVINSI'] as $key => $value) {
             $match=("/".$value.".*.(\w+)/");
            $DATA_EX=preg_grep($match,$data_ex);
            if(count($DATA_EX)){
                $DATA_EX=array_values($DATA_EX);
                $DATA_EX_s=explode(':',$DATA_EX[0]);
                if(count($DATA_EX_s)>1){
                    $DATA_EX[0]=$DATA_EX_s[1];
                }

                $DATA_EX[0]=trim(str_replace($value, '', $DATA_EX[0]));
                switch ($value) {
                    case 'KEWARGANEGARAAN':
                        if(strpos($DATA_EX[0], 'A')!==false){
                            $DATA_EX='WNA';
                        }else{
                            $DATA_EX='WNI';

                        }
                    break;
                    case 'NIK':
                        $NIK= $DATA_EX[0];
                        $NIK=strtolower($NIK);
                        $NIK=str_replace(['b','l'], '6',$NIK);
                        $NIK=str_replace(['o'], '0',$NIK);
                        $NIK=str_replace(['i'], '1',$NIK);
                        $NIK=str_replace(['B'], '8',$NIK);
                        $NIK=str_replace(['s','S'], '5',$NIK);
                        $NIK=str_replace(['Z','z'], '2',$NIK);
                        $DATA_EX=$NIK;
                        # code...
                        break;
                     case 'JENIS KELAMIN':
                        $DATA_EX=strpos($DATA_EX[0],'LAKI')!==false?1:0;
                        # code...
                        break;
                    case 'BERLAKU HINGGA':
                        preg_match('/[\w][\w]-[\w][\w]-[\w][\w][\w][\w]/', $DATA_EX[0],$BERLAKU);
                        if(count($BERLAKU)){
                            $BERLAKU=$BERLAKU[0];
                        }else{
                            $BERLAKU='';
                        }

                        $BERLAKU_S=$BERLAKU;
                        $BERLAKU=strtolower($BERLAKU);
                        $BERLAKU=str_replace(['b','l'], '6',$BERLAKU);
                        $BERLAKU=str_replace(['o'], '0',$BERLAKU);
                        $BERLAKU=str_replace(['i'], '1',$BERLAKU);
                        $BERLAKU=str_replace(['B'], '8',$BERLAKU);
                        $BERLAKU=str_replace(['s','S'], '5',$BERLAKU);
                        $BERLAKU=str_replace(['Z','z'], '2',$BERLAKU);
                        $DATA_EX=Carbon::parse(trim($BERLAKU))->toDateString();

                        break;
                     case 'TEMPATTGL LAHIR':
                        preg_match('/[\w][\w]-[\w][\w]-[\w][\w][\w][\w]/', $DATA_EX[0],$TANGGAL);
                        if(count($TANGGAL)){
                            $TANGGAL=$TANGGAL[0];
                        }else{
                            $TANGGAL='';
                        }

                        $TANGGAL_S=$TANGGAL;
                        $TANGGAL=strtolower($TANGGAL);
                        $TANGGAL=str_replace(['b','l'], '6',$TANGGAL);
                        $TANGGAL=str_replace(['o'], '0',$TANGGAL);
                        $TANGGAL=str_replace(['i'], '1',$TANGGAL);
                        $TANGGAL=str_replace(['B'], '8',$TANGGAL);
                        $TANGGAL=str_replace(['s','S'], '5',$TANGGAL);
                        $TANGGAL=str_replace(['Z','z'], '2',$TANGGAL);

                        $data['TANGGAL LAHIR']=Carbon::parse(trim($TANGGAL))->toDateString();
                        $TEMPAT=str_replace([$TANGGAL_S,','], '', $DATA_EX[0]);
                        $data['TEMPAT LAHIR']=trim($TEMPAT);
                        $DATA_EX=$DATA_EX[0];
                        break;
                    
                    default:
                        # code...
                    $DATA_EX=$DATA_EX[0];
                        break;
                }
            }else{
                $DATA_EX='';
            }
            $data[$value]=$DATA_EX;
            # code...
        }

        foreach ($data as $key => $value) {
            if($value==''){
                $data[$key]=null;
            }
        }

        $jenis=$request->jenis;
        $data_record=null;
        if($NIK){
            $data_record=DB::table('identity_tamu as ind')
            ->leftJoin('tamu as v','v.id','=','ind.tamu_id')
            ->where(DB::RAW("replace(ind.identity_number,'-','')"),'like',$NIK)
            ->where('ind.jenis_identity',$jenis)
            ->selectRaw("v.*,ind.path_identity,ind.identity_number,ind.berlaku_hingga")->first();
        }

        $ALAMAT=''.(isset($data['ALAMAT'])?$data['ALAMAT']:null).
        (isset($data['RTRW'])?' RT/RW '.$data['RTRW']:null).
        (isset($data['KELDESA'])?' Kel/Desa '.$data['KELDESA']:null).
        (isset($data['KECAMATAN'])?' KECAMATAN '.$data['KECAMATAN']:null).
        (isset($data['PROVINSI'])?' PROVINSI '.$data['PROVINSI']:null);

        $ex_data=[
            'nama'=>isset($data['NAMA'])?$data['NAMA']:null,
            'nik'=>isset($data['NIK'])?$data['NIK']:null,
            'tempat_lahir'=>isset($data['TEMPAT LAHIR'])?$data['TEMPAT LAHIR']:null,
            'tanggal_lahir'=>isset($data['TANGGAL LAHIR'])?$data['TANGGAL LAHIR']:null,
            'alamat'=>$ALAMAT,
            'foto'=>'',
            'jenis_kelamin'=>isset($data['JENIS KELAMIN'])?$data['JENIS KELAMIN']:1,
            'nomer_telpon'=>'',
            'agama'=>isset($data['AGAMA'])?$data['AGAMA']:1,
            'berkalu_hingga'=>isset($data['BERLAKU HINGGA'])?$data['BERLAKU HINGGA']:null,
            'kewarganegaraan'=>isset($data['KEWARGANEGARAAN'])?$data['KEWARGANEGARAAN']:null,
            'status_perkawinan'=>isset($data['STATUS PERKAWINAN'])?$data['STATUS PERKAWINAN']:null,
            'pekerjaan'=>isset($data['PEKERJAAN'])?$data['PEKERJAAN']:null,
            'golongan_darah'=>isset($data['GOL. DARAH'])?$data['GOL. DARAH']:null,
            'record_identity'=>$data,
        ];

        if($data_record){
            $ex_data['nama']=$data_record->nama;
            $ex_data['foto']=asset($data_record->foto??'tamu-def.png');
            $ex_data['alamat']=$data_record->alamat;
            $ex_data['tanggal_lahir']=$data_record->tanggal_lahir;
            $ex_data['alamat']=$data_record->alamat;
            $ex_data['nomer_telpon']=$data_record->nomer_telpon;
            $ex_data['nik']=$data_record->identity_number;
            $ex_data['jenis_kelamin']=$data_record->jenis_kelamin;
            $ex_data['record_identity']=asset($data_record->path_identity);
            $ex_data['berkalu_hingga']=asset($data_record->berkalu_hingga);
            $ex_data['agama']=asset($data_record->agama);
            $ex_data['pekerjaan']=asset($data_record->pekerjaan);
            $ex_data['golongan_darah']=asset($data_record->golongan_darah);
        }

        return array(
            'status'=>200,
            'data'=>$ex_data,
            'qd'=>$data_ex
        );
    }
}
