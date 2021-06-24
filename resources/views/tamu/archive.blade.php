@extends('adminlte::page')

@section('content')
<H4><b>MASTER DATA TAMU</b></H4>

<div class="card">
    <div class="card-header with-border">
        <form action="{{url()->full()}}" method="get">
        	<input type="text" class="form-control" name="q" value ="{{$req->q}}" placeholder="search">
        </form>
    </div>
    <div class="card-body">
    	<div class="table-responsive">
    		<table class="table table-bordered">
    			<thead>
    				<tr class="text-center">
    					<th>NO.</th>
    					<th>FOTO</th>
                        <th>CATATAN</th>
                        <th>JENIS TAMU</th>
    					<th>IDENTITAS</th>
    					<th>NAMA</th>
    					<th>NOMOR TELEPON</th>
    					<th>PEKERJAAN</th>
    					<th>GOL DARAH</th>
    					<th>JENIS KELAMIN</th>
    					<th>TEM/TGL LAHIR</th>
    					<th>ALAMAT</th>
    					<th>AKSI</th>
    				</tr>
    			</thead>
    			<tbody>
    				@foreach ($data as $key=>$d)

    				<tr>
                        <td>{{$key+1}}</td>
    					<td>
    						<img onclick="show_pic.show('{{url($d->foto??'tamu-def.png')}}')" src="{{url($d->foto??'tamu-def.png')}}" style="max-width:100px;">
    					</td>
                        <td>
                            {{($d->izin_akses_masuk?'DI IZINKAN':'TIDAK DI IZINKAN') }}
                        </td>
                        <td>
                            @if($d->tamu_khusus)
                            TAMU KHUSUS - {{$d->jenis_tamu_khusus}}
                            @else
                            BIASA
                            @endif
                        </td>
    					<td>
    						@foreach (explode( '||', $d->idt_list??'') as $element)
    							<p style="font-size:12px; margin:0px; padding: 3px; font-weight: bolder;"><b>{{$element}}</b></p>
    						@endforeach
    					</td>
    					<td>
    						{{$d->nama}}
    					</td>
    					<td>
    						{{$d->nomer_telpon}}
    					</td>
    					<td>
    						{{$d->pekerjaan}}
    					</td>
    					<td>
    						{{$d->golongan_darah}}
    					</td>
    					<td>{{$d->jenis_kelamin?'LAKI-LAKI':'PEREMPUAN'}}</td>
    					<td>
    						{{$d->tempat_lahir}} / {{$d->tanggal_lahir}}
    					</td>
    					<td>{{$d->alamat}}</td>
    					<td style="min-width: 200px;">
    						<div class="btn-group">
                              <a href="{{route('g.daftar_tamu.gate_provos',['id'=>$d->id_tamu,'slug'=>Str::slug($d->nama)])}}" class="btn btn-primary btn-sm">Form Masuk</a>
                              @if(Auth::User()->can('is_admin'))
                              <a href="{{route('g.tamu.edit',['id'=>$d->id,'slug'=>Str::slug($d->nama)])}}" class="btn btn-warning btn-sm"><i class="fa fa-pen"></i> Edit</a>
                              @else
                               <a href="{{route('g.tamu.view',['id'=>$d->id,'slug'=>Str::slug($d->nama)])}}" class="btn btn-warning btn-sm"><i class="fa fa-eye"></i> Detail</a>

                              @endif
                            </div>
    					</td>
    				</tr>
    					{{-- expr --}}
    				@endforeach
    			</tbody>
    		</table>
    	</div>
    </div>
</div>
@endsection
