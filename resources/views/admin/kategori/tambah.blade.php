@extends('adminlte::page')

@section('content_header')
<a href="{{route('a.k.index')}}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> KEMBALI </a>
@stop
@section('content')
<H4><b>TAMBAH KATEGORI TAMU</b></H4>

<div class="row">
   	<div class="col-md-6">
		<form action="{{route('a.k.store')}}" method="post">
			@csrf
			<div class="card">
			    <div class="card-body">
			    
			       		<div class="form-group">
			       			<label>NAMA KATEGORI</label>
			       			<input type="text" required="" class="form-control" name="nama" value="{{old('nama')}}">
			       		</div>
			       		<div class="form-group">
			       			<label>DESKRIPSI</label>
			       			<textarea class="form-control" name="deskripsi">{{old('deskripsi')}}</textarea>
			       		</div>
			       	</div>
			     
			    <div class="card-footer">
			    	<button class="btn btn-primary" type="submit">TAMBAH</button>
			    </div>
			</div>
		</form>
	</div>
</div>
@endsection
