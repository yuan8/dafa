@extends('adminlte::page')

@section('content')
<script  type="application/javascript" src="{{asset('tparty/bower_components/webcamjs/webcam.js') }}"></script>
<H4><b>TAMBAH DATA TAMU</b></H4>
<div class="btn-group" id="action_input">
    <button @click="ktp" class="btn btn-primary bg-info">EXTRASI DATA KTP</button>
    <button @click="sim" class="btn btn-danger">EXTRASI DATA SIM</button>
    <button @click="lainya" class="btn btn-success">EXTRASI DATA LAINYA</button>

</div>
<div class="card">
    <div class="card-body">
        <div class="row" id="vinput">
            <div class="col-md-3">
                <div class="text-center" style="width:100%; min-height:100px; border:1px solid #222">
                    <img src="" :src="foto" alt="" onerror="errFoto(this)" style="max-width:100%;">
                </div>
                <script  type="application/javascript">
                    function errFoto(d){
                        d.src='{{asset('tamu-def.png') }}'
                    }
                </script>
            <div class="btn-group" style="margin-top:10px; margin-bottom:10px;">
                <button  class="btn btn-primary bg-info">FOTO UTAMA</button>

                <button v-if="no_identity" class="btn btn-primary">CHEKIN GATE</button>
            </div>
            </div>
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Jenis Identitas</label>
                            <select disabled name="jenis_identity" id="" v-model="jenis_identity" class="form-control">
                                <option value="KTP">KTP</option>
                                <option value="KTP">SIM</option>
                                <option value="LAINYA">LAINYA</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label  for="">Nomer Identitas</label>
                            <input type="text" v-model="no_identity" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Nama Tamu</label>
                            <input type="text" v-model="nama"   class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="">Jenis Kelamin</label>
                            <select name=""   id="" v-model="jenis_kelamin" class="form-control">
                                <option value="1">LAKI LAKI</option>
                                <option value="0">PEREMPUAN</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Golongan Darah</label>
                            <select name="" id=""  v-model="golongan_darah" class="form-control">
                                <option value="A">A</option>
                                <option value="B">B</option>
                                <option value="O">O</option>
                                <option value="AB">AB</option>
                                <option value="-">-</option>

                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Nomer Telpon</label>
                            <input type="text"  @change="phoneNumber" v-model="nomer_telpon" class="form-control">
                           
                        </div>
                        <div class="form-group">
                            <label for="">Tempat Lahir</label>
                            <input type="text" v-model="tempat_lahir" class="form-control">
                           
                        </div>
                        <div class="form-group">
                            <label for="">Tanggal Lahir</label>
                            <input type="date" v-model="tanggal_lahir" class="form-control">
                           
                        </div>
                        <div class="form-group">
                            <label for="">Alamat</label>
                           <textarea name=""  v-model="alamat" class="form-control" id="" cols="30" rows="4"></textarea>
                        </div>
                    </div>
                </div>
            
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div class="row" id="videntity">
            <div class="col-md-3 text-center">
                <p><b>KTP</b></p>
                <img src="" :src="path_ktp" style="width:100%;" alt="" onerror="idferr(this)">
                <input type="hidden" name="path_ktp" v-model="path_ktp" >
            </div>
            <div class="col-md-3 text-center" style="background-color: #ddd">
                <p><b>SIM</b></p>
                <img src="" :src="path_sim" style="width:100%;" alt="" onerror="idferr(this)">
                <input type="hidden" name="path_sim" v-model="path_sim" >
            </div>
            <div class="col-md-3 text-center">
                <p><b>LAINYA</b></p>
                <img src="" :src="path_id_lainya" style="width:100%;" alt="" onerror="idferr(this)">
                <input type="hidden" name="path_id_lainya" v-model="path_id_lainya" >
            </div>
            <div class="col-md-3 text-center" style="background-color: #ddd">
                <p><b>FOTO GATE</b></p>
                <img src="" :src="path_foto_gate" style="width:50%;" alt="" onerror="idferrg(this)">
                <input type="hidden" name="path_foto_gate" v-model="path_foto_gate" >
            </div>
        </div>
        <script>
            function idferr(d){
                d.src="{{ asset('card-def.png') }}"
            }
            function idferrg(d){
                d.src="{{ asset('gate-def.svg') }}"
            }
        </script>
    </div>
</div>

<div id="picIdInput">
    <div  v-if="display" style="color:#fff; position:fixed; overflow-y:scroll; padding:15px; padding-top:80px; z-index:100; height:100vh; max-width:555px;; background:#343a40; right:0; top:0;">
        <div class="row">
            <h5><span><button @click="closePicInput" class="btn btn-sm btn-circle btn-primary"><i class="fa fa-times"></i></button></span> INPUT IDENTITY @{{ jenis }}</h5>
            <input type="hidden" v-model="jenis">
            <div class="col-md-12">
                <div id="cam-record" style="width:326px; overflow:hidden; background:#f1f1f1; padding:5px; height:246px; border:1px solid #fff;"></div>
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
@endsection


@section('js')
<script>

    var viden=new Vue({
        el:"#videntity",
        data:{
            path_foto_gate:"",
            path_ktp:"",
            path_sim:"",
            path_id_lainya:"",




        },
        methods:{

        }
    });

    var bc_provos = new BroadcastChannel('bcgate-{{Auth::User()->id}}');

    var testid='data:image/jpeg;base64,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';
    var vactionInput=new Vue({
        el:'#action_input',
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
            jenis_identity: 'KTP',
            no_identity: '',
            nama: '',
            foto: '',
            tempat_lahir: '',
            golongan_darah:'-',
            jenis_kelamin: 1,
            tanggal_lahir: null,
            alamat: "",
            nomer_telpon: "+62",
            keperluan:"",
            id_log:null


        },
        methods:{
            init:function(data){
                this.jenis_identity=data.jenis_identity;
                this.id_log=data.id_log;
                this.no_identity=data.identity_number;
                this.nama=data.nama;
                this.foto=data.foto;
                this.tempat_lahir=data.tempat_lahir;
                this.tanggal_lahir=data.tanggal_lahir;
                this.nomer_telpon=data.nomer_telpon;
                this.alamat=data.alamat;
                this.golongan_darah=data.golongan_darah;
                this.keperluan=data.keperluan;








            },
            namaTamu:function(){
                if(this.nama){
                    this.nama=this.nama.toUpperCase();
                }

            },
            numberIdentity:function(){
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


                }

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
                        }else{
                            this.nomer_telpon=window.them_phone;
                        }

                        
                }


            },
            bc:function(){
                window.bc_provos.postMessage(vinput.$data);
            }
        },
        watch:{
            nomer_telpon:'phoneNumber',
            no_identity:'numberIdentity',
            nama:'namaTamu',
            jenis_identity: 'bc',
            foto: 'bc',
            tempat_lahir: 'bc',
            golongan_darah:'bc',
            jenis_kelamin: 'bc',
            tanggal_lahir: 'bc',
            alamat: "bc",
        }
        
    });


    
  

    $('#no_iden').on('change',function(){
        var val=this.value;
        console.log(val);
        socket_ws.on("connection", () => {
        var t=socket_ws.emit("Finger-{{$fingerprint}}",data);
        console.log(t);
       
    });
    });

  
</script>


<script  type="application/javascript">
 var vpicItput=new Vue({
        el:'#picIdInput',
        data:{
            jenis:'KTP',
            display:false,
            pic_data:null,
            url_filled:false
        },
        methods:{
            extractData:function(){
                if(this.pic_data){
                    var data={
                        jenis:this.jenis,
                        pic_data:window.testid,
                    };
                    $.post('{{route('api.identity.extract')}}',data,function(res){
                        vinput.nama=res.data.nama;
                        vinput.no_identity=res.data.nik;
                        vinput.tanggal_lahir=res.data.tanggal_lahir;
                        vinput.tempat_lahir=res.data.tempat_lahir;
                        vinput.foto=res.data.foto;
                        vinput.alamat=res.data.alamat;
                        vinput.nomer_telpon=res.data.nomer_telpon;




                        console.log(res.data);
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
                        width: 400,
                        height: 300,
                        jpeg_quality: 90,
                        force_flash: false,
                        flip_horiz: false,
                        fps: 2
                    });
                    
                    window.Webcam.attach('#cam-record')
                },300);

            },
            takePic:function(){

                window.Webcam.snap( function(data_uri) {
                // window.vpicItput.pic_data=data_uri;

                window.vpicItput.pic_data=window.testid;
                window.Webcam.reset();
                    $('#cam-record').html( '<img clss="img-responsive" style="max-width:100%;" src="'+window.vpicItput.pic_data+'">');
                } );

            }
        },
        watch:{
            display:function(val){
                if(val){
                    this.displayingStat();
                }else{

                }
            },
            pic_data:function(val){
                if(val){
                    this.url_filled=true;
                }else{
                    this.url_filled=false;

                }
                console.log(vpicItput.url_filled);

            }
        }
    });

    vinput.init(<?=json_encode($data)?>);

</script>
@stop

