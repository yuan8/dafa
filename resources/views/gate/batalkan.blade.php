<div class="modal-dialog">
	<form action="{{route('k.batalkan',['id'=>$data->id])}}" method="post">
		@csrf
		@method('DELETE')
		<div class="modal-content bg-danger">
		<div class="modal-header">
	        <h5 class="modal-title">BATALKAN KUNJUNGAN {{$data->nama}}</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
		<div class="modal-body text-center">
			<P>Apakah Anda Yakin Ingin Membatalkan Kunjungan?</P>
		</div>
		<div class="modal-footer">
			<button type="submit" class="btn btn-primary">YA</button>
		</div>
	</div>
	</form>
</div>