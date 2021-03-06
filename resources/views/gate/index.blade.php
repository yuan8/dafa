@extends('adminlte::page')

@section('content')
<script type="text/javascript">
     function errFoto(d){
        d.src='{{asset('tamu-def.png') }}'
    }
</script>
<div class="card">
    <div class="card-header with-border" id="venv">

       <form id="form_env" method="get">
        @can('is_gate')


        @endcan
           <b>DATA TAMU : </b>
            <span><div class="btn-group">
               <button type="button"  class="btn btn-primary"  >@{{ h }}</button>

                <input type="hidden" name="date" v-model="active_h">
            </div>

           @can('provos_and_gate')
             <div class="input-group" style="margin-bottom: 10px; margin-top: 10px" >
                <input type="date" name="start_date" class="form-control" v-model="start_date">
                <input type="date" name="end_date" v-model="end_date" class="form-control">
             </div>
           @endcan
        {{-- </span></b> --}}
        <b style="margin-left: 10px;">STATUS :
            <span>
                <div class="btn-group">

                    <button class="btn " v-on:click="status='GATE_CHECKIN'" v-bind:class="status=='GATE_CHECKIN'?'btn btn-primary':'btn-default'">TAMU MASUK (@{{rekap.count_in}})</button>
                   <button class="btn " v-on:click="status='GATE_CHECKOUT'"v-bind:class="status=='GATE_CHECKOUT'?'btn btn-primary':'btn-default'" >TAMU KELUAR (@{{rekap.count_out}})</button>


                    <input type="hidden" name="status" v-model=status>

                </div>
            </span>
        </b>
        <hr>
        <div class="row">
            <div class="col-md-5">
                @can('provos_and_gate')
                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="Search" value="{{$req->q}}">
                </div>
                @endcan
            </div>
        </div>
       </form>
    </div>
    <div class="card-body ">

       <div class="table-responsive">
        <table class="table-bordered table " id="list-visitor">
            <thead>
                <tr class="text-center">
                    <th>NO.</th>
                    <th>NOMOR KARTU</th>
                    {{-- <th>FOTO</th> --}}
                    <th>NAMA</th>
                    <th>NO. TELEPON</th>
                    <th>TUJUAN & KEPERLUAN</th>
                    {{-- <th>JENIS IDENTITAS</th> --}}
                    <th>STATUS</th>
                    <th>TANGGAL & JAM MASUK</th>
                    <th>OPERATOR MASUK</th>

                    <th>TANGGAL & JAM KELUAR</th>
                    <th>OPERATOR KELUAR</th>



                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_visitor as $key=> $v)
                 @php
                    $gate_ls=($v->gate_checkout?'CHECKOUT':($v->gate_checkin?'CHECKIN':($v->rekap?'REKAP':'')));
                @endphp

                <tr class="vis_">
                    <td class="text-center">{{($paging?

                        ((($req->page??1)-1)*$paging)+($key+1):$key+1)}}</td>
                    <td class="text-center">{{$v->nomer_kartu}}</td>
                    {{-- <td class="text-center"></td> --}}
                    {{-- <td class="text-center">
                        <img onclick="show_pic.show('{{url($v->foto??'tamu-def.png')}}')" src="{{asset($v->foto)}}" onerror="errFoto(this)" alt="" style="max-width:80px;">
                    </td> --}}
                    <td>
                        <p>
                        <b>
                        {{ Carbon\Carbon::parse($v->log_created_at)->format('ymd').'-'.$v->id_log }}</b></p>
                        {{ $v->nama }}
                        <p>{{
                            config('web_config.kategori_tamu')[$v->kategori_tamu]??$v->kategori_tamu
                        }}</p>
                        <p>  <span class="badge badge-primary">{{$v->instansi}}</span>
                        </p>
                    </td>
                    <td>{{ $v->nomer_telpon }}
                        <br>
                        <div class="btn-group" style="margin-top: 10px;">
                          {{--   <a target="_blank" href="https://api.whatsapp.com/send?phone={{str_replace('-', '', str_replace('+', '', $v->nomer_telpon))}}"  class="btn btn-success btn-xs">Whatapps</a> --}}
                            <button onclick="phone_call('{{$v->nama}}','{{$v->nomer_telpon}}')" class="btn btn-primary btn-xs">Phone Call</button>

                        </div>
                    </td>
                    <td>

                        <strong > {{implode(', ',json_decode($v->tujuan,true))}}</strong>

                        <p >{{ $v->keperluan }}</p>
                    </td>
                    {{-- <td><b>{{ $v->jenis_id }}</b>
                        <br>
                        <span class="badge badge-warning"><div style="font-size:14;">{{ $v->identity_number }}</div></span>
                       <div style="margin-top: 10px;">
                            <img onclick="show_pic.show('{{asset($v->path_identity)}}')" src="{{asset($v->path_identity)}}" class="img-thumbnail" style="max-width: 100px;">
                       </div>
                    </td> --}}
                    <td>
                            @switch($gate_ls)
                                @case('PROVOS')
                                    TAMU TERDAFTAR DI PROVOS
                                    @break
                                @case('CHECKIN')
                                    MASUK
                                    @break

                             @case('CHECKOUT')
                                    @if($v->checkout_from_gate)
                                    KELUAR
                                    @else
                                   <span class="text-red">MEMBATALKAN</span> KUJUNGAN
                                    @endif
                                @break
                                 @default
                                 KELUAR
                            @endswitch



                    </td>
                    <td>
                        {{ $v->gate_checkin?Carbon\Carbon::parse($v->gate_checkin)->format('d/m/Y h:i a'):'-' }}

                    </td>
                    <td>
                        {{$v->nama_gate_handle??'-'}}
                    </td>
                    <td>
                         {{ $v->gate_checkout?Carbon\Carbon::parse($v->gate_checkout)->format('d/m/Y h:i a'):'-' }}
                    </td>
                    <td>
                        {{$v->nama_gate_out_handle??'-'}}

                    </td>
                    <td>
                              <div class="btn-group-vertical">
                            @can('is_gate')
                                @if($gate_ls=='CHECKIN')

                                @endif
                            @endcan
                            @can('gate_check_out_provos')
                                @if($gate_ls=='PROVOS')
                                    <a href="javascript:void(0)" onclick="batalkan({{$v->id_log}})" class="btn btn-danger btn-sm">BATALKAN KUNJUNGAN</a>
                                    <a href="{{route('g.input',['id'=>$v->id_log,'slug'=>Str::slug($v->nama),'fingerprint'=>$fingerprint])}}" class="btn btn-primary btn-sm">CHECKIN GATE</a>
                                @endif
                                @if($gate_ls=='CHECKIN')
                                    <a href="{{route('g.checkout',['id'=>$v->id_log,'slug'=>Str::slug($v->nama),'fingerprint'=>$fingerprint])}}" class="btn btn-danger btn-sm">CHECKOUT GEDUNG</a>
                                @endif
                                @if($gate_ls=='CHECKOUT')

                                @endif
                            @endcan
                        </div>

                    </td>






                </tr>

                @endforeach
            </tbody>
        </table>
       </div>
       @php
       @endphp
       @if($paging)
       {{$data_visitor->links()}}
       @endif


    </div>
</div>

<div class="modal fade" id="modal-id-phone-call">

</div>

<div class="modal fade modal-danger" id="modal-t-danger">

</div>
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
    function batalkan(id){
        $.get('{{route('batalkan.kunjungan')}}/'+id,{},function(res){
            $('#modal-t-danger').html(res);
            $('#modal-t-danger').modal();
        });
    }
</script>
<script type="text/javascript">
    var venv=new Vue({
        el:'#venv',
        data:{
            h:'{{ Carbon\Carbon::now()->format('d F Y') }}',
            h_1:'{{ Carbon\Carbon::now()->addDays(-1)->format('d F Y') }}',
            h_2:'{{ Carbon\Carbon::now()->addDays(-2)->format('d F Y') }}',
            h_3:'{{ Carbon\Carbon::now()->addDays(-3)->format('d F Y') }}',
            active_h:'{{$active_h->format('d F Y')}}',
            status:'{{$status}}',
            start_date:'{{$date_start}}',
            end_date:'{{$date_end}}',
            rekap:<?=json_encode($rekap_tamu)?>,
        },
        methods:{
            change_env:function(d){
                this.active_h=d;
                $('#form_env').submit();
            },
            submit_form:function(){
                 setTimeout(function(){
                $('#form_env').submit();
                },500);
            }
        },
        watch:{
            status:function(v,old){
                setTimeout(function(){
                $('#form_env').submit();
                },500);
            },
            start_date:function(){
                this.submit_form();
            },
            end_date:function(){
                this.submit_form();
            }
        }
    })
</script>
@stop
