<?php

	/**
	 * Set true to check file dimension
	 * Default: true
	 */
	define("check_max_dim", true);

	/**
	 * Set locale for result message from upload
	 * Default: it
	 */
	define("default_lang", "it");

	/**
	 * Type of result message from upload
	 * -	txt
	 * -	json
	 * Default: json
	 */
	define("txt_response", "json");

	$init_upl = array(
		'Images' => array(
			'url' => 'up_img/',
			'directory' => '../up_img/',
			'maxSize' => 2097152, //	2MB
			'allowedExtensions' => 'bmp,gif,jpeg,jpg,jpe,png',
			'allowedMIMETypes' => 'image/jpeg,image/png,image/bmp,image/gif',
			'deniedExtensions' => ''
		),
		'Files' => array(
			'url' => 'up_files/',
			'directory' => '../up_files/',
			'maxSize' => 2097152, //	2MB
			'allowedExtensions' => 'pdf,doc,docx',
			'allowedMIMETypes' => 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/download,application/force-download',
			'deniedExtensions' => ''
		)
	);

	$init_upl['CheckDoubleExtension'] = false;
	$init_upl['OverwriteIfExists'] = false;
	$init_upl['CheckDimFile'] = check_max_dim;