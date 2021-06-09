<!DOCTYPE html>
<html>
<head>
	<title></title>
	 <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

	<style type="text/css">
		
		

		@page { 
			margin: 0px;
			font-style: "Open Sans",
			border:1px solid #222;
		 }
		 p{
		 	line-height: 8px;
		 	font-size: 8px;
		 	padding: 0px;
		 	margin:0px;
		 }

		 p.g{

		 	line-height: 14px;
		 	font-size: 10px;
		 }
		 .text-center{
		 	text-align: center;
		 }


			
		/**/
	</style>
</head>
<body>
	<div class="col-p-12">
		<p>jdskjk</p>
	</div>
	<div class="col-p-12">
		<p>dsds</p>
	</div>
	<div style="background: yellow; position: absolute; 
		z-index: 1; 
		width:100%; height:80px; 
		margin-top: -8px;
		 margin-left: -8px;">
		
	</div>

	
	<div  style="width:100%; text-align: center; margin-top:20px; z-index: 99; margin-bottom: 5px;">
		
			<img style="width:50%; background: #fff; border:1px solid #222; padding: 5px;  margin-top: 8px; margin-left: 3px;" src="{{'data:image/png;base64,'.DNS2D::getBarcodePNG($code_id,'QRCODE')}}">
		

		
	</div>


	


	<p class="text-center"><b>TAMU KHUSUS</b></p>
	<p class="text-center g"><b>{{$tamu->string_id}}</b></p>
	<div style="margin-top: 5px; border-top:1px solid #222;  float: left; width:100%"></div>
	<div style="margin-top: 5px; float: left;">
		<div style="width:40%; float: left;">
			<img src="{{$foto}}" style=" border:1px solid #222; padding:3px; background:yellow; width: 90%; margin-left: 8px;" class="img-responsive">
		</div>
		<div style="margin-left: 45%; float: left; padding-left:5px;">
			<p class="g"><b>{{$tamu->nama}}</b></p>
			<p><small>{{$tamu->jenis_tamu_khusus}}</small></p>
			<p class="g">{{$tamu->def_instansi}}</p>


		</div>
	</div>
	


</body>
</html>