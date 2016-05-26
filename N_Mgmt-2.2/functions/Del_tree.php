<?php
function del_tree($path){
	$path=rtrim($path, '/').'/';
	$hdl=opendir($path);
	while($file=readdir($hdl)){
		if($file!='.' AND $file!='..'){
			$fPath=$path.$file;
			if(is_dir($fPath)) del_tree($fPath);
			else unlink($fPath);
		}
	}
	closedir($hdl);
	return rmdir($path);
}
?>