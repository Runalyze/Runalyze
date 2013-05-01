	<h1>Eine Trainings-Datei hochladen</h1>

	<div id="upload-container">
		<div class="c button hide" id="file-upload">Datei hochladen</div>
	</div>

<script>
var uploaderIsDefined = false;
function createUploader() {
	if (uploaderIsDefined)
		return;

	$("#file-upload").removeClass("hide");
	uploaderIsDefined = true;

	var submittedFiles = [], completedFiles = 0;

	new qq.FineUploaderBasic({
		button: $("#file-upload")[0],
		request: {
			endpoint: '<?php echo $_SERVER['SCRIPT_NAME']; ?>?json=true'
		},
		validation: {
			allowedExtensions: [<?php echo "'".implode("', '", $this->Filetypes)."'"; ?>],
			//sizeLimit: 204800 // 200 kB = 200 * 1024 bytes
		},
		callbacks: {
			onError: function(id, name, errorReason, xhr) {
				$("#ajax").append('<p class="error appended-by-uploader">'+errorReason+'</p>');
			},
			onSubmit: function(id, fileName) {
				submittedFiles.push(fileName);
				$("#upload-container").addClass('loading');
			},
			//onUpload: function(id, fileName) {
			//	// Initializing
			//},
			//onProgress: function(id, fileName, loaded, total) {
			//	if (loaded < total) {
			//		progress = Math.round(loaded / total * 100) + '% of ' + Math.round(total / 1024) + ' kB';
			//	} else {
			//		// Saving
			//	}
			//},
			onComplete: function(id, fileName, responseJSON) {
				completedFiles++;

				if (completedFiles == submittedFiles.length) {
					$(".appended-by-uploader").remove();

					if (completedFiles == 1)
						$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?file='+encodeURIComponent(fileName));
					else
						$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?files='+encodeURIComponent(submittedFiles.join(';')));
				}
				

				if (!responseJSON.success) {
					if (responseJSON.error == '')
						responseJSON.error = 'An unknown error occured.';
					$("#ajax").append('<p class="error appended-by-uploader">'+fileName+': '+responseJSON.error+'</p>');
					$("#upload-container").removeClass('loading');
				}
			}
		}
	});
}

function createUploaderOLD() {
	$("#file-upload").removeClass("hide");
	new AjaxUpload('#file-upload', {
		allowedExtensions: [<?php echo "'".implode("', '", $this->Filetypes)."'"; ?>],
		action: '<?php echo $_SERVER['SCRIPT_NAME']; ?>?json=true',
		onSubmit : function(file, extension){
			$("#upload-container").addClass('loading');
		},
		onComplete : function(file, response){
			$(".appended-by-uploader").remove();

			if (response.substring(0,7) == 'success')
				$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?file='+encodeURIComponent(file));
			else {
				if (response == '')
					response = 'An unknown error occured.';
				$("#ajax").append('<p class="error appended-by-uploader">'+response+'</p>');
				$("#upload-container").removeClass('loading');
			}
		}		
	});
}
</script>

	<p class="text">
		&nbsp;
	</p>

	<p class="info">
		Unterst&uuml;tzte Formate: <?php echo '*.'.implode(', *.', $this->Filetypes); ?>
	</p>

<?php foreach ($this->filetypeInfo() as $info): ?>
	<p class="info">
		<?php echo $info; ?>
	</p>
<?php endforeach; ?>