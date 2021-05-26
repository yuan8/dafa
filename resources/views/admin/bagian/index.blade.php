@extends('adminlte::page')

@section('content_header')
<a href="{{route('a.b.tambah')}}" class="btn btn-primary">TAMBAH BAGIAN</a>
@stop
@section('content')
<H4><b>TUJUAN BAGIAN TAMU</b></H4>

<div class="card">
    <div class="card-header with-border">
        
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="v-kategori">
                <thead>
                    <tr>
                        <th style="width:100px;">AKSI</th>
                        <th>TAG</th>
                        <th>NAMA</th>
                    </tr>
                </thead>
                <tbody >
                    @foreach($data as $d)
                    <tr>
                        <td>
                            <button class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            <a href="{{route('a.b.ubah',['id'=>$d['tag'],'slug'=>Str::slug($d['name'])])}}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                        </td>
                        <td>{{$d['tag']}}</td>
                        <td>{{$d['name']}}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
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