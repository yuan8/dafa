@extends('adminlte::page')

@section('content_header')
<a href="{{route('a.k.tambah')}}" class="btn btn-primary">TAMBAH KATEGORI</a>
@stop
@section('content')
<H4><b>KATEGORI TAMU</b></H4>

<div class="card">
    <div class="card-header with-border">
        <form method="get">
            <div class="form-group">
            <input type="text" name="q" class="form-control" placeholder="Cari.." value="{{$req->q}}">
        </div>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="v-kategori">
                <thead>
                    <tr>
                        <th style="width:100px;">AKSI</th>
                        <th>NAMA</th>
                        <th>DESKRIPSI</th>
                    </tr>
                </thead>
                <tbody >
                    @foreach($data as $d)
                    <tr>
                        <td>
                            <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            <a href="{{route('a.k.ubah',['id'=>$d->id,'slug'=>Str::slug($d->nama)])}}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                        </td>
                        <td>{{$d->nama}}</td>
                        <td>{{$d->deskripsi}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{$data->links()}}
    </div>
</div>
@endsection
@section('js')
<script type="text/javascript">
    var vkategori=new Vue({
        el:'#v-kategori',
        data:{
            items:<?=  json_encode($data) ?>,

        }
    })
</script>

@stop