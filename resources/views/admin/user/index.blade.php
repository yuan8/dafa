@extends('adminlte::page')

@section('content')
<div class="card ">
	<div class="card-header">
		<a style="margin-bottom: 10px;" href="{{route('a.u.tambah')}}" class="btn btn-primary">TAMBAH USER</a>
		<form action="{{url()->full()}}" method="get">
			<div class="row">
				<div class="col-md-6">
					<input type="text" name="q" value="{{$request->q}}" class="form-control" placeholder="Search">
				</div>
				<div class="col-md-6">
					<select class="form-control" name="status">
						<option value="" >USER AKTIF</option>
						<option value="USER_TIDAK_AKTIF" {{$request->status=='USER_TIDAK_AKTIF'?'selected':''}}>USER TIDAK AKTIF</option>
					</select>
				</div>
			</div>
		</form>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table class="table-bordered table">
			<thead>
				<tr class="text-center">
					<th>NO.</th>
					<th>NAMA</th>
					<th>USERNAME</th>
					<th>PANGKAT</th>
					<th>EMAIL</th>
					<th>ROLE</th>
					<th>JABATAN</th>
					<th>AKSI</th>

				</tr>
			</thead>
			<tbody>
				@foreach ($data as $key => $d)
				<tr class="text-center">
                    <td>{{$key+1}}</td>
					<td>{{$d->name}}</td>
					<td>{{$d->username}}</td>

					<td>{{$d->pangkat}}</td>
					<td>{{$d->email}}</td>
					<td>{{isset($role[$d->role])?$role[$d->role]:''}}</td>
					<td>{{$d->jabatan}}</td>
					<td>

						<div class="btn-group">
							<a href="{{route('a.u.ubah',['id'=>$d->id,'slug'=>Str::slug($d->username)])}}" class="btn btn-sm btn-primary">Ubah</a>
							{{-- <a href="javascript::void(0)" class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></a> --}}

						</div>
					</td>


				</tr>
					{{-- expr --}}
				@endforeach
			</tbody>
		</table>
		</div>
		{{$data->links()}}
	</div>
</div>
@endsection
