<?php


if (isset($_REQUEST['wpska_email_test'])) {
	add_action('init', function() {
		$headers = array('Content-Type: text/html; charset=UTF-8');
		$sent = wp_mail($_REQUEST['to'], 'E-mail test', $_REQUEST['message'], $headers);
		dd($sent, $_REQUEST);
		die;
	});
}


class Wpska_Email_Ajax extends Wpska_Ajax
{
	public function wpska_email_test()
	{
		return array(
			'rand' => rand(),
		);
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
				wp_editor($wpska_email_template, 'wpska_email_template'); ?>
				<br><small>Não se esqueça de inserir a variável {$content}, ela representa a mensagem enviada.</small>
			</div>

			<div class="col-sm-4 form-group">
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
						wpska_email_test: true,
						to: $("#wpska_email_test_to").val(),
						message: $("#wpska_email_test_message").val(),
					};
					$.post("<?php echo site_url(); ?>", post, function(resp) {
						$("#wpska_email_test_response").html(resp);
					});
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
		return $args;
	}
}

new Wpska_Email_Actions();
new Wpska_Email_Filters();
new Wpska_Email_Ajax();