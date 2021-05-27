@extends('adminlte::page')

@section('content')
@php
@endphp
    
@php
@endphp
<script type="application/javascript" src="{{asset('tparty/bower_components/webcamjs/webcam.js') }}"></script>
   <script type="application/javascript">
        function errFoto(d){
            d.src='{{asset('tamu-def.png') }}'
        }
    </script>
<div  id="picIdInput">
    <div  v-if="display" class="col-md-12" style="padding:10px; background:#ddd; z-index: 9999; margin-top: 30px; display: none; position: fixed; max-width:340px; right:0; top:0; border-radius: 10px;  " >
       <div class="box">
           <div class="box-body">
                <div class="row">
            <div class="col-md-12">
                 <h5><span><button @click="closePicInput" class="btn btn-sm btn-circle btn-primary"><i class="fa fa-times"></i></button></span> INPUT IDENTITY @{{ jenis }}</h5>
            <input type="hidden" v-model="jenis">
            </div>
            <div class="col-md-12" style="margin-bottom: 10px;">
                <div id="cam-record" style="max-width: 100%; min-width: 320px; min-height: 240px; overflow: hidden; border-radius: 10px;"></div>

            </div>
           
        </div>
       
           </div>
           <div class="box-footer">
               
                <div class="btn-group">
                    <button v-if="!url_filled" class="btn btn-primary" @click="takePic">Snap</button>
                    <button v-if="url_filled" class="btn btn-primary" @click="displayingStat">Resnap</button>
                    <button v-if="url_filled" class="btn btn-primary" @click="extractData">Extrak Data</button>
                    <button v-if="url_filled" class="btn btn-primary" @click="displayingStat">Save Data</button>

                </div>
          
           </div>
       </div>
    </div>
</div>

<H4><b>TAMBAH DATA TAMU</b></H4>
<div class="btn-group" id="action_input">
    <a href="{{ route('g.receiver',['fingerprint'=>$fingerprint ])}}" onclick="setTimeout(function(){vinput.bc; console.log('bc_run')},3000)" target="_blank" class="btn btn-primary">HALAMAN LAYAR TAMU</a>
    <button v-if="env=='KTP'" @click="ktp" class="btn btn-primary bg-info">EXTRASI DATA KTP</button>
    <button v-if="env=='SIM'" @click="sim" class="btn btn-danger">EXTRASI DATA SIM</button>
    <button v-if="env=='LAINYA'" @click="lainya" class="btn btn-success">EXTRASI DATA LAINYA</button>
</div>


<form action="{{ route('g.input.proccess',['id'=>$data->id,'slug'=>Str::slug($data->nama),'fingerprint'=>$fingerprint]) }}" id="submit-form-provos" method="post"  enctype='multipart/form-data'>
    @csrf
    <div class="card" id="vinput">
        <div v-for="item in identity_record" class="card-header with-border">
            <div style="max-width: 30%;">
                <img :src="item.path" style="width:100%;" >
                <p>@{{item.jenis}}</p>
                <input type="hidden" v-bind:name="'identity_record['+item.jenis+']'" v-model="item.path">
            </div>
        </div>

        <div class="card-body">

            <div class="row" >

                <div class="col-md-3">
                    <div class="text-center" style="width:100%; min-height:100px; border:1px solid #222">
                        <img src="" :src="foto" alt="" onerror="errFoto(this)" style="max-width:100%;">
                        <div class="input-group input-group-sm">
                            <input type="file" v-on:change="processFileFoto($event)" class="form-control " name="foto_file" accept="image/*">
                            <span  class="input-group-addon">
                                <button type="button" class="btn btn-primary btn-sm">CAMERA</button>
                            </span>
                        </div>
                    </div>
                  
                 
                    <input type="hidden" name="foto" v-model="foto">
                    <div  v-if="btn_check">
                        <div class="btn-group" style="margin-top:10px; ">
                            <button type="button" @click="submit_form" class="btn btn-primary">CHEKIN GATE</button>
                        </div>
                        <hr>
                    </div>
                    <p style="margin-top: 10px;"><b>DATA KUNJUNGAN</b></p>
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
                        <label>Instansi</label>
                       <input type="text" class="form-control" v-model="instansi" name="instansi">
                    </div>
                    <div class="form-group">
                        <label>Tujuan*</label>
                        <select class="form-control" v-model="tujuan" name="tujuan[]" multiple="">
                            @foreach (config('web_config.tujuan_tamu')??[] as $t)
                                 <option value="{{$t['tag']}}">{{$t['name']}}</option>
                            @endforeach
                        </select>
                    </div>
                     <div class="form-group">
                        <label>Keterangan Keperluan*</label>
                        <textarea name="keperluan" class="form-control" v-model="keperluan"></textarea>
                    </div>
                </div>
                <div class="col-md-9">
                     <p><b>IDENTITAS TAMU</b></p>
                     <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Jenis Identitas*</label>
                                <select name="jenis_identity" required id="" v-model="jenis_identity"  class="form-control">
                                    @foreach (config('web_config.identity_list')??[] as $k)
                                        <option value="{{$k['tag']}}">{{$k['name']}}</option>
                                        {{-- expr --}}
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="">Nomer Identitas*</label>
                                <input name="no_identity" value="" required type="text" v-model="no_identity" class="form-control">
                            </div>
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
                                <label for="">@{{jenis_identity}} Berlaku Hingga</label>
                                <input  name="berlaku_hingga" type="date" v-model="berlaku_hingga" class="form-control">
                            
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="">Nomer Telpon*</label>
                                <input name="nomer_telpon" required type="text"   @change="phoneNumber" v-model="nomer_telpon" class="form-control">
                            
                            </div>
                            <div class="form-group">
                                <label for="">Tempat Lahir</label>
                                <input name="tempat_lahir" type="text" v-model="tempat_lahir" class="form-control">
                            
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
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>FILE @{{jenis_identity}}</label>
                                    <input id="input-file-id" v-on:change="processFile($event)"type="file" class="form-control" name="file" accept="image/*">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label>CAPTURE @{{jenis_identity}}</label>
                                <div >
                                <img src="" :src="identity.rendered" id="file-identity" class="img-thumbnail">
                                    
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
    var bc_provos = new BroadcastChannel('bcgate-{{$fingerprint}}');

    var vactionInput=new Vue({
        el:'#action_input',
        data:{
            env:'KTP'
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
            jenis_identity: '{{$data->jenis_identity}}',
            no_identity: '{{$data->no_identity}}',
            nama: '{{$data->nama}}',
            foto:'{{($data->foto)?asset($data->foto):''}}',
            foto_file:null,
            foto_def:'{{($data->foto)?asset($data->foto):''}}',
            tempat_lahir: '{{$data->tempat_lahir}}',
            golongan_darah:'{{$data->golongan_darah??'-'}}',
            jenis_kelamin:{{$data->jenis_kelamin??1}},
            tanggal_lahir: '{{$data->tanggal_lahir?Carbon\Carbon::parse($data->tanggal_lahir)->format('Y-m-d'):null}}',
            alamat: '{{$data->alamat}}',
            nomer_telpon: "{{$data->nomer_telpon??'+62'}}",
            pekerjaan: "{{$data->pekerjaan}}",
            keperluan: "{{$data->keperluan}}",
            instansi: "{{$data->instansi}}",
            kategori_tamu: '{{$data->kategori_tamu}}',
            agama: "{{$data->agama}}",
            berlaku_hingga: "{{$data->berlaku_hingga}}",
            btn_check: false,
            tujuan:<?=($data->tujuan)??'[]'?>,
            identity:{
                "recorded":'{{($data->path_identity)}}',
                "file":null,
                "rendered_def":'{{$data->path_identity?url($data->path_identity):''}}',
                "rendered":'{{$data->path_identity?url($data->path_identity):''}}'
            },
            identity_record:[

            ]
        },
        methods:{

            get_identity:function(){

                if(window.api_get_id!=null){
                    window.api_get_id.abort();
                }

                 window.api_get_id=$.post('{{route('api.get.identity')}}',{
                    'no_identity':this.no_identity,
                    'jenis_identity':this.jenis_identity,
                    'nomer_telpon':this.nomer_telpon,

                },function(res){
                    if(res.code==200){
                        // vinput.no_identity=res.data.identity_number;
                        // vinput.jenis_identity=res.data.jenis_identity;
                        // vinput.berlaku_hingga=res.data.berlaku_hingga;
                        $('#input-file-id').val(null);
                        $('#input-file-id').trigger('change');
                        vinput.identity.rendered_def=null;
                        vinput.identity.rendered=res.data.path_identity;

                    }
                    // console.log(res);
                });
            },
            display_identity:function(){
                $.post('{{route('api.identity.match')}}',{'jenis_identity':this.jenis_identity!=null?this.jenis_identity:null,'no_identity':(this.no_identity.length>5?this.no_identity:null),'nomer_telpon':(this.nomer_telpon.length>12?this.nomer_telpon:null)},function(res){
                        
                       if(res.code==200){
                         if(vinput.identity.file==null){
                            
                        }
                    }else{

                    }
                    });

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
                        this.get_identity();

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
                            this.bc();

                        }else{
                            this.nomer_telpon=window.them_phone;
                             this.bc();

                        }
                    this.get_identity();

                }

            },
            bc:function(){
                window.bc_provos.postMessage(vinput.$data);
                if((this.nomer_telpon.length>11) && (this.no_identity.length>3) && (this.nama.length>3) && (this.jenis_kelamin!=null) && (this.kategori_tamu!=null)){
                    this.btn_check=true;

                }else{
                    this.btn_check=false;
                }

            },
            processFile:function(event){
                this.identity.file = event.target.files[0]??null;

            },
            processFileFoto:function(event){
                this.foto_file = event.target.files[0]??null;

            }
        },

        watch:{
           
            nomer_telpon:'phoneNumber',
            no_identity:'numberIdentity',
            nama:'namaTamu',
            jenis_identity:  function(val,old){
                window.vactionInput.env=val;

                if(val!=old){
                   window.vactionInput.env=val;
                    $('#input-file-id').val(null);
                    this.identity.rendered_def=null;
                    this.identity.rendered=null;
                    $('#input-file-id').trigger('change');
                    this.identity.rendered=this.identity.recoded_def??null;
                    this.bc();
                    this.get_identity();
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
    vinput.bc();

</script>


<script  type="application/javascript">
 var vpicItput=new Vue({
        el:'#picIdInput',
        data:{
            jenis:'KTP',
            display:false,
            pic_data:null,
            url_filled:false,
        },
        methods:{
            
            extractData:function(){
                if(this.pic_data){
                    var data={
                        jenis:this.jenis,
                        pic_data:this.pic_data,
                    };

                    let timerInterval
                    var interval_time=0;
                        Swal.fire({
                          title: 'Extrasi data!',
                          html: '<b></b> milliseconds.',
                          timer: 5000,
                          timerProgressBar: true,
                          didOpen: () => {
                            Swal.showLoading()
                            timerInterval = setInterval(() => {
                              const content = Swal.getContent()
                              if (content) {
                                const b = content.querySelector('b')
                                if (b) {
                                interval_time+=100;
                                  b.textContent = interval_time;
                                }
                              }
                            }, 100)
                          },
                          willClose: () => {
                            clearInterval(timerInterval)
                          }
                        }).then((result) => {
                          /* Read more about handling dismissals below */
                          if (result.dismiss === Swal.DismissReason.timer) {
                            console.log('I was closed by the timer')
                          }
                    });

                    $.post('{{route('api.identity.extract')}}',data,function(res){
                          Swal.fire("Berhasil", "Extrasi data selesai", "success");
                                vinput.nama=res.data.nama;
                                vinput.no_identity=res.data.nik;
                                vinput.tanggal_lahir=res.data.tanggal_lahir;
                                vinput.tempat_lahir=res.data.tempat_lahir;
                                vinput.foto=res.data.foto;
                                vinput.alamat=res.data.alamat;
                                vinput.nomer_telpon=res.data.nomer_telpon;




                    });
                }
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
                    
                    dest_width: 320,
                    dest_height: 240,
                    
                    crop_width: 320,
                    crop_height: 240,
                    
                    image_format: 'png',
                    jpeg_quality: 100,
                    enable_flash: true,
                    flip_horiz: false,
                    fps: 15,
                    facingMode: "environment"
                    });
                    
                    window.Webcam.attach('#cam-record')
                },300);

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
                        console.log(this.pic_data);
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


    vinput.bc();




</script>

<div class="modal fade"  tabindex="-1" id="modal-submit" role="dialog">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">KONFIRMASI PENGINPUTAN</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <p>Apakah anda yakin mengirim form ini?</p>
          <p><b>Nama</b>: @{{ nama }}, <b>Jenis ID</b>: @{{ jenis_identity }} </p>
          <p><b>@{{ no_identity }}</b></p>
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
                          $('#modal-submit').show();
                          $('#modal-submit').modal({ keyboard: false })   // initialized with no keyboard
                            $('#modal-submit').modal('show')                // initializes and invokes show immediately
                     }
              }
          }
      })
  </script>
@stop

