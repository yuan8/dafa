<!DOCTYPE html>
<html>
<head>
	<title></title>
	  <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	 <style type="text/css" >
	 	 @font-face {
	        font-family: 'Roboto';
	        src: {{ storage_path('/fonts/Roboto-Reguler.ttf') }} ;
	        font-weight: bold;
	        font-style: normal;

	    }

	 		table th,table td{
	 			font-size: 8px!important;
	 			border: .1px solid #222;
	 			border-right:none;
	 			font-family: 'Roboto', sans-serif;

		 	}

		 	table th,td{
		 		padding: .75rem;
			    vertical-align: top;
			    color: #222;
			    text-align: left;
			    font-weight: bold;

			}

		 	.table{
		 		width: 100%;
			    margin-bottom: 1rem;
	 			border-right: 1px solid #222;
			    background-color: transparent;
			}

			tr.bg-primary th{
				background: #233e8b;
				color:#fff;

			}

			tr.bg-primary-2 th{
				background: #1eae98;
				color:#fff;
			}
			tr.bg-primary-3 th{
				background: #0a1931;
				color:#fff;
			}
			table th{
				font-weight: bold;
			}
			tr.n th{
				font-size: 20px;
				text-align: center;
			}




	 </style>
</head>
<body>
	<table class="table table-bordered" border="0" cellpadding="0" cellspacing="0">
		<thead>
			<tr class="bg-primary-3">
				<th colspan="16">{{config('web_config.name')}} : {{Carbon\Carbon::now()}}</th>
			</tr>
			<tr>
				<th colspan="3">MULAI</th>
				<th colspan="3">HINGGA</th>
				<th colspan="3">STATUS</th>
				<th colspan="7"></th>


			</tr>
			<tr>
				<th colspan="3">{{$start}}</th>
				<th colspan="3">{{$end}}</th>
				<th colspan="3">{{$status}}</th>
				<th colspan="7"></th>
			</tr>
			<tr class="bg-primary">
				<th>FOTO</th>

				<th>JENIS ID</th>
				<th>NOMER ID</th>
				<th>NOMER TELP</th>
				<th>NAMA</th>
				<th>JENIS KELAMIN</th>
				<th>GOL. DARAH</th>
				<th>TMP/TGL LAHIR</th>
				<th>ALAMAT</th>
				<th>PERKERJAAN</th>
				<th>KATEGORI TAMU</th>
				<th>TUJUAN</th>
				<th>MASUK DATE</th>
				<th>KELUAR DATE</th>
				<th>STATUS</th>
			</tr>
			<tr class="n bg-primary-2">
				<th>1</th>
				<th>2</th>
				<th>3</th>
				<th>4</th>
				<th>5</th>
				<th>6</th>
				<th>7</th>
				<th>8</th>
				<th>9</th>
				<th>10</th>
				<th>11</th>
				<th>12</th>
				<th>13</th>
				<th>14</th>
				<th>15</th>



			</tr>
		</thead>
		<tbody>
			@foreach ($data as $v)
			@php
				$v->status=($v->gate_checkout?'CHECKOUT':($v->gate_checkin?'CHECKIN':($v->provos_checkin?'PROVOS':'')));
					switch($v->status){
		                case 'PROVOS':
		                   $v->status="TAMU TERDAFTAR DI PROVOS";
		                    break;
		                case 'CHECKIN':
		                   $v->status="TAMU TELAH MEMASUKI GATE";
		                    break;

		             	case 'CHECKOUT':
			             	if($v->checkout_from_gate){
			                     $v->status="TELAH MENYELESAIKAN KUNJUNGAN";
			                  }else{
			                     $v->status="MEMBATALKAN KUNJUNGAN";

			                  }
		                    break;

		                default:
		                   $v->status="TELAH MENYELESAIKAN KUNJUNGAN";
		            }

			@endphp
				<tr>
					<td>
						<img src="{{url($v->foto??'tamu-def.png')}}" style="max-width: 90px;">
					</td>

					<td>{{$v->jenis_identity}}</td>
					<td>{{$v->identity_number}}</td>
					<td>{{$v->nomer_telpon}}</td>
					<td>{{$v->nama}}</td>
					<td>{{$v->jenis_kelamin?'LAKI-LAKI':'PEREMPUAN'}}</td>
					<td>{{ $v->golongan_darah}}</td>
					<td>{{ $v->tempat_lahir}}/{{$v->tanggal_lahir}}</td>
					<td>{{$v->alamat}}</td>
					<td>{{$v->pekerjaan}}</td>
					<td>{{$v->kategori_tamu}}

						<p><b>{{$v->instansi}}</b></p>
					</td>
					<td>
						<p>{{implode(', ',json_decode($v->tujuan??[]) )}}</p>
						<p>{{$v->keperluan}}</p>
					</td>

					<td>
						{{$v->gate_checkin}}
					</td>
					<td>
						{{$v->gate_checkout}}
					</td>
					<td>
						{{$v->status}}
					</td>








				</tr>
				{{-- expr --}}
			@endforeach
		</tbody>
	</table>

</body>
</html>
