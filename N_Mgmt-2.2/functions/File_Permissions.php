<?php
function File_Permissions($namehref){
	$perms=fileperms($namehref);
	if(($perms & 0xC000) == 0xC000)		$info = 's';	// Socket
	elseif(($perms & 0xA000) == 0xA000)	$info = 'l';	// Symbolic Link
	elseif(($perms & 0x8000) == 0x8000)	$info = '-';	// Regular
	elseif(($perms & 0x6000) == 0x6000)	$info = 'b';	// Block special
	elseif(($perms & 0x4000) == 0x4000)	$info = 'd';	// Directory
	elseif(($perms & 0x2000) == 0x2000)	$info = 'c';	// Character special
	elseif(($perms & 0x1000) == 0x1000)	$info = 'p';	// FIFO pipe
	else								$info = 'u';	// Unknown
	// Owner
	$info .= (($perms & 0x0100) ? 'r' : '-');
	$info .= (($perms & 0x0080) ? 'w' : '-');
	$info .= (($perms & 0x0040) ?
		(($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
	// Group
	$info .= (($perms & 0x0020) ? 'r' : '-');
	$info .= (($perms & 0x0010) ? 'w' : '-');
	$info .= (($perms & 0x0008) ?
		(($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
	// Public
	$info .= (($perms & 0x0004) ? 'r' : '-');
	$info .= (($perms & 0x0002) ? 'w' : '-');
	$info .= (($perms & 0x0001) ?
		(($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
	return $info;
}
?>