<?php /*-.___________________________________________________________________________
/
	Launcher: Text-editing
		TinyMCE			(Javascript HTML article editor)
		CodeMirror		(Code editor)
\________________________________________________________________________________.-*/

// Include Paths
$Inc_tMCE=	$IncDir.'/tinymce-4.0.26';
$Inc_CM=	$IncDir.'/codemirror-4.1';

$File_Content=file_get_contents($edithref);
$File_Content = str_replace(
	array('<textarea','textarea>'),
	array('<!--EDIT textarea','textarea EDIT-->'),
$File_Content);
$exts=explode('.',$edit);
$extn= (count($exts)==1) ? ' ' : strtoupper(end($exts)); ?>
<form id="MyForm" action="" method="POST"><div id="Brwsr">
	<div id="BrwsrHdr">
		<input class="Btn" type="reset" name="Reset" value="Reset" style="margin-top: 2px;" />
		<input class="Btn" type="submit" name="Save" value="Save" style="margin-top: 2px;" />
	</div>
	<div class="Container"><textarea class="Editor" id="MyTextArea" name="EditPost" style="width: 100%; height: 100%;"><?php print_r($File_Content); ?></textarea></div>
	<div id="BrwsrFtr"></div>
</div></form><?php
if(isset($_GET['tMCE'])){ ?>
	<script type="text/javascript" src="<?=$Inc_tMCE ?>/tinymce.min.js"></script>
	<script type="text/javascript">
		tinymce.init({
			selector: "#MyTextArea",
			theme: "modern",
			content_css: "<?=$tMCE_Css ?>",
			body_class: "<?=$tMCE_Class ?>",
			plugins: [
				 "advlist autolink link image lists charmap print preview hr anchor pagebreak",
				 "searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking",
				 "save table contextmenu directionality emoticons template paste textcolor"
			],
			toolbar: "reset save | undo redo | bold italic underline | alignleft aligncenter alignright alignjustify | forecolor backcolor | bullist numlist outdent indent | link image | preview media fullscreen",
		});
	</script><?php
}
/*if(isset($_GET['CKE'])){ ?>
	<script type="text/javascript" src="<?=$Inc_CKE ?>/ckeditor.js"></script>
	<script type="text/javascript">
		CKEDITOR.replaceClass = 'Editor';
	</script><?php
}*/
if(isset($_GET['CM'])){
	switch ($extn){
		case 'HTM': case 'HTML':		$mode='htmlmixed'; break;
		case 'CSS':						$mode='css'; break;
		case 'JS':						$mode='javascript'; break;
		case 'PHP':						$mode='php'; break;
		case 'INI': case 'HTACCESS':	$mode='properties'; break;
		default:						$mode=''; break;
	} ?>
	<link rel="stylesheet" type="text/css" href="<?=$Inc_CM ?>/lib/codemirror.css" />
	<link rel="stylesheet" type="text/css" href="<?=$Inc_CM ?>/addon/hint/show-hint.css">
	<link rel="stylesheet" type="text/css" href="<?=$Inc_CM ?>/addon/fold/foldgutter.css" />
	<script src="<?=$Inc_CM ?>/lib/codemirror.js"></script>
	<script src="<?=$Inc_CM ?>/mode/xml/xml.js"></script>
	<script src="<?=$Inc_CM ?>/mode/javascript/javascript.js"></script>
	<script src="<?=$Inc_CM ?>/mode/css/css.js"></script>
	<script src="<?=$Inc_CM ?>/mode/htmlmixed/htmlmixed.js"></script>
	<script src="<?=$Inc_CM ?>/mode/clike/clike.js"></script>
	<script src="<?=$Inc_CM ?>/mode/php/php.js"></script>
	<script src="<?=$Inc_CM ?>/mode/xml/xml.js"></script>
	<script src="<?=$Inc_CM ?>/mode/properties/properties.js"></script>
	<script src="<?=$Inc_CM ?>/addon/selection/active-line.js"></script>
	<script src="<?=$Inc_CM ?>/addon/fold/foldcode.js"></script>
	<script src="<?=$Inc_CM ?>/addon/fold/foldgutter.js"></script>
	<script src="<?=$Inc_CM ?>/addon/fold/brace-fold.js"></script>
	<script src="<?=$Inc_CM ?>/addon/fold/xml-fold.js"></script>
	<script src="<?=$Inc_CM ?>/addon/fold/comment-fold.js"></script>
	<script type="text/javascript">
		var elm = document.getElementById("MyTextArea");
		var editor = CodeMirror.fromTextArea(elm, {
			mode:  "<?=$mode ?>",
			theme: "default",
			//indentUnit: 4,
			smartIndent: false,
			tabSize: 4,
			indentWithTabs: true,
			electricChars: true,
			//specialChars: RegExp,
			//specialCharPlaceholder: function(char) ? Element,
			//rtlMoveVisually: 0,
			//keyMap: string,
			//extraKeys: object,
			lineWrapping: true,
			lineNumbers: true,
			//lineNumberFormatter:  function(line: integer) ? string,
			//fixedGutter: boolean,
			//coverGutterNextToScrollbar: boolean,
			//readOnly: boolean|string,
			showCursorWhenSelecting: true,
			//undoDepth: 40,
			//historyEventDelay: 500,
			//tabindex: integer,
			//autofocus: boolean,
			//dragDrop: boolean,
			//onDragEvent: function(instance: CodeMirror, event: Event) ? boolean,
			//onKeyEvent: function(instance: CodeMirror, event: Event) ? boolean,
			//cursorBlinkRate: number,
			//cursorScrollMargin: number,
			//cursorHeight: number,
			//resetSelectionOnContextMenu: boolean,
			//workTime: number,
			//workDelay: number,
			//pollInterval: number,
			//flattenSpans: boolean,
			//addModeClass: boolean,
			//maxHighlightLength: number,
			//crudeMeasuringFrom: number,
			//viewportMargin: integer,
			styleActiveLine: true,
			foldGutter: {
				rangeFinder: new CodeMirror.fold.combine(CodeMirror.fold.brace, CodeMirror.fold.comment)
			},
			gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
		});
	</script><?php
} ?>