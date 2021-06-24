@extends('adminlte::page')

@section('css')
<script type="text/javascript" src="{{asset('tparty/bower_components/datatables.net/js/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript" src="{{asset('tparty/bower_components/datatables.net-bs4/js/dataTables.bootstrap4.js')}}"></script>
<link rel="stylesheet" type="text/css" href="{{asset('tparty/bower_components/datatables.net-bs4/css/dataTables.bootstrap4.min.css')}}">
@stop
@section('content_header')


<a href="{{route('a.b.tambah')}}" class="btn btn-primary">TAMBAH BAGIAN</a>
@stop
@section('content')
<div class="modal fade"  id="modal-delete" >
    <div class="modal-dialog">
         <div class="modal-content bg-danger">
            <div class="modal-header">
                <h5 class="modal-title">HAPUS BAGIAN</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
            <div class="modal-body text-center">
                <p>Apakah Anda Yakin Ingin Menghapus Bagian "@{{bagian}}" ?</p>
            </div>
            <div class="modal-footer">
        <form v-bind:action="url" method="post">
            @csrf
            @method('DELETE')
                <button type="submit" class="btn btn-primary">YA</button>
        </form>

            </div>
        </div>
    </div>
</div>
<H4><b>TUJUAN BAGIAN TAMU</b></H4>

<div class="card">
    <div class="card-header with-border">

    </div>
    <div class="card-body">
        <div class="table-responsive col-md-12">
            <table class="table table-bordered" id="v-kategori">
                <thead>
                    <tr class="text-center">
                        <th>NO.</th>
                        <th>TAG</th>
                        <th>NAMA</th>
                        <th style="width:100px;">AKSI</th>
                    </tr>
                </thead>
                <tbody >
                    @foreach($data as $key => $d)
                    <tr class="text-center">
                        <td>{{$key+1}}</td>
                        <td>{{$d['tag']}}</td>
                        <td>{{$d['name']}}</td>
                        <td>
                            <button onclick="window.modalDelete.build('{{$d['name']}}','{{route('a.b.delete',['id'=>$d['tag'],'slug'=>Str::slug($d['name'])])}}')" class="btn btn-danger"><i class="fa fa-trash"></i></button>
                            <a href="{{route('a.b.ubah',['id'=>$d['tag'],'slug'=>Str::slug($d['name'])])}}" class="btn btn-primary"><i class="fa fa-eye"></i></a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
<style type="text/css">
    .dataTables_wrapper.form-inline{
        display: block;
    }
    .col-xs-12{
        width:100%;
    }
</style>


<div class="modal fade modal-danger show" id="modal-delete" ><div class="modal-dialog">
    <form v-bind:action="url" method="post">
           @csrf
           @method('DELETE')
            <div class="modal-content bg-danger">
            <div class="modal-header">
                <h5 class="modal-title">HAPUS BAGIAN </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span>
                </button>
              </div>
            <div class="modal-body text-center">
                <p>APAKAH ANDA YAKIN MENGHAPUS BAGIAN "@{{bagian}}"?</p>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">YA</button>
            </div>
        </div>
        </form>
    </div>
</div>



@stop
@section('js')
<script type="text/javascript">

    var modalDelete=new Vue({
        el:"#modal-delete",
        data:{
            bagian:null,
            url:null
        },
        methods:{
            display:function(){
                $('#modal-delete').modal();
            },
            build:function(bagian,url){
                this.bagian=bagian;
                this.url=url;
                this.display();
            }
        },

    });


   $('#v-kategori').dataTable({
        sort:false,
        paginate:false
   });


</script>

@stop
