<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style type="text/css">
    	.text-uppercase{
    		text-transform: uppercase;
    	}

    	.bg-warning {
		    background-color: #fff5d9!important;
			color:#222;
		}
			.bg-success {
		    background-color: #d8f9df!important;
			color:#222;
			}
			.bg-maroon {
		    background-color: #ffd0e1!important;
			color:#222;
	}

	

       *{font-family:Helvetica}.tdata td,.tdata th{font-size:11px}.break-print{page-break-after:always}.bg-gray{background:#f1f1f1}.panel{border-right:1px solid #000;border-top:1px solid #000;background:#fff}.text-center{text-align:center}.text-right{text-align:right}.table>tbody>tr>td,.table>tbody>tr>th,.table>tfoot>tr>td,.table>tfoot>tr>th,.table>thead>tr>td,.table>thead>tr>th{padding:4px;line-height:1.42857143;vertical-align:top;border-top:1px solid #ddd}.table-bordered>tbody>tr>td,.table-bordered>tbody>tr>th,.table-bordered>tfoot>tr>td,.table-bordered>tfoot>tr>th,.table-bordered>thead>tr>td,.table-bordered>thead>tr>th{border:1px solid #ddd}.table tr th{background-color:#f1f1f1!important;vertical-align:middle!important}p{font-size:12px;line-height:13px;margin-bottom:2px;margin-top:2px}h3,h4{margin-bottom:5px}table{width:100%;max-width:100%;margin-bottom:5px;border-collapse:collapse;border-spacing:0}.tdata{margin-bottom:10px}@page{size:auto;margin:0;margin-top:10px;margin-bottom:15px;margin-left:60px;margin-right:15px}@page :footer{display:none!important}@page :header{display:none!important}p{line-height:12px}
    </style>
  
</head>
<body>
	
	@yield('content')
</body>
</html>