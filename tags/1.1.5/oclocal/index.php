<html>
<head>
<title>OpenCart Local by UK Site Builder</title>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<style type="text/css">
body{
	text-align:center;
}
body,td,th {
	font-family: Verdana, Geneva, sans-serif;
	color: #666666;
}
#logo{
	width:560px;
	margin:0 auto;
}
#content{
	width:800px;
	margin:0 auto;
	text-align:left;
}
h1, th{
	text-shadow:3px 3px 3px #999999;
}
a:link {
	text-decoration: none;
	color: #6666CC;
}
a:visited {
	text-decoration: none;
	color: #6666CC;
}
a:hover {
	text-decoration: none;
	color: #F33;
}
a:active {
	text-decoration: none;
	color: #6666CC;
}
th, td{
	padding: 5px;
	width:25%;
	text-align:left;
}
th{
	background:#666666;
	color:#ffffff;
}
#table{
	width:800px;
	padding:10px;
	box-shadow: 10px 10px 5px #999999;
	-moz-box-shadow: 10px 10px 5px #999999;
	-webkit-box-shadow: 10px 10px 5px #999999;
	border:1px solid #999999;
	-webkit-border-radius:7px;
	-moz-border-radius:7px;
	-khtml-border-radius:7px;
	border-radius:7px;
	-moz-background-clip:padding;
	-webkit-background-clip:padding-box;
	background-clip:padding-box;
}
.row1{
	background:#ffffff;
}
.row2{
	background:#e4e4e4;
}
.footer{
	font-size:small;
	text-align:center;
	margin-top:20px;
}
</style>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>
</head>
<body>
  <div id="logo"><a href="http://www.opencart.com/index.php?route=extension/extension&filter_username=uksitebuilder" target="_blank"><img src="splash.png" width="560" height="226" alt="opencartlocal" border="0" title="View UK Site Builder's Extensions" style="margin:0 auto;"></a></div>
  <div id="content">
    <h1>Directory Index</h1>

    <div id="table">
      <table width="800" border="0" cellspacing="0">
        <tr>
          <th scope="col">Site</th>
          <th scope="col">Admin</th>
          <th scope="col">vQModerator</th>
          <th scope="col">Database Name</th>
        </tr>
 <?php
	$row = 'row2';
	foreach (array_reverse(glob('*', GLOB_ONLYDIR)) as $dir) {
		if (strpos($dir, 'vQModded') === false) {
			$row = ($row=='row1'?'row2':'row1');
			$db = '';
			if (file_exists($dir.'/config.php')) {
				require($dir.'/config.php');
			} else {
				$db = '';
			}
			if (substr($dir,0,2) !== '14') {
				$modir = $dir;
			}
			$token = (isset($_GET['token'])) ? '&token=' . $_GET['token'] : '';
?>
        <tr>
          <td class="<?php echo $row; ?>"><a href="<?php echo $dir; ?>" target="_blank"><?php if($db!=''){ echo 'OpenCart v'.$dir; }else{ echo $dir; } ?></a></td>
          <td class="<?php echo $row; ?>"><?php if($db!=''){ ?><a href="<?php echo $dir.'/admin/index.php?route=common/home' . $token; ?>" target="_blank" title="Username: admin Password admin"><?php echo $dir.'/admin/'; ?></a><?php }else{ ?>&nbsp;<?php } ?></td>
          <td class="<?php echo $row; ?>"><?php if($db!=''){ ?><a href="<?php echo $dir.'/admin/index.php?route=tool/vqmod' . $token; ?>" target="_blank"><?php echo $dir.' vQMod'; ?></a><?php }else{ ?>&nbsp;<?php } ?></td>
          <td class="<?php echo $row; ?>"><?php if($db!=''){ ?><a href="/phpmyadmin/index.php?db=<?php echo $db; ?>" target="_blank" title="Username: root [Password leave blank]"><?php echo $db; ?></a><?php }else{ ?>&nbsp;<?php } ?></td>
        </tr>
<?php
		}
	}
?>
        </table>
<?php if (!isset($_GET['token']) && $modir) { ?>
		<div class="heading">
			<h1>Please enter your login details.</h1>
		</div>
		<div class="content" style="min-height: 150px;">
			<form id="form" enctype="multipart/form-data" method="post" action="<?php echo $modir;?>/admin/index.php?route=common/login">
				<table style="width: 60%;">
					<tr>
						<td>
							Username:<br/><input type="text" style="margin-top: 4px;" value="admin" name="username" /><br/><br/>
							Password:<br/><input type="password" style="margin-top: 4px;" value="admin" name="password">
							<input type="hidden" value="http://<?php echo filter_input(INPUT_SERVER, 'SERVER_NAME') . ':' . filter_input(INPUT_SERVER, 'SERVER_PORT');?>/index.php?i=1" name="redirect" />
						</td>
					</tr>
					<tr>
						<td style="text-align: right;"><a class="button" onclick="$('#form').submit();">Login</a></td>
					</tr>
				</table>
			</form>
			<script type="text/javascript"><!--
				$('.button').button();
				$('#form input').keydown(function(e) {
					if (e.keyCode == 13) {
						$('#form').submit();
					}
				});
			//--></script>
		</div>
<?php } ?>
    </div>
    <p class="footer"><table style="border:0px;">
	<tr>
		<td style="text-align:center;">Brought to you by <a href="http://www.opencart-extensions.co.uk/" target="_blank">UK Site Builder Ltd</a></td>
		<td style="text-align:center;">and by <a href="#" onclick="$('#donate').submit(); return false;" target="_blank">The Wizard of Osch</a></td>
	</tr><tr>
		<td style="text-align:center;"><a href="http://www.uksitebuilder.net/donation" target="_blank"><img src="beer.png" alt="donate" width="64" height="64" border="0" style="margin:10px;" title="Find this tool useful ? Buy me a beer or two"></a></td>
		<td style="text-align:center;"><form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_blank" id="donate"><input type="hidden" name="cmd" value="_donations"><input type="hidden" name="business" value="paypal@avanosch.nl"><input type="hidden" name="lc" value="US"><input type="hidden" name="item_name" value="AvanOsch Appreciation Donation"><input type="hidden" name="currency_code" value="USD"><input type="hidden" name="bn" value="PP-DonationsBF:btn_donate_LG.gif:NonHosted"><input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!"><img alt="" border="0" src="https://www.paypalobjects.com/nl_NL/i/scr/pixel.gif" width="1" height="1"></form></td>
	</tr><tr>
		<td colspan="2" style="text-align:center;">View UK Site Builder's Extensions at the <a href="http://www.opencart.com/index.php?route=extension/extension&filter_username=uksitebuilder" target="_blank">OpenCart Extension Store</a></td>
	</tr><tr>
		<td colspan="2" style="text-align:center;">View AvanOsch's Extensions at the <a href="http://www.opencart.com/index.php?route=extension/extension&filter_username=avanosch" target="_blank">OpenCart Extension Store</a></td>
	</tr>
	</table></p>
</div>
</body>
</html>