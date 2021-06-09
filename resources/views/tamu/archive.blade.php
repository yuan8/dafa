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
                        <th>IZIN AKSES MASUK</th>
                        <th>JENIS TAMU</th>


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
                    @if($d->tamu_khusus)
                        <tr class="bg-info">
                            <td colspan ="12" class="text-center"><b>TAMU KHUSUS</b></td>
                        </tr>
                    @endif
    				<tr>
    					<td>
    						<img src="{{url($d->foto??'tamu-def.png')}}" style="max-width:100px;">
    					</td>
                        <td></td>
                        <td>
                            @if($d->tamu_khusus)
                            TAMU KHUSUS - {{$d->jenis_tamu_khusus}}
                            @else
                            BIASA
                            @endif
                        </td>
    					<td>
    						@foreach (explode( '||', $d->idt_list??'') as $element)
    							<p style="font-size:12px; margin:0px; padding: 0px;"><b>{{$element}}</b></p>
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
              <a href="{{route('g.tamu.edit',['id'=>$d->id,'slug'=>Str::slug($d->nama)])}}" class="btn btn-warning"><i class="fa fa-pen"></i> Edit</a>                  
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
