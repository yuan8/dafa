<div class="modal-dialog">
	<div class="modal-content">
		<div class="modal-header">
	        <h5 class="modal-title">TELPON {{$request->nama}}</h5>
	        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
	          <span aria-hidden="true">&times;</span>
	        </button>
	      </div>
		<div class="modal-body text-center">
			<?=  DNS2D::getBarcodeHTML('tel:'.$request->nomer_telpon, 'QRCODE'); ?>
		</div>
		<div class="modal-footer">
		</div>
	</div>
</div>