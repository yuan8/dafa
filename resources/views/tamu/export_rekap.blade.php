@extends('layouts.export')
@section('content')
<style type="text/css">
	tr td{
		vertical-align: middle!important;
	}
</style>
@php
		$d1=$day->format('d/m/Y');
		$d2=$day_last->format('d/m/Y');
		$tujuan=collect($tujuan_json??[])->pluck('label')->toArray();
@endphp
<h3 class="text-center">REKAP {{$req->jenis_tamu=='KHUSUS'?'TAMU KHUSUS':($req->jenis_tamu=='ALL'?'TAMU':'TAMU NON KHUSUS')}} {{
($status=='GATE_CHECKIN')?
'MASUK'
:
(
	($status=='GATE_CHECKOUT')?
	'KELUAR':'')}} <span><p>{{implode(', ',$tujuan)}}</p></span> </h3>
	<p class="text-center">Waktu Export : {{Carbon\Carbon::now()->format('d/m/Y h:i a')}}</p>

<p class="text-center">Kunjungan {{$d1==$d2?$d1:$d1.' - '.$d2}}</p>
<hr>
	<table class="table-bordered table tdata" >
		
		<thead>
                <tr>
                    <th>NO</th>
                   @if($req->jenis_table=='LENGKAP')
                    <th >FOTO</th>
                    @endif
                   @if($req->jenis_table=='LENGKAP')
                    <th >NO IDENTITAS</th>
                    @endif

                    <th>NAMA</th>
                    <th>JENIS TAMU</th>
                   @if($req->jenis_table=='LENGKAP')
                    <th >INSTANSI </th>
                    @endif

                    <th>TUJUAN</th>
                    <th>KEPERLUAN</th>
                    <th>JAM MASUK</th>
                   @if($req->jenis_table=='LENGKAP')
                    <th >USER HANDLE MASUK </th>
                    @endif

                    <th>JAM KELUAR</th>
                   @if($req->jenis_table=='LENGKAP')
                    <th >USER HANDLE KELUAR </th>
                    @endif

                </tr>
            </thead>
            <tbody>
            	@php
            		$foto_def=json_encode(file_get_contents('tamu-def.png'));
            	@endphp
                @foreach($data as  $key=>$v)
                 <tr class="{{$v->status_out?'bg-warning':'bg-success'}}">
                 	<td>{{$key+1}}</td>
                 	@php
                 		$foto='';
                 		if($v->foto){
                 			$foto=asset($v->foto);
                 		}else{
                 			$foto=$foto_def;
                 		}
                 	@endphp
                 	@if($req->jenis_table=='LENGKAP')
                 	<td  >  <img src="{{($foto)}}" alt="" style="max-width:80px;"></td>
                    @endif

                 	@if($req->jenis_table=='LENGKAP')
                 	<td  >{{$v->identity_number}} </td>
                    @endif


                 	<td>{{$v->nama}}</td>
                 	<td class="{{$v->tamu_khusus?'bg-maroon':''}}">{{$v->tamu_khusus?''.($v->jenis_tamu_khusus):$v->kategori_tamu }}</td>
                 	@php
                 			$tujuan=collect( (CV::build_from_array('tujuan_tamu',json_decode($v->tujuan??'[]'))))->pluck('label')->toArray();
                 		@endphp
                 	@if($req->jenis_table=='LENGKAP')
                 	<td  >{{$v->instansi}} </td>
                    @endif


                 	<td>{{implode(' , ',($tujuan??[]))}}</td>
                 	<td>{{$v->keperluan??'-'}}</td>
                 	<td>{{$v->gate_checkin?Carbon\Carbon::parse($v->gate_checkin)->format('d F Y h:i a'):'-'}}</td>
                 	@if($req->jenis_table=='LENGKAP')
                 	<td  >{{$v->nama_gate_handle}}</td>
                    @endif

                 	<td>{{$v->gate_checkout?Carbon\Carbon::parse($v->gate_checkout)->format('d F Y h:i a'):'-'}}</td>
                 	@if($req->jenis_table=='LENGKAP')
                 	<td  >{{$v->nama_gate_out_handle??'-'}}</td>
                    @endif


                 </tr>
                @endforeach
            </tbody>



	</table>
@stop