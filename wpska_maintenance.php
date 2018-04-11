<?php

class Wpska_Maintenance_Actions extends Wpska_Actions
{
	
	public function init()
	{
		if (! get_current_user_id()) {
			echo get_option('wpska_maintenance_html'); die;
		}
	}


	public function wpska_settings()
	{
		wpska_tab('Manutenção', function() {
		$wpska_maintenance_html = get_option('wpska_maintenance_html');
		?>

		<select name="wpska_maintenance_active" data-value="<?php echo get_option('wpska_maintenance_active'); ?>" class="form-control">
			<option value="">Inativa</option>
			<option value="1">Ativa</option>
		</select>

		<iframe src="" id="wpska-maintenance-iframe" style="border:none; width:100%; height:0px;"></iframe>
		<textarea name="wpska_maintenance_html" id="wpska-settings-codemirror" data-codemirror="{}"><?php echo $wpska_maintenance_html; ?></textarea>
		<button type="button" class="btn btn-default" onclick="_wpskaSettingsCodemirrorDefaultHtml();">Default HTML</button>
		<button type="button" class="btn btn-default" onclick="_wpskaSetingsMaintenancePreview();">Preview</button>

<!-- Default HTML -->
<script type="text/html" id="wpska_settings_default_html"><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>

</body>
</html></script>
<!-- Default HTML -->

		<script>
		var _wpskaSettingsCodemirrorDefaultHtml = function() {
			var html = $("#wpska_settings_default_html").html();
			var editor = $("#wpska-settings-codemirror").get(0).codemirror();
			editor.getDoc().setValue(html);
		};

		var _wpskaSetingsMaintenancePreview = function() {
			if (CodeMirror||false) {
				var editor = $("#wpska-settings-codemirror").get(0).codemirror();
				html = editor.getValue();

				var doc = $("#wpska-maintenance-iframe")[0].contentWindow.document;
				doc.open();
				doc.write(html);
				doc.close();

				var _wpskaSettingsMaintenanceIframeResize = function() {
					var height = (doc.body.offsetHeight||0) + 20;
					console.log(height);
					$("#wpska-maintenance-iframe").css({"height":height});
				};

				$("#wpska-maintenance-iframe").on("load", _wpskaSettingsMaintenanceIframeResize);
				$(doc).find("*").on("load", _wpskaSettingsMaintenanceIframeResize);
				_wpskaSettingsMaintenanceIframeResize();
			}
		};
		</script>
		<?php });
	}
}


new Wpska_Maintenance_Actions();