<?php echo $header; ?>
<div id="content">
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <?php if ($error_warning) { ?>
  <div class="warning"><?php echo $error_warning; ?></div>
  <?php } ?>
  <?php if ($success) { ?>
  <div class="success"><?php echo $success; ?></div>
  <?php } ?>
  <div class="box">
    <div class="heading">
      <h1><img src="view/image/setting.png" alt="" /> <?php echo $heading_title; ?></h1>
      <div class="buttons">
        <?php echo $text_sort;?> <select id="xml-sorter">
          <option value="file.asc"<?php if (!$xml_sorter || $xml_sorter == 'file.asc') echo ' selected="selected"';?>><?php echo $text_name_asc;?></option>
          <option value="file.desc"<?php if ($xml_sorter == 'file.desc') echo ' selected="selected"';?>><?php echo $text_name_desc;?></option>
          <option value="type.desc"<?php if ($xml_sorter == 'type.desc') echo ' selected="selected"';?>><?php echo $text_type_desc;?></option>
          <option value="type.asc"<?php if ($xml_sorter == 'type.asc') echo ' selected="selected"';?>><?php echo $text_type_asc;?></option>
        </select> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;
        <?php if ($install_all) { ?><a class="button" href="<?php echo $install_all;?>"><?php echo $button_install_all; ?></a><?php } ?>
        <?php if ($uninstall_all) { ?><a class="button" href="<?php echo $uninstall_all;?>"><?php echo $button_uninstall_all; ?></a><?php } ?>
        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a class="button vqmod-config"><?php echo $button_config; ?></a> <a class="button vqmod-log"><?php echo $button_log; ?></a> &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a class="button vqmod-upload"><?php echo $button_upload; ?></a></div>
    </div>
    <div class="content">
      <table class="list">
        <thead>
          <tr>
            <td class="left">&nbsp;</td>
            <td class="left"><?php echo $column_name; ?></td>
            <td class="left"><?php echo $column_version; ?></td>
            <td class="left"><?php echo $column_vqmver; ?></td>
            <td class="left"><?php echo $column_author; ?></td>
            <td class="right"><?php echo $column_action; ?></td>
          </tr>
        </thead>
        <tbody>
        <?php if ($vqmods) { ?>
          <?php foreach ($vqmods as $vqmod) { ?>
          <tr class="xml-type-<?php echo $vqmod['type'];?>">
			<td class="left"><?php echo $vqmod['install']; ?></td>
			<td class="left">
				<b><?php echo $vqmod['title'];?></b><br/>
				<small style="color:#666;"><?php echo $vqmod['file']; ?> &nbsp; (<?php echo $vqmod['size']; ?>)</small>
			</td>
            <td class="left"><?php echo $vqmod['version']; ?><br/><small style="color:#666;">(<?php echo $vqmod['date'];?>)</small></td>
            <td class="left"><?php echo $vqmod['vqmver']; ?></td>
            <td class="left"><?php echo $vqmod['author']; ?></td>
            <td class="right"><?php foreach ($vqmod['action'] as $action) { ?>
              &nbsp;<a href="<?php echo $action['href']; ?>"><?php echo $action['text']; ?></a>
              <?php } ?></td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td class="center" colspan="6"><?php echo $text_no_results; ?></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      <div id="xml-filter" style="display:inline-block;float:left;">
        <input type="checkbox" value="enabled" id="xml-enabled" name="xml_filter[]"<?php if (!$xml_filter || strpos($xml_filter, 'e') !== false) echo ' checked="checked"';?>><label for="xml-enabled"><img title="Installed" alt="Installed" src="view/image/success.png" /></label>
        <input type="checkbox" value="disabled" id="xml-disabled" name="xml_filter[]"<?php if (strpos($xml_filter, 'd') !== false) echo ' checked="checked"';?>><label for="xml-disabled"><img title="Uninstalled" alt="Uninstalled" src="view/image/attention.png" /></label>
        <input type="checkbox" value="backup" id="xml-backups" name="xml_filter[]"<?php if (strpos($xml_filter, 'b') !== false) echo ' checked="checked"';?>><label for="xml-backups"><img title="Backup File" alt="Backup File" src="view/image/product.png" style="width:16px;height:16px;" /></label>
      </div>
      <a href="<?php echo $vqmod_new_file;?>" style="float:right;"><?php echo $text_xml_new;?></a>
    </div>
  </div>
</div>
<div id="vqmod-upload" style="display:none;">
  <form action="<?php echo $vqmod_page; ?>" method="post" enctype="multipart/form-data" id="vqmod-uploader">
    <table class="list">
      <tbody>
        <tr><td class="left" colspan="2"><?php echo $text_vqmod_uploads; ?></td></tr>
        <tr>
          <td class="left"><?php echo $entry_select_file; ?></td>
          <td class="left"><input id="vqmod-xml" name="vqmod_xml" type="file" /></td>
        </tr>
      </tbody>
    </table>
  </form>
</div>
<div id="vqmod-log" style="display:none;">
	<textarea id="log" readonly="readonly"></textarea>
	<select id="select-log">
		<option value="log">OpenCart</option>
		<?php foreach ($log_files as $log_file => $log_name) { ?><option value="<?php echo $log_file;?>"><?php echo $log_name;?></option><?php } ?>
	</select>
	</div>
	<textarea id="loadlog" style="display:none;"><?php echo $text_log_load;?></textarea>
</div>
<div id="vqmod-config" style="display:none;">
    <?php foreach ($vqconfig as $vqname => $vqval) { ?>
      <?php if ($vqname == 'vqm' || $vqname == 'vqm_create' || $vqname == 'generate_html') { ?>
      <?php $edit_id = ($vqname == 'vqm') ? 'set-vqmod' : ($vqname == 'vqm_create' ? 'set-editor' : 'set-manual');?>
  <table class="list"<?php if ($vqname != 'vqm') echo ' style="display:none"';?> id="<?php echo $edit_id;?>">
    <tbody>
	  <?php } ?>
      <tr>
      <?php if ($vqname == 'vqm_create' || $vqname == 'show_trim' || $vqname == 'show_regex' || $vqname == 'show_info' || $vqname == 'generate_html' || $vqname == 'text_style') { ?>
        <?php if ($vqname == 'show_regex' && !isset($vqconfig['show_trim'])) { ?>
        <td class="left"><?php echo $this->language->get('entry_show_trim'); ?></td>
        <td class="left">
          <input name="show_trim" type="checkbox" value="1" checked="checked" />
        </td>
        <?php } ?>
        <td class="left"><?php echo $this->language->get('entry_' . $vqname); ?></td>
        <td class="left">
          <input name="<?php echo $vqname;?>" type="checkbox" value="1" <?php if ($vqval) echo 'checked="checked" ';?>data-orig="<?php echo $vqval;?> "/>
		  <?php if ($this->language->get('entry_help_' . $vqname) != 'entry_help_' . $vqname) echo $this->language->get('entry_help_' . $vqname);?>
        </td>
	  <?php } elseif ($vqname == 'manual_css') { ?>
        <td class="left" colspan="2">
          <?php echo $this->language->get('entry_' . $vqname); ?><br/>
          <textarea name="manual_css" id="manual_css" style="width:565px;" rows="4" data-orig="<?php echo $vqval;?>"><?php echo $vqval;?></textarea>
        </td>
      <?php } else { ?>
        <td class="left"><?php echo $this->language->get('entry_' . $vqname); ?></td>
        <td class="left">
          <?php if ($vqname == 'vqm_xml' || $vqname == 'vqm_cache' || $vqname == 'vqm_backup' || $vqname == 'log_file') { ?>
          <input class="vqm" type="text" style="width:120px;" disabled="disabled" readonly="readonly" value="<?php echo $vqconfig['vqm'];?>" />
          <input name="<?php echo $vqname;?>" type="text" class="vqdir" style="width:260px;" value="<?php echo $vqval;?>" data-orig="<?php echo $vqval;?>" />
          <?php } else { ?>
          <input name="<?php echo $vqname;?>" type="text"<?php if ($vqname == 'vqm') echo ' class="vqdir"';?> style="width:380px;" value="<?php echo $vqval;?>" data-orig="<?php echo $vqval;?>" />
          <?php } ?>
        </td>
      <?php } ?>
      </tr>
      <?php if ($vqname == 'vqm_opcrt' || $vqname == 'search_delay' || $vqname == 'manual_css') { ?>
    </tbody>
  </table>
      <?php if ($edit_id == 'set-vqmod') { ?>
  <div id="update-buttons" style="float:right"><button class="update-vqmod" id="update-vqmod"><?php echo $button_update_vqmod;?></button> &nbsp; <button class="update-vqmod"><?php echo $button_update;?></button></div>
	  <?php } ?>
	  <?php } ?>
    <?php } ?>
</div>
<div id="vqdialog" style="display:none;"></div>
<div id="vqtooltip" style="display:none;position:absolute;"><?php echo $changelog;?></div>
<div id="vqloading" style="position:fixed;width:100%;text-align:center;top:180px;display:none;"><img alt="Loading..." src="<?php echo $loading_image;?>" /></div>
<div id="vqgenerate" style="display:none;position:absolute;"><?php echo $text_generate_mods;?></div>
<script type="text/javascript">
<?php if ($changelog) { ?>
$('.vqtooltip').mouseenter(function() {
	$('#vqtooltip').fadeIn('slow');
	$(document).mousemove( function(e) {
		$('#vqtooltip').css({'top': e.pageY + 5, 'left': e.pageX + 5});
	});
}).mouseleave(function() {
	$('#vqtooltip').fadeOut('slow');
	$(document).unbind("mousemove");
}).click(function() {
	$('#vqtooltip').fadeOut('slow');
	$(document).unbind("mousemove");
});
<?php } ?>
$(document).ready(function() {
	$('#xml-filter').buttonset();
	$('input[name="xml_filter[]"]').change(function() {
		var these = $(this).val();
		if (!$(this).is(':checked')) $('.xml-type-' + these).hide();
		else $('.xml-type-' + these).fadeIn();
		$.ajax({
			url: '<?php echo $vqmod_setfilter;?>',
			data: $('input[name="xml_filter[]"]:checked'),
			type: 'POST'
		});
	});
	$('input[name="xml_filter[]"]').change();
	$('#xml-sorter').change(function() {
		var args = $(this).val().split('.');
		window.location.href = '<?php echo $vqmod_page; ?>&sort=' + args[0] + '&order=' + args[1];
	});
	// Press shift to generate vQModifications
	$(document).keydown(function(e) {
		$(this).disableSelection();
		if (e.shiftKey) {
			var div = $('#vqgenerate');
			div.fadeIn();
			$(document).mousemove(function(ev) {
				div.css({
				   left:  ev.pageX -28,
				   top:   ev.pageY -25
				});
			});
		}
	}).keyup(function() {
		$(this).enableSelection();
		$('#vqgenerate').fadeOut(function(){
			$(document).unbind("mousemove");
		});
	});
	$('#vqgenerate').click(function() {
		$(document).unbind("keydown");
		$('#vqgenerate').hide();
		$('#vqloading').fadeIn();
		$.ajax({
			url: '<?php echo $vqmod_generate;?>',
			success: function() {
				$('.loading').remove();
			},
			complete: function() {
				$('#vqloading').fadeOut('slow');
			}
		});
	});

	$('.uninstall').click(function() {
		var url = $(this).attr('href'),
			delfiles = $(this).data('files').split('|'),
			sep = ($(this).hasClass('delete')) ? '|' : '&files=',
			files = '',
			href = '';
		for (var f in delfiles) if (delfiles[f]) files += '<input type="checkbox" class="delfile" value="' + delfiles[f] + '" checked="checked" /> ' + delfiles[f] + '<br/>';
		href = url + (files ? sep + $(this).data('files') : '');
		files = '<?php echo $text_delete_files;?><br/>' + files;
		$('#vqdialog').html(files);
		$('.delfile').click(function() {
			delfiles = '';
			$('.delfile:checked').each(function() {
				delfiles += $(this).val() + '|';
			});
			href = url + (delfiles ? sep + delfiles : '');
		});

		$('#vqdialog').dialog({
			title: '<?php echo $text_delete_header;?>',
			autoOpen: true,
			width: 'auto',
			height: 'auto',
			buttons: {
				'<?php echo $button_continue;?>': function() {
					window.location.href = href;
				},
				'<?php echo $button_cancel;?>': function() {
					$('#vqdialog').html('');
					$(this).dialog('close');
				}
			}
		});
		return false;
	});

	$('.vqmod-upload').click(function() {
		$('.warning, .success').fadeOut(300, function() { $('.warning, .success').remove(); });
		$('#vqmod-upload').dialog({
			title: '<?php echo $text_vqmod_upload;?>',
			autoOpen: true,
			width: 'auto',
			height: 'auto',
			buttons: {
				'<?php echo $button_upload;?>': function() {
					if ($('#vqmod-xml').val() == '') {
						$('#vqmod-xml').after('<div class="warning"><?php echo $error_no_file;?></div>');
					} else {
						$('#vqmod-uploader').submit();
					}
				},
				'<?php echo $button_cancel;?>': function() {
					$(this).dialog('close');
				}
			}
		});
	});
	$('#vqmod-xml').change(function() {
		var file = $(this).val();
		if (file == '') {
			$('#vqmod-xml').after('<div class="warning"><?php echo $error_no_file;?></div>');
		} else if (file.substr(file.length -4) != '.xml' && file.substr(file.length -5) != '.xml_') {
			$('#vqmod-xml').after('<div class="warning"><?php echo $error_no_xml;?></div>');
		}
	});
	$('.vqmod-install').click(function() {
		var xist = ($(this).is('#install-xisting')) ? 'xist' : '1';
		$(this).after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
		$('.success').fadeOut(300);
		$.ajax({
			url : '<?php echo $vqmod_install;?>&vqmod=' + xist,
			dataType: 'html',
			success: function(data) {
				if (data.indexOf('INSTALLED') === -1 && data.indexOf('UPGRADE') === -1) {
					$('.warning').html(data).fadeIn(400);
				} else {
					$('.warning').addClass('success').removeClass('warning').html(data).fadeIn(400);
				}
			}
		});
	});

	$('.vqmod-config').click(function() {
		var highlight = ($(this).hasClass('vqm-update')) ? 'vqm' : ($(this).hasClass('vqmr-update') ? 'vqmr' : false);
		$('.warning, .success').fadeOut(300, function() { $('.warning, .success').remove(); });
		$('#vqmod-config').dialog({
			title: '<?php echo $text_vqmod_config;?>',
			autoOpen: true,
			width: '750',
			height: '550',
			buttons: [{
				id: 'button-set-vqmod',
				text: '<?php echo $button_set_vqmod;?>',
				click: function() {
					var config = $('#vqmod-config').parent();
					config.find('table:visible').hide();
					$('#set-vqmod, #update-buttons').show();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-vqmod').button('disable');
				}
			},{
				id: 'button-set-editor',
				text: '<?php echo $button_set_editor;?>',
				click: function() {
					var config = $('#vqmod-config').parent();
					config.find('table:visible, #update-buttons').hide();
					$('#set-editor').show();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-editor').button('disable');
				}
			},{
				id: 'button-set-manual',
				text: '<?php echo $button_set_manual;?>',
				click: function() {
					var config = $('#vqmod-config').parent();
					config.find('table:visible, #update-buttons').hide();
					$('#set-manual').show();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-manual').button('disable');
				}
			},{
				text: '<?php echo $button_save;?>',
				click: function() {
					$.ajax({
						url : '<?php echo $vqmod_config;?>',
						type: 'POST',
						data: $('#vqmod-config').find('input:not("[type=checkbox]"), input:checked, textarea'),
						dataType: 'json',
						success: function(data) {
							var div = $('<div/>').hide();
							if (data.success) {
								div.addClass('success').html(data.success);
							} else {
								div.addClass('warning').html(data.warning);
							}
							$('.breadcrumb').after(div);
							div.fadeIn(400);
							$('#vqmod-config').dialog('close');
						}
					});
				}
			},{
				text: '<?php echo $button_cancel;?>',
				click: function() {
					$(':input', '#vqmod-config').each(function() {
						var orig = $(this).data('orig');
						if ($(this).is(':checkbox')) {
							var checked = $(this).is(':checked');
							if (checked != orig) $(this).click();
						} else {
							$(this).val(orig);
							$(this).keyup();
						}
					});
					if (!$('input[name="generate_html"]').is(':checked')) $('[name="manual_css"]').parent().fadeOut();
					$(this).dialog('close');
				}
			}],
			open: function() {
				$('#button-set-vqmod').button('disable');
				$('.update-vqmod').button();
				if (highlight) $('.update-vqmod').addClass('ui-state-highlight');
				if (highlight == 'vqm') $('.update-vqmod:not("#update-vqmod")').removeClass('ui-state-highlight');
				else if (highlight == 'vqmr') $('#update-vqmod').removeClass('ui-state-highlight');
			}
		});
	});
	$('.update-vqmod').click(function() {
		$(this).after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
		var vqmod = ($(this).is('#update-vqmod')) ? '&vqmod=1' : '';
		$.ajax({
			url : '<?php echo $vqmod_install;?>' + vqmod,
			dataType: 'html',
			success: function(data) {
				var div = $('<div/>').hide();
				if (vqmod === '' || data.indexOf('INSTALLED') != -1 || data.indexOf('UPGRADE') != -1) {
					div.addClass('success').html(data).fadeIn(400);
				} else {
					div.addClass('warning').html(data).fadeIn(400);
				}
				$('.breadcrumb').after(div);
				$('.loading').remove();
				$('#vqmod-config').dialog('close');
			}
		});
	});
	$('.vqdir').blur(function() {
		var vqd = $(this);
		var name = vqd.attr('name'),
			vqmdir = $('input[name="vqm"]').val(),
			value = $.trim(vqd.val());
		if (value.indexOf('/') == 0) value = value.substr(1);
		if (name != 'log_file' && value.lastIndexOf('/') != value.length - 1) value += '/';
		if (name != 'vqm') value = value.replace(vqmdir, '');
		else $('.vqm').val(value);

		vqd.val(value);
		$(this).keyup();
	});
	var t = {};
	$('.vqdir').keyup(function() {
		var vqd = $(this);
		var name = vqd.attr('name'),
			vqdir = ((name != 'vqm') ? vqd.prev('.vqm') : $('.vqm')),
			vqmdir = $('input[name="vqm"]').val(),
			value = $.trim(vqd.val());
		if (t[name]) clearTimeout(t[name]);
		if (name == 'vqm') {
			$('.vqm').val(value);
			$('.vqdir:not([name="vqm"])').keyup();
		} else {
			value = vqmdir + '&file=' + value;
		}
		var checkdir = function() {

			$.ajax({
				url : '<?php echo $vqmod_check_dir;?>' + value,
				dataType: 'json',
				success: function(exists) {
					if (exists == 'exists') {
						var color = '#ccffc4', colors = '#add7a6';
					} else {
						var color = '#ffbebe', colors = '#dfa6a6';
					}

					vqd.animate({'background-color': color}, 500);
					vqdir.animate({'background-color': colors}, 500);
				}
			});
		};
		t[name] = setTimeout(checkdir, 800);
	});
	$('.vqdir').blur();

	$('.vqmod-log').click(function() {
		$('#vqmod-log').dialog({
			title: '<?php echo $text_vqmod_log;?>',
			autoOpen: true,
			width: 'auto',
			height: 'auto',
			buttons: [{
				text: '<?php echo $button_log_download;?>',
				id: 'log-download',
				click: function(e) {
					e.preventDefault();
					window.location.href = '<?php echo $vqmod_log_download;?>' + $('#select-log').val();
				}
			}, {
				text: '<?php echo $button_log_delete;?>',
				click: function(e) {
					loadLog('del');
				}
			}, {
				text: '<?php echo $button_log_clear;?>',
				click: function(e) {
					loadLog('clear');
				}
			}],
			open: function() {
				$(this).parent().find('.ui-dialog-buttonpane').prepend($('#select-log'));
				$('#log').html($('#loadlog').html());
				setTimeout("loadLog();", 1500);
			},
			beforeClose: function() {
				$('#select-log').appendTo('#vqmod-log');
			}
		});
	});
	$('#select-log').change(function() {
		loadLog();
	});
});
function loadLog(action) {
	action = action ? action : 'get';
	var selected = $('#select-log').val();
	$.ajax({
		url : '<?php echo $vqmod_log;?>' + selected + '&action=' + action,
		dataType: 'json',
		success: function(data) {
			if (action == 'del') {
				$('#select-log').find('option[value="' + selected + '"]').remove();
			}
			$('#log').html(data);
		}
	});
	return false;
}
</script>
<?php echo $footer; ?>