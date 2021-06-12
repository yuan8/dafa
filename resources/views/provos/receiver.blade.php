@extends('layouts.receiver')


@section('content')
 <script type="text/javascript">
                function errFoto(d){
                    d.src='{{asset('tamu-def.png') }}'
                }
            </script>
<div class="card">
    <div class="card-header with-border">
        <h4><b>CHECK IN</b></h4>
    </div>
    <div class="card-body">
        <div class="row" id="vinput">
            <div class="col-md-3">
                <div class="text-center" style="width:100%; min-height:100px; border:1px solid #222">
                    <img src="" :src="foto" alt="" onerror="errFoto(this)" style="max-width:100%;">
                </div>

            </div>

            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Jenis Identitas</label>
                           <p>@{{ jenis_identity  }}</p>
                           <hr>
                        </div>
                        <div class="form-group">
                           <label for="">Nomer Identitas</label>
                           <p>@{{ no_identity }}</p>
                           <hr>

                        </div>
                        <div class="form-group">
                            <label for="">Nama Tamu</label>
                           <p>@{{ nama }}</p>
                           <hr>
                        </div>
                        <div class="form-group">
                            <label for="">Jenis Kelamin</label>
                          <p>@{{ jenis_kelamin==1?'LAKI LAKI':'PEREMPUAN' }}</p>
                          <hr>
                        </div>
                        <div class="form-group">
                            <label for="">Golongan Darah</label>
                            <p> @{{ golongan_darah }}</p>
                           <hr>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="">Nomer Telpon</label>
                            <p>@{{ nomer_telpon }}</p>
                           <hr>

                        </div>
                        <div class="form-group">
                            <label for="">Tempat Lahir</label>
                            <p>@{{ tempat_lahir }}</p>
                           <hr>
                        </div>
                        <div class="form-group">
                            <label for="">Tanggal Lahir</label>
                            <p>@{{ tanggal_lahir }}</p>
                           <hr>

                        </div>
                        <div class="form-group">
                            <label for="">Alamat</label>
                            <p>@{{ alamat }}</p>
                           <hr>
                        </div>
                        <div class="form-group">
                            <label for="">Keperluan</label>
                            <p><b>@{{ _.pluck(tujuan_json,'label').join(', ') }}</b></p>

                            <p>@{{ keperluan }}</p>
                           <hr>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

@stop

@section('js')
<script>
var bc_provos = new BroadcastChannel('bcprovos-{{$fingerprint}}');

 var vinput = new Vue({
        el: '#vinput',
        data: {
            jenis_identity: '',
            no_identity: '',
            nama: '',
            foto: '',
            tempat_lahir: '',
            golongan_darah:'-',
            jenis_kelamin: '',
            tanggal_lahir: '',
            alamat: "",
            keperluan: "",
            tujuan_json: [],
            nomer_telpon: "",


        },
        methods:{
            changedata:function(data){
                for(i in data){
                    this[i]=data[i];
                }
            }
        },
        watch:{

        }
});

bc_provos.onmessage = function (ev) {
    window.res=ev.data;
    vinput.changedata(ev.data);

 }


</script>
@stop
