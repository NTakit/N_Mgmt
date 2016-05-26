<?php /*-.___________________________________________________________________________
/
	Name:			NTAK_Thumbs
	Description:	Creates dir and thumb images of required sizes if don't exist.
					Returns arrays of thumbfiles and sizes.
	Author:			N.Takit
	Last Update:	13/03/2014
\________________________________________________________________________________.-*/

include 'Return_Bytes.php';

// Thb_MaxWidth and Thb_MaxHeight :  0 to inhibe
// list($aThbs, $aWThbs, $aHThbs) = NTAK_Thumbs($Dir, $ThbsDir, $Wmax, $Hmax, 1);
function NTAK_Thumbs($FilesArray, $Dir, $ThbsDir, $Wmax, $Hmax){
	// Inits
	$MaxMem=Return_Bytes(ini_get('memory_limit'));
	$aThbs=''; $aWThbs=''; $aHThbs='';
	if(!file_exists($ThbsDir)){ // Creates ThbsDir dir if doesn't exist
		mkdir($ThbsDir);
		chmod($ThbsDir, 0755);
	}
	// Loops through the array of files to manage Thumbs
	if(isset($FilesArray)){
		for($i=0; $i<count($FilesArray); $i++){
			// Gets names
			$name=$FilesArray[$i];
			$File=$Dir.$name;
			$Thb=$ThbsDir.$name;
			$Do=1;
			// Calculates image ratio
			list($WImg, $HImg) = getimagesize($File); // Gets image size
			$ratioh= ($Hmax==0) ? 1 : $Hmax/$HImg;
			$ratiow= ($Wmax==0) ? 1 : $Wmax/$WImg;
			$ratio= min( ($ratiow < $ratioh) ? $ratiow : $ratioh , 1);
			$W=round($WImg*$ratio,0);
			$H=round($HImg*$ratio,0);
			if(file_exists($Thb)){ // Check Thb if exists
				$modtime=date("M j Y g:i A", filemtime($File));
				$modtimeThb=date("M j Y g:i A", filemtime($Thb));
				list($WThb, $HThb) = getimagesize($Thb);
				if((($modtime<$modtimeThb) AND ($WThb==$W) AND ($HThb==$H))){ // If Thb is good,
					$aThbs[]=$Thb; $aWThbs[]=$W; $aHThbs[]=$H; $Do=0; // don't create.
				}
			}
			if($Do){
				$memNeed=round((($WImg*$HImg)+Pow(2,16))*4)+round((($W*$H)+Pow(2,16))*4);
				if((memory_get_usage()+$memNeed)>$MaxMem) $Do=0;
			}
			if($Do){
				$ThbTmp=imagecreatetruecolor($W, $H);
				$exts=explode('.',$name);
				$extn=strtolower(end($exts));
				switch($extn){
					case 'jpg': case 'jpeg':
						$ImgTmp = imagecreatefromjpeg($File);
						imagecopyresampled($ThbTmp, $ImgTmp, 0, 0, 0, 0, $W, $H, $WImg, $HImg);
						imagejpeg($ThbTmp, $Thb, 100); // 100 is quality
					break;
					case 'png':
						$ImgTmp = imagecreatefrompng($File);
						imagealphablending($ThbTmp, false);
						imagecopyresampled($ThbTmp, $ImgTmp, 0, 0, 0, 0, $W, $H, $WImg, $HImg);
						imagesavealpha($ThbTmp, true);
						imagepng($ThbTmp, $Thb);
					break;
					case 'gif':
						$ImgTmp = imagecreatefromgif($File);
						$bgc = imagecolorallocate ($ThbTmp, 255, 255, 255);
						imagefilledrectangle ($ThbTmp, 0, 0, $W, $H, $bgc);
						imagecopyresampled($ThbTmp, $ImgTmp, 0, 0, 0, 0, $W, $H, $WImg, $HImg);
						imagegif($ThbTmp, $Thb, 100); // 100 is quality
					break;
					default: break;
				}
				imagedestroy($ImgTmp);
				imagedestroy($ThbTmp);
				chmod($Thb, 0644);
				$aThbs[]=$Thb; $aWThbs[]=$W; $aHThbs[]=$H;
			}
		}
	}
	return array($aThbs, $aWThbs, $aHThbs);
}
?>