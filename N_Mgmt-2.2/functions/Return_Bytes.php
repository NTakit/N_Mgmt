<?php
function Return_Bytes($val){
	if(empty($val))return 0;
	$val = trim($val);
	preg_match('#([0-9]+)[\s]*([a-z]+)#i', $val, $matches);
	$last = '';
	if(isset($matches[2]))	$last=$matches[2];
	if(isset($matches[1]))	$val= (int) $matches[1];
	switch(strtolower($last)){
		case 'g':	case 'gb':	$val *= 1024;
		case 'm':	case 'mb':	$val *= 1024;
		case 'k':	case 'kb':	$val *= 1024;
		default:	break;
	}
	return (int) $val;
}
?>