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
      <div class="buttons"><input id="autosave" type="text" value="<?php echo $text_autosave_time;?>" title="<?php echo $text_autosave_help;?>" class="numeric" />
		&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a class="button vqmod-config"><?php echo $button_config; ?></a> <a class="button vqmod-log"><?php echo $button_log; ?></a>
        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <a class="button generate continue"><?php echo $button_generate_go;?></a> <a class="button generate"><?php echo $button_save; ?></a> <a class="button" href="<?php echo $vqmod_page;?>" onclick="return confirm('<?php echo $this->language->get('text_confirm_back');?>');"><?php echo $button_back; ?></a></div>
    </div>
    <div class="content">
      <form name="generator" id="generator" action="<?php echo $vqmod_editor;?>" method="post">
        <fieldset class="ma">
          <legend><?php echo $text_xml_header;?></legend>
          <table style="border-width:0px;" cellspacing="8">
            <?php if ($filename !== false) { ?>
            <tr>
              <td style="width:250px;"><?php echo $entry_xml_name;?><input type="hidden" name="oldfile" value="<?php echo $oldfile;?>" /></td>
              <td><input id="filename" name="filename" type="text" onblur="$(this).val($(this).val().replace('.xml', ''));" size="32" value="<?php echo $filename;?>" />.xml</td>
            </tr>
			<?php } ?>
            <tr>
              <td><?php echo $entry_xml_title;?></td>
              <td><input id="fileid" name="fileid" type="text" style="width:400px;" value="<?php echo $vqmod_info->id; ?>"></td>
            </tr>
            <tr>
              <td><?php echo $entry_xml_version;?></td>
              <td><input id="version" name="version" type="text" style="width:50px;" value="<?php echo $vqmod_info->version; ?>" class="numeric"></td>
            </tr>
            <?php if ($entry_xml_author) { ?>
            <tr>
              <td><?php echo $entry_xml_author;?></td>
              <td><input id="author" name="author" type="text" style="width:400px;" value="<?php echo $vqmod_info->author; ?>"></td>
            </tr>
            <?php } ?>
            <tr>
              <td><?php echo $entry_vqm_version;?> <span id="vqmver-required" style="<?php if ((int)str_ireplace(array('v','.'), '', $vqmod_info->vqmver) < 240) echo 'display:none;';?>margin-left:20px;"><?php echo $entry_vqmver_required;?> <input id="vqmodver-required" name="vqmodver_required" type="checkbox" value="1" <?php if (isset($vqmod_info->vqmver['required']) && $vqmod_info->vqmver['required']) echo 'checked="checked" '; ?>></span><?php echo $entry_vqmver_help;?></td>
              <td><input id="vqmodver" name="vqmodver" type="text" style="width:50px;" value="<?php echo str_ireplace('v', '', $vqmod_info->vqmver); ?>" class="numeric"> <?php echo $text_vqmod_version;?>
              <?php if ($filename === false) { ?><input type="hidden" name="oldfile" value="<?php echo $oldfile;?>" /><input name="filename" type="hidden" value="<?php echo substr($oldfile, 0, -4);?>" /><?php } ?>
              <?php if (!$entry_xml_author) { ?><input name="author" type="hidden" style="width:400px;" value="<?php echo $vqmod_info->author; ?>"><?php } ?>
			  </td>
            </tr>
          </table>
          <div class="arrow <?php echo (isset($vqmod_info->file)) ? 'down' : 'up';?>" title="<?php echo (isset($vqmod_info->file) ? $text_expand : $text_collapse) . $text_all_files;?>"></div>
        </fieldset>

        <div id="vqmods"></div>

        <div id="add-buttons" class="buttons" style="margin-top:10px;">
          <div style="width:33%;display:inline-block;margin-top:10px;"><a class="button add_op ui-state-disabled"><?php echo $button_add_operation;?></a> &nbsp; <a class="button generate continue"><?php echo $button_generate_go;?></a></div>
		  <div style="width:34%;text-align:center;display:inline-block;margin-top:10px;"><a id="generatexml"class="button"><?php echo $button_generate_xml;?></a><input name="generatexml" type="hidden" value="0" /></div>

          <div style="width:33%;display:inline-block;margin-top:10px;"><a class="button add_fi" style="background-color:green;"><?php echo $button_add_file;?></a></div>
		  <div style="width:34%;text-align:center;display:inline-block;margin-top:10px;"><a id="generatehtml" class="button"><?php echo $button_generate_html;?></a><input name="generatehtml" type="hidden" value="<?php echo ($vqconfig['generate_html']) ? '0' : '1';?>" /></div>

          <div style="width:100%;display:inline-block;margin-top:10px;"><a class="button add_new" style="background-color:#420080;"><?php echo $button_add_newfile;?></a></div>

          <div style="width:100%;display:inline-block;margin-top:10px;"><a class="button restart" href="<?php echo $vqmod_editor;?>" onclick="return confirm('<?php echo $this->language->get('text_confirm_restart');?>');" style="background-color:#cd0000;"><?php echo $button_restart;?></a></div>
        </div>
      </form>
    </div>
  </div>
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
  <input name="vqm_cache" type="hidden" value="<?php echo $vqconfig['vqm_cache'];?>" />
  <input name="log_file" type="hidden" value="<?php echo $vqconfig['log_file'];?>" />
<?php unset($vqconfig['vqm_cache'], $vqconfig['log_file']); ?>
<?php foreach ($vqconfig as $vqname => $vqval) { ?>
  <?php if ($vqname == 'update' || $vqname == 'vqm_create' || $vqname == 'generate_html') { ?>
  <?php $edit_id = ($vqname == 'update') ? 'set-vqmod' : ($vqname == 'vqm_create' ? 'set-editor' : 'set-manual');?>
  <table class="list"<?php if ($vqname != 'update') echo ' style="display:none"';?> id="<?php echo $edit_id;?>">
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
    <?php if ($vqname == 'vqm_backup') { ?>
        <input name="<?php echo $vqname;?>" type="text" class="vqdir" style="width:380px;" value="<?php echo $vqval;?>" data-orig="<?php echo $vqval;?>" />
    <?php } elseif ($vqname == 'vqm_cache' || $vqname == 'log_file') { ?>
        <input name="<?php echo $vqname;?>" type="hidden" value="<?php echo $vqval;?>" />
    <?php } else { ?>
        <input name="<?php echo $vqname;?>" type="text" style="width:380px;" value="<?php echo $vqval;?>" data-orig="<?php echo $vqval;?>" />
    <?php } ?>
      </td>
  <?php } ?>
    </tr>
  <?php if ($vqname == 'log_size' || $vqname == 'search_delay' || $vqname == 'manual_css') { ?>
  </table>
	  <?php } ?>
    <?php } ?>
</div>
<img id="image-preview" style="display:none;position:fixed;" src="" />
<div id="vqloading" style="position:fixed;width:100%;text-align:center;top:180px;"><img alt="Loading..." src="<?php echo $loading_image;?>" /></div>
<div id="saved-vqmod" style="position:fixed;width:100%;text-align:center;top:200px;display:none;"><img alt="Saved!!!" src="<?php echo $saved_image;?>" /></div>
<div id="vqgenerate" style="display:none;position:absolute;"><?php echo $text_generate_mods;?></div>
<script type="text/javascript">
<?php if (!$vqconfig['text_style']) { ?>
// Following line of code is minified Tabby 0.12
(function($){$.fn.tabby=function(options){var opts=$.extend({},$.fn.tabby.defaults,options);var pressed=$.fn.tabby.pressed;return this.each(function(){$this=$(this);var options=$.meta?$.extend({},opts,$this.data()):opts;$this.bind('keydown',function(e){var kc=$.fn.tabby.catch_kc(e);if(16==kc)pressed.shft=true;if(17==kc){pressed.ctrl=true;setTimeout("$.fn.tabby.pressed.ctrl = false;",1000)}if(18==kc){pressed.alt=true;setTimeout("$.fn.tabby.pressed.alt = false;",1000)}if(9==kc&&!pressed.ctrl&&!pressed.alt){e.preventDefault;pressed.last=kc;setTimeout("$.fn.tabby.pressed.last = null;",0);process_keypress($(e.target).get(0),pressed.shft,options);return false}}).bind('keyup',function(e){if(16==$.fn.tabby.catch_kc(e))pressed.shft=false}).bind('blur',function(e){if(9==pressed.last)$(e.target).one('focus',function(e){pressed.last=null}).get(0).focus()})})};$.fn.tabby.catch_kc=function(e){return e.keyCode?e.keyCode:e.charCode?e.charCode:e.which};$.fn.tabby.pressed={shft:false,ctrl:false,alt:false,last:null};function debug($obj){if(window.console&&window.console.log)window.console.log('textarea count: '+$obj.size())};function process_keypress(o,shft,options){var scrollTo=o.scrollTop;if(o.setSelectionRange)gecko_tab(o,shft,options);else if(document.selection)ie_tab(o,shft,options);o.scrollTop=scrollTo}$.fn.tabby.defaults={tabString:String.fromCharCode(9)};function gecko_tab(o,shft,options){var ss=o.selectionStart;var es=o.selectionEnd;if(ss==es){if(shft){if("\t"==o.value.substring(ss-options.tabString.length,ss)){o.value=o.value.substring(0,ss-options.tabString.length)+o.value.substring(ss);o.focus();o.setSelectionRange(ss-options.tabString.length,ss-options.tabString.length)}else if("\t"==o.value.substring(ss,ss+options.tabString.length)){o.value=o.value.substring(0,ss)+o.value.substring(ss+options.tabString.length);o.focus();o.setSelectionRange(ss,ss)}}else{o.value=o.value.substring(0,ss)+options.tabString+o.value.substring(ss);o.focus();o.setSelectionRange(ss+options.tabString.length,ss+options.tabString.length)}}else{var lines=o.value.split("\n");var indices=new Array();var sl=0;var el=0;var sel=false;for(var i in lines){el=sl+lines[i].length;indices.push({start:sl,end:el,selected:(sl<=ss&&el>ss)||(el>=es&&sl<es)||(sl>ss&&el<es)});sl=el+1}var modifier=0;for(var i in indices){if(indices[i].selected){var pos=indices[i].start+modifier;if(shft&&options.tabString==o.value.substring(pos,pos+options.tabString.length)){o.value=o.value.substring(0,pos)+o.value.substring(pos+options.tabString.length);modifier-=options.tabString.length}else if(!shft){o.value=o.value.substring(0,pos)+options.tabString+o.value.substring(pos);modifier+=options.tabString.length}}}o.focus();var ns=ss+((modifier>0)?options.tabString.length:(modifier<0)?-options.tabString.length:0);var ne=es+modifier;o.setSelectionRange(ns,ne)}}function ie_tab(o,shft,options){var range=document.selection.createRange();if(o==range.parentElement()){if(''==range.text){if(shft){var bookmark=range.getBookmark();range.moveStart('character',-options.tabString.length);if(options.tabString==range.text){range.text=''}else{range.moveToBookmark(bookmark);range.moveEnd('character',options.tabString.length);if(options.tabString==range.text)range.text=''}range.collapse(true);range.select()}else{range.text=options.tabString;range.collapse(false);range.select()}}else{var selection_text=range.text;var selection_len=selection_text.length;var selection_arr=selection_text.split("\r\n");var before_range=document.body.createTextRange();before_range.moveToElementText(o);before_range.setEndPoint("EndToStart",range);var before_text=before_range.text;var before_arr=before_text.split("\r\n");var before_len=before_text.length;var after_range=document.body.createTextRange();after_range.moveToElementText(o);after_range.setEndPoint("StartToEnd",range);var after_text=after_range.text;var end_range=document.body.createTextRange();end_range.moveToElementText(o);end_range.setEndPoint("StartToEnd",before_range);var end_text=end_range.text;var check_html=$(o).html();$("#r3").text(before_len+" + "+selection_len+" + "+after_text.length+" = "+check_html.length);if((before_len+end_text.length)<check_html.length){before_arr.push("");before_len+=2;if(shft&&options.tabString==selection_arr[0].substring(0,options.tabString.length))selection_arr[0]=selection_arr[0].substring(options.tabString.length);else if(!shft)selection_arr[0]=options.tabString+selection_arr[0]}else{if(shft&&options.tabString==before_arr[before_arr.length-1].substring(0,options.tabString.length))before_arr[before_arr.length-1]=before_arr[before_arr.length-1].substring(options.tabString.length);else if(!shft)before_arr[before_arr.length-1]=options.tabString+before_arr[before_arr.length-1]}for(var i=1;i<selection_arr.length;i++){if(shft&&options.tabString==selection_arr[i].substring(0,options.tabString.length))selection_arr[i]=selection_arr[i].substring(options.tabString.length);else if(!shft)selection_arr[i]=options.tabString+selection_arr[i]}if(1==before_arr.length&&0==before_len){if(shft&&options.tabString==selection_arr[0].substring(0,options.tabString.length))selection_arr[0]=selection_arr[0].substring(options.tabString.length);else if(!shft)selection_arr[0]=options.tabString+selection_arr[0]}if((before_len+selection_len+after_text.length)<check_html.length){selection_arr.push("");selection_len+=2}before_range.text=before_arr.join("\r\n");range.text=selection_arr.join("\r\n");var new_range=document.body.createTextRange();new_range.moveToElementText(o);if(0<before_len)new_range.setEndPoint("StartToEnd",before_range);else new_range.setEndPoint("StartToStart",before_range);new_range.setEndPoint("EndToEnd",range);new_range.select()}}}})(jQuery);
$(function() { $('textarea').tabby(); });
<?php } ?>

var idx = 0, idx2 = 0, ndx = 0, cache = {}, textHeight = '<?php echo $vqconfig['text_height'];?>px', changed = false;

function addFile(d, scroll) {
	if (idx2) idx++;
	var vqmversion = parseInt($('#vqmodver').val().split('.').join(''));
	var x = "\n<div class=\"file\">", arrow = scroll ? 'up' : 'down';
	x += "\n\t<fieldset id=\"filefieldset_" + idx + "\" class=\"fi\">";
	x += "\n\t\t<legend><?php echo $text_file_header;?></legend>";
	x += "\n\t\t<div class=\"vqtable\">";
	x += "\n\t\t\t<div class=\"vqrow\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_file_path;?></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><input id=\"path_" + idx + "\" name=\"path[" + idx + "]\" type=\"text\" value=\"" + (d ? d.path : '') + "\" size=\"" + (d && d.path != '' ? d.path.length : '1') + "\" class=\"vqm230\"" + (vqmversion < 230 ? " style=\"display:none;\"" : '') + " /><input id=\"file_" + idx + "\" name=\"file[" + idx + "]\" type=\"text\" value=\"" + (d ? d.file : '') + "\" style=\"width:96%;\" /></div>";
	x += "\n\t\t\t\t<div class=\"vqcell vqremove\"><?php echo $entry_remove;?> <input id=\"remove_" + idx + "\" name=\"remove_" + idx + "\" type=\"checkbox\" value=\"1\" class=\"vqremover\"></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t\t<div class=\"vqrow file_" + idx + " removeme\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_file_error;?></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><select id=\"error_" + idx + "\" name=\"error_" + idx + "\">";
	x += "\n\t\t\t\t\t<option value=\"abort\"" + ((d && d.error === 'abort') ? " selected=\"selected\"" : '') + "><?php echo $entry_abort;?></option>";
	x += "\n\t\t\t\t\t<option value=\"log\"" + ((!d || !d.error) ? " selected=\"selected\"" : '') + "><?php echo $entry_log;?></option>";
	x += "\n\t\t\t\t\t<option value=\"skip\"" + ((d && d.error === 'skip') ? " selected=\"selected\"" : '') + "><?php echo $entry_skip;?></option>";
	x += "\n\t\t\t\t</select></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t</div>";
	x += "\n\t\t<div class=\"arrow " + arrow + "\" title=\"" + (scroll ? '<?php echo $text_collapse;?>' : '<?php echo $text_expand;?>') + "<?php echo $text_this_file;?>\"></div>";
	x += "\n\t</fieldset>";
	x += "\n</div>";

	$('#vqmods').append(x);

	if (!d && scroll) {
		var height = $('.file:last').offset().top + $('body').scrollTop();
		$('html, body').animate({scrollTop: height}, 800);
	}
	if ($('.add_op').is(':hidden')) $('.add_op').fadeIn();
	if (scroll) {
		loadAutocomplete(idx, true);
		loadPathcomplete(idx);
	}

	idx2 = 0;
}

function addOperation(d, scroll) {
	if ($('#remove_' + idx).is(':checked')) {
		alert('Please clear the \'Remove on Generate\' checkbox for this file, if you wish to add a new operation to it.\n\nAlternatively, you can add a new file to edit.');
	} else {
		var scroller = scroll ? true : false;
		if (scroll === 0) {
			scroller = false;
			scroll = true;
		}
		var vqmversion = parseInt($('#vqmodver').val().split('.').join('')), arrow = scroll ? 'up' : 'down', hidden = scroll ? '' : " style=\"display:none;\"";
		var x = "\n\t<div class=\"operation\"" + hidden + ">";
		x += "\n\t\t<fieldset id=\"operationfieldset_" + idx + "_" + idx2 + "\" class=\"op\"";
		if (d && d.position == 'all') {
			alert('<?php echo $error_position_all;?>');
			x += " style=\"background-color:#FFDEDE;\"";
		}
		x += ">";
		x += "\n\t\t\t<legend>Operation to perform</legend>";
		x += "\n\t\t\t<div class=\"foundit collapseme\"" + hidden + "></div>";
		x += "\n\t\t\t<div class=\"vqtable\">";
		x += "\n\t\t\t\t<div class=\"vqrow\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_search;?><div style=\"float:right;margin-right:15px;\" class=\"hideonall\">";
		x += "<small><?php echo $entry_ignoreif_check;?></small><input id=\"ignore_" + idx + "_" + idx2 + "\" class=\"ignoreif\" type=\"checkbox\"" + ((d && d.ignoreif != '' && vqmversion >= 230) ? " checked=\"checked\"" : '') + " />";
		x += "<span class=\"vqregex\"><small><?php echo $entry_regex;?></small><input name=\"regex[" + idx + "][" + idx2 + "]\" type=\"checkbox\" value=\"true\" " + ((d && d.regex == 'true') ? " selected=\"selected\"" : '') + "/></span>";
		x += "<span class=\"vqtrim\"><small><?php echo $entry_trims;?></small><input name=\"trims[" + idx + "][" + idx2 + "]\" type=\"checkbox\" value=\"true\" " + ((d && d.trims == 'false') ? '' : "checked=\"checked\" ") + "/></span></div></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><input id=\"search_" + idx + "_" + idx2 + "\" name=\"search[" + idx + "][" + idx2 + "]\" type=\"text\" value=\"" + (d ? d.search : '') + "\" style=\"width:95%;\"></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell vqremove\"><?php echo $entry_remove;?> <input id=\"remove_" + idx + "_" + idx2 + "\" name=\"remove_" + idx + "_" + idx2 + "\" type=\"checkbox\" value=\"1\" class=\"vqremover\"></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t</div>";
		x += "\n\t\t\t<div class=\"vqtable collapseme\"" + hidden + ">";
		x += "\n\t\t\t\t<div class=\"vqrow\"" + ((d && d.ignoreif != '' && vqmversion >= 230) ? '' : ' style="display:none;"') + ">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_ignoreif;?><div style=\"float:right;margin-right:15px;\" class=\"hideonall vqregex\"><?php echo $entry_regex;?><input name=\"regif[" + idx + "][" + idx2 + "]\" type=\"checkbox\" value=\"true\" " + ((d && d.regif == 'true') ? " selected=\"selected\"" : '') + "/></div></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><textarea id=\"ignoreif_" + idx + "_" + idx2 + "\" name=\"ignoreif[" + idx + "][" + idx2 + "]\" rows=\"3\" style=\"width:95%;\">" + ((d && d.ignoreif) ? d.ignoreif : '') + "</textarea></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_position;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><select id=\"position_" + idx + "_" + idx2 + "\" name=\"position[" + idx + "][" + idx2 + "]\">";
		x += "\n\t\t\t\t\t\t<option value=\"replace\"" + ((!d || !d.position) ? " selected=\"selected\"" : '') + "><?php echo $entry_replace;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"ibefore\"" + ((d && d.position == 'ibefore') ? " selected=\"selected\"" : '') + " class=\"vqm240\"" + (vqmversion < 240 ? " style=\"display:none;\"" : '') + "><?php echo $entry_ibefore;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"before\"" + ((d && d.position == 'before') ? " selected=\"selected\"" : '') + "><?php echo $entry_before;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"iafter\"" + ((d && d.position == 'iafter') ? " selected=\"selected\"" : '') + " class=\"vqm240\"" + (vqmversion < 240 ? " style=\"display:none;\"" : '') + "><?php echo $entry_iafter;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"after\"" + ((d && d.position == 'after') ? " selected=\"selected\"" : '') + "><?php echo $entry_after;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"top\"" + ((d && d.position == 'top') ? " selected=\"selected\"" : '') + "><?php echo $entry_top;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"bottom\"" + ((d && d.position == 'bottom') ? " selected=\"selected\"" : '') + "><?php echo $entry_bottom;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"all\"" + ((d && d.position == 'all') ? " selected=\"selected\"" : '') + " style=\"background-color:#FFEBEB;\"><?php echo $entry_all;?></option>";
		x += "\n\t\t\t\t\t</select><?php echo $entry_position_help;?></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_offset;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><input id=\"offset_" + idx + "_" + idx2 + "\" name=\"offset[" + idx + "][" + idx2 + "]\" type=\"text\" value=\"" + (d ? d.offset : '') + "\" style=\"width:40px;\" class=\"numeric\"><?php echo $entry_offset_help;?></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow hideonall\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_index;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><input id=\"index_" + idx + "_" + idx2 + "\" name=\"index[" + idx + "][" + idx2 + "]\" type=\"text\" value=\"" + (d ? d.index : '') + "\" style=\"width:40px;\" class=\"numeric\"><?php echo $entry_index_help;?></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_error;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><select id=\"error_" + idx + "_" + idx2 + "\" name=\"error[" + idx + "][" + idx2 + "]\">";
		x += "\n\t\t\t\t\t\t<option value=\"abort\"" + ((!d || !d.error) ? " selected=\"selected\"" : '') + "><?php echo $entry_abort;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"log\"" + ((d && d.error == 'log') ? " selected=\"selected\"" : '') + "><?php echo $entry_log;?></option>";
		x += "\n\t\t\t\t\t\t<option value=\"skip\"" + ((d && d.error == 'skip') ? " selected=\"selected\"" : '') + "><?php echo $entry_skip;?></option>";
		x += "\n\t\t\t\t\t</select><?php echo $entry_error_help;?></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow vqtrim\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_trim;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><input name=\"trim[" + idx + "][" + idx2 + "]\" type=\"checkbox\" value=\"true\" " + ((d && d.trim == 'true') ? "checked=\"checked\" " : '') + "/>";
		x += "\n\t\t\t\t\t<?php echo $entry_trim_help;?></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t\t<div class=\"vqrow vqinfo\">";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><?php echo $entry_info;?></div>";
		x += "\n\t\t\t\t\t<div class=\"vqcell\"><input name=\"info[" + idx + "][" + idx2 + "]\" type=\"text\" value=\"" + ((d && typeof(d.info) != 'undefined' && d.info != '') ? d.info : '') + "\" style=\"width:95%;\" /></div>";
		x += "\n\t\t\t\t</div>";
		x += "\n\t\t\t</div>";
		x += "\n\t\t\t<div style=\"width:100%;" + (scroll ? '' : 'display:none;') + "\" class=\"collapseme\"><textarea id=\"add_" + idx + "_" + idx2 + "\" name=\"add[" + idx + "][" + idx2 + "]\" class=\"vqtext\" style=\"height:" + textHeight + ";\">" + (d ? d.add : '') + "</textarea></div>";
		x += "\n\t\t\t<div class=\"vqadd collapseme\"" + hidden + ">";
		x += "\n\t\t\t\t<?php echo $entry_add;?><select id=\"newop_" + idx + "_" + idx2 + "\" name=\"newop[" + idx + "][" + idx2 + "]\">";
		x += "\n\t\t\t\t\t<option value=\"0\" selected=\"selected\">0</option>";
		x += "\n\t\t\t\t\t<option value=\"1\">1</option>";
		x += "\n\t\t\t\t\t<option value=\"2\">2</option>";
		x += "\n\t\t\t\t\t<option value=\"3\">3</option>";
		x += "\n\t\t\t\t</select><?php echo $entry_after_this;?>";
		x += "\n\t\t\t</div>";
		x += "\n\t\t\t<div class=\"arrow " + arrow + "\" title=\"" + (scroll ? '<?php echo $text_collapse;?>' : '<?php echo $text_expand;?>') + "<?php echo $text_this_file;?>\"></div>";
		x += "\n\t\t</fieldset>";
		x += "\n\t</div>";

		$('.file:last').append(x);

		if (!d && scroller) {
			var height = $('.operation:last').offset().top + $('body').scrollTop();
			$('html, body').animate({scrollTop: height}, 800);
		}
		if (!$('input[name="show_trim"]').is(':checked')) $('.vqtrim', '.operation:last').fadeOut();
		if (!$('input[name="show_regex"]').is(':checked')) $('.vqregex', '.operation:last').fadeOut();
		if (!$('input[name="show_info"]').is(':checked')) $('.vqinfo', '.operation:last').fadeOut();
		if (scroll) {
			loadCodeArea(idx + '_' + idx2);
			if ($('.add_op').hasClass('ui-state-disabled')) $('.add_op').removeClass('ui-state-disabled');
		}

		idx2++;
	}
}

function addNewFile(d, scroll) {
	idx++;
	var x = "\n<div class=\"file\">", arrow = scroll ? 'up' : 'down';
	x += "\n\t<fieldset id=\"filefieldset_" + idx + "\" class=\"fi newfi\">";
	x += "\n\t\t<legend><?php echo $text_newfile_header;?></legend>";
	x += "\n\t\t<div class=\"vqtable\">";
	x += "\n\t\t\t<div class=\"vqrow\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_file_path;?><input id=\"mime_" + idx + "\" name=\"mime_" + idx + "\" type=\"hidden\" value=\"" + (d ? d.mime : '') + "\"></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><input id=\"file_" + idx + "\" name=\"file[" + idx + "]\" type=\"text\" value=\"" + (d ? d.file : '') + "\" style=\"width:95%;\"></div>";
	x += "\n\t\t\t\t<div class=\"vqcell vqremove\"><?php echo $entry_remove;?> <input id=\"remove_" + idx + "\" name=\"remove_" + idx + "\" type=\"checkbox\" value=\"1\" class=\"vqremover\"></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t\t<div class=\"vqrow operation file_" + idx + " removeme\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_newfile_chmod;?></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\">\n\t\t\t\t\t<table class=\"chmod\">";
	x += "\n\t\t\t\t\t\t<tr><td></td><td>Owner</td><td>Group</td><td>Other</td></tr>";
	x += "\n\t\t\t\t\t\t<tr class=\"read\"><td>Read</td>";
	x += "<td><input type=\"checkbox\" value=\"4\" class=\"owner\"></td>";
	x += "<td><input type=\"checkbox\" value=\"4\" class=\"group\"></td>";
	x += "<td><input type=\"checkbox\" value=\"4\" class=\"other\"></td>";
	x += "<td rowspan=\"4\">= <input id=\"chmod_" + idx + "\" name=\"chmod_" + idx + "\" type=\"text\" size=\"4\" value=\"" + (d ? d.chmod : '0644') + "\" /></td></tr>";
	x += "\n\t\t\t\t\t\t<tr class=\"write\"><td>Write</td>";
	x += "<td><input type=\"checkbox\" value=\"2\" class=\"owner\"></td>";
	x += "<td><input type=\"checkbox\" value=\"2\" class=\"group\"></td>";
	x += "<td><input type=\"checkbox\" value=\"2\" class=\"other\"></td></tr>";
	x += "\n\t\t\t\t\t\t<tr class=\"exec\"><td>Execute</td>";
	x += "<td><input type=\"checkbox\" value=\"1\" class=\"owner\"></td>";
	x += "<td><input type=\"checkbox\" value=\"1\" class=\"group\"></td>";
	x += "<td><input type=\"checkbox\" value=\"1\" class=\"other\"></td></tr>";
	x += "\n\t\t\t\t\t</table></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><a id=\"upload_" + idx + "\" class=\"button\" style=\"background-color:#420080;\"><?php echo $button_upload;?></a></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t\t<div class=\"vqrow operation file_" + idx + " removeme\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_newfile_exist;?></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><select id=\"exist_" + idx + "\" name=\"exist_" + idx + "\">";
	x += "\n\t\t\t\t\t<option value=\"skip\"" + ((d && d.exist == 'skip') ? " selected=\"selected\"" : '') + "><?php echo $entry_newfile_skip;?></option>";
	x += "\n\t\t\t\t\t<option value=\"update\"" + ((!d || !d.exist) ? " selected=\"selected\"" : '') + "><?php echo $entry_newfile_update;?></option>";
	x += "\n\t\t\t\t\t<option value=\"delete\"" + ((d && d.exist == 'delete') ? " selected=\"selected\"" : '') + "><?php echo $entry_newfile_delete;?></option>";
	x += "\n\t\t\t\t</select></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t\t<div class=\"vqrow operation file_" + idx + " removeme\">";
	x += "\n\t\t\t\t<div class=\"vqcell\"><?php echo $entry_newfile_error;?></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"><select id=\"error_" + idx + "\" name=\"error_" + idx + "\">";
	x += "\n\t\t\t\t\t<option value=\"abort\"" + ((d && d.error == 'abort') ? " selected=\"selected\"" : '') + "><?php echo $entry_abort;?></option>";
	x += "\n\t\t\t\t\t<option value=\"log\"" + ((!d || !d.error) ? " selected=\"selected\"" : '') + "><?php echo $entry_log;?></option>";
	x += "\n\t\t\t\t\t<option value=\"skip\"" + ((d && d.error == 'skip') ? " selected=\"selected\"" : '') + "><?php echo $entry_skip;?></option>";
	x += "\n\t\t\t\t</select></div>";
	x += "\n\t\t\t\t<div class=\"vqcell\"></div>";
	x += "\n\t\t\t</div>";
	x += "\n\t\t</div>";
	x += "\n\t\t<div style=\"width:100%;\" class=\"operation removeme\"><textarea id=\"add_" + idx + "\" name=\"add[" + idx + "]\" class=\"vqtext\" style=\"height:" + textHeight + ";\">" + (d ? d.add : '') + "</textarea></div>";
	x += "\n\t\t<div class=\"arrow " + arrow + "\" title=\"" + (scroll ? '<?php echo $text_collapse;?>' : '<?php echo $text_expand;?>') + "<?php echo $text_this_file;?>\"></div>";
	x += "\n\t</fieldset>";
	x += "\n</div>";

	$('#vqmods').append(x);

	if (!d && scroll) {
		var height = $('.file:last').offset().top + $('body').scrollTop();
		$('html, body').animate({scrollTop: height}, 800);
	}
	if ($('.add_op').is(':visible')) $('.add_op').fadeOut();
	$('#chmod_' + idx).blur();

	if (scroll) {
		loadAutocomplete(idx, false);
		loadCodeArea(idx);
	}
	if (d) loadFile(idx, d.mime);

	var button = '#upload_' + idx;
	new AjaxUpload(button, {
		action: '<?php echo $upload; ?>',
		name: 'file',
		autoSubmit: true,
		responseType: 'json',
		onSubmit: function(file, extension) {
			$(button).after('<img src="view/image/loading.gif" class="loading" style="padding-left: 5px;" />');
			$(button).attr('disabled', true);
		},
		onComplete: function(file, json) {
			$(button).attr('disabled', false);
			var ndx = $(button).attr('id').split('_');
			ndx = ndx[1];
			$('.error').remove();

			if (json.success) {
				$('#mime_' + ndx).val(json.mime);
				if (json.mime == 'text') {
					var data = decode64(json.data);
					if (data) json.data = data;
				}
				$('#add_' + ndx).val(json.data);
				loadFile(ndx, json.mime);

				var filename = $('#file_' + ndx).val().replace(/\\/g,'/').replace(/\/[^\/]*$/, '') + '/' + json.file;
				$('#file_' + ndx).val(filename);
				alert(json.success);
			} else if (json['error']) {
				$('#upload_' + ndx).after('<span class="error">' + json['error'] + '</span>');
			}
			$('.loading').remove();
		}
	});

	idx2 = 0;
}

var animate = true;
$(".arrow").live('click', function() {
	var title = ($(this).hasClass('up')) ? '<?php echo $text_collapse;?>' : '<?php echo $text_expand;?>',
		act = ($(this).hasClass('up')) ? '.up' : '.down',
		dad = $(this).parent();
	if (dad.is(".ma")) {
		title += '<?php echo $text_all_files;?>';
		animate = false;
		$(".fi").find(".arrow" + act).click();
		animate = true;
	} else if (dad.is(".fi")) {
		title += '<?php echo $text_this_file;?>';
		if (animate) {
			$(this).closest('.file').find('.operation').slideToggle();
		} else {
			if (act == '.up') $(this).closest('.file').find('.operation').hide();
			else $(this).closest('.file').find('.operation').show();
		}
		var file = $(this).parent().attr('id').replace('filefieldset_', '');
		if (!$('#file_' + file).hasClass('ui-autocomplete-input')) loadAutocomplete(file, !$(this).parent().hasClass('newfi'));
		if (!$('#path_' + file).hasClass('ui-autocomplete-input')) loadPathcomplete(file);
		if (dad.parent().is($('.file:last'))) {
			if (act == '.up' && !$('.add_op').hasClass('ui-state-disabled')) $('.add_op').addClass('ui-state-disabled');
			else if (act == '.down' && $('.add_op').hasClass('ui-state-disabled')) $('.add_op').removeClass('ui-state-disabled');
		}
	} else {
		title += '<?php echo $text_this_operation;?>';
		$(this).parent().find('.collapseme').slideToggle();
		var file = $(this).parent().attr('id').replace('operationfieldset_', '');
		if ($(this).parent().find('.foundit').html() == '') $('#search_' + file).keyup();
		if ($(this).parent().find('.CodeMirror').length <= 0) loadCodeArea(file);
	}
	$(this).toggleClass('up').toggleClass('down');
	$(this).attr('title', title);
});

$(".generate").live('click', function() {
	var val = 1;
	if ($(this).hasClass('newop')) { // When "adding a new operation after this one"
		$('input[name="generatexml"]').val('');
	} else {
		if ($(this).hasClass('continue')) val++;
		$('input[name="generatexml"]').val(val);
	}
	for (var id in codeMirrors) {
		codeMirrors[id].save();
	}
	if (val === 1) {
		setTimeout("$('#generator').submit();", 100);
	} else {
		$.ajax({
			url : $('#generator').attr('action'),
			dataType: 'html',
			data: $('#generator').find('input:not("[type=checkbox]"), input:checked, textarea, select'),
			type: 'POST',
			success: function(data) {
				if (data === 'SAVED') $('#saved-vqmod').fadeIn('slow').delay(1000).fadeOut('slow');
				else alert(data);
			}
		});
	}
});

$('input[id^="file_"]').live('keyup', function() {
	var vqmversion = parseInt($('#vqmodver').val().split('.').join('')), value = $(this).val();
	if (value.indexOf(' ') != -1) value = value.replace(' ', '');
	if (value.indexOf(',') != -1 && vqmversion < 230) value = value.replace(',', '');
	var file = $(this).attr('id').replace('file_', '');
	if (value.indexOf('*') >= 0) {
		if ($(".file_" + file).is(':visible')) $(".file_" + file).fadeOut();
	} else {
		if ($(".file_" + file).is(':hidden') && $('#path_' + file).val().indexOf('*') == -1) $(".file_" + file).fadeIn();
	}
	$(this).val(value);
}).live('click', function() {
	var file = $(this).attr('id').replace('file_', '');
	if (!$(this).hasClass('ui-autocomplete-input')) loadAutocomplete(file, !$(this).parent().hasClass('newfi'));
	var vqmversion = parseInt($('#vqmodver').val().split('.').join(''));
	var path = $('#path_' + file).val(), value = $(this).val();
	if (vqmversion >= 230 && value.indexOf(path) === 0) {
		$(this).val(value.replace(path, ''));
	} else if (vqmversion < 230 && path != '' && value.indexOf(path) !== 0) {
		$(this).val(path + value);
		$('#path_' + file).val('');
	}
	$(this).autocomplete('search');
});
$('input[id^="path_"]').live('keyup', function() {
	var value = $(this).val();
	if (value.indexOf(' ') >= 0) value = value.replace(' ', '');
	if (value.indexOf(',') >= 0) value = value.replace(',', '');
	var file = $(this).attr('id').replace('path_', '');
	if (value.indexOf('*') >= 0) {
		if ($('.file_' + file).is(':visible')) $('.file_' + file).fadeOut();
	} else {
		if ($('.file_' + file).is(':hidden') && $('#file_' + file).val().indexOf('*') == -1) $('.file_' + file).fadeIn();
	}
	$(this).val(value);
}).live('click', function() {
	var file = $(this).attr('id').replace('path_', '');
	if (!$(this).hasClass('ui-autocomplete-input')) loadPathcomplete(file);
	$(this).autocomplete('search');
});
$('input[id^="offset_"]').live('click', function() {
	if ($(this).val().indexOf('.') != -1) $(this).val($(this).val().replace('.', ''));
	if ($(this).val().indexOf(',') != -1) $(this).val($(this).val().replace(',', ''));
	$(this).autocomplete('search');
});

$('.ignoreif').live('change', function() {
	var vqmversion = parseInt($('#vqmodver').val().split('.').join(''));
	var ignore = $('#' + $(this).attr('id').replace('ignore_', 'ignoreif_'));
	if ($(this).is(':checked')) {
		if (vqmversion < 230) {
			alert('<?php echo $entry_ignoreif_needs;?>' + $('#vqmodver').val().replace('v', ''));
			$(this).click();
		} else if (ignore.is(':hidden')) {
			ignore.parent().parent().slideDown();
		}
	} else {
		if (ignore.is(':visible')) ignore.parent().parent().slideUp();
	}
});

$('input.vqremover').live('change', function() {
	var iidx = $(this).attr('id').split('_');
	var iidx2 = (typeof (iidx[2]) != 'undefined') ? iidx[2] : false;
	iidx = iidx[1];
	var hide = $('.removeme, .operation', $(this).closest('.file'));
	if (iidx2) {
		hide = $(this).closest('.op').find('.collapseme, .arrow');
		if ($('input[id^="remove_' + iidx + '_"]').not(':checked').length === 0) {
			$('input[id^="remove_' + iidx + '_"]').attr('disabled','disabled');
			$('#remove_' + iidx).click();
		}
	} else {
		if ($(this).is(':checked')) {
			$('input[id^="remove_' + iidx + '_"]').attr('checked','checked').attr('disabled','disabled');
		} else {
			$('input[id^="remove_' + iidx + '_"]').removeAttr('disabled').click();
		}
	}
	if ($(this).is(':checked') && hide.is(':visible')) {
		hide.fadeOut();
		$(this).closest('fieldset').animate({'background-color': '#FFDEDE', 'border-color': '#800000'}, 500);
	} else if (!$(this).is(':checked') && hide.is(':hidden')) {
		hide.fadeIn();
		$(this).closest('fieldset').css({'background-color': '', 'border-color': ''});
	}
});

$('input.numeric').live('keyup', function() {
	var value = $(this).val().replace(/[A-Za-z $-$=]/g, "");
	$(this).val(value);
});

$('select[id^="position_"]').live('change', function() {
	var disable = $('#' + $(this).attr('id').replace('position', 'search')),
		offset = $('#' + $(this).attr('id').replace('position', 'offset')).closest('.vqrow'),
		parent = $(this).closest('.op'),
		sel = $('option:selected', $(this)).val();
	if (!$('#vqmodver-required').is(':checked') && (sel == 'ibefore' || sel == 'iafter')) $('#vqmodver-required').click();
	if (sel == 'top' || sel == 'bottom' || sel == 'all') {
		var oldval = disable.val();
		if (oldval !== '<?php echo $entry_top;?>' && oldval !== '<?php echo $entry_bottom;?>' && oldval !== '<?php echo $entry_all;?>') {
			disable.data('oldval', oldval);
		}
		value = (sel == 'top') ? '<?php echo $entry_top;?>' : ((sel == 'bottom') ? '<?php echo $entry_bottom;?>' : '<?php echo $entry_all;?>');
		disable.val(value);
		disable.attr('readonly', 'readonly').css('color', '#AAA');
		if ($('.hideonall:first', parent).is(':visible')) $('.hideonall', parent).fadeOut();
		if (sel == 'all') {
			if (offset.is(':visible')) offset.fadeOut();
		} else {
			if (offset.is(':hidden')) offset.fadeIn();
		}
	} else if (disable.is('[readonly]')) {
		disable.val(disable.data('oldval'));
		disable.removeAttr('readonly').css('color', '');
		if ($('.hideonall:first', parent).is(':hidden')) $('.hideonall', parent).fadeIn();
		if (offset.is(':hidden')) offset.fadeIn();
	}
});

$('input', '.chmod').live('change', function() {
	var u = {'owner': 0, 'group': 0, 'other': 0}, table = $(this).closest('table');
	if ($(this).is(':checkbox')) {
		$('input:checkbox', table).each(function() {
			if ($(this).is(':checked')) {
				if ($(this).hasClass('owner')) u.owner += parseInt($(this).val());
				else if ($(this).hasClass('group')) u.group += parseInt($(this).val());
				else if ($(this).hasClass('other')) u.other += parseInt($(this).val());
			}
		});
		$('input[name^="chmod_"]', table).val('0' + u.owner + u.group + u.other);
	} else {
		$('input:checkbox', table).attr('checked', false);
		var chmod = $(this).val();
		chmod = (chmod.length == 4) ? chmod.substr(1,3).split('') : chmod.split('');
		u.owner = parseInt(chmod[0]), u.group = parseInt(chmod[1]), u.other = parseInt(chmod[2]);
		$('tr[class]', table).each(function() {
			var perm = $(this).attr('class');
			$('input:checkbox', $(this)).each(function() {
				var user = $(this).attr('class');
				if (perm === 'read' && u[user] >= 4) $(this).attr('checked', true);
				else if (perm === 'write' && (u[user] == 2 || u[user] == 3 || u[user] >= 6)) $(this).attr('checked', true);
				else if (perm === 'exec' && (u[user]%2 != 0)) $(this).attr('checked', true);
			});
		});
	}
});
$('input[type="text"]', '.chmod').live('keyup', function() {
	var value = $(this).val().replace(/[A-Za-z89$-]/g, "");
	if (value.length > 4) value = value.substr(0, 4);
	$(this).val(value);
}).live('blur', function() {
	var value = $(this).val();
	for (l = 4; l > value.length; l--) value = '0' + value;
	$(this).val(value);
	$(this).change();
});

$(document).ready(function() {
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

	var interval;
	$("#autosave").focus(function() {
		$(this).val('');
		clearInterval(interval);
	}).blur(function() {
		if ($(this).val()) {
			var seconds = parseFloat($(this).val());
			$(this).data('oldval', seconds);
			seconds *= 60;
			var rr = '|';
			interval = setInterval(function () {
				if (seconds <= 0) {
					seconds = parseFloat($("#autosave").data('oldval')) * 60;
					$('.continue:first').click();
				}
				$("#autosave").val(Math.round(seconds) + ' <?php echo $text_seconds_togo;?>  ' + rr + ' ' + rr + ' ' + rr);
				rr = (rr == '|') ? '/' : (rr == '/' ? '-' : (rr == '-' ? '\\' : '|'));
				seconds -= 0.2;
			}, 200);
		} else {
			clearInterval(interval);
			$(this).val('<?php echo $text_autosave_time;?>');
		}
	});

	$(".add_fi").click(function() {
		addFile(false, true);
		addOperation(false, 0);
	});
	$(".add_new").click(function() {
		addNewFile(false, true);
	});

	$(".add_op").click(function() {
		if (!$(this).hasClass('ui-state-disabled')) {
			if ($('.operation:last').find('input[id^="remove_"]').is(':checked')) {
				alert('<?php echo $error_add_operation;?>');
			} else {
				addOperation(false, true);
			}
		}
	});

	$("#generatehtml, #generatexml").click(function() {
		var value = ($('input[name="' + $(this).attr('id') + '"]').val() == '1') ? 0 : 1;
		var back = (value) ? 'green' : '#CCC', color = (value) ? '#FFF' : '#999';
		$('input[name="' + $(this).attr('id') + '"]').val(value);
		$(this).stop(true,true).animate({'background-color': back, 'color': color}, 800);
	});
	$("#generatehtml, #generatexml").click();

	$('#vqmodver').blur(function() {
		var vqmversion = parseInt($(this).val().split('.').join('')), required = $('#vqmver-required:visible');
		if (vqmversion >= 230) {
			$('textarea[id^="ignoreif_"]').each(function() {
				var ignore = $('#' + $(this).attr('id').replace('if', ''));
				if ($(this).val() != '' && !ignore.is(':checked')) ignore.click(); // Show IgnoreIf fields
			});
			$('input[id^="path_"]:hidden').fadeIn();
			if (vqmversion >= 240) {
				$('#vqmver-required:hidden').fadeIn();
				$('.vqm240').show();
			} else {
				if (required.length >= 1) {
					if (required.is(':checked')) required.click();
					required.fadeOut();
				}
				$('.vqm240').hide();
			}
		} else {
			$('input[id^="path_"]:visible').fadeOut();
			if (required.length >= 1) {
				if (required.is(':checked')) required.click();
				required.fadeOut();
			}
			$('.ignoreif:checked').click(); // Hide IgnoreIf fields
			$('.vqm240').hide();
		}
	});

<?php if (isset($vqmod_info->file)) { ?>
	<?php foreach ($vqmod_info->file as $file) { ?>
	var data = {
		'path': '<?php if (isset($file['path'])) echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes($file['path']), ENT_QUOTES, 'UTF-8')); ?>',
		'file': '<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes($file['name']), ENT_QUOTES, 'UTF-8')); ?>',
		'error': '<?php echo (isset($file['error']) && $file['error'] && $file['error'] != 'log') ? $file['error'] : ''; ?>'
	};
	addFile(data, false);
		<?php foreach ($file->operation as $do) { ?>
		var data = {
			'search': '<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes($do->search), ENT_QUOTES, 'UTF-8')); ?>',
			'ignoreif': "<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes($do->ignoreif), ENT_QUOTES, 'UTF-8')); ?>",
			'position': '<?php echo ($do->search['position'] && $do->search['position'] != 'replace') ? $do->search['position'] : ''; ?>',
			'offset': '<?php if (isset($do->search['offset'])) echo $do->search['offset']; ?>',
			'index': '<?php if (isset($do->search['index'])) echo $do->search['index']; ?>',
			'error': '<?php if (isset($do['error'])) echo $do['error'];?>',
			'info': '<?php if (isset($do['info'])) echo htmlentities(addslashes($do['info']), ENT_QUOTES, 'UTF-8');?>',
			'regex': '<?php if (isset($do->search['regex'])) echo $do->search['regex'];?>',
			'trims': '<?php if (isset($do->search['trim'])) echo $do->search['trim'];?>',
			'add': "<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes((string)$do->add), ENT_QUOTES, 'UTF-8'));?>",
			'trim': '<?php if (isset($do->add['trim'])) echo $do->add['trim'];?>'
		};
		addOperation(data, false);
		<?php } ?>
	<?php } ?>
<?php } else { ?>
	addFile(false, false);
	addOperation(false, 0);
<?php } ?>
<?php if (isset($vqmod_info->newfile)) { ?>
	<?php foreach ($vqmod_info->newfile as $file) { ?>
	var data = {
		'file': '<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes($file['name']), ENT_QUOTES, 'UTF-8')); ?>',
		'error': '<?php echo (isset($file['error']) && $file['error'] && $file['error'] != 'log') ? $file['error'] : ''; ?>',
		'exist': '<?php echo (isset($file['exist']) && $file['exist'] && $file['exist'] != 'update') ? $file['exist'] : ''; ?>',
		'mime': '<?php if (isset($file['mime'])) echo $file['mime']; ?>',
		'chmod': '<?php if (isset($file['chmod'])) echo $file['chmod']; ?>',
		'add': "<?php echo preg_replace("/\r?\n/", "\\n", htmlentities(addslashes((string)$file->add), ENT_QUOTES, 'UTF-8'));?>"
	};
	addNewFile(data, false);
	<?php } ?>
<?php } ?>

	$('#vqmodver').blur();

	$('input[id^="file_"]').keyup();

	$('select[id^="position_"]').change();

	$('input[type="text"]', '.chmod').blur();

	$('#vqloading').fadeOut('slow');
<?php if ($vqconfig['text_style']) { ?>
	var configCodeMirror = CodeMirror.fromTextArea(document.getElementById('manual_css'), {
		height: "120px",
		mode: 'css',
		matchBrackets: true,
		indentUnit: 2,
		indentWithTabs: true,
		lineWrapping: true,
		enterMode: "keep"
	});
<?php } ?>
	$('.vqmod-config').click(function() {
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
					$('#set-vqmod').show();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-vqmod').button('disable');
				}
			},{
				id: 'button-set-editor',
				text: '<?php echo $button_set_editor;?>',
				click: function() {
					var config = $('#vqmod-config').parent();
					config.find('table:visible').hide();
					$('#set-editor').show();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-editor').button('disable');
				}
			},{
				id: 'button-set-manual',
				text: '<?php echo $button_set_manual;?>',
				click: function() {
					var config = $('#vqmod-config').parent();
					config.find('table:visible').hide();
					$('#set-manual').show();
					configCodeMirror.refresh();
					config.find('.ui-button').button('enable').removeClass('ui-state-focus ui-state-hover');
					$('#button-set-manual').button('disable');
				}
			},{
				text: '<?php echo $button_save;?>',
				click: function() {
					configCodeMirror.save();
					$.ajax({
						url : '<?php echo $vqmod_config;?>',
						type: 'POST',
						data: $('input:not("[type=checkbox]"), input:checked, textarea', '#vqmod-config'),
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
					if (!$('input[name="show_trim"]').is(':checked')) $('.vqtrim').fadeOut();
					if (!$('input[name="show_regex"]').is(':checked')) $('.vqregex').fadeOut();
					if (!$('input[name="show_info"]').is(':checked')) $('.vqinfo').fadeOut();
					if (!$('input[name="generate_html"]').is(':checked')) $('[name="manual_css"]').parent().fadeOut();
					$(this).dialog('close');
				}
			}],
			open: function() {
				<?php if ($vqconfig['text_style']) { ?>configCodeMirror.refresh();<?php } ?>
				$('#button-set-vqmod').button('disable');
			}
		});
	});
	var t = {};
	$('.vqdir').keyup(function() {
		var vqd = $(this);
		var value = $.trim(vqd.val());
		if (t) clearTimeout(t);
		var checkdir = function() {
			$.ajax({
				url : '<?php echo $vqmod_check_dir;?>' + value,
				dataType: 'json',
				success: function(exists) {
					var color = (exists == 'exists') ? '#ccffc4' : '#ffbebe';
					vqd.animate({'background-color': color}, 500);
				}
			});
		};
		t = setTimeout(checkdir, 800);
	}).blur(function() {
		var vqd = $(this);
		var value = $.trim(vqd.val());
		if (value.indexOf('../vqmod/') != -1) value = value.replace('../vqmod', '');
		value = value.replace('/', '') + '/';

		vqd.val(value);
		$(this).keyup();
	});
	$('.vqdir').blur();
<?php if ($vqmod_search_delay) { ?>
	$('input[id^="search_"]').live('keyup', function() {
		var sId = $(this).attr('id').split('_');
		var file = $('#path_' + sId[1]).val() + $('#file_' + sId[1]).val(),
			search = $(this),
			name = 'search' + sId[1] + '_' + sId[2],
			regex = ($('#regex_' + sId[1] + '_' + sId[2]).is(':checked')) ? '&regex=1' : '';
		if (search.val() != '' && file != '') {
			var foundit = search.parents('fieldset').find('.foundit');
			foundit.html('');
			if (t[name]) clearTimeout(t[name]);
			var checksearch = function() {
				$.ajax({
					url : '<?php echo $vqmod_check_search;?>' + regex,
					type: 'POST',
					dataType: 'json',
					data: {'search': search.val(), 'file': file},
					success: function(found) {
						if (!found) {
							foundit.html('<?php echo $text_search_not_found;?>');
						} else {
							var times = '';
							for (var i in found) {
								var file = (found[i] !== 'no file') ? '<?php echo $text_search_found;?>' : '<?php echo $text_search_not_found;?>';
								if (times !== '') times += '<br/>';
								times += '<b>' + i + '</b>: ' + file.replace('%s', found[i]);
							}
							if (times === '') return;
							foundit.html(times);
						}
						if (foundit.is(':hidden') && foundit.parent().find('.arrow.up').length >= 1) foundit.fadeIn('slow');
					}
				});
			};
			t[name] = setTimeout(checksearch, <?php echo $vqmod_search_delay;?>);
		}
	});
<?php } ?>
	$('.vqdir').blur();
	$('input[name="show_trim"]').change(function() {
		if ($(this).is(':checked')) {
			$('.vqtrim').fadeIn();
		} else {
			$('.vqtrim').fadeOut();
		}
	});
	$('input[name="show_regex"]').change(function() {
		if ($(this).is(':checked')) {
			$('.vqregex').fadeIn();
		} else {
			$('.vqregex').fadeOut();
		}
	});
	$('input[name="show_info"]').change(function() {
		if ($(this).is(':checked')) {
			$('.vqinfo').fadeIn();
		} else {
			$('.vqinfo').fadeOut();
		}
	});
	$('input[name="generate_html"]').change(function() {
		if ($(this).is(':checked')) {
			$('[name="manual_css"]').parent().fadeIn();
		} else {
			$('[name="manual_css"]').parent().fadeOut();
		}
		$("#generatehtml").click();
	});

	$('input[name="text_height"]').keyup(function() {
		var value = $(this).val().replace(/[A-Za-z$-]/g, "");
		$(this).val(value);
 		textHeight = value + 'px';
<?php if ($vqconfig['text_style']) { ?>
		$('.CodeMirror').animate({height: textHeight}, 800);
<?php } else { ?>
		$('.vqtext').animate({height: textHeight}, 800);
<?php } ?>
	});
	if (!$('input[name="show_trim"]').is(':checked')) $('.vqtrim').fadeOut();
	if (!$('input[name="show_regex"]').is(':checked')) $('.vqregex').fadeOut();
	if (!$('input[name="show_info"]').is(':checked')) $('.vqinfo').fadeOut();
	if (!$('input[name="generate_html"]').is(':checked')) $('[name="manual_css"]').parent().fadeOut();

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

function loadAutocomplete(id, files) {
	files = files ? '' : '&files=0';
	$('#file_' + id).autocomplete({
		delay: 500,
		minLength: 0,
		source: function(request, response) {
			var term = $('#path_' + id).val() + request.term;
			if (term in cache) {
				response(cache[term]);
				return;
			}
			$.ajax({
				url: '<?php echo $autocomplete; ?>&path=' + $('#path_' + id).val() + '&dir=' + encodeURIComponent(request.term) + files,
				dataType: 'json',
				success: function(json) {
					cache[term] = json;
					response(json);
				}
			});
		},
		select: function(event, ui) {
			if (ui.item.value.indexOf('*') != -1) {
				var file = $(this).attr('id');
				if ($('.file_' + file).is(':visible')) $('.file_' + file).fadeOut();
			}
		}
	}).keyup(function() {
		var dir = $(this).val().toLowerCase().split('.'), ext = '';
		if (dir.length > 1) {
			ext = dir[dir.length-1];
		}
		if (ext.length === 3 && ext !== 'php' && ext !== 'tpl') $(this).css('background-color', '#ffbebe');
		else $(this).css('background-color', '');
	}).blur(function() {
		var val = $(this).val();
		if (val && (val.indexOf('/') === 0 || val.indexOf('\\') === 0)) val = val.substr(1);
	});
}
function loadPathcomplete(id) {
	$('#path_' + id).autocomplete({
		delay: 500,
		minLength: 0,
		source: function(request, response) {
			var term = request.term + '-p';
			if (term in cache) {
				response(cache[term]);
				return;
			}
			$.ajax({
				url: '<?php echo $autocomplete; ?>&files=0&dir=' +  encodeURIComponent(request.term),
				dataType: 'json',
				success: function(json) {
					cache[term + '-p'] = json;
					if (json.length) {
						var width = 1, path = $('#path_' + id), file = $('#file_' + id);
						for (var j in json) {
							if (json[j].length > width) width = json[j].length;
						}
						path.attr('size', width);
						width = file.parent().outerWidth(false) - path.outerWidth(true) - 25;
						file.css('width', width);
					}
					response(json);
				}
			});
		},
		select: function(event, ui) {
			ui.item.value;
			var file = $(this).attr('id').replace('path_', '');
			if (ui.item.value.indexOf('*') != -1) {
				if ($('.file_' + file).is(':visible')) $('.file_' + file).fadeOut();
			}
		}
	}).blur(function() {
		var val = $(this).val();
		if (val) {
			if (val.indexOf('/') === 0 || val.indexOf('\\') === 0) val = val.substr(1);
			if (val.substr(val.length -1) === '\\') val = val.substr(0, val.length -1);
			if (val.substr(val.length -1) !== '/') val += '/';
		}
	});
}

var codeMirrors = {};
function loadCodeArea(id) {
<?php if ($vqconfig['text_style']) { ?>
	var textWidth = $('#add_' + id).outerWidth() + 'px';
	codeMirrors[id] = CodeMirror.fromTextArea(document.getElementById('add_' + id), {
		mode: 'text/x-php',
		matchBrackets: true,
		indentUnit: 2,
		indentWithTabs: true,
		lineWrapping: true,
		enterMode: "keep"
	});
	codeMirrors[id].setSize(textWidth, textHeight);
<?php } else { ?>
	$('#add_' + id).tabby();
<?php } ?>
}

function loadFile(id, mime) {
	if (!mime || mime == 'text') {
		$('#add_' + id).attr('readonly', false).css('background-color', '');
	} else {
		$('#add_' + id).attr('readonly', 'readonly').css('background-color', '#f0e0ff');
	}
	var image_el = $('#add_' + id),
		data = $('#add_' + id).val();
<?php if ($vqconfig['text_style']) { ?>
	codeMirrors[id].setValue(data);
	if (!mime || mime == 'text') {
		codeMirrors[id].setOption('readOnly', false);
		$('.CodeMirror', '#filefieldset_' + id).css('background-color', '');
	} else {
		codeMirrors[id].setOption('readOnly', true);
		$('#filefieldset_' + id).find('.CodeMirror').css('background-color', '#f0e0ff');
	}
	image_el = $('.CodeMirror', '#filefieldset_' + id);
<?php } ?>
	if (mime.indexOf('image') != -1) {
		image_el.mouseenter(function() {
		$('#image-preview').attr('src', 'data:' + mime + ';base64,' + data);
		if ($('#image-preview').is(':hidden')) $('#image-preview').fadeIn();
			$(this).mousemove(function(e) {
				$('#image-preview').css('top', e.clientY - ($('#image-preview').height()/2)).css('left', e.clientX + 50);
			});
		}).mouseleave(function() {
			$(this).unbind('mousemove');
			$('#image-preview').attr('src', '');
			if ($('#image-preview').is(':visible')) $('#image-preview').fadeOut();
		});
	} else {
		image_el.unbind('mouseenter').unbind('mouseleave');
	}
}

var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
function decode64(input) {
	var output = "";
	var chr1, chr2, chr3 = "";
	var enc1, enc2, enc3, enc4 = "";
	var i = 0;

	// remove all characters that are not A-Z, a-z, 0-9, +, /, or =
	var base64test = /[^A-Za-z0-9\+\/\=]/g;
	if (base64test.exec(input)) {
		var r = confirm("There were invalid base64 characters in the input text!\nContinue decoding?");
		if (r == false) return false;
	}
	input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

	do {
		enc1 = keyStr.indexOf(input.charAt(i++));
		enc2 = keyStr.indexOf(input.charAt(i++));
		enc3 = keyStr.indexOf(input.charAt(i++));
		enc4 = keyStr.indexOf(input.charAt(i++));

		chr1 = (enc1 << 2) | (enc2 >> 4);
		chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
		chr3 = ((enc3 & 3) << 6) | enc4;

		output = output + String.fromCharCode(chr1);
		if (enc3 != 64) output = output + String.fromCharCode(chr2);
		if (enc4 != 64) output = output + String.fromCharCode(chr3);

		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";
	} while (i < input.length);
	return unescape(output);
}
$(window).keypress(function(event) {
    if (!(event.which == 115 && event.ctrlKey) && !(event.which == 19)) return true;
	$('.continue:first').click();
    event.preventDefault();
    return false;
});
</script>
<?php echo $footer; ?>