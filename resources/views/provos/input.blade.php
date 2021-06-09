@extends('adminlte::page')

@section('content')
<script type="application/javascript" src="{{asset('tparty/bower_components/webcamjs/webcam.js') }}"></script>
   <script type="application/javascript">
        function errFoto(d){
            d.src='{{asset('tamu-def.png') }}'
        }
    </script>
<div  id="picIdInput">
    <div  v-if="display" class="col-md-12" style="padding:10px; background:#ddd; z-index: 9999; margin-top: 30px;  position: fixed; max-width:340px; right:0; top:0; border-radius: 10px;  " >
       <div class="box">
           <div class="box-body">
                <div class="col-md-12">
                     <h5><span><button @click="closePicInput" class="btn btn-sm btn-circle btn-primary"><i class="fa fa-times"></i></button></span></h5>
                 </div>
                
                <div class="col-md-12" style="margin-bottom: 10px;">
                    <div id="cam-record" style="max-width: 100%; min-width: 320px; min-height: 240px; overflow: hidden; border-radius: 10px;"></div>

                </div>
               
            </div>
       
          <div class="box-footer">
               
                <div class="btn-group">
                    <button v-if="!url_filled" class="btn btn-primary" @click="takePic">Snap</button>
                    <button v-if="url_filled" class="btn btn-primary" @click="displayingStat">Resnap</button>
                   
                    <button v-if="url_filled"  class="btn btn-primary" @click="save">Save Data</button>

                </div>
          
           </div>
       </div>
    </div>
</div>
<H4><b>TAMBAH DATA TAMU MASUK</b></H4>
<div class="btn-group" id="action_input">
    <a href="{{ route('p.receiver',['fingerprint'=>$fingerprint ])}}" target="_blank" class="btn btn-primary">HALAMAN LAYAR TAMU</a>
   
</div>
<div id="data-pencarian">
    <div class="card" v-if="item!=null">
    <div class="card-header">
        DITEMUKAN DATA <span><button v-if="item.izin_akses_masuk==true" v-on:click="implemented" class="btn btn-xs btn-primary"><i class="fa fa-download"></i> TERAPKAN</button></span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
        <thead>
            <tr>
                <th>FOTO</th>
                <th>JENIS TAMU</th>

                <th>NAMA</th>
                <th>NOMER TELPON</th>
                <th>JENIS ID</th>
                <th>NOMER ID</th>
                <th>ALAMAT</th>
            </tr>
        </thead>
        <tbody>
            <tr class="bg-danger" v-if="item.izin_akses_masuk==false">
                <td  colspan="7">TAMU DILARANG BERKUNGJUNG</td>
            </tr>
            <tr class="bg-warning" v-if="item.izin_akses_masuk==false">
                <td class="text-left" colspan="7">@{{item.keterangan_tolak_izin_akses}}</td>
            </tr>
            <tr>
                <td><img style="max-width: 50px;" v-bind:src="item.tamu_foto"  onerror="errFoto(this)"></td>
                <td>@{{item.tamu_khusus?'TAMU KHUSUS - '+item.jenis_tamu_khusus:'-'}}</td>
                <td>@{{item.tamu_nama}}</td>
                <td>@{{item.tamu_nomer_telpon}}</td>
                <td>@{{item.jenis_identity}}</td>
                <td>@{{item.identity_number}}</td>
                <td>@{{item.tamu_alamat}}</td>
            </tr>
        </tbody>
    </table>
        </div>
    </div>

</div>

</div>

<style type="text/css">
        #data-pencarian table tr td{
            padding: 5px!important;
            text-align: center;
            font-weight: bold;
        }
        #data-pencarian table tr td.text-left{
            text-align: left;
        }
    </style>
<form action="{{ route('p.submit',['fingerprint'=>$fingerprint]) }}" id="submit-form-provos" method="post"  enctype='multipart/form-data'>
    @csrf
    <div class="card" id="vinput">
        

        <div class="card-body">

            <div class="row" >

                <div class="col-md-3">
                    <div class="text-center" style="width:100%; min-height:100px; border:1px solid #222">
                        <img src="" :src="foto" alt="" onerror="errFoto(this)" style="max-width:100%;">
                        <div class="input-group input-group-sm">
                            <input type="file" v-on:change="processFileFoto($event)" class="form-control" id="file-foto" name="foto_file" accept="image/*">
                            <input type="hidden" name="file_foto_cam" v-model="foto_file_cam">
                            <span  class="input-group-addon">
                                <button v-on:click="getFotoCam()" type="button" class="btn btn-primary btn-sm">CAMERA</button>
                            </span>
                        </div>
                    </div>
                  
                 
                    <input type="hidden" name="foto" v-model="foto">
                    <div  v-if="btn_check">
                        <div class="btn-group" style="margin-top:10px; ">
                            <button type="button" @click="submit_form" class="btn btn-primary">MASUK GATE</button>
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
                        <input type="text" class="form-control" name="instansi" v-model="instansi">
                    </div>
                    <div class="form-group">
                        <label>Tujuan*</label>
                        <input type="hidden" name="tujuan" required="" v-model="JSON.stringify(tujuan_json)">
                        <v-select class="vue-select2" multiple="" 
                            :options="options_tujuan" v-model="tujuan_json"
                            :searchable="true" language="en-US">
                        </v-select>     

                       {{--  <select class="form-control" v-model="tujuan"  multiple="">
                            @foreach (config('web_config.tujuan_tamu')??[] as $t)
                                 <option value="{{$t['tag']}}" {{in_array($t['tag'],old('tujuan')??[])?'selected':''}}>{{$t['name']}}</option>
                            @endforeach
                        </select> --}}
                    </div>
                     <div class="form-group">
                        <label>Keterangan Keperluan*</label>
                        <textarea name="keperluan"  class="form-control" v-model="keperluan"></textarea>
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
    function isEmpty(data){
        if(data === null || data === '') {
            return true;
        }else{
            return false;
        }
    }

    var bc_provos = new BroadcastChannel('bcprovos-{{$fingerprint}}');
    var api_get_id=null;
    var api_get_id_v=null;
    var BUILD_IN_FORM={{old('BUILD_IN_FORM')?1:0}};
    var vactionInput=new Vue({
        el:'#action_input',
        data:{
            env:'ddddd'
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


    var vinput = new Vue({
        el: '#vinput',
        data: {
            tamu_khusus: {{old('tamu_khusus')?1:0}},
            jenis_tamu_khusus: "{{old('jenis_tamu_khusus')}}",
            jenis_identity: '{{old('jenis_identity')??'KTP'}}',
            no_identity: '{{old('no_identity')}}',
            nama: '{{old('nama')}}',
            foto:'{{url('tamu-def.png')}}',
            foto_file:null,
            foto_file_cam:false,
            foto_def:null,
            tempat_lahir: '{{old('tempat_lahir')}}',
            golongan_darah:'{{old('golongan_darah')??'-'}}',
            jenis_kelamin:{{old('jenis_kelamin')??1}},
            tanggal_lahir: '{{old('tanggal_lahir')}}',
            alamat: '{{old('alamat')}}',
            instansi: '{{old('instansi')}}',
            nomer_telpon: "{{old('nomer_telpon')??'+62'}}",
            pekerjaan: "{{old('pekerjaan')}}",
            kategori_tamu: "{{old('kategori_tamu')}}",
            agama: "{{old('agama')}}",
            berlaku_hingga: "{{old('berlaku_hingga')}}",
            btn_check: false,
            keperluan:"{{old('keperluan')}}",
            tujuan_json:<?=json_encode(CV::build_from_array('tujuan_tamu',old('tujuan')??[]))??[]?>,
            options_tujuan:<?= json_encode(CV::build_options('tujuan_tamu')) ?>,
            tujuan:<?=!empty(old('tujuan')?json_encode(old('tujuan')):'[]')?>,
            identity:{
                "recorded":null,
                "file":null,
                "rendered_def":null,
                "rendered":null
            },
            identity_record:[

            ]
        },


        methods:{
            get_identity:function(expt='dd'){

                if(window.api_get_id_v!=null){
                    window.api_get_id_v.abort();
                }

                 window.api_get_id_v=$.post('{{route('api.get.identity')}}',{
                    'no_identity':vinput.no_identity.length>5?vinput.no_identity:null,
                    'jenis_identity':vinput.jenis_identity,
                    'match':'perfect',
                    'nomer_telpon':vinput.nomer_telpon.length>11?vinput.nomer_telpon:null,
                },function(res){

                    window.res_2=res;
                    if(res.code==200){

                        vinput.identity.rendered_def=null;
                        vinput.identity.rendered=null;
                        vinput.identity.recorded=null;

                            if((res.data.path_identity!=undefined) && !isEmpty(res.data.path_identity)){
                                if(vinput.identity.rendered!=res.data.path_identity){
                                    vinput.identity.rendered_def=res.data.path_identity;
                                    vinput.identity.rendered=res.data.path_identity;
                                    vinput.identity.recorded=res.data.path_identity;

                                }
                            }

                             if((res.data.berlaku_hingga!=undefined) && !isEmpty(res.data.berlaku_hingga)){
                                if(vinput.identity.berlaku_hingga!=res.data.berlaku_hingga){
                                    vinput.identity.berlaku_hingga=res.data.berlaku_hingga;
                                   
                                }
                            }

                             if((res.data.berlaku_hingga!=undefined) && !isEmpty(res.data.berlaku_hingga)){
                                if(vinput.berlaku_hingga!=res.data.berlaku_hingga){
                                    vinput.berlaku_hingga=res.data.berlaku_hingga;
                                   
                                }
                            }
                            if((res.data.identity_number!=undefined) && !isEmpty(res.data.identity_number)){
                                if(vinput.no_identity!=res.data.identity_number){
                                    vinput.no_identity=res.data.identity_number;
                                   
                                }
                            }

                            //  if((res.data.path_identity!=undefined) && !isEmpty(res.data.path_identity)){
                            //     if(vinput.identity.rendered!=res.data.path_identity){
                            //         vinput.identity.rendered_def=res.data.path_identity;
                            //         vinput.identity.rendered=res.data.path_identity;
                            //         vinput.identity.recorded=res.data.path_identity;

                            //     }
                            // }

                             
                            $('#input-file-id').val(null);
                            $('#input-file-id').trigger('change');

                    }else{
                        vinput.identity.rendered_def=null;
                        vinput.identity.rendered=null;
                        vinput.identity.recorded=null;

                           
                            $('#input-file-id').val(null);
                            $('#input-file-id').trigger('change');
                    }
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
                    }
                    vdatacari.get_identity('identity_number');
                    this.get_identity('identity_number');

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
                            vdatacari.get_identity('nomer_telpon');
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
                if(this.foto_file){
                    this.foto_file_cam=false;
                }
            },
            getFotoCam:function(){
                vpicItput.display=true;
            }
        },

        watch:{
           
            nomer_telpon:'phoneNumber',
            no_identity:'numberIdentity',
            nama:'namaTamu',
            jenis_identity:  function(val,old){

                if(val!=old){


                    $('#input-file-id').val(null);
                    this.identity.recorded=null;
                    this.identity.rendered_def=null;
                    this.identity.rendered_def=null;
                    this.identity.rendered=null;
                    $('#input-file-id').trigger('change');

                    this.identity.rendered=this.identity.recoded_def??null;
                    this.bc();
                    vdatacari.get_identity('jenis_identity');
                    this.get_identity('jenis_identity');

                }

            },
            foto: 'bc',
            foto_file: function(file,old){
                  if(file!=null){
                    var reader = new FileReader();
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
        window.vinput.bc();
        window.vinput.get_identity('jenis_identity');
        window.vdatacari.get_identity('nomer_telpon');
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
                },300);

            },
            takePic:function(){

                window.Webcam.snap( function(data_uri) {
                window.vpicItput.pic_data=data_uri;
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
      });

      var vdatacari=new Vue({
        el:'#data-pencarian',
        data:{
            item:null
        },
        methods:{
              get_identity:function(expt='dd',IDUU=null){

                if(window.api_get_id!=null){
                    window.api_get_id.abort();
                }

                 window.api_get_id=$.post('{{route('api.get.identity')}}',{
                    'no_identity':vinput.no_identity.length>5?vinput.no_identity:null,
                    'id_uu':IDUU,
                    'jenis_identity':vinput.jenis_identity,
                    'nomer_telpon':vinput.nomer_telpon.length>11?vinput.nomer_telpon:null,
                },function(res){

                    if(res.code==200){
                        window.res=res;
                        vdatacari.item=res.data;
                        if(window.BUILD_IN_FORM){
                           setTimeout(function(){
                             vdatacari.implemented();
                            window.BUILD_IN_FORM=0;
                           },500);

                        }
                        
                    }else{
                        vdatacari.item=null;
                        window.BUILD_IN_FORM=0;
                    }
                });
            },
            implemented:function(){
                if(vinput.identity.file==null){
                        if((this.item.tamu_foto!=undefined) && !isEmpty(this.item.tamu_foto)){
                                    if(vinput.foto!=this.item.tamu_foto){
                                            if(vinput.foto_file_cam==false && vinput.foto_file==null){
                                                vinput.foto=this.item.tamu_foto;
                                            }
                                        }
                                }

                            }

                            if((this.item.tamu_nomer_telpon!=undefined) && !isEmpty(this.item.tamu_nomer_telpon)){
                                if(vinput.nomer_telpon!=this.item.tamu_nomer_telpon){
                                    vinput.nomer_telpon=this.item.tamu_nomer_telpon;
                                    
                                }
                            }

                           


                                if((this.item.identity_number!=undefined) && !isEmpty(this.item.identity_number)){
                                    if(vinput.no_identity!=this.item.identity_number){
                                        vinput.no_identity=this.item.identity_number;
                                        
                                    }
                                }


                            if((this.item.jenis_identity!=undefined) && !isEmpty(this.item.jenis_identity)){
                                if(vinput.jenis_identity!=this.item.jenis_identity){
                                    vinput.jenis_identity=this.item.jenis_identity;
                                    
                                }
                            }

                            if((this.item.tamu_tempat_lahir!=undefined) && !isEmpty(this.item.tamu_tempat_lahir)){
                                if(vinput.tempat_lahir!=this.item.tamu_tempat_lahir){
                                    vinput.tempat_lahir=this.item.tempat_lahir;
                                    
                                }
                            }
                            if((this.item.tamu_tanggal_lahir!=undefined) && !isEmpty(this.item.tamu_tanggal_lahir)){
                                if(vinput.tanggal_lahir!=this.item.tamu_tanggal_lahir){
                                    vinput.tanggal_lahir=this.item.tamu_tanggal_lahir;
                                    
                                }
                            }

                            

                            if((this.item.tamu_pekerjaan!=undefined) && !isEmpty(this.item.tamu_pekerjaan)){
                                if(vinput.pekerjaan!=this.item.tamu_pekerjaan){
                                    vinput.pekerjaan=this.item.tamu_pekerjaan;
                                    
                                }
                            }

                            if((this.item.tamu_golongan_darah!=undefined) && !isEmpty(this.item.tamu_golongan_darah)){
                                if(vinput.golongan_darah!=this.item.tamu_golongan_darah){
                                    vinput.golongan_darah=this.item.tamu_golongan_darah;
                                    
                                }
                            }

                             if((this.item.tamu_alamat!=undefined) && !isEmpty(this.item.tamu_alamat)){
                                if(vinput.alamat!=this.item.tamu_alamat){
                                    vinput.alamat=this.item.tamu_alamat;
                                    
                                }
                            }

                             if((this.item.tamu_tempat_lahir!=undefined) && !isEmpty(this.item.tamu_tempat_lahir)){
                                if(vinput.tempat_lahir!=this.item.tamu_tempat_lahir){
                                    vinput.tempat_lahir=this.item.tamu_tempat_lahir;
                                    
                                }
                            }

                             if((this.item.tamu_nama!=undefined) && !isEmpty(this.item.tamu_nama)){
                                if(vinput.nama!=this.item.tamu_nama){
                                    vinput.nama=this.item.tamu_nama;
                                    
                                }
                            }


                             if((this.item.tamu_jenis_kelamin!=undefined) && !isEmpty(this.item.tamu_jenis_kelamin)){
                                if(vinput.jenis_kelamin!=this.item.tamu_jenis_kelamin){
                                    vinput.jenis_kelamin=this.item.tamu_jenis_kelamin;
                                    
                                }
                            }

                            if((this.item.alamat!=undefined) && !isEmpty(this.item.alamat)){
                                if(vinput.alamat!=this.item.alamat){
                                    vinput.jenis_kelamin=this.item.tamu_jenis_kelamin;
                                    
                                }
                            }

                            if((this.item.def_instansi!=undefined) && !isEmpty(this.item.def_instansi)){
                                if(vinput.instansi!=this.item.def_instansi){
                                    vinput.instansi=this.item.def_instansi;
                                    
                                }
                            }

                            if((this.item.def_keperluan!=undefined) && !isEmpty(this.item.def_keperluan)){
                                if(vinput.keperluan!=this.item.def_keperluan){
                                    vinput.keperluan=this.item.def_keperluan;
                                    
                                }
                            }

                             if((this.item.def_tujuan!=undefined) && !isEmpty(this.item.def_tujuan)){
                                if(vinput.tujuan_json!=this.item.def_tujuan){
                                    vinput.tujuan_json=this.item.def_tujuan;
                                }
                            }

                             if((this.item.def_kategori_tamu!=undefined) && !isEmpty(this.item.def_kategori_tamu)){
                                if(vinput.kategori_tamu!=this.item.def_kategori_tamu){
                                    vinput.kategori_tamu=this.item.def_kategori_tamu;
                                    
                                }
                            }


                            vinput.identity.rendered_def=null;
                            vinput.identity.rendered=null;
                            vinput.identity.recorded=null;

                            if((this.item.path_identity!=undefined) && !isEmpty(this.item.path_identity)){
                                if(vinput.identity.rendered!=this.item.path_identity){
                                    vinput.identity.rendered_def=this.item.path_identity;
                                    vinput.identity.rendered=this.item.path_identity;
                                    vinput.identity.recorded=this.item.path_identity;

                                }
                            }

                             
                            $('#input-file-id').val(null);
                            $('#input-file-id').trigger('change');
            }
        }
      });


    var this_page_active=true;
    // active pages

    (function() {
          var hidden = "hidden";

          // Standards:
          if (hidden in document)
            document.addEventListener("visibilitychange", onchange);
          else if ((hidden = "mozHidden") in document)
            document.addEventListener("mozvisibilitychange", onchange);
          else if ((hidden = "webkitHidden") in document)
            document.addEventListener("webkitvisibilitychange", onchange);
          else if ((hidden = "msHidden") in document)
            document.addEventListener("msvisibilitychange", onchange);
          // IE 9 and lower:
          else if ("onfocusin" in document)
            document.onfocusin = document.onfocusout = onchange;
          // All others:
          else
            window.onpageshow = window.onpagehide
            = window.onfocus = window.onblur = onchange;

          function onchange (evt) {
            var v = "visible", h = "hidden",
                evtMap = {
                  focus:v, focusin:v, pageshow:v, blur:h, focusout:h, pagehide:h
                };

                evt = evt || window.event;
                if (evt.type in evtMap){
                    if( evtMap[evt.type]=='hidden'){
                        this_page_active=false;
                        console.log('down');
                    }else{

                       this_page_active=true;
                        console.log('up');

                    }
                }
                else{
                    if(this[hidden]){
                        this_page_active=false;
                        console.log('down');


                    }else{
                        this_page_active=true;
                        console.log('up');



                    }
                  // document.body.className = this[hidden] ? "hidden" : "visible";
                }
              // console.log('page active : '+this_page_active);
                
        }
        if( document[hidden] !== undefined )
            onchange({type: document[hidden] ? "blur" : "focus"});
        })();



    // SCANE  
    onScan.attachTo(document, {
        singleScanQty:1,
        onScan: function(sScanned, iQty) { 
            if(this_page_active){
                window.BUILD_IN_FORM=true;
                 window.vdatacari.get_identity('string_id',$sScanned);
            }
         },
        onScanError: function(oDebug) {  },
        onScanButtonLongPress: function() {  },
        onKeyDetect: function(iKeyCode, oEvent){ 
            
        },
        onKeyProcess: function(sChar, oEvent){  },
        onPaste: function(sPasted){
            console(sPasted);
         },
        reactToPaste:true,
        minLength:5,
    });


  </script>
@stop

