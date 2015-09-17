<?php

	if (file_exists(dirname(__FILE__)."/libs/settings.php"))
		include(dirname(__FILE__)."/libs/settings.php");

	class UpFiles {

		var $url_r = Site_Root;
		var $brind = 0;
		var $txt_d;
		var $d_lang;

		function __construct() {

			$this->txt_d = "";

		}

		private function genRandName() {

			return chr(rand(65, 90)).chr(rand(97, 122)).chr(rand(65, 90)).chr(rand(97, 122)).chr(rand(65, 90))."_";

		}

		public function getPth($sngp='') {

			$fin = (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') ? "\\" : "/";

			if (!isset($sngp) || empty($sngp)) {

				$len_s = strlen(__DIR__);
				$num_p = strrpos(__DIR__, $fin);

				$pathy = ($len_s != $num_p) ? __DIR__.$fin : __DIR__;

			} else {

				$pathy = $fin;

			}

			return $pathy;

		}

		public function detectMaxUploadFileSize() {

			$normalize = function($size) {

				if (preg_match('/^(-?[\d\.]+)(|[KMG])$/i', $size, $match)) {

					$pos = array_search($match[2], array("", "K", "M", "G"));
					$size = $match[1] * pow(1024, $pos);

				} else {

					throw new Exception("Failed to normalize memory size '{$size}' (unknown format)");

				}

				return $size;

			};

			$limits = array();

			$limits[] = $normalize(ini_get('upload_max_filesize'));

			if (($max_post = $normalize(ini_get('post_max_size'))) != 0)
				$limits[] = $max_post;

			if (($memory_limit = $normalize(ini_get('memory_limit'))) != -1)
				$limits[] = $memory_limit;

			$maxFileSize = min($limits);

			return $maxFileSize;

		}

		private function formatSizeInMb($size, $maxDecimals = 3, $mbSuffix = " MB") {

			$mbSize = round($size / 1024 / 1024, $maxDecimals);
			return preg_replace("/\.?0+$/", "", $mbSize).$mbSuffix;

		}

		public function getMaxDim() {

			$file_up_dim = $this->detectMaxUploadFileSize();
			return $this->formatSizeInMb($file_up_dim);

		}

		public function makeSize($size) {

			$units = array('B','KB','MB','GB','TB');

			$u = 0;

			while((round($size / 1024) > 0) && ($u < 4)) {

				$size = $size / 1024;
				$u++;

			}

			return (round($size,2) . " " . $units[$u]);

		}

		public function human_filesize($bytes, $decimals = 2) {

		  $sz = 'BKMGTP';

		  $factor = floor((strlen($bytes) - 1) / 3);

		  return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];

		}

    public function parse_lang($text_l) {

			if (defined("default_lang") && (default_lang)) {

				if (file_exists(dirname(__FILE__)."/locale/".default_lang.".php"))
					include(dirname(__FILE__)."/locale/".default_lang.".php");

			}

			if (isset($up_lang) && is_array($up_lang) && !empty($up_lang)) {

	      if (array_key_exists($text_l, $up_lang)) {

	        $mosy = str_ireplace($text_l, $up_lang[$text_l], $text_l, $count);

	        if ($count > 0)
	        	return $mosy;

	      }

	    }

    }

		public function up_file_img($cmp_f, $info_file, $tipo_upl) {

			if (isset($cmp_f) && !empty($cmp_f["name"])) {

				$prefix_rand = $this->genRandName();

				$tmp_file = $cmp_f["tmp_name"];

				//	Dimension File
				$sfile = $cmp_f["size"];

				//	Type file - Can be falsified - Untrusted
				$firstc = $cmp_f["type"];

		    //  Type file - Is currently the most reliable
		    if (function_exists('finfo_file')) {

		      $finfo = finfo_open();
		      $real_mime = finfo_file($finfo, $tmp_file, FILEINFO_MIME);
		      finfo_close($finfo);

		    }

				$nfile = str_replace("'", "_", $cmp_f["name"]);

				//	Extension File 1
				$unext = pathinfo($nfile, PATHINFO_EXTENSION);

				//	Extension File 2
		    $original_extension = (false === $pos = strrpos($nfile, '.')) ? '' : substr($nfile, $pos);
		    $original_extension = str_replace('.', '', strtolower($original_extension));

				//	Comparing file extensions
				if ($unext != $original_extension) {

					$this->brind++;
					return "Estensioni non corrispondenti";

				}

				//	Allowed File extensions
				$file_ext = explode(",", $info_file[$tipo_upl]['allowedExtensions']);

				//	Allowed File MIMETypes
				$file_mime = explode(",", $info_file[$tipo_upl]['allowedMIMETypes']);

				//	Rename File - Tips recommended for added security
				$nfile = $prefix_rand.$nfile;

				//	Check if array of file extensions is empty
				if (!empty($file_ext)) {

					//	Check if file extension is permitted
					if (in_array($unext, $file_ext) === FALSE) {

						$this->brind++;
						return "Estensione del file non permessa";

					}

				}

				//	Check if array of MIMEType is empty
				if (!empty($file_mime)) {

			    $err_mimet = 0;

			    //  Check if permitted MIMEType - Step 1 - Untrusted
			    if (in_array($firstc, $file_mime) === false)
			      $err_mimet++;

			    if (function_exists('finfo_file')) {

			      //  Check if permitted MIMEType - Step 2 - Is currently the most reliable
			      if (in_array($real_mime, $file_mime) === false)
			        $err_mimet++;

			    }
//echo $real_mime.'-'.$firstc;
			    //  Check that at least one of the controls on the mimetype, it was successful
			    if ($err_mimet == 2) {

			      $this->brind++;
			      return "Tipologia di file non permessa";

			    }

				}

    /*if($_FILES['file']['size'] > 10485760) { //10 MB (size is also in bytes)
        // File too big
    } else {
        // File within size restrictions
    }*/

				//	Directory for upload
				//$dir_up = $this->url_r.$info_file[$tipo_upl]['directory'];
				//$dir_up = $info_file[$tipo_upl]['directory'];
				$dir_up = realpath(dirname(__FILE__)).$this->getPth(1).$info_file[$tipo_upl]['directory'];
				//echo realpath(dirname(__FILE__)).$this->getPth(1).$info_file[$tipo_upl]['directory'];
				//	Check if exists directory for upload
				if (!is_dir($dir_up)) {

					$this->brind++;
					return $dir_up."Cartella per l'upload non presente sul server";

				}

				//	Check if a file exists with the same name
				if (!file_exists($dir_up.$nfile)) {

					@chmod($dir_up, 0777);

					@move_uploaded_file($tmp_file , $dir_up.$nfile);

					//	Set the file permissions to 664 - Tips recommended for added security
					@chmod($dir_up.$nfile, 0664);

					//	Set the dir permissions to 755 - Tips recommended for added security
					@chmod($dir_up, 0755);

				} else {

					//	check if you have enabled the overwriting of files
					if ($info_file['OverwriteIfExists']) {

						@chmod($dir_up, 0777);

						@move_uploaded_file($tmp_file , $dir_up.$nfile);

						//	Set the file permissions to 664 - Tips recommended for added security
						@chmod($dir_up.$nfile, 0664);

						//	Set the dir permissions to 755 - Tips recommended for added security
						@chmod($dir_up, 0755);

					} else {

						//	Re-fetch the file information
						$du_file = pathinfo($nfile);

						//	Rename the file to prevent overwriting
						$nfile = $this->genRandName().$du_file['filename'].".".$du_file['extension'];

						@chmod($dir_up, 0777);

						@move_uploaded_file($tmp_file , $dir_up.$nfile);

						//	Set the file permissions to 664 - Tips recommended for added security
						@chmod($dir_up.$nfile, 0664);

						//	Set the dir permissions to 755 - Tips recommended for added security
						@chmod($dir_up, 0755);

					}

				}

				if (($cmp_f['error'] != UPLOAD_ERR_NO_FILE) && empty($this->brind) && file_exists($dir_up.$nfile))
					return "File caricato con successo";
				else
					return "Errore durante l'upload del file";

			} else {

				return "Campo File vuoto";

			}

		}

	}