<?php
error_reporting(E_ALL);
ini_set('display_errors',1);
/**
 * convert less files to css
 * 
 * a 'full'description
 * multilene is cool
 * and big number is more cool
 * @author Jesus Christian Cruz Acono <devel@compermisosnetwork.info>
 * @version 0.0.1
 * @package sample
*/


function parser($dirPath = './', $fileType = 'less', $recursive = 10 , $basedir = './'){
	if($recursive == 0){
		return;
	}	
	$retval = array();
	$fileTypePattern = '/\.'. $fileType . '$/';
	if(substr($dirPath, -1) != "/"){
		$dirPath .= "/";
	} 
	if ($handle = opendir($dirPath)) {
		while (false !== ($file = readdir($handle))) {
			$fileEntry = $dirPath. $file;
			if ($file != "." && $file != "..") {
				if (is_dir($fileEntry)) {
					$retval[] = array(
						"name" => $file,
						"type" => filetype($fileEntry),
						"size" => 0,
						"lastmod" => filemtime($fileEntry),
						"content" => parser($fileEntry, $fileType, $recursive - 1, $basedir)
					);
				}else {
					if(preg_match($fileTypePattern, $file)){
						$retval[] = array(
							"name" => $file,
							"namenotype" => str_replace('.' . $fileType, '', $file),
							"pathname" => str_replace($basedir, '', str_replace($file , '', $fileEntry)),
							"size" => filesize($fileEntry),
							"lastmod" => filemtime($fileEntry)
						);
					} 
				}
			}
		}
		closedir($handle);
	}
	
	return $retval;
}
function cleaner($tree = array()){
	$cant = count($tree);
	$dummy_array = array();
	$rtree = array();
	for($i = 0; $i< $cant; $i++){
		if(isset($tree[$i]['content'])){
			if($tree[$i]['content'] === $dummy_array){
				unset($tree[$i]); /*no it is nesseray */
			}else{
				$tree[$i]['content'] = cleaner($tree[$i]['content']);
				$rtree[] = $tree[$i];
			}
		}else{
			$rtree[] = $tree[$i];
		}
	}
	return $rtree;
}
function deTree($tree, &$out = array()){
	$newTree = array();
	foreach($tree as $file){
		if(isset($file['content'])){
			deTree($file['content'], $out);
		}else{
			$out[] = $file;
		}
	}
	return $out;
}



function genCSS($lessDir = 'less/', $cssDir = 'css/', $lessExt = 'less' ){
	require "lessc.inc.php";
	$unclean = 1;
	$tree = parser($lessDir, $lessExt, 10, $lessDir);
	$cleanTree = array();
	while($unclean){
		if($cleanTree == $tree){
			$unclean = 0;
		}else{
			$cleanTree = cleaner($tree);
			$tree = $cleanTree;
		}
	}
	$tree = deTree($tree);
	$less = new lessc;
	foreach($tree as $file){
		$cssName = $cssDir . $file['pathname'] . $file['namenotype'] . '.css';
		$lessName = $lessDir . $file['pathname'] . $file['namenotype'] . '.' . $lessExt;
		$cssCDir = $cssDir . $file['pathname'];
		if(!is_dir($cssCDir)){
			mkdir($cssCDir, 0755, TRUE);
		}
		try {
			$less->checkedCompile($lessName, $cssName);
		} catch (exception $e) {
			echo "fatal error: " . $e->getMessage();
		}
		
	}
	
}
/*generate('less/', 'css/', 'less');
var_dump($argv);
var_dump($argc);*/
echo('Usage less2css.php less/ css/ less' . "\n");
$var = array();
if(isset($argv[1])){
	$var[1] = $argv[1];
}else{
	$var[1] = 'less/';
}
if(isset($argv[2])){
	$var[2] = $argv[2];
}else{
	$var[2] = 'css/';
}
if(isset($argv[3])){
	$var[3] = $argv[3];
}else{
	$var[3] = 'less';
}

genCSS($var[1],$var[2],$var[3]);

#genCSS();
