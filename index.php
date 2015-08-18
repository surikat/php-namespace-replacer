<?php
/*
 * Php-Namespace-Replacer
 *
 * @package php-namespace-replacer
 * @version 1.0
 * @link http://github.com/surikat/php-namespace-replacer/
 * @author Jo Surikat <jo@surikat.pro>
 * @website http://wildsurikat.com
 */
$recursive = function($repository,$call)use(&$recursive){
	if($dh = opendir($repository)){
		while($d=readdir($dh)){
			if($d=='.'||$d=='..'||$d=='.git'||strpos($d,'.git.')===0)
				continue;
			$f = $repository.'/'.$d;
			if(is_dir($f)){
				$recursive($f,$call);
			}
			elseif(is_file($f)){
				call_user_func($call,$f);
			}
		}
		closedir($dh);
	}
};

$directory = isset($_GET['directory'])?rtrim($_GET['directory'],'/'):'';
$action = '?directory='.urlencode($directory);

$nso = '';
$nsn = '';
$modified = false;
if(isset($_POST['namespace_old'])&&$_POST['namespace_old']&&isset($_POST['namespace_new'])&&$_POST['namespace_new']){
	$nso = $_POST['namespace_old'];
	$nsn = $_POST['namespace_new'];
	$nsod = str_replace('\\','\\\\',$nso).'\\\\';
	$nsnd = str_replace('\\','\\\\',$nsn).'\\\\';
	$replace = [
		' '.$nso.';'			=>	' '.$nsn.';',
		"\t".$nso.';'			=>	"\t".$nsn.';',
		"\n".$nso.';'			=>	"\n".$nsn.';',
		"\r".$nso.';'			=>	"\r".$nsn.';',
	];
	if(substr($nsn,-1*strlen($nso))==$nso){
		$uniqid = uniqid().uniqid();
		$nsnt = $uniqid.$nsn.$uniqid;
		$nsndt = str_replace('\\','\\\\',$nsnt).'\\\\';;
		$replace[$nsod] = $nsndt;
		$replace[$nso.'\\'] = $nsnt.'\\';
		$replace[$nsndt.$nsnt.'\\\\'] = $nsndt;
		$replace[$uniqid] = '';
	}
	else{
		$replace[$nsod] = $nsnd;
		$replace[$nso.'\\'] = $nsn.'\\';
		$replace[$nsnd.$nsn.'\\\\'] = $nsnd;
	}
	$keys = array_keys($replace);
	$values = array_values($replace);
	$modified = [];
	$recursive($directory,function($file)use($keys,$values,&$modified){
		$content = file_get_contents($file);
		$ncontent = str_replace($keys,$values,$content);
		if($ncontent!==$content){
			file_put_contents($file,$ncontent);
			$modified[] = $file;
		}
	});
}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>Php Namespace Replacer</title>
	</head>
	<body>
		<h1>Php Namespace Replacer</h1>
		<form method="GET" action="">
			<fieldset>
				<label for="directory">Directory</label>
				<input id="directory" name="directory" type="text" value="<?php echo $directory;?>">
			</fieldset>
			<input type="submit" value="Select">
		</form>
		<?php if($directory):?>
			<form method="POST" action="<?php echo $action;?>">
				<?php echo $directory;?>
				<fieldset>
					<label for="namespace_old">Old Namespace</label>
					<input id="namespace_old" name="namespace_old" type="text" value="<?php echo $nso;?>">
				</fieldset>
				<fieldset>
					<label for="namespace_new">New Namespace</label>
					<input id="namespace_new" name="namespace_new" type="text" value="<?php echo $nsn;?>">
				</fieldset>
				<input type="submit" value="Find and Replace">
			</form>
		<?php endif;?>
		
		<?php if($modified):?>
			<hr>
			<h2>Modified files:</h2>
			<ul>
				<?php foreach($modified as $file):?>
					<li><a href="file://<?php echo $file;?>" target="_blank"><?php echo $file;?></a></li>
				<?php endforeach;?>
			</ul>
		<?php endif;?>
	</body>
</html>