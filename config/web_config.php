<?php

return [
	'name'=>'Aplikasi Buku Tamu',
	'broadcast_network'=>false,
	'tujuan_tamu'=>[
		[
			'tag'=>'DIR-A',
			'name'=>'DIREKTORAT A'
		],
		[
			'tag'=>'DIR-B',
			'name'=>'DIREKTORAT B'
		]
	],

	'kategori_tamu'=>[
		[
			'tag'=>'TNI',
			'name'=>'TNI'
		],
		[
			'tag'=>'POLISI',
			'name'=>'POLISI'
		],
		[
			'tag'=>'PEMERINTAHAN-DALAM-NEGERI',
			'name'=>'PEMERINTAHAN DALAM NEGERI'
		],
		[
			'tag'=>'PEMERINTAHAN-LUAR-NEGERI',
			'name'=>'PEMERINTAHAN LUAR NEGERI'
		],
		[
			'tag'=>'SIPIL',
			'name'=>'SIPIL'
		],
		[
			'tag'=>'SIPIL',
			'name'=>'SIPIL'
		]
	],
	'identity_list'=>[
		[

			'tag'=>'KTP',
			'name'=>'KTP',
			'taxonomy'=>'TKP'
		],
		[

			'tag'=>'PASSPORT-WNA',
			'name'=>'PASSPORT-WNA',
			'taxonomy'=>'PASSPORT'

		],
		[

			'tag'=>'PASSPORT-WNI',
			'name'=>'PASSPORT-WNI',
			'taxonomy'=>'PASSPORT'

		],
		[

			'tag'=>'SIM-C',
			'name'=>'SIM C',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'SIM-A',
			'name'=>'SIM A',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'SIM-B1',
			'name'=>'SIM B1',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'SIM-B2',
			'name'=>'SIM B2',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'SIM-D',
			'name'=>'SIM D',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'SIM-INTERNASIONAL',
			'name'=>'SIM INTERNASIONAL',
			'taxonomy'=>'SIM'

		],
		[

			'tag'=>'ID-PERUSAHAAN',
			'name'=>'ID PERUSAHAAM',
			'taxonomy'=>'LAINYA'

		],
		[

			'tag'=>'ID-MAHASISWA',
			'name'=>'ID MAHASISWA',
			'taxonomy'=>'LAINYA'

		],
		[

			'tag'=>'LAINYA',
			'name'=>'LAINYA',
			'taxonomy'=>'LAINYA'

		]
	]

];