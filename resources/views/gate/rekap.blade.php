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
       
           <b>DATA TAMU  : <span>
            @can('is_admin')
            <div class="input-group" >
                <input type="date" name="start_date" class="form-control" v-model="Date.parse(date_start)">
                <input type="date" name="end_date" v-model="Date.parse(date_end)" class="form-control">
            </div>
            @endcan
            <div class="btn-group">
             
             
                 <input type="hidden" name="date" v-model="active_h">
            </div>
        </span>
       
    </b>
       
        <hr>

        <div class="row">
            <div class="col-md-6">
                 <b style="margin-left: 10px;">STATUS :
            <span>
                <div class="btn-group">
                  
                    <button type="button" class="btn " v-on:click="status='GATE_CHECKIN'" v-bind:class="status=='GATE_CHECKIN'?'btn btn-primary':'btn-default'">TAMU MASUK (@{{rekap.count_in??0}})</button>
                   <button type="button" class="btn " v-on:click="status='GATE_CHECKOUT'"v-bind:class="status=='GATE_CHECKOUT'?'btn btn-primary':'btn-default'" >TAMU KELUAR (@{{rekap.count_out??0}})</button>


                    <input type="hidden" name="status" v-model=status>

                </div>
            </span>
        </b>
                
            </div>
            <div class="col-md-5">

                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="Search" value="{{$req->q}}">
                </div>
            </div>
        </div>
        <hr>
       </form>
    </div>
    <div class="card-body ">

       <div class="table-responsive">
        <table class="table-bordered table " id="list-visitor">
            <thead>
                <tr>
                    <th>NO</th>
                    <th>NAMA</th>
                    <th>TUJUAN</th>
                    <th>KEPERLUAN</th>
                    <th>JAM MASUK</th>
                    <th>JAM KELUAR</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_visitor as $v)
                 
                @endforeach
            </tbody>
        </table>
       </div>
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
            h_def:'{{ Carbon\Carbon::now()->format('d F Y') }}',
            h_start:'{{ $date_start??Carbon\Carbon::now()->format('d F Y') }}',
            h_end:'{{ $date_end??Carbon\Carbon::now()->format('d F Y') }}',
            h:'{{ Carbon\Carbon::now()->format('d F Y') }}',
            h_1:'{{ Carbon\Carbon::now()->addDays(-1)->format('d F Y') }}',
            h_2:'{{ Carbon\Carbon::now()->addDays(-2)->format('d F Y') }}',
            h_3:'{{ Carbon\Carbon::now()->addDays(-3)->format('d F Y') }}',
            active_h:'{{$active_h->format('d F Y')}}',
            status:'{{$status}}',
            rekap:<?=json_encode($rekap_tamu)?>
        },
        methods:{
            change_env:function(d){
                this.active_h=d;
                this.check_date(this.status);  

                 setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
            check_date:function(v){
                var v=this.status;

                console.log(v,[this.h,this.h_1,this.h_2,this.h_3],([this.h,this.h_1,this.h_2,this.h_3].includes(this.active_h)));
                 if(['GATE_CHECKIN','GATE_CHECKOUT'].includes(v)){
                    if(!([this.h,this.h_1,this.h_2,this.h_3].includes(this.active_h))){
                        this.active_h=this.h_def;
                    }
                }
            }
        },

        watch:{
            active_h:function(){
                this.check_date(this.status);  
                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
            status:function(v,old){
                this.check_date(v);  
                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            }
        }

    });

    setTimeout(function(){
        window.venv.check_date();
    },500);

</script>
@stop
