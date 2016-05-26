<?php
function foldersize($d){
	$s = 0; // Size
	$i = 0; // File number
	$d_array = scandir($d);
	foreach($d_array as $key=>$f){
		if($f!='..' && $f!='.'){ // File
			if(is_dir($d.'/'.$f)){
				$si = foldersize($d.'/'.$f);
				$s = $s + $si[0];
				$i = $i + $si[1];
			}else if(is_file($d.'/'.$f)){
				$s = $s + filesize($d.'/'.$f);
				$i++;
			}
		}
	}
	return array($s,$i);
}
function SizeB($s){
	if($s>(1024*1024*1024))	$r=number_format($s/(1024*1024*1024),2).' GiB';
	elseif($s>(1024*1024))	$r=number_format($s/(1024*1024),1).' MiB';
	elseif($s>1024)			$r=number_format($s/1024,0).' KiB';
	else					$r=number_format($s).' B';
	return $r;
}
?>