<?php



class Wpska_Email_Ajax extends Wpska_Ajax
{
	public function wpska_email_test()
	{
		if (! filter_var($_REQUEST['to'])) {
			$this->error('E-mail inválido');
		}

		if (! $_REQUEST['message']) {
			$this->error('Mensagem vazia');
		}

		if (! $this->error()) {
			return wp_mail($_REQUEST['to'], 'E-mail test', $_REQUEST['message']);
		}
	}
}



class Wpska_Email_Actions extends Wpska_Actions
{
	public function wpska_settings()
	{
		wpska_tab('E-mail', function() { ?>
		
		<?php $wpska_email = get_option('wpska_email'); ?>
		<div class="row">
			<div class="col-sm-6 form-group">
				<label>Ativo</label>
				<select name="wpska_email" class="form-control" data-value="<?php echo $wpska_email; ?>">
					<option value="0">Inativo</option>
					<option value="1">Ativo</option>
				</select>
			</div>
			<div class="clearfix"></div>

			<?php if ($wpska_email): ?>
			<div class="col-sm-8">
				<?php $wpska_email_template = get_option('wpska_email_template', '{$content}');
				wp_editor($wpska_email_template, 'wpska_email_template', 'editor_height=300'); ?>
				<br><small>Não se esqueça de inserir a variável {$content}, ela representa a mensagem enviada.</small>
			</div>

			<div class="col-sm-4 form-group wpska_email_test">
				<label>Testar envio</label>
				<input type="text" id="wpska_email_test_to" class="form-control" placeholder="Enviar para" onkeydown="if (event.keyCode==13) wpska_email_test_send(event);"><br>
				<textarea class="form-control" id="wpska_email_test_message" placeholder="Mensagem"></textarea>
				<div id="wpska_email_test_response"></div>
				<br>
				<div class="text-right">
					<input type="button" value="Enviar" class="btn btn-primary" onclick="wpska_email_test_send();">
				</div>
				<script>
				var wpska_email_test_send = function(ev) {
					var $=jQuery;
					if (ev) ev.preventDefault();
					var post = {
						wpska: "wpska_email_test",
						to: $("#wpska_email_test_to").val(),
						message: $("#wpska_email_test_message").val(),
					};
					$(".wpska_email_test").css({opacity:.5});
					$.post("<?php echo site_url(); ?>", post, function(resp) {
						$(".wpska_email_test").css({opacity:1});
						var str = '<div class="alert alert-danger">Erro desconhecido</div>';
						if (resp.error) str = '<div class="alert alert-danger">'+ resp.error.join('<br />') +'</div>';
						if (resp.success) str = '<div class="alert alert-success">E-mail enviado</div>';
						$("#wpska_email_test_response").html(str);
					}, "json");
				};
				</script>
			</div>
			<?php endif; ?>
		</div>
		<?php });
	}
}



class Wpska_Email_Filters extends Wpska_Filters
{
	public function wp_mail($args)
	{
		$wpska_email_template = get_option('wpska_email_template', '{$content}');
		$args['message'] = str_replace('{$content}', $args['message'], $wpska_email_template);
		$args['headers'] = isset($args['headers'])? $args['headers']: array();
		$args['headers'] = is_array($args['headers'])? $args['headers']: array();
		$args['headers'][] = 'Content-Type: text/html; charset=UTF-8';
		return $args;
	}
}

new Wpska_Email_Actions();
new Wpska_Email_Filters();
new Wpska_Email_Ajax();