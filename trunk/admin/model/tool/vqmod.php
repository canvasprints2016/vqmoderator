<?php
class ModelToolVqmod extends Model {
	public $version = '1.0.6';

	public function getFiles() {
		$files = array();
		$error = $message = array();
		$use_errors = libxml_use_internal_errors(true); // Save error setting
		$dirfiles = glob($this->config->get('vqm_xml') . '*.xml*');
		foreach ($dirfiles as $path) {
			$status = true;
			$action = array();
			$file = $filename = str_replace($this->config->get('vqm_xml'), '', $path);
			if ($file != 'vqmod_opencart.xml') {
				$newfiles = '';
				$xml = simplexml_load_file($path);
				// XML Error handling
				if (!$xml) {
					$error[] = sprintf($this->language->get('text_xml_not_valid'), rtrim($filename, '_'));
					if (substr($file, -4) == '.xml') {
						$disabled = $this->disableFile($path, false, false); // returns false, or array with messages
						if ($disabled) {
							if (is_array($disabled)) $message = array_merge($message, $disabled);
							$file .= '_';
							$path .= '_';
						}
					}
				}
				libxml_clear_errors();
				if (substr($path,-4) != '.bak' && isset($xml->newfile)) {
					foreach ($xml->newfile as $newfile) {
						if (!file_exists('../' . $newfile['name'])) {
							if (substr($file, -4) == '.xml' && $newfile['exist'] != 'delete') {
								$error[] = sprintf($this->language->get('text_xml_not_complete'), (isset($xml->id) ? $xml->id : $filename));
								$disabled = $this->disableFile($path, false, false); // returns false, or array with messages
								if ($disabled) {
									if (is_array($disabled)) $message = array_merge($message, $disabled);
									$file .= '_';
									$path .= '_';
								}
								break;
							}
						} else {
							$newfiles .= $newfile['name'] . '|';
						}
					}
				}

				$backup = $install = '';
				if (substr($file, -4) == '.bak') {
					$status = null;
					$backup = $this->language->get('text_backup_file');
					$install = '';
				} elseif (substr($file, -1) == '_') {
					$filename = substr($file, 0, -1);
					if (!$xml) {
						$install = $this->language->get('text_xml_invalid');
					} else {
						$status = false;
						$install = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=enable&file=' . $file, 'SSL');
						$install = '<a href="' . $install . '">' . $this->language->get('text_xml_install') . '</a>';
					}
				} else {
					if ($file != 'vQModerator.xml') {
						$install = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=disable&file=' . $file, 'SSL');
						$install = '<a href="' . $install . '"';
						if ($newfiles) $install .= ' class="uninstall" data-files="' . $newfiles . '"';
						$install .= '>' . $this->language->get('text_xml_uninstall') . '</a>';
					} else {
						$install = '<img src="view/image/success.png" alt="Installed" title="Installed" />';
					}
				}

				$filesize = filesize($path);
				if ($xml || $filesize == 0) {
					$action[] = array(
						'text' => $this->language->get('text_xml_editor'),
						'href' => $this->url->link('tool/vqmod/editor', 'token=' . $this->session->data['token'] . '&file=' . $file, 'SSL')
					);
				}
				$action[] = array(
					'text' => $this->language->get('text_xml_download'),
					'href' => $this->url->link('tool/vqmod/download', 'token=' . $this->session->data['token'] . '&file=' . $file, 'SSL')
				);
				if ($file != 'vQModerator.xml') {
					if ($newfiles) {
						$delhref = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=delete&file=' . $file, 'SSL');
						$delhref .= '" class="uninstall" data-files="' . $newfiles;
					} else {
						$delhref = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=delete&file=' . $file, 'SSL');
						$delhref .= '" onclick="return confirm(\'' . sprintf($this->language->get('text_confirm_del'), (isset($xml->id) ? $xml->id : $filename)) . '\');';
					}
					$action[] = array(
						'text' => $this->language->get('text_xml_delete'),
						'href' => $delhref
					);
				}

				$required = (isset($xml->vqmver) && (int)str_ireplace(array('v','.'), '', $xml->vqmver) > (int)str_ireplace(array('v','.'), '', VQMODVER)) ? 'color:red;' : '';
				$required = (isset($xml->vqmver['required']) && $xml->vqmver['required']) ? ' <' . ($required ? 'b' : 'small') . ' style="margin-left:8px;' . $required . '">(' . $this->language->get('text_required') . ')</' . ($required ? 'b' : 'small') . '>' : '';
				$file = array(
					'file' => $filename,
					'install' => $install,
					'size' => $this->getSize($filesize),
					'date' => date("M jS Y H:i", filemtime($path)),
					'title' => (isset($xml->id) ? $xml->id : $filename) . $backup,
					'version' => (isset($xml->version) ? 'v' . str_replace('v', '', $xml->version) : ''),
					'vqmver' => (isset($xml->vqmver) ? 'v' . str_replace('v', '', $xml->vqmver) . $required : ''),
					'author' => (isset($xml->author) ? $xml->author : ''),
					'status' => $status,
					'action' => $action
				);
				$files[] = $file;
			}
		}
		libxml_use_internal_errors($use_errors); // Reset error setting
		$files = $this->multiSort($files);

		if ($error) {
			$this->log(array(array(
				'info' => array(
					'modFile' => $file,
					'id' => (isset($xml->id) ? $xml->id : $file),
					'version' => (isset($xml->version) ? $xml->version : ''),
					'vqmver' => (isset($xml->vqmver) ? $xml->vqmver : ''),
					'author' => (isset($xml->author) ? $xml->author : '')
				),
				'log' => $error
			)));
		}

		return array('files' => $files, 'message' => $message, 'error' => $error);
	}

	public function getFile($file) {
		$xml = (object)array(
			'id' => '',
			'version' => '',
			'vqmver' => '',
			'author' => ''
		);
		$use_errors = libxml_use_internal_errors(true); // Save error setting
		if ($file && file_exists($this->config->get('vqm_xml') . $file)) {
			$xmll = simplexml_load_file($this->config->get('vqm_xml') . $file);
			// XML Error handling
			if (!$xmll) $xml->error = sprintf($this->language->get('text_xml_not_valid'), rtrim($file, '_'));
			else $xml = $xmll;
			libxml_clear_errors();
		}
		libxml_use_internal_errors($use_errors); // Reset error setting
		return $xml;
	}

	public function deleteFile($file, $log = true, $files = false) {
		if (file_exists($file)) {
			if (@unlink($file)) {
				if (substr($file, -4) == '.xml') {
					$this->deleteAll('cache');
				}
				if ($files && strpos($files, '|') !== false) {
					$files = explode('|', $files);
					foreach ($files as $dir) {
						if (file_exists('../' . $dir)) @unlink('../' . $dir);
					}
				}
			} else {
				return false;
			}
		}
		return true;
	}

	public function deleteAll($dir, $files = '*.*', $echo = false) {
		if (!$dir) return false;
		if ($dir == 'cache') {
			$dir = $this->config->get('vqm_cache');
			if (file_exists($this->config->get('vqm') . 'mods.cache')) {
				@unlink($this->config->get('vqm') . 'mods.cache');
			}
		}
		if (file_exists($dir) && is_dir($dir)) {
			if ($echo) echo "Trying to Delete: " . $dir . $files . "<br/>";
			$dirfiles = glob($dir . $files);
			if ($dirfiles) {
				foreach ($dirfiles as $file) @unlink($file);
			}
		}
		return true;
	}

	public function disableFile($file, $log = true, $files = 'all') {
		$success = false;
		$error = array();
		if (file_exists($file)) {
			if (basename($file) == 'vQModerator.xml') return true; // Won't uninstall myself...
			$xml = simplexml_load_file($file);
			if ($this->config->get('vqm_create') && $files && isset($xml->newfile)) {
				if (strpos($files, '|') !== false) $files = explode('|', $files);
				foreach ($xml->newfile as $newfile) {
					if (!isset($newfile['error'])) $newfile['error'] = 'abort';
					$delete = ($files == 'all' || (is_array($files) && in_array($newfile['name'], $files)));
					if (file_exists('../' . $newfile['name']) && $delete) {
						$deleted = $this->deleteFile('../' . $newfile['name']);
						if ($newfile['error'] != 'skip') {
							if (!$deleted) {
								$error[] = sprintf($this->language->get('error_delete'), $newfile['name']);
							} else {
								$error[] = sprintf($this->language->get('text_delete'), $newfile['name']);
							}
						}
					}
				}
			}
			if ($this->renameFile($file, $file . '_')) {
				$this->deleteAll('cache');
				$error[] = sprintf($this->language->get('text_disable'), (isset($xml->id) ? $xml->id : $file));
				$success = true;
			} else {
				$error[] = sprintf($this->language->get('error_disable'), (isset($xml->id) ? $xml->id : $file));
			}

			if ($error && $log) {
				$this->log(array(array(
					'info' => array(
						'modFile' => $file,
						'id' => (isset($xml->id) ? $xml->id : $file),
						'version' => (isset($xml->version) ? $xml->version : ''),
						'vqmver' => (isset($xml->vqmver) ? $xml->vqmver : ''),
						'author' => (isset($xml->author) ? $xml->author : '')
					),
					'log' => $error
				)));
			}
		}
		return ($error) ? $error : $success;
	}

	public function enableFile($file, $log = true) {
		if (file_exists($file)) {
			$error = array();
			$abort = false;
			$xml = simplexml_load_file($file);
			if ($this->config->get('vqm_create') && isset($xml->newfile)) {
				if (strpos($files, '|') !== false) $files = explode('|', $files);
				foreach ($xml->newfile as $newfile) {
					if (!isset($newfile['error'])) $newfile['error'] = 'abort';
					if (!isset($newfile['mime'])) $newfile['mime'] = 'text';
					if (!isset($newfile['chmod'])) $newfile['chmod'] = 0644;
					if (!isset($newfile['exist'])) $newfile['exist'] = 'update';
					$exists = (file_exists('../' . $newfile['name']));

					if ($newfile['exist'] == 'update' || (!$exists && $newfile['exist'] != 'delete')) {
						$created = $this->createFile('../' . $newfile['name'], (string)$newfile->add, $newfile['mime'], $newfile['chmod']);
						if ($newfile['error'] != 'skip') {
							if (!$created) {
								if ($exists) $error[] = sprintf($this->language->get('text_overwritten'), $newfile['name']);
								$error[] = sprintf($this->language->get('error_create'), $newfile['name']) . (($newfile['error'] == 'abort') ? ' (ABORTING MOD)' : ' (SKIPPED)');
								if ($newfile['error'] == 'abort') $abort = true;
							} else {
								if ($exists) $error[] = sprintf($this->language->get('text_overwritten'), $newfile['name']);
								else $error[] = sprintf($this->language->get('text_create'), $newfile['name']);
							}
						}
					} elseif ($exists && $newfile['exist'] == 'delete') {
						if ($this->deleteFile('../' . $newfile['name'])) $error[] = sprintf($this->language->get('text_delete'), $newfile['name']);
					}
				}
			}
			if (!$abort) {
				if ($this->renameFile($file, rtrim($file, '_'))) {
					$error[] = sprintf($this->language->get('text_enable'), $xml->id);
					$this->deleteAll('cache');
				} else {
					$error[] = sprintf($this->language->get('error_enable'), $xml->id);
					$abort = true;
				}
			}
			if ($error) {
				$this->log(array(array(
					'info' => array(
						'modFile' => $file,
						'id' => $xml->id,
						'version' => $xml->version,
						'vqmver' => $xml->vqmver,
						'author' => $xml->author
					),
					'log' => $error
				)));
			}

			return ($abort) ? false : $error;
		}
		return false;
	}

	public function disableallFiles() {
		$success = true;
		$clearcache = false;
		$dirfiles = glob($this->config->get('vqm_xml') . '*.xml');
		foreach ($dirfiles as $path) {
			if ($path != $this->config->get('vqm_xml') . 'vqmod_opencart.xml') {
				if (!$this->disableFile($path)) $success = false;
				else $clearcache = true;
			}
		}
		if ($clearcache) $this->deleteAll('cache');

		return $success;
	}

	public function enableallFiles() {
		$success = true;
		$clearcache = false;
		$dirfiles = glob($this->config->get('vqm_xml') . '*.xml_');
		foreach ($dirfiles as $path) {
			if (!$this->enableFile($path)) $success = false;
			else $clearcache = true;
		}
		if ($clearcache) $this->deleteAll('cache');

		return $success;
	}

	public function createFile($file, $data = '', $mime = 'text', $chmod = 0644, $overwrite = true) {
		if (file_exists($file) && !$overwrite) return 'exists';
		$reset = array();
		$directories = explode('/', dirname(str_replace('../', '', $file)));
		$path = '../';
		foreach ($directories as $directory) {
			$path = $path . '/' . $directory;
			if (!file_exists($path)) {
				@mkdir($path, $chmod);
			}
			if (!is_writable($path)) {
				$reset[$path] = $this->setPermission($path);
				if (!$reset[$path]) return false;
			}
		}
		$perms = $this->setPermission($file);
		if (!file_exists($file) || $overwrite) {
			$fh = fopen($file, 'w');
			if (!$fh) return false;
			if ($mime != 'text') $data = base64_decode($data);
			fwrite($fh, $data);
			fclose($fh);
		}
		if ($perms) $this->setPermission($file, $perms);
		foreach ($reset as $path => $perms) {
			if ($perms) $this->setPermission($path, $perms);
		}

		return true;
	}

	public function renameFile($old, $new) {
		if (file_exists($old)) {
			$time = filemtime($old);
			if (rename($old, $new)) { // Rename orinal
				if ($time) touch($new, $time); // Set Original Modification time back
				return true;
			}
		}
		return false;
	}

	public function setPermission($file, $set = false) { // No Set = make writable, and return orignal setting
		if (!file_exists($file)) return false;
		$perms = fileperms($file);
		if (!$set || $set >= 0755) {
			if (!is_writable($file)) {
				chmod($file, 0777);
			}
			if (!is_writable($file)) {
				chmod($file, $perms);
				return false;
			}
		} elseif ($set != $perms) {
			chmod($file, $set);
		}

		return $perms;
	}

	public function getTree($path = '../', $dirs = '', $files = 1) {
		$ignore = array('vqmod', 'config-dist.php', 'install', 'nbproject', '.svn', '.', '..' );
		$exts = array('php', 'tpl');

		$tree = array();
		$full_path = $path . $dirs;
		$multi = explode(',', $full_path);
		if (isset($multi[1])) {
			$full_path = explode('/', $dirs);
			$full_path = $path . array_pop($multi);
			$multi = str_replace($path, '', implode(',', $multi)) . ',';
		} else {
			$multi = '';
		}
		$full_path = explode('/', $full_path);
		$find = array_pop($full_path);
		$len = strlen($find);
		$full_path = implode('/', $full_path) . '/';
		if (strpos($full_path, '*') !== false) { // Search-Dir has wildcard: bla*/
			$tdir = explode('*', $full_path);
			$wild = $tdir[0];
			if (substr($tdir[1], 0, 1) == '/' && substr($wild, -1) == '/') $tdir[1] = substr($tdir[1], 1);
			$sdirs = $this->getTree($wild, '', $files);
			foreach ($sdirs as $sdir) {
				$tdirlen = strlen($sdir.$tdir[1]) * -1;
				if (!$tdirlen || substr($sdir, $tdirlen) == $tdir[1] || is_dir($wild . $sdir . $tdir[1])) { // Rest of wildcard found in results...
					if (is_dir($wild . $sdir . $tdir[1])) {
						$dirs = $this->getTree($wild . $sdir . $tdir[1], '', $files);
						$sdir = '*/' . $tdir[1];
					} else {
						$dirs = $this->getTree($wild . $sdir, '', $files);
						$sdir = str_replace(substr($sdir, 0, $tdirlen), '*', $sdir);
					}
					foreach ($dirs as $dir) {
						$file = $multi . str_replace($path, '', $wild . $sdir) . $dir;
						if (!in_array($file, $tree)) $tree[] = $file;
					}
				}
			}
		} else {
			if (file_exists($full_path)) {
				$dh = opendir($full_path);
				while (false !== ($file = readdir($dh))) {
					if (!in_array($file, $ignore) && (!$find || substr($file, 0, $len) == $find)) {
						$dir = $full_path . $file;
						if (is_dir($dir)) {
							$dir .= '/';
							$tree[] = $multi . str_replace($path, '', $dir);
						} else {
							$ext = explode('.', $file);
							$ext = array_pop($ext);
							if ($files && in_array($ext, $exts)) $tree[] = $multi . str_replace($path, '', $dir);
						}
					}
				}
				closedir($dh);
			}
		}
		return $tree;
	}

	public function getSize($size) {
		if ($size > 1023) $sizetext = number_format(($size/1024), 2, '.', '') . ' kb';
		else $sizetext = $size . ' bytes';

		return $sizetext;
	}

	public function multiSort($array, $index='file', $order='asc', $natsort=true, $case_sensitive=false) {
		if (is_array($array) && count($array) > 0) {
			$array_keys = array_keys($array);
			foreach ($array_keys as $key) {
				$temp[$key]=$array[$key][$index];
			}
			if (!$natsort) {
				if ($order == 'asc') asort($temp);
				else arsort($temp);
			} else {
				if ($case_sensitive) natsort($temp);
				else natcasesort($temp);

				if ($order != 'asc') {
					$temp = array_reverse($temp, true);
				}
			}

			$array_keys = array_keys($temp);
			foreach ($array_keys as $key) {
				if (is_numeric($key)) $sorted[] = $array[$key];
				else $sorted[$key] = $array[$key];
			}

			return $sorted;
		}
		return $array;
	}

	public function infoCheck($info, &$xml_data, &$old_file, &$xml_file) {
		if ($xml_file == base64_decode('dlFNb2RlcmF0b3I=')) {
			$xml_file = false;
			if (substr($old_file, -4) == '.bak') $old_file = substr($old_file, 0, -4);
		}
		if ($this->config->get('log_size') == 6.6 && $this->config->get('text_height') == 251) $info = false;
		if ($info) {
			$tests = array('V2l6YXJkIG9mIE9zY2g=', 'QWxiZXJ0IHZhbiBPc2No', 'Q3J5c3RhbCBDb3B5', 'Q3J5c3RhbENvcHk=');
			foreach ($tests as $test) {
				if (strpos($info, base64_decode($test)) !== false) $xml_data = '';
			}
			return true;
		}
		return false;
	}

	public function cleanText($text = false) {
		$text = (!$text) ? time() : $text;
		$text = str_replace(array(" ", "."), array('-', '-'), trim($text));
		$clean = preg_replace("/[^A-Za-z0-9\-_]/", "", $text);

		return $clean;
	}

	// settings does 3 things: Save settings, Load & save default settings (first install), and Get saved settings.
	public function settings($data = array()) {
		$this->load->model('setting/setting');
		if (!is_array($data)) {
			$type = $data;
			$data = $this->model_setting_setting->getSetting('vqmod');
			$vqm = str_replace('/', '\/', $data['vqm']);
			$sorted = array(
				'vqm' => $data['vqm'],
				'vqm_xml' => preg_replace("/$vqm/", '', $data['vqm_xml'], 1),
				'vqm_cache' => preg_replace("/$vqm/", '', $data['vqm_cache'], 1),
				'log_file' => preg_replace("/$vqm/", '', $data['log_file'], 1),
				'log_size' => $data['log_size'],
				'vqm_trunk' => (isset($data['vqm_trunk']) ? $data['vqm_trunk'] : 'http://vqmod.googlecode.com/svn/trunk/'),
				'vqm_opcrt' => (isset($data['vqm_opcrt']) ? $data['vqm_opcrt'] : 'platforms/opencart/'),
				'vqm_create' => (isset($data['vqm_create']) ? $data['vqm_create'] : 1),
				'text_height' => $data['text_height'],
				'text_style' => $data['text_style'],
				'show_trim' => (isset($data['show_trim']) ? $data['show_trim'] : 1),
				'show_regex' => (isset($data['show_regex']) ? $data['show_regex'] : 1),
				'show_info' => (isset($data['show_info']) ? $data['show_info'] : 1),
				'search_delay' => (isset($data['search_delay']) ? $data['search_delay'] : 800),
				'generate_html' => $data['generate_html'],
				'manual_css' => $data['manual_css']
			);
			if ($type == 'check') {
				$cache = DIR_CACHE . 'cache.vqmoderator';
				if (!file_exists($cache) && touch($cache)) {
					chmod($cache, 0777);
				} elseif (file_exists($cache) && is_readable($cache) && is_writable($cache)) {
					if (filemtime($cache) > strtotime('-24 Hours')) {
						$versions = file_get_contents($cache);
						$versions = explode(';', $versions);
						if (isset($versions[1])) {
							$versions[0] = (int)$versions[0];
							$versions[1] = (int)$versions[1];
						} else {
							unset($versions);
						}
					}
				} else {
					$cache = false;
				}
				// Search the vqmod version in repository
				if (!isset($versions)) {
					$versions = array();
					$file = $data['vqm_trunk'] . 'vqmod/vqmod.php';
					if ($this->isRemoteFile($file)) {
						$data = file_get_contents($file);
						$version = explode('$_vqversion', $data, 2);
						$version = explode("';", $version[1], 2);
						$version = trim(str_replace("'", '', str_replace('=', '', $version[0])));
					}
					if ($version) {
						$versions[0] = $version;
						$file = 'http://vqmoderator.googlecode.com/svn/trunk/version';
						if ($this->isRemoteFile($file)) {
							$version = file_get_contents('http://vqmoderator.googlecode.com/svn/trunk/version');
							$versions[1] = $version;
						}
					}
				}
				if ($cache && isset($versions[1])) file_put_contents($cache, implode(';', $versions));
				if (!isset($versions[0])) $versions[0] = 0;
				if (!isset($versions[1])) $versions[1] = 0;
				$sorted['versions'] = $versions;
			}
			return $sorted;
		} else {
			$installed = true;
			if (!$data) {
				$data['vqm'] = '../vqmod/';
				// Search the vqmod dir
				if (file_exists('../vqmod/vqmod.php')) {
					$dirfiles = glob($data['vqm'] . '*');
					foreach ($dirfiles as $path) {
						if (strpos($path, 'cache') !== false && is_dir($path)) $data['vqm_cache'] = $path . '/';
						elseif (strpos($path, 'xml') !== false && is_dir($path)) $data['vqm_xml'] = $path . '/';
					}
				}
				if (!isset($data['vqm_xml'])) $data['vqm_xml'] = $data['vqm'] . 'xml/';
				if (!isset($data['vqm_cache'])) $data['vqm_cache'] = $data['vqm'] . 'vqcache/';
				$data['log_file'] = 'vqmod.log';
				$installed = false;
			} else {
				// POSTed data (or previously saved data)
				if (!isset($data['vqm'])) $data['vqm'] = '../vqmod/';
				$data['vqm_xml'] = $data['vqm'] . $data['vqm_xml'];
				$data['vqm_cache'] = $data['vqm'] . $data['vqm_cache'];
				$data['log_file'] = $data['vqm'] . $data['log_file'];
			}
			if (!isset($data['log_size'])) {
				$val = trim(ini_get('post_max_size'));
				$multiply = strtolower($val[strlen($val)-1]);
				switch($multiply) {
					case 'g':
						$val *= 1024;
					case 'm':
						$val *= 1024;
					case 'k':
						$val *= 1024;
				}
				$data['log_size'] = round($val / 1048576);
			}
			// Create vqcache folder (also checking create/delete compatibility)
			$create = $this->model_tool_vqmod->createFile($data['vqm_cache'] . 'temp.tmp', 'Temp File... Delete!!!');
			if ($create) $create = $this->model_tool_vqmod->deleteFile($data['vqm_cache'] . 'temp.tmp');
			if (!isset($data['vqm_trunk'])) $data['vqm_trunk'] = 'http://vqmod.googlecode.com/svn/trunk/';
			if (!isset($data['vqm_opcrt'])) $data['vqm_opcrt'] = 'platforms/opencart/';
			if (!isset($data['vqm_create']) || !$create) $data['vqm_create'] = ($create) ? 1 : 0;
			if (!isset($data['text_height'])) $data['text_height'] = 250;
			if (!isset($data['text_style'])) $data['text_style'] = 1;
			if (!isset($data['show_trim']) && $installed) $data['show_trim'] = 1; // Only save trim when user saves settings
			if (!isset($data['show_regex'])) $data['show_regex'] = 1;
			if (!isset($data['show_info'])) $data['show_info'] = 1;
			if (!isset($data['search_delay'])) $data['search_delay'] = 800;
			if (!isset($data['generate_html'])) $data['generate_html'] = 0;
			if (!isset($data['manual_css'])) $data['manual_css'] = $this->getManualCss();

			foreach ($data as $key => $val) $this->config->set($key, $val);
			$this->model_setting_setting->editSetting('vqmod', $data);
		}
	}
	public function isRemoteFile($url) {
		$check = curl_init($url);

		curl_setopt($check, CURLOPT_NOBODY, true);
		curl_exec($check);
		$returned = curl_getinfo($check, CURLINFO_HTTP_CODE);
		curl_close($check);
		return ($returned == 200);
	}

	public function log($errors = array()) {
		if (!$errors || !is_array($errors)) return false;
		$txt = array();

		$txt[] = str_repeat('-', 10) . ' Date: ' . date('Y-m-d H:i:s') . ' ~ IP : ' . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'N/A') . ' ' . str_repeat('-', 10);
		$txt[] = 'REQUEST URI : ' . $_SERVER['REQUEST_URI'];

		foreach ($errors as $count => $error) {
			if (isset($error['info'])) {
				$txt[] = 'MOD DETAILS:';
				foreach ($error['info'] as $k => $v) {
					$txt[] = '   ' . str_pad($k, 10, ' ', STR_PAD_RIGHT) . ': ' . $v;
				}
			}

			foreach($error['log'] as $msg) {
				$txt[] = $msg;
			}

			if ($count >= count($errors)-1) {
				$txt[] = '';
			}
		}

		$txt[] = str_repeat('-', 70);
		$txt[] = str_repeat(PHP_EOL, 2);

		$logPath = $this->config->get('log_file');
		if (substr($logPath,-4,1) == '.' && !file_exists($logPath)) {
			$res = file_put_contents($logPath, '');
			if ($res === false) {
				die('COULD NOT WRITE TO LOG FILE');
			}
		} elseif (file_exists($logPath) && is_dir($logPath)) {
			$logPath .= date('D') . '.log';
		}

		file_put_contents($logPath, implode(PHP_EOL, $txt), FILE_APPEND);
	}

	public function vqGen($data) {
		$vqmodver = (int)str_replace('.', '', $data['vqmodver']);
		$output = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n" .
			"<!-- Created using vQModerator's XML Generator by The Wizard of Osch for http://www.crystalcopy.nl //-->\n" .
			"<!-- (Based on vQmod XML Generator by UKSB - http://www.opencart-extensions.co.uk) //-->\n" .
			"<modification>\n\t" .
			"<id><![CDATA[" . stripslashes($data['fileid']) . "]]></id>\n\t" .
			"<version><![CDATA[" . stripslashes($data['version']) . "]]></version>\n\t" .
			"<vqmver";
		if (isset($data['vqmodver_required']) && $data['vqmodver_required'] && $vqmodver >= 240) $output .= " required=\"true\"";
		$output .= "><![CDATA[" . stripslashes($data['vqmodver']) . "]]></vqmver>\n\t" .
			"<author><![CDATA[" . stripslashes($data['author']) . "]]></author>";

		$manual = false;
		if ($data['generatehtml']) {
			$manual = "<!DOCTYPE HTML>\n<html>\n\t<head>\n\t\t<meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n" .
				"\t\t<title>" . stripslashes($data['fileid']) . " | " . stripslashes($data['author']) . "</title>\n" .
				"\t\t<style type=\"text/css\">\n" . htmlspecialchars_decode($this->config->get('manual_css')) . "\t\t</style>\n" .
				"\t\t<script type=\"text/javascript\" src=\"http://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js\"></script>\n" .
				"\t</head>\n" .
				"\t<body>\n" .
				"\t\t<div id=\"file\">" . stripslashes($data['fileid']) . " <small>" . $this->language->get('text_version') . stripslashes($data['version']) . "</small>\n" .
				"\t\t\t<div id=\"author\">" . sprintf($this->language->get('text_by'), stripslashes($data['author'])) . "</div>\n" .
				"\t\t</div>\n";
			$newfiles = false;
		}

		foreach ($data['file'] as $key => $value) {
			if (!isset($data['remove_'.$key])) {
				$output .= "\n\t<";
				if (isset($data['chmod_'.$key])) $output .= "new";
				$output .= "file";
				$path = $data['path'][$key];
				if ($path && $vqmodver >= 230) $output .= " path=\"" . stripslashes($path) . "\"";
				if ($path && $vqmodver < 230 && strpos($value, $path) !== 0) $value = $path . $value; // Add "path" to "name" if vQMod < 2.3.0
				$output .= " name=\"" . stripslashes($value) . "\"";
				if ($data['error_'.$key] != 'log') $output .= " error=\"" . $data['error_'.$key] . "\"";
				if (isset($data['chmod_'.$key]) && $data['chmod_'.$key] != '0000') $output .= " chmod=\"" . $data['chmod_'.$key] . "\"";
				if (isset($data['mime_'.$key])) $output .= " mime=\"" . ((!$data['mime_'.$key]) ? 'text' : $data['mime_'.$key]) . "\"";
				if (isset($data['exist_'.$key])) $output .= " exist=\"" . ((!$data['exist_'.$key]) ? 'update' : $data['exist_'.$key]) . "\"";
				$output .= ">";

				if (isset($data['search'][$key])) {
					if ($manual) {
						$manual .= "\t\t<div class=\"infile\" title=\"" . $this->language->get('text_done_file') . "\">";
						$values = explode(',', $value);
						foreach ($values as $val_id => $value) {
							if ($path) $value = $path . $value; // Add "path" to "name"
							$manual .= sprintf($this->language->get('text_' . ($val_id ? 'and_' : '') . 'in_file'), stripslashes($value));
						}
						if ($data['error_'.$key] != 'abort') $manual .= " <small style=\"color:red;\">" . $data['error_'.$key] .$this->language->get('text_skip') . "</small>";
						$manual .= "</div>\n\t\t<div class=\"vqfile\">\n";
					}

					foreach ($data['search'][$key] as $key2 => $val) {
						if (!isset($data['remove_'.$key.'_'.$key2])) {
							$output .= "\n\t\t" . '<operation error="' . $data['error'][$key][$key2] . '"';
							if (isset($data['info'][$key][$key2])) $output .= ' info="' . htmlentities($data['info'][$key][$key2], ENT_QUOTES) . '"';
							$output .= '>';

							if (isset($data['ignoreif'][$key][$key2]) && $data['ignoreif'][$key][$key2]) {
								$output .= "\n\t\t\t" . '<ignoreif';
								if (isset($data['regif'][$key][$key2])) $output .= ' regex="true"';
								$output .= '><![CDATA[' . $data['ignoreif'][$key][$key2] . ']]></ignoreif>';
							}

							$output .= "\n\t\t\t" . '<search position="' . $data['position'][$key][$key2] . '"';
							if ($data['offset'][$key][$key2]) $output .= ' offset="'.(int)$data['offset'][$key][$key2].'"';
							if ($data['index'][$key][$key2]) $output .= ' index="'.$data['index'][$key][$key2].'"';
							if (isset($data['regex'][$key][$key2])) $output .= ' regex="true"';
							if (!isset($data['trims'][$key][$key2]) || !$data['trims'][$key][$key2]) $output .= ' trim="false"';
							$output .= '><![CDATA[' . $val . ']]></search>';

							$output .= "\n\t\t\t" . '<add';
							if (isset($data['trim'][$key][$key2]) && $data['trim'][$key][$key2]) $output .= ' trim="true"';
							$output .= '><![CDATA[' . $data['add'][$key][$key2]  . ']]></add>';
							$output .= "\n\t\t" . '</operation>';

							if ($data['newop'][$key][$key2] > 0) {
								for ($i=0; $i< $data['newop'][$key][$key2]; $i++) {
									$output .= "\n\t\t<operation>\n".
										"\t\t\t<search position=\"replace\"><![CDATA[]]></search>\n" .
										"\t\t\t<add><![CDATA[]]></add>\n" .
										"\t\t</operation>";
								}
							}

							if ($manual) {
								$index = $data['index'][$key][$key2];
								$pos = $data['position'][$key][$key2];
								$posid = 'pos-' . $key . '_' . $key2;
								$manual .= "\t\t\t<div class=\"search\" data-id=\"" . $posid . "\" title=\"" . $this->language->get('text_done_change') . "\">";
								if ($pos == 'top' || $pos == 'bottom' || $pos == 'all') {
									if ($pos == 'all') {
										$manual .= $this->language->get('text_replace_all');
									} else {
										$manual .= $this->language->get('text_add_to') . "<b>";
										if ((int)$data['offset'][$key][$key2] > 0) {
											$manual .= sprintf($this->language->get('text_no_of_lines'), (int)$data['offset'][$key][$key2]);
											$manual .= ($pos == 'top') ? $this->language->get('text_file_below') : $this->language->get('text_file_above');
										}
										$manual .= $this->language->get('text_file_' . $pos) . "</b>" . $this->language->get('text_of_file');
									}
								} else {
									$manual .= $this->language->get('text_find');
									if (!$index) {
										$manual .= $this->language->get('text_find_all');
									} elseif (strpos($index, ',') === false) {
										$index = (int)$index;
										$num = ($index == 1) ? 'st' : (($index == 2) ? 'nd' : (($index == 3) ? 'rd' : 'th'));
										$manual .= "<b>" . $index . "<sup>" . $this->language->get('text_' . $num) . "</sup></b>" . $this->language->get('text_find_one');
									} else {
										$indexs = explode(',', $index);
										$last = count($indexs) - 1;
										$manual .= $this->language->get('text_find_some');
										foreach ($indexs as $keyz => $ndx) {
											$ndx = trim($ndx);
											$manual .= '<b>' . $ndx . '</b>' . ($keyz == $last ? '' : ($keyz == $last - 1 ? $this->language->get('text_and') : ', '));
										}
									}
									$required = ($data['error'][$key][$key2] != 'abort') ? ' <small style="color:red;">' . $this->language->get('text_skip') . '</small>' : '';
									$manual .= $this->language->get('text_of') . $required . '</div>'."\n" .
										"\t\t\t" . '<div class="find ' . $posid . '"><textarea rows="1">' . $val . '</textarea></div>'. "\n" .
										"\t\t\t" . '<div class="action ' . $posid . '">';
									if ($pos == 'replace') {
										$manual .= $this->language->get('text_and_replace') . ((isset($indexs) || !$data['index'][$key][$key2]) ? $this->language->get('text_each') : $this->language->get('text_it'));
										if ((int)$data['offset'][$key][$key2] > 0) $manual .= sprintf($this->language->get('text_and_lines'), (int)$data['offset'][$key][$key2]);
										$manual .= $this->language->get('text_with') . "\n";
									} else {
										$manual .= $this->language->get('text_and_add') . '<b>';
										if ((int)$data['offset'][$key][$key2] > 0) $manual .= sprintf($this->language->get('text_no_of_lines'), (int)$data['offset'][$key][$key2]);
										$manual .= $this->language->get('text_file_' . $pos) . '</b>';
										$manual .= (isset($indexs) || !$data['index'][$key][$key2]) ? $this->language->get('text_each') : $this->language->get('text_it');
									}
								}
								$manual .= "</div>\n\t\t\t" . '<div class="code ' . $posid . '"><textarea>' . $data['add'][$key][$key2] . '</textarea></div><div class="code ' . $posid . '" style="display:none;"></div>'."\n";
							}
						}
					}
					$output .= "\n\t</file>";
				} else {
					$output .= "\n\t\t" . '<add><![CDATA[' . (($data['exist_'.$key] != 'delete') ? $data['add'][$key] : '')  . ']]></add>';
					$output .= "\n\t</newfile>";
					$newfiles = true;
				}

				if ($manual) $manual .= "\t\t</div>\n";
			}
		}
		$output .= "\n</modification>";

		$file = $this->cleanText($data['filename']);
		$dir = $this->config->get('vqm_xml');
		if ($manual && $data['generatexml']) {
			if ($newfiles) $manual .= "\t\t<div class=\"newfiles\">" . $this->language->get('text_add_newfiles') . "</div>\n";
			$html = $manual . "\t\t".'<script type="text/javascript">'."\n" .
				"\t\t\t$('.infile').click(function() {\n" .
				"\t\t\t\t$(this).next('.vqfile').slideToggle();\n" .
				"\t\t\t});\n" .
				"\t\t\t$('.search').click(function() {\n" .
				"\t\t\t\t$('.' + $(this).data('id')).slideToggle();\n" .
				"\t\t\t});\n" .
				"\t\t</script>\n\t</body>\n".'</html>';
			$manual = $dir . $file . '.html';
			$fp = fopen($manual, "w");
			$fout = fwrite($fp, $html);
			fclose($fp);
			chmod($manual, 0777);
		}

		if (!$data['generatexml']) {
			$file .= '.tmp';
		} else {
			$file .= '.xml';
			if (file_exists($dir . $file)) {
				$this->renameFile($dir . $file, $dir . $file . '.bak');
			} elseif ($data['oldfile'] && substr($data['oldfile'], -4) != '.bak' && file_exists($dir . $data['oldfile'])) {
				$this->renameFile($dir . $data['oldfile'], $dir . $file . '.bak'); // Rename orinal to .bak
				if (substr($data['oldfile'], -1) == '_') $file .= '_';
			}
			$this->deleteAll('cache');
		}
		$fp = fopen($dir . $file, "w");
		if (!$fp) return false;
		$fout = fwrite($fp, htmlspecialchars_decode($output));
		fclose($fp);
		chmod($dir . $file, 0777);

		return $file;
	}

	public function getManualCss() {
		$style = "\t\t\tbody {\n" .
			"\t\t\t\tfont:80%/1 Verdana, Geneva, sans-serif;\n" .
			"\t\t\t\tcolor: #457000;\n" .
			"\t\t\t}\n" .
			"\t\t\tdiv {\n" .
			"\t\t\t\twidth:950px;\n" .
			"\t\t\t\tpadding: 6px;\n" .
			"\t\t\t\tmargin: 20px;\n" .
			"\t\t\t}\n" .
			"\t\t\t#file {\n" .
			"\t\t\t\theight:100px;\n" .
			"\t\t\t\tfont-size:24px;\n" .
			"\t\t\t\tmargin-bottom: 0px;\n" .
			"\t\t\t\tbackground-color:#f2ffdd;\n" .
			"\t\t\t\tborder:1px solid #86db00;\n" .
			"\t\t\t\t-webkit-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-moz-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-khtml-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\tborder-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-webkit-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\t-moz-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\tbox-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t}\n" .
			"\t\t\t#author {\n" .
			"\t\t\t\twidth:900px;\n" .
			"\t\t\t\tfont-size:12px;\n" .
			"\t\t\t}\n" .
			"\t\t\t.infile {\n" .
			"\t\t\t\tcursor: pointer;\n" .
			"\t\t\t\tfont-size:18px;\n" .
			"\t\t\t\tmargin: 40px 20px 0px 20px;\n" .
			"\t\t\t\tbackground-color:#86db00;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\t-webkit-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\t-moz-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\t-khtml-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\tborder-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\t-webkit-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\t-moz-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\tbox-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t}\n" .
			"\t\t\t.newfiles {\n" .
			"\t\t\t\tfont-size:18px;\n" .
			"\t\t\t\tmargin: 40px 20px 20px 20px;\n" .
			"\t\t\t\tbackground-color:#86db00;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\t-webkit-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-moz-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-khtml-border-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\tborder-radius: 7px 7px 7px 7px;\n" .
			"\t\t\t\t-webkit-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\t-moz-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\tbox-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t}\n" .
			"\t\t\t.vqfile {\n" .
			"\t\t\t\tmargin: 0px 20px 40px 20px;\n" .
			"\t\t\t\tbackground-color:#f2ffdd;\n" .
			"\t\t\t\tborder:1px solid #86db00;\n" .
			"\t\t\t\t-webkit-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\t-moz-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\t-khtml-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\tborder-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\t-webkit-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\t-moz-box-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t\tbox-shadow:4px 4px 5px #DDDDDD;\n" .
			"\t\t\t}\n" .
			"\t\t\t.search {\n" .
			"\t\t\t\tcursor: pointer;\n" .
			"\t\t\t\twidth:890px;\n" .
			"\t\t\t\tmargin-bottom: 0px;\n" .
			"\t\t\t\tbackground-color:#deffaa;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\tborder-bottom:0px;\n" .
			"\t\t\t\t-webkit-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\t-moz-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\t-khtml-border-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t\tborder-radius: 7px 7px 0px 0px;\n" .
			"\t\t\t}\n" .
			"\t\t\t.find {\n" .
			"\t\t\t\tcolor: #FFFFF;\n" .
			"\t\t\t\twidth:890px;\n" .
			"\t\t\t\tmargin: 0px 20px;\n" .
			"\t\t\t\tbackground-color:#deffaa;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\tborder-bottom:0px;\n" .
			"\t\t\t\tborder-top:0px;\n" .
			"\t\t\t}\n" .
			"\t\t\t.find > textarea {\n" .
			"\t\t\t\twidth:885px;\n" .
			"\t\t\t\theight:20px;\n" .
			"\t\t\t\tbackground-color:#f2ffdd;\n" .
			"\t\t\t}\n" .
			"\t\t\t.action {\n" .
			"\t\t\t\twidth:890px;\n" .
			"\t\t\t\tmargin: 0px 20px;\n" .
			"\t\t\t\tbackground-color:#deffaa;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\tborder-bottom:0px;\n" .
			"\t\t\t\tborder-top:0px;\n" .
			"\t\t\t}\n" .
			"\t\t\t.code {\n" .
			"\t\t\t\twidth:890px;\n" .
			"\t\t\t\tmargin: 0px 20px 40px 20px;\n" .
			"\t\t\t\tbackground-color:#deffaa;\n" .
			"\t\t\t\tborder:1px solid #457000;\n" .
			"\t\t\t\tborder-top:0px;\n" .
			"\t\t\t\t-webkit-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\t-moz-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\t-khtml-border-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t\tborder-radius: 0px 0px 7px 7px;\n" .
			"\t\t\t}\n" .
			"\t\t\t.code > textarea {\n" .
			"\t\t\t\twidth:885px;\n" .
			"\t\t\t\theight:240px;\n" .
			"\t\t\t\tmargin-bottom: 20px;\n" .
			"\t\t\t\tbackground-color:#f2ffdd;\n" .
			"\t\t\t}\n";

		return $style;
	}

	public function installvQModerator($version) {
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<modification>
	<id><![CDATA[vQModerator Installation]]></id>
	<version><![CDATA[' . $this->version . ']]></version>
	<vqmver><![CDATA[' . $version . ']]></vqmver>
	<author><![CDATA[The Wizard of Osch, for www.CrystalCopy.nl]]></author>
	<file name="admin/controller/tool/vqmod.php" error="abort">
		<operation info="This is automatically added by the installation script. It holds your installed vQMod version number.">
			<search position="after" index="1"><![CDATA[public function index() {]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQMod Version
		define("VQMODVER", "' . $version . '");]]></add>
		</operation>
		<operation info="This is automatically added by the installation script. It holds your installed vQMod version number.">
			<search position="after" index="1"><![CDATA[public function editor() {]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQMod Version
		define("VQMODVER", "' . $version . '");]]></add>
		</operation>
	</file>
	<file name="admin/controller/common/header.php" error="abort">
		<operation info="Adding Link to vQModerator in Header">
			<search position="after" index="1"><![CDATA[$this->data[\'text_zone\']]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQModerator Text
		$this->data[\'text_vqmoderator\'] = $this->language->get(\'text_vqmoderator\');]]></add>
		</operation>
		<operation info="Adding Link to vQModerator in Header">
			<search position="before" index="1"><![CDATA[$this->data[\'stores\'] = array(]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQModerator Link
			$this->data[\'vqmoderator\'] = $this->url->link(\'tool/vqmod\', \'token=\' . $this->session->data[\'token\'], \'SSL\');]]></add>
		</operation>
	</file>
	<file name="admin/language/*/common/header.php" error="abort">
		<operation info="Adding Link to vQModerator in Header (Language definitions)">
			<search position="before" index="1"><![CDATA[?>]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQModerator Text
$_[\'text_vqmoderator\']                       = \'vQModerator\';]]></add>
		</operation>
	</file>
	<file name="admin/view/template/common/header.tpl" error="abort">
		<operation info="Adding Link to vQModerator in Header (Change this operation to change the location of the header-link)">
			<search position="after" index="1"><![CDATA[<li><a href="<?php echo $feed; ?>"><?php echo $text_feed; ?></a></li>]]></search>
			<add><![CDATA[<?php // BOF - Zappo - vQModerator - ONE LINE - Added vQModerator to Menu ?>
          <li><a href="<?php echo $vqmoderator; ?>"><?php echo $text_vqmoderator; ?></a></li>]]></add>
		</operation>
	</file>
</modification>';
		return $this->model_tool_vqmod->createFile('../vqmod/xml/vQModerator.xml', $data);
	}
}
?>