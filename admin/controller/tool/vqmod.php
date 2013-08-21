<?php 
class ControllerToolVqmod extends Controller {
	private $error = array();
	
	public function index() {
		$this->load->language('tool/vqmod');
		$this->load->model('tool/vqmod');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->addStyle('view/stylesheet/vqmod.css');

		// Check if vQModerator is installed
		if ($this->config->get('show_trim') === null) { // Check for oldest setting (covering all versions)
			$this->model_tool_vqmod->settings(); // Save initial (default) settings
			$this->error['warning'] = $this->language->get('error_settings');
		}
		// Check if vQModerator Settings need updating
		$settings_check = $this->model_tool_vqmod->settings('check');
		$vqm_version = $settings_check['versions'][0];
		$vqm_ver = (int)str_replace('.', '', $vqm_version);
		// Save ChangeLog and Version for later use.
		$changelog = explode("\n", $settings_check['versions'][1]);
		$vqmr_version = array_shift($changelog);
		$this->data['changelog'] = implode('', $changelog);
		$vqmr_ver = (int)str_replace('.', '', $vqmr_version);
		unset($settings_check['versions']);
		if (!defined('VQMODVER') || $this->config->get('vqm_backup') === null) { // Check for newest setting
			$this->model_tool_vqmod->settings($settings_check); // re-save settings, adding new stuff
			$this->error['warning'] = $this->language->get('error_settings');
			if (!defined('VQMODVER')) {
				define('VQMODVER', '0');
				$this->error['warning'] = $this->language->get('error_installation');
			}
		}
		$this->model_tool_vqmod->deleteAll($this->config->get('vqm_xml'), '*.tmp');
		if (!isset($this->session->data['x_able'])) $this->session->data['x_able'] = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->validate()) {
				$this->error['warning'] = $this->language->get('error_permission');
			} else {
				if (isset($this->request->files['vqmod_xml']) && !empty($this->request->files['vqmod_xml']['name'])) {
					$file = $this->request->files['vqmod_xml']['tmp_name'];
					$filename = html_entity_decode($this->request->files['vqmod_xml']['name'], ENT_QUOTES, 'UTF-8');
					if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
						$this->error['warning'] = $this->language->get('error_filename');
					}
					if ($this->request->files['vqmod_xml']['error'] != UPLOAD_ERR_OK) {
						$this->error['warning'] = $this->language->get('error_upload_' . $this->request->files['vqmod_xml']['error']);
					}
				} else {
					$this->error['warning'] = $this->language->get('error_upload');
				}
				if (!isset($this->error['warning']) && is_uploaded_file($file) && file_exists($file)) {
					if ($this->request->files['vqmod_xml']['type'] != 'text/xml') {
						$this->error['warning'] = $this->language->get('error_no_xml');
					} else {
						libxml_use_internal_errors(true);
						$xml = simplexml_load_file($file);

						if (libxml_get_errors()) {
							libxml_clear_errors();
							$this->error['warning'] = $this->language->get('error_invalid_xml');
						}
					}
					if (!isset($this->error['warning'])) {
						$enable = true;
						$path = $this->config->get('vqm_xml') . $filename;
						if (!file_exists($path) && file_exists($path . '_')) {
							$path .= '_';
							$enable = false;
						}
						$msg = '';
						if (file_exists($path)) {
							$msg .= ($msg ? '<br/>' : '') . sprintf($this->language->get('text_upload_exists'), $filename);
							if ($this->model_tool_vqmod->renameFile($path, $this->config->get('vqm_xml') . $filename . '.bak')) {
								$msg .= '<br/>' . sprintf($this->language->get('text_upload_backup'), $filename);
							}
						}
						if ($enable && substr($path,-4) == '.xml') $path .= '_';
						if (!move_uploaded_file($file, $path)) {
							$this->error['warning'] = $this->language->get('error_move');
						} else {
							$this->session->data['success'] = $msg . '<br/>' . sprintf($this->language->get('text_upload_success'), $xml->id);
							if ($enable && substr($path, -5) == '.xml_') {
								$this->request->get['action'] = 'enable';
								$this->request->get['file'] = $filename . '_';
								$this->session->data['success'] .= $this->language->get('text_upload_update');
							}
							$this->model_tool_vqmod->deleteAll('cache');
						}
					}
				}
			}
		}

		$success = false;
		if (isset($this->request->get['action'])) {
			if (!$this->validate()) {
				$this->error['warning'] = $this->language->get('error_permission');
			} else {
				$action = $this->request->get['action'];
				$file = (isset($this->request->get['file'])) ? $this->request->get['file'] : false;
				$path = (substr($file,-4) == '.bak' ? $this->config->get('vqm_backup') : $this->config->get('vqm_xml')) . $file;
				if (!isset($this->session->data['success'])) $this->session->data['success'] = '';
				if ($file) {
					if (isset($this->request->get['files'])) {
						$success = $this->model_tool_vqmod->{$action . 'File'}($path, true, $this->request->get['files']);
					} else {
						$success = $this->model_tool_vqmod->{$action . 'File'}($path);
						if (strpos($file, '|')) $file = str_replace('|', ' & ', substr($file, 0, -1));
					}
					if ($success && is_array($success)) foreach ($success as $message) $this->session->data['success'] .= $message . '<br/>';
					elseif ($success) $this->session->data['success'] .= sprintf($this->language->get('text_' . $action), $file);
					else $this->error['warning'] = sprintf($this->language->get('error_' . $action), $file);
				} else {
					$success = $this->model_tool_vqmod->{$action . 'Files'}();
					if ($success) $this->session->data['success'] .= $this->language->get('text_' . $action);
					else $this->error['warning'] = $this->language->get('error_' . $action);
				}
			}
		}

		if ($success) $this->redirect($this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));

		$this->data['heading_title'] = $this->language->get('heading_title');
		if ($vqmr_ver > str_replace('.', '', $this->model_tool_vqmod->version)) {
			$this->data['heading_title'] .= ' <small style="margin-left:8px;color:red;cursor:pointer;" class="vqmod-config vqmr-update vqtooltip">(' . $this->language->get('text_update_found') . $vqmr_version . ')</small>';
		}
		
		$this->data['column_name'] = $this->language->get('column_name');
		$this->data['column_version'] = $this->language->get('column_version');
		$this->data['column_vqmver'] = $this->language->get('column_vqmver') . ' <small style="margin-left:8px;">(' . VQMODVER . ')</small>';
		if ($vqm_ver > (int)str_replace('.', '', VQMODVER)) {
			$this->data['column_vqmver'] .= ' <small style="margin-left:8px;color:red;cursor:pointer;" class="vqmod-config vqm-update">(' . $this->language->get('text_update_found') . $vqm_version . ')</small>';
		}
		$this->data['column_author'] = $this->language->get('column_author');
		$this->data['column_action'] = $this->language->get('column_action');

		$this->data['button_config'] = $this->language->get('button_config');
		$this->data['button_update'] = $this->language->get('button_update');
		$this->data['button_update_vqmod'] = $this->language->get('button_update_vqmod');
		$this->data['button_log'] = $this->language->get('button_log');
		$this->data['button_log_clear'] = $this->language->get('button_log_clear');
		$this->data['button_log_delete'] = $this->language->get('button_log_delete');
		$this->data['button_log_download'] = $this->language->get('button_log_download');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_continue'] = $this->language->get('button_continue');
		$this->data['button_upload'] = $this->language->get('button_upload');
		$this->data['button_install_all'] = $this->language->get('button_install_all');
		$this->data['button_uninstall'] = $this->language->get('button_uninstall');
		$this->data['button_uninstall_all'] = $this->language->get('button_uninstall_all');
		$this->data['button_set_vqmod'] = $this->language->get('button_set_vqmod');
		$this->data['button_set_editor'] = $this->language->get('button_set_editor');
		$this->data['button_set_manual'] = $this->language->get('button_set_manual');

		$this->data['text_no_results'] = $this->language->get('text_no_results');
		$this->data['text_vqmod_config'] = $this->language->get('text_vqmod_config');
		$this->data['text_vqmod_log'] = $this->language->get('text_vqmod_log');
		$this->data['text_vqmod_upload'] = $this->language->get('text_vqmod_upload');
		$this->data['text_vqmod_uploads'] = $this->language->get('text_vqmod_uploads');
		$this->data['text_upload_uninstall'] = $this->language->get('text_upload_uninstall');
		$this->data['text_upload_continue'] = $this->language->get('text_upload_continue');
		$this->data['text_xml_new'] = $this->language->get('text_xml_new');
		$this->data['text_vqmod_exists'] = $this->language->get('text_vqmod_exists');
		$this->data['text_delete_header'] = $this->language->get('text_delete_header');
		$this->data['text_delete_files'] = $this->language->get('text_delete_files');
		$this->data['text_overwrite_header'] = $this->language->get('text_overwrite_header');
		$this->data['text_overwrite_files'] = $this->language->get('text_overwrite_files');
		$this->data['text_generate_mods'] = $this->language->get('text_generate_mods');
		$this->data['text_sort'] = $this->language->get('text_sort');
		$this->data['text_name_asc'] = $this->language->get('text_name_asc');
		$this->data['text_name_desc'] = $this->language->get('text_name_desc');
		$this->data['text_type_desc'] = $this->language->get('text_type_desc');
		$this->data['text_type_asc'] = $this->language->get('text_type_asc');
		$this->data['entry_select_file'] = $this->language->get('entry_select_file');
		$this->data['error_no_file'] = $this->language->get('error_no_file');
		$this->data['error_no_xml'] = $this->language->get('error_no_xml');

		$this->data['text_log_load'] = $this->language->get('text_log_load');

		$this->data['loading_image'] = "view/image/loading.png";

		$log_file = $this->config->get('log_file');
		// Check if vQMod is Installed (overwrites previous errors)
		$vqerror = (!file_exists($this->config->get('vqm') . 'install/index.php')) ? 'missing' : 'install';
		$installed = ($vqerror != 'missing') ? strpos(file_get_contents('../index.php'), 'VirtualQMOD') : false;
		if (!$installed) {
			$this->error['warning'] = $this->language->get('error_vqmod_' . $vqerror);
		} elseif (!file_exists($log_file)) {
			$this->model_tool_vqmod->createFile($log_file);
		}
		$this->data['log_files'] = array();
		if (file_exists($log_file)) {
			if (is_file($log_file)) {
				$this->data['log_files'][] = $log_file;
			} else {
				$dirfiles = glob($log_file . '*.log');
				foreach ($dirfiles as $path) $this->data['log_files'][basename($path)] = 'vQMod: ' . substr(basename($path),0,-4);
			}
		}

		if (!ini_get('date.timezone')) {
			date_default_timezone_set('UTC');
		}

		$vqmods = $this->model_tool_vqmod->getFiles();
		$msg = (isset($this->session->data['success'])) ? $this->session->data['success'] : '';
		foreach ($vqmods['message'] as $message) $msg .= ($msg ? '<br/>' : '') . $message;
		if (!isset($this->error['warning'])) $this->error['warning'] = '';
		foreach ($vqmods['error'] as $message) $this->error['warning'] .= ($this->error['warning'] ? '<br/>' : '') . $message;
		$this->session->data['success'] = $msg;
		$this->data['vqmods'] = $vqmods['files'];
		$enable = $disable = $this->data['install_all'] = $this->data['uninstall_all'] = false;
		foreach ($this->data['vqmods'] as $vqmod) {
			if ($vqmod['file'] != 'vQModerator.xml') {
				if ($vqmod['type'] == 'enabled') $disable = true;
				elseif ($vqmod['type'] != 'backup') $enable = true;
			}
		}
		if ($enable) {
			$this->data['install_all'] = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=enableall', 'SSL');
		}
		if ($disable) {
			$this->data['uninstall_all'] = $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'] . '&action=disableall', 'SSL');
		}

		$this->data['vqconfig'] = $this->model_tool_vqmod->settings('get');

		$this->data['vqmod_install'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/vqinstall', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_page'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_new_file'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/editor', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_config'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/saveconfig', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_check_dir'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/checkdir', 'token=' . $this->session->data['token'] . '&dir=', 'SSL'));
		$this->data['vqmod_setfilter'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/setfilter', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_generate'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/generator', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_log'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/log', 'token=' . $this->session->data['token'] . '&file=', 'SSL'));
		$this->data['vqmod_log_download'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/download', 'token=' . $this->session->data['token'] . '&file=', 'SSL'));

		// Added XML sorting
		$this->data['xml_sorter'] = false;
		if (isset($this->request->get['sort']) && isset($this->request->get['order'])) {
			$this->data['xml_sorter'] = $this->request->get['sort'] . '.' . $this->request->get['order'];
		}
		// Added XML filter
		$this->data['xml_filter'] = 'e';
		if (isset($this->session->data['vqmod']['filter'])) {
			$this->data['xml_filter'] = $this->session->data['vqmod']['filter'];
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		if (isset($this->session->data['x_able'])) {
			$this->data['x_able'] = $this->session->data['x_able'];
			unset($this->session->data['x_able']);
		} else {
			$this->data['x_able'] = '';
		}

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),     		
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->template = 'tool/vqmod.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function editor() {
		$this->load->language('tool/vqmod');
		$this->load->model('tool/vqmod');

		$this->document->setTitle($this->language->get('heading_title') . ' - ' . $this->language->get('heading_editor'));

		// Check if vQModerator is installed
		if ($this->config->get('show_trim') === null) {
			$this->model_tool_vqmod->settings();
			$this->error['warning'] = $this->language->get('error_settings');
		}

		$file = (isset($this->request->get['file'])) ? $this->request->get['file'] : false;
		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!$this->validate()) {
				$this->error['warning'] = $this->language->get('error_permission');
			} else {
				$file = $this->model_tool_vqmod->vqGen($this->request->post);

				if ($file) {
					if ($this->request->post['generatexml']) {
						if ((int)$this->request->post['generatexml'] >= 2) {
							echo 'SAVED';
							exit;
						} else {
							$this->session->data['success'] = sprintf($this->language->get('text_xml_success'), $file);
							$this->redirect($this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
						}
					} else {
						$newops = 0;
						foreach($this->request->post['newop'] as $ops) {
							foreach ($ops as $newop) $newops += $newop;
						}
						if ($newops) $this->session->data['success'] = ($newops == 1) ? $this->language->get('text_add_succes') : sprintf($this->language->get('text_add_success'), $newops);
					}
				} else {
					if ((int)$this->request->post['generatexml'] >= 2) {
						echo $this->language->get('error_saving_xml');
						exit;
					} else {
						$file = (isset($this->request->get['file'])) ? $this->request->get['file'] : false;
						$this->error['warning'] = $this->language->get('error_saving_xml');
					}
				}
			}
		}

		$this->document->addStyle('view/stylesheet/vqmod.css');
		$this->document->addScript('view/javascript/jquery/ajaxupload.js');
		if ($this->config->get('text_style')) {
			$this->document->addScript('view/javascript/codemirror.js');
		}

		$this->data['heading_title'] = $this->language->get('heading_title') . ' - ' . $this->language->get('heading_editor');

		$this->data['entry_xml_name'] = $this->language->get('entry_xml_name');
		$this->data['entry_xml_title'] = $this->language->get('entry_xml_title');
		$this->data['entry_xml_version'] = $this->language->get('entry_xml_version');
		$this->data['entry_xml_author'] = $this->language->get('entry_xml_author');
		$this->data['entry_vqm_version'] = $this->language->get('entry_vqm_version');
		$this->data['entry_vqmver_required'] = $this->language->get('entry_vqmver_required');
		$this->data['entry_vqmver_help'] = $this->language->get('entry_vqmver_help');
		$this->data['entry_file_path'] = $this->language->get('entry_file_path');
		$this->data['entry_file_error'] = $this->language->get('entry_file_error');
		$this->data['entry_newfile_error'] = $this->language->get('entry_newfile_error');
		$this->data['entry_newfile_exist'] = $this->language->get('entry_newfile_exist');
		$this->data['entry_newfile_chmod'] = $this->language->get('entry_newfile_chmod');
		$this->data['entry_newfile_skip'] = $this->language->get('entry_newfile_skip');
		$this->data['entry_newfile_update'] = $this->language->get('entry_newfile_update');
		$this->data['entry_newfile_delete'] = $this->language->get('entry_newfile_delete');
		$this->data['entry_remove'] = $this->language->get('entry_remove');
		$this->data['entry_add'] = $this->language->get('entry_add');
		$this->data['entry_after_this'] = $this->language->get('entry_after_this');
		$this->data['entry_skip'] = $this->language->get('entry_skip');
		$this->data['entry_log'] = $this->language->get('entry_log');
		$this->data['entry_abort'] = $this->language->get('entry_abort');
		$this->data['entry_ignoreif'] = $this->language->get('entry_ignoreif');
		$this->data['entry_ignoreif_check'] = $this->language->get('entry_ignoreif_check');
		$this->data['entry_ignoreif_needs'] = $this->language->get('entry_ignoreif_needs');
		$this->data['entry_search'] = $this->language->get('entry_search');
		$this->data['entry_position'] = $this->language->get('entry_position');
		$this->data['entry_position_help'] = $this->language->get('entry_position_help');
		$this->data['entry_replace'] = $this->language->get('entry_replace');
		$this->data['entry_ibefore'] = $this->language->get('entry_ibefore');
		$this->data['entry_before'] = $this->language->get('entry_before');
		$this->data['entry_iafter'] = $this->language->get('entry_iafter');
		$this->data['entry_after'] = $this->language->get('entry_after');
		$this->data['entry_top'] = $this->language->get('entry_top');
		$this->data['entry_bottom'] = $this->language->get('entry_bottom');
		$this->data['entry_all'] = $this->language->get('entry_all');
		$this->data['entry_offset'] = $this->language->get('entry_offset');
		$this->data['entry_offset_help'] = $this->language->get('entry_offset_help');
		$this->data['entry_index'] = $this->language->get('entry_index');
		$this->data['entry_index_help'] = $this->language->get('entry_index_help');
		$this->data['entry_error'] = $this->language->get('entry_error');
		$this->data['entry_error_help'] = $this->language->get('entry_error_help');
		$this->data['entry_regex'] = $this->language->get('entry_regex');
		$this->data['entry_trim'] = $this->language->get('entry_trim');
		$this->data['entry_trim_help'] = $this->language->get('entry_trim_help');
		$this->data['entry_trims'] = $this->language->get('entry_trims');
		$this->data['entry_info'] = $this->language->get('entry_info');

		$this->data['button_config'] = $this->language->get('button_config');
		$this->data['button_log'] = $this->language->get('button_log');
		$this->data['button_log_clear'] = $this->language->get('button_log_clear');
		$this->data['button_log_delete'] = $this->language->get('button_log_delete');
		$this->data['button_log_download'] = $this->language->get('button_log_download');
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_back'] = $this->language->get('button_back');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_generate_go'] = $this->language->get('button_generate_go');
		$this->data['button_generate_xml'] = $this->language->get('button_generate_xml');
		$this->data['button_generate_html'] = $this->language->get('button_generate_html');
		$this->data['button_add_operation'] = $this->language->get('button_add_operation');
		$this->data['button_add_file'] = $this->language->get('button_add_file');
		$this->data['button_add_newfile'] = $this->language->get('button_add_newfile');
		$this->data['button_upload'] = $this->language->get('button_upload');
		$this->data['button_restart'] = $this->language->get('button_restart');
		$this->data['button_set_vqmod'] = $this->language->get('button_set_vqmod');
		$this->data['button_set_editor'] = $this->language->get('button_set_editor');
		$this->data['button_set_manual'] = $this->language->get('button_set_manual');

		$this->data['text_search_found'] = $this->language->get('text_search_found');
		$this->data['text_search_not_found'] = $this->language->get('text_search_not_found');
		$this->data['text_xml_header'] = $this->language->get('text_xml_header');
		$this->data['text_file_header'] = $this->language->get('text_file_header');
		$this->data['text_newfile_header'] = $this->language->get('text_newfile_header');
		$this->data['text_operation'] = $this->language->get('text_operation');
		$this->data['text_collapse'] = $this->language->get('text_collapse');
		$this->data['text_expand'] = $this->language->get('text_expand');
		$this->data['text_this_file'] = $this->language->get('text_this_file');
		$this->data['text_this_operation'] = $this->language->get('text_this_operation');
		$this->data['text_all_files'] = $this->language->get('text_all_files');
		$this->data['text_vqmod_config'] = $this->language->get('text_vqmod_config');
		$this->data['text_vqmod_log'] = $this->language->get('text_vqmod_log');
		$this->data['text_vqmod_version'] = sprintf($this->language->get('text_vqmod_version'), VQMODVER);
		$this->data['text_generate_mods'] = $this->language->get('text_generate_mods');
		$this->data['text_autosave_time'] = $this->language->get('text_autosave_time');
		$this->data['text_autosave_help'] = $this->language->get('text_autosave_help');
		$this->data['text_seconds_togo'] = $this->language->get('text_seconds_togo');
		$this->data['error_add_operation'] = $this->language->get('error_add_operation');
		$this->data['error_position_all'] = $this->language->get('error_position_all');

		$this->data['text_log_load'] = $this->language->get('text_log_load');

		if (!ini_get('date.timezone')) {
			date_default_timezone_set('UTC');
		}

		$this->data['vqconfig'] = $this->model_tool_vqmod->settings('get');
		$this->data['vqmod_info'] = $this->model_tool_vqmod->getFile($file);
		if (isset($this->data['vqmod_info']->error)) $this->error['warning'] = $this->data['vqmod_info']->error;

		$this->data['oldfile'] = ($file && substr($file, -4) == '.tmp') ? substr($file, 0, -4) . '.xml' : ($file ? $file : '');
		$this->data['filename'] = ($file) ? substr($file, 0, strrpos(str_replace('.bak', '', $file), '.')) : '';
		$this->model_tool_vqmod->infoCheck($this->data['vqmod_info']->author, $this->data['entry_xml_author'], $this->data['oldfile'], $this->data['filename']);

		$this->data['vqmod_page'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_config'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/saveconfig', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_check_dir'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/checkdir', 'token=' . $this->session->data['token'] . '&dir=', 'SSL'));
		$this->data['vqmod_check_search'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/checksearch', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_search_delay'] = $this->config->get('search_delay');
		$this->data['vqmod_generate'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/generator', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['vqmod_log'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/log', 'token=' . $this->session->data['token'] . '&file=', 'SSL'));
		$this->data['vqmod_log_download'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/download', 'token=' . $this->session->data['token'] . '&file=', 'SSL'));
		$this->data['vqmod_restart'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/editor', 'token=' . $this->session->data['token'] . ($file ? '&file=' . $file : ''), 'SSL'));
		$this->data['autocomplete'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/autocomplete', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['upload'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/uploadtoxml', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['generate'] = str_replace('&amp;', '&', $this->url->link('tool/vqmod/editor', 'token=' . $this->session->data['token'], 'SSL'));
		$this->data['loading_image'] = "view/image/loading.png";
		$this->data['saved_image'] = "view/image/saved.png";
		$log_file = $this->config->get('log_file');
		$this->data['log_files'] = array();
		if (file_exists($log_file)) {
			if (is_file($log_file)) {
				$this->data['log_files'][] = $log_file;
			} else {
				$dirfiles = glob($log_file . '*.log');
				foreach ($dirfiles as $path) $this->data['log_files'][basename($path)] = 'vQMod: ' . substr(basename($path),0,-4);
			}
		}

		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_editor'),
			'href'      => $this->url->link('tool/vqmod/editor', ($file ? 'file=' . $file . '&' : '') . 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

		$this->template = 'tool/vqmod_edit.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render());
	}

	public function encode_image($image) {
		//$data = file_get_contents($_FILES['name_of_control']['tmp_name']);
		//$data = base64_encode($data);
		$imgtype = array('jpg', 'gif', 'png');
		$filename = file_exists($image) ? htmlentities($image) : die('Image file name does not exist');
		$filetype = pathinfo($filename, PATHINFO_EXTENSION);
		if (in_array($filetype, $imgtype)){
			$imgbinary = fread(fopen($filename, "r"), filesize($filename));
		} else {
			die ('Invalid image type, jpg, gif, and png is only allowed');
		}
		return 'data:image/' . $filetype . ';base64,' . base64_encode($imgbinary);
	}

	public function uploadToXml() {
		$allowed = array('tpl','php','css','js','log','txt','jpg','jpeg','png','bmp','gif');
		$text = array('tpl','php','css','js','log','txt');
		$this->language->load('tool/vqmod');
		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			if (!empty($this->request->files['file']['name'])) {
				$filename = html_entity_decode($this->request->files['file']['name'], ENT_QUOTES, 'UTF-8');
				$extension = utf8_substr(strrchr($filename, '.'), 1);
				if ((utf8_strlen($filename) < 3) || (utf8_strlen($filename) > 128)) {
					$json['error'] = $this->language->get('error_filename');
				}
				if (!in_array($extension, $allowed)) {
					$json['error'] = $this->language->get('error_filetype');
				}
				if ($this->request->files['file']['error'] != UPLOAD_ERR_OK) {
					$json['error'] = $this->language->get('error_upload_' . $this->request->files['file']['error']);
				}
			} else {
				$json['error'] = $this->language->get('error_upload');
			}
			if (!isset($json['error'])) {
				$file = $this->request->files['file']['tmp_name'];
				if (is_uploaded_file($file) && file_exists($file)) {
					$json['file'] = $filename;
					$json['data'] = base64_encode(file_get_contents($file));
					if (in_array($extension, $text)) {
						$json['mime'] = 'text';
					} else {
						$info = getimagesize($file);
						$json['mime'] = $info['mime'];
					}
				}
				$json['success'] = $this->language->get('text_upload');
			}
		}
		$this->response->setOutput(json_encode($json));
	}

	public function download() {
		if ($this->validate()) {
			if (isset($this->request->get['file'])) {
				$filename = $this->request->get['file'];
				if ($filename == 'log') {
					$filename = $this->config->get('config_error_filename');
					$file = DIR_SYSTEM . 'logs/' . $filename;
					$type = 'text/plain';
				} elseif (substr($filename, -4) == '.log') {
					$file = $this->config->get('log_file') . $filename;
					$type = 'text/plain';
				} elseif ($filename == 'vqlog') {
					$filename = 'vqmod.log';
					$file = $this->config->get('log_file');
					$type = 'text/plain';
				} else {
					$file = (substr($filename,-4) == '.bak' ? $this->config->get('vqm_backup') : $this->config->get('vqm_xml')) . $filename;
					$type = 'text/xml';
				}
				if (substr($file, -1) == '_') $filename = substr($filename, 0, -1);

				$this->response->addheader('Pragma: public');
				$this->response->addheader('Expires: 0');
				$this->response->addheader('Content-Description: File Transfer');
				$this->response->addheader('Content-Type: ' . $type);
				$this->response->addheader('Content-Disposition: attachment; filename=' . $filename);
				$this->response->addheader("Content-length: " . filesize($file));
				$this->response->addheader("Cache-control: private");

				$this->response->setOutput(readfile($file));
			}
		} else {
			return $this->forward('error/permission');
		}
	}

	public function backup($vqmod = false) {
		$this->load->language('tool/vqmod');

		if (!$this->validate()) {
			$this->error['warning'] = $this->language->get('error_permission');
			$this->redirect($this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
		} else {
			$vqmods = glob($this->session->data['vqm_xml'] . '*.xml*');

			$zipped = tempnam('tmp', 'zip');

			$zip = new ZipArchive();
			$zip->open($zipped, ZipArchive::OVERWRITE);
			foreach ($vqmods as $vqmod) {
				$zip->addFile($vqmod, basename($vqmod));
			}
			$zip->close();

			$this->response->addheader('Pragma: public');
			$this->response->addheader('Expires: 0');
			$this->response->addheader('Content-Description: File Transfer');
			$this->response->addheader('Content-Type: application/zip');
			$this->response->addheader('Content-Disposition: attachment; filename=vqmods_backup_' . date('Y-m-d') . '.zip');
			$this->response->addheader('Content-Transfer-Encoding: binary');
			$temp = readfile($zipped);
			@unlink($zipped);

			$this->response->setOutput($temp);
		}
	}

  	public function setfilter() {
		$this->session->data['vqmod']['filter'] = '';
		if (isset($this->request->post['xml_filter'])) {
			$filters = '';
			foreach ($this->request->post['xml_filter'] as $filter) $filters .= substr($filter, 0, 1);
			$this->session->data['vqmod']['filter'] = $filters;
		}
	}

  	public function generator() {
		$this->load->model('tool/vqmod');
		$this->model_tool_vqmod->generateAll();
	}

  	public function saveconfig() {
		$this->load->language('tool/vqmod');

		$json = array('success' => false, 'warning' => $this->language->get('error_save_config'));
		if (!$this->validate()) {
			$json['warning'] = $this->language->get('error_permission');
		}
		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validate()) {
			$this->load->model('tool/vqmod');
			$this->model_tool_vqmod->settings($this->request->post);
			$json['success'] = $this->language->get('text_success');
		}
		$this->response->setOutput(json_encode($json));
  	}

  	public function log() {
		$this->load->language('tool/vqmod');
		$log_file = (isset($this->request->get['file'])) ? $this->request->get['file'] : 'log';
		$log_dir = $this->config->get('log_file');
		if ($log_file == 'log') {
			$log_file = DIR_SYSTEM . 'logs/' . $this->config->get('config_error_filename');
		} else {
			if (file_exists($log_dir) && is_file($log_dir)) $log_file = $log_dir;
			else $log_file = $log_dir . $log_file;
		}

		$json = $this->language->get('error_log_load');
		if (!$this->validate()) {
			$json = $this->language->get('error_permission');
		} elseif (file_exists($log_file) && isset($this->request->get['action'])) {
			$action = $this->request->get['action'];
			if ($action == 'get') {
				if (filesize($log_file) > ($this->config->get('log_size') * 1048576)) {
					$json = sprintf($this->language->get('text_log_2big'), $this->config->get('log_size'));
				} elseif (filesize($log_file) < 1) {
					$json = $this->language->get('text_log_empty');
				} else {
					$json = file_get_contents($log_file, FILE_USE_INCLUDE_PATH, null);
				}
			} elseif ($action == 'clear') {
				$json = '';
				$handle = @fopen($log_file, 'w+');
				if (!fclose($handle)) {
					$json = $this->language->get('error_log_clear');
				}
			} elseif ($action == 'del') {
				$json = '';
				$success = @unlink($log_file);
				if (!$success) {
					$json = $this->language->get('error_log_delete');
				}
			}
			if (!$json) $json = $this->language->get('text_log_empty');
		}
		$this->response->setOutput(json_encode($json));
  	}

	public function autocomplete() {
		$json = array();

		$dir = (isset($this->request->get['dir'])) ? $this->request->get['dir'] : false;
		$path = (isset($this->request->get['path'])) ? $this->request->get['path'] : '';
		$files = (isset($this->request->get['files'])) ? (int)$this->request->get['files'] : 1;
		if ($dir !== false && substr_count($path . $dir, '*') <= 1) {
			$this->load->model('tool/vqmod');
			$json = $this->model_tool_vqmod->getTree('../' . $path, $dir, $files);
		}
		if (!$json && $dir && file_exists('../' . $path . $dir)) $json[] = $dir;

		$this->response->setOutput(json_encode($json));
	}

	public function checkdir() {
		$json = '';
		$dir = (isset($this->request->get['dir'])) ? $this->request->get['dir'] : false;
		$return = (isset($this->request->get['return'])) ? $this->request->get['return'] : 'exists';
		if ($dir) {
			$check = (isset($this->request->get['file'])) ? $this->request->get['file'] : false;
			if ($return != 'exists') {
				$this->load->language('tool/vqmod');
				$return = ($check) ? sprintf($this->language->get($return), $check) : $this->language->get($return);
			}
			if (substr($dir, -1) == '/') $dir = substr($dir, 0, -1);
			if (file_exists($dir) && is_dir($dir)) {
				if (!$check || file_exists($dir . '/' . $check)) $json = $return;
			}
		}
		$this->response->setOutput(json_encode($json));
	}

	public function checksearch() {
		$found = array();
		$file = (isset($this->request->post['file'])) ? '../' . $this->request->post['file'] : false;
		$search = (isset($this->request->post['search'])) ? htmlspecialchars_decode($this->request->post['search']) : false;
		$regex = isset($this->request->post['regex']);
		if (substr_count($file, '*') == 1) {
			$this->load->model('tool/vqmod');
			$sdir = false;
			$tdir = explode('*', $file);
			if (isset($tdir[1])) {
				if (substr($tdir[1], 0, 1) == '/') $tdir[1] = substr($tdir[1], 1);
				$dirs = $this->model_tool_vqmod->getTree($tdir[0]);
				foreach ($dirs as $sdir) {
					$cdir = $tdir[0] . $sdir . $tdir[1];
					if (file_exists($cdir)) {
						$file = $cdir;
						break;
					}
				}
			}
		}
		if ($file && $search && file_exists($file)) {
			$tests = $this->config->get('vqm') . 'test/';
			if (file_exists($tests) && is_dir($tests)) {
				$dirfiles = glob($tests . '*');
				foreach ($dirfiles as $path) {
					$files = $path . substr($file, 2);
					if (file_exists($files)) {
						$files = file_get_contents($files);
						$found[basename($path)] = substr_count($files, $search);
					} else {
						$found[basename($path)] = 'no file';
					}
				}
			}
			$file = file_get_contents($file);
			$count = substr_count($file, $search);
			if ($regex) $count = preg_match($search, $tmp);
			if (file_exists($tests) && is_dir($tests)) $found['Installed'] = $count;
			else $found['Result'] = $count;
		}
		$this->response->setOutput(json_encode($found));
	}

	public function vqinstall() {
		$this->load->language('tool/vqmod');
		$this->load->model('tool/vqmod');
		$admin = basename(DIR_APPLICATION);
		$json = '';
		$success = true;
		$install_vqmod = (isset($this->request->get['vqmod'])) ? $this->request->get['vqmod'] : false;
		if ($install_vqmod && $install_vqmod != 'xist') {
			$trunk = $this->config->get('vqm_trunk');
			if (!$trunk) $trunk = 'http://vqmod.googlecode.com/svn/trunk/';
			if (substr($trunk,0,7) !== 'http://') $trunk = 'http://' . $trunk;
			$vqm_opencart = $this->config->get('vqm_opcrt');
			if (!$vqm_opencart) $vqm_opencart = 'platforms/opencart/';
			$files = array(
				'vqmod/vqmod.php' => 'vqmod/vqmod.php',
				'vqmod/install/ugrsr.class.php' => 'vqmod/install/ugrsr.class.php',
				'vqmod/install/index.php' => $vqm_opencart . 'install/index.php',
				'vqmod/xml/vqmod_opencart.xml' => $vqm_opencart . 'xml/vqmod_opencart.xml',
			);
			foreach ($files as $local => $remote) {
				$remote = $trunk . $remote;
				if ($this->model_tool_vqmod->isRemoteFile($remote)) {
					$data = file_get_contents($remote);
					// Get the newly installed vqmod version
					if ($local == 'vqmod/vqmod.php') $version = $this->getVersion($data);
					// Set the admin folder in vQMod Install file
					if ($local == 'vqmod/install/index.php') $data = str_replace("= 'admin';", "= '$admin';", $data);
					if ($success) $success = $this->model_tool_vqmod->createFile('../' . $local, $data, 'text', 0755);
				} else {
					$success = false;
				}
			}
		} else {
			$success = false;
		}
		if (!$success) {
			// Search the currently installed vqmod version
			if (file_exists('../vqmod/vqmod.php')) {
				$data = file_get_contents('../vqmod/vqmod.php');
				$version = $this->getVersion($data);
				if ($version) $success = true;
			}
		}
		// We should now have the installed vQMod Version
		if ($success && $version) {
			$re = '';
			if (file_exists('../vqmod/xml/vQModerator.xml')) {
				$this->model_tool_vqmod->deleteFile('../vqmod/xml/vQModerator.xml');
				$re = 'Re-';
			}
			// Get repository vQModerator version
			$modver = 'http://vqmoderator.googlecode.com/svn/trunk/version';
			$modver = ($this->model_tool_vqmod->isRemoteFile($modver)) ? file_get_contents($modver) : 0;
			if ($modver) {
				$modver = explode("\n", $modver);
				$modver = trim($modver[0]); // Version is first line (rest is changelog)
			}
			if ((int)str_replace('.', '', $modver) > (int)str_replace('.', '', $this->version)) {
				$this->model_tool_vqmod->version = $modver;
				// Update vQModerator from Repository
				$files = 'http://vqmoderator.googlecode.com/svn/trunk/files';
				$files = ($this->model_tool_vqmod->isRemoteFile($files)) ? file_get_contents($files) : '';
				$files = explode("\n", $files);
				foreach ($files as $file) {
					$remote = 'http://vqmoderator.googlecode.com/svn/trunk/' . $file;
					if ($file && $this->model_tool_vqmod->isRemoteFile($remote)) {
						$data = file_get_contents($remote);
						if ($success) $success = $this->model_tool_vqmod->createFile('../' . $file, $data, 'text', 0755);
					} else {
						$success = false;
					}
				}
				if ($success) $json .= $this->language->get('text_success_installl');
			}
			$this->model_tool_vqmod->installvQModerator($version);
			$json .= sprintf($this->language->get('text_success_instal'), $re);

			if ($install_vqmod) {
				$json .= $this->language->get('text_success_install');
				// Set and Save permissions
				$chmods = array(
					'../index.php' => $this->model_tool_vqmod->setPermission('../index.php', 0755),
					'../' . $admin => $this->model_tool_vqmod->setPermission('../' . $admin, 0755),
					'./index.php' => $this->model_tool_vqmod->setPermission('./index.php', 0755)
				);
				$json .= file_get_contents(HTTP_CATALOG . str_replace('../', '', $this->config->get('vqm')) . 'install/index.php');
				if (strpos($json, 'INSTALLED') === false && strpos($json, 'UPGRADE COMPLETE') === false) {
					$json .= $this->language->get('text_failed_instal');
					$json .= file_get_contents(HTTP_CATALOG . str_replace('../', '', $this->config->get('vqm')) . 'install/index.php');
					if (strpos($json, 'INSTALLED') === false) $json .= $this->language->get('text_failed_install');
					else $json .= $this->language->get('text_success_afterall');
				}
				if (strpos($json, 'INSTALLED') !== false || strpos($json, 'UPGRADE COMPLETE') !== false) {
					$json .= sprintf($this->language->get('text_going_reload'), $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
				}
				// Restore Saved permissions
				foreach ($chmods as $file => $chmod) $this->model_tool_vqmod->setPermission($file, $chmod);
			} else {
				$json .= sprintf($this->language->get('text_going_reload'), $this->url->link('tool/vqmod', 'token=' . $this->session->data['token'], 'SSL'));
			}
		} else {
			$json .= $this->language->get('text_failed_installl');
		}

		$this->response->setOutput($json);
	}
	private function getVersion($data) {
		if (!$data || strpos($data, '$_vqversion') === false) return false;
		$version = explode('$_vqversion', $data, 2);
		$version = explode("';", $version[1], 2);
		$version = trim(str_replace("'", '', str_replace('=', '', $version[0])));
		return $version;
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'tool/vqmod')) {
			$this->error['warning'] = $this->language->get('error_permission');
			return false;
		} else {
			return true;
		}
	}
}
?>