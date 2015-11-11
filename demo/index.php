<?php

	if (file_exists(dirname(__FILE__)."/../uppy/uppy.class.php"))
		include(dirname(__FILE__)."/../uppy/uppy.class.php");

	if (class_exists("UpFiles"))
		$basco = new UpFiles;

	$val_dm = $result = "";

	$val_dm = $basco->detectMaxUploadFileSize();

	if (isset($_GET['act']) && !empty($_GET['act']) && ($_GET['act'] == "to_upload")) {

		$result = $basco->up_file_img($_FILES['upl_files'], $init_upl, 'Images');
		//$result = $basco->up_file_img($_FILES['upl_files'], $init_upl, 'Files');

	}

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<title>Example</title>
		<style type='text/css'>
			html, body, * { font-family: Tahoma; font-size: 11px; color: #000; }
			div#form-args { width: 90%; margin: 0 auto; padding: 3px; }
			div#fdata { width: 40%; float: left; }
			div#fdata form label { font-weight: bold; display: block; margin-bottom: 3px; }
			div#response { width: 23%; height: auto !important; padding: 3px; text-align: center; font-weight: bold; float: left; margin: 0px 5px; }
			div#chkdata { width: 23%; border: 1px solid #000; padding: 3px; float: right; }
			div#chkdata span { margin-bottom: 3px; display: block; }
			.clearfix { clear: both; }
		</style>
	</head>
	<body>
		<div id='form-args'>
			<div id='fdata'>
				<form name='frmEdit' action="<?php echo sprintf("http://%s%s", $_SERVER['SERVER_NAME'], $_SERVER['REQUEST_URI']); ?>?act=to_upload" method='POST' enctype='multipart/form-data'>
					<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $val_dm; ?>" />
					<label><?php echo $basco->parse_lang('sel_file'); ?>:</label>
					<input type='file' name='upl_files' id='upl_files' class='allego' /> <input type='submit' name='modifa' value='Carica' />
				</form>
			</div>
			<div id='response'>
				<?php echo $result; ?>
			</div>
			<div id='chkdata'>
				<span><?php echo $basco->parse_lang('max_size'); ?>: &nbsp; <b><?php echo $basco->getMaxDim(); ?></b></span>
				<span>CHMOD: &nbsp; <b><?php echo $basco->checkPerms(); ?></b></span>
				<span><?php echo $basco->parse_lang('perm_file'); ?>: &nbsp; <b><?php echo $basco->getPerms(realpath(dirname(__FILE__))); ?></b> (<i><u><?php echo $basco->parse_lang('oct_val'); ?></u></i>)</span>
				<span>Fileinfo: &nbsp; <?php echo $basco->checkFinfo(); ?></span>
				<span><?php echo $basco->parse_lang('php_vers'); ?>: &nbsp; <b><?php echo phpversion(); ?></b></span>
			</div>
			<div class='clearfix'></div>
		</div>
	</body>
</html>