<?php
function cp_file($path, $cpPath){
	$copf_msg='';
	if(!copy($path, $cpPath))
		$copf_msg.="Couldn't create file \"$cpPath\". ";
	elseif(!chmod($cpPath, fileperms($path)))
		$copf_msg.="Couldn't copy the permissions of \"$path\". ";
	return $copf_msg;
}

function cp_tree($path, $cpPath){
	$copt_msg='';
	if(!mkdir($cpPath))
		$copt_msg.="Couldn't create folder \"$cpPath\". ";
	if(!chmod($cpPath, fileperms($path)))
		$copt_msg.="Couldn't copy the permissions of folder \"$path\". ";
	$path=rtrim($path, '/').'/';
	$cpPath=rtrim($cpPath, '/').'/';
	$hdl=opendir($path);
	while($file=readdir($hdl)){
		if($file!='.' AND $file!='..'){
			$fPath=$path.$file;
			$fcpPath=$cpPath.$file;
			if(is_dir($fPath))	$copt_msg.=cp_tree($fPath, $fcpPath);
			else				$copt_msg.=cp_file($fPath, $fcpPath);
		}
	}
	closedir($hdl);
	return $copt_msg;
}

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