@extends('adminlte::page')

@section('content_header')
<a href="{{route('a.b.index')}}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> KEMBALI </a>
@stop
@section('content')
<H4><b>TAMBAH BAGIAN TAMU</b></H4>

<div class="row">
   	<div class="col-md-6">
		<form action="{{route('a.b.store')}}" method="post">
			@csrf
			<div class="card">
			    <div class="card-body">
			    
			    		<div class="form-group">
			       			<label>TAG BAGIAN</label>
			       			<input type="text" required="" class="form-control" name="tag" value="{{old('tag')}}">
			       		</div>
			       		<div class="form-group">
			       			<label>NAMA BAGIAN</label>
			       			<input type="text" required="" class="form-control" name="name" value="{{old('nama')}}">
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
