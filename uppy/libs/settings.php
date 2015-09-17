<?php

	define("check_max_dim", true);
	define("default_lang", "it");

	$init_upl = array(
		'Images' => array(
			'url' => 'up_img/',
			'directory' => '../up_img/',
			'maxSize' => 0,
			'allowedExtensions' => 'bmp,gif,jpeg,jpg,jpe,png',
			'allowedMIMETypes' => 'image/jpeg,image/png,image/bmp,image/gif',
			'deniedExtensions' => ''
		),
		'Files' => array(
			'url' => 'up_files/',
			'directory' => '../up_files/',
			'maxSize' => 0,
			'allowedExtensions' => 'pdf,doc,docx',
			'allowedMIMETypes' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/download,application/force-download',
			'deniedExtensions' => ''
		)
	);

	$init_upl['CheckDoubleExtension'] = false;
	$init_upl['OverwriteIfExists'] = false;
	$init_upl['CheckDimFile'] = check_max_dim;