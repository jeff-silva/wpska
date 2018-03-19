<?php

/*

1) Crie a validação no functions.php:

wpska_contact_validate('fale-conosco', function($resp, $post) {
	if (! filter_var($post['email'], FILTER_VALIDATE_EMAIL)) {
		$resp->error('E-mail inválido');
	}
	return $resp;
});



2) Crie o HTML no template:

<?php echo wpska_contact('fale-conosco', function() { ?>
	<div class="form-group">
		<label>E-mail</label>
		<input type="text" name="email" class="form-control">
	</div>

	<input type="submit" value="Enviar" class="btn btn-default">

	<div class="wpska-form-error"></div>
	<div class="wpska-form-success">Contato enviado. Obrigado!</div>
<?php }); ?>

*/



function wpska_contact_validate($form_id, $callback) {
	add_filter("wpska_contact_action_{$form_id}", function($resp) use($form_id, $callback) {
		$request = $_REQUEST;
		unset($request['wpska_form_action'], $request['wpska_contact_action'], $request['post_type'], $request['form_id']);

		foreach($_FILES as $key=>$val) {
			if ($_FILES[$key]['tmp_name']) {
				$upl = wpska_upload($key, get_option('wpska_form_contact_attachments_dir', '/form-attachments/'));
				if ($upl['error']) { $resp['error'][] = $upl['error']; }
				else { $request[$key] = $upl['upload']; }
			}
		}

		$resp2 = call_user_func($callback, $resp, $request);
		$resp = get_class($resp2)=='Wpska_Response'? $resp2: $resp;

		if (! $resp->error()) {

			$post_content = '';
			foreach($request as $key=>$val) {
				$key = ucfirst(preg_replace('/[-_]/', ' ', $key));
				$post_content .= "<p><strong>{$key}:</strong> {$val}</p>";
			}


			$post_title = ucwords(preg_replace('/[^a-zA-Z0-9]/', ' ', $_REQUEST['form_id']));
			$return = wp_insert_post(array(
				'post_content' => $post_content,
				'post_title' => $post_title,
				'post_status' => 'draft',
				'post_type' => 'wpska_contact',
			), true);

			foreach($request as $key=>$val) { update_post_meta($return, $key, $val); }

			if ($to = get_option('wpska_form_contact_to')) {
				wp_mail($to, $post_title, $post_content, array(
					'Content-Type: text/html; charset=UTF-8',
				));
			}

			$resp->success($return);
			return $resp;
		}
	});
}



//return wpska_form($form_id, $content, $attrs, array('post_type'=>'wpska_contact'));
function wpska_contact($form_id, $content=null) {

	if (is_callable($content)) {
		ob_start();
		call_user_func($content);
		$content = ob_get_clean();
	}

	$post = array();
	$post['form_id'] = $form_id;
	$post['wpska_contact_action'] = uniqid();

	?>
	<form autocomplete="off" onsubmit="return wpska_contact(this);">
	<?php foreach($post as $key=>$val) echo "<input type='hidden' name='{$key}' value='{$val}' >"; ?> 
	<?php echo $content; ?>
	</form>
	<script>
	var wpska_contact = function(form) {
		var $=jQuery, $form=$(form), $success=$form.find(".wpska-form-success"),
		$error=$form.find(".wpska-form-error"), post={};

		var formData = new FormData();		
		$form.find(":input").each(function() {
			if (!this.name) return;
			if (this.type=="file") {
				for(var i in this.files) {
					if (typeof this.files[i]!="object") continue;
					formData.append((this.name+'_'+i), this.files[i]);
				}
			}
			else { formData.append(this.name, this.value); }
		});

		$success.hide(); $error.hide();
		$form.addClass("wpska-form-loading");
		$.ajax({
			url: '<?php echo site_url('?wpska_form_action'); ?>',
			type: 'post',
			contentType: false,
			processData: false,
			data: formData,
			success: function(resp) {
				$form.removeClass("wpska-form-loading");
				resp = JSON.parse(resp);
				if (resp.error) {
					$error.fadeIn(200).html(resp.error.join('<br />'));
					return false;
				}

				if (resp.success) {
					$success.fadeIn(200);
					form.reset();
				}
			},
			error: function(resp) {
				$form.removeClass("wpska-form-loading");
				$error.fadeIn(200).html(resp.statusText);
			},
		});

		return false;
	};
	</script>
	<style>.wpska-form-success, .wpska-form-error {display:none;}</style>
	<?php
}



function wpska_newsletter($form_id, $content=null, $attrs=null) {
	if (is_callable($content)) {
		ob_start();
		call_user_func($content);
		$content = ob_get_clean();
	}

	$post = array();
	$post['form_id'] = $form_id;
	$post['wpska_newsletter_action'] = uniqid();

	?>
	<form autocomplete="off" onsubmit="return wpska_newsletter(this);">
	<?php foreach($post as $key=>$val) echo "<input type='hidden' name='{$key}' value='{$val}' >"; ?> 
	<?php echo $content; ?>
	</form>
	<script>
	var wpska_newsletter = function(form) {
		var $=jQuery, $form=$(form), $success=$form.find(".wpska-form-success"),
		$error=$form.find(".wpska-form-error"), post={};

		var formData = new FormData();		
		$form.find(":input").each(function() {
			if (!this.name) return;
			formData.append(this.name, this.value);
		});

		$success.hide(); $error.hide();
		$form.addClass("wpska-form-loading");
		$.ajax({
			url: '<?php echo site_url('?wpska_newsletter_action'); ?>',
			type: 'post',
			contentType: false,
			processData: false,
			data: formData,
			success: function(resp) {
				$form.removeClass("wpska-form-loading");
				resp = JSON.parse(resp);
				if (resp.error) {
					$error.fadeIn(200).html(resp.error.join('<br />'));
					return false;
				}

				if (resp.success) {
					$success.fadeIn(200);
					form.reset();
				}
			},
			error: function(resp) {
				$form.removeClass("wpska-form-loading");
				$error.fadeIn(200).html(resp.statusText);
			},
		});

		return false;
	};
	</script>
	<style>.wpska-form-success, .wpska-form-error {display:none;}</style>
	<?php
}




add_action('load-edit.php', function() { ?>
<?php if (isset($_GET['post_type']) AND $_GET['post_type']=='wpska_form'): ?>
<style>
.post-state, #filter-by-date, #post-query-submit {display:none;}
</style>
<?php endif; ?>
<?php });




class Wpska_Form_Actions
{
	
	public $post_types = array('wpska_contact', 'wpska_newsletter');

	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;
			add_action($method, array($this, $method), 10, 12);
		}
	}



	public function init() {
		
		// Contact action
		if (isset($_REQUEST['wpska_contact_action'])) {
			$resp = new Wpska_Response();
			$resp2 = apply_filters("wpska_contact_action_{$_REQUEST['form_id']}", $resp);
			$resp = get_class($resp2)=='Wpska_Response'? $resp2: $resp;
			$resp->json();
		}


		// Newsletter action
		if (isset($_REQUEST['wpska_newsletter_action'])) {
			global $wpdb;
			$resp = new Wpska_Response();

			// Email invalido
			if (! filter_var($_REQUEST['email'], FILTER_VALIDATE_EMAIL)) {
				$resp->error('E-mail inválido');
			}

			// Email existente
			$has_posts = sizeof(get_posts(array(
				'post_type' => 'wpska_newsletter',
				'post_status' => 'any',
				'meta_query' => array(
					'relation' => 'AND',
					array(
						'key' => 'email',
						'value' => $_REQUEST['email'],
						'compare' => '=',
					),
					array(
						'key' => 'form_id',
						'value' => $_REQUEST['form_id'],
						'compare' => '=',
					),
				),
			)));
			if ($has_posts) {
				$resp->error('E-mail já cadastrado');
			}

			// Nome existe mas está vazio
			if (isset($_REQUEST['name']) AND empty($_REQUEST['name'])) {
				$resp->error('Por favor, informe seu nome.');
			}

			if (! $resp->error()) {
				$return = wp_insert_post(array(
					'post_content' => "Nome: {$_REQUEST['name']} <br>E-mail: {$_REQUEST['email']}",
					'post_title' => $_REQUEST['email'],
					'post_status' => 'draft',
					'post_type' => 'wpska_newsletter',
				), true);

				update_post_meta($return, 'name', $_REQUEST['name']);
				update_post_meta($return, 'email', $_REQUEST['email']);
				update_post_meta($return, 'form_id', $_REQUEST['form_id']);
				$resp->success($return);
			}

			$resp->json();
		}


		if (get_option('wpska_form_contact')) {
			register_post_type('wpska_contact', array(
				'labels' => array(
					'name' => 'Contatos',
					'singular_name' => 'Contatos',
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-format-status',
			));
		}

		if (get_option('wpska_form_newsletter')) {
			register_post_type('wpska_newsletter', array(
				'labels' => array(
					'name' => 'Newsletter',
					'singular_name' => 'Newsletter',
				),
				'public' => true,
				'has_archive' => true,
				'menu_icon' => 'dashicons-email-alt',
			));
		}
	}


	public function admin_head()
	{
		?><style>
		.menu-icon-wpska_newsletter ul,
		.menu-icon-wpska_contact ul {display:none;}
		</style><?php
	}


	public function all_admin_notices()
	{
		$post_type = isset($_GET['post_type'])? $_GET['post_type']: 'page';
		if (! in_array($post_type, $this->post_types)) return;
		?><style>
		.row-actions, .tablenav, .subsubsub, .page-title-action, .search-box {display:none !important;}
		</style><?php
	}


	public function wpska_settings()
	{
		wpska_tab('Formulários & Contatos', function() { ?>
		<div class="row">
			<div class="col-xs-6">
				<div class="panel panel-default">
					<div class="panel-heading">Contato</div>
					<div class="panel-body">
						
						<?php $wpska_form_contact = get_option('wpska_form_contact', '0'); ?>
						<div class="form-group">
							<label>Ativar Contato</label>
							<select name="wpska_form_contact" class="form-control" data-value="<?php echo $wpska_form_contact; ?>">
								<option value="0">Inativo</option>
								<option value="1">Ativo</option>
							</select>
						</div>

						<?php if ($wpska_form_contact): ?>
						<div class="form-group">
							<label>Enviar para o e-mail</label>
							<input type="text" name="wpska_form_contact_to" value="<?php echo get_option('wpska_form_contact_to'); ?>" class="form-control">
						</div>

						<div class="form-group">
							<label>Pasta de anexos</label>
							<div class="input-group">
								<div class="input-group-addon">
									<?php $dir = wp_upload_dir();
									echo $dir['baseurl']; ?>/
								</div>
								<input type="text" name="wpska_form_contact_attachments_dir" value="<?php echo get_option('wpska_form_contact_attachments_dir', '/form-attachments/'); ?>" class="form-control">
							</div>
							<small class="text-muted">Observar se a hospedagem permite que o script crie pastas.
							Caso essa permissão não esteja disponível, deixar este campo vazio, assim os uploads
							serão feitos para a pasta raiz.</small>
						</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<div class="col-xs-6">
				<div class="panel panel-default">
					<div class="panel-heading">Newsletter</div>
					<div class="panel-body">
						<div class="form-group">
							<label>Ativar Newsletter</label>
							<select name="wpska_form_newsletter" class="form-control" data-value="<?php echo get_option('wpska_form_newsletter', '0'); ?>">
								<option value="0">Inativo</option>
								<option value="1">Ativo</option>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
		<?php });
	}
}




class Wpska_Form_Filters
{
	public function __construct()
	{
		foreach(get_class_methods($this) as $method) {
			if ($method=='__construct') continue;
			add_filter($method, array($this, $method), 10, 2);
		}
	}


	public function manage_wpska_contact_posts_columns($columns)
	{
		return array('data_read' => 'Ler');
	}


	public function manage_wpska_contact_posts_custom_column($column)
	{
		global $post;
		
		//data_read
		if ($column=='data_read'): ?>
			<div style="float:right;">
				<?php $delete_url = wp_nonce_url(admin_url("post.php?post={$post->ID}&action=trash"), "trash-post_{$post->ID}"); ?>
				<a href="<?php echo $delete_url; ?>" onclick="return confirm('Tem certeza que deseja deletar?');">Deletar</a>
			</div>
			<strong><?php echo "#{$post->ID} - {$post->post_title}"; ?></strong><br>

			<div class="<?php echo "post-text-toggle-{$post->ID}"; ?>"><a href="javascript:;" onclick="post_text_toggle('<?php echo ".post-text-toggle-{$post->ID}"; ?>');">Ler mais</a></div>

			<div class="<?php echo "post-text-toggle-{$post->ID}"; ?>" style="display:none;">
			<a href="javascript:;" onclick="post_text_toggle('<?php echo ".post-text-toggle-{$post->ID}"; ?>');">Ler menos</a>
			<div style="padding:15px; background:#fff; border:solid 1px #eee;">
				<?php $regex = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
				$post_content = preg_replace($regex, '<a href="$0" target="_blank">$0</a>', $post->post_content);
				echo nl2br($post_content); ?>
			</div>
			</div>

			<script>var post_text_toggle = function(className) { jQuery(className).slideToggle(200); };</script>
		<?php endif;

	}


	public function manage_wpska_newsletter_posts_columns($columns)
	{
		return array(
			'newsletter_email' => 'E-mail',
			'newsletter_name' => 'Nome',
			'newsletter_form_id' => 'Grupo',
		);
	}


	public function manage_wpska_newsletter_posts_custom_column($column)
	{
		global $post;
		
		if ($column=='newsletter_email') {
			echo $post->post_title;
		}

		else if ($column=='newsletter_name') {
			echo get_post_meta($post->ID, 'name', true);
		}

		else if ($column=='newsletter_form_id') {
			echo get_post_meta($post->ID, 'form_id', true);
		}

	}


	public function post_row_actions($actions, $post)
	{
		if ($post->post_type=='wpska_contact' OR $post->post_type=='wpska_newsletter') {
			$trash = $actions['trash'];
			$actions = array();

			$data_read = admin_url("post.php?post={$post->ID}&action=edit");
			$actions['edit'] = "<a href='{$data_read}'>Ler</a>";
			
			$actions['trash'] = $trash;

			$actions['html'] = "<div>{$post->post_content}</div>";
		}
		return $actions;
	}
}


new Wpska_Form_Actions();
new Wpska_Form_Filters();
