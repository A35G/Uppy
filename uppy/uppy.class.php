<?php

	if (file_exists(dirname(__FILE__)."/libs/settings.php"))
		include(dirname(__FILE__)."/libs/settings.php");

	class UpFiles {

		var $url_r = Site_Root;
		var $brind = 0;
		var $txt_d;
		var $d_lang;
		var $lng_data;

		function __construct() {

			$this->txt_d = "";

			$this->d_lang = isset($_SERVER['HTTP_ACCEPT_LANGUAGE']) ? substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2) : NULL;

			if ($this->d_lang === NULL || empty($this->d_lang))
				$this->d_lang = (defined("default_lang") && (default_lang)) ? default_lang : "it";

			$this->getLocale();

		}

		protected function getLocale() {

			if (($this->d_lang != NULL) && @file_exists(dirname(__FILE__)."/locale/".$this->d_lang.".php")) {

				@include(dirname(__FILE__)."/locale/".$this->d_lang.".php");
				$this->lng_data = $up_lang;

			}

			return $this->lng_data;

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

					throw new Exception(sprintf($this->parse_lang('not_chg_dim'), $size));

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

		public function human_filesize($bytes, $decimals = 2) {

			$sz = 'BKMGTP';

			$factor = floor((strlen($bytes) - 1) / 3);

			return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];

		}

		public function parse_lang($text_l) {

			if (isset($this->lng_data) && is_array($this->lng_data) && !empty($this->lng_data)) {

				if (array_key_exists($text_l, $this->lng_data)) {

					$mosy = str_ireplace($text_l, $this->lng_data[$text_l], $text_l, $count);

					if ($count > 0)
						return $mosy;

				}

			}

		}

		public function checkPerms() {

			if (!chmod(realpath(dirname(__FILE)), 0777)) {

				$prs_text = $this->parse_lang("not_enabled");

			} else {

				@chmod(realpath(dirname(__FILE)), 0755);
				$prs_text = $this->parse_lang("enabled");

			}

			return $prs_text;

		}

		public function getPerms($dir) {

			if (is_dir($dir))
				return substr(sprintf('%o', fileperms($dir)), -4);

		}

		public function checkFinfo() {

			if (!function_exists("finfo_file"))
				$dvar = sprintf("<b>%s</b><br /><i>%s</i> - %s", $this->parse_lang("not_enabled"), $this->parse_lang("finfo_advise"), $this->parse_lang("finfo_php_version"));
			else
				$dvar = sprintf("<b>%s</b>", $this->parse_lang("enabled"));

			return $dvar;

		}

		public function up_file_img($cmp_f, $info_file, $tipo_upl) {

			if (isset($cmp_f) && !empty($cmp_f["name"])) {

				if ($cmp_f['error'] === UPLOAD_ERR_INI_SIZE) {

					$this->brind++;
					return $this->parse_lang('size_big');

				}

				$prefix_rand = $this->genRandName();

				$tmp_file = $cmp_f["tmp_name"];

				$sfile = $cmp_f["size"];

				$firstc = $cmp_f["type"];

				if (defined("check_max_dim") && (check_max_dim)) {

					$size_server = $this->detectMaxUploadFileSize();

					$stt_size = (isset($info_file[$tipo_upl]['maxSize']) && !empty($info_file[$tipo_upl]['maxSize'])) ? $info_file[$tipo_upl]['maxSize'] : $size_server;

					$size_check = ($stt_size <= $size_server) ? $stt_size : $size_server;

					if ($cmp_f['size'] > $size_check) {

						$this->brind++;
						return $this->parse_lang('size_big');

					}

				}

				if (function_exists('finfo_file')) {

					$finfo = finfo_open();
					$real_mime = finfo_file($finfo, $tmp_file, FILEINFO_MIME);
					finfo_close($finfo);

				}

				$nfile = str_replace("'", "_", $cmp_f["name"]);

				$unext = pathinfo($nfile, PATHINFO_EXTENSION);

				$original_extension = (false === $pos = strrpos($nfile, '.')) ? '' : substr($nfile, $pos);
				$original_extension = str_replace('.', '', strtolower($original_extension));

				if ($unext != $original_extension) {

					$this->brind++;
					return $this->parse_lang('ext_diff');

				}

				$file_ext = explode(",", $info_file[$tipo_upl]['allowedExtensions']);

				$file_mime = explode(",", $info_file[$tipo_upl]['allowedMIMETypes']);

				$nfile = $prefix_rand.$nfile;

				if (!empty($file_ext)) {

					if (in_array($unext, $file_ext) === FALSE) {

						$this->brind++;
						return $this->parse_lang('ext_not');

					}

				}

				if (!empty($file_mime)) {

					$err_mimet = 0;

					if (in_array($firstc, $file_mime) === false)
						$err_mimet++;

					if (function_exists('finfo_file')) {

						if (in_array($real_mime, $file_mime) === false)
							$err_mimet++;

					}

					if ($err_mimet == 2) {

						$this->brind++;
						return $firstc.'-'.$real_mime.'-'.$this->parse_lang('mime_not');

					}

				}

				$dir_up = realpath(dirname(__FILE__)).$this->getPth(1).$info_file[$tipo_upl]['directory'];

				if (!is_dir($dir_up)) {

					$this->brind++;
					return $this->parse_lang('dir_not');

				}

				if (!file_exists($dir_up.$nfile)) {

					@chmod($dir_up, 0777);

					@move_uploaded_file($tmp_file , $dir_up.$nfile);

					@chmod($dir_up.$nfile, 0664);

					@chmod($dir_up, 0755);

				} else {

					if ($info_file['OverwriteIfExists']) {

						@chmod($dir_up, 0777);

						@move_uploaded_file($tmp_file , $dir_up.$nfile);

						@chmod($dir_up.$nfile, 0664);

						@chmod($dir_up, 0755);

					} else {

						$du_file = pathinfo($nfile);

						$nfile = $this->genRandName().$du_file['filename'].".".$du_file['extension'];

						@chmod($dir_up, 0777);

						@move_uploaded_file($tmp_file , $dir_up.$nfile);

						@chmod($dir_up.$nfile, 0664);

						@chmod($dir_up, 0755);

					}

				}

				if (($cmp_f['error'] != UPLOAD_ERR_NO_FILE) && empty($this->brind) && file_exists($dir_up.$nfile))
					return $this->parse_lang('upl_succ');
				else
					return $this->parse_lang('upl_err');

			} else {

				return $this->parse_lang('emp_field');

			}

		}

	}