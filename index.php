<?php /*-.___________________________________________________________________________
/
	Php-list - Launcher
\________________________________________________________________________________.-*/

// -------- User config --------
// Dirs relative path
$IncDir='N_Mgmt-2.3'; // Include core
$CfgDir='.config'; // Config. path
// Alternatives to config. files
$AdmPassword=	'-2';	// If '', admin tools will never be displayed
$UsrPassword=	'-1';	// If '', browsing will be accessible without password
$AltColor=	'#00f';
// Files to exclude from display
$XcludPrfix=	array('.','_');
$XcludDirs=		array('');
$XcludExtns=	array('');
$XcludFiles=	array('');
// Read-only and Copy-only
$ReadOnlyDirs=	array('');
$ReadOnlyFiles=	array('');
$CopyOnlyDirs=	array('');
$CopyOnlyFiles=	array('');
// Extensions to edit
$EditableExtns=	array('PHP','CSS','HTM','HTML','TXT','INI','HTACCESS');
$ArticleExtns=	array('HTM','HTML');
// Modules
$RecBinON=	1;	// 1 = Delete moves to RecycleBin
$UploadsON=	1;	// 1 = Admin can upload
$SessTimeOut=60; // in minutes
// ------- / User config -------

// -------- Php config ---------
session_start(); $PhpFile=__FILE__; require $IncDir.'/core.php'; ?>