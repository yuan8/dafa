@extends('adminlte::page')

@section('content')
<script type="text/javascript">
     function errFoto(d){
        d.src='{{asset('tamu-def.png') }}'
    }
</script>
  <style type="text/css">
        .bg-warning {
            background-color: #fff5d9!important;
            color:#222!important;
        }
            .bg-success {
            background-color: #d8f9df!important;
            color:#222!important;
        }
            .bg-maroon {
            background-color: #ffd0e1!important;
            color:#222!important;
    }
    </style>

<div class="card" id="venv">
    <div class="card-header with-border" >

       <form id="form_env" method="get">

           <b>DATA TAMU  : <span>
           {{--  <div class="btn-group">
            <button type="button" class="btn btn-primary" >@{{h}}</button>
            </div> --}}

            <div class="input-group" >
                <input type="date" name="start_date" class="form-control" v-model="date_start">
                <input type="date" name="end_date" v-model="date_end" class="form-control">
            </div>

           {{--  @can('provos_and_gate')
            <div class="btn-group">

                @can('is_admin')
            	<button type="button" v-on:click="active_date=h1" v-bind:class="active_date==h1?'btn btn-primary':'btn btn-default'" >@{{h1}}</button>
            	<button type="button" v-on:click="active_date=h2" v-bind:class="active_date==h2?'btn btn-primary':'btn btn-default'" >@{{h2}}</button>
            	<button type="button" v-on:click="active_date=h3" v-bind:class="active_date==h3?'btn btn-primary':'btn btn-default'" >@{{h3}}</button>
                @endcan
            	<input type="hidden" name="start_date" v-model="date_start">
            	<input type="hidden" name="end_date" v-model="date_start">

            </div>

            @endcan --}}

        </span>

    </b>

        <hr>

        <div class="row">
            <div class="col-md-6">
                 <b style="margin-left: 10px;">STATUS :
            <span>
                <div class="btn-group">


                    <button type="button" class="btn " v-on:click="status='GATE_CHECKIN'" v-bind:class="status=='GATE_CHECKIN'?'btn btn-primary':'btn-default'">TAMU MASUK (@{{rekap.count_in.count_data??0}})</button>
                   <button type="button" class="btn " v-on:click="status='GATE_CHECKOUT'"v-bind:class="status=='GATE_CHECKOUT'?'btn btn-primary':'btn-default'" >TAMU KELUAR (@{{rekap.count_out.count_data??0}})</button>

                    <input type="hidden" name="status" v-model=status>

                </div>
            </span>
        </b>

            </div>
            <div class="col-md-4">

                <div class="form-group">
                    <input type="text" name="q" class="form-control" placeholder="Search" v-model="q">
                </div>
            </div>
            <div class="col-md-2">
            	<div class="btn-group">
            		<button type="button" v-on:click="_export('PDF')"class="btn btn-primary">PDF</button>
            		<button type="button" v-on:click="_export('EXCEL')" class="btn btn-success">Excel</button>
            	</div>
            </div>

        </div>
        <hr>
        <div class="row">
        	<div class="col-md-6">
        		<label>JENIS TAMU : </label>
        		<div class="btn-group">
        			 <button type="button" class="btn " v-on:click="jenis_tamu='ALL'"v-bind:class="jenis_tamu=='ALL'?'btn btn-primary':'btn-default'" >SEMUA @{{parseInt(rekap_inher.count_khusus??0)+parseInt(rekap_inher.count_non_khusus??0)}}</button>
                    <button type="button" class="btn " v-on:click="jenis_tamu='TAMU_KHUSUS'" v-bind:class="jenis_tamu=='KHUSUS'?'btn btn-primary':'btn-default'">TAMU KHUSUS (@{{parseInt(rekap_inher.count_khusus??0)}})</button>
                   <button type="button" class="btn " v-on:click="jenis_tamu='TAMU'"v-bind:class="jenis_tamu=='TAMU'?'btn btn-primary':'btn-default'" >TAMU (@{{parseInt(parseInt(rekap_inher.count_non_khusus??0))}})</button>

                    <input type="hidden" name="jenis_tamu" v-model=jenis_tamu>

                </div>
        	</div>
        	<div class="col-md-6">
        		<label>TUJUAN</label>
        		<input type="hidden" name="tujuan_json" v-model="JSON.stringify(tujuan_json)">
                    <label> <button type="button" v-on:click="clear_tujuan()" class="btn-circle btn-xs btn-primary">Clear</button></label>
                    <v-select class="vue-select2" multiple=""
                        :options="options" v-model="tujuan_json"
                        :searchable="true" language="en-US">
                    </v-select>
        	</div>
        </div>
       </form>
    </div>
    <form id="form-export" method="get" >
    	<input type="hidden" name="start_date" v-model="date_start">
    	<input type="hidden" name="end_date" v-model="date_end">
    	<input type="hidden" name="q" v-model="q">
    	<input type="hidden" name="status" v-model="status">
    	<input type="hidden" name="jenis_tamu" v-model="jenis_tamu">
    	<input type="hidden" name="jenis_table" v-model="jenis_table">

    	<input type="hidden" name="tujuan_json" v-model="JSON.stringify(tujuan_json)">
    	<input type="hidden" name="v_export" v-model="v_export">

    </form>
    <div class="card-body " style="padding-top: 10px;">
    	<div class="btn-group" style="margin-bottom: 10px;">
    		<button type="button" v-on:click="jenis_table='RINGKAS'" v-bind:class="(jenis_table=='RINGKAS')?'btn btn-primary':'btn btn-default'">RINGKAS</button>

    		<button type="button" v-on:click="jenis_table='LENGKAP'" v-bind:class="(jenis_table=='LENGKAP')?'btn btn-primary':'btn btn-default'">LENGKAP</button>
    	</div>

    	<p>TOTAL DATA : {{number_format(count($data))}} KUNJUNGAN <b>-</b> <a href="{{route('g.index')}}"><b style="color: #ff0000">TAMU DI DALAM GEDUNG HARI INI <b style="color: #292cff">(@{{rekap.count_in.count_data??0}})</b> TANGGAL</b><b style="color: #1e22f5"> (@{{h}})</b></a></p>
       <div class="table-responsive">
        <table class="table-bordered table " id="list-visitor">
            <thead>
                <tr class="text-center">
                    <th>NO</th>
                    <th v-if="jenis_table=='LENGKAP'">FOTO</th>
                    <th v-if="jenis_table=='LENGKAP'">NO IDENTITAS</th>
                    <th>NAMA</th>
                    <th>KATEGORI & JENIS TAMU</th>
                    <th v-if="jenis_table=='LENGKAP'">INSTANSI </th>
                    <th>TUJUAN</th>
                    <th>KEPERLUAN</th>
                    <th>TANGGAL & JAM MASUK</th>
                    <th v-if="jenis_table=='LENGKAP'">OPERATOR MASUK </th>
                    <th>TANGGAL & JAM KELUAR</th>
                    <th v-if="jenis_table=='LENGKAP'">OPERATOR KELUAR </th>

                </tr>
            </thead>
            <tbody>
                @foreach($data as $key=>$v)
                 <tr class="{{$v->status_out?'bg-warning':'bg-success'}} text-center">
                 	<td class="text-center">{{$key+1}}</td>
                 	<td  v-if="jenis_table=='LENGKAP'">  <img onclick="show_pic.show('{{url($v->foto??'tamu-def.png')}}')" src="{{asset($v->foto)}}" onerror="errFoto(this)" alt="" style="max-width:80px;"></td>
                 	<td  v-if="jenis_table=='LENGKAP'">{{$v->identity_number}} </td>

                 	<td><a href="{{route('g.tamu.view',['id'=>$v->id_tamu,'slug'=>Str::slug($v->nama)])}}">{{$v->nama}}</a></td>
                 	<td class="{{$v->tamu_khusus?'bg-maroon':''}}">{{$v->tamu_khusus?''.($v->jenis_tamu_khusus):$v->kategori_tamu }}</td>
                 	@php
                 			$tujuan=collect( (CV::build_from_array('tujuan_tamu',json_decode($v->tujuan??'[]'))))->pluck('label')->toArray();
                 		@endphp
                 	<td  v-if="jenis_table=='LENGKAP'">{{$v->instansi}} </td>

                 	<td>{{implode(' , ',($tujuan??[]))}}</td>
                 	<td>{{$v->keperluan??'-'}}</td>
                 	<td>{{$v->gate_checkin?Carbon\Carbon::parse($v->gate_checkin)->format('d F Y h:i a'):'-'}}</td>
                 	<td  v-if="jenis_table=='LENGKAP'">{{$v->nama_gate_handle}}</td>
                 	<td>{{$v->gate_checkout?Carbon\Carbon::parse($v->gate_checkout)->format('d F Y h:i a'):'-'}}</td>
                 	<td  v-if="jenis_table=='LENGKAP'">{{$v->nama_gate_out_handle??'-'}}</td>


                 </tr>
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
            date_start:'{{ $date_start??Carbon\Carbon::now()->format('Y-m-d') }}',
            date_end:'{{ $date_end??Carbon\Carbon::now()->format('Y-m-d') }}',
            status:'{{$status}}',
            q:'{{$req->q}}',
            active_date:'{{$date_2}}',
            h:'{{Carbon\Carbon::now()->format('d F Y')}}',
            h1:'{{Carbon\Carbon::now()->addDays(-1)->format('d F Y')}}',
            h2:'{{Carbon\Carbon::now()->addDays(-2)->format('d F Y')}}',
            h3:'{{Carbon\Carbon::now()->addDays(-3)->format('d F Y')}}',
            jenis_table:'{{$req->jenis_table??'RINGKAS'}}',
            jenis_tamu:'{{$req->jenis_tamu??'ALL'}}',
            v_export:null,
           	tujuan_json:<?=count($tujuan_json??[])?json_encode($tujuan_json??[]):'[]'?>,
            options:<?=count($option)?json_encode($option):'[]'?>,
            rekap:<?=json_encode($rekap)?>,
            rekap_inher:{},
        },
        methods:{
        	clear_tujuan:function(){
        		this.tujuan_json=[];
        	},
            _export:function(j){
            	this.v_export=j;
            	setTimeout(function(){
                    $('#form-export').submit();

                },500);
            },

            change_status:function(){
            	var v=this.status;
            	if(v=='GATE_CHECKIN'){
					this.rekap_inher=this.rekap.count_in;
            	}else{
            		this.rekap_inher=this.rekap.count_out;
            	}
            }

        },

        watch:{
        	jenis_table:function(v){
        		console.log(v);
        	},
            date_end:function(){

                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
            date_start:function(){

                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
             active_date:function(v){
            		this.date_start=v;
            		this.date_end=v;

            },
            q:function(v){
            	if(window.qTms){
            		clearTimeout(window.qTms);
            	}
                window.qTms=setTimeout(function(){
                    $('#form_env').submit();
                },2000);
            },
            status:function(v,old){
            	this.change_status();

                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
            jenis_tamu:function(v,old){

                setTimeout(function(){
                    $('#form_env').submit();

                },500);
            },
            tujuan_json:function(){
            	   setTimeout(function(){
                    $('#form_env').submit();

                },500);
            }
        }

    });


    window.venv.change_status();



</script>
@stop
