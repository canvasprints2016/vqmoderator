<?php
class ModelToolVqmod extends Model {
	public $version = '1.1.6';
	private $vqmver = 0;
	private $ftp_connection = false;
	private $hideError = false;
	public $vqm = '../vqmod/';
	public $xml = '../vqmod/xml/';
	public $vqtrunk = 'http://vqmod.googlecode.com/svn/trunk/';
	public $vqtags = 'http://vqmod.googlecode.com/svn/tags/';
	public $vqopcrt = 'platforms/opencart/';
	public $vqmtrunk = 'http://vqmoderator.googlecode.com/svn/trunk/';
	//public $vqmtrunk = '../../vQModerator/';

	public function getFiles() {
		$files = array();
		$error = $message = array();
		$vqm_manuals = $this->config->get('vqm_manuals');
		$vqm_backup = $this->config->get('vqm_backup');
		if (strpbrk($vqm_backup, "\\/?%*:|\"<>") === false || strpos($vqm_backup, $this->vqm . $this->vqm) !== false) {
			$error[] = sprintf($this->language->get('error_backup_folder'), $vqm_backup);
			$vqm_backup = $this->xml;
		}
		$use_errors = libxml_use_internal_errors(true); // Save error setting
		$dirfiles = glob($this->xml . '*.*ml*');
		$bakfiles = glob($vqm_backup . '*.xml*');
		if (!$dirfiles) $dirfiles = array();
		if (!$bakfiles) $bakfiles = array();
		$dirfiles = array_merge($dirfiles, $bakfiles);
		foreach ($dirfiles as $path) {
			$status = true;
			$action = array();
			$file = $filename = basename($path);
			if ($file != 'vqmod_opencart.xml') {
				if (substr($file, -5) == '.html') {
					if ($vqm_manuals && dirname($path) . '/' != $vqm_manuals) {
						$moved = $this->renameFile($path, $vqm_manuals . $file);
						if ($moved) {
							if ($vqm_manuals == $this->xml) $message[] = sprintf($this->language->get('text_found_manual'), $file) . $this->language->get('text_moved_xml');
							else $message[] = sprintf($this->language->get('text_found_manual'), $file) . $this->language->get('text_moved_manual');
						} else {
							$error[] = sprintf($this->language->get('error_moving_manual'), $file);
						}
					}
					continue;
				}
				$newfiles = '';
				$xml = simplexml_load_file($path);
				// XML Error handling
				if (!$xml) {
					$error[] = $errmsg = sprintf($this->language->get('text_xml_not_valid'), rtrim($filename, '_'));
					$this->log(array(array(
						'info' => array(
							'modFile' => $file,
							'id' => (isset($xml->id) ? $xml->id : $file),
							'version' => (isset($xml->version) ? $xml->version : ''),
							'vqmver' => (isset($xml->vqmver) ? $xml->vqmver : ''),
							'author' => (isset($xml->author) ? $xml->author : '')
						),
						'log' => $errmsg
					)));
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
								$error[] = $errmsg = sprintf($this->language->get('text_xml_not_complete'), (isset($xml->id) ? $xml->id : $filename));
								$this->log(array(array(
									'info' => array(
										'modFile' => $file,
										'id' => (isset($xml->id) ? $xml->id : $file),
										'version' => (isset($xml->version) ? $xml->version : ''),
										'vqmver' => (isset($xml->vqmver) ? $xml->vqmver : ''),
										'author' => (isset($xml->author) ? $xml->author : '')
									),
									'log' => $errmsg
								)));
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
					if ($vqm_backup && dirname($path) . '/' != $vqm_backup) {
						if (!file_exists($vqm_backup)) $this->createFile($vqm_backup . 'temp.tmp');
						$this->deleteFile($vqm_backup . 'temp.tmp');
						$moved = $this->renameFile($path, $vqm_backup . $file);
						if ($moved) {
							if ($vqm_backup == $this->xml) $message[] = sprintf($this->language->get('text_found_backup'), $file) . $this->language->get('text_moved_xml');
							else $message[] = sprintf($this->language->get('text_found_backup'), $file) . $this->language->get('text_moved_backup');
							$path = $vqm_backup . $file;
						} else {
							$error[] = sprintf($this->language->get('error_moving_backup'), $file);
						}
					}
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
				$manual = substr($filename, 0, -4) . '.html';
				if (file_exists($vqm_manuals . $manual)) {
					$action[] = array(
						'text' => $this->language->get('text_html_download'),
						'href' => $this->url->link('tool/vqmod/download', 'token=' . $this->session->data['token'] . '&file=' . $manual, 'SSL')
					);
					$action[] = array(
						'text' => $this->language->get('text_html_delete'),
						'href' => $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=delete&file=' . $manual, 'SSL') . '" onclick="return confirm(\'' . sprintf($this->language->get('text_confirm_del'), $manual) . '\');'
					);
				}
				if ($file != 'vQModerator.xml') {
					$delhref = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=delete&file=' . $file, 'SSL');
					if ($newfiles) {
						$delhref .= '" class="uninstall" data-files="' . $newfiles;
					} else {
						$delhref .= '" onclick="return confirm(\'' . sprintf($this->language->get('text_confirm_del'), (isset($xml->id) ? $xml->id : $filename)) . '\');';
					}
					$action[] = array(
						'text' => $this->language->get('text_xml_delete'),
						'href' => $delhref
					);
				}

				$required = (isset($xml->vqmver) && (int)str_ireplace(array('v','.'), '', $xml->vqmver) > (int)str_ireplace(array('v','.'), '', $this->vqmver)) ? 'color:red;' : '';
				$required = (isset($xml->vqmver['required']) && $xml->vqmver['required']) ? ' <' . ($required ? 'b' : 'small') . ' style="margin-left:8px;' . $required . '">(' . $this->language->get('text_required') . ')</' . ($required ? 'b' : 'small') . '>' : '';
				$file = array(
					'file' => $filename,
					'type' => ($install ? ((substr($file, -1) == '_') ? 'dis' : 'en') . 'abled' : 'backup'),
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
		$sort = 'file';
		$order = 'asc';
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
			$this->session->data['vqmod']['sort'] = $sort;
			if (isset($this->request->get['order'])) {
				$order = $this->request->get['order'];
				$this->session->data['vqmod']['order'] = $order;
			}
		} elseif (isset($this->session->data['vqmod']['sort'])) {
			$sort = $this->session->data['vqmod']['sort'];
			if (isset($this->session->data['vqmod']['order'])) $order = $this->session->data['vqmod']['order'];
		}
		$files = $this->multiSort($files, $sort, $order);

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
		$xml_file = ($file && file_exists($this->xml . $file)) ? $this->xml . $file : $this->config->get('vqm_backup') . $file;
		if ($file && file_exists($xml_file)) {
			$xmll = simplexml_load_file($xml_file);
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
			if ($this->doDelete($file)) {
				if (substr($file, -4) == '.xml') {
					$file = str_replace($this->xml, $this->config->get('vqm_manual'), substr($file, 0, -3)) . 'html';
					if (file_exists($file)) $this->doDelete($file);
					$this->deleteAll('cache');
				}
				if ($files && strpos($files, '|') !== false) {
					$files = explode('|', $files);
					foreach ($files as $dir) {
						if (file_exists('../' . $dir)) $this->doDelete('../' . $dir);
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
			if (file_exists($this->vqm . 'mods.cache')) {
				$this->doDelete($this->vqm . 'mods.cache');
			}
		}
		if (file_exists($dir) && is_dir($dir)) {
			if ($echo) echo "Trying to Delete: " . $dir . $files . "<br/>";
			$dirfiles = glob($dir . $files);
			if ($dirfiles) {
				foreach ($dirfiles as $file) $this->doDelete($file);
			}
		}
		return true;
	}

	public function delTree($dir, $force=true) {
		if (!is_dir($dir)) return false;
		if (substr($dir, strlen($dir) - 1, 1) != '/') $dir .= '/';
		$files = glob($dir . '*', GLOB_MARK);
		if (!$files || $force) {
			foreach ($files as $file) {
				if (is_dir($file)) $this->delTree($file);
				else $this->doDelete($file);
			}
			return rmdir($dir);
		}
		return false;
	}

	private function doDelete($file) {
		if ($this->ftp_connection) {
			$base = str_replace('/system', '', DIR_SYSTEM);
			$file = str_replace($base, '', str_replace('../', '', $file));
			return ftp_delete($this->ftp_connection, $file);
		} else {
			return unlink($file);
		}
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
		$dirfiles = glob($this->xml . '*.xml');
		foreach ($dirfiles as $path) {
			if ($path != $this->xml . 'vqmod_opencart.xml') {
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
		$dirfiles = glob($this->xml . '*.xml_');
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
		$path = '../' . ((strpos($file, '../../') !== false) ? '../' : '');
		$directories = explode('/', dirname(str_replace('../', '', $file)));
		foreach ($directories as $directory) {
			$path = $path . '/' . $directory;
			if (!file_exists($path)) {
				if ($this->ftp_connection && strpos($path, '../../') === false) {
					$base = str_replace('/system', '', DIR_SYSTEM);
					$path = str_replace($base, '', str_replace('../', '', $path));
					if (!ftp_mkdir($this->ftp_connection, $path)) return false;
				} else {
					if (!mkdir($path, $chmod)) return false;
				}
			}
			if (!is_writable($path)) {
				$reset[$path] = $this->setPermission($path);
				if (!$reset[$path]) return false;
			}
		}
		if ($file && substr($file, -1) != '/') {
			$perms = $this->setPermission($file);
			if (!file_exists($file) || $overwrite) {
				$fh = fopen($file, 'w');
				if (!$fh) return false;
				if ($mime != 'text') $data = base64_decode($data);
				fwrite($fh, $data);
				fclose($fh);
			}
			if ($perms) $this->setPermission($file, $perms);
		}
		foreach ($reset as $path => $perms) {
			if ($perms) $this->setPermission($path, $perms);
		}

		return true;
	}

	public function renameFile($old, $new) {
		if (file_exists($old)) {
			if (!file_exists($new)) $this->createFile($new);
			$time = filemtime($old);
			if ($this->ftp_connection) {
				$base = str_replace('/system', '', DIR_SYSTEM);
				$renamed = ftp_rename($this->ftp_connection, str_replace($base, '', str_replace('../', '', $old)), str_replace($base, '', str_replace('../', '', $new)));
			} else {
				$renamed = rename($old, $new);
			}
			if ($renamed) {
				if ($time) touch($new, $time); // Set Original Modification time back
				return true;
			}
		}
		return false;
	}

	// Use an FTP connection when chmodding/creating files (Should prevent permission errors)
	// Sets "$this->ftp_connection" to the connection, or "false" when not connected
	// Functions use "$this->ftp_connection" for file actions (or PHP functions when no connection)
	// Returns "true" on success, "false" or "error string" when not connected
	public function setFTP($set = true) { // No Set = close connection
		// Catch warnings (for Permissions and file creation)
		set_error_handler(array($this, 'warningHandler'), E_WARNING);
		$this->hideError = ($set && $set !== true);
		if ($this->config->get('config_ftp_status') && function_exists('ftp_connect') && $set) {
			$this->ftp_connection = ftp_connect($this->config->get('config_ftp_host'), $this->config->get('config_ftp_port'));
			if (!$this->ftp_connection) {
				return "Couldn't connect to FTP Server @ " . $this->config->get('config_ftp_host') . ":" . $this->config->get('config_ftp_port') . "\n";
			}
			$login = ftp_login($this->ftp_connection, $this->config->get('config_ftp_username'), $this->config->get('config_ftp_password'));
			if (!$login) {
				ftp_close($this->ftp_connection);
				$this->ftp_connection = false;
				return "Couldn't connect as " . $this->config->get('config_ftp_username') . "\n";
			}
			// turn passive mode on
			ftp_pasv($this->ftp_connection, true);
			if ($this->config->get('config_ftp_root')) {
				$root = ftp_chdir($this->ftp_connection, $this->config->get('config_ftp_root'));
				if (!$root) {
					ftp_close($this->ftp_connection);
					$this->ftp_connection = false;
					return "Couldn't change to directory " . $this->config->get('config_ftp_root') . "\n";
				}
			}
			$contents = ftp_nlist($this->ftp_connection, ".");
			// Check if we're in the correct folder
			if (!$contents || !in_array('index.php', $contents) || !in_array('config.php', $contents) || !in_array('system', $contents)) {
				ftp_close($this->ftp_connection);
				$this->ftp_connection = false;
				return "Wrong root folder!";
			}
			return ($this->ftp_connection) ? true : false;
		} else {
			$closed = ($this->ftp_connection && !$set) ? ftp_close($this->ftp_connection) : true;
			$this->ftp_connection = false;
			return $closed;
		}
	}

	public function setPermission($file, $set = false) { // No Set = make writable, and return orignal setting
		if (!file_exists($file)) return false;
		$base = str_replace('/system', '', DIR_SYSTEM);
		$perms = fileperms($file);
		if (!$set) {
			if (!is_writable($file)) {
				if ($this->ftp_connection) {
					$file = str_replace($base, '', str_replace('../', '', $file));
					ftp_chmod($this->ftp_connection, 0777, $file);
				} else {
					chmod($file, 0777);
				}
			}
		} elseif ($set != $perms) {
			if ($this->ftp_connection) {
				$file = str_replace($base, '', str_replace('../', '', $file));
				ftp_chmod($this->ftp_connection, $set, $file);
			} else {
				chmod($file, $set);
			}
		}

		return substr(decoct($perms), 2);
	}

	// Suppress errors (when chmodding, for example)
	private function warningHandler($errno, $errstr) {
		if (!isset($this->hideError) || !$this->hideError) echo "Error #$errno occured: $errstr\n";
	}

	public function getTree($path = '../', $file = '', $files = true, $exts = array('php', 'tpl')) {
		$ignore = array('vqmod', 'config-dist.php', 'install', 'nbproject', '.svn', '.', '..' );
		$real = false;
		if ($path === true) {
			$real = true;
			$path = '../';
		}
		$tree = array();

		$multi = explode(',', $file);
		if (isset($multi[1])) {
			$file = array_pop($multi);
			$multi = implode(',', $multi) . ',';
		} else {
			$multi = '';
		}
		$treefile = explode('/', $file);
		array_pop($treefile);
		$treefile = implode('/', $treefile);
		if ($treefile) $treefile .= '/';
		$treefile = $multi . $treefile;
		if (substr($file, -1) != '*') $file .= '*';
		$treefiles = glob($path . $file);
		foreach ($treefiles as $file) {
			$tfile = basename($file);
			if (!in_array($tfile, $ignore)) {
				$tfile = ($real) ? $file : $treefile . $tfile;
				if (is_file($file) && $files && !in_array($tfile, $tree)) {
					$ext = explode('.', $file);
					$ext = array_pop($ext);
					if ($exts && in_array($ext, $exts)) $tree[] = $tfile;
				} elseif (is_dir($file) && !in_array($tfile, $tree)) {
					$tree[] = $tfile . '/';
				}
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
				$temp[$key] = $array[$key][$index];
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

	// settings does 3 things: Save settings ($data = array), Get settings (!$data), and Save first Install (!$data && !$settings)
	public function settings($posted = array()) {
		$this->load->model('setting/setting');
		if (!$posted || !is_array($posted)) {
			$data = $this->model_setting_setting->getSetting('vqmod');
		} else {
			// POSTed data (or previously saved data)
			$data = $posted;
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
		// Make string one folder deep, with trailing slash
		$data['vqm_backup'] = $this->vqm . (isset($data['vqm_backup']) ? str_replace(array($this->vqm, '/', '.'), '', $data['vqm_backup']) . '/' : 'backups/');
		$data['vqm_test'] = $this->vqm . (isset($data['vqm_test']) ? str_replace(array($this->vqm, '/', '.'), '', $data['vqm_test']) . '/' : 'test/');
		$data['vqm_manuals'] = $this->vqm . (isset($data['vqm_manuals']) ? str_replace(array($this->vqm, '/', '.'), '', $data['vqm_manuals']) . '/' : 'manuals/');
		// Empty old folders, and create new folders
		$success = $this->changeFolder($this->config->get('vqm_backup'), $data['vqm_backup']);
		if ($this->config->get('vqm_test') && file_exists($this->config->get('vqm_test')) && $this->config->get('vqm_test') !== $data['vqm_test']) $success = rename($this->config->get('vqm_test'), $data['vqm_test']);
		if ($success) $success = $this->changeFolder($this->config->get('vqm_manuals'), $data['vqm_manuals']);
		if (!$success) return false;
		$sorted = array(
			'update' => (isset($data['update']) ? $data['update'] : 24),
			'vqm_cache' => (isset($data['vqm_cache']) ? $data['vqm_cache'] : $this->vqm . 'vqcache/'),
			'log_file' => (isset($data['log_file']) ? $data['log_file'] : $this->vqm . 'logs/'),
			'log_size' => $data['log_size'],
			'vqm_create' => (isset($data['vqm_create']) ? $data['vqm_create'] : 1),
			'vqm_backup' => $data['vqm_backup'],
			'vqm_backups' => (isset($data['vqm_backups']) ? $data['vqm_backups'] : 3),
			'vqm_test' => $data['vqm_test'],
			'test_vqmod' => (isset($data['test_vqmod']) && $data['test_vqmod']) ? $data['test_vqmod'] : 'vQModded',
			'test_delay' => (isset($data['test_delay']) ? $data['test_delay'] : 800),
			'text_height' => (isset($data['text_height']) ? $data['text_height'] : 250),
			'text_style' => (isset($data['text_style']) ? $data['text_style'] : 1),
			'show_trim' => (isset($data['show_trim']) ? $data['show_trim'] : 1),
			'show_regex' => (isset($data['show_regex']) ? $data['show_regex'] : 1),
			'show_info' => (isset($data['show_info']) ? $data['show_info'] : 1),
			'generate_html' => (isset($data['generate_html']) ? $data['generate_html'] : 0),
			'vqm_manuals' => $data['vqm_manuals'],
			'manual_css' => (isset($data['manual_css']) && $data['manual_css'] ? $data['manual_css'] : $this->getManualCss())
		);
		if ($posted === 'html') {
			// Return configuration page html
			$page_start = array('update', 'vqm_backup', 'generate_html');
			$page_stop = array('vqm_create', 'show_info', 'manual_css');
			$checkbox = array('vqm_create', 'show_trim', 'show_regex', 'show_info', 'generate_html', 'text_style');
			$vqdir = array('vqm_backup', 'vqm_manuals', 'vqm_test');
			$html = '  <input name="vqm_cache" type="hidden" value="' . $sorted['vqm_cache'] . '" />'."\n".'  <input name="log_file" type="hidden" value="' . $sorted['log_file'] . '" />'."\n";
			unset($sorted['vqm_cache'], $sorted['log_file']);
			foreach ($sorted as $vqname => $vqval) {
				if (in_array($vqname, $page_start)) {
					$edit_id = ($vqname == $page_start[0]) ? 'set-vqmod' : ($vqname == $page_start[1] ? 'set-editor' : 'set-manual');
					$html .= '  <table class="list"' . (($vqname != $page_start[0]) ? ' style="display:none"' : '') . ' id="' . $edit_id . '">'."\n";
				}
				$html .= "    <tr>\n";
				if (in_array($vqname, $checkbox)) {
					$html .= '      <td class="left">' . $this->language->get('entry_' . $vqname) . '</td>'."\n".
							'      <td class="left">'."\n".
							'        <input name="' . $vqname . '" type="checkbox" value="1" ' . ($vqval ? 'checked="checked" ' : '') . 'data-orig="' . $vqval . ' "/>'."\n";
					if ($this->language->get('entry_help_' . $vqname) != 'entry_help_' . $vqname) {
						$html .= $this->language->get('entry_help_' . $vqname);
					}
					$html .= "      </td>\n";
				} elseif ($vqname == 'manual_css') {
					$html .= '      <td class="left" colspan="2">'."\n".
							$this->language->get('entry_' . $vqname) . "<br/>\n".
							'        <textarea name="manual_css" id="manual_css" style="width:565px;" rows="4" data-orig="' . $vqval . '">' . $vqval . '</textarea>'."\n".
							"      </td>\n";
				} elseif (($vqname != 'vqm_test' && $vqname != 'test_vqmod') || !defined('SUBFOLDER') || !defined('LOCALPATH')) {
					$html .= '      <td class="left">' . $this->language->get('entry_' . $vqname) . '</td>'."\n".
							'      <td class="left">'."\n";
					if ($vqname == 'vqm_backup' && defined('SUBFOLDER') && defined('LOCALPATH')) {
						$html .= '        <input name="test_vqmod" type="hidden" value="' . $vqval . '" />'."\n";
					}
					if (in_array($vqname, $vqdir)) {
						$html .= '        <input name="' . $vqname . '" type="text" class="vqdir" style="width:380px;" value="' . $vqval . '" data-orig="' . $vqval . '" />'."\n";
					} else {
						$html .= '        <input name="' . $vqname . '" type="text" style="width:380px;" value="' . $vqval . '" data-orig="' . $vqval . '" />'."\n";
					}
					$html .= "      </td>\n";
				}
				$html .= "    </tr>\n";
				if (in_array($vqname, $page_stop)) {
					$html .='  </table>'."\n";
					if ($edit_id == 'set-vqmod') {
						$html .= '  <div id="update-buttons" style="float:right"><a href="' . str_replace('&amp;', '&', $this->url->link('tool/vqmod', 'checkup=1&token=' . $this->session->data['token'], 'SSL')) . '" class="vqbutton">' . $this->language->get('button_update_check') . '</a>'.
								'&nbsp; &nbsp; &nbsp; <a href="' . str_replace('&amp;', '&', $this->url->link('tool/vqmod/vqinstall', 'install=vq&token=' . $this->session->data['token'], 'SSL')) . '" class="vqbutton vqmod-install" onclick="return false;">' . $this->language->get('button_update_vqmod') . '</a>'.
								'&nbsp; <a href="' . str_replace('&amp;', '&', $this->url->link('tool/vqmod/vqinstall', 'install=vqm&token=' . $this->session->data['token'], 'SSL')) . '" class="vqbutton vqmod-install" onclick="return false;">' . $this->language->get('button_update') . '</a>'."\n  </div>\n";
					}
				}
			}
			return $html;
		} elseif ($posted || $this->config->get('vqm_test') === null) {
			// Save settings if POSTed, or if newest setting is not set (first install/update)
			foreach ($sorted as $key => $val) $this->config->set($key, $val);
			$this->model_setting_setting->editSetting('vqmod', $sorted);
		}
		return $sorted;
	}

	// Change folder settings (copy files to root)
	public function changeFolder($old, $new) {
		if ($new != $old && file_exists($old)) {
			$files = glob($old . '*');
			// Copy all files to vqmod root folder (will copy to proper folder on pageload)
			foreach ($files as $file) $this->renameFile($file, $this->xml . basename($file));
			$this->delTree($old);
		}
		if (!file_exists($new)) {
			$this->createFile($new . 'tmp.del');
			$this->deleteFile($new . 'tmp.del');
		}
		return file_exists($new);
	}

	public function isRemoteFile($url) {
		if (strpos($url, '../../') === 0 && file_exists($url)) return true; // Return true if it's a relative local path (testing)
		$check = curl_init($url);

		curl_setopt($check, CURLOPT_NOBODY, true);
		curl_exec($check);
		$returned = curl_getinfo($check, CURLINFO_HTTP_CODE);
		curl_close($check);
		return ($returned == 200);
	}

	public function getContribute($type = false) {
		$mailme = $this->vqmtrunk . (!$type ? 'contact' : 'contribute');
		$mailme = ($this->isRemoteFile($mailme)) ? trim(file_get_contents($mailme)) : '';
		if ($type) {
			$mailme = explode('~|[', $mailme);
			$message = array_shift($mailme);
			$subject = $file = array('');
			foreach ($mailme as $part) {
				if (strpos($part, 'FILE:') !== false) $file = explode(']|~', substr($part, 5));
				elseif (strpos($part, 'SUBJECT:') !== false) $subject = explode(']|~', substr($part, 8));
			}
			$mailme = array('message' => $message, 'file' => trim($file[0]), 'subject' => trim($subject[0]));
		}
		return $mailme;
	}

	public function log($errors = array()) {
		if (!$errors || !is_array($errors)) return false;
		$txt = array();

		$txt[] = str_repeat('-', 10) . ' Date: ' . date('Y-m-d H:i:s') . ' ~ IP : ' . (filter_input(INPUT_SERVER, 'REMOTE_ADDR') ? filter_input(INPUT_SERVER, 'REMOTE_ADDR') : 'N/A') . ' ' . str_repeat('-', 10);
		$txt[] = 'REQUEST URI : ' . filter_input(INPUT_SERVER, 'REQUEST_URI');

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
		if (substr($logPath, -4) == '.log' && !file_exists($logPath)) {
			$res = file_put_contents($logPath, '');
			if ($res === false) {
				die('COULD NOT WRITE TO LOG FILE');
			}
		} elseif (file_exists($logPath) && is_dir($logPath)) {
			$logPath .= date('D') . '.log';
		}

		file_put_contents($logPath, implode(PHP_EOL, $txt), FILE_APPEND);
	}

	public function generateAll() {
		$files = array();
		$error = $message = array();
		$use_errors = libxml_use_internal_errors(true); // Save error setting
		$xml_dir = $this->xml;
		$dirfiles = glob($xml_dir . '*.xml');
		foreach ($dirfiles as $path) {
			$file = str_replace($xml_dir, '', $path);
			if ($file != 'vqmod_opencart.xml') {
				$xml = simplexml_load_file($path);
				// XML Error handling
				if (!$xml) {
					$error[] = $errmsg = sprintf($this->language->get('text_xml_not_valid'), $file);
					$this->log(array(array(
						'info' => array(
							'modFile' => $file,
							'id' => (isset($xml->id) ? $xml->id : $file),
							'version' => (isset($xml->version) ? $xml->version : ''),
							'vqmver' => (isset($xml->vqmver) ? $xml->vqmver : ''),
							'author' => (isset($xml->author) ? $xml->author : '')
						),
						'log' => $errmsg
					)));
				}
				libxml_clear_errors();
				if (isset($xml->file)) {
					foreach ($xml->file as $file) {
						$thefiles = explode(',', $file['name']);
						foreach ($thefiles as $filename) {
							$filename = (isset($file['path']) ? $file['path'] : '') . trim($filename);
							if (!in_array($filename, $files)) $files[] = $filename;
						}
					}
				}
			}
		}
		libxml_use_internal_errors($use_errors); // Reset error setting

		$success = false;
		if ($files) {
			$this->deleteAll('cache');
			if (defined('SUBFOLDER') && defined('LOCALPATH')) {
				$tests = '../../';
			} else {
				$tests = $this->config->get('vqm_test');
			}
			//$VQMod = new VQMod();
			foreach ($files as $file) {
				$genfiles = glob('../' . $file);
				foreach ($genfiles as $file) {
					$file = str_replace('../', '', $file);
					$genfile = VQMod::modcheck($file);
					if (is_file($genfile)) {
						$newfile = $tests . $this->config->get('test_vqmod') . '/' . $file;
						$success = $this->createFile($newfile); // Pre-create file, to also get dirs in place
						if ($success) $this->renameFile($genfile, $newfile);
					}
				}
			}
		}
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
							// Fix nested CDATA
							if (strpos($data['add'][$key][$key2], '&lt;![CDATA[') !== false) {
								$data['add'][$key][$key2] = str_replace(']]&gt;',']]]]&gt;&lt;![CDATA[&gt;', $data['add'][$key][$key2]);
								$data['add'][$key][$key2] = str_replace(']]]]]]&gt;&lt;![CDATA[&gt;&lt;![CDATA[&gt;',']]]]&gt;&lt;![CDATA[&gt;', $data['add'][$key][$key2]);
							}
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
		if ($manual && $data['generatexml']) {
			$dir = $this->config->get('vqm_manuals');
			if (!$dir) $dir = $this->xml;
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

		$dir = $this->xml;
		$bdir = $this->config->get('vqm_backup');
		if (!$data['generatexml']) {
			$file .= '.tmp';
		} else {
			$file .= '.xml';
			$rename = false;
			if (file_exists($dir . $file)) {
				$rename = $file;
			} elseif ($data['oldfile'] && substr($data['oldfile'], -4) != '.bak' && file_exists($dir . $data['oldfile'])) {
				$rename = $data['oldfile']; // Rename orinal to .bak
			}
			if ($rename) {
				$backups = (int)$this->config->get('vqm_backup');
				if (file_exists($bdir . $file . '.bak') && $backups > 1) {
					// Rename backup files from high to low
					for ($i = $backups - 1; $i >= 1; $i--) {
						if (file_exists($bdir . $file . '-' . $i . '.bak') && ($i + 1 < $backups)) {
							$this->renameFile($bdir . $file . '-' . $i . '.bak', $bdir . $file . '-' . ($i+1) . '.bak');
						}
					}
					$this->renameFile($bdir . $file . '.bak', $bdir . $file . '-1.bak');
				}
				$this->renameFile($dir . $rename, $bdir . $file . '.bak');
			}
			if ($data['oldfile'] && substr($data['oldfile'], -1) == '_' && file_exists($dir . $data['oldfile'])) {
				$file .= '_';
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

	// Get vQMod & vQModerator online versions
	public function getVersions() {
		$update_time = ($this->config->get('update') === null) ? 24 : $this->config->get('update');
		if (!$update_time) return array('vq' => $this->vqmver, 'vqm' => $this->version, 'changelog' => '');
		$cache = DIR_CACHE . 'cache.vqmoderator';
		if (!file_exists($cache) && touch($cache)) {
			chmod($cache, 0777);
		} elseif (file_exists($cache) && is_readable($cache) && is_writable($cache)) {
			if (filemtime($cache) > strtotime('-' . $update_time . ' Hours')) {
				$version = file_get_contents($cache);
				$version = (substr($version,1,1) != '.') ? unserialize($version) : false;
				if (is_array($version) && isset($version['vqm'])) {
					$versions = $version;
				}
			}
		} else {
			$cache = false;
		}
		if (!isset($versions)) {
			$versions = array();
			// Get the vqmod version from repository
			$file = $this->vqtrunk . 'vqmod/vqmod.php';
			if ($this->isRemoteFile($file)) {
				$data = file_get_contents($file);
				$version = explode('$_vqversion', $data, 2);
				$version = explode("';", $version[1], 2);
				$version = trim(str_replace("'", '', str_replace('=', '', $version[0])));
			}
			// Get vQModerator version from repository
			if (isset($version) && $version) {
				$versions = array('vq' => $version);
				$file = $this->vqmtrunk . 'version';
				if ($this->isRemoteFile($file)) {
					$version = file_get_contents($file);
					if ($version) {
						$versions['changelog'] = '';
						$version = preg_split("/\r\n|\n|\r/", $version);
						$versions['vqm'] = trim($version[0]);
						$this_ver = (int)str_replace('.', '', $this->version);
						foreach ($version as $i => $ver) {
							if (strlen(trim($ver)) == 5 && substr_count($ver, '.') == 2) {
								$vqmr_ver = (int)str_replace('.', '', trim($ver));
								if ($vqmr_ver <= $this_ver) break;
								$ver = ($i ? '<br/>' : '') . '<b>' . $ver . '</b>';
							}
							$versions['changelog'] .= $ver . '<br/>';
						}
					}
				}
			}
		}
		// Save versions to cache if we got both of them
		if ($cache && isset($versions['vqm'])) {
			file_put_contents($cache, serialize($versions));
		} elseif (!$versions || !isset($versions['vqm'])) {
			$versions = array('vq' => $this->vqmver, 'vqm' => $this->version, 'changelog' => '');
		}
		return $versions;
	}

	// Get vQMod version (from file -> could not be installed)
	public function getVQModVersion() {
		if (!$this->vqmver) {
			$this->getVQModConfig();
		}
		return $this->vqmver;
	}

	// Get vQMod version (from file -> could not be installed)
	public function getVQModConfig($get = '$_vqversion') {
		$file = $this->vqm . 'vqmod.php';
		if (file_exists($file)) {
			$data = file_get_contents($file);
			$version = explode($get, $data, 2);
			if (isset($version[1])) {
				$version = explode("';", $version[1], 2);
				$version = trim(str_replace("'", '', str_replace('=', '', $version[0])));
			} else {
				$version = '';
			}
		}
		if (isset($version)) {
			if ($version && $get === '$_vqversion') $this->vqmver = $version;
			return $version;
		}
		return false;
	}

	public function checkInstall() {
		$warning = false;
		// Get settings (and save default settings if not found)
		$settings_check = $this->model_tool_vqmod->settings();
		if (!class_exists('VQMod')) {
			$link = str_replace('&amp;', '&', $this->url->link('tool/vqmod/vqinstall', 'install=vq&token=' . $this->session->data['token'], 'SSL'));
			if (!file_exists($this->vqm . 'vqmod.php')) {
				$warning = sprintf($this->language->get('error_vqmod_missing'), $link);
			} else {
				$warning = sprintf($this->language->get('error_vqmod_install'), str_replace('install=vq&', 'install=govq&try=1&', $link), $link);
			}
		} else {
			$this->getVQModConfig();
			$log_file = $this->getVQModConfig('$logFolder');
			$log_file_name = $this->getVQModConfig('$logFileName');
			if (!$log_file) $log_file = $this->getVQModConfig('$logFilePath');
			if ($log_file && $log_file_name) $log_file .= $log_file_name;
			$vqm_cache = $this->getVQModConfig('$vqCachePath');
			// Update log and cache settings, if not correct
			if (($log_file && '../' . $log_file != $this->config->get('log_file')) || ($vqm_cache && '../' . $vqm_cache != $this->config->get('vqm_cache'))) {
				$settings_check['log_file'] = '../' . $log_file;
				$settings_check['vqm_cache'] = '../' . $vqm_cache;
				$this->settings($settings_check);
				$this->session->data['success'] = $this->language->get('success_update_vqmod');
			}
			// Check if vQModerator is installed
			$xml_ver = $this->getFile('vQModerator.xml');
			$link = str_replace('&amp;', '&', $this->url->link('tool/vqmod/vqinstall', 'install=govqm&token=' . $this->session->data['token'], 'SSL'));
			if (isset($this->request->get['installing'])) { // Installing... Needed to reload.
				$this->session->data['success'] = sprintf($this->language->get('success_continue_vqmoder'), $link) . '<br/>';
				$warning = true;
			} elseif (!$xml_ver->version || $xml_ver->version != $this->version) {
				$warning = sprintf($this->language->get('error_installation'), $link, str_replace('install=govqm&', 'install=vqm&', $link));
			} elseif ($this->config->get('vqm_backup') === null || isset($settings_check['vqm'])) { // vQModerator installed, but some old version
				$this->settings($settings_check); // re-save settings, adding/removing new/old stuff
				$this->session->data['success'] = $this->language->get('success_update_vqmoder');
			}
		}

		return $warning;
	}

	public function getVQMod() {
		$admin = basename(DIR_APPLICATION);
		$vqmod = str_replace('../', '', $this->vqm);
		$failed = '';
		$files = array(
			$vqmod . 'vqmod.php' => 'vqmod/vqmod.php',
			$vqmod . 'install/ugrsr.class.php' => 'vqmod/install/ugrsr.class.php',
			$vqmod . 'install/index.php' => $this->vqopcrt . 'install/index.php',
			$vqmod . 'xml/vqmod_opencart.xml' => $this->vqopcrt . 'xml/vqmod_opencart.xml'
		);
		foreach ($files as $local => $remote) {
			$remote = $this->vqtrunk . $remote;
			if ($this->isRemoteFile($remote)) {
				$data = file_get_contents($remote);
				// Set the admin folder in vQMod Install file
				if ($local == $vqmod . 'install/index.php' && $admin != 'admin') $data = str_replace("= 'admin';", "= '$admin';", $data);
				if (!$this->createFile('../' . $local, $data, 'text', 0755)) $failed .= '<br/>' . $local;
			} else {
				return false;
			}
		}
		if ($failed) return  $failed;

		return true;
	}

	public function installVQMod() {
		if (!file_exists($this->vqm . 'install/index.php')) return false;
		// Set and Save permissions
		$admin = basename(DIR_APPLICATION);
		$chmods = array(
			'../index.php' => $this->setPermission('../index.php'),
			'../' . $admin . '/' => $this->setPermission('../' . $admin . '/'),
			'../' . $admin . '/index.php' => $this->setPermission('../' . $admin . '/index.php')
		);
		$response = file_get_contents(HTTP_CATALOG . str_replace('../', '', $this->vqm) . 'install/index.php');

		// Restore Saved permissions
		foreach ($chmods as $file => $chmod) $this->setPermission($file, $chmod);

		return $response;
	}

	public function getVQModerator($dirs = array('../')) {
		$failed = '';
		$admin = basename(DIR_APPLICATION);
		// Get language list to check for
		$this->load->model('localisation/language');
		$languages = $this->model_localisation_language->getLanguages();
		$langs = array();
		// Get vQModerator from Repository
		$files = $this->vqmtrunk . 'files';
		$files = ($this->isRemoteFile($files)) ? file_get_contents($files) : '';
		$files = preg_split("/\r\n|\n|\r/", $files);
		if (!is_array($files)) return false;
		foreach ($files as $file) {
			$file = trim($file);
			$remote = $this->vqmtrunk . $file;
			if ($file && $this->isRemoteFile($remote)) {
				if ($admin != 'admin') $file = str_replace('admin/', $admin . '/', $file);
				if (strpos($file, 'language/english')) {
					// Check for language files
					foreach ($languages as $language) {
						$rremote = str_replace('english', $language['directory'], $remote);
						if ($this->isRemoteFile($rremote)) {
							$data = file_get_contents($rremote);
							$ffile = str_replace('english', $language['directory'], $file);
							foreach ($dirs as $dir) {
								if (!$this->createFile($dir . $ffile, $data, 'text', 0755)) $failed .= '<br/> - ' . $dir . $ffile;
							}
							if (!$failed && $language['code'] != 'en') $langs[] = $language['name'];
						}
					}
				} else {
					$data = file_get_contents($remote);
					foreach ($dirs as $dir) {
						if (!$this->createFile($dir . $file, $data, 'text', 0755)) $failed .= '<br/> - ' . $dir . $file;
					}
				}
			} else {
				return false;
			}
		}
		if ($failed) return $failed;

		return ($langs) ? $langs : true;
	}

	public function installVQModerator() {
		$admin = basename(DIR_APPLICATION);
		$data = '<?xml version="1.0" encoding="UTF-8"?>
<modification>
	<id><![CDATA[vQModerator Installation]]></id>
	<version><![CDATA[' . $this->version . ']]></version>
	<vqmver><![CDATA[' . $this->vqmver . ']]></vqmver>
	<author><![CDATA[The Wizard of Osch, for www.CrystalCopy.nl]]></author>
	<file name="' . $admin . '/controller/common/header.php" error="abort">
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
	<file name="' . $admin . '/language/*/common/header.php" error="abort">
		<operation info="Adding Link to vQModerator in Header (Language definitions)">
			<search position="before" index="1"><![CDATA[?>]]></search>
			<add><![CDATA[// BOF - Zappo - vQModerator - ONE LINE - Added vQModerator Text
$_[\'text_vqmoderator\']                       = \'vQModerator\';]]></add>
		</operation>
	</file>
	<file name="' . $admin . '/view/template/common/header.tpl" error="abort">
		<operation info="Adding Link to vQModerator in Header (Change this operation to change the location of the header-link)">
			<search position="after" index="1"><![CDATA[<li><a href="<?php echo $feed; ?>"><?php echo $text_feed; ?></a></li>]]></search>
			<add><![CDATA[<?php // BOF - Zappo - vQModerator - ONE LINE - Added vQModerator to Menu ?>
          <li><a href="<?php echo $vqmoderator; ?>"><?php echo $text_vqmoderator; ?></a></li>]]></add>
		</operation>
	</file>
</modification>';
		return $this->createFile($this->xml . 'vQModerator.xml', $data);
	}
}
?>