@extends('adminlte::page')

@section('content')
<div class="card ">
	<form action="{{route('a.u.store')}}" method="post">
	@csrf
	<div class="card-header with-border">
		<h4 class="title">TAMBAH USER </h4>
	</div>
	<div class="card-body">
		
			<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>NAME*</label>
					<input type="text" required="" class="form-control" name="name" value="{{old('name')}}">
				</div>
				<div class="form-group">
					<label>USERNAME*</label>
					<input type="text" required="" class="form-control" name="username" value="{{old('username')}}">
				</div>
				<div class="form-group">
					<label>EMAIL*</label>
					<input type="email" required="" class="form-control" name="email" value="{{old('email')}}">
				</div>
				<div class="form-group">
					<label>NRP* </label>
					<input type="text" required="" class="form-control" name="nrp" value="{{old('nrp')}}">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>PANGKAT</label>
					<input type="text" required="" class="form-control" name="pangkat" value="{{old('pangkat')}}">
				</div>
				<div class="form-group">
					<label>JABATAN</label>
					<input type="text" required="" class="form-control" name="jabatan" value="{{old('jabatan')}}">
				</div>
				<div class="form-group">
					<label>ROLE</label>
					<select class="form-control" name="role" required="">
						@foreach ($role as $key=>$r)
						<option value="{{$key}}" {{$key==old('role')?'selected':''}}>{{$r}}</option>
							{{-- expr --}}
						@endforeach
					</select>
				</div>
				<div class="row">
						<div class="col-md-6">
							<div class="form-group">
								<label>PASSWORD </label>
								<input type="password" class="form-control" required="" name="password">
							</div>
						</div>
						<div class="col-md-6">
							<div class="form-group">
								<label>KONFIRMASI PASSWORD</label>
								<input type="password" class="form-control" required="" name="password_confirmation">
							</div>
						</div>
					</div>

				
			</div>
		</div>
	</div>
	<div class="card-footer">
		<button type="submit" class="btn btn-primary">TAMBAH</button>
	</div>
	</form>

</div>

@endsection
