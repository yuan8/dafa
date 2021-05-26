@extends('adminlte::page')

@section('content_header')
<a href="{{route('a.b.index',['id'=>$data['tag'],'slug'=>Str::slug($data['name'])])}}" class="btn btn-primary"><i class="fa fa-arrow-left"></i> KEMBALI </a>
@stop
@section('content')
<H4><b>UBAH BAGIAN TAMU</b></H4>

<div class="row">
   	<div class="col-md-6">
		<form action="{{route('a.b.update',['id'=>$data['tag'],'slug'=>Str::slug($data['name'])])}}" method="post">
			@csrf
			@method('PUT')
			<div class="card">
			    <div class="card-body">
			    
			    		<div class="form-group">
			       			<label>TAG BAGIAN</label>
			       			<input type="text" required="" class="form-control" name="tag" value="{{$data['tag']}}">
			       		</div>
			       		<div class="form-group">
			       			<label>NAMA BAGIAN</label>
			       			<input type="text" required="" class="form-control" name="name" value="{{$data['name']}}">
			       		</div>
			       		
			       	</div>
			     
			    <div class="card-footer">
			    	<button class="btn btn-primary" type="submit">UPDATE</button>
			    </div>
			</div>
		</form>
	</div>
</div>
@endsection
