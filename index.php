<?php
$password = "password"; // Let empty to disable password management

session_start(); // (re)Starts session
if (($_SESSION['time']) + 60*5 < time())	$_SESSION['access']=0; // Kills access if > 5mins
if ($password == "")						$_SESSION['access']=1; // Grants access if no password

if(!$_SESSION['access'] AND ($_POST['mot_de_passe']!=$password OR !isset($_POST["submit"])))
{
	?>
	<!doctype html>
	<html>
	<head>
		<meta charset="UTF-8">
		<title>Password required</title>
	</head>
	<body>
		<form action="" method="POST" enctype="multipart/form-data">
			<table align="center">
				<tr><td align="center">
				Please type your password:
				</td></tr>
				<tr><td align="center" >
				<input size="25" type="password" name="mot_de_passe" value="" >
				</td></tr>
				<tr><td align="center">
				And click... <input type="submit" name="submit" value="Submit" />
				</td></tr>
				<tr><td align="center">
				<font size="2">( The session closes after 5 minutes of inactivity. )</font>
				</td></tr>
			</table>
		</form>
	</body>
	</html>
	<?php
}
else{
	$_SESSION['access']=1; // Grants access
	$_SESSION['time']=time(); // Sets the session beginning time
	?>
	<!doctype html>
	<html>

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title>Directory Contents</title>
		<link rel="stylesheet" href="inc/style.css">
		<script src="inc/sorttable.js"></script>
	</head>

	<body>
		<div id="container">
	  
			<h1>Directory Contents</h1>
			
			<table class="sortable">
				<thead>
					<tr>
						<th>Filename</th>
						<th>Type</th>
						<th>Size <small>(bytes)</small></th>
						<th>Date Modified</th>
					</tr>
				</thead>
				<tbody><?php
					// Reads dir, ignores .. and opens directory
					if(isset($_GET['dir'])) $dir=str_replace("..","",$_GET['dir'])."/";
					$dirhref="./".$dir;
					$o_dir=opendir($dirhref);
					// Gets each entry, ignores self
					while($entryName=readdir($o_dir)) {
						if($dir OR ($entryName!="index.php" && $entryName!="inc")) $dirArray[]=$entryName;
					}
					// Closes directory
					closedir($o_dir);

					// Sorts files
					sort($dirArray);
					// Loops through the array of files
					for($index=0; $index < count($dirArray); $index++) {
						if(substr("$dirArray[$index]", 0, 1) != ".") { // Hides . files and directories
							// Gets Names
							$name=$dirArray[$index];
							$namehref=$dirhref.$name;
							// Gets Extensions
							$filename=strtolower($namehref);
							$exts=split("[/\\.]",$filename);
							$n=count($exts)-1;
							$extn=$exts[$n];
							// Gets Sizes
							$size=number_format(filesize($namehref));
							// Gets Date Modified Data
							$modtime=date("M j Y g:i A", filemtime($namehref));
							$timekey=date("YmdHis", filemtime($namehref));
							  
							// Prettifies Types
							switch ($extn){
								case "png": $extn="PNG Image"; break;
								case "jpg": $extn="JPEG Image"; break;
								case "svg": $extn="SVG Image"; break;
								case "gif": $extn="GIF Image"; break;
								case "ico": $extn="Windows Icon"; break;
								
								case "txt": $extn="Text File"; break;
								case "log": $extn="Log File"; break;
								case "htm": $extn="HTML File"; break;
								case "php": $extn="PHP Script"; break;
								case "js":	$extn="Javascript"; break;
								case "css": $extn="Stylesheet"; break;
								case "pdf": $extn="PDF Document"; break;

								case "rar": $extn="RAR Archive"; break;								
								case "zip": $extn="ZIP Archive"; break;
								case "bak": $extn="Backup File"; break;
								
								default: $extn=strtoupper($extn)." File"; break;
							}
							  
							// Separates directories
							if(is_dir($namehref)) {
								$extn="&lt;Directory&gt;"; 
								$size="&lt;Directory&gt;"; 
								$class="dir";
								$namehref="./?dir=".$dir.$dirArray[$index];
							} else {
								$class="file";
							}
													
							// Solves the ' problem : That's not the only problem
							$namehref = htmlspecialchars($namehref,ENT_QUOTES);
							// Prints 'em
							print("	<tr class='$class'>
										<td><a href='$namehref'>$name</a></td>
										<td><a href='$namehref'>$extn</a></td>
										<td><a href='$namehref'>$size</a></td>
										<td sorttable_customkey='$timekey'><a href='$namehref'>$modtime</a></td>
									</tr>");
						}
					}
				?></tbody>
			</table>
			
			<h2><?php
				if($dir) print("<a href='.'>Return to root</a>");
			?></h2>
		
		</div>
	</body>

	</html>
	<?php
}
?>