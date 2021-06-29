@extends('adminlte::page')

@section('content')


<div class="row">
	<div class="col-md-8 col-md-offset-2" id="v-provos">
		<div class="input-group input-group-lg">
			<div class="input-group-prepend">
				<select class="form-control form-control-lg" >
					<option v-for="op in opsi_identitas" v-bind:value="op" >@{{op}}</option>
				</select>	
		 	 </div>
			  
		 	 <input type="text" class="form-control" name="no_identity" placeholder="Nomor Identitas" v-model="no_identity">
			  <div class="input-group-append">
			  	<button class="btn btn-primary">PROSES</button>
			  </div>
		</div>
	</div>
</div>
@stop

@section('js')
<script type="text/javascript">
	var vprovos=new Vue({
		el:'#v-provos',
		data:{
			jenis_indentitas:'KTP',
			no_identity:'',
			opsi_identitas:[
				'KTP',
				'SIM A',
				'SIM C',
				'SIM B UMUM',
				'SIM B',
				'LAINYA'
			]
		},
		method:{
				
		},
		watch:{
			no_identity:function (val,oldVal) {
				if(val!=oldVal){
                    if(this.no_identity){
                        var val=this.no_identity;
                        val=val.replace(/[-]/g,'');
                        let arr_val=val.split('');
                        var char_no_identity='';
                        for(var i=0;i<arr_val.length;i++){
                            if(i%4==0 && i!=0){
                                char_no_identity+='-';
                            }
                            char_no_identity+=arr_val[i];
                        }
                        this.no_identity=char_no_identity;
                    }
                }
			}
		}


	});
</script>

@stop