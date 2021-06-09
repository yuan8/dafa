@extends('adminlte::page')

@section('content')

<script type="application/javascript" src="{{asset('tparty/bower_components/webcamjs/webcam.js') }}"></script>
   <script type="application/javascript">
        function errFoto(d){
            d.src='{{asset('tamu-def.png') }}'
        }
    </script>
<div  id="picIdInput">
    <div  v-if="display" class="col-md-12" style="padding:10px; background:#222; z-index: 9999; margin-top: 30px;  position: fixed; max-width:340px; right:0; top:0; border-radius: 10px;  " >
       <div class="box">
           <div class="box-body">
                <div class="col-md-12">
                     <h5><span><button @click="closePicInput" class="btn btn-sm btn-circle btn-primary"><i class="fa fa-times"></i></button></span></h5>
                     <hr style="background: #fff">
                 </div>
                
                <div class="col-md-12 text-center" style="margin-bottom: 10px;">
                    <div id="cam-record" v-bind:height="height+'px'" v-bind:width="width+'px'"
                     style=" overflow: hidden; border-radius: 10px;">
                         
                     </div>

                </div>
               
            </div>
       
          <div class="box-footer">
                <div class="btn-group">
                    <button v-if="!url_filled" class="btn btn-primary" @click="takePic">AMBIL</button>
                    <button v-if="url_filled" class="btn btn-primary" @click="displayingStat">AMBIL UANG</button>
                   
                    <button v-if="url_filled"  class="btn btn-primary" @click="save">GUNAKAN</button>

                </div>
           </div>
       </div>
    </div>
</div>

<H4><b>DATA TAMU </b></H4>



<form action="{{route('g.tamu.update',['id'=>$data->id])}}" id="submit-form-provos" method="post"  enctype='multipart/form-data'>
    @csrf
    @method('PUT')
    <div class="card" id="vinput">
        <div class="card-header bg-danger" v-if="izin_akses_masuk==false">
            TAMU TIDAK DI IZINKAN MASUK 
        </div>
        <div class="card-body">

            <div class="row" >

                <div class="col-md-3">
                    <div class="text-center" style="width:100%; min-height:100px; border:1px solid #222">
                        <img src="" :src="foto" alt="" onerror="errFoto(this)" style="max-width:100%;">
                         <input type="hidden" name="file_foto_cam" v-model="foto_file_cam">
                        <div class="input-group input-group-sm " style="margin-top: 10px; border-top:1px solid #222" >
                            <input type="file" v-on:change="processFileFoto($event)" class="form-control" id="file-foto" name="foto_file" accept="image/*">
                           
                            <span  class="input-group-addon">
                                <button v-on:click="getFotoCam()" type="button" class="btn btn-primary btn-sm"><i class="fa fa-camera"></i> .</button>
                            </span>
                        </div>
                    </div>
                  
                 
                    <input type="hidden" name="foto" v-model="foto">
                   
                    <div class="form-group" >
                        <label>JENIS TAMU</label>
                        <select class="form-control" name="tamu_khusus" v-model="tamu_khusus">
                            <option value="0">BIASA</option>
                            <option value="1">TAMU KHUSUS</option>
                        </select>
                    </div>
                    <div  v-if="btn_check && tamu_khusus==false">
                                <div class="btn-group" style="margin-top:10px; ">
                                    <button type="button" @click="submit_form" class="btn btn-primary">UPDATE</button>
                                </div>
                                <hr>
                            </div>
                           

                    <div v-if="tamu_khusus==true">
                        <label>ID TAMU KHUSUS</label>
                        <p><span class="badge bg-warning">{{$data->string_id}}</span></p>
                        <div class="form-group" >
                        <label>JENIS TAMU KHUSUS</label>
                        <select class="form-control" name="jenis_tamu_khusus" v-model="jenis_tamu_khusus">
                           @foreach (config('web_config.jenis_tamu_khusus') as $keyj=> $j)
                                <option value="{{$keyj}}">{{$keyj}}</option>
                               {{-- expr --}}
                           @endforeach
                        </select>
                    </div>
                        <p style="margin-top: 10px;"><b>DATA KUNJUNGAN ISI OTOMATIS</b></p>
                     <hr>

                     <div class="form-group" >
                        <label>Kategori Tamu*</label>
                        <select class="form-control" name="kategori_tamu" v-model="kategori_tamu">
                            @foreach (config('web_config.kategori_tamu') as $k)
                                
                            <option {{$k['tag']}} {{old('kategori_tamu')==$k['tag']?'selected':""}} >{{$k['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                     <div class="form-group">
                        <label>Instansi*</label>
                       <input type="text" class="form-control" required="" v-model="instansi" name="instansi">
                    </div>
                    <div class="form-group">
                         <label>Tujuan*</label>
                        <input type="hidden" name="tujuan" required="" v-model="JSON.stringify(tujuan_json)">
                        <v-select class="vue-select2" multiple="" 
                            :options="options_tujuan" v-model="tujuan_json"
                            :searchable="true" language="en-US">
                        </v-select>  
                    </div>
                     <div class="form-group">
                        <label>Keterangan Keperluan*</label>
                        <textarea name="keperluan" class="form-control" v-model="keperluan"></textarea>
                    </div>
                    </div>
                </div>
                <div class="col-md-9">
                     <p><b>IDENTITAS TAMU </b></p>
                     <hr>

                    <div class="row">
                        <div class="col-md-6">
                          
                           
                            <div class="form-group">
                                <label for="">Nama Tamu*</label>
                                <input name="nama" required type="text" v-model="nama"   class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="">Jenis Kelamin*</label>
                                <select name="jenis_kelamin" required   id="" v-model="jenis_kelamin" class="form-control">
                                    <option value="1">LAKI LAKI</option>
                                    <option value="0">PEREMPUAN</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Golongan Darah</label>
                                <select name="golongan_darah" id=""  v-model="golongan_darah" class="form-control">
                                    <option value="A">A</option>
                                    <option value="B">B</option>
                                    <option value="O">O</option>
                                    <option value="AB">AB</option>
                                    <option value="-">-</option>

                                </select>
                            </div>
                           
                             <div class="form-group">
                                <label for="">Nomer Telpon*</label>
                                <input name="nomer_telpon" required type="text"   @change="phoneNumber" v-model="nomer_telpon" class="form-control">
                            
                            </div>
                               <div class="form-group">
                                <label for="">Tempat Lahir</label>
                                <input name="tempat_lahir" type="text" v-model="tempat_lahir" class="form-control">
                            
                            </div>
                            <div v-if="tamu_khusus==true">
                                <p class="text-danger">Jenis Tamu Khusus <b>@{{jenis_tamu_khusus}}</b>  Mewajibkan Memasukan Data ID <b>@{{list_jenis_tamu_khusus[jenis_tamu_khusus]}}</b> Terlebih Dahulu</p>
                            </div>
                             <div  v-if="btn_check && tamu_khusus==true">
                                <div class="btn-group" style="margin-top:10px; ">
                                    <button type="button" @click="submit_form" class="btn btn-primary">UPDATE</button>
                                </div>
                                <hr>
                            </div>
                           
                           
                        </div>

                        <div class="col-md-6">
                            <div class="" v-if="tamu_khusus==true">

                                <label>IDENTITY TAMU KHUSUS</label>
                                <div class="row">
                                    @php
                                    $hs=(Hash::make('TAM'.$data->id.'SUS'));
                                        $exp_list=['M|nor','Zz*&','Linq<>','uTes)','YarJ^U','hALLLall'];
                                        $exp=$exp_list[array_rand($exp_list,1)];
                                        $id_route=route('g.tamu.id_khusus',['id'=>$data->id,'hash_log'=>$hs.$exp.base64_decode(date('Ymd'))]);
                                    @endphp
                                     <iframe class="col-md-12" src="{{$id_route}}">
                                        
                                    </iframe>
                                    <div class="col-md-12">
                                       
                                    <div class="btn-group text-left">
                                        <a href="{{$id_route}}" download="" class="btn btn-xs btn-primary">DOWNLOAD ID</a>
                                    </div> 
                                    </div>
                                </div>
                                <hr>
                            </div>
                            <div class="form-group">
                                <label for="">Tanggal Lahir</label>
                                <input  name="tanggal_lahir" type="date" v-model="tanggal_lahir" class="form-control">
                            
                            </div>

                            <div class="form-group">
                                <label for="">Pekerjaan</label>
                                <input  name="pekerjaan" type="text" v-model="pekerjaan" class="form-control">
                            
                            </div>
                            <div class="form-group">
                                <label for="">Alamat</label>
                            <textarea name="alamat"  v-model="alamat" class="form-control" id="" cols="30" rows="4"></textarea>
                            </div>
                        </div>
                       
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label>IZIN MASUK*</label>
                                <select class="form-control" name="izin_akses_masuk" v-model="izin_akses_masuk">
                                    <option value="0">TIDAK DIZINKAN</option>
                                    <option value="1">DIIZINKAN</option>
                                </select>
                            </div>
                            <div class="form-group" name="keterangan_tolak_izin_akses" v-if="izin_akses_masuk==0">
                                <label>KETERANGAN PENCEKALAN IZIN MASUK</label>
                                <textarea name="keterangan_tolak_izin_akses" v-model="keterangan_tolak_izin_akses" class="form-control"></textarea>
                            </div>
                        </div>
                    </div>
                   <div class="row">
                       <div class="col-md-12">
                        <label>JENIS IDENTITAS TAMU*</label>
                           <div class="table-responsive">
                               <table class="table-bordered table">
                                   <thead>
                                       <tr>
                                           <th>AKSI</th>
                                           <th></th>

                                           <th>JENIS IDENTITAS</th>
                                           <th>NOMER IDENTITAS</th>
                                           <th>BERKALU HINGGA</th>


                                       </tr>
                                   </thead>
                                   <tbody>
                                       <tr v-for="item in data_id ">
                                        <td></td>
                                        <td>
                                            <img v-bind:src="item.path_rendered" style="width:100px;">
                                        </td>
                                           <td>@{{item.jenis_identity}}</td>
                                            <td>@{{item.identity_number}}</td>
                                       </tr>
                                   </tbody>
                               </table>
                           </div>
                       </div>
                   </div>
                   
                
                </div>
            </div>
            
        </div>
    </div>
</form>


@endsection


@section('js')
<script  type="application/javascript">
    var bc_provos = new BroadcastChannel('bcgate-edit');
    function isEmpty(data){
        if(data == null || data == '') {
            return true;
        }else{
            return false;
        }
    }

    var vactionInput=new Vue({
        el:'#action_input',
        data:{
            env:'CCCC'
        },
        methods:{
            ktp:function(){
                vpicItput.jenis='KTP';
                vpicItput.display=true;

            },
            sim:function(){
                vpicItput.jenis='SIM';
                vpicItput.display=true;
            },
            lainya:function(){
                vpicItput.jenis='LAINYA';
                vpicItput.display=true;
            }
        }
    });


    var them_phone='+62';
    var api_get_id=null;
    var vinput = new Vue({
        el: '#vinput',
         data: {
            // 
            izin_akses_masuk:{{$data->izin_akses_masuk?1:0}},
            keterangan_tolak_izin_akses:'{{preg_replace( "/\r|\n/", " ",$data->keterangan_tolak_izin_akses)}}',
            jenis_tamu_khusus:'{{$data->jenis_tamu_khusus??'REKANAN'}}',
            list_jenis_tamu_khusus:<?=json_encode(config('web_config.jenis_tamu_khusus'))?>,
            jenis_identity: '',
            no_identity: '',

            nama: '{{$data->nama}}',
            foto:'{{($data->foto)?asset($data->foto):''}}',
            foto_file:null,
            foto_file_cam:null,
            tamu_khusus: {{$data->tamu_khusus?1:0}},
            foto_def:'{{($data->foto)?asset($data->foto):''}}',
            tempat_lahir: '{{$data->tempat_lahir}}',
            golongan_darah:'{{$data->golongan_darah??'-'}}',
            jenis_kelamin:{{$data->jenis_kelamin??1}},
            tanggal_lahir: '{{$data->tanggal_lahir?Carbon\Carbon::parse($data->tanggal_lahir)->format('Y-m-d'):null}}',
            alamat: "{{preg_replace( "/\r|\n/", " ",$data->alamat)}}",
            nomer_telpon: "{{$data->nomer_telpon??'+62'}}",
            pekerjaan: "{{$data->pekerjaan}}",
            keperluan: "{{preg_replace( "/\r|\n/", " ",$data->def_keperluan)}}",
            instansi: "{{$data->def_instansi}}",
            kategori_tamu: "{{$data->def_kategori_tamu}}",
            agama: "{{$data->agama}}",
            berlaku_hingga: "",
            btn_check: false,
            data_id:<?= json_encode($data_id??[])?>,
            tujuan_json:<?=json_encode(CV::build_from_array('tujuan_tamu',json_decode($data->def_tujuan??'[]')??[]))??[]?>,
            options_tujuan:<?= json_encode(CV::build_options('tujuan_tamu')) ?>,
            tujuan:<?=($data->def_tujuan)??'[]'?>,
            identity:{
                "recorded":'',
                "file":null,
                "rendered_def":'',
                "rendered":''
            },
            // identity_record:[

            // ]
        },
        methods:{

            get_identity:function(expt='dd'){

               
            },
          

            submit_form:function(){
                window.vmodalsubmit.isActive=true;
            },
            namaTamu:function(){
                if(this.nama){
                    this.nama=this.nama.toUpperCase();
                    this.bc();

                }
            },

            
            numberIdentity:function(val='',oldvAL){
                if(val!=oldvAL){

                    if(this.no_identity){
                        var val=this.no_identity;
                        val=val.replace(/[-]/g,'');
                        let arr_val=val.split('');
                        var char_no_identity='';
                        for(var i=0;i<arr_val.length;i++){

                            if(i%4==0 && i!=0){
                                char_no_identity+='-';
                            }
                            char_no_identity+=arr_val[i];
                        }

                        this.no_identity=char_no_identity;
                        this.bc();
                        this.get_identity('nomer_identity');

                    }
                }
                return true;
            },
            phoneNumber:function(){
                if(this.nomer_telpon){
                    var val=this.nomer_telpon;
                        var char_phone='';
                        val=val.replace(/[-]/g,'');
                        val=val.replace('+62','0');
                        val=val.slice(0,12);
                        let arr_val=val.split('');
                        for(var i=0;i<arr_val.length;i++){
                            if((i==0) && (arr_val[0]!='+')){
                                char_phone='+62';
                            }else if((i==0) && (arr_val[0]=='+')){
                                char_phone='+';
                            }
                            if(i>0){
                                if(i%3==0){
                                    char_phone+='-';
                                }
                                if( !isNaN(parseInt(arr_val[i])) || (arr_val[i]=='-')){
                                    char_phone+=arr_val[i];
                                }
                                
                            }
                            

                        }
                        if(window.them_phone!=char_phone){
                            this.nomer_telpon=char_phone;
                            window.them_phone=char_phone;
                            this.get_identity('nomer_telpon');
                            this.bc();


                        }else{
                            this.nomer_telpon=window.them_phone;

                            this.bc();

                        }

                }

            },
            bc:function(){
                window.bc_provos.postMessage(vinput.$data);
               if(this.tamu_khusus==true){

                    if((this.nomer_telpon.length>11)  && (this.nama.length>3) && (this.jenis_kelamin!=null) && (this.tujuan_json.length!=0) && (this.keperluan!="") && (this.kategori_tamu!="") && (this.instansi!="")  ){
                        this.btn_check=true;

                    }else{
                        this.btn_check=false;
                    }

               }else{
                console.log('NO KGUSUS');
                 if((this.nomer_telpon.length>11)  && (this.nama.length>3) && (this.jenis_kelamin!=null) ){
                        this.btn_check=true;

                }else{
                    this.btn_check=false;
                }
               }

            },
            processFile:function(event){
                this.identity.file = event.target.files[0]??null;

            },
            processFileFoto:function(event){
                this.foto_file = event.target.files[0]??null;
                if(this.foto_file){
                    this.foto_file_cam=false;
                }
            }
            ,
            getFotoCam:function(){
                vpicItput.display=true;
            }

        },

        watch:{
           
            nomer_telpon:'phoneNumber',
            no_identity:'numberIdentity',
            tamu_khusus:function(val,old){
                
                    
                    this.bc();

                

            },
            tujuan_json:"bc",
            instansi:"bc",
            kategori_tamu:"bc",
            keperluan:"bc",



            nama:'namaTamu',
            jenis_identity:  function(val,old){
                if(val!=old){
                   window.vactionInput.env=val;
                    $('#input-file-id').val(null);
                    this.identity.rendered_def=null;
                    this.identity.rendered=null;
                    $('#input-file-id').trigger('change');
                    this.identity.rendered=this.identity.recoded_def??null;
                    this.bc();
                    this.get_identity('jenis_identity');
                    this.bc();

                }
               


            },
            foto: 'bc',

            foto_file: function(file,old){
                  if(file!=null){
                    var reader = new FileReader();
                    console.log(file);
                    reader.readAsDataURL(file);
                    reader.onload = function(e) {
                      vinput.foto=this.result;
                    }
                }else{

                    this.foto=this.foto_def??null;
                }

            },
            tempat_lahir:'bc',
            golongan_darah:'bc',
            jenis_kelamin: 'bc',
            tanggal_lahir: 'bc',
            alamat: "bc",
            pekerjaan: "bc",
            agama: "bc",
            kategori_tamu: "bc",

            "identity.file":function(file){
                if(file!=null){
                    var reader = new FileReader();
                    reader.readAsDataURL(file);
                    reader.onload = function(e) {
                      vinput.identity.rendered=this.result;
                    }
                }else{

                    this.identity.rendered=this.identity.recoded_def??null;
                }
            }
        }
    });


  $(function(){
     setTimeout(function(){
        window.vinput.get_identity();
        window.vinput.bc();

        console.log('init');
     },1000);
 });

</script>


<script  type="application/javascript">
 var vpicItput=new Vue({
        el:'#picIdInput',
        data:{
            display:false,
            pic_data:null,
            url_filled:false,
            width:320,
            height:240
        },
        methods:{
            
            save:function(){
                $('#file-foto').val(null);
                $('#file-foto').trigger('change');

                vinput.foto_file_cam=this.pic_data;
                vinput.foto=this.foto=this.pic_data;
                

            },
            closePicInput:function(){
                this.display=false;
            },
            hasGetUserMedia:function(){
                return !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia);
            },
            displayingStat:function(){
                if(this.display){

                    if (this.hasGetUserMedia()) {
                    // Good to go!
                            this.attacthCam();

                    } else {
                        alert("getUserMedia() is not supported by your browser");
                    }
                }
                
            },
            attacthCam:function(){
                this.pic_data=null;
                setTimeout(function(){
                    window.Webcam.set({
                    width: 320,
                    height: 240,
                    dest_width: 640,
                    dest_height: 480,
                    
                    crop_width: 480,
                    crop_height: 490,
                    
                    image_format: 'png',
                    jpeg_quality: 90,
                    
                    // flip horizontal (mirror mode)
                    flip_horiz: true,
                        fps: 15,
                    // facingMode: "environment"
                    });
                    
                    window.Webcam.attach('#cam-record')
                },1000);

            },
            takePic:function(){

                window.Webcam.snap( function(data_uri) {
                window.vpicItput.pic_data=data_uri;
                console.log(data_uri);
                // window.vpicItput.pic_data=window.testid;
                window.Webcam.reset();
                    $('#cam-record').html( '<img clss="img-responsive" style="max-width:100%;" src="'+window.vpicItput.pic_data+'">');

                } );

            }
        },
        watch:{
            display:function(val){
                if(val){
                    if(this.pic_data){
                        setTimeout(function(){
                             $('#cam-record').html( '<img clss="img-responsive" style="max-width:100%;" src="'+window.vpicItput.pic_data+'">');
                         },300);
                    }else{
                     this.displayingStat();

                    }
                }else{

                }
            },
            pic_data:function(val){
                if(val){
                    this.url_filled=true;
                }else{
                    this.url_filled=false;

                }

            }
        }
    });






</script>

<div class="modal fade"  tabindex="-1" id="modal-submit" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">KONFIRMASI UPDATE</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Apakah anda yakin mengirim form ini?</p>
          <p><b>Nama</b>: @{{ nama }}, <b>NO TELP</b>: @{{ nomer_telpon }} </p>
         
        </div>
        <div class="modal-footer">
          <button type="button" @click="submit" class="btn btn-primary">KIRIM</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">BATAL</button>
        </div>
      </div>
    </div>
  </div>
  <script type="application/javascript">
      var vmodalsubmit=new Vue({
          el:"#modal-submit",
          data:{
              nama:'',
              jenis_identity:'',
              no_identity:'',
              nomer_telpon:'',

              isActive:false
          },
          methods:{
              submit:function(){
                  
                  $('#submit-form-provos').submit();
              }
          },
          watch:{
              isActive:function(val,old){
                      if(val){
                        this.nama=window.vinput.nama;
                        this.jenis_identity=window.vinput.jenis_identity;
                        this.no_identity=window.vinput.no_identity;
                        this.nomer_telpon=window.vinput.nomer_telpon;

                          $('#modal-submit').show();
                          $('#modal-submit').modal({ keyboard: false })   // initialized with no keyboard
                            $('#modal-submit').modal('show')                // initializes and invokes show immediately
                     }
              }
          }
      })
  </script>
@stop
