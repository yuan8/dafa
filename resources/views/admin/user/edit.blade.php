@extends('adminlte::page')

@section('content')
<div class="card ">
	<form action="{{route('a.u.update',['id'=>$data->id,'slug'=>$data->username])}}" method="post">
	@csrf
	@method('PUT')
	<div class="card-header with-border">
		<h4 class="title">UBAH USER {{$data->name}}</h4>
	</div>
	<div class="card-body">
		
			<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>NAME*</label>
					<input type="text" required="" class="form-control" name="name" value="{{$data->name}}">
				</div>
				<div class="form-group">
					<label>USERNAME*</label>
					<input type="text" required="" class="form-control" name="username" value="{{$data->username}}">
				</div>
				<div class="form-group">
					<label>EMAIL*</label>
					<input type="email" required="" class="form-control" name="email" value="{{$data->email}}">
				</div>
				<div class="form-group">
					<label>NRP* </label>
					<input type="text" required="" class="form-control" name="nrp" value="{{$data->nrp}}">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>PANGKAT</label>
					<input type="text" required="" class="form-control" name="pangkat" value="{{$data->pangkat}}">
				</div>
				<div class="form-group">
					<label>JABATAN</label>
					<input type="text" required="" class="form-control" name="jabatan" value="{{$data->jabatan}}">
				</div>
				@if(Auth::User()->id!=$data->role)
				<div class="form-group">
					<label>ROLE</label>
					<select class="form-control" name="role" required="">
						@foreach ($role as $key=>$r)
						<option value="{{$key}}" {{$key==$data->role?'selected':''}}>{{$r}}</option>
							{{-- expr --}}
						@endforeach
					</select>
				</div>
				@endif

				@if(Auth::User()->id!=$data->role)
					@if(Auth::User()->can('is_admin'))
					<div class="form-group">
						<label>STATUS USER</label>
						<select class="form-control" name="deleted_at" required="">
							<option value="{{Carbon\Carbon::now()}}" {{null!=$data->deleted_at?'selected':''}}>USER TIDAK AKTIF</option>
							<option value="NULL_VALUE" {{null==$data->deleted_at?'selected':''}}>ACTIVE</option>
						</select>
					</div>
					@endif

				@endif
			</div>
		</div>
	</div>
	<div class="card-footer">
		<button type="submit" class="btn btn-primary">UPDATE</button>
	</div>
	</form>

</div>

<div class="card ">
	<form action="{{route('a.u.password',['id'=>$data->id,'slug'=>Str::slug($data->username)])}}" method="post">
	@csrf
	@method('PUT')
	<div class="card-header with-border">
		<h4 class="title">UBAH PASSWORD USER {{$data->name}}</h4>
	</div>
	<div class="card-body">
		@if(Auth::User()->id==$data->id)
			<div class="row">
				<div class="col-md-6">
					<div class="form-group">
						<label>PASSWORD LAMA</label>
						<input type="password" class="form-control" required="" name="old_password">
					</div>
				</div>
			</div>
		@endif
		<div class="row">
			<div class="col-md-6">
				<div class="form-group">
					<label>PASSWORD BARU</label>
					<input type="password" class="form-control" required="" name="password">
				</div>
			</div>
			<div class="col-md-6">
				<div class="form-group">
					<label>KONFIRMASI PASSWORD BARU</label>
					<input type="password" class="form-control" required="" name="password_confirmation">
				</div>
			</div>
		</div>
	</div>
	<div class="card-footer">
		<button type="submit" class="btn btn-primary">UPDATE</button>
	</div>
	</form>
</div>
@endsection
