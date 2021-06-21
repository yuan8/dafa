@extends('adminlte::page')

@section('content')
<div class="card">
   
    <div class="card-header with-border" id="venv">

       <form id="form_env" method="get">
        @can('is_gate')
        @endcan
           <div class="row ">
               <div class="col-md-6">
                   <div class="form-group">
                       <label>MULAI</label>
                       <input type="date" class="form-control" name="start" v-model="start">
                   </div>
               </div>
               <div class="col-md-6">
                   <div class="form-group">
                       <label>HINGGA</label>
                       <input type="date" class="form-control" name="end" v-model="end">
                   </div>
               </div>
           </div>


        <hr>
        <div class="row">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="">STATUS</label>
                    <select class="form-control"  name="status" v-model="status" id="">
                        <option value="GATE_CHECKIN">MASUK</option>
                        <option value="GATE_CHECKOUT">KELUAR</option>
                    </select>
                     <div style="margin-top: 10px;">
                         <label>Total Data: {{$data_visitor->total()}} Data</label>
                     </div>

                </div>
            </div>
            <div class="col-md-4">
                <label>Export</label>
                <input type="hidden" name="export" v-model="export_file">
               <div>
                    <div class="btn-group">
                    <button v-on:click="export_excel('EXCEL')" class="btn btn-success">EXCEL</button>
                    <button v-on:click="export_excel('PDF')" class="btn btn-primary">PDF</button>
                </div>
               </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                <input type="hidden" name="tujuan_json" v-model="JSON.stringify(tujuan_json)">
                    <label>TUJUAN <button v-on:click="clear_tujuan()" class="btn-circle btn-xs btn-primary">Clear</button></label>
                    <v-select class="vue-select2" multiple=""
                        :options="options" v-model="tujuan_json"
                        :searchable="true" language="en-US">
                    </v-select>


                </div>
            </div>
        </div>
       </form>
    </div>
    <div class="card-body ">
       
       <div class="table-responsive">
        <table class="table-bordered table " id="list-visitor">
            <thead>
                <tr>
                    <th>FOTO</th>
                    <th>NAMA</th>
                    <th>NO TELEPON</th>
                    <th>TUJUAN & KEPERLUAN</th>
                    <th>JENIS IDENTITAS</th>
                    <th>STATUS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_visitor as $v)
                 @php
                        $gate_ls=($v->gate_checkout?'CHECKOUT':($v->gate_checkin?'CHECKIN':($v->provos_checkin?'PROVOS':'')));
                    @endphp

                <tr class="vis_">
                    <td class="text-center">
                        {{-- <img src="{{asset($v->foto)}}" onerror="errFoto(this)" alt="" style="max-width:80px;"> --}}
                        <img onclick="show_pic.show('{{url($v->foto??'tamu-def.png')}}')" src="{{asset($v->foto)}}" onerror="errFoto(this)" alt="" style="max-width:80px;">
                    </td>
                    <td><p><b>{{ Carbon\Carbon::parse($v->log_created_at)->format('ymd').'-'.$v->id_log }}</b></p>
                        {{ $v->nama }}
                        <p>{{
                            config('web_config.kategori_tamu')[$v->kategori_tamu]??$v->kategori_tamu
                        }}</p>
                        <p><span class="badge badge-primary">{{$v->instansi}}</span>
                        </p>
                    </td>

                    </td>

                    <td>
                        {{ $v->nomer_telpon }}
                        <br>
                        <div class="btn-group" style="margin-top: 10px;">
                          {{--   <a target="_blank" href="https://api.whatsapp.com/send?phone={{str_replace('-', '', str_replace('+', '', $v->nomer_telpon))}}"  class="btn btn-success btn-xs">Whatapps</a> --}}
                            <button  onclick="phone_call('{{$v->nama}}','{{$v->nomer_telpon}}')" class="btn btn-primary btn-xs">Phone Call</button>

                        </div>
                    </td>
                    <td>

                        <strong > {{implode(', ',json_decode($v->tujuan,true))}}</strong>

                        <p >{{ $v->keperluan }}</p>
                    </td>
                    <td><b>{{ $v->jenis_id }}</b>
                        <br>
                        <span class="badge badge-warning"><div style="font-size:14;">{{ $v->identity_number }}</div></span>
                       <div style="margin-top: 10px;">
                            {{-- <img src="{{asset($v->path_identity)}}" class="img-thumbnail" style="max-width: 100px;"> --}}
                            <img onclick="show_pic.show('{{asset($v->path_identity)}}')" src="{{asset($v->path_identity)}}" class="img-thumbnail" style="max-width: 100px;">
                       </div>
                    </td>
                    <td>
                            @switch($gate_ls)
                                @case('PROVOS')
                                    TAMU TERDAFTAR DI PROVOS
                                    @break
                                @case('CHECKIN')
                                    TAMU TELAH MEMASUKI GATE
                                    @break

                             @case('CHECKOUT')
                                    TELAH MENYELESAIKAN KUNJUNGAN
                                    @break
                                    <p>{{ Carbon\Carbon::parse($v->gate_checkout)->format('d/m/Y h:i a') }}</p>

                                @default
                                        TELAH MENYELESAIKAN KUNJUNGAN
                            @endswitch



                    </td>





                </tr>
                <tr class="vis_">
                    <td colspan="7">
                        <div class="cd-horizontal-timeline loaded">
                            <div class="timeline">
                                <div class="events-wrapper">
                                  <div class="events" style="width: 100%;">
                                        <ol>
                                            <li><a href="#" data-date="{{ $v->gate_checkin }}" style="left: 20%;" class="{{ $gate_ls=='CHECKIN'?'selected':($gate_ls=='PROVOS'?'older-event':'') }}">MASUK {{(in_array($gate_ls, ['CHECKIN','CHECKOUT'])?Carbon\Carbon::parse($v->gate_checkin)->format('d/m/Y h:i a'):'-')}}
                                                <b class="text-center">(oleh : {{$v->nama_gate_handle}})</b>
                                            </a>
                                            </li>

                                            <li><a href="#" data-date="{{ $v->gate_checkout }}" style="left: 70%;" class="{{ $v->gate_checkout?'selected':'' }} }}">KELUAR {{(in_array($gate_ls, ['CHECKOUT'])?Carbon\Carbon::parse($v->gate_checkout)->format('d/m/Y h:i a'):'-')}}
                                                <b class="text-center">(oleh : {{$v->nama_gate_out_handle}})</b>
                                            </a></li>

                                        </ol>
                                        <span class="filling-line" aria-hidden="true" ></span>
                                    </div>
                                    <!-- .events -->
                                </div>
                                <!-- .events-wrapper -->

                                <!-- .cd-timeline-navigation -->
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
       </div>
       {{$data_visitor->links('')}}

    </div>
    <div class="box-footer">

    </div>

</div>

<div class="modal fade" id="modal-id-phone-call">

</div>

<form id="form-export">
    <input type="hidden" name="q" v-model="q">
    <input type="hidden" name="q" v-model="q">

</form>
@stop

@section('js')
<script type="text/javascript">
    var count=1;
    var element='<tr class="vis_ animate__animated  animate__flash"><td class="text-center"> <img src="http://localhost/daftar/storage/VISITOR/1/foto.jpg" alt="" style="max-width:80px;"></td><td>NGABALIN</td><td>+62877-7107-9782</td><td>ANU</td><td>KTP</td><td>3522-2627-0495-0001</td><td></td></tr>'
    +'<tr class="vis_ animate__animated animate__flash"><td colspan="7"><div class="cd-horizontal-timeline loaded"><div class="timeline"><div class="events-wrapper"><div class="events" style="width: 1800px;"><ol><li><a href="#0" data-date="16/01/2017" class="older-event" style="left: 120px;">Provos 16 Jan 2019 12:03 am</a></li><li><a href="#0" data-date="28/02/2017" style="left: 400px;" class="older-event">Gate 16 Jan 2019 12:03 am</a></li><li><a href="#0" data-date="20/04/2017" style="left: 800px;" class="selected">20 Mar</a></li></ol> <span class="filling-line" aria-hidden="true" style="transform: scaleX(0.281506);"></span></div></div></div></div></td></tr>';

    function add(){
        var dom=element.replace('NGABALIN','NGABALIN '+count);
        $('#list-visitor tbody').prepend(dom);

    }
    var state='{{$req->check??'PROVOS'}}';
    @if(config('web_config.broadcast_network'))
    if(state=='PROVOS'){
        window.Echo.channel('dh_provos-channel')
        .listen('.new-check-in', (e) => {
            window.add();

        });
    }

    @endif


    function phone_call(name,phone){
        $.post('{{route('generate.qr.phone_call')}}',{nama:name,nomer_telpon:phone},function(res){
            $('#modal-id-phone-call').html(res);
            $('#modal-id-phone-call').modal();
        });
    }


</script>
<script type="text/javascript">


    function errFoto(d){
        d.src='{{asset('tamu-def.png') }}'
    }
    @php
    $option=[];
        $option_def=config('web_config.tujuan_tamu')?(config('web_config.tujuan_tamu')):[];
        foreach ($option_def as $key => $value) {
            # code...
            $option[]=[
                'label'=>$value['name'],
                'code'=>$value['tag']
            ];
        }
    @endphp

    var venv=new Vue({
        el:'#venv',
        data:{
            start:'{{$start}}',
            end:'{{$end}}',
            status:'{{$status}}',
            export_file:null,
            tujuan:<?=count($tujuan)?json_encode($tujuan??[]):'[]'?>,
            tujuan_json:<?=count($tujuan_json)?json_encode($tujuan_json??[]):'[]'?>,

            options:<?=count($option)?json_encode($option):'[]'?>

        },
        methods:{
            export_excel:function(d=null){
                this.export_file=d;
            },
            clear_tujuan:function(){
                this.tujuan_json=[];
            },
            toArray:function(){

            },

        },
        watch:{
            status:function(v,old){
                $('#form_env').submit();
            },
            end:function(v,old){
                $('#form_env').submit();
            },
            start:function(v,old){
                $('#form_env').submit();
            },
            export_file:function(v,old){
                $('#form_env').submit();
            },
            tujuan:function(v,old){

            },
            tujuan_json:function(v,old){
                setTimeout(function(){
                 $('#form_env').submit();

                },500);


            }
        }

    })

</script>
@stop
