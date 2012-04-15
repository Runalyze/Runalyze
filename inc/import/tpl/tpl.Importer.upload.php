	<h1>Eine Trainings-Datei hochladen</h1>

	<div class="c button hide" id="file-upload">Datei hochladen</div>

<script>
function createUploader() {
	$("#file-upload").removeClass("hide");
	new AjaxUpload('#file-upload', {
		allowedExtensions: [<?php echo $AllowedFormatsForJS; ?>],
		action: '<?php echo $_SERVER['SCRIPT_NAME']; ?>?json=true',
		onComplete : function(file, response){
			$("#ajax").loadDiv('<?php echo $_SERVER['SCRIPT_NAME']; ?>?file='+encodeURIComponent(file));
		}		
	});
}
</script>

	<p class="text">
		&nbsp;
	</p>

	<p class="info">
		Unterst&uuml;tzte Formate: <?php echo $AllowedFormats; ?>
	</p>

<?php foreach (self::$additionalImporterInfo as $info): ?>
	<p class="info">
		<?php echo $info; ?>
	</p>
<?php endforeach; ?>