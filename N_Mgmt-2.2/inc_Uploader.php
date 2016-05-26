<?php /*-.___________________________________________________________________________
/
	Launcher: Uploading
		Plupload (File uploading API)
\________________________________________________________________________________.-*/

// Include Path
$Inc_plup=$IncDir.'/plupload-2.1.2';
$UpldDir=$PhpFullDir.'/'.str_replace('./','',$dir);
//$ChunkSize=min(ini_get('post_max_size'),ini_get('upload_max_filesize'),'8M');
?>
<script type="text/javascript" src="<?=$Inc_plup ?>/js/plupload.full.min.js"></script>
<div class="Btn DBtn" id="pickfiles" href="javascript:;"><img src="<?=$IncDir ?>/icons/sys/upload.png" title="Upload" alt="Upload" height="16" width="16" /></div>
<div class="Upload Veil"></div>
<div id="UpldList" class="Upload Container">
	<div>
	<img src="<?=$IncDir ?>/icons/sys/upload.png" alt="" height="16" width="16" /><b><i> Uploading files</i></b><a href=""><img src="<?=$IncDir ?>/icons/sys/logout.png" title="Cancel" alt="X" height="16" width="16" /></a><hr><hr>
	<!--div class="Container"></div-->
	</div>
</div>
<script type="text/javascript">
	function Elm(id){ return document.getElementById(id); }
	function attachCallbacks(Uploader){
		Uploader.bind('FileUploaded', function(Up, File, Response){
			if( (Uploader.total.uploaded + 1) == Uploader.files.length){ window.location.reload(false); }
		});
	}
	var uploader = new plupload.Uploader({
		browse_button: 'pickfiles',
		chunk_size: '512kb',
		max_retries: 5,
		drop_element: 'DropBox',
		url: '<?=$Inc_plup ?>/upload.php?dir=<?=$UpldDir ?>',
		preinit: attachCallbacks,
		init: {
			PostInit: function(){
				$("#DropBox").on("dragenter", function(){ $(".Upload.Veil").show(); });
				$(".Upload.Veil").on("dragleave", function(){ $(".Upload.Veil").fadeOut('0.8s'); });
				$("#DropBox").on("drop", function(){ $(".Upload").show(); });
			},
			FilesAdded: function(up, files){
				$(".Upload").show();
				UpldList=Elm("UpldList");
				plupload.each(files, function(file){
					UpldList.innerHTML += '<div id="' + file.id + '" title="'+ file.name +'">' + file.name.substring(0,40) + '… (' + plupload.formatSize(file.size) + ')<b></b><br /></div>';
				});
				uploader.start();
			},
			UploadProgress: function(up, file){
				$(".Upload").show();
				Elm(file.id).getElementsByTagName('b')[0].innerHTML = '<span style="float: right;">' + file.percent + '%</span>';
			},
			Error: function(up, err){
				UpldList.innerHTML += "\nError #" + err.code + ": " + err.message;
			}
		}
	});
	uploader.init();
</script>