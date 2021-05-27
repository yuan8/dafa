@extends('adminlte::page')

@section('content')
<H4><b>TAMU</b></H4>

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
    				<tr>
    					<th>FOTO</th>
    					<th>IDENTITY</th>

    					<th>NAMA</th>
    					<th>NOMER TELPON</th>
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
    					<td>
    						<img src="{{url($d->foto??'tamu-def.png')}}" style="max-width:100px;">
    					</td>
    					<td>
    						@foreach (explode( '||', $d->idt_list??'') as $element)
    							<p style="font-size:8px; margin:0px; padding: 0px;"><b>{{$element}}</b></p>
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
    					<td>
    						<a href="{{route('g.daftar_tamu.gate_provos',['id'=>$d->id_tamu,'slug'=>Str::slug($d->nama)])}}" class="btn btn-primary btn-sm">Form Input Provos</a>
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
