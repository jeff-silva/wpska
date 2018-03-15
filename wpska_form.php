<?php

/*

1) Crie o HTML. Parâmetro 1 é o ID do formulário, 

<?php echo wpska_form_contact('footer', null, function() { ?>
	<div class="form-group">
		<label>E-mail</label>
		<input type="text" name="email" class="form-control">
	</div>
<?php }); ?>

*/




function wpska_form_contact($form_id, $content=null, $attrs=null) {
	return wpska_form($form_id, $attrs, $content, array('post_type'=>'wpska_contact'));
}



function wpska_form_newsletter($form_id, $content=null, $attrs=null) {
	return wpska_form($form_id, $attrs, $content, array('post_type'=>'wpska_newsletter'));
}



function wpska_form($form_id, $content=null, $attrs=null, $post=array()) {
	
	if (is_callable($content)) {
		ob_start();
		call_user_func($content);
		$content = ob_get_clean();
	}

	$post['form_id'] = $form_id;
	$post['wpska_form_action'] = uniqid();
	
	?>
	<form <?php echo $attrs; ?> onsubmit="return wpska_form_submit(this);">
		<?php foreach($post as $key=>$val) { echo "<input type='hidden' name='{$key}' value='{$val}'>"; } ?> 
		<?php echo $content; ?>
	</form>
	<script>
	var wpska_form_submit = function(form) {
		var $=jQuery,
		$form=$(form),
		$success=$form.find(".wpska-form-success"),
		$error=$form.find(".wpska-form-error"),
		post=<?php echo json_encode($post); ?>;

		var formData = new FormData();		
		$form.find(":input").each(function() {
			if (!this.name) return;

			if (this.type=="file") {
				for(var i in this.files) {
					if (typeof this.files[i]!="object") continue;
					formData.append((this.name+'_'+i), this.files[i]);
				}
			}

			else {
				formData.append(this.name, this.value);
			}
		});


		$success.hide();
		$error.hide();
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
					$error.show().html(resp.error.join('<br />'));
					return false;
				}

				$success.show();
				form.reset();
			},
			error: function(resp) {
				console.log('error:', resp);
			},
		});

		return false;
	};
	</script>
	<style>
	.wpska-form-loading {opacity:.5;}
	.wpska-form-error, .wpska-form-success {display:none;}</style>
	<?php
}



function wpska_form_response($form_id, $callback) {
	if (isset($_REQUEST['wpska_form_action']) AND isset($_REQUEST['form_id']) AND $_REQUEST['form_id']==$form_id AND is_callable($callback)) {
		add_action('init', function() use($form_id, $callback) {
			$request = $_POST;
			unset($request['form_id'], $request['post_type'], $request['wpska_form_action']);
			$resp = array('success'=>false, 'error'=>array());

			foreach($_FILES as $key=>$val) {
				if ($_FILES[$key]['tmp_name']) {
					$upl = wpska_upload($key, get_option('wpska_form_contact_attachments_dir', '/form-attachments/'));
					if ($upl['error']) { $resp['error'][] = $upl['error']; }
					else { $request[$key] = $upl['upload']; }
				}
			}

			$resp = call_user_func($callback, $request, $resp);

			if (empty($resp['error'])) {
				$resp['error'] = false;

				$post_content = '';
				foreach($request as $key=>$val) {
					$key = ucfirst(preg_replace('/[-_]/', ' ', $key));
					$post_content .= "<p><strong>{$key}:</strong> {$val}</p>";
				}

				$post_title = uvwords(str_replace('/[^a-zA-Z0-9]/', ' ', $_REQUEST['form_id']));
				$post_title .= " #{$_REQUEST['wpska_form_action']}";
				$return = wp_insert_post(array(
					'post_content' => $post_content,
					'post_title' => $post_title,
					'post_status' => 'draft',
					'post_type' => $_REQUEST['post_type'],
				), true);

				if ($return) {
					foreach($request as $key=>$val) {
						update_post_meta($return, $key, $val);
					}
				}

				if ($to = get_option('wpska_form_contact_to')) {
					wp_mail($to, $post_title, $post_content, array('Content-Type: text/html; charset=UTF-8'));
				}

				$resp['success'] = $return;
			}
			
			$resp['post'] = $request;
			echo json_encode($resp); die;
		});
	}
}




add_filter('manage_wpska_form_posts_columns', function ( $columns ) {
	$columns['wpska_form_data'] = 'Dados';
	return $columns;
});

add_filter('manage_edit-wpska_form_columns', function($columns) {
	return array(
		'cb' => $columns['cb'],
		'title' => $columns['title'],
		'wpska_form_data' => $columns['wpska_form_data'],
	);
});


add_action('manage_wpska_form_posts_custom_column', function ($column_name, $post_id ) {
    
    if ($column_name == 'wpska_form_data') {
    	$post = get_post($post_id);
    	$post->post_content = json_decode($post->post_content, true);
    	$ignore = array('wpska_form_action', 'type', 'email');
    	?>
		
		<button style="float:right;" onclick="_toggleDetails(this);">Mais detalhes</button>
    	<strong><?php echo $post->post_content['email']; ?></strong>
    	<div style="clear:both;"></div>
		<div class="toggle-details" style="display:none;">
			<?php foreach($post->post_content as $key=>$val): if (in_array($key, $ignore)) continue; ?>
			<div>
				<strong><?php echo $key; ?></strong>
				&nbsp; <?php echo $val; ?>
			</div>
			<?php endforeach; ?>
		</div>

		<script>
		var _toggleDetails = function(el) {
			var $=jQuery;
			var $wrapper = $(el).parent().find('.toggle-details');
			$wrapper.slideToggle(200);
			if ($wrapper.find(">*").length==0) {
				$wrapper.html('<div style="color:#777; text-align:center; background:#eee; padding:5px;">Nenhum detalhe</div>');
			}
		};
		</script>
    	<?php
	}

}, 10, 2);


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
			add_action($method, array($this, $method));
		}
	}



	public function init() {
		if (get_option('wpska_form_contact')) {
			register_post_type('wpska_contact', array(
				'labels' => array(
					'name' => 'Contatos',
					'singular_name' => 'Contatos',
				),
				'public' => true,
				'has_archive' => true,
				'supports' => array('title'),
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
				'supports' => array('title'),
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
						<div class="form-group">
							<label>Ativar Contato</label>
							<select name="wpska_form_contact" class="form-control" data-value="<?php echo get_option('wpska_form_contact', '0'); ?>">
								<option value="0">Inativo</option>
								<option value="1">Ativo</option>
							</select>
						</div>

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

	public function manage_wpska_newsletter_posts_columns($columns)
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
				<a href="<?php echo $delete_url; ?>">Deletar</a>
			</div>
			<strong><?php echo $post->post_title; ?></strong><br>

			<div class="<?php echo "post-text-toggle-{$post->ID}"; ?>"><a href="javascript:;" onclick="post_text_toggle('<?php echo ".post-text-toggle-{$post->ID}"; ?>');">Ler mais</a></div>

			<div class="<?php echo "post-text-toggle-{$post->ID}"; ?>" style="display:none;">
			<a href="javascript:;" onclick="post_text_toggle('<?php echo ".post-text-toggle-{$post->ID}"; ?>');">Ler menos</a>
			<div style="padding:15px; background:#fff;">
				<?php $regex = '~(?:(https?)://([^\s<]+)|(www\.[^\s<]+?\.[^\s<]+))(?<![\.,:])~i';
				$post_content = preg_replace($regex, '<a href="$0" target="_blank">$0</a>', $post->post_content);
				echo $post_content; ?>
			</div>
			</div>

			<script>
			var post_text_toggle = function(className) {
				jQuery(className).slideToggle(200);
			};
			</script>
		<?php endif;

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
