<?php /*-.___________________________________________________________________________
/
	Name:			N_Mgmt-2.2 - Core
	Description:	Directory-listing including sub-directories,
					password protection and administration tools.
	Author:			N.Takit
	Last update:	09/07/2014
  \_______
	Works with:
		jQuery			(Feature-rich JavaScript library)
		-prefix-free	(Break free from CSS prefix hell!)
  \__________________________
	Included and integrated:
		Plupload		(File uploading API)
		TinyMCE			(Javascript HTML article editor)
		CodeMirror		(Code editor)
		colpick			(jQuery Plugin color selector)
\________________________________________________________________________________.-*/

if(session_id()=='') exit;		// PHP < 5.4
//if(session_status()!=2) exit;	// PHP > 5.4

error_reporting(E_ALL); // E_ALL E_STRICT
ini_set('memory_limit','512M');
// Include
if(get_magic_quotes_gpc())	include 'functions/Kill_magic_quotes.php';
include 'functions/File_Management.php';
// Paths
$ClrPath=preg_replace('/[^A-Za-z0-9]/','',$PhpFile);
$PhpFullDir=dirname($PhpFile);
$PhpFile=basename($PhpFile);
$PhpDir=basename($PhpFullDir);
if(strpos($PhpDir,'http')!==FALSE)
	$PhpDir=basename(dirname($PhpFullDir));
$ss=$ClrPath.'-';
// eXclusions and formatting
$XcludDirs=	array_map('strtolower',$XcludDirs);
$XcludExtns=array_map('strtoupper',$XcludExtns);
$XcludFiles=array_map('strtolower',$XcludFiles);
$ThbablesExtns=array('PNG','JPEG','JPG','GIF');
// Config files
$cfgUsers=	$CfgDir.'/Users.txt';	// Users
$MultiUsrs=file_exists($cfgUsers);
$cfgColor=	$CfgDir.'/Color.color';	// Color
$Color= (file_exists($cfgColor)) ? file_get_contents($cfgColor) : $AltColor;
$cfgOptions=$CfgDir.'/Options.txt';	// Options file
// Options parameters
/* 1) */ $Thb_MaxWidth=150; $Thb_MaxHeight=100;
/* 2) */ $Bak_file=0;
/* 3) */ $DirSizeON=0;
/* 4) */ $tMCE_Css='';
/* 5) */ $tMCE_Class='';
/* 6) */ $CaseSens=0;
if(file_exists($cfgOptions)){
	$t=file($cfgOptions); $t=preg_replace('/[^A-Za-z0-9_.:\/]/','',$t); // Loads and cleans file
	// 1) Thumbs Size
	list($t1,$t2)=explode(':',$t[0]);
	list($Thb_MaxWidth,$Thb_MaxHeight)=explode('x',$t2);
	// 2) Backup when editing files
	list($t1,$t2)=explode(':',$t[1]);
	if($t2=='ON')	$Bak_file=1;
	// 3) Folders sizes
	list($t1,$t2)=explode(':',$t[2]);
	if($t2=='ON')	$DirSizeON=1;
	// 4) TinyMCE CSS for article
	list($t1,$tMCE_Css)=explode(':',$t[3]);
	// 5) TinyMCE article class
	list($t1,$tMCE_Class)=explode(':',$t[4]);
	// 6) Case-sensitiveness
	list($t1,$t2)=explode(':',$t[5]);
	if($t2=='ON')	$CaseSens=1;
}
// Erase all accesses
$_SESSION[$ss.'axs']=0;
$ssOpen=0; $LoginOK=0;
// Access conditions
if(isset($_SESSION[$ss.'StrtTime'])){
	if(($_SESSION[$ss.'StrtTime']+(60*$SessTimeOut)) > time()) // Session not expired
		$ssOpen=1;
}
if(isset($_POST['SubmitLog'])){
	$_SESSION[$ss.'Powers']=0;
	if($MultiUsrs){ // Multi users access
		$File=array_map('trim',file($cfgUsers)); // Loads Users file
		$File=str_replace(' ','',$File); // Cleans spaces
		for($i=1;$i<count($File);$i++){
			list($Usrs[],$Pwds[],$Powr[])=explode('/',$File[$i]); // Creates lists
		}
		if(isset($_POST['Usr']) AND isset($_POST['Pwd'])){ // User and password submitted
			$Usr=$_POST['Usr'];
			if(1){ //if(!$CaseSens){ // Make case-insensitive
				$Usr=strtoupper($_POST['Usr']);
				$Usrs=array_map('strtoupper',$Usrs);
			}
			$Pwd=htmlspecialchars($_POST['Pwd']);
			$i=array_search($Usr, $Usrs);
			if($i!==FALSE){ // Valid user
				if($Pwd==$Pwds[$i])		$LoginOK=1; // Valid password
				$cfgPowers=	$CfgDir.'/Powers.txt';	// Powers file
				$File=array_map('trim',file($cfgPowers));
				$File=str_replace(array(' ',"\t"),'',$File);			
				$File=str_replace(array('-','x'),array('0','1'),$File);
				for($j=1;$j<count($File);$j++){
					list($t1,$t2)=explode(':',$File[$j]);
					if($Powr[$i]==$t1){
						$_SESSION[$ss.'Powers']= explode('|',$t2);
					}
				}
			}
		}
	}else{ // Simple password access
		if($AltUsrPw=='') // Grants access if no password
			$LoginOK=1;
		if(isset($_POST['Pwd'])){ // Password submitted
			$Pwd=htmlspecialchars($_POST['Pwd']);
			if($Pwd==$AltUsrPw)	$LoginOK=1; // User password submitted is correct
			if($Pwd==$AltAdmPw AND $AltAdmPw){
				$LoginOK=1; // Admin password submitted is correct
			}
		}
	}
}
// Grants access
if($ssOpen OR $LoginOK)
	$_SESSION[$ss.'axs']=1;
else
	$_SESSION[$ss.'Powers']=0;
list($t,$Pow_nDir,$Pow_nFile,$Pow_Upload,$Pow_tMCE,$Pow_CM,$Pow_Color,$Pow_Tools,$Pow_ToolsXtnd,$Pow_RecBin,$Pow_ChMod,$Pow_HiddenAgent,$t)= $_SESSION[$ss.'Powers'];
if(isset($_GET['LogOut'])) // LogOut
	$_SESSION[$ss.'axs']=0;
// Ensures PhpFile-name is in the URL
if(strpos($_SERVER['REQUEST_URI'],$PhpFile)) $F5=0;
else $F5=1;
// Reads and filters dir requests
$dir='.'; // Init.
if(isset($_SESSION[$ss.'dir']) AND $_SESSION[$ss.'dir']!=''){
	$GetDir=$_SESSION[$ss.'dir']; $F5=1;
}
if(isset($_GET['dir']) AND $_GET['dir']!=''){
	$_SESSION[$ss.'dir']=''; $GetDir=$_GET['dir'];
}
if(isset($GetDir)){
	// Cleans URL request
	if(substr($GetDir, 0, 2)!='./')	$GetDir='./'.$GetDir;
	do{	$GetDir=str_replace('//','/',str_replace(array('..','././','/./.'),array('.','./','/.'),$GetDir,$NbR));
		if($NbR){ $ErrInfo='Don\'t play with that URL.'; $F5=1; }
	}while($NbR>0);
	if($GetDir=='./' OR $GetDir=='./.') $GetDir='.';
	$dir=$GetDir; // Copy clean request
}else	$F5=1;
$InRecycle= ($dir=="./.Recycle"); // Are you in Recycle ?
if(!$Pow_HiddenAgent AND strpos($dir, '/.')!==FALSE AND !($InRecycle AND $Pow_RecBin)){ // E403
	$ErrInfo='Forbidden ! But nice try…';
	$dir='.'; $F5=1;
}
if(!file_exists($dir)){ // E404
	$ErrInfo='The requested directory doesn\'t exist. Back to root…';
	$dir='.'; $F5=1;
}
$dirhref=$dir.'/';
// Edition requests
$edit=''; $Editor='';
if(isset($_GET['edit'])) $edit=$_GET['edit'];
if(($Pow_tMCE OR $Pow_CM) AND $edit!=''){
	$edithref=$dirhref.$edit;
	$Editor=file_exists($edithref);
}
if(isset($_POST['EditPost'])){ // Saves the edition even if session expired
	$nPost=$_POST['EditPost'];
	$nPost=str_replace(
		array(	//'&laquo; ',' &raquo;',' ?',' ;',' :',' !',
				'<!--EDIT textarea','textarea EDIT-->', // textareas workaround
				"\r" // Lonely [CR]
		),
		array(	//'&laquo; ',' &raquo;',' ?',' ;',' :',' !', // nbsp corrections
				'<textarea','textarea>',
				''
		),
	$nPost);
	if($Bak_file){ // Backup option
		$new=$edithref.'.tmp';
		$bak=$dirhref.'_bak-'.$edit;
		if(file_put_contents($new,$nPost)){
			if(file_exists($bak)) unlink($bak);
			if(copy($edithref,$bak) && chmod($bak, 0644)){
				if(rename($new,$edithref) && chmod($edithref, 0644)){ $_SESSION[$ss.'ErrInfo']="File saved."; }
				else{ $ErrInfo="The file \"$new\" couldn't be renamed as \"$edithref\""; $F5=1; }
			}else{ $ErrInfo="Couldn't create file \"$bak\". "; $F5=1; }
		}else{ $ErrInfo="The file \"$edithref\" couldn't be saved."; $F5=1; }
	}else{
		file_put_contents($edithref, $nPost);
		$_SESSION[$ss.'ErrInfo']="File saved.";
	}
}

if($_SESSION[$ss.'axs']==1){ // ---------------- Global "if" ( Access granted ) ----------------
	$_SESSION[$ss.'StrtTime']=time();	// Sets session beginning time
	if(!$Editor){ // ---------------- Sub "if" ( browse mode ) ----------------	
		// Parameters requests
		if(!isset($_SESSION[$ss.'Thb']))	$_SESSION[$ss.'Thb']=1;
		if(!isset($_SESSION[$ss.'Typ']))	$_SESSION[$ss.'Typ']=1;
		if(!isset($_SESSION[$ss.'Siz']))	$_SESSION[$ss.'Siz']=1;
		if(!isset($_SESSION[$ss.'Dat']))	$_SESSION[$ss.'Dat']=1;
		if(!isset($_SESSION[$ss.'Hdn']))	$_SESSION[$ss.'Hdn']=0;
		if(!isset($_SESSION[$ss.'Chm']))	$_SESSION[$ss.'Chm']=0;
		$ThbON=$_SESSION[$ss.'Thb'];	if($ThbON)	include 'functions/NTAK_Thumbs.php';
		$TypON=$_SESSION[$ss.'Typ'];
		$SizON=$_SESSION[$ss.'Siz'];	if($SizON)	include 'functions/Sizes.php';
		$DatON=$_SESSION[$ss.'Dat'];
		$HdnON=$_SESSION[$ss.'Hdn'] && $Pow_HiddenAgent;
		$ChmON=$_SESSION[$ss.'Chm'] && $Pow_ChMod;	if($ChmON)	include 'functions/File_Permissions.php';
		if(isset($_GET['Thb'])){ $_SESSION[$ss.'Thb']=!$_SESSION[$ss.'Thb']; $F5=1; }
		if(isset($_GET['Typ'])){ $_SESSION[$ss.'Typ']=!$_SESSION[$ss.'Typ']; $F5=1; }
		if(isset($_GET['Siz'])){ $_SESSION[$ss.'Siz']=!$_SESSION[$ss.'Siz']; $F5=1; }
		if(isset($_GET['Dat'])){ $_SESSION[$ss.'Dat']=!$_SESSION[$ss.'Dat']; $F5=1; }

		// Parameters requests
		if($Pow_HiddenAgent && isset($_GET['Hdn'])){ $_SESSION[$ss.'Hdn']=!$_SESSION[$ss.'Hdn']; $F5=1; }
		if($Pow_ChMod && isset($_GET['Chm'])){ $_SESSION[$ss.'Chm']=!$_SESSION[$ss.'Chm']; $F5=1; }
		// Tools requests
		if($Pow_nDir && isset($_GET['nDir'])){
			$nDir=$dirhref.$_GET['nDir'];
			if(!file_exists($nDir)){
				mkdir($nDir); chmod($nDir, 0755); // Creates and corrects chmod
			}else	$ErrInfo="\"$nDir\" already exists.";
			$F5=1;
		}
		if($Pow_nFile AND isset($_GET['nFile'])){
			$nFile=$dirhref.$_GET['nFile'];
			if(!file_exists($nFile)){
				touch($nFile); // file_put_contents($nFile,'');
				chmod($nFile, 0644); // Corrects chmod
			}else	$ErrInfo="\"$nFile\" already exists.";
			$F5=1;
		}
		if($Pow_RecBin AND $InRecycle AND isset($_GET['emptyBin'])){
			$hdl=opendir($dirhref);
			while($ent=readdir($hdl)){
				if($ent!='.' AND $ent!='..'){	
					$delEnt=$dirhref.$ent;
					if(is_dir($delEnt)){
						if(!del_tree($delEnt))	$f1=1;
					}elseif(!unlink($delEnt))	$f1=1;
				}
			}
			closedir($hdl);
			if(isset($f1)){
				if(!file_exists('./.Recycle/_')) mkdir('./.Recycle/_');
				$f2=0;
				$hdl=opendir($dirhref);
				while($ent=readdir($hdl)){
					if($ent!='.' AND $ent!='..' AND $ent!='_'){
							$f1++;	$f2=1;
						if(rename("./.Recycle/$ent", "./.Recycle/_/$ent"))
							$f1--;
					}
				}
				closedir($hdl);
				if($f1==1){
					if($f2)	$ErrInfo="Some files couldn't be deleted and have been hidden.";
					else $ErrInfo="Recycle Bin looks empty.";
				}else $ErrInfo="Some files couldn't be deleted nor hidden.";
			}else $ErrInfo="Recycle Bin is now empty.";
			$F5=1;
		}
		if($Pow_Tools && isset($_GET['elm'])){
			$elm=$_GET['elm'];
			if(file_exists($elm)){
				if(isset($_GET['ren'])){
					$ren=$_GET['ren'];
					$renhref=dirname($elm).'/'.$ren;
					if(!file_exists($renhref)){
						if(!rename($elm, $renhref)) $ErrInfo="\"$elm\" couldn't be renamed as \"$ren\".";
					}
					else $ErrInfo="\"$ren\" already exists.";
				}
				if(isset($_GET['mov'])){
					$mov=$_GET['mov'];
					if(file_exists($mov)){
						if(!rename($elm, $mov.'/'.basename($elm))) $ErrInfo="\"$elm\" couldn't be moved to \"$mov\".";
					}else	$ErrInfo="Folder \"$mov\" couldn't be found.";
				}
				if(isset($_GET['cop'])){
					$cop=$dirhref.$_GET['cop'];
					if(!file_exists($cop)){
						if(is_dir($elm))
							$ErrInfo=cp_tree($elm, $cop);
						else
							$ErrInfo=cp_file($elm, $cop);
					}else	$ErrInfo="\"$cop\" already exists.";
				}
				if(isset($_GET['del'])){
					if($RecBinON AND !$InRecycle){
						if(!rename($elm, '.Recycle/'.date("Y-m-d_H:i:s ", time()).basename($elm)))
							$ErrInfo="\"$elm\" couldn't be moved to the Recycle Bin.";
					}
					elseif(is_dir($elm)){
						if(!del_tree($elm))	$ErrInfo="Directory \"$elm\" couldn't be deleted.";
					}elseif(!unlink($elm))	$ErrInfo="File \"$elm\" couldn't be deleted.";
				}
				if($Pow_ChMod && isset($_GET['chm'])){
					if(!chmod($elm, octdec($_GET['chm'])))	$ErrInfo="Couldn't change the permissions of \"$elm\".";
				}
				if(isset($_GET['hex'])){
					$hex=$_GET['hex'];
					if(!file_put_contents($elm, "#$hex")){	$ErrInfo="The color file \"$elm\" couldn't be saved."; }
				}
			}
			else	$ErrInfo="Element \"$elm\" doesn't exist.";
			$F5=1;
		}

		$WxH16='width="16" height="16"'; // Typical size for icons
		// Top right buttons
		$MozBtn="<a id='MozBtn' class='Btn TBtn0' onclick='$(\".Mosaic\").show(); $(\"#MozBtn\").removeClass(\"TBtn0\").addClass(\"TBtn1\");'><img src='$IncDir/icons/sys/mosaic.png' title='Display thumbnails mosaic' alt='Mosaic' $WxH16 /></a> ";
		$ThbT= ($ThbON) ? 'Hide' : 'Show';
		$ThbBtn="<a class='Btn TBtn$ThbON' href='?dir=$dir&amp;Thb' style='margin-right: 20px;'><img src='$IncDir/icons/sys/thumbs.png' title='$ThbT images thumbnails' alt='Thumbs' $WxH16 /></a> ";
		$TypT= ($TypON) ? 'Hide' : 'Show';
		$TypBtn="<a class='Btn TBtn$TypON' href='?dir=$dir&amp;Typ'><img src='$IncDir/icons/sys/details.png' title='$TypT file type' alt='Type' $WxH16 /></a> ";
		$SizT= ($SizON) ? 'Hide' : 'Show';
		$SizBtn="<a class='Btn TBtn$SizON' href='?dir=$dir&amp;Siz'><img src='$IncDir/icons/sys/size.png' title='$SizT file size' alt='Size' $WxH16 /></a> ";
		$DatT= ($DatON) ? 'Hide' : 'Show';
		$DatBtn="<a class='Btn TBtn$DatON' href='?dir=$dir&amp;Dat'><img src='$IncDir/icons/sys/clock.png' title='$DatT modified date and time' alt='Date' $WxH16 /></a> ";
		$HdnT= ($HdnON) ? 'Hide' : 'Show';
		$HdnBtn="<a class='Btn TBtn$HdnON' href='?dir=$dir&amp;Hdn' style='margin-right: 10px;'><img src='$IncDir/icons/sys/hidden.png' title='$HdnT hidden files' alt='Hidden files' $WxH16 /></a>";
		$ChmT= ($ChmON) ? 'Hide' : 'Show';
		$ChmBtn="<a class='Btn TBtn$ChmON' href='?dir=$dir&amp;Chm'><img src='$IncDir/icons/sys/chmod.png' title='$ChmT Chmod' alt='Chmod' $WxH16 /></a> ";
		// Row button images
		$HdnImg=	"<img src='$IncDir/icons/sys/hidden.png' style='opacity: .32; margin-left: 0;' alt='' $WxH16 />";
		$tMCEimg=	"<img src='$IncDir/icons/sys/tMCE.png' alt='TinyMCE' title='Edit with TinyMCE' $WxH16 />";
		$CMimg=		"<img src='$IncDir/icons/sys/CM.ico' alt='CodeMirror' title='Edit with CodeMirror' $WxH16 />";
		$RenImg=	"<img src='$IncDir/icons/sys/rename.png' alt='Rename' title='Rename' $WxH16 />";
		$MovImg=	"<img src='$IncDir/icons/sys/move.png' alt='Move' title='Move' $WxH16 />";
		$CopImg=	"<img src='$IncDir/icons/sys/copy.png' alt='Copy' title='Copy' $WxH16 />";
		$DelImg=	"<img src='$IncDir/icons/sys/delete.png' alt='Delete' title='Delete' $WxH16 />";
		$ChmImg=	"<img src='$IncDir/icons/sys/chmod.png' alt='Edit' title='Edit' $WxH16 />";
	}
	// -------- Displays page --------
	?><!DOCTYPE html>
	<html>
		<head><?php
			if($F5) echo "<meta http-equiv='refresh' content='0; url=$PhpFile?dir=$dir' />"; ?>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<meta name="viewport" content="width=device-width" />
			<title><?=$PhpDir ?></title>
			<link rel="shortcut icon" href="<?=$IncDir ?>/favicon.png" type="image/png" />
			<link rel="stylesheet" href="<?=$IncDir ?>/style.css" />
			<style><?php echo "#Hdr, #List th{ background-color: $Color; }"; ?></style>
			<script src="<?=$IncDir ?>/../jquery-2.1.1.min.js"></script>
			<script src="<?=$IncDir ?>/../prefixfree.min.js"></script>
			<script type="text/javascript">
				function resize(){
					var currentH= $(window).height();
					var DeltaH=200;
					if(currentH<700){ DeltaH=170; };
					if(currentH<600){ DeltaH=140; };
					var Hmax= (currentH-DeltaH)+'px';
					$('.Container').css("maxHeight", Hmax);
					$('.Container').css("maxHeight", $('#Content').css("maxHeight"));
					$('#InMosaic').css("height", $('#Content').css("height"));
					$('.CodeMirror').css("height", Hmax); // Faute de mieux, pour le moment
				}
				// No selection allowed
				function selection(e){ return false; }
				function clic(){ return true; }
				document.onselectstart=new Function ("return false");
				if(window.sidebar){ document.onmousedown=selection; document.onclick=clic; }
			</script>
			<?php if(!$Editor){
				if(!$F5){ ?>
					<script src="<?=$IncDir ?>/../SortTable.min.js"></script>
					<!--link rel="stylesheet" type="text/css" href="<?=$IncDir ?>/colpick.css" /-->
					<style>#Thumbs{ <?php $t=($Thb_MaxWidth+8).'px'; echo "min-width:$t; width:$t;"; ?> }</style>
					<!--script src="../.inc/colpick/js/colpick.min.js"></script-->
					<script>
						function nDir(){
							var Name = prompt("Type a name for the new folder :","New_folder");
							if(Name != null && Name != "" && Name != false)
								window.location = window.location+"&nDir="+Name;
						return; }
						function nFile(){
							var Name = prompt("Type a name for the new file :","New_file");
							if(Name != null && Name != "" && Name != false)
								window.location = window.location+"&nFile="+Name;
						return; }
						function ren(elm){
							var Name = elm.replace(/^.*[\\\/]/, ''); // Extracts filename
							var nName = prompt("Type a new name for \""+Name+"\" :",Name);
							if(nName != Name && nName != null && nName != "" && nName != false)
								window.location = window.location+"&elm="+elm+"&ren="+nName;
						return; }
						function mov(elm){
							var Path = elm.replace(/\\/g,'/').replace(/\/[^\/]*$/, ''); // Extracts dirname
							var nPath = prompt("Type a new pathname for \""+elm+"\" :",Path);
							if(nPath != elm && nPath != null && nPath != "" && nPath != false)
								window.location = window.location+"&elm="+elm+"&mov="+nPath;
						return; }
						function cop(elm){
							var Name = elm.replace(/^.*[\\\/]/, '');
							var nName = prompt("Type a name for the copy of \""+Name+"\" :",Name);
							if(nName != Name && nName != null && nName != "" && nName != false)
								window.location = window.location+"&elm="+elm+"&cop="+nName;
						return; }
						function del(elm){
							var sure = false;
							var sure = confirm("Are you sure you want to delete \""+elm+"\" ?");
							if(sure)	window.location = window.location+"&elm="+elm+"&del";
						return; }
						function chmod(elm, chmod){
							var nChmod = prompt("Type a new chmod for \""+elm+"\" :",chmod);
							if(nChmod != chmod && nChmod != null && nChmod != "" && nChmod != false)
							window.location = window.location+"&elm="+elm+"&chm="+nChmod;
						return; }
					</script><?php
				}
			} ?>
		</head>
		<body id="DropBox" onload="resize()" onresize="resize();"><?php
			if(isset($_SESSION[$ss.'ErrInfo'])){ // Err/Info management
				$ErrInfo=$_SESSION[$ss.'ErrInfo'];	
				unset($_SESSION[$ss.'ErrInfo']);
				echo "<div id='ErrInfo'>$ErrInfo</div>"; ?>
				<script>$('#ErrInfo').delay(4000).fadeOut(1000);</script><?php
			}
			if($F5){ if(isset($ErrInfo)) $_SESSION[$ss.'ErrInfo']=$ErrInfo; } ?>
			<div id="Hdr">
				<div id="Icon"><a href="" onClick="window.location.reload(false)"><img src="<?=$IncDir ?>/favicon.png" height="16" width="16" /></a></div>
				<div id="LogOut"><a href="?LogOut"><img src="<?=$IncDir ?>/icons/sys/logout.png" alt="X" height="16" width="16" /></a></div>
				<div id="Path"><? // Clickable path
					$t= $Editor ? 'Editing…' : 'Indexing…';
					echo "<span style='color:black;'><i>$t</i></span>   ";
					$t=$dirhref;
					if($Editor)	$t.=$edit;
					if($t!='./'){
						echo "<a href='?dir=.'>$PhpDir</a>";
						$brwsname=basename($t);
						if(strlen($brwsname)>32) $brwsname=substr($brwsname,0,32).'…';
						$Path=' / '.htmlspecialchars($brwsname,ENT_QUOTES,'UTF-8');
						$ahref=dirname($t);
						while($ahref!='.'){
							$brwshref=htmlspecialchars('?dir='.$ahref,ENT_QUOTES,'UTF-8');
							$brwsname=basename($ahref);
							if(strlen($brwsname)>32) $brwsname=substr($brwsname,0,32).'…';
							$Path=" / <a href='$brwshref'>$brwsname</a>".$Path;
							$ahref=dirname($ahref);
						}
						echo $Path;
						if(!$Editor) echo ' /';
					}else echo $PhpDir; ?>
				</div>
			</div><?php
			if($Editor)
				include 'inc_Editor.php';
			else{ ?>
				<div id="Brwsr">
					<!--script>
						$("#DropBox").on("keydown", function(event){
						  $( "#log" ).html( event.type + ": " +  event.which );
						});
						var code = e.keyCode || e.which;
							if(code == 13) { // Enter keycode
							//Do something
						}
					</script-->
					<div id="BrwsrHdr"><?php
						// Top left icons
						if(!$InRecycle){
							if($Pow_nDir)	echo "<div class='Btn DBtn' onclick='nDir()'><img src='$IncDir/icons/sys/folder.png' alt='New folder' title='New folder' $WxH16 /></div>";
							if($Pow_nFile)	echo " <div class='Btn DBtn' onclick='nFile()'><img src='$IncDir/icons/sys/file.png' alt='New file' title='New file' $WxH16 /></div>";
							if($Pow_Upload)	include 'inc_Uploader.php';
							if($Pow_RecBin AND $RecBinON)	echo "<a class='Btn DBtn' href='?dir=.Recycle' style='margin: 0 20px;'><img src='$IncDir/icons/sys/recycle.png' title='Recycle Bin' alt='Recycle Bin' $WxH16 /></a>";
						}else{
							echo "<a class='Btn DBtn' href='?dir=$dir&amp;emptyBin'><img src='$IncDir/icons/sys/emptyBin.png' title='Empty Recycle Bin' alt='Empty Bin' $WxH16 /></a>";
						}
						// Top right icons
						echo '<div style="display: inline-block; float: right; margin-right: 10px">';
						if($ThbON)	echo $MozBtn;
						echo $ThbBtn,$TypBtn,$SizBtn,$DatBtn;
						if($Pow_ChMod)	echo $ChmBtn;
						echo '</div>'; ?>
					</div>
					<div id="Content" class="Container"><table id="List" class="sortable">
						<thead><tr><?php
							echo '<th id="File">Filename</th>';
							if($ThbON) echo '<th id="Thumbs"></th>';
							if($TypON) echo '<th id="Type">Type</th>';
							if($SizON) echo '<th id="Size">Size</th>';
							if($DatON) echo '<th id="Date">Date modified</th>';
							if($ChmON) echo '<th id="Chmd">Permissions</th>';
						?></tr></thead>
						<tbody><?php
							if($F5){ // Disp empty line in table
								$t='<td><a href="">…</a></td>';	echo $t;
								if($ThbON) echo $t; if($TypON) echo $t; if($SizON) echo $t; if($DatON) echo $t; if($ChmON) echo $t;
								$DirsNb=0; $FilesNb=0;
							}else{ // Creates and displays table
								// Browses folder
								if($dirhref=='./') $XcludFiles[]=strtolower($PhpFile); // Hide self
								$b=$ThbON && (basename($dirhref)!='_Thumbs');
								$hdl=opendir($dirhref);
								while($entryName=readdir($hdl)){ // Gets each entry in hdl
									if($entryName!='..' AND $entryName!='.'){
										if(!in_array(substr($entryName, 0, 1),$XcludPrfix) OR $HdnON){
											if(is_dir($dirhref.$entryName)){
												if(!in_array(strtolower($entryName),$XcludDirs) OR $HdnON)
													$aDir[]=$entryName;
											}elseif(!in_array(strtolower($entryName),$XcludFiles) OR $HdnON){
												$aFile[]=$entryName;
												if($b){
													$extn=explode('.',$entryName);
													if(in_array(strtoupper(end($extn)),$ThbablesExtns)) // If thumbable
														$aThbables[]=$entryName;
												}
											}
										}
									}
								}
								closedir($hdl);
								// Thumbs management
								$ImgsNb= isset($aThbables) ? count($aThbables) : 0;
								if($ImgsNb){
									$ThumbsDir=$dirhref.'_Thumbs/';
									list($ThbsArray,$ThbsW,$ThbsH)= NTAK_Thumbs($aThbables,$dirhref,$ThumbsDir,$Thb_MaxWidth,$Thb_MaxHeight);
									$ThbsNames=array_map('basename',$ThbsArray);
								}
								// Displays the table
								if($dir!='.'){ // Disp parent dir
									$brwshref=htmlspecialchars("?dir=".dirname($dirhref),ENT_QUOTES,'UTF-8'); // Solves the ' problem
									$hrf="href='$brwshref'"; $t="<td><a $hrf></a></td>";
									echo "<tr><td><a $hrf><img src='$IncDir/icons/parent.png' alt='' $WxH16 />..</a></td>";
									if($ThbON) echo $t;
									if($TypON) echo "<td><a $hrf>&lt; Parent Directory &gt;</a></td>";
									if($SizON) echo $t; if($DatON) echo $t; if($ChmON) echo $t;
									echo '</tr>';
								}
								if(isset($aDir)){ // Disp dirs
									$DirsNb=count($aDir); sort($aDir);
									for($i=0; $i<$DirsNb; $i++){
										// Gets names
										$name=$aDir[$i];
										$namehref=$dirhref.$name;
										// Creates links
										$brwshref="./$PhpFile?dir=".$namehref;
										$brwshref=htmlspecialchars($brwshref,ENT_QUOTES,'UTF-8'); // Solves the ' problem
										$hrf="href='$brwshref'";
										// Start of line
										echo "<tr><td><div><a $hrf>";
										switch($namehref){ // Icon selection
											case './.Recycle':	echo "<img src='$IncDir/icons/sys/recycle.png' alt='Recycle Bin' $WxH16 />"; break;
											case './'.$CfgDir:	echo "<img src='$IncDir/icons/sys/config.png' alt='Config' $WxH16 />"; break;
											default:			echo "<img src='$IncDir/icons/folder.png' alt='' $WxH16 />"; break;
										}
										if(strlen($name)>64){ // Shortens long name
											$s=substr($name,0,64)."…";
											echo "<span title='$name'>$s</span>";
										}else		echo $name;
										if(in_array(substr($name, 0, 1),$XcludPrfix) OR in_array(strtolower($name),$XcludDirs))
											echo $HdnImg;
										echo '</a><div class="Tools">';
										if($Pow_Tools){ // FileTools
											if(($Pow_Tools AND !in_array($name,$ReadOnlyDirs)) OR $Pow_ToolsXtnd){
												if(!in_array($name,$CopyOnlyDirs) OR $Pow_ToolsXtnd)
													echo "<div class='Btn' onclick='ren(\"$namehref\")'>$RenImg</div>",
														"<div class='Btn' onclick='mov(\"$namehref\")' >$MovImg</div>";
												if(!$InRecycle) echo "<div class='Btn' onclick='cop(\"$namehref\")'>$CopImg</div>";
												if(!in_array($name,$CopyOnlyDirs) OR $Pow_ToolsXtnd)
													echo "<div class='Btn' onclick='del(\"$namehref\")'>$DelImg</div>";
											}
										}
										echo '</div></div></td>';
										if($ThbON) echo "<td><a $hrf></a></td>";
										if($TypON) echo "<td><a $hrf>&lt; Directory &gt;</a></td>";
										if($SizON){ // Size
											if($DirSizeON){
												list($size,$nbFiles)=foldersize($namehref);
												$t=SizeB($size);
												echo "<td sorttable_customkey='$size'><a $hrf>$t</a></td>";
											}else echo "<td><a $hrf>-</a></td>";
										}
										if($DatON){ // Date
											$modtime=date("M j Y g:i A", filemtime($namehref));
											$timekey=date("YmdHis", filemtime($namehref));
											echo "<td sorttable_customkey='$timekey'><a $hrf>$modtime</a></td>";
										}
										if($ChmON){ // Chmod
											$Perms=File_Permissions($namehref);
											echo "<td><div><a $hrf>$Perms</a><div class='Btn Chm' onclick='chmod(\"$namehref\", \"$Perms\")'>$ChmImg</div></div></td>";
										}
										echo '</tr>'; // End of line
									}
								}else $DirsNb=0;
								if(isset($aFile)){ // Disp files
									$FilesNb=count($aFile); sort($aFile);
									for($i=0; $i<$FilesNb; $i++){
										// Gets names
										$name=$aFile[$i];
										$namehref=$dirhref.$name;
										// Extension
										$exts=explode('.',$name);
										$extn= (count($exts)==1) ? ' ' : strtoupper(end($exts));
										if(!in_array($extn,$XcludExtns) OR $Pow_HiddenAgent){
											switch($extn){ // Prettifies files
												case 'BMP': case 'JPG': case 'JPEG': case 'PNG': case 'SVG': case 'GIF':
													$type=$extn.' Image';			$icon='image.png';	break;
												case 'MP3': case 'OGG': case 'AAC': case 'WMA': case 'WAV':
													$type=$extn.' Audio File';		$icon='audio.png';	break;
												case 'AVI': case 'WMV': case 'MP4': case 'MOV': case 'M4A':
													$type=$extn.' Video File';		$icon='video.png';	break;
												case 'PPT': case 'PPTX': case 'PPS': case 'PPSX':
													$type='PowerPoint Document';	$icon='office.png';	break;
												case 'XLS': case 'XLSX': case 'XLSM':
													$type='Excel Document';			$icon='xls.png';	break;
												case 'DOC': case 'DOCX': case 'DOCM':
													$type='Word Document';			$icon='doc.png';	break;
												case 'HTML': case 'HTM': case 'XML':
													$type=$extn.' File';			$icon='xml.png';	break;
												case 'RAR': case 'ISO':
													$type=$extn.' Archive';			$icon='rar.png';	break;
												case 'DWG':	$type='DWG Drawing';	$icon='dwg.png';	break;
												case 'ZIP':	$type='ZIP Archive';	$icon='zip.png';	break;
												case 'PHP':	$type='PHP Script';		$icon='php.png';	break;
												case 'CSS':	$type='Stylesheet';		$icon='css.png';	break;
												case 'TXT':	$type='Text File';		$icon='txt.png';	break;
												case 'RTF':	$type='Rich Text File';	$icon='rtf.png';	break;
												case 'PDF':	$type='PDF Document';	$icon='pdf.png';	break;
												case 'EXE': $type='Executable';		$icon='exe.png';	break;
												case 'LOG': $type='Log File';		$icon='txt.png';	break;
												case 'JS':	$type='Javascript';		$icon='js.png';		break;
												case 'LUA':	$type='LUA script';		$icon='lua.png';	break;
												case 'BAK': $type='Backup File';	$icon='file.png';	break;
												case 'PART':	$type='Part file';	$icon='part.png';	break;
												case 'COLOR':	$type='Color';		$icon='color.jpg';	break;
												default:	$type=$extn.' File';	$icon='file.png';	break;
											}
											$namehref=htmlspecialchars($namehref,ENT_QUOTES,'UTF-8'); // Solves the ' problem
											$hrf="href='$namehref' target='_blank'";
											echo "<tr><td><div><a $hrf><img src='$IncDir/icons/$icon' alt='' $WxH16 />"; // Start of line
											if(strlen($name)>64){ // Shortens long name
												$s=substr($name,0,64)."…";
												echo "<span title='$name'>$s</span>";
											}else	echo $name;
											if(in_array(substr($name, 0, 1),$XcludPrfix) OR in_array(strtolower($name),$XcludFiles)) // Hidden ?
												echo $HdnImg;
											echo '</a><div class="Tools">';
											if(in_array($extn,$EditableExtns)){ // Editors
												if($Pow_tMCE && in_array($extn,$ArticleExtns))
													echo "<div class='Btn' onclick=\"window.location = ('./$PhpFile?dir=$dirhref&amp;edit=$name&amp;tMCE');\">$tMCEimg</div>";
												if($Pow_CM)
													echo "<div class='Btn' onclick=\"window.location = ('./$PhpFile?dir=$dirhref&amp;edit=$name&amp;CM');\">$CMimg</div>";
												if(($Pow_Tools AND !in_array($name,$ReadOnlyFiles)) OR $Pow_ToolsXtnd)
													echo '<div style="display: inline-block; height: 14px; margin: 0px 8px; border-right: 1px solid rgba(0,0,0,.1);"></div>';
											}
											if(($Pow_Tools AND !in_array($name,$ReadOnlyFiles)) OR $Pow_ToolsXtnd){ // FileTools
												if(!in_array($name,$CopyOnlyFiles) OR $Pow_ToolsXtnd)
													echo "<div class='Btn' onclick='ren(\"$namehref\")'>$RenImg</div>",
														"<div class='Btn' onclick='mov(\"$namehref\")' >$MovImg</div>";
												if(!$InRecycle) echo "<div class='Btn' onclick='cop(\"$namehref\")'>$CopImg</div>";
												if(!in_array($name,$CopyOnlyFiles) OR $Pow_ToolsXtnd)
													echo "<div class='Btn' onclick='del(\"$namehref\")'>$DelImg</div>";
											}
											echo '</div></div></td>';
											if($ThbON){ // Thumbnails
												echo '<td>';
												if($extn=='COLOR'){ // Color picker
													$color=file_get_contents("$namehref");
													if($Pow_Color){
														echo "<div><div class='Color CP' id='picker$i' style='background-color: $color;'></div></div>";
														echo "<!--script>$('#picker$i').colpick({ flat: false, layout: 'rgbhex', color: '$color', submit: 1, onSubmit: function(hsb,hex,rgb,el){ window.location = window.location+'&elm=$namehref&hex='+hex; } });</script-->";
													}else{
														echo "<div><div class='Color' style='background-color: $color;'></div></div>";
													}
												}
												echo "<a $hrf>";
												if($ImgsNb){
													if(in_array($name,$ThbsNames)){
														$Thumbhref=htmlspecialchars($ThumbsDir.$name,ENT_QUOTES,'UTF-8'); // Solves the ' problem
														echo "<img src='$Thumbhref' />";
													}
												}
												echo '</a></td>';
											}
											if($TypON) echo "<td><a $hrf>$type</a></td>";
											if($SizON){ // Size
												$size=filesize($namehref);
												$t=SizeB($size);
												echo "<td sorttable_customkey='$size'><a $hrf>$t</a></td>";
											}
											if($DatON){ // Date
												$modtime=date("M j Y g:i A", filemtime($namehref));
												$timekey=date("YmdHis", filemtime($namehref));
												echo "<td sorttable_customkey='$timekey'><a $hrf>$modtime</a></td>";
											}
											if($ChmON){ // Chmod
												$Perms=File_Permissions($namehref);
												echo "<td><div><a $hrf>$Perms</a><div class='Btn Chm'><div onclick='chmod(\"$namehref\", \"$Perms\")'>$ChmImg</div></div></div></td>";
											}
											echo '</tr>'; // End of line
										}
									}
								}else $FilesNb=0;
							} ?>
						</tbody>
						<tr><?php // Colored end of table
							$t='<th></th>'; echo $t;
							if($ThbON) echo $t; if($TypON) echo $t; if($SizON) echo $t; if($DatON) echo $t; if($ChmON) echo $t; ?>
						</tr>
					</table></div>
					<div id="BrwsrFtr"><?php
						if($Pow_HiddenAgent)	echo "<div id='Agent'>$HdnBtn</div><div id='Inf' style='right: 64px'>";
						else		echo '<div id="Inf" style="right: 20px">';
						if($DirsNb){
							echo "$DirsNb Dir";		if($DirsNb>1) echo 's';
							if($FilesNb) echo ', ';
						}
						if($FilesNb){
							echo "$FilesNb File";	if($FilesNb>1) echo 's';
						}
						echo '</div>'; ?>
					</div>
				</div><?php
				if($ThbON){ ?>
					<div class="Mosaic Veil" onclick="$('.Mosaic').fadeOut(800); $('#MozBtn').removeClass('TBtn1').addClass('TBtn0');"></div>
					<div id="InMosaic" class="Mosaic Container"><?php
						if($ImgsNb){ // Displays ThumbFiles
							$ThbsNb=count($ThbsArray);
							sort($ThbsArray);
							for($i=0; $i<$ThbsNb; $i++){
								$Thumbhref=htmlspecialchars($ThbsArray[$i],ENT_QUOTES); // Solves the ' problem
								$namehref=$dirhref.basename($Thumbhref);
								$name=explode('.',basename($Thumbhref));
								$etxn=strtoupper($name[1]); $name=$name[0];
								if(strlen($name)>20){ // Shortens long name
									$sName=substr($name,0,20)."…";
									$t="<span title='$name'>$sName</span>";
								}else $t=$name;
								list($tW, $tH) = getimagesize($namehref);
								echo "<div><p id='ImgInfo'>$etxn $tW","x$tH</p><a href='$namehref' target='_blank'><img src='$Thumbhref' alt='' /></a><p id='ImgTitle'>$t</p></div>";
							}
						} ?>
					</div><?php
				}
			} ?>
		</body>
	</html><?php
}else{ // ---------------- Global "else" ( "Password required" ) ----------------
	unset($_SESSION[$ss.'StrtTime']);
	$_SESSION[$ss.'Powers']=0;
	if(isset($_GET['dir']) AND $_GET['dir']!='')
		$_SESSION[$ss.'dir']=$_GET['dir'];	// Keeps the requested path
	// -------- Displays page --------
	?><!DOCTYPE html>
	<html>
		<head>
			<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
			<meta http-equiv="X-UA-Compatible" content="IE=edge" />
			<title>Password required</title>
			<link rel="stylesheet" href="<?=$IncDir ?>/style.css" /><?php
			if(file_exists($cfgCSS)) echo "<link rel='stylesheet' href='$cfgCSS' />"; ?>
			<link rel="shortcut icon" href="<?=$IncDir ?>/favicon.png" type="image/png" />
		</head>
		<body>
			<form action="?" method="POST" enctype="multipart/form-data">
				<div id="Password"><?php
					if($MultiUsrs)
							echo '<p>Please type your login and password:</p><input class="TypeArea" type="text" name="Usr" value="" />';
						else
							echo '<p>Please type your password:</p>'; ?>
					<input class="TypeArea" type="password" name="Pwd" value="" />
					<input class="Btn" type="submit" name="SubmitLog" value="Enter" />
					<br />
					<p><?php echo "( The session closes after $SessTimeOut minutes of inactivity. )"; ?></p>
				</div>
			</form>
		</body>
	</html>
<?php } // ---------------- Global "end" ---------------- ?>