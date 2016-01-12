	<div id="upload-container">
		<div class="c button" id="file-upload"><?php _e('Upload file'); ?></div>
	</div>

<script>
var submittedFiles = [], completedFiles = 0, uploadedFiles = 0;

new qq.FineUploaderBasic({
	button: $("#file-upload")[0],
	request: {
		endpoint: '<?php echo $_SERVER['SCRIPT_NAME']; ?>?json=true'
	},
	validation: {
		allowedExtensions: [<?php echo "'".implode("', '", $this->Filetypes)."'"; ?>]
	},
	callbacks: {
		onError: function(id, name, errorReason, xhr) {
			$("#ajax").append('<p class="error appended-by-uploader">'+name+': '+errorReason+'</p>');
		},
		onSubmit: function(id, fileName) {
			submittedFiles.push(fileName);
			$("#upload-container").addClass('loading');
		},
		onComplete: function(id, fileName, responseJSON) {
			uploadedFiles++;

			if (responseJSON.success) {
				completedFiles++;

				if (completedFiles == submittedFiles.length) {
					$(".appended-by-uploader").remove();

					if (completedFiles == 1)
						$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?file='+encodeURIComponent(fileName));
					else
						$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?files='+encodeURIComponent(submittedFiles.join(';')));
				}
			} else {
				$("#ajax").append('<p class="error appended-by-uploader"><?php _e('There were problems while uploading.'); ?></p>');
			}

			if (uploadedFiles == submittedFiles.length) {
				$("#upload-container").removeClass('loading');	

				submittedFiles = [];
				completedFiles = 0;
				uploadedFiles = 0;				
			}
		}
	}
});

if (!qq.supportedFeatures.ajaxUploading)
	$("#ajax").append('<p class="error"><?php _e('Your browser does not seem to support this uploader, see'); ?> <a href="http://docs.fineuploader.com/browser-support.html" target="_blank">http://docs.fineuploader.com/browser-support.html</a>.</p>');
</script>

	<p class="text">
		&nbsp;
	</p>

	<p class="info">
		<a href="http://help.runalyze.com/en/latest/starting-guide/import.html#different-file-types" target="_blank" title="<?php _e('Table showing included data by filetype'); ?>">
		<?php _e('Supported file extensions:'); ?></a> <?php echo '*.'.implode(', *.', $this->Filetypes); ?>
	</p>

	<?php if (Filesystem::getMaximumFilesize() != INFINITY): ?>
	<p class="info">
		<?php _e('Supported file size:'); ?> <?php echo Filesystem::getMaximumFilesizeAsString(); ?>
	</p>
	<?php endif; ?>

<?php foreach ($this->filetypeInfo() as $info): ?>
	<p class="info">
		<?php echo $info; ?>
	</p>
<?php endforeach; ?>